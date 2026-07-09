<?php

namespace App\Service\Ai\Project;

use App\Entity\AiProjectResource;
use App\Entity\MediaAsset;
use Symfony\AI\Platform\Message\Content\Document;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class AiProjectDocumentExtractor
{
    private const PDF_EXTRACTION_MODEL = 'gpt-5-mini';
    private const PDF_EXTRACTION_FAILURE_MARKER = 'PDF_TEXT_EXTRACTION_FAILED';

    public function __construct(
        private readonly PlatformInterface $platform,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        #[Autowire(service: 'ai_project.document_extraction.cache')] private readonly CacheInterface $pdfExtractionCache,
    ) {
    }

    public function extract(AiProjectResource $resource): string
    {
        $relativePath = trim((string) ($resource->getFilePath() ?? ''));
        if ('' === $relativePath) {
            throw new \RuntimeException('Keine Datei fuer die Ressource hinterlegt.');
        }

        $absolutePath = $this->projectDir.'/public/'.ltrim($relativePath, '/');
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            throw new \RuntimeException('Datei nicht gefunden oder nicht lesbar.');
        }

        $mimeType = strtolower((string) ($resource->getMimeType() ?? ''));
        $extension = strtolower((string) pathinfo($absolutePath, \PATHINFO_EXTENSION));
        $mediaType = $resource->getMediaType();

        if (MediaAsset::TYPE_IMAGE === $mediaType || MediaAsset::TYPE_MODEL === $mediaType || MediaAsset::TYPE_VIDEO === $mediaType) {
            throw new \RuntimeException('Dieser Dateityp wird fuer die Wissensbasis aktuell nicht extrahiert.');
        }

        if ('pdf' === $extension || str_contains($mimeType, 'pdf')) {
            $text = $this->extractPdf($absolutePath);

            if (\strlen($text) > 200000) {
                $text = substr($text, 0, 200000);
            }

            return $text;
        }

        $raw = file_get_contents($absolutePath);
        if (false === $raw) {
            throw new \RuntimeException('Dateiinhalt konnte nicht gelesen werden.');
        }

        $text = match (true) {
            str_contains($mimeType, 'json') || 'json' === $extension => $this->extractJson($raw),
            str_contains($mimeType, 'xml') || \in_array($extension, ['xml', 'xsd'], true) => $this->extractXml($raw),
            str_contains($mimeType, 'html') || \in_array($extension, ['html', 'htm', 'xhtml'], true) => $this->extractHtml($raw),
            str_starts_with($mimeType, 'text/') || \in_array($extension, ['txt', 'md', 'markdown', 'csv', 'log', 'yaml', 'yml'], true) => $this->normalizeText($raw),
            default => throw new \RuntimeException('Dateiformat wird fuer die Wissensbasis aktuell nicht unterstuetzt.'),
        };

        $text = trim($text);
        if ('' === $text) {
            throw new \RuntimeException('Aus der Datei konnte kein Text extrahiert werden.');
        }

        if (\strlen($text) > 200000) {
            $text = substr($text, 0, 200000);
        }

        return $text;
    }

    private function extractJson(string $raw): string
    {
        $decoded = json_decode($raw, true);
        if (!\is_array($decoded)) {
            return $this->normalizeText($raw);
        }

        return $this->normalizeText((string) json_encode($decoded, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES));
    }

    private function extractXml(string $raw): string
    {
        return $this->normalizeText(strip_tags($raw));
    }

    private function extractHtml(string $raw): string
    {
        return $this->normalizeText(html_entity_decode(strip_tags($raw), \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8'));
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function extractPdf(string $absolutePath): string
    {
        $cacheKey = $this->buildPdfCacheKey($absolutePath);

        return $this->pdfExtractionCache->get($cacheKey, function (ItemInterface $item) use ($absolutePath): string {
            $item->expiresAfter(60 * 60 * 24 * 30);

            try {
                $messages = new MessageBag(
                    Message::forSystem('Du extrahierst den inhaltlich relevanten Text aus PDFs fuer eine Vektor-Wissensbasis. Gib nur bereinigten Klartext zurueck, keine Einleitung, kein Markdown-Codeblock. Erhalte Ueberschriften, Listen, Tabellenwerte, Nummern und wichtige Formulierungen. Lass reine Layout-Artefakte, doppelte Kopf-/Fusszeilen und offensichtliches Navigationsrauschen weg.'),
                    Message::ofUser(
                        'Lies dieses PDF und gib den semantisch relevanten Inhalt als Klartext fuer die Wissensbasis zurueck. Wenn kein sinnvoller Text lesbar ist, antworte exakt mit '.self::PDF_EXTRACTION_FAILURE_MARKER.'.',
                        Document::fromFile($absolutePath),
                    ),
                );

                $text = trim($this->platform->invoke(self::PDF_EXTRACTION_MODEL, $messages)->asText());
            } catch (\Throwable $exception) {
                $message = trim($exception->getMessage());
                if (str_contains(strtolower($message), 'idle timeout')) {
                    throw new \RuntimeException('PDF-Verarbeitung hat das OpenAI-Zeitlimit erreicht. Bitte erneut versuchen. Falls das weiterhin passiert, PDF in kleinere Teilbereiche aufteilen.', previous: $exception);
                }

                throw new \RuntimeException('PDF konnte ueber Symfony AI nicht gelesen werden: '.$message, previous: $exception);
            }

            if ('' === $text || self::PDF_EXTRACTION_FAILURE_MARKER === trim($text)) {
                throw new \RuntimeException('Aus dem PDF konnte kein verwertbarer Text extrahiert werden.');
            }

            return $this->normalizeText($text);
        });
    }

    private function buildPdfCacheKey(string $absolutePath): string
    {
        $fileSize = @filesize($absolutePath);
        $modifiedAt = @filemtime($absolutePath);

        return 'ai_project_pdf_'.hash('sha256', implode('|', [
            $absolutePath,
            false !== $fileSize ? (string) $fileSize : '0',
            false !== $modifiedAt ? (string) $modifiedAt : '0',
        ]));
    }
}
