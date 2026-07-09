<?php

declare(strict_types=1);

namespace App\Service;

final class SpaAssetLocator
{
    private const ASSETS_PATH = '/public/assets/portals/chiptuningfile/react_website/tuning-sanctuary/assets/';

    public function __construct(private readonly string $projectDir)
    {
    }

    public function locate(string $prefix, string $suffix): string
    {
        $assetsDir = $this->projectDir . self::ASSETS_PATH;
        if (!is_dir($assetsDir)) {
            return '';
        }

        $files = scandir($assetsDir);
        if ($files === false) {
            return '';
        }

        $matches = [];
        foreach ($files as $file) {
            if (!is_string($file)) {
                continue;
            }

            if (str_starts_with($file, $prefix) && str_ends_with($file, $suffix)) {
                $matches[] = $file;
            }
        }

        if ([] === $matches) {
            return '';
        }

        usort($matches, function (string $a, string $b) use ($assetsDir): int {
            $aTime = file_exists($assetsDir . $a) ? (int) filemtime($assetsDir . $a) : 0;
            $bTime = file_exists($assetsDir . $b) ? (int) filemtime($assetsDir . $b) : 0;

            return $bTime <=> $aTime;
        });

        return $matches[0] ?? '';
    }
}
