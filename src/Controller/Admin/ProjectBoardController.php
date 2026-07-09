<?php

namespace App\Controller\Admin;

use App\Entity\ProjectIdeaCard;
use App\Entity\ProjectTodoCard;
use App\Repository\ProjectIdeaCardRepository;
use App\Repository\ProjectTodoCardRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProjectBoardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProjectTodoCardRepository $todoRepository,
        private readonly ProjectIdeaCardRepository $ideaRepository,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[AdminRoute(path: '/projekte/todo', name: 'projects_todo', options: ['methods' => ['GET', 'POST']])]
    public function todo(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleTodoMutation($request);
        }

        return $this->renderBoard(
            'todo',
            'ToDo Board',
            'Arbeite Aufgaben als Karten im Kanban-Flow ab.',
            ProjectTodoCard::getStatusChoices(),
            $this->buildColumns($this->todoRepository, ProjectTodoCard::getStatusChoices()),
        );
    }

    #[AdminRoute(path: '/projekte/ideen', name: 'projects_ideas', options: ['methods' => ['GET', 'POST']])]
    public function ideas(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleIdeaMutation($request);
        }

        return $this->renderBoard(
            'ideas',
            'Ideen Board',
            'Sammle, bewerte und plane Ideen als Karten im Kanban-Board.',
            ProjectIdeaCard::getStatusChoices(),
            $this->buildColumns($this->ideaRepository, ProjectIdeaCard::getStatusChoices()),
        );
    }

    #[AdminRoute(path: '/projekte/todo/{id}/move', name: 'projects_todo_move', options: ['requirements' => ['id' => '\d+'], 'methods' => ['POST']])]
    public function moveTodo(ProjectTodoCard $card, Request $request): JsonResponse
    {
        return $this->syncBoard(
            $request,
            ProjectTodoCard::getStatusChoices(),
            ProjectTodoCard::class,
            'project_board_todo_move',
        );
    }

    #[AdminRoute(path: '/projekte/ideen/{id}/move', name: 'projects_ideas_move', options: ['requirements' => ['id' => '\d+'], 'methods' => ['POST']])]
    public function moveIdea(ProjectIdeaCard $card, Request $request): JsonResponse
    {
        return $this->syncBoard(
            $request,
            ProjectIdeaCard::getStatusChoices(),
            ProjectIdeaCard::class,
            'project_board_ideas_move',
        );
    }

    private function handleTodoMutation(Request $request): RedirectResponse
    {
        $action = trim((string) $request->request->get('_action', ''));

        if ('create' === $action) {
            if (!$this->isCsrfTokenValid('project_board_todo_create', (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_projects_todo');
            }

            $title = trim((string) $request->request->get('title', ''));
            if ('' === $title) {
                $this->addFlash('warning', 'Bitte einen Titel fuer die ToDo-Karte angeben.');

                return $this->redirectToRoute('admin_projects_todo');
            }

            $status = (string) $request->request->get('status', ProjectTodoCard::STATUS_BACKLOG);
            $card = (new ProjectTodoCard())
                ->setTitle($title)
                ->setDescription($request->request->get('description'))
                ->setTaskCode($request->request->get('taskCode'))
                ->setAssignedTo($request->request->get('assignedTo'))
                ->setTargetDate($this->parseTargetDate($request->request->get('targetDate')))
                ->setTags($this->parseTags($request->request->get('tags')))
                ->setImages($this->storeUploadedImages($request->files->all('images')))
                ->setStatus($status)
                ->setPosition($this->todoRepository->nextPositionForStatus($status))
                ->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($card);
            $this->entityManager->flush();
            $this->addFlash('success', 'ToDo-Karte wurde erstellt.');

            return $this->redirectToRoute('admin_projects_todo');
        }

        $cardId = (int) $request->request->get('card_id', 0);
        $card = $cardId > 0 ? $this->todoRepository->find($cardId) : null;
        if (!$card instanceof ProjectTodoCard) {
            $this->addFlash('warning', 'ToDo-Karte wurde nicht gefunden.');

            return $this->redirectToRoute('admin_projects_todo');
        }

        if ('update' === $action) {
            if (!$this->isCsrfTokenValid('project_board_todo_update_'.$card->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_projects_todo');
            }

            $title = trim((string) $request->request->get('title', ''));
            if ('' === $title) {
                $this->addFlash('warning', 'Bitte einen Titel fuer die ToDo-Karte angeben.');

                return $this->redirectToRoute('admin_projects_todo');
            }

            $targetStatus = (string) $request->request->get('status', $card->getStatus());
            if ($targetStatus !== $card->getStatus()) {
                $card->setPosition($this->todoRepository->nextPositionForStatus($targetStatus));
            }

            $card
                ->setTitle($title)
                ->setDescription($request->request->get('description'))
                ->setTaskCode($request->request->get('taskCode'))
                ->setAssignedTo($request->request->get('assignedTo'))
                ->setTargetDate($this->parseTargetDate($request->request->get('targetDate')))
                ->setTags($this->parseTags($request->request->get('tags')))
                ->setImages(array_values(array_merge(
                    $card->getImages(),
                    $this->storeUploadedImages($request->files->all('images'))
                )))
                ->setStatus($targetStatus)
                ->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->flush();
            $this->addFlash('success', 'ToDo-Karte wurde aktualisiert.');

            return $this->redirectToRoute('admin_projects_todo');
        }

        if ('delete' === $action) {
            if (!$this->isCsrfTokenValid('project_board_todo_delete_'.$card->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_projects_todo');
            }

            $this->entityManager->remove($card);
            $this->entityManager->flush();
            $this->addFlash('success', 'ToDo-Karte wurde geloescht.');
        }

        return $this->redirectToRoute('admin_projects_todo');
    }

    private function handleIdeaMutation(Request $request): RedirectResponse
    {
        $action = trim((string) $request->request->get('_action', ''));

        if ('create' === $action) {
            if (!$this->isCsrfTokenValid('project_board_ideas_create', (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_projects_ideas');
            }

            $title = trim((string) $request->request->get('title', ''));
            if ('' === $title) {
                $this->addFlash('warning', 'Bitte einen Titel fuer die Ideen-Karte angeben.');

                return $this->redirectToRoute('admin_projects_ideas');
            }

            $status = (string) $request->request->get('status', ProjectIdeaCard::STATUS_NEW);
            $card = (new ProjectIdeaCard())
                ->setTitle($title)
                ->setDescription($request->request->get('description'))
                ->setTaskCode($request->request->get('taskCode'))
                ->setAssignedTo($request->request->get('assignedTo'))
                ->setTargetDate($this->parseTargetDate($request->request->get('targetDate')))
                ->setTags($this->parseTags($request->request->get('tags')))
                ->setImages($this->storeUploadedImages($request->files->all('images')))
                ->setStatus($status)
                ->setPosition($this->ideaRepository->nextPositionForStatus($status))
                ->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($card);
            $this->entityManager->flush();
            $this->addFlash('success', 'Ideen-Karte wurde erstellt.');

            return $this->redirectToRoute('admin_projects_ideas');
        }

        $cardId = (int) $request->request->get('card_id', 0);
        $card = $cardId > 0 ? $this->ideaRepository->find($cardId) : null;
        if (!$card instanceof ProjectIdeaCard) {
            $this->addFlash('warning', 'Ideen-Karte wurde nicht gefunden.');

            return $this->redirectToRoute('admin_projects_ideas');
        }

        if ('update' === $action) {
            if (!$this->isCsrfTokenValid('project_board_ideas_update_'.$card->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_projects_ideas');
            }

            $title = trim((string) $request->request->get('title', ''));
            if ('' === $title) {
                $this->addFlash('warning', 'Bitte einen Titel fuer die Ideen-Karte angeben.');

                return $this->redirectToRoute('admin_projects_ideas');
            }

            $targetStatus = (string) $request->request->get('status', $card->getStatus());
            if ($targetStatus !== $card->getStatus()) {
                $card->setPosition($this->ideaRepository->nextPositionForStatus($targetStatus));
            }

            $card
                ->setTitle($title)
                ->setDescription($request->request->get('description'))
                ->setTaskCode($request->request->get('taskCode'))
                ->setAssignedTo($request->request->get('assignedTo'))
                ->setTargetDate($this->parseTargetDate($request->request->get('targetDate')))
                ->setTags($this->parseTags($request->request->get('tags')))
                ->setImages(array_values(array_merge(
                    $card->getImages(),
                    $this->storeUploadedImages($request->files->all('images'))
                )))
                ->setStatus($targetStatus)
                ->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->flush();
            $this->addFlash('success', 'Ideen-Karte wurde aktualisiert.');

            return $this->redirectToRoute('admin_projects_ideas');
        }

        if ('delete' === $action) {
            if (!$this->isCsrfTokenValid('project_board_ideas_delete_'.$card->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_projects_ideas');
            }

            $this->entityManager->remove($card);
            $this->entityManager->flush();
            $this->addFlash('success', 'Ideen-Karte wurde geloescht.');
        }

        return $this->redirectToRoute('admin_projects_ideas');
    }

    /**
     * @param ProjectTodoCardRepository|ProjectIdeaCardRepository $repository
     * @param array<string, string> $statusChoices
     *
     * @return array<string, array<int, object>>
     */
    private function buildColumns(object $repository, array $statusChoices): array
    {
        $columns = [];
        foreach ($statusChoices as $label => $status) {
            $columns[$status] = [
                'label' => $label,
                'cards' => $repository->findByStatusOrdered($status),
            ];
        }

        return $columns;
    }

    /**
     * @param array<string, string> $statusChoices
     * @param array<string, array{label:string,cards:array<int, object>}> $columns
     */
    private function renderBoard(string $boardKey, string $title, string $intro, array $statusChoices, array $columns): Response
    {
        return $this->render('admin/project_board.html.twig', [
            'boardKey' => $boardKey,
            'boardTitle' => $title,
            'boardIntro' => $intro,
            'statusChoices' => $statusChoices,
            'columns' => $columns,
            'createTokenId' => 'project_board_'.$boardKey.'_create',
            'moveTokenId' => 'project_board_'.$boardKey.'_move',
            'moveRouteName' => 'todo' === $boardKey ? 'admin_projects_todo_move' : 'admin_projects_ideas_move',
            'boardRouteName' => 'todo' === $boardKey ? 'admin_projects_todo' : 'admin_projects_ideas',
        ]);
    }

    /**
     * @param array<string, string> $statusChoices
     */
    private function syncBoard(Request $request, array $statusChoices, string $entityClass, string $csrfTokenId): JsonResponse
    {
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid($csrfTokenId, $token)) {
            return $this->json(['success' => false, 'message' => 'Ungueltiges CSRF-Token.'], Response::HTTP_FORBIDDEN);
        }

        $rawBoardState = (string) $request->request->get('boardState', '');
        $boardState = json_decode($rawBoardState, true);
        if (!\is_array($boardState)) {
            return $this->json(['success' => false, 'message' => 'Ungueltiger Board-Status.'], Response::HTTP_BAD_REQUEST);
        }

        $validStatuses = array_values($statusChoices);
        $cardIds = [];
        foreach ($boardState as $column) {
            if (!\is_array($column)) {
                continue;
            }

            $status = trim((string) ($column['status'] ?? ''));
            if (!\in_array($status, $validStatuses, true)) {
                return $this->json(['success' => false, 'message' => 'Ungueltiger Status.'], Response::HTTP_BAD_REQUEST);
            }

            foreach (($column['cardIds'] ?? []) as $cardId) {
                if (is_numeric($cardId)) {
                    $cardIds[] = (int) $cardId;
                }
            }
        }

        $cardIds = array_values(array_unique(array_filter($cardIds, static fn (int $id): bool => $id > 0)));
        if ([] === $cardIds) {
            return $this->json(['success' => false, 'message' => 'Keine Karten uebermittelt.'], Response::HTTP_BAD_REQUEST);
        }

        $cards = $this->entityManager->getRepository($entityClass)->findBy(['id' => $cardIds]);
        $cardsById = [];
        foreach ($cards as $card) {
            if (\is_object($card) && method_exists($card, 'getId')) {
                $cardsById[(int) $card->getId()] = $card;
            }
        }

        $position = 1;
        foreach ($boardState as $column) {
            if (!\is_array($column)) {
                continue;
            }

            $status = trim((string) ($column['status'] ?? ''));
            $columnPosition = 1;
            foreach (($column['cardIds'] ?? []) as $cardId) {
                $normalizedCardId = is_numeric($cardId) ? (int) $cardId : 0;
                $card = $cardsById[$normalizedCardId] ?? null;
                if (!\is_object($card)) {
                    continue;
                }

                $card->setStatus($status);
                $card->setPosition($columnPosition);
                $card->setUpdatedAt(new \DateTimeImmutable());
                ++$columnPosition;
                ++$position;
            }
        }

        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    private function parseTargetDate(mixed $value): ?\DateTimeImmutable
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        try {
            return new \DateTimeImmutable(trim($value));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return list<string>
     */
    private function parseTags(mixed $value): array
    {
        if (!\is_string($value) || '' === trim($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (string $tag): string => trim($tag),
            preg_split('/[,\\n]+/', $value) ?: []
        ))));
    }

    /**
     * @param mixed $uploadedFiles
     *
     * @return list<string>
     */
    private function storeUploadedImages(mixed $uploadedFiles): array
    {
        $files = [];
        if ($uploadedFiles instanceof UploadedFile) {
            $files[] = $uploadedFiles;
        } elseif (\is_array($uploadedFiles)) {
            foreach ($uploadedFiles as $uploadedFile) {
                if ($uploadedFile instanceof UploadedFile) {
                    $files[] = $uploadedFile;
                }
            }
        }

        $storedPaths = [];
        $uploadDir = $this->projectDir.'/public/uploads/project-board';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return [];
        }

        foreach ($files as $uploadedFile) {
            if (!$uploadedFile->isValid()) {
                continue;
            }

            $baseName = pathinfo($uploadedFile->getClientOriginalName(), \PATHINFO_FILENAME);
            $sanitizedBaseName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $baseName ?: 'card-image');
            $sanitizedBaseName = trim((string) $sanitizedBaseName, '-');
            if ('' === $sanitizedBaseName) {
                $sanitizedBaseName = 'card-image';
            }

            $extension = $uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension() ?: 'bin';
            $fileName = sprintf('%s-%s.%s', $sanitizedBaseName, substr(bin2hex(random_bytes(8)), 0, 12), strtolower($extension));
            $uploadedFile->move($uploadDir, $fileName);
            $storedPaths[] = 'uploads/project-board/'.$fileName;
        }

        return $storedPaths;
    }
}
