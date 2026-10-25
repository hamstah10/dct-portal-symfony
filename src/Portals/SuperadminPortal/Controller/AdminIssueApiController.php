<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use App\Entity\SuperadminSetting;
use App\Repository\SuperadminSettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class AdminIssueApiController extends AbstractController
{
    private const SETTING_KEY = 'adminIssueReporter.issues';

    public function __construct(
        private readonly SuperadminSettingRepository $settingRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/admin/issues', name: 'superadmin_portal_admin_issues_list', methods: ['GET'])]
    public function list(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertSuperadminPortal($request, $portalRuntimeResolver);

        return $this->json($this->loadIssues());
    }

    #[Route('/api/admin/issues', name: 'superadmin_portal_admin_issues_create', methods: ['POST'])]
    public function create(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertSuperadminPortal($request, $portalRuntimeResolver);

        $body = $this->readJsonBody($request);
        $title = trim((string) ($body['title'] ?? ''));
        $description = trim((string) ($body['description'] ?? ''));
        if ('' === $title || '' === $description) {
            return $this->json(['message' => 'Titel und Beschreibung sind erforderlich.'], 400);
        }

        $now = (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM);
        $issue = $this->normalizeIssue([
            ...$body,
            'id' => $this->createIssueId(),
            'status' => $this->validStatus($body['status'] ?? null) ?? 'open',
            'comments' => $this->normalizeComments($body['comments'] ?? []),
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);

        $issues = $this->loadIssues();
        array_unshift($issues, $issue);
        $this->saveIssues($issues);

        return $this->json($issue, 201);
    }

    #[Route('/api/admin/issues/{id}', name: 'superadmin_portal_admin_issues_update', requirements: ['id' => '[A-Za-z0-9._:-]+'], methods: ['PUT'])]
    public function update(string $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertSuperadminPortal($request, $portalRuntimeResolver);

        $issues = $this->loadIssues();
        $index = $this->findIssueIndex($issues, $id);
        if (null === $index) {
            return $this->json(['message' => 'Meldung nicht gefunden.'], 404);
        }

        $body = $this->readJsonBody($request);
        $updated = $this->normalizeIssue([
            ...$issues[$index],
            ...$body,
            'id' => $id,
            'comments' => $this->normalizeComments($body['comments'] ?? $issues[$index]['comments'] ?? []),
            'updatedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ]);

        $issues[$index] = $updated;
        $this->saveIssues($issues);

        return $this->json($updated);
    }

    #[Route('/api/admin/issues/{id}/comments', name: 'superadmin_portal_admin_issues_comment', requirements: ['id' => '[A-Za-z0-9._:-]+'], methods: ['POST'])]
    public function comment(string $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertSuperadminPortal($request, $portalRuntimeResolver);

        $issues = $this->loadIssues();
        $index = $this->findIssueIndex($issues, $id);
        if (null === $index) {
            return $this->json(['message' => 'Meldung nicht gefunden.'], 404);
        }

        $body = $this->readJsonBody($request);
        $text = trim((string) ($body['text'] ?? ''));
        if ('' === $text) {
            return $this->json(['message' => 'Kommentar darf nicht leer sein.'], 400);
        }

        $now = (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM);
        $comments = $this->normalizeComments($issues[$index]['comments'] ?? []);
        $comments[] = [
            'id' => $this->createIssueId(),
            'authorId' => $this->nullableString($body['authorId'] ?? null),
            'authorName' => trim((string) ($body['authorName'] ?? 'Admin')) ?: 'Admin',
            'text' => $text,
            'createdAt' => $now,
        ];

        $issues[$index] = $this->normalizeIssue([
            ...$issues[$index],
            'comments' => $comments,
            'updatedAt' => $now,
        ]);
        $this->saveIssues($issues);

        return $this->json($issues[$index]);
    }

    private function assertSuperadminPortal(Request $request, PortalRuntimeResolver $portalRuntimeResolver): void
    {
        $systemType = (string) $request->attributes->get('_brand_system_type', '');
        if (SystemType::SUPERADMIN === $systemType) {
            return;
        }

        $portal = $portalRuntimeResolver->requirePortalSystem($request);
        if ($portal->getPortalType() !== Portal::TYPE_SUPERADMIN_PORTAL) {
            throw new NotFoundHttpException('Admin issue reporter is not available for this portal.');
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadIssues(): array
    {
        $setting = $this->settingRepository->findOneBy(['settingKey' => self::SETTING_KEY]);
        $value = $setting instanceof SuperadminSetting ? $setting->getSettingValue() : [];
        if (!is_array($value)) {
            return [];
        }

        $issues = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $issues[] = $this->normalizeIssue($item);
            }
        }

        return $issues;
    }

    /**
     * @param list<array<string, mixed>> $issues
     */
    private function saveIssues(array $issues): void
    {
        $setting = $this->settingRepository->findOneBy(['settingKey' => self::SETTING_KEY]);
        if (!$setting instanceof SuperadminSetting) {
            $setting = (new SuperadminSetting())->setSettingKey(self::SETTING_KEY);
            $this->entityManager->persist($setting);
        }

        $setting->setSettingValue(array_slice(array_values($issues), 0, 500));
        $this->entityManager->flush();
    }

    /**
     * @param list<array<string, mixed>> $issues
     */
    private function findIssueIndex(array $issues, string $id): ?int
    {
        foreach ($issues as $index => $issue) {
            if ((string) ($issue['id'] ?? '') === $id) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function readJsonBody(Request $request): array
    {
        $body = json_decode((string) $request->getContent(), true);

        return is_array($body) ? $body : [];
    }

    /**
     * @param array<string, mixed> $issue
     *
     * @return array<string, mixed>
     */
    private function normalizeIssue(array $issue): array
    {
        $createdBy = is_array($issue['createdBy'] ?? null) ? $issue['createdBy'] : [];
        $assignedTo = is_array($issue['assignedTo'] ?? null) ? $issue['assignedTo'] : null;

        return [
            'id' => trim((string) ($issue['id'] ?? $this->createIssueId())),
            'title' => trim((string) ($issue['title'] ?? '')),
            'description' => trim((string) ($issue['description'] ?? '')),
            'category' => $this->validCategory($issue['category'] ?? null) ?? 'other',
            'status' => $this->validStatus($issue['status'] ?? null) ?? 'open',
            'priority' => $this->validPriority($issue['priority'] ?? null) ?? 'normal',
            'labels' => $this->normalizeStringList($issue['labels'] ?? []),
            'assignedTo' => null !== $assignedTo ? $this->normalizeUser($assignedTo) : null,
            'pageUrl' => trim((string) ($issue['pageUrl'] ?? '')),
            'pageTitle' => $this->nullableString($issue['pageTitle'] ?? null),
            'screenshotDataUrl' => $this->nullableString($issue['screenshotDataUrl'] ?? null),
            'audioUrl' => $this->nullableString($issue['audioUrl'] ?? null),
            'comments' => $this->normalizeComments($issue['comments'] ?? []),
            'createdAt' => $this->validDate($issue['createdAt'] ?? null) ?? (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'createdBy' => $this->normalizeUser($createdBy),
            'updatedAt' => $this->validDate($issue['updatedAt'] ?? null),
            'resolvedAt' => $this->validDate($issue['resolvedAt'] ?? null),
            'selector' => $this->nullableString($issue['selector'] ?? null),
            'entityType' => $this->nullableString($issue['entityType'] ?? null),
            'entityId' => $this->nullableString($issue['entityId'] ?? null),
            'entityLabel' => $this->nullableString($issue['entityLabel'] ?? null),
            'vehicleId' => $this->nullableString($issue['vehicleId'] ?? null),
            'engineId' => $this->nullableString($issue['engineId'] ?? null),
            'stageId' => $this->nullableString($issue['stageId'] ?? null),
            'field' => $this->nullableString($issue['field'] ?? null),
            'currentValue' => $this->nullableString($issue['currentValue'] ?? null),
        ];
    }

    /**
     * @param mixed $comments
     *
     * @return list<array<string, mixed>>
     */
    private function normalizeComments(mixed $comments): array
    {
        if (!is_array($comments)) {
            return [];
        }

        $normalized = [];
        foreach ($comments as $comment) {
            if (!is_array($comment)) {
                continue;
            }
            $text = trim((string) ($comment['text'] ?? ''));
            if ('' === $text) {
                continue;
            }
            $normalized[] = [
                'id' => trim((string) ($comment['id'] ?? $this->createIssueId())),
                'authorId' => $this->nullableString($comment['authorId'] ?? null),
                'authorName' => trim((string) ($comment['authorName'] ?? 'Admin')) ?: 'Admin',
                'text' => $text,
                'createdAt' => $this->validDate($comment['createdAt'] ?? null) ?? (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ];
        }

        return $normalized;
    }

    /**
     * @param mixed $items
     *
     * @return list<string>
     */
    private function normalizeStringList(mixed $items): array
    {
        if (!is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => trim((string) $item),
            $items
        ), static fn (string $item): bool => '' !== $item));
    }

    /**
     * @param array<string, mixed> $user
     *
     * @return array{id: string, name: string}
     */
    private function normalizeUser(array $user): array
    {
        $name = trim((string) ($user['name'] ?? 'Admin'));

        return [
            'id' => trim((string) ($user['id'] ?? '1')) ?: '1',
            'name' => '' !== $name ? $name : 'Admin',
        ];
    }

    private function validStatus(mixed $status): ?string
    {
        $status = trim((string) ($status ?? ''));

        return in_array($status, ['open', 'in_progress', 'resolved', 'rejected'], true) ? $status : null;
    }

    private function validPriority(mixed $priority): ?string
    {
        $priority = trim((string) ($priority ?? ''));

        return in_array($priority, ['low', 'normal', 'high', 'critical'], true) ? $priority : null;
    }

    private function validCategory(mixed $category): ?string
    {
        $category = trim((string) ($category ?? ''));

        return in_array($category, ['incorrect_data', 'missing_data', 'translation', 'layout', 'technical_error', 'other'], true) ? $category : null;
    }

    private function validDate(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ('' === $value) {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->format(\DateTimeInterface::ATOM);
        } catch (\Throwable) {
            return null;
        }
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return '' !== $value ? $value : null;
    }

    private function createIssueId(): string
    {
        return bin2hex(random_bytes(8));
    }
}
