<?php

namespace App\Controller\Api;

use App\Entity\ApiToken;
use App\Repository\ApiTokenRepository;
use App\Service\Api\OpenClawApiResourceRegistry;
use App\Service\Api\OpenClawApiSerializer;
use App\Service\Api\OpenClawApiWriter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/openclaw')]
final class OpenClawApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly OpenClawApiResourceRegistry $resourceRegistry,
        private readonly OpenClawApiSerializer $serializer,
        private readonly OpenClawApiWriter $writer,
    ) {
    }

    #[Route('', name: 'api_openclaw_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeOpenClaw($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $resources = [];
        foreach ($this->resourceRegistry->getResources() as $resource => $className) {
            $resources[] = [
                'resource' => $resource,
                'class' => $className,
                'listEndpoint' => '/api/openclaw/' . $resource,
                'schemaEndpoint' => '/api/openclaw/' . $resource . '/schema',
            ];
        }

        return new JsonResponse([
            'message' => 'OpenClaw Read API',
            'application' => 'OpenClaw',
            'resources' => $resources,
            'query' => [
                'page' => 'Standard 1',
                'limit' => 'Standard 50, Maximum 250',
                'orderBy' => 'Skalares Feld der Ressource',
                'orderDir' => 'asc oder desc',
                'ids' => 'Kommagetrennte ID-Liste',
                'filter[field]' => 'exakter Vergleich auf skalares Feld',
                'view' => 'compact oder full',
            ],
            'methods' => [
                'GET /api/openclaw',
                'GET /api/openclaw/{resource}',
                'GET /api/openclaw/{resource}/schema',
                'GET /api/openclaw/{resource}/{id}',
                'POST /api/openclaw/{resource}',
                'PUT /api/openclaw/{resource}/{id}',
                'PATCH /api/openclaw/{resource}/{id}',
                'DELETE /api/openclaw/{resource}/{id}',
            ],
        ]);
    }

    #[Route('/{resource}', name: 'api_openclaw_list', requirements: ['resource' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function list(string $resource, Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeOpenClaw($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $className = $this->resourceRegistry->resolveClass($resource);
        if (null === $className) {
            return $this->errorResponse('Ressource nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $metadata = $this->entityManager->getClassMetadata($className);
        $repository = $this->entityManager->getRepository($className);
        $queryBuilder = $repository->createQueryBuilder('e');

        $identifierFields = $metadata->getIdentifierFieldNames();
        $primaryIdentifier = $identifierFields[0] ?? 'id';
        $orderBy = trim((string) $request->query->get('orderBy', $primaryIdentifier));
        if (!$metadata->hasField($orderBy)) {
            $orderBy = $primaryIdentifier;
        }

        $orderDir = strtolower(trim((string) $request->query->get('orderDir', 'desc')));
        $orderDir = 'asc' === $orderDir ? 'ASC' : 'DESC';

        $this->applyIdFilter($queryBuilder, $primaryIdentifier, (string) $request->query->get('ids', ''));
        $this->applyScalarFilters($queryBuilder, $metadata, $request);

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(250, (int) $request->query->get('limit', 50)));

        $queryBuilder
            ->orderBy('e.' . $orderBy, $orderDir)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder->getQuery());
        $items = [];
        $fullView = 'full' === strtolower(trim((string) $request->query->get('view', 'compact')));

        foreach ($paginator as $entity) {
            if (!is_object($entity)) {
                continue;
            }

            $items[] = $this->serializer->serialize($entity, $fullView);
        }

        return new JsonResponse([
            'resource' => $resource,
            'class' => $className,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'count' => count($items),
                'total' => count($paginator),
                'orderBy' => $orderBy,
                'orderDir' => strtolower($orderDir),
                'view' => $fullView ? 'full' : 'compact',
            ],
            'items' => $items,
        ]);
    }

    #[Route('/{resource}/schema', name: 'api_openclaw_schema', requirements: ['resource' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function schema(string $resource, Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeOpenClaw($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $className = $this->resourceRegistry->resolveClass($resource);
        if (null === $className) {
            return $this->errorResponse('Ressource nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->describeResource($className));
    }

    #[Route('/{resource}', name: 'api_openclaw_create', requirements: ['resource' => '[a-z0-9\-]+'], methods: ['POST'])]
    public function create(string $resource, Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeOpenClaw($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $className = $this->resourceRegistry->resolveClass($resource);
        if (null === $className) {
            return $this->errorResponse('Ressource nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $this->readJsonPayload($request);
            $entity = $this->writer->create($className, $payload);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (\Throwable $throwable) {
            return $this->errorResponse($throwable->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'resource' => $resource,
            'item' => $this->serializer->serialize($entity, true),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{resource}/{id}', name: 'api_openclaw_detail', requirements: ['resource' => '[a-z0-9\-]+', 'id' => '[^/]+' ], methods: ['GET'])]
    public function detail(string $resource, string $id, Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeOpenClaw($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $className = $this->resourceRegistry->resolveClass($resource);
        if (null === $className) {
            return $this->errorResponse('Ressource nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $entity = $this->entityManager->getRepository($className)->find($id);
        if (!is_object($entity)) {
            return $this->errorResponse('Datensatz nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'resource' => $resource,
            'item' => $this->serializer->serialize($entity, true),
        ]);
    }

    #[Route('/{resource}/{id}', name: 'api_openclaw_put', requirements: ['resource' => '[a-z0-9\-]+', 'id' => '[^/]+' ], methods: ['PUT'])]
    public function put(string $resource, string $id, Request $request): JsonResponse
    {
        return $this->update($resource, $id, $request, false);
    }

    #[Route('/{resource}/{id}', name: 'api_openclaw_patch', requirements: ['resource' => '[a-z0-9\-]+', 'id' => '[^/]+' ], methods: ['PATCH'])]
    public function patch(string $resource, string $id, Request $request): JsonResponse
    {
        return $this->update($resource, $id, $request, true);
    }

    #[Route('/{resource}/{id}', name: 'api_openclaw_delete', requirements: ['resource' => '[a-z0-9\-]+', 'id' => '[^/]+' ], methods: ['DELETE'])]
    public function delete(string $resource, string $id, Request $request): Response
    {
        if (($authResponse = $this->authorizeOpenClaw($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $className = $this->resourceRegistry->resolveClass($resource);
        if (null === $className) {
            return $this->errorResponse('Ressource nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $entity = $this->entityManager->getRepository($className)->find($id);
        if (!is_object($entity)) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function authorizeOpenClaw(Request $request): ?JsonResponse
    {
        $provided = $this->getBearerToken($request);
        if (null === $provided) {
            return $this->errorResponse('OpenClaw API-Token fehlt.', Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->apiTokenRepository->findValidByToken($provided);
        if (!$token instanceof ApiToken) {
            return $this->errorResponse('OpenClaw API-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $applicationName = strtolower(trim((string) $token->getApplicationName()));
        if (!str_starts_with($applicationName, 'openclaw')) {
            return $this->errorResponse('Dieser API-Token ist nicht fuer OpenClaw freigegeben.', Response::HTTP_FORBIDDEN);
        }

        return null;
    }

    private function update(string $resource, string $id, Request $request, bool $partial): JsonResponse
    {
        if (($authResponse = $this->authorizeOpenClaw($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $className = $this->resourceRegistry->resolveClass($resource);
        if (null === $className) {
            return $this->errorResponse('Ressource nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $entity = $this->entityManager->getRepository($className)->find($id);
        if (!is_object($entity)) {
            return $this->errorResponse('Datensatz nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $this->readJsonPayload($request);
            $this->writer->update($entity, $payload, $partial);
            $this->entityManager->flush();
        } catch (\Throwable $throwable) {
            return $this->errorResponse($throwable->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'resource' => $resource,
            'item' => $this->serializer->serialize($entity, true),
        ]);
    }

    private function applyIdFilter(QueryBuilder $queryBuilder, string $identifierField, string $rawIds): void
    {
        $normalized = array_values(array_filter(array_map('trim', explode(',', $rawIds)), static fn (string $value): bool => '' !== $value));
        if ([] === $normalized) {
            return;
        }

        $queryBuilder
            ->andWhere('e.' . $identifierField . ' IN (:ids)')
            ->setParameter('ids', $normalized);
    }

    private function applyScalarFilters(QueryBuilder $queryBuilder, object $metadata, Request $request): void
    {
        $allQuery = $request->query->all();
        $filters = $allQuery['filter'] ?? null;
        if (!is_array($filters)) {
            return;
        }

        $index = 0;
        foreach ($filters as $field => $value) {
            if (!is_string($field) || '' === trim($field) || !$metadata->hasField($field)) {
                continue;
            }

            if (is_array($value)) {
                continue;
            }

            $parameterName = 'filter_' . $index++;
            $queryBuilder
                ->andWhere('e.' . $field . ' = :' . $parameterName)
                ->setParameter($parameterName, $value);
        }
    }
}
