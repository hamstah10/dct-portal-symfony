<?php

namespace App\Controller\Api;

use App\Entity\AdminIssue;
use App\Entity\AdminIssueComment;
use App\Entity\User;
use App\Repository\AdminIssueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/api/admin/issues')]
final class AdminIssueController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly AdminIssueRepository $issueRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'api_admin_issues_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $issues = $this->issueRepository->findBy([], ['createdAt' => 'DESC']);

        return new JsonResponse([
            'data' => array_map($this->normalizeIssue(...), $issues),
        ]);
    }

    #[Route('/{id}', name: 'api_admin_issues_get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $issue = $this->issueRepository->find($id);
        if (!$issue instanceof AdminIssue) {
            return $this->errorResponse('Issue nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->normalizeIssue($issue));
    }

    #[Route('', name: 'api_admin_issues_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return $this->errorResponse('Benutzer nicht authentifiziert.', Response::HTTP_UNAUTHORIZED);
        }

        try {
            $payload = $this->readJsonPayload($request);
            $issue = new AdminIssue();

            $issue->setTitle($payload['title'] ?? '');
            $issue->setDescription($payload['description'] ?? '');
            $issue->setCategory($payload['category'] ?? 'other');
            $issue->setPriority($payload['priority'] ?? 'normal');
            $issue->setLabels($payload['labels'] ?? []);
            $issue->setPageUrl($payload['pageUrl'] ?? null);
            $issue->setPageTitle($payload['pageTitle'] ?? null);
            $issue->setSelector($payload['selector'] ?? null);
            $issue->setEntityType($payload['entityType'] ?? null);
            $issue->setEntityId($payload['entityId'] ?? null);
            $issue->setEntityLabel($payload['entityLabel'] ?? null);
            $issue->setStageId($payload['stageId'] ?? null);
            $issue->setField($payload['field'] ?? null);
            $issue->setCurrentValue($payload['currentValue'] ?? null);
            $issue->setScreenshotDataUrl($payload['screenshotDataUrl'] ?? null);
            $issue->setAudioUrl($payload['audioUrl'] ?? null);

            $issue->setCreatedById($user->getId() ?? null);
            $issue->setCreatedByName($user->getUsername() ?? 'Unknown');

            $this->entityManager->persist($issue);
            $this->entityManager->flush();

            return new JsonResponse($this->normalizeIssue($issue), Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->errorResponse('Fehler beim Erstellen des Issues: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'api_admin_issues_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $issue = $this->issueRepository->find($id);
        if (!$issue instanceof AdminIssue) {
            return $this->errorResponse('Issue nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $this->readJsonPayload($request);

            if (isset($payload['title'])) {
                $issue->setTitle($payload['title']);
            }
            if (isset($payload['description'])) {
                $issue->setDescription($payload['description']);
            }
            if (isset($payload['status'])) {
                $issue->setStatus($payload['status']);
            }
            if (isset($payload['priority'])) {
                $issue->setPriority($payload['priority']);
            }
            if (isset($payload['category'])) {
                $issue->setCategory($payload['category']);
            }
            if (isset($payload['labels'])) {
                $issue->setLabels($payload['labels']);
            }

            $issue->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            return new JsonResponse($this->normalizeIssue($issue));
        } catch (\Exception $e) {
            return $this->errorResponse('Fehler beim Aktualisieren des Issues: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/comments', name: 'api_admin_issues_add_comment', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function addComment(Request $request, int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return $this->errorResponse('Benutzer nicht authentifiziert.', Response::HTTP_UNAUTHORIZED);
        }

        $issue = $this->issueRepository->find($id);
        if (!$issue instanceof AdminIssue) {
            return $this->errorResponse('Issue nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $this->readJsonPayload($request);
            $comment = new AdminIssueComment();
            $comment->setText($payload['text'] ?? '');
            $comment->setIssue($issue);
            $comment->setAuthorId($user->getId() ?? null);
            $comment->setAuthorName($user->getUsername() ?? 'Unknown');

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return new JsonResponse([
                'id' => $comment->getId(),
                'text' => $comment->getText(),
                'authorName' => $comment->getAuthorName() ?? 'Unknown',
                'createdAt' => $comment->getCreatedAt()->format('c'),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->errorResponse('Fehler beim Hinzufügen des Kommentars: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeIssue(AdminIssue $issue): array
    {
        return [
            'id' => $issue->getId(),
            'title' => $issue->getTitle(),
            'description' => $issue->getDescription(),
            'category' => $issue->getCategory(),
            'status' => $issue->getStatus(),
            'priority' => $issue->getPriority(),
            'labels' => $issue->getLabels(),
            'pageUrl' => $issue->getPageUrl(),
            'pageTitle' => $issue->getPageTitle(),
            'selector' => $issue->getSelector(),
            'entityType' => $issue->getEntityType(),
            'entityId' => $issue->getEntityId(),
            'entityLabel' => $issue->getEntityLabel(),
            'stageId' => $issue->getStageId(),
            'field' => $issue->getField(),
            'currentValue' => $issue->getCurrentValue(),
            'screenshotDataUrl' => $issue->getScreenshotDataUrl(),
            'audioUrl' => $issue->getAudioUrl(),
            'assignedToId' => $issue->getAssignedToId(),
            'assignedToName' => $issue->getAssignedToName(),
            'createdById' => $issue->getCreatedById(),
            'createdByName' => $issue->getCreatedByName(),
            'comments' => array_map(static function ($comment) {
                return [
                    'id' => $comment->getId(),
                    'text' => $comment->getText(),
                    'authorName' => $comment->getAuthorName() ?? 'Unknown',
                    'createdAt' => $comment->getCreatedAt()->format('c'),
                ];
            }, $issue->getComments()->toArray()),
            'createdAt' => $issue->getCreatedAt()->format('c'),
            'updatedAt' => $issue->getUpdatedAt()?->format('c'),
            'resolvedAt' => $issue->getResolvedAt()?->format('c'),
        ];
    }
}
