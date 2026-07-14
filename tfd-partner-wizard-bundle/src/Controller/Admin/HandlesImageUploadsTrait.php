<?php

namespace Tfd\PartnerWizardBundle\Controller\Admin;

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
        $uploadedFile->move($absoluteUploadDir, $fileName);

        return $relativeUploadDir . '/' . $fileName;
    }

    protected function renderSingleImagePreview(?string $value): string
    {
        if (null === $value || '' === trim($value)) {
            return '<span class="text-muted">Kein Bild</span>';
        }

        $url = '/' . ltrim(trim($value), '/');
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<a href="%1$s" target="_blank" rel="noopener" title="Vorschau oeffnen" aria-label="Bild Vorschau oeffnen" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border:1px solid #d9e0ea;text-decoration:none;color:#0f172a;"><i class="fa fa-eye" aria-hidden="true"></i></a>',
            $escapedUrl
        );
    }
}
