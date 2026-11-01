<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Entity\KnowledgeEntry;
use App\Entity\VehicleEngine;
use App\Service\Ai\KnowledgeSearchTool;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class KnowledgeEntryApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly KnowledgeSearchTool $brandKnowledgeSearch,
    ) {
    }

    #[Route('/operations-api/brand-knowledge/search', name: 'superadmin_portal_brand_knowledge_search', methods: ['GET'])]
    public function searchBrandKnowledge(Request $request): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));
        if ('' === $query) {
            return $this->json(['data' => []]);
        }

        $result = $this->brandKnowledgeSearch->search($query);
        if (!$result['found']) {
            return $this->json(['data' => []]);
        }

        $items = [];
        foreach (preg_split('/\n\n(?=\[\d+\] )/', $result['passages']) ?: [] as $part) {
            if (1 !== preg_match('/^\[\d+\] ([^\n]*)\n(.*)$/s', $part, $matches)) {
                continue;
            }
            $items[] = ['source' => $matches[1], 'text' => $matches[2]];
        }

        return $this->json(['data' => $items]);
    }

    #[Route('/operations-api/knowledge-entries', name: 'superadmin_portal_knowledge_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $category = $request->query->get('category');
        $tag = $request->query->get('tag');
        $search = $request->query->get('search');
        $engineId = $request->query->get('engineId');

        $qb = $this->entityManager->getRepository(KnowledgeEntry::class)
            ->createQueryBuilder('ke')
            ->where('ke.active = true')
            ->orderBy('ke.updatedAt', 'DESC');

        if ($category) {
            $qb->andWhere('ke.category = :category')
                ->setParameter('category', $category);
        }

        if ($search) {
            $qb->andWhere('(ke.title LIKE :search OR ke.content LIKE :search)')
                ->setParameter('search', '%'.$search.'%');
        }

        if ($tag) {
            $qb->andWhere('JSON_CONTAINS(ke.tags, JSON_QUOTE(:tag)) = 1')
                ->setParameter('tag', $tag);
        }

        if ($engineId) {
            $qb->leftJoin('ke.vehicleEngines', 've')
                ->andWhere('ve.id = :engineId OR :engineId IS NULL')
                ->setParameter('engineId', $engineId);
        }

        $entries = $qb->setMaxResults(100)->getQuery()->getResult();

        $data = array_map(fn (KnowledgeEntry $e) => [
            'id' => $e->getId(),
            'title' => $e->getTitle(),
            'content' => $e->getContent(),
            'category' => $e->getCategory(),
            'tags' => $e->getTags(),
            'usefulness' => $e->getUsefulness(),
            'engineCount' => $e->getVehicleEngines()->count(),
            'createdAt' => $e->getCreatedAt()->format('c'),
            'updatedAt' => $e->getUpdatedAt()->format('c'),
        ], $entries);

        return $this->json(['data' => $data]);
    }

    #[Route('/operations-api/knowledge-entries', name: 'superadmin_portal_knowledge_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        $entry = new KnowledgeEntry();
        $entry->setTitle((string) ($payload['title'] ?? ''));
        $entry->setContent((string) ($payload['content'] ?? ''));
        $entry->setCategory((string) ($payload['category'] ?? 'Allgemein'));
        $entry->setTags((array) ($payload['tags'] ?? []));
        $entry->setActive((bool) ($payload['active'] ?? true));

        if (isset($payload['engineIds']) && is_array($payload['engineIds'])) {
            foreach ($payload['engineIds'] as $engineId) {
                $engine = $this->entityManager->getRepository(VehicleEngine::class)->find($engineId);
                if ($engine) {
                    $entry->addVehicleEngine($engine);
                }
            }
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        return $this->json(['data' => ['id' => $entry->getId()]], 201);
    }

    #[Route('/operations-api/knowledge-entries/{id}', name: 'superadmin_portal_knowledge_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $entry = $this->entityManager->getRepository(KnowledgeEntry::class)->find($id);
        if (!$entry) {
            throw new NotFoundHttpException('Entry not found');
        }

        return $this->json(['data' => [
            'id' => $entry->getId(),
            'title' => $entry->getTitle(),
            'content' => $entry->getContent(),
            'category' => $entry->getCategory(),
            'tags' => $entry->getTags(),
            'usefulness' => $entry->getUsefulness(),
            'active' => $entry->isActive(),
            'engineIds' => array_map(fn (VehicleEngine $e) => $e->getId(), $entry->getVehicleEngines()->toArray()),
        ]]);
    }

    #[Route('/operations-api/knowledge-entries/{id}', name: 'superadmin_portal_knowledge_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $entry = $this->entityManager->getRepository(KnowledgeEntry::class)->find($id);
        if (!$entry) {
            throw new NotFoundHttpException('Entry not found');
        }

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        if (isset($payload['title'])) $entry->setTitle((string) $payload['title']);
        if (isset($payload['content'])) $entry->setContent((string) $payload['content']);
        if (isset($payload['category'])) $entry->setCategory((string) $payload['category']);
        if (isset($payload['tags'])) $entry->setTags((array) $payload['tags']);
        if (isset($payload['active'])) $entry->setActive((bool) $payload['active']);
        if (isset($payload['usefulness'])) $entry->setUsefulness((int) $payload['usefulness']);

        $entry->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/operations-api/knowledge-entries/{id}', name: 'superadmin_portal_knowledge_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $entry = $this->entityManager->getRepository(KnowledgeEntry::class)->find($id);
        if (!$entry) {
            throw new NotFoundHttpException('Entry not found');
        }

        $this->entityManager->remove($entry);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
}
