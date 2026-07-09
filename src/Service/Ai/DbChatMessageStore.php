<?php

namespace App\Service\Ai;

use Symfony\AI\Chat\ManagedStoreInterface;
use Symfony\AI\Chat\MessageStoreInterface;
use Symfony\AI\Platform\Message\MessageBag;

final class DbChatMessageStore implements MessageStoreInterface, ManagedStoreInterface
{
    public function __construct(
        private readonly ChatSessionStorage $storage,
        private readonly int $sessionId,
    ) {
    }

    public function setup(array $options = []): void
    {
    }

    public function drop(): void
    {
        $this->storage->resetSession($this->sessionId);
    }

    public function save(MessageBag $messages): void
    {
        $this->storage->saveMessageBag($this->sessionId, $messages);
    }

    public function load(): MessageBag
    {
        return $this->storage->loadMessageBag($this->sessionId);
    }
}
