<?php

namespace App\Service\Ai;

use Doctrine\DBAL\Connection;
use Symfony\AI\Chat\MessageNormalizer;
use Symfony\AI\Platform\Message\AssistantMessage;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\MessageInterface;
use Symfony\AI\Platform\Message\SystemMessage;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class ChatSessionStorage
{
    private const TABLE = 'ai_chat_session';

    private readonly SerializerInterface $serializer;

    public function __construct(private readonly Connection $connection)
    {
        $this->serializer = new Serializer(
            [new ArrayDenormalizer(), new MessageNormalizer()],
            [new JsonEncoder()],
        );
    }

    public function createSession(?string $title = null, ?MessageBag $messages = null): int
    {
        $now = time();
        $payload = $this->serializeMessages($messages ?? new MessageBag());

        $this->connection->insert(self::TABLE, [
            'project_id' => null,
            'title' => $title,
            'messages' => $payload,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function exists(int $id): bool
    {
        $value = $this->connection->fetchOne(
            'SELECT id FROM '.self::TABLE.' WHERE id = ? AND project_id IS NULL',
            [$id],
        );

        return false !== $value;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSession(int $id): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, title, messages, created_at, updated_at FROM '.self::TABLE.' WHERE id = ? AND project_id IS NULL',
            [$id],
        );

        return is_array($row) ? $row : null;
    }

    /**
     * @return list<array{
     *   id:int,
     *   title:?string,
     *   createdAt:int,
     *   updatedAt:int,
     *   preview:?string,
     *   messageCount:int
     * }>
     */
    public function listSessions(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, title, messages, created_at, updated_at FROM '.self::TABLE.' WHERE project_id IS NULL ORDER BY updated_at DESC'
        );

        $sessions = [];
        foreach ($rows as $row) {
            $messages = $this->deserializeMessages((string) ($row['messages'] ?? ''));
            $preview = $this->extractPreview($messages);

            $sessions[] = [
                'id' => (int) $row['id'],
                'title' => $row['title'] ?? null,
                'createdAt' => (int) $row['created_at'],
                'updatedAt' => (int) $row['updated_at'],
                'preview' => $preview,
                'messageCount' => $messages->count(),
            ];
        }

        return $sessions;
    }

    public function loadMessageBag(int $id): MessageBag
    {
        $row = $this->connection->fetchAssociative(
            'SELECT messages FROM '.self::TABLE.' WHERE id = ?',
            [$id],
        );
        if (!is_array($row)) {
            return new MessageBag();
        }

        return $this->deserializeMessages((string) ($row['messages'] ?? ''));
    }

    public function saveMessageBag(int $id, MessageBag $messages): void
    {
        $payload = $this->serializeMessages($messages);

        $this->connection->update(self::TABLE, [
            'messages' => $payload,
            'updated_at' => time(),
        ], ['id' => $id]);
    }

    public function resetSession(int $id): void
    {
        $this->connection->update(self::TABLE, [
            'messages' => $this->serializeMessages(new MessageBag()),
            'updated_at' => time(),
        ], ['id' => $id]);
    }

    public function updateTitleIfEmpty(int $id, string $title): void
    {
        $title = $this->normalizeTitle($title);
        if ('' === $title) {
            return;
        }

        $current = $this->connection->fetchOne(
            'SELECT title FROM '.self::TABLE.' WHERE id = ?',
            [$id],
        );

        if (null !== $current && '' !== trim((string) $current)) {
            return;
        }

        $this->connection->update(self::TABLE, [
            'title' => $title,
            'updated_at' => time(),
        ], ['id' => $id]);
    }

    /**
     * @param list<array{role?: string, content?: string}> $legacy
     */
    public function createFromLegacy(array $legacy): int
    {
        $messages = new MessageBag();
        foreach ($legacy as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $role = $entry['role'] ?? null;
            $content = $entry['content'] ?? null;
            if (!is_string($role) || !is_string($content) || '' === trim($content)) {
                continue;
            }

            $content = trim($content);
            if ('assistant' === $role) {
                $messages->add(new AssistantMessage($content));
            } elseif ('user' === $role) {
                $messages->add(new UserMessage(new \Symfony\AI\Platform\Message\Content\Text($content)));
            } elseif ('system' === $role) {
                $messages->add(new SystemMessage($content));
            }
        }

        $title = $this->extractPreview($messages);

        return $this->createSession($title, $messages);
    }

    public function createProjectSession(int $projectId, ?string $title = null, ?MessageBag $messages = null): int
    {
        $now = time();
        $payload = $this->serializeMessages($messages ?? new MessageBag());

        $this->connection->insert(self::TABLE, [
            'project_id' => $projectId,
            'title' => $title,
            'messages' => $payload,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getProjectSession(int $projectId, int $id): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, title, messages, created_at, updated_at FROM '.self::TABLE.' WHERE id = ? AND project_id = ?',
            [$id, $projectId],
        );

        return is_array($row) ? $row : null;
    }

    /**
     * @return list<array{
     *   id:int,
     *   title:?string,
     *   createdAt:int,
     *   updatedAt:int,
     *   preview:?string,
     *   messageCount:int
     * }>
     */
    public function listProjectSessions(int $projectId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, title, messages, created_at, updated_at FROM '.self::TABLE.' WHERE project_id = ? ORDER BY updated_at DESC, id DESC',
            [$projectId],
        );

        $sessions = [];
        foreach ($rows as $row) {
            $messages = $this->deserializeMessages((string) ($row['messages'] ?? ''));
            $preview = $this->extractPreview($messages);

            $sessions[] = [
                'id' => (int) $row['id'],
                'title' => $row['title'] ?? null,
                'createdAt' => (int) $row['created_at'],
                'updatedAt' => (int) $row['updated_at'],
                'preview' => $preview,
                'messageCount' => $messages->count(),
            ];
        }

        return $sessions;
    }

    public function deleteProjectSession(int $projectId, int $id): void
    {
        $this->connection->delete(self::TABLE, [
            'id' => $id,
            'project_id' => $projectId,
        ]);
    }

    private function serializeMessages(MessageBag $bag): string
    {
        return $this->serializer->serialize($bag->getMessages(), 'json');
    }

    private function deserializeMessages(string $payload): MessageBag
    {
        $payload = trim($payload);
        if ('' === $payload) {
            return new MessageBag();
        }

        try {
            $messages = $this->serializer->deserialize($payload, MessageInterface::class.'[]', 'json');
        } catch (\Throwable) {
            return new MessageBag();
        }

        if (!is_array($messages)) {
            return new MessageBag();
        }

        return new MessageBag(...$messages);
    }

    private function extractPreview(MessageBag $messages): ?string
    {
        $items = $messages->getMessages();
        if ([] === $items) {
            return null;
        }

        $last = $items[\count($items) - 1];
        if (!$last instanceof MessageInterface) {
            return null;
        }

        $content = $this->extractMessageContent($last);
        if (null === $content) {
            return null;
        }

        $content = trim($content);
        if ('' === $content) {
            return null;
        }

        return $this->normalizeTitle($content);
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

    private function normalizeTitle(string $title): string
    {
        $title = trim($title);
        if ('' === $title) {
            return '';
        }

        if (\strlen($title) > 80) {
            $title = substr($title, 0, 77).'...';
        }

        return $title;
    }
}
