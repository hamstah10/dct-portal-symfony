<?php

namespace App\Service\Ai\Project;

use Symfony\AI\Store\Document\Transformer\TextSplitTransformer;
use Symfony\AI\Store\Document\TransformerInterface;

final class AiProjectChunkTransformer implements TransformerInterface
{
    private readonly TextSplitTransformer $transformer;

    public function __construct()
    {
        $this->transformer = new TextSplitTransformer(1200, 150);
    }

    public function transform(iterable $documents, array $options = []): iterable
    {
        return $this->transformer->transform($documents, $options);
    }
}
