<?php

namespace App\Message;

final class AnalyzePdfMessage
{
    public function __construct(
        public readonly int $runId,
        public readonly string $filePath,
        public readonly string $model,
        public readonly string $prompt,
    ) {
    }
}
