<?php

namespace App\MessageHandler;

use App\Entity\ScraperRun;
use App\Message\ScraperRunMessage;
use App\Repository\ScraperRunRepository;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsMessageHandler]
final class ScraperRunMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ScraperRunRepository $scraperRunRepository,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function __invoke(ScraperRunMessage $message): void
    {
        $scraperDir = $this->projectDir . '/scrapy-remote-scraper';

        $command = ['scrapy', 'crawl', $message->getSpider()];
        foreach ($message->getArguments() as $key => $value) {
            if (null === $value || '' === trim($value)) {
                continue;
            }
            $command[] = '-a';
            $command[] = sprintf('%s=%s', $key, $value);
        }

        $before = $this->findLatestOutputFile($scraperDir, $message->getSpider(), $message->getType(), $message->getArguments());
        $beforeMtime = $before['mtime'] ?? null;
        $process = new Process($command, $scraperDir);
        $process->setTimeout(900);
        $process->setIdleTimeout(300);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            // let exit code and stderr capture below
        }
        $after = $this->findLatestOutputFile($scraperDir, $message->getSpider(), $message->getType(), $message->getArguments(), $beforeMtime);
        if (null === $after && null !== $before && 'truck_chiptuningfiles' !== $message->getSpider()) {
            $after = $before;
        }

        $run = null;
        if (null !== $message->getRunId()) {
            $run = $this->scraperRunRepository->find($message->getRunId());
        }
        if (null === $run) {
            $run = new ScraperRun();
        }

        try {
            $parametersJson = json_encode($message->getArguments(), \JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $parametersJson = '';
        }

        $stderr = $process->getErrorOutput();
        $wasShutdown = str_contains($stderr, "finish_reason': 'shutdown'")
            || str_contains($stderr, 'Closing spider (shutdown)');

        $run
            ->setSpider($message->getSpider())
            ->setType($message->getType())
            ->setParameters($parametersJson)
            ->setExitCode($process->getExitCode())
            ->setSuccess($process->isSuccessful() && !$wasShutdown)
            ->setStdout($process->getOutput())
            ->setStderr($stderr)
            ->setJsonPath($after['path'] ?? null);

        $this->entityManager->persist($run);
        $this->entityManager->flush();
    }

    /**
     * @return array{path:string,name:string,mtime:int,publicPath:string}|null
     */
    /**
     * @param array<string, string|null> $arguments
     */
    private function findLatestOutputFile(string $scraperDir, string $spider = '', string $type = '', array $arguments = [], ?int $newerThanMtime = null): ?array
    {
        if (!is_dir($scraperDir)) {
            return null;
        }

        $type = $type ?: 'truck';
        if ('truck_chiptuningfiles' === $spider && 'brands:scrape-one' !== ($arguments['mode'] ?? null)) {
            return null;
        }

        if ('truck_chiptuningfiles' === $spider) {
            $brandSlug = $this->slug((string) ($arguments['brand'] ?? ''));
            if ('' === $brandSlug) {
                return null;
            }

            $expectedFiles = [
                sprintf('%s/data/output/%s/%s/%s.json', $scraperDir, $spider, $type, $brandSlug),
                sprintf('%s/data/output/%s/%s.json', $scraperDir, $type, $brandSlug),
            ];
            foreach ($expectedFiles as $expectedFile) {
                if (is_file($expectedFile)) {
                    if (null !== $newerThanMtime && (int) filemtime($expectedFile) <= $newerThanMtime) {
                        continue;
                    }

                    return $this->createOutputFileMetadata($expectedFile);
                }
            }

            return null;
        }

        $outputDirs = [
            sprintf('%s/data/output/%s/%s', $scraperDir, $spider, $type),
            sprintf('%s/data/output/%s', $scraperDir, $type),
        ];

        $files = [];
        foreach ($outputDirs as $outputDir) {
            if (!is_dir($outputDir)) {
                continue;
            }

            $pattern = 'olsx_brands_spider' === $spider ? '/olsx_brands*.json' : '/*.json';
            $files = array_merge($files, glob($outputDir . $pattern) ?: []);
        }

        if ('olsx_brands_spider' === $spider) {
            $files = array_merge($files, glob($scraperDir . '/olsx_brands*.json') ?: []);
        }

        if ([] === $files) {
            return null;
        }

        usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
        return $this->createOutputFileMetadata($files[0]);
    }

    private function slug(string $value): string
    {
        $value = trim(mb_strtolower($value));
        if ('' === $value) {
            return '';
        }

        $value = strtr($value, [
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
            '&' => ' and ',
        ]);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = preg_replace('/-+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    private function createOutputFileMetadata(string $path): array
    {
        $relative = $this->createRelativePublicPath($path);

        return [
            'path' => $path,
            'name' => basename($path),
            'mtime' => (int) filemtime($path),
            'publicPath' => $relative,
        ];
    }

    private function createRelativePublicPath(string $path): string
    {
        $projectDir = realpath($this->projectDir) ?: $this->projectDir;

        if (str_starts_with($path, $projectDir)) {
            $relative = substr($path, strlen($projectDir));
        } else {
            $relative = $path;
        }

        return '/' . ltrim($relative, '/');
    }
}
