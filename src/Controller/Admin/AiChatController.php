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

final class AiChatController extends AbstractController
{
    private const LEGACY_SESSION_KEY = 'tfd_ai_chat_messages';
    private const ACTIVE_CHAT_KEY = 'tfd_ai_chat_active_id';

    public function __construct(
        #[Autowire(service: 'ai.agent.default')] private readonly AgentInterface $agent,
        private readonly ChatSessionStorage $storage,
    ) {
    }

    #[AdminRoute(path: '/ai/chat', name: 'ai_chat', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        $error = null;
        $requestSession = $request->getSession();

        $activeChatId = $this->resolveActiveChatId($request);
        $activeChatId = $this->migrateLegacyChats($requestSession, $activeChatId);

        if ($activeChatId <= 0 || !$this->storage->exists($activeChatId)) {
            $activeChatId = $this->storage->createSession();
        }

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_token', '');
            if (!$this->isCsrfTokenValid('admin_ai_chat', $csrfToken)) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_ai_chat', ['chat' => $activeChatId]);
            }

            if ('1' === (string) $request->request->get('new_chat', '')) {
                $newChatId = $this->storage->createSession();

                return $this->redirectToRoute('admin_ai_chat', ['chat' => $newChatId]);
            }

            if ('1' === (string) $request->request->get('clear', '')) {
                $this->storage->resetSession($activeChatId);

                return $this->redirectToRoute('admin_ai_chat', ['chat' => $activeChatId]);
            }

            $messageInput = trim((string) $request->request->get('message', ''));
            if ('' !== $messageInput) {
                try {
                    $store = new DbChatMessageStore($this->storage, $activeChatId);
                    $chat = new Chat($this->agent, $store);
                    $chat->submit(Message::ofUser($messageInput));
                    $this->storage->updateTitleIfEmpty($activeChatId, $messageInput);
                } catch (\Throwable $exception) {
                    $error = $exception->getMessage();
                }
            }
        }

        $requestSession?->set(self::ACTIVE_CHAT_KEY, $activeChatId);

        $messages = $this->buildMessageList($this->storage->loadMessageBag($activeChatId));
        $sessions = $this->storage->listSessions();

        return $this->render('admin/ai_chat/index.html.twig', [
            'messages' => $messages,
            'sessions' => $sessions,
            'activeChatId' => $activeChatId,
            'error' => $error,
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

    private function resolveActiveChatId(Request $request): int
    {
        $queryId = (int) $request->query->get('chat', 0);
        $postId = (int) $request->request->get('chatId', 0);
        $session = $request->getSession();

        if ($postId > 0) {
            return $postId;
        }

        if ($queryId > 0) {
            return $queryId;
        }

        return (int) ($session?->get(self::ACTIVE_CHAT_KEY, 0) ?? 0);
    }

    private function migrateLegacyChats(?\Symfony\Component\HttpFoundation\Session\SessionInterface $session, int $activeChatId): int
    {
        if (!$session instanceof \Symfony\Component\HttpFoundation\Session\SessionInterface) {
            return $activeChatId;
        }

        $legacy = $session->get(self::LEGACY_SESSION_KEY);
        if (!is_array($legacy) || [] === $legacy) {
            return $activeChatId;
        }

        $newChatId = $this->storage->createFromLegacy($legacy);
        $session->remove(self::LEGACY_SESSION_KEY);

        return $activeChatId > 0 ? $activeChatId : $newChatId;
    }
}
