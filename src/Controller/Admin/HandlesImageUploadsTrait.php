<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait HandlesImageUploadsTrait
{
    protected function storeSingleUpload(mixed $uploadedFile, string $relativeUploadDir): ?string
    {
        if (!$uploadedFile instanceof UploadedFile) {
            return null;
        }

        $absoluteUploadDir = $this->getParameter('kernel.project_dir') . '/public/' . $relativeUploadDir;
        if (!is_dir($absoluteUploadDir)) {
            mkdir($absoluteUploadDir, 0775, true);
        }

        $extension = $uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension() ?: 'bin';
        $fileName = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), strtolower($extension));
        $absoluteFilePath = $absoluteUploadDir . '/' . $fileName;
        $uploadedFile->move($absoluteUploadDir, $fileName);

        return $relativeUploadDir . '/' . $fileName;
    }

    protected function storeSingleUploadPreserveFileName(mixed $uploadedFile, string $relativeUploadDir): ?string
    {
        if (!$uploadedFile instanceof UploadedFile) {
            return null;
        }

        $absoluteUploadDir = $this->getParameter('kernel.project_dir') . '/public/' . $relativeUploadDir;
        if (!is_dir($absoluteUploadDir)) {
            mkdir($absoluteUploadDir, 0775, true);
        }

        $clientName = str_replace("\0", '', (string) $uploadedFile->getClientOriginalName());
        $clientName = str_replace('\\', '/', $clientName);
        $fileName = trim(basename($clientName));
        if ('' === $fileName) {
            return null;
        }

        $uploadedFile->move($absoluteUploadDir, $fileName);

        return $relativeUploadDir . '/' . $fileName;
    }

    /**
     * @param array<int, mixed> $uploadedFiles
     */
    protected function storeMultipleUploads(array $uploadedFiles, string $relativeUploadDir): array
    {
        $paths = [];
        foreach ($uploadedFiles as $uploadedFile) {
            $path = $this->storeSingleUpload($uploadedFile, $relativeUploadDir);
            if (null !== $path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @return array<int, string>
     */
    protected function toPathList(?string $value): array
    {
        if (null === $value || '' === trim($value)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(array_filter(array_map(static fn (string $line): string => trim($line), $lines)));
    }

    protected function renderSingleImagePreview(?string $value): string
    {
        if (null === $value || '' === trim($value)) {
            return '<span class="text-muted">Kein Bild</span>';
        }

        $url = '/' . ltrim(trim($value), '/');
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<a href="%1$s" data-lightbox-url="%1$s" data-lightbox-type="image" data-lightbox-caption="Bild Vorschau" title="Vorschau oeffnen" aria-label="Bild Vorschau oeffnen" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border:1px solid #d9e0ea;text-decoration:none;color:#0f172a;"><i class="fa fa-eye" aria-hidden="true"></i></a>',
            $escapedUrl
        );
    }

    protected function renderMultipleImagePreview(?string $value): string
    {
        $paths = $this->toPathList($value);
        if ([] === $paths) {
            return '<span class="text-muted">Keine Bilder</span>';
        }

        $items = [];
        foreach ($paths as $path) {
            $url = '/' . ltrim($path, '/');
            $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $escapedPath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
            $items[] = sprintf(
                '<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;"><img src="%s" alt="" style="width:46px;height:46px;object-fit:cover;border:1px solid #d5d9e0;border-radius:6px;"><code style="font-size:11px;word-break:break-all;">%s</code></div>',
                $escapedUrl,
                $escapedPath
            );
        }

        return implode('', $items);
    }

    protected function renderSingleFilePreview(?string $value, string $label = 'Datei'): string
    {
        if (null === $value || '' === trim($value)) {
            return sprintf('<span class="text-muted">Keine %s</span>', htmlspecialchars($label, ENT_QUOTES, 'UTF-8'));
        }

        $path = trim($value);
        $url = '/' . ltrim($path, '/');
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $escapedPath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
        $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $previewType = $this->resolvePreviewTypeFromPath($path);

        $fileLink = sprintf('<a href="%s" target="_blank" rel="noopener" title="Datei oeffnen" aria-label="Datei oeffnen" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border:1px solid #d9e0ea;text-decoration:none;color:#0f172a;"><i class="fa fa-eye" aria-hidden="true"></i></a>', $escapedUrl);

        if (null !== $previewType) {
            $escapedPreviewType = htmlspecialchars($previewType, ENT_QUOTES, 'UTF-8');
            $fileLink = sprintf(
                '<a href="%1$s" data-lightbox-url="%1$s" data-lightbox-type="%2$s" data-lightbox-caption="%3$s" title="Vorschau oeffnen" aria-label="Vorschau oeffnen" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border:1px solid #d9e0ea;text-decoration:none;color:#0f172a;"><i class="fa fa-eye" aria-hidden="true"></i></a>',
                $escapedUrl,
                $escapedPreviewType,
                $escapedLabel
            );
        }

        return sprintf(
            '<div style="display:flex;align-items:center;gap:8px;"><span style="font-size:11px;color:#6b7280;">%s</span>%s</div>',
            $escapedLabel,
            $fileLink
        );
    }

    private function resolvePreviewTypeFromPath(string $path): ?string
    {
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        if ('' === $extension) {
            return null;
        }

        if ('glb' === $extension) {
            return 'glb';
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'bmp'], true)) {
            return 'image';
        }

        return null;
    }
}
