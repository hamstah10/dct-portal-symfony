<?php

namespace App\Service;

use App\Entity\SupportTicket;
use App\Entity\SupportTicketAttachment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SupportTicketFileService
{
    private const UPLOAD_DIR = 'public/uploads/tickets';
    private const MAX_FILE_SIZE = 52428800; // 50MB
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'mov', 'avi', 'bin', 'ori', 'mod', 'ecu', 'tuning', 'frf', 'hex', 'ols', 'slave', 'pdf', 'zip', 'rar', '7z', 'txt'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    public function uploadFile(SupportTicket $ticket, UploadedFile $file, ?string $description = null): SupportTicketAttachment
    {
        // Validierung
        $fileSize = $file->getSize();
        if ($fileSize > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException(sprintf('Datei zu groß. Maximum: %d MB', self::MAX_FILE_SIZE / 1024 / 1024));
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \InvalidArgumentException(sprintf('Dateityp nicht erlaubt. Erlaubte Typen: %s', implode(', ', self::ALLOWED_EXTENSIONS)));
        }

        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?? 'application/octet-stream';

        // Verzeichnis erstellen
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $uploadDir = $projectDir . '/' . sprintf(self::UPLOAD_DIR, $ticket->getId()) . '/' . $ticket->getId();
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        // Neue Dateiname mit Timestamp
        $newName = sprintf(
            '%d_%s_%s',
            time(),
            bin2hex(random_bytes(4)),
            preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName)
        );

        // Datei speichern - Symfony kümmert sich um alles
        try {
            $file->move($uploadDir, $newName);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Upload-Fehler: ' . $e->getMessage());
        }

        // Attachment Entity erstellen
        $attachment = (new SupportTicketAttachment())
            ->setTicket($ticket)
            ->setOriginalFilename($originalName)
            ->setFilename($newName)
            ->setMimeType($mimeType)
            ->setFilesize($fileSize)
            ->setDescription($description);

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        return $attachment;
    }

    public function deleteAttachment(SupportTicketAttachment $attachment): void
    {
        $filePath = sprintf('%s/%d/%s', self::UPLOAD_DIR, $attachment->getTicket()?->getId() ?? 0, $attachment->getFilename());
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->entityManager->remove($attachment);
        $this->entityManager->flush();
    }

    public function getFilePath(SupportTicketAttachment $attachment): string
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        return $projectDir . '/' . sprintf(self::UPLOAD_DIR, $attachment->getTicket()?->getId() ?? 0) . '/' . $attachment->getTicket()?->getId() . '/' . $attachment->getFilename();
    }

    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function isImageFile(SupportTicketAttachment $attachment): bool
    {
        return in_array(strtolower(pathinfo($attachment->getFilename(), PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    public function isVideoFile(SupportTicketAttachment $attachment): bool
    {
        return in_array(strtolower(pathinfo($attachment->getFilename(), PATHINFO_EXTENSION)), ['mp4', 'webm', 'mov', 'avi'], true);
    }
}
