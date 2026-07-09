<?php

namespace App\Controller\Admin;

use App\Service\Ai\ChatSessionStorage;
use App\Service\Ai\DbChatMessageStore;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Chat\Chat;
use Symfony\AI\Platform\Message\AssistantMessage;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\MessageInterface;
use Symfony\AI\Platform\Message\SystemMessage;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AiAgentController extends AbstractController
{
    private const ACTIVE_CHAT_KEY = 'admin_ai_agent_active_id';

    public function __construct(
        #[Autowire(service: 'ai.agent.default')] private readonly AgentInterface $agent,
        private readonly ChatSessionStorage $storage,
    ) {
    }

    #[AdminRoute(path: '/ai/agent', name: 'ai_agent', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        $error = null;
        $metadata = null;
        $requestSession = $request->getSession();
        $prompt = '';
        $systemPrompt = trim((string) ($requestSession?->get('admin_ai_agent_system_prompt', '') ?? ''));

        $activeChatId = (int) $request->request->get('chatId', 0);
        if ($activeChatId <= 0) {
            $activeChatId = (int) ($requestSession?->get(self::ACTIVE_CHAT_KEY, 0) ?? 0);
        }
        if ($activeChatId <= 0 || !$this->storage->exists($activeChatId)) {
            $activeChatId = $this->storage->createSession();
        }

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_token', '');
            if (!$this->isCsrfTokenValid('admin_ai_agent', $csrfToken)) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_ai_agent');
            }

            if ('1' === (string) $request->request->get('new_chat', '')) {
                $newChatId = $this->storage->createSession();
                $systemPrompt = trim((string) $request->request->get('systemPrompt', ''));
                $requestSession?->set('admin_ai_agent_system_prompt', $systemPrompt);
                $requestSession?->set(self::ACTIVE_CHAT_KEY, $newChatId);

                return $this->redirectToRoute('admin_ai_agent');
            }

            $systemPrompt = trim((string) $request->request->get('systemPrompt', $systemPrompt));
            $requestSession?->set('admin_ai_agent_system_prompt', $systemPrompt);

            $prompt = trim((string) $request->request->get('prompt', ''));
            if ('' === $prompt) {
                $error = 'Bitte eine Eingabe angeben.';
            } else {
                try {
                    $bag = $this->storage->loadMessageBag($activeChatId);
                    $messages = new MessageBag();

                    if ('' !== $systemPrompt) {
                        $messages->add(Message::forSystem($systemPrompt));
                    }

                    foreach ($bag as $message) {
                        if (!$message instanceof MessageInterface) {
                            continue;
                        }

                        if ($message instanceof SystemMessage) {
                            continue;
                        }

                        if ($message instanceof UserMessage) {
                            $messages->add(Message::ofUser($message->asText()));
                        } elseif ($message instanceof AssistantMessage) {
                            $messages->add(Message::ofAssistant($message->getContent()));
                        }
                    }

                    $messages->add(Message::ofUser($prompt));

                    $store = new DbChatMessageStore($this->storage, $activeChatId);
                    $chat = new Chat($this->agent, $store);

                    if ('' !== $systemPrompt) {
                        $this->storage->resetSession($activeChatId);
                    }

                    $chat->submit(Message::ofUser($prompt));
                    $bagAfterSubmit = $this->storage->loadMessageBag($activeChatId);

                    $assistantResponse = null;
                    foreach (array_reverse($bagAfterSubmit->getMessages()) as $savedMessage) {
                        if ($savedMessage instanceof AssistantMessage) {
                            $assistantResponse = $savedMessage->getContent();
                            break;
                        }
                    }

                    $metadata = [
                        'saved' => true,
                        'assistantPreview' => $assistantResponse,
                    ];
                } catch (\Throwable $exception) {
                    $error = $exception->getMessage();
                }
            }
        }

        $requestSession?->set(self::ACTIVE_CHAT_KEY, $activeChatId);
        $messages = $this->buildMessageList($this->storage->loadMessageBag($activeChatId));

        return $this->render('admin/ai_agent/index.html.twig', [
            'systemPrompt' => $systemPrompt,
            'prompt' => $prompt,
            'messages' => $messages,
            'metadata' => $metadata,
            'error' => $error,
            'activeChatId' => $activeChatId,
        ]);
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    private function buildMessageList(MessageBag $bag): array
    {
        $list = [];

        foreach ($bag as $message) {
            if (!$message instanceof MessageInterface) {
                continue;
            }

            $role = $message->getRole()->value;
            $content = $this->extractMessageContent($message);

            if (null === $content || '' === trim($content)) {
                continue;
            }

            if ('system' === $role) {
                continue;
            }

            $list[] = [
                'role' => $role,
                'content' => trim($content),
            ];
        }

        return $list;
    }

    private function extractMessageContent(MessageInterface $message): ?string
    {
        if ($message instanceof UserMessage) {
            return $message->asText();
        }

        if ($message instanceof AssistantMessage) {
            return $message->getContent();
        }

        if ($message instanceof SystemMessage) {
            return (string) $message->getContent();
        }

        return null;
    }
}
