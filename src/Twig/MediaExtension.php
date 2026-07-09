<?php

namespace App\Twig;

use App\Entity\MediaAsset;
use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MediaExtension extends AbstractExtension
{
    public function __construct(private readonly Packages $packages)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('media_url', [$this, 'mediaUrl']),
        ];
    }

    public function mediaUrl(MediaAsset|string|null $asset): ?string
    {
        if ($asset instanceof MediaAsset) {
            $path = $asset->getFilePath();
        } else {
            $path = $asset;
        }

        if (null === $path) {
            return null;
        }

        $path = trim($path);
        if ('' === $path) {
            return null;
        }

        return $this->packages->getUrl(ltrim($path, '/'));
    }
}
