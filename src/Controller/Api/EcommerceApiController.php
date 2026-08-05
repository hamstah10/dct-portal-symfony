<?php

namespace App\Controller\Api;

use App\Entity\ApiToken;
use App\Entity\ShopProduct;
use App\Repository\ApiTokenRepository;
use App\Repository\ShopCategoryRepository;
use App\Repository\ShopManufacturerRepository;
use App\Repository\ShopProductLinkRepository;
use App\Repository\ShopProductRepository;
use App\Service\EcommercePayloadMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ecommerce')]
final class EcommerceApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly ShopProductRepository $productRepository,
        private readonly ShopCategoryRepository $categoryRepository,
        private readonly ShopManufacturerRepository $manufacturerRepository,
        private readonly ShopProductLinkRepository $productLinkRepository,
        private readonly EcommercePayloadMapper $payloadMapper,
    ) {
    }

    #[Route('', name: 'api_ecommerce_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeEcommerceApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        return new JsonResponse([
            'message' => 'Ecommerce Katalog API',
            'endpoints' => [
                'GET /api/ecommerce/products',
                'GET /api/ecommerce/products/{uid}',
                'GET /api/ecommerce/categories',
                'GET /api/ecommerce/manufacturers',
            ],
        ]);
    }

    #[Route('/products', name: 'api_ecommerce_products', methods: ['GET'])]
    public function products(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeEcommerceApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $page = max(1, $request->query->getInt('page', 1));
        $limit = max(1, min(100, $request->query->getInt('limit', 50)));
        $category = $this->queryInt($request, 'category');
        $manufacturer = $this->queryInt($request, 'manufacturer');
        $search = trim((string) $request->query->get('q', ''));
        $includeInactive = $this->queryBool($request, 'includeInactive');

        $result = $includeInactive
            ? $this->productRepository->findSyncCatalogProducts(
                '' !== $search ? $search : null,
                $category,
                $manufacturer,
                $page,
                $limit
            )
            : $this->productRepository->findVisibleCatalogProducts(
                '' !== $search ? $search : null,
                $category,
                $manufacturer,
                $page,
                $limit
            );

        $total = $result['total'];

        return new JsonResponse([
            'data' => array_map(
                fn (ShopProduct $product): array => $this->payloadMapper->mapProduct($product),
                $result['items']
            ),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int) max(1, ceil($total / $limit)),
                'filters' => [
                    'q' => '' !== $search ? $search : null,
                    'category' => $category,
                    'manufacturer' => $manufacturer,
                    'includeInactive' => $includeInactive,
                ],
            ],
        ]);
    }

    #[Route('/products/{uid}', name: 'api_ecommerce_product_detail', requirements: ['uid' => '\d+'], methods: ['GET'])]
    public function productDetail(int $uid, Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeEcommerceApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $includeInactive = $this->queryBool($request, 'includeInactive');
        $product = $includeInactive
            ? $this->productRepository->findSyncCatalogProduct($uid)
            : $this->productRepository->findVisibleCatalogProduct($uid);
        if (!$product instanceof ShopProduct) {
            return $this->errorResponse('Produkt nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'data' => $this->payloadMapper->mapProduct(
                $product,
                true,
                $this->productLinkRepository->findVisibleByProduct($product)
            ),
        ]);
    }

    #[Route('/categories', name: 'api_ecommerce_categories', methods: ['GET'])]
    public function categories(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeEcommerceApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $categories = $this->categoryRepository->findVisibleCatalogCategories();

        return new JsonResponse([
            'data' => array_map(
                fn ($category): array => $this->payloadMapper->mapCategory($category),
                $categories
            ),
            'meta' => [
                'total' => count($categories),
            ],
        ]);
    }

    #[Route('/manufacturers', name: 'api_ecommerce_manufacturers', methods: ['GET'])]
    public function manufacturers(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeEcommerceApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $manufacturers = $this->manufacturerRepository->findVisibleCatalogManufacturers();

        return new JsonResponse([
            'data' => array_values(array_filter(array_map(
                fn ($manufacturer): ?array => $this->payloadMapper->mapManufacturer($manufacturer),
                $manufacturers
            ))),
            'meta' => [
                'total' => count($manufacturers),
            ],
        ]);
    }

    private function authorizeEcommerceApi(Request $request): ApiToken|JsonResponse
    {
        $provided = $this->getBearerToken($request);
        if (null === $provided) {
            return $this->errorResponse('Bearer-Token fehlt.', Response::HTTP_UNAUTHORIZED);
        }

        $apiToken = $this->apiTokenRepository->findValidByToken($provided);
        if (!$apiToken instanceof ApiToken) {
            return $this->errorResponse('API-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        return $apiToken;
    }

    private function queryInt(Request $request, string $name): ?int
    {
        $raw = trim((string) $request->query->get($name, ''));
        if ('' === $raw) {
            return null;
        }

        if (!ctype_digit($raw)) {
            return null;
        }

        $value = (int) $raw;

        return $value > 0 ? $value : null;
    }

    private function queryBool(Request $request, string $name): bool
    {
        $raw = strtolower(trim((string) $request->query->get($name, '')));

        return in_array($raw, ['1', 'true', 'yes', 'on'], true);
    }
}
