<?php

namespace App\Message;

final class ScraperRunMessage
{
    private ?int $runId = null;

    public function __construct(
        private readonly string $spider,
        private readonly string $type,
        private readonly array $arguments,
        ?int $runId = null,
    ) {
        $this->runId = $runId;
    }

    public function getSpider(): string
    {
        return $this->spider;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, string|null>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getRunId(): ?int
    {
        return $this->runId;
    }
}
