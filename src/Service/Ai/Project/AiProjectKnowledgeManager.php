<?php

namespace App\Service\Ai\Project;

use App\Entity\AiProject;
use App\Entity\AiProjectResource;
use App\Entity\MediaAsset;
use App\Repository\AiProjectResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\TextDocument;
use Symfony\AI\Store\IndexerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AiProjectKnowledgeManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AiProjectResourceRepository $resourceRepository,
        private readonly AiProjectDocumentExtractor $extractor,
        private readonly AiProjectVectorStore $vectorStore,
        #[Target('project_knowledge')] private readonly IndexerInterface $indexer,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    public function attachExistingMediaAsset(AiProject $project, MediaAsset $mediaAsset): AiProjectResource
    {
        $existing = $this->resourceRepository->findOneBy([
            'project' => $project,
            'mediaAsset' => $mediaAsset,
        ]);
        if ($existing instanceof AiProjectResource) {
            return $existing;
        }

        $resource = (new AiProjectResource())
            ->setProject($project)
            ->setMediaAsset($mediaAsset)
            ->setTitle($mediaAsset->getName())
            ->setFilePath($mediaAsset->getFilePath())
            ->setMediaType($mediaAsset->getMediaType())
            ->setMimeType($mediaAsset->getMimeType())
            ->setFileSize($mediaAsset->getFileSize())
            ->setIndexingStatus(AiProjectResource::STATUS_PENDING);

        $this->entityManager->persist($resource);
        $this->entityManager->flush();

        $this->reindexResource($resource);

        return $resource;
    }

    public function uploadAndAttach(AiProject $project, UploadedFile $uploadedFile): AiProjectResource
    {
        $mediaAsset = $this->createMediaAssetFromUpload($uploadedFile);

        return $this->attachExistingMediaAsset($project, $mediaAsset);
    }

    public function reindexResource(AiProjectResource $resource): void
    {
        $project = $resource->getProject();
        $resourceId = $resource->getId();
        if (!$project instanceof AiProject || null === $resourceId || null === $project->getId()) {
            throw new \RuntimeException('Projekt-Ressource ist nicht vollstaendig gespeichert.');
        }

        $this->vectorStore->removeByResource($project->getId(), $resourceId);

        try {
            if (!$resource->isSearchableDocument()) {
                throw new \RuntimeException('Dieser Ressourcentyp wird aktuell nicht indexiert.');
            }

            $text = $this->extractor->extract($resource);
            $metadata = new Metadata([
                'projectId' => $project->getId(),
                'resourceId' => $resourceId,
                'resourceTitle' => $resource->getTitle(),
                'mediaType' => $resource->getMediaType(),
                'mimeType' => $resource->getMimeType(),
                Metadata::KEY_SOURCE => (string) ($resource->getFilePath() ?? ''),
                Metadata::KEY_TEXT => $text,
            ]);

            $document = new TextDocument(
                sprintf('project-resource-%d', $resourceId),
                $text,
                $metadata,
            );

            $this->indexer->index($document, [
                'chunk_size' => 1200,
                'overlap' => 150,
            ]);

            $resource
                ->setExtractedText($text)
                ->setIndexingStatus(AiProjectResource::STATUS_INDEXED)
                ->setErrorMessage(null)
                ->setLastIndexedAt(new \DateTimeImmutable());
        } catch (\Throwable $exception) {
            $resource
                ->setExtractedText(null)
                ->setIndexingStatus($this->determineStatusForException($exception))
                ->setErrorMessage($exception->getMessage())
                ->setLastIndexedAt(new \DateTimeImmutable());
        }

        $this->entityManager->persist($resource);
        $this->entityManager->flush();
    }

    public function removeResource(AiProjectResource $resource): void
    {
        $projectId = $resource->getProject()?->getId();
        $resourceId = $resource->getId();
        if (null !== $projectId && null !== $resourceId) {
            $this->vectorStore->removeByResource($projectId, $resourceId);
        }

        $this->entityManager->remove($resource);
        $this->entityManager->flush();
    }

    private function createMediaAssetFromUpload(UploadedFile $uploadedFile): MediaAsset
    {
        $uploadDir = $this->projectDir.'/public/uploads/ai/project';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException('Upload-Verzeichnis konnte nicht erstellt werden.');
        }

        $extension = $uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension() ?: 'bin';
        $filename = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), $extension);
        $uploadedFile->move($uploadDir, $filename);

        $relativePath = 'uploads/ai/project/'.$filename;
        $mimeType = $uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();

        $asset = (new MediaAsset())
            ->setName(pathinfo((string) $uploadedFile->getClientOriginalName(), \PATHINFO_FILENAME) ?: $filename)
            ->setFilePath('/'.$relativePath)
            ->setMimeType((null !== $mimeType && '' !== trim($mimeType)) ? trim($mimeType) : null)
            ->setFileSize(\is_int($size) ? $size : null)
            ->setMediaType($this->detectMediaType($mimeType, $relativePath))
            ->setActive(true);

        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        return $asset;
    }

    private function detectMediaType(?string $mimeType, string $path): string
    {
        $normalizedMime = strtolower((string) $mimeType);
        $extension = strtolower((string) pathinfo($path, \PATHINFO_EXTENSION));

        if (\in_array($extension, ['svg', 'ico', 'icns'], true) || str_contains($normalizedMime, 'svg') || str_contains($normalizedMime, 'icon')) {
            return MediaAsset::TYPE_ICON;
        }
        if (str_starts_with($normalizedMime, 'image/')) {
            return MediaAsset::TYPE_IMAGE;
        }
        if (str_starts_with($normalizedMime, 'video/')) {
            return MediaAsset::TYPE_VIDEO;
        }
        if (\in_array($extension, ['glb', 'gltf'], true) || str_contains($normalizedMime, 'gltf')) {
            return MediaAsset::TYPE_MODEL;
        }
        if (str_starts_with($normalizedMime, 'text/') || str_contains($normalizedMime, 'pdf') || str_contains($normalizedMime, 'json') || str_contains($normalizedMime, 'xml')) {
            return MediaAsset::TYPE_DOCUMENT;
        }

        return MediaAsset::TYPE_FILE;
    }

    private function determineStatusForException(\Throwable $exception): string
    {
        $message = strtolower(trim($exception->getMessage()));

        foreach ([
            'wird aktuell nicht indexiert',
            'wird fuer die wissensbasis aktuell nicht extrahiert',
            'aus dem pdf konnte kein verwertbarer text extrahiert werden',
            'dateiformat wird fuer die wissensbasis aktuell nicht unterstuetzt',
        ] as $needle) {
            if (str_contains($message, $needle)) {
                return AiProjectResource::STATUS_UNSUPPORTED;
            }
        }

        return AiProjectResource::STATUS_ERROR;
    }
}
