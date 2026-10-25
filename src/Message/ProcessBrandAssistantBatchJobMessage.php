<?php

declare(strict_types=1);

namespace App\Message;

final class ProcessBrandAssistantBatchJobMessage
{
    public function __construct(
        private readonly int $jobId,
    ) {
    }

    public function getJobId(): int
    {
        return $this->jobId;
    }
}
