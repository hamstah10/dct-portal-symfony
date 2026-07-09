<?php

namespace App\Message;

final class AnalyzeImageMessage
{
    public function __construct(
        public readonly int $runId,
        public readonly string $imagePath,
        public readonly string $model,
        public readonly string $prompt,
    ) {
    }
}
