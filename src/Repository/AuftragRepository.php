<?php

namespace App\Repository;

use App\Entity\Auftrag;
use App\Entity\Firma;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Ulid;

/**
 * @extends ServiceEntityRepository<Auftrag>
 */
class AuftragRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Auftrag::class);
    }

    public function findNextAutoModJob(): ?Auftrag
    {
        $jobs = $this->findNextAutoModJobs(null, 1);

        return $jobs[0] ?? null;
    }

    /**
     * @return list<Auftrag>
     */
    public function findNextAutoModJobs(?Firma $firma = null, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.kunde', 'k')
            ->andWhere('a.autoModEnabled = true')
            ->andWhere('a.autoModStatus = :status')
            ->setParameter('status', Auftrag::AUTOMOD_STATUS_DECRYPTED)
            ->orderBy('a.createdAt', 'ASC')
            ->setMaxResults($limit);

        if ($firma instanceof Firma) {
            $qb->andWhere('k.firma = :firma')
                ->setParameter('firma', $firma);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByUlid(string $ulid): ?Auftrag
    {
        return $this->findOneBy(['ulid' => $ulid]);
    }

    public function markAutoModReady(Auftrag $order): void
    {
        $order->setAutoModEnabled(true);
        if (null === $order->getUlid()) {
            $order->setUlid((string) new Ulid());
        }
        $order->setAutoModStatus(Auftrag::AUTOMOD_STATUS_DECRYPTED);

        $this->getEntityManager()->flush();
    }
}
