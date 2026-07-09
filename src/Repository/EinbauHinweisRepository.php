<?php

namespace App\Repository;

use App\Entity\EinbauHinweis;
use App\Entity\VehicleEngine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EinbauHinweis>
 */
class EinbauHinweisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EinbauHinweis::class);
    }

    /**
     * @return EinbauHinweis[]
     */
    public function findForEngine(VehicleEngine $engine): array
    {
        $model = $engine->getVehicleModel();

        // Hinweise direkt am Motor
        $byEngine = $this->createQueryBuilder('h')
            ->join('h.vehicleEngines', 've')
            ->where('h.active = true')
            ->andWhere('ve.id = :engineId')
            ->setParameter('engineId', $engine->getId())
            ->getQuery()
            ->getResult();

        // Hinweise am Modell ohne eigene Motor-Verknüpfungen
        $byModel = [];
        if (null !== $model) {
            $byModel = $this->createQueryBuilder('h')
                ->join('h.vehicleModels', 'vm')
                ->where('h.active = true')
                ->andWhere('vm.id = :modelId')
                ->andWhere('h.vehicleEngines IS EMPTY')
                ->setParameter('modelId', $model->getId())
                ->getQuery()
                ->getResult();
        }

        $merged = [];
        foreach (array_merge($byEngine, $byModel) as $h) {
            $merged[$h->getId()] = $h;
        }
        usort($merged, fn ($a, $b) => strnatcasecmp($a->getTitle(), $b->getTitle()));

        return array_values($merged);
    }
}
