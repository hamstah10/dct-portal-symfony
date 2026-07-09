<?php

namespace App\Controller\Api;

use App\Entity\Auftrag;
use App\Repository\AuftragRepository;
use App\Service\Realtime\PortalMercureTopics;
use App\Service\Realtime\PortalRealtimePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/job')]
final class AutoModJobApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly AuftragRepository $auftragRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly PortalRealtimePublisher $portalRealtimePublisher,
        private readonly PortalMercureTopics $portalMercureTopics,
        private readonly string $apiToken,
        private readonly string $projectDir,
        #[Autowire('%portal.mailer_from%')]
        private readonly string $fromAddress,
        #[Autowire('%portal.registration_notify_to%')]
        private readonly string $notifyTo,
    ) {
    }

    #[Route('', name: 'api_v1_job_get', methods: ['GET'])]
    public function getJob(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return new JsonResponse(['status' => false, 'message' => 'Unauthorized.'], 401);
        }

        $order = $this->auftragRepository->findNextAutoModJob();
        if (null === $order) {
            return new JsonResponse(['status' => true, 'order' => null]);
        }

        $relativePath = $this->ensureDecodedSourceFileOnDisk($order);
        if (null === $relativePath) {
            $this->logger->error('AutoMod job source file could not be materialized.', ['ulid' => $order->getUlid()]);

            return new JsonResponse(['status' => true, 'order' => null]);
        }

        return new JsonResponse([
            'status' => true,
            'order' => [
                'ulid' => $order->getUlid(),
                'file_name' => $order->getFileName(),
                'file' => $request->getSchemeAndHttpHost() . '/' . $relativePath,
                'auto_mod_status' => Auftrag::AUTOMOD_STATUS_DECRYPTED,
                'modification' => [
                    'name' => $order->getAutoModModificationName(),
                    'options' => $order->getAutoModModificationOptions(),
                ],
            ],
        ]);
    }

    #[Route('', name: 'api_v1_job_post', methods: ['POST'])]
    public function postJob(Request $request): JsonResponse
    {
        if (!$this->isAuthorized($request)) {
            return new JsonResponse(['status' => false, 'message' => 'Unauthorized.'], 401);
        }

        $ulid = trim((string) $request->request->get('ulid', ''));
        if ('' === $ulid) {
            return new JsonResponse(['status' => false, 'message' => 'ulid ist erforderlich.']);
        }

        $rawStatus = (string) $request->request->get('auto_mod_status', '');
        if (!in_array($rawStatus, ['3', '9'], true)) {
            return new JsonResponse(['status' => false, 'message' => 'auto_mod_status muss 3 oder 9 sein.']);
        }

        $order = $this->auftragRepository->findByUlid($ulid);
        if (null === $order) {
            return new JsonResponse(['status' => false, 'message' => 'Auftrag nicht gefunden.']);
        }

        $winolsId = trim((string) $request->request->get('winols_id', ''));
        if ('' !== $winolsId) {
            $order->setWinolsId($winolsId);
        }

        if ('9' === $rawStatus) {
            $order->setAutoModStatus(Auftrag::AUTOMOD_STATUS_FAILED);
            $order->setAutoModSubmittedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->notifyAdminOfFailure($order);
            $this->publishAutoModStatusEvent($order);

            return new JsonResponse(['status' => true, 'message' => 'Order updated successfully.']);
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');
        if (null === $file) {
            return new JsonResponse(['status' => false, 'message' => 'file ist erforderlich wenn auto_mod_status=3.']);
        }

        try {
            $this->storeResultFile($order, $file);
        } catch (\Throwable $e) {
            $this->logger->error('AutoMod result file storage failed.', ['exception' => $e, 'ulid' => $ulid]);

            return new JsonResponse(['status' => false, 'message' => 'Datei konnte nicht gespeichert werden.']);
        }

        $order->setAutoModStatus(Auftrag::AUTOMOD_STATUS_COMPLETED);
        $order->setAutoModSubmittedAt(new \DateTimeImmutable());
        $order->setStatus(Auftrag::STATUS_COMPLETED);
        $this->entityManager->flush();

        $this->publishAutoModStatusEvent($order);

        return new JsonResponse(['status' => true, 'message' => 'Order updated successfully.']);
    }

    private function publishAutoModStatusEvent(Auftrag $order): void
    {
        $kunde = $order->getKunde();
        if (null === $kunde || null === $kunde->getId()) {
            return;
        }

        $this->portalRealtimePublisher->publish(
            $this->portalMercureTopics->customerOrders((int) $kunde->getId()),
            sprintf('Auftrag %s AutoMod-Status aktualisiert', (string) $order->getOrderNumber()),
            [
                'kind' => 'order.status_changed',
                'orderId' => $order->getId(),
                'orderNumber' => $order->getOrderNumber(),
                'customerId' => $kunde->getId(),
                'status' => $order->getStatus(),
                'autoModStatus' => $order->getAutoModStatus(),
                'autoModFailureReason' => $order->getAutoModFailureReason(),
                'changedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
                'source' => 'automod',
            ],
        );
    }

    private function isAuthorized(Request $request): bool
    {
        $provided = $this->getBearerToken($request);

        return null !== $provided && '' !== trim($this->apiToken) && hash_equals($this->apiToken, $provided);
    }

    private function ensureDecodedSourceFileOnDisk(Auftrag $order): ?string
    {
        $ulid = $order->getUlid();
        if (null === $ulid || '' === $ulid) {
            return null;
        }

        $relative = sprintf('storage/tmp/decode/%s.bin', $ulid);
        $absolute = rtrim($this->projectDir, '/') . '/public/' . $relative;

        if (is_file($absolute)) {
            return $relative;
        }

        $decoded = base64_decode((string) $order->getFileData(), true);
        if (false === $decoded) {
            return null;
        }

        if (!is_dir(dirname($absolute)) && !@mkdir(dirname($absolute), 0775, true) && !is_dir(dirname($absolute))) {
            return null;
        }

        return @file_put_contents($absolute, $decoded) !== false ? $relative : null;
    }

    private function storeResultFile(Auftrag $order, UploadedFile $file): void
    {
        $orderId = $order->getId();
        if (null === $orderId) {
            throw new \RuntimeException('Auftrag hat keine ID.');
        }

        $targetName = $this->buildFinishedFileName((string) $order->getFileName());
        $targetName = str_replace("\0", '', $targetName);
        $targetName = trim(basename(str_replace('\\', '/', $targetName)));
        if ('' === $targetName) {
            $targetName = 'mod_file.bin';
        }

        $relativeDir = sprintf('uploads/auftraege/fertig/%d', $orderId);
        $absoluteDir = rtrim($this->projectDir, '/') . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new \RuntimeException('Zielverzeichnis konnte nicht erstellt werden.');
        }

        if (is_file($absoluteDir . '/' . $targetName)) {
            $timestamp = (new \DateTimeImmutable())->format('Ymd_His');
            $base = pathinfo($targetName, \PATHINFO_FILENAME);
            $ext = pathinfo($targetName, \PATHINFO_EXTENSION);
            $targetName = '' !== $ext ? sprintf('%s_%s.%s', $base, $timestamp, $ext) : sprintf('%s_%s', $base, $timestamp);
        }

        $file->move($absoluteDir, $targetName);

        $relativePath = $relativeDir . '/' . $targetName;
        $size = @filesize($absoluteDir . '/' . $targetName);
        $uploadedAt = new \DateTimeImmutable();

        $order
            ->setFinishedFilePath($relativePath)
            ->setFinishedFileName($targetName)
            ->setFinishedFileSize(is_int($size) ? $size : null)
            ->setFinishedFileUploadedAt($uploadedAt);

        $files = $order->getFinishedFiles();
        $files[] = [
            'path' => $relativePath,
            'name' => $targetName,
            'size' => is_int($size) ? $size : null,
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

    private function notifyAdminOfFailure(Auftrag $order): void
    {
        if ('' === trim($this->notifyTo)) {
            return;
        }

        try {
            $email = (new Email())
                ->from(new Address($this->fromAddress, 'AutoMod'))
                ->to($this->notifyTo)
                ->subject(sprintf('AutoMod fehlgeschlagen: %s', $order->getOrderNumber()))
                ->text(sprintf(
                    "Auftrag %s (ulid %s) ist bei der AutoMod-Verarbeitung fehlgeschlagen.",
                    $order->getOrderNumber(),
                    (string) $order->getUlid()
                ));
            $this->mailer->send($email);
        } catch (\Throwable $e) {
            $this->logger->error('AutoMod failure notification mail failed.', ['exception' => $e]);
        }
    }
}
