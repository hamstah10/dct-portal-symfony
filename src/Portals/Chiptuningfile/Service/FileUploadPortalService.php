<?php

namespace App\Portals\Chiptuningfile\Service;

use App\Entity\Kunde;
use App\Portals\Chiptuningfile\Entity\PortalUploadJob;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

final class FileUploadPortalService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SluggerInterface $slugger,
        #[Autowire('%portal.upload_dir%')]
        private readonly string $uploadDir,
    ) {
    }

    public function createUploadJob(Kunde $customer, UploadedFile $file, ?string $notes = null): PortalUploadJob
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = $this->slugger->slug($originalName)->lower()->toString();
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $storedFilename = sprintf('%s-%s.%s', $safeName, bin2hex(random_bytes(6)), $extension);

        if (!is_dir($this->uploadDir) && !mkdir($concurrentDirectory = $this->uploadDir, 0775, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Upload directory "%s" could not be created.', $this->uploadDir));
        }

        try {
            $file->move($this->uploadDir, $storedFilename);
        } catch (FileException $exception) {
            throw new \RuntimeException('Datei konnte nicht gespeichert werden.', 0, $exception);
        }

        $job = (new PortalUploadJob())
            ->setKunde($customer)
            ->setOriginalFilename((string) $file->getClientOriginalName())
            ->setStoredFilename($storedFilename)
            ->setMimeType($file->getClientMimeType())
            ->setSizeBytes((int) $file->getSize())
            ->setNotes($notes);

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        return $job;
    }
}
