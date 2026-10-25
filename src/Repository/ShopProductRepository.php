<?php

namespace App\Repository;

use App\Entity\ShopProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopProduct>
 */
class ShopProductRepository extends ServiceEntityRepository
{
    use ProvidesNextUidTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopProduct::class);
    }

    /**
     * @return array{items: list<ShopProduct>, total: int}
     */
    public function findVisibleCatalogProducts(
        ?string $search = null,
        ?int $category = null,
        ?int $manufacturer = null,
        int $page = 1,
        int $limit = 50
    ): array {
        return $this->findCatalogProducts($search, $category, $manufacturer, $page, $limit, false);
    }

    /**
     * @return array{items: list<ShopProduct>, total: int}
     */
    public function findSyncCatalogProducts(
        ?string $search = null,
        ?int $category = null,
        ?int $manufacturer = null,
        int $page = 1,
        int $limit = 50
    ): array {
        return $this->findCatalogProducts($search, $category, $manufacturer, $page, $limit, true);
    }

    public function findVisibleCatalogProduct(int $uid): ?ShopProduct
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.uid = :uid')
            ->setParameter('uid', $uid)
            ->setMaxResults(1);

        $this->applyVisibleProductConstraints($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findSyncCatalogProduct(int $uid): ?ShopProduct
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.uid = :uid')
            ->andWhere('p.deleted = :deleted')
            ->andWhere('p.sysLanguageUid = :language')
            ->setParameter('uid', $uid)
            ->setParameter('deleted', false)
            ->setParameter('language', 0)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array{items: list<ShopProduct>, total: int}
     */
    private function findCatalogProducts(
        ?string $search,
        ?int $category,
        ?int $manufacturer,
        int $page,
        int $limit,
        bool $sync
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.categories', 'category')
            ->addSelect('category')
            ->leftJoin('p.manufacturer', 'manufacturer')
            ->addSelect('manufacturer')
            ->orderBy('p.sorting', 'ASC')
            ->addOrderBy('p.title', 'ASC')
            ->setFirstResult((max(1, $page) - 1) * max(1, $limit))
            ->setMaxResults(max(1, $limit));

        $countQb = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.uid)');

        if ($category !== null) {
            $qb->andWhere('category.uid = :category');
            $countQb
                ->innerJoin('p.categories', 'countCategory')
                ->andWhere('countCategory.uid = :category')
                ->setParameter('category', $category);
            $qb->setParameter('category', $category);
        }

        if ($manufacturer !== null) {
            $qb->andWhere('manufacturer.uid = :manufacturer');
            $countQb
                ->leftJoin('p.manufacturer', 'countManufacturer')
                ->andWhere('countManufacturer.uid = :manufacturer')
                ->setParameter('manufacturer', $manufacturer);
            $qb->setParameter('manufacturer', $manufacturer);
        }

        $normalizedSearch = trim((string) $search);
        if ($normalizedSearch !== '') {
            $qb
                ->andWhere('LOWER(p.title) LIKE :search OR LOWER(p.sku) LIKE :search OR LOWER(p.shortDescription) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($normalizedSearch) . '%');
            $countQb
                ->andWhere('LOWER(p.title) LIKE :search OR LOWER(p.sku) LIKE :search OR LOWER(p.shortDescription) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($normalizedSearch) . '%');
        }

        if ($sync) {
            $this->applySyncProductConstraints($qb);
            $this->applySyncProductConstraints($countQb);
        } else {
            $this->applyVisibleProductConstraints($qb);
            $this->applyVisibleProductConstraints($countQb);
        }

        return [
            'items' => $qb->getQuery()->getResult(),
            'total' => (int) $countQb->getQuery()->getSingleScalarResult(),
        ];
    }

    private function applyVisibleProductConstraints($qb): void
    {
        $this->applySyncProductConstraints($qb);
        $now = time();
        $qb
            ->andWhere('p.hidden = :hidden')
            ->andWhere('p.isActive = :isActive')
            ->andWhere('p.apiActive = :apiActive')
            ->andWhere('p.starttime = 0 OR p.starttime <= :now')
            ->andWhere('p.endtime = 0 OR p.endtime >= :now')
            ->setParameter('hidden', false)
            ->setParameter('isActive', true)
            ->setParameter('apiActive', true)
            ->setParameter('now', $now);
    }

    private function applySyncProductConstraints($qb): void
    {
        $qb
            ->andWhere('p.deleted = :deleted')
            ->andWhere('p.sysLanguageUid = :language')
            ->setParameter('deleted', false)
            ->setParameter('language', 0);
    }
}
