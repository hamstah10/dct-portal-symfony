<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Auftrag;
use App\Entity\Portal;
use App\Entity\WinolsConfiguration;
use App\Repository\AuftragRepository;
use App\Repository\WinolsConfigurationRepository;
use App\Service\Realtime\PortalMercureTopics;
use App\Service\Realtime\PortalRealtimePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

#[Route('/api/v1/job')]
final class AutoModJobApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly AuftragRepository $auftragRepository,
        private readonly WinolsConfigurationRepository $winolsConfigurationRepository,
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly PortalRealtimePublisher $portalRealtimePublisher,
        private readonly PortalMercureTopics $portalMercureTopics,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        #[Autowire('%winols.api_token%')]
        private readonly string $apiToken,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    #[Route('', name: 'api_winols_job_get', methods: ['GET'])]
    public function getJob(Request $request): JsonResponse
    {
        $portal = $this->resolveActiveWinolsPortal($request);
        $configuration = $this->resolveWinolsConfiguration($portal);

        if (null !== ($error = $this->authorizeRequest($request, $configuration))) {
            return $error;
        }

        if (!$portal instanceof Portal || null === $portal->getFirma() || !$configuration instanceof WinolsConfiguration) {
            return $this->json([
                'status' => true,
                'order' => null,
            ]);
        }

        $order = null;
        foreach ($this->auftragRepository->findNextAutoModJobs($portal->getFirma(), 100) as $candidate) {
            if (!$candidate instanceof Auftrag) {
                continue;
            }

            if ($configuration->matchesOrder($candidate)) {
                $order = $candidate;
                break;
            }
        }

        if (!$order instanceof Auftrag) {
            return $this->json([
                'status' => true,
                'order' => null,
            ]);
        }

        if (null === $order->getUlid() || '' === trim((string) $order->getUlid())) {
            $order->setUlid((string) new Ulid());
            $this->entityManager->flush();
        }

        $fileUrl = $this->ensureDecodedSourceFileOnDisk($order);
        $absoluteFileUrl = rtrim($request->getSchemeAndHttpHost(), '/') . $fileUrl;

        return $this->json([
            'status' => true,
            'order' => [
                'ulid' => (string) $order->getUlid(),
                'file_name' => (string) $order->getFileName(),
                'file' => $absoluteFileUrl,
                'auto_mod_status' => (int) ($order->getAutoModStatus() ?? Auftrag::AUTOMOD_STATUS_DECRYPTED),
                'modification' => [
                    'name' => $order->getAutoModModificationName(),
                    'options' => $order->getAutoModModificationOptions(),
                ],
            ],
        ]);
    }

    #[Route('', name: 'api_winols_job_post', methods: ['POST'])]
    public function postJob(Request $request): JsonResponse
    {
        $portal = $this->resolveActiveWinolsPortal($request);
        $configuration = $this->resolveWinolsConfiguration($portal);

        if (null !== ($error = $this->authorizeRequest($request, $configuration))) {
            return $error;
        }

        $ulid = trim((string) $request->request->get('ulid', ''));
        $statusValue = (int) $request->request->get('auto_mod_status', 0);
        $winolsId = trim((string) $request->request->get('winols_id', ''));
        $resultFile = $request->files->get('file');

        if ('' === $ulid) {
            return $this->errorResponse('ulid fehlt.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = $this->auftragRepository->findByUlid($ulid);
        if (!$order instanceof Auftrag) {
            return $this->errorResponse('Auftrag nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        if (9 === $statusValue) {
            $order
                ->setAutoModStatus(Auftrag::AUTOMOD_STATUS_FAILED)
                ->setAutoModFailureReason('WinOLS hat die Verarbeitung als fehlgeschlagen gemeldet.')
                ->setAutoModSubmittedAt(new \DateTimeImmutable())
                ->setWinolsId('' !== $winolsId ? $winolsId : $order->getWinolsId());

            $this->entityManager->flush();
            $this->publishOrderStatusChange($order, 'WinOLS-Verarbeitung fehlgeschlagen.');

            return $this->json([
                'status' => true,
                'message' => 'Order updated successfully.',
            ]);
        }

        if (3 !== $statusValue) {
            return $this->errorResponse('auto_mod_status muss 3 oder 9 sein.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$resultFile instanceof UploadedFile) {
            return $this->errorResponse('file fehlt.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->storeResultFile($order, $resultFile);
        $order
            ->setAutoModStatus(Auftrag::AUTOMOD_STATUS_COMPLETED)
            ->setAutoModFailureReason(null)
            ->setAutoModSubmittedAt(new \DateTimeImmutable())
            ->setWinolsId('' !== $winolsId ? $winolsId : $order->getWinolsId());

        $this->entityManager->flush();
        $this->publishOrderStatusChange($order, 'WinOLS-Verarbeitung abgeschlossen.');

        return $this->json([
            'status' => true,
            'message' => 'Order updated successfully.',
        ]);
    }

    private function authorizeRequest(Request $request, ?WinolsConfiguration $configuration): ?JsonResponse
    {
        $bearerToken = $this->getBearerToken($request);
        $expectedToken = null !== $configuration && '' !== trim($configuration->getApiToken())
            ? $configuration->getApiToken()
            : $this->apiToken;

        if (null === $bearerToken || '' === trim($expectedToken) || !hash_equals($expectedToken, $bearerToken)) {
            return $this->errorResponse('Unauthorized.', Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }

    private function resolveWinolsConfiguration(?Portal $portal): ?WinolsConfiguration
    {
        if (!$portal instanceof Portal) {
            return null;
        }

        return $this->winolsConfigurationRepository->findActiveForPortal($portal);
    }

    private function resolveActiveWinolsPortal(Request $request): ?Portal
    {
        $portal = $this->portalRuntimeResolver->resolveCurrentPortal($request);
        if (!$portal instanceof Portal) {
            return null;
        }

        if (!$portal->isActive() || !$portal->isPortalEnabled() || !$portal->isWinolsEnabled()) {
            return null;
        }

        return $portal;
    }

    private function ensureDecodedSourceFileOnDisk(Auftrag $order): string
    {
        $ulid = trim((string) $order->getUlid());
        if ('' === $ulid) {
            $ulid = (string) new Ulid();
            $order->setUlid($ulid);
        }

        $relativePath = sprintf('storage/tmp/decode/%s.bin', $ulid);
        $absolutePath = rtrim($this->projectDir, '/') . '/public/' . $relativePath;
        $directory = \dirname($absolutePath);

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Decode-Verzeichnis konnte nicht erstellt werden.');
        }

        if (!is_file($absolutePath)) {
            $decoded = base64_decode((string) ($order->getFileData() ?? ''), true);
            if (false === $decoded) {
                $decoded = (string) ($order->getFileData() ?? '');
            }

            if (false === @file_put_contents($absolutePath, $decoded)) {
                throw new \RuntimeException('Decode-Datei konnte nicht geschrieben werden.');
            }
        }

        return '/' . $relativePath;
    }

    private function storeResultFile(Auftrag $order, UploadedFile $file): void
    {
        $orderId = $order->getId();
        if (null === $orderId) {
            throw new \RuntimeException('Auftrag ohne ID kann nicht gespeichert werden.');
        }

        $relativeDir = sprintf('uploads/auftraege/fertig/%d', $orderId);
        $absoluteDir = rtrim($this->projectDir, '/') . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new \RuntimeException('Zielverzeichnis fuer Ergebnisdatei konnte nicht erstellt werden.');
        }

        $targetName = $this->buildFinishedFileName((string) $order->getFileName());
        $targetName = basename(str_replace('\\', '/', str_replace("\0", '', $targetName)));
        if ('' === $targetName) {
            $targetName = 'mod_file.bin';
        }

        try {
            $storedFile = $file->move($absoluteDir, $targetName);
            $absolutePath = $storedFile->getPathname();
        } catch (\Throwable $exception) {
            $contents = file_get_contents($file->getPathname());
            if (false === $contents || false === @file_put_contents($absoluteDir . '/' . $targetName, $contents)) {
                throw new \RuntimeException('Ergebnisdatei konnte nicht gespeichert werden.', 0, $exception);
            }

            $absolutePath = $absoluteDir . '/' . $targetName;
        }

        $relativePath = $relativeDir . '/' . $targetName;
        $fileSize = @filesize($absolutePath);
        $uploadedAt = new \DateTimeImmutable();

        $order
            ->setFinishedFilePath($relativePath)
            ->setFinishedFileName($targetName)
            ->setFinishedFileSize(is_int($fileSize) ? $fileSize : null)
            ->setFinishedFileUploadedAt($uploadedAt)
            ->setStatus(Auftrag::STATUS_COMPLETED);

        $files = $order->getFinishedFiles();
        $files[] = [
            'path' => $relativePath,
            'name' => $targetName,
            'size' => is_int($fileSize) ? $fileSize : null,
            'uploadedAt' => $uploadedAt->format(\DateTimeInterface::ATOM),
        ];
        $order->setFinishedFiles($files);
    }

    private function buildFinishedFileName(string $originalFileName): string
    {
        $trimmed = trim($originalFileName);
        if ('' === $trimmed) {
            return 'mod_file.bin';
        }

        $extension = pathinfo($trimmed, \PATHINFO_EXTENSION);
        $base = pathinfo($trimmed, \PATHINFO_FILENAME);
        $base = '' !== trim((string) $base) ? (string) $base : $trimmed;

        if ('' === trim((string) $extension)) {
            return sprintf('%s_mod', $base);
        }

        return sprintf('%s_mod.%s', $base, $extension);
    }

    private function publishOrderStatusChange(Auftrag $order, string $summary): void
    {
        $kunde = $order->getKunde();
        if (null === $kunde || null === $kunde->getId()) {
            return;
        }

        $this->portalRealtimePublisher->publish(
            $this->portalMercureTopics->customerOrders((int) $kunde->getId()),
            $summary,
            [
                'kind' => 'order.status_changed',
                'orderId' => $order->getId(),
                'orderNumber' => $order->getOrderNumber(),
                'customerId' => $kunde->getId(),
                'status' => $order->getStatus(),
                'autoModStatus' => $order->getAutoModStatus(),
                'winolsId' => $order->getWinolsId(),
            ],
        );
    }
}
