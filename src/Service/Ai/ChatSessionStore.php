<?php

namespace App\Service\Ai;

use Symfony\AI\Chat\ManagedStoreInterface;
use Symfony\AI\Chat\MessageStoreInterface;
use Symfony\AI\Platform\Message\AssistantMessage;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\MessageInterface;
use Symfony\AI\Platform\Message\SystemMessage;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class ChatSessionStore implements MessageStoreInterface, ManagedStoreInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly string $sessionKey = 'tfd_ai_chat_messages',
    ) {
    }

    public function setup(array $options = []): void
    {
        $session = $this->getSession();
        if (!$session instanceof SessionInterface) {
            return;
        }

        if (!$session->has($this->sessionKey)) {
            $session->set($this->sessionKey, []);
        }
    }

    public function drop(): void
    {
        $session = $this->getSession();
        if ($session instanceof SessionInterface) {
            $session->remove($this->sessionKey);
        }
    }

    public function save(MessageBag $messages): void
    {
        $session = $this->getSession();
        if (!$session instanceof SessionInterface) {
            return;
        }

        $session->set($this->sessionKey, $this->normalizeMessages($messages));
    }

    public function load(): MessageBag
    {
        $session = $this->getSession();
        $payload = $session?->get($this->sessionKey, []) ?? [];
        if (!is_array($payload)) {
            $payload = [];
        }

        return $this->denormalizeMessages($payload);
    }

    private function getSession(): ?SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || !$request->hasSession()) {
            return null;
        }

        return $request->getSession();
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    private function normalizeMessages(MessageBag $messages): array
    {
        $payload = [];

        foreach ($messages as $message) {
            if (!$message instanceof MessageInterface) {
                continue;
            }

            $entry = $this->normalizeMessage($message);
            if (null === $entry) {
                continue;
            }

            $payload[] = $entry;
        }

        return $payload;
    }

    /**
     * @return array{role: string, content: string}|null
     */
    private function normalizeMessage(MessageInterface $message): ?array
    {
        $content = null;

        if ($message instanceof UserMessage) {
            $content = $message->asText();
        } elseif ($message instanceof AssistantMessage) {
            $content = $message->getContent();
        } elseif ($message instanceof SystemMessage) {
            $content = $message->getContent();
        }

        if (null === $content) {
            return null;
        }

        $content = trim((string) $content);
        if ('' === $content) {
            return null;
        }

        return [
            'role' => $message->getRole()->value,
            'content' => $content,
        ];
    }

    /**
     * @param array<int, mixed> $payload
     */
    private function denormalizeMessages(array $payload): MessageBag
    {
        $bag = new MessageBag();

        foreach ($payload as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $role = $entry['role'] ?? null;
            $content = $entry['content'] ?? null;
            if (!is_string($role) || !is_string($content) || '' === trim($content)) {
                continue;
            }

            $content = trim($content);

            switch ($role) {
                case 'assistant':
                    $bag->add(Message::ofAssistant($content));
                    break;
                case 'user':
                    $bag->add(Message::ofUser($content));
                    break;
                case 'system':
                    $bag->add(Message::forSystem($content));
                    break;
                default:
                    break;
            }
        }

        return $bag;
    }
}
