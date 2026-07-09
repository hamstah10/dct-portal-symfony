<?php

namespace App\Service\Ai;

use App\Entity\VehicleEngine;
use Doctrine\ORM\EntityManagerInterface;

final class VehicleDatabaseLookup
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return array<int, array{engine: VehicleEngine, score: float}>
     */
    public function search(array $criteria, int $limit = 25): array
    {
        $filters = $this->normalizeCriteria($criteria);
        if ([] === $filters) {
            return [];
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT e', 'm', 's', 'b', 'st')
            ->from(VehicleEngine::class, 'e')
            ->leftJoin('e.vehicleModel', 'm')
            ->leftJoin('m.vehicleSeries', 's')
            ->leftJoin('s.vehicleBrand', 'b')
            ->leftJoin('e.stages', 'st')
            ->andWhere('e.active = :active')
            ->setParameter('active', true);

        $or = $qb->expr()->orX();
        if (isset($filters['brand'])) {
            $or->add('LOWER(b.name) LIKE :brand');
            $qb->setParameter('brand', $filters['brand']);
        }
        if (isset($filters['series'])) {
            $or->add('LOWER(s.name) LIKE :series');
            $qb->setParameter('series', $filters['series']);
        }
        if (isset($filters['model'])) {
            $or->add('LOWER(m.name) LIKE :model OR LOWER(e.name) LIKE :model');
            $qb->setParameter('model', $filters['model']);
        }
        if (isset($filters['engineCode'])) {
            $or->add('LOWER(e.engineCode) LIKE :engineCode OR LOWER(e.name) LIKE :engineCode');
            $qb->setParameter('engineCode', $filters['engineCode']);
        }
        if (isset($filters['engineName'])) {
            $or->add('LOWER(e.name) LIKE :engineName');
            $qb->setParameter('engineName', $filters['engineName']);
        }
        if (isset($filters['fuel'])) {
            $or->add('LOWER(e.fuel) LIKE :fuel');
            $qb->setParameter('fuel', $filters['fuel']);
        }
        if (isset($filters['displacement'])) {
            $or->add('LOWER(COALESCE(e.displacement, \'\')) LIKE :displacement');
            $qb->setParameter('displacement', $filters['displacement']);
        }
        if (isset($filters['horsepowerMin'], $filters['horsepowerMax'])) {
            $or->add('e.horsepower BETWEEN :horsepowerMin AND :horsepowerMax');
            $qb->setParameter('horsepowerMin', $filters['horsepowerMin']);
            $qb->setParameter('horsepowerMax', $filters['horsepowerMax']);
        }

        if (0 === $or->count()) {
            return [];
        }

        $qb->andWhere($or);

        $maxResults = max(50, $limit * 6);
        $qb->setMaxResults(min(200, $maxResults));

        $items = $qb->getQuery()->getResult();

        $matches = [];
        foreach ($items as $engine) {
            if (!$engine instanceof VehicleEngine) {
                continue;
            }

            $score = $this->scoreEngine($engine, $filters);
            if ($score <= 0) {
                continue;
            }

            $id = $engine->getId() ?? 0;
            if ($id <= 0) {
                continue;
            }

            if (isset($matches[$id]) && $matches[$id]['score'] >= $score) {
                continue;
            }

            $matches[$id] = [
                'engine' => $engine,
                'score' => $score,
            ];
        }

        if ([] === $matches) {
            return [];
        }

        usort($matches, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, max(1, $limit));
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return array<string, mixed>
     */
    private function normalizeCriteria(array $criteria): array
    {
        $filters = [];

        $brand = $this->normalizeString($criteria['brand'] ?? null);
        if (null !== $brand) {
            $filters['brand'] = '%' . $brand . '%';
        }

        $series = $this->normalizeString($criteria['series'] ?? null);
        if (null !== $series) {
            $filters['series'] = '%' . $series . '%';
        }

        $model = $this->normalizeString($criteria['model'] ?? null);
        if (null !== $model) {
            $filters['model'] = '%' . $model . '%';
        }

        $engineCode = $this->normalizeString($criteria['engineCode'] ?? null);
        if (null !== $engineCode) {
            $filters['engineCode'] = '%' . $engineCode . '%';
        }

        $engineName = $this->normalizeString($criteria['engineName'] ?? null);
        if (null !== $engineName) {
            $filters['engineName'] = '%' . $engineName . '%';
        }

        $fuel = $this->normalizeString($criteria['fuel'] ?? null);
        if (null !== $fuel) {
            $filters['fuel'] = '%' . $fuel . '%';
        }

        $displacement = $this->normalizeString($criteria['displacement'] ?? null);
        if (null !== $displacement) {
            $filters['displacement'] = '%' . $displacement . '%';
        }

        $powerHp = $this->toInt($criteria['powerHp'] ?? null);
        $powerKw = $this->toInt($criteria['powerKw'] ?? null);
        if (null === $powerHp && null !== $powerKw) {
            $powerHp = (int) round($powerKw * 1.35962);
        }

        if (null !== $powerHp) {
            $tolerance = max(15, (int) round($powerHp * 0.15));
            $filters['horsepowerMin'] = max(0, $powerHp - $tolerance);
            $filters['horsepowerMax'] = $powerHp + $tolerance;
            $filters['powerHp'] = $powerHp;
        }

        return $filters;
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function scoreEngine(VehicleEngine $engine, array $filters): float
    {
        $score = 0.0;

        $brandName = $engine->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand()?->getName();
        $seriesName = $engine->getVehicleModel()?->getVehicleSeries()?->getName();
        $modelName = $engine->getVehicleModel()?->getName();

        if (isset($filters['brand']) && $this->matches($filters['brand'], $brandName)) {
            $score += 3.0;
        }
        if (isset($filters['series']) && $this->matches($filters['series'], $seriesName)) {
            $score += 2.5;
        }
        if (isset($filters['model']) && $this->matches($filters['model'], $modelName)) {
            $score += 4.0;
        }
        if (isset($filters['engineCode']) && $this->matches($filters['engineCode'], $engine->getEngineCode())) {
            $score += 5.0;
        }
        if (isset($filters['engineName']) && $this->matches($filters['engineName'], $engine->getName())) {
            $score += 3.0;
        }
        if (isset($filters['fuel']) && $this->matches($filters['fuel'], $engine->getFuel())) {
            $score += 2.0;
        }
        if (isset($filters['displacement']) && $this->matches($filters['displacement'], $engine->getDisplacement())) {
            $score += 2.0;
        }

        if (isset($filters['powerHp']) && null !== $engine->getHorsepower()) {
            $target = (int) $filters['powerHp'];
            $current = (int) $engine->getHorsepower();
            $diff = abs($current - $target);
            if ($diff <= max(10, (int) round($target * 0.05))) {
                $score += 3.5;
            } elseif ($diff <= max(20, (int) round($target * 0.15))) {
                $score += 2.0;
            }
        }

        return $score;
    }

    private function normalizeString(mixed $value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);
        if ('' === $value) {
            return null;
        }

        return function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
    }

    private function toInt(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_string($value)) {
            if (preg_match('/\d+/', $value, $matches)) {
                return (int) $matches[0];
            }
        }

        return null;
    }

    private function matches(string $needle, ?string $haystack): bool
    {
        $needle = trim($needle, '% ');
        $haystack = trim((string) ($haystack ?? ''));
        if ('' === $needle || '' === $haystack) {
            return false;
        }

        $needle = function_exists('mb_strtolower') ? mb_strtolower($needle) : strtolower($needle);
        $haystack = function_exists('mb_strtolower') ? mb_strtolower($haystack) : strtolower($haystack);

        return str_contains($haystack, $needle) || str_contains($needle, $haystack);
    }
}
