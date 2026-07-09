<?php

namespace App\Controller\Api;

use App\Api\ApiValidationException;
use App\Entity\Kunde;
use App\Repository\ApiTokenRepository;
use App\Repository\KundeRepository;
use App\Service\Api\CustomerApiPayloadService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/customers')]
final class CustomerController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly KundeRepository $kundeRepository,
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CustomerApiPayloadService $payloadService,
        private readonly string $apiToken,
    ) {
    }

    #[Route('', name: 'api_customers_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeSystemApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $customers = $this->kundeRepository->findBy([], ['id' => 'ASC']);

        return new JsonResponse([
            'data' => array_map(
                fn (Kunde $customer): array => $this->payloadService->normalizeCustomer($customer),
                $customers
            ),
        ]);
    }

    #[Route('/{id}', name: 'api_customers_get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getCustomer(Request $request, int $id): JsonResponse
    {
        if (($authResponse = $this->authorizeSystemApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $customer = $this->kundeRepository->find($id);
        if (!$customer instanceof Kunde) {
            return $this->errorResponse('Kunde nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->payloadService->normalizeCustomer($customer));
    }

    #[Route('', name: 'api_customers_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeSystemApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        try {
            $payload = $this->readJsonPayload($request);
            $customer = new Kunde();
            $this->payloadService->applyPayload($customer, $payload, false);
            $this->entityManager->persist($customer);
            $this->entityManager->flush();
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (ApiValidationException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (UniqueConstraintViolationException) {
            return $this->errorResponse('E-Mail oder Kundennummer existiert bereits.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse($this->payloadService->normalizeCustomer($customer), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_customers_put', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function put(Request $request, int $id): JsonResponse
    {
        return $this->update($request, $id, false);
    }

    #[Route('/{id}', name: 'api_customers_patch', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function patch(Request $request, int $id): JsonResponse
    {
        return $this->update($request, $id, true);
    }

    #[Route('/{id}', name: 'api_customers_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(Request $request, int $id): Response
    {
        if (($authResponse = $this->authorizeSystemApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $customer = $this->kundeRepository->find($id);
        if (!$customer instanceof Kunde) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        foreach ($customer->getAbos() as $abo) {
            $this->entityManager->remove($abo);
        }

        $this->entityManager->remove($customer);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function update(Request $request, int $id, bool $partial): JsonResponse
    {
        if (($authResponse = $this->authorizeSystemApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $customer = $this->kundeRepository->find($id);
        if (!$customer instanceof Kunde) {
            return $this->errorResponse('Kunde nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $this->readJsonPayload($request);
            $this->payloadService->applyPayload($customer, $payload, $partial);
            $this->entityManager->flush();
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (ApiValidationException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (UniqueConstraintViolationException) {
            return $this->errorResponse('E-Mail oder Kundennummer existiert bereits.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse($this->payloadService->normalizeCustomer($customer));
    }

    private function authorizeSystemApi(Request $request): ?JsonResponse
    {
        $provided = $this->getBearerToken($request);
        if (null === $provided) {
            return $this->errorResponse('API-Token fehlt.', Response::HTTP_UNAUTHORIZED);
        }

        $envTokenMatches = '' !== trim($this->apiToken) && hash_equals($this->apiToken, $provided);
        $dbTokenMatches = null !== $this->apiTokenRepository->findOneBy(['apiToken' => $provided]);

        if (!$envTokenMatches && !$dbTokenMatches) {
            return $this->errorResponse('API-Token ist ungueltig.', Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }
}
