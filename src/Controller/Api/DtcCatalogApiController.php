<?php

namespace App\Controller\Api;

use App\Entity\DtcCategory;
use App\Entity\DtcCode;
use App\Repository\DtcCategoryRepository;
use App\Repository\DtcCodeRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DtcCatalogApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly DtcCodeRepository $codeRepository,
        private readonly DtcCategoryRepository $categoryRepository,
    ) {
    }

    #[Route('/api/dtc-codes', name: 'api_dtc_codes_list', methods: ['GET'])]
    public function codes(Request $request): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));
        $category = trim((string) $request->query->get('category', ''));

        $queryBuilder = $this->createVisibleCodesQueryBuilder();

        if ('' !== $query) {
            $queryBuilder
                ->andWhere('LOWER(code.fehlercode) LIKE :query OR LOWER(code.beschreibung) LIKE :query')
                ->setParameter('query', '%' . mb_strtolower($query) . '%');
        }

        if ('' !== $category) {
            if (ctype_digit($category)) {
                $queryBuilder
                    ->andWhere('category.uid = :categoryUid')
                    ->setParameter('categoryUid', (int) $category);
            } else {
                $queryBuilder
                    ->andWhere('LOWER(category.buchstabe) = :categoryKey OR LOWER(category.name) = :categoryKey')
                    ->setParameter('categoryKey', mb_strtolower($category));
            }
        }

        /** @var list<DtcCode> $codes */
        $codes = $queryBuilder->getQuery()->getResult();

        return new JsonResponse([
            'data' => array_map(fn (DtcCode $code): array => $this->normalizeCode($code), $codes),
            'total' => count($codes),
        ]);
    }

    #[Route('/api/dtc-codes/categories', name: 'api_dtc_codes_categories_list', methods: ['GET'])]
    #[Route('/api/dtc-categories', name: 'api_dtc_categories_list', methods: ['GET'])]
    public function categories(): JsonResponse
    {
        $categories = $this->findVisibleCategories();

        return new JsonResponse([
            'data' => array_map(fn (DtcCategory $category): array => $this->normalizeCategory($category, true), $categories),
            'total' => count($categories),
        ]);
    }

    #[Route('/api/dtc-codes/categories/{uid}', name: 'api_dtc_codes_category_detail', requirements: ['uid' => '\d+'], methods: ['GET'])]
    #[Route('/api/dtc-categories/{uid}', name: 'api_dtc_category_detail', requirements: ['uid' => '\d+'], methods: ['GET'])]
    public function category(int $uid): JsonResponse
    {
        $category = $this->findVisibleCategory($uid);
        if (!$category instanceof DtcCategory) {
            return $this->errorResponse('DTC-Kategorie nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'data' => $this->normalizeCategory($category, true),
        ]);
    }

    #[Route('/api/dtc-codes/categories/{uid}/codes', name: 'api_dtc_codes_category_codes', requirements: ['uid' => '\d+'], methods: ['GET'])]
    #[Route('/api/dtc-categories/{uid}/codes', name: 'api_dtc_category_codes', requirements: ['uid' => '\d+'], methods: ['GET'])]
    public function categoryCodes(int $uid): JsonResponse
    {
        $category = $this->findVisibleCategory($uid);
        if (!$category instanceof DtcCategory) {
            return $this->errorResponse('DTC-Kategorie nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        /** @var list<DtcCode> $codes */
        $codes = $this->createVisibleCodesQueryBuilder()
            ->andWhere('category.uid = :categoryUid')
            ->setParameter('categoryUid', $uid)
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'category' => $this->normalizeCategory($category, false),
            'data' => array_map(fn (DtcCode $code): array => $this->normalizeCode($code), $codes),
            'total' => count($codes),
        ]);
    }

    #[Route('/api/dtc-codes/{identifier}', name: 'api_dtc_codes_detail', requirements: ['identifier' => '[^/]+'], methods: ['GET'])]
    public function code(string $identifier): JsonResponse
    {
        $code = $this->findVisibleCode($identifier);
        if (!$code instanceof DtcCode) {
            return $this->errorResponse('DTC-Code nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'data' => $this->normalizeCode($code),
        ]);
    }

    private function createVisibleCodesQueryBuilder(): QueryBuilder
    {
        return $this->codeRepository->createQueryBuilder('code')
            ->innerJoin('code.category', 'category')
            ->addSelect('category')
            ->andWhere('code.hidden = false')
            ->andWhere('code.deleted = false')
            ->andWhere('category.hidden = false')
            ->andWhere('category.deleted = false')
            ->orderBy('category.buchstabe', 'ASC')
            ->addOrderBy('category.name', 'ASC')
            ->addOrderBy('code.fehlercode', 'ASC');
    }

    private function findVisibleCode(string $identifier): ?DtcCode
    {
        $identifier = trim($identifier);
        if ('' === $identifier) {
            return null;
        }

        $queryBuilder = $this->createVisibleCodesQueryBuilder()
            ->setMaxResults(1);

        if (ctype_digit($identifier)) {
            $queryBuilder
                ->andWhere('code.uid = :uid')
                ->setParameter('uid', (int) $identifier);
        } else {
            $queryBuilder
                ->andWhere('LOWER(code.fehlercode) = :fehlercode')
                ->setParameter('fehlercode', mb_strtolower($identifier));
        }

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result instanceof DtcCode ? $result : null;
    }

    /**
     * @return list<DtcCategory>
     */
    private function findVisibleCategories(): array
    {
        /** @var list<DtcCategory> $categories */
        $categories = $this->categoryRepository->createQueryBuilder('category')
            ->andWhere('category.hidden = false')
            ->andWhere('category.deleted = false')
            ->orderBy('category.buchstabe', 'ASC')
            ->addOrderBy('category.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $categories;
    }

    private function findVisibleCategory(int $uid): ?DtcCategory
    {
        $result = $this->categoryRepository->createQueryBuilder('category')
            ->andWhere('category.uid = :uid')
            ->andWhere('category.hidden = false')
            ->andWhere('category.deleted = false')
            ->setParameter('uid', $uid)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof DtcCategory ? $result : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeCode(DtcCode $code): array
    {
        $category = $code->getCategory();

        return [
            'uid' => $code->getUid(),
            'code' => trim($code->getFehlercode()),
            'description' => trim((string) ($code->getBeschreibung() ?? '')),
            'category' => $category instanceof DtcCategory ? $this->normalizeCategory($category, false) : null,
            'created_at' => $this->normalizeTimestamp($code->getCrdate()),
            'updated_at' => $this->normalizeTimestamp($code->getTstamp()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeCategory(DtcCategory $category, bool $includeCodeCount): array
    {
        $data = [
            'uid' => $category->getUid(),
            'name' => trim($category->getName()),
            'letter' => strtoupper(trim($category->getBuchstabe())),
            'description' => trim((string) ($category->getBeschreibung() ?? '')),
            'created_at' => $this->normalizeTimestamp($category->getCrdate()),
            'updated_at' => $this->normalizeTimestamp($category->getTstamp()),
        ];

        if ($includeCodeCount) {
            $data['code_count'] = $this->countVisibleCodesForCategory($category);
        }

        return $data;
    }

    private function countVisibleCodesForCategory(DtcCategory $category): int
    {
        return (int) $this->codeRepository->createQueryBuilder('code')
            ->select('COUNT(code.uid)')
            ->andWhere('code.category = :category')
            ->andWhere('code.hidden = false')
            ->andWhere('code.deleted = false')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function normalizeTimestamp(int $timestamp): ?string
    {
        if ($timestamp <= 0) {
            return null;
        }

        return (new \DateTimeImmutable('@' . $timestamp))->format(DATE_ATOM);
    }
}
