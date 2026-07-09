<?php

namespace App\Service\Ai;

use App\Entity\VehicleEngine;
use App\Entity\VehicleStage;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool('vehicle_database_search', description: 'Search the vehicle database using vehicle criteria and return matching engines with stages.', method: 'search')]
final class VehicleDatabaseTool
{
    public function __construct(
        private readonly VehicleDatabaseLookup $lookup,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function search(
        ?string $brand = null,
        ?string $series = null,
        ?string $model = null,
        ?string $engineCode = null,
        ?string $engineName = null,
        ?string $fuel = null,
        ?string $displacement = null,
        ?int $powerHp = null,
        ?int $powerKw = null,
        int $limit = 5,
    ): array {
        $criteria = [
            'brand' => $brand,
            'series' => $series,
            'model' => $model,
            'engineCode' => $engineCode,
            'engineName' => $engineName,
            'fuel' => $fuel,
            'displacement' => $displacement,
            'powerHp' => $powerHp,
            'powerKw' => $powerKw,
        ];

        $matches = $this->lookup->search($criteria, max(1, $limit));

        return [
            'count' => \count($matches),
            'results' => array_map([$this, 'mapEngine'], $matches),
        ];
    }

    /**
     * @param array{engine: VehicleEngine, score: float} $match
     *
     * @return array<string, mixed>
     */
    private function mapEngine(array $match): array
    {
        $engine = $match['engine'];
        $stages = array_values(array_filter(
            $engine->getStages()->toArray(),
            static fn (mixed $stage): bool => $stage instanceof VehicleStage
        ));
        usort($stages, static fn (VehicleStage $a, VehicleStage $b): int => strnatcasecmp($a->getName(), $b->getName()));

        return [
            'score' => $match['score'],
            'engineId' => $engine->getId(),
            'engineName' => $engine->getName(),
            'engineCode' => $engine->getEngineCode(),
            'fuel' => $engine->getFuel(),
            'displacement' => $engine->getDisplacement(),
            'horsepower' => $engine->getHorsepower(),
            'torque' => $engine->getTorque(),
            'brand' => $engine->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand()?->getName(),
            'series' => $engine->getVehicleModel()?->getVehicleSeries()?->getName(),
            'model' => $engine->getVehicleModel()?->getName(),
            'stages' => array_map(static fn (VehicleStage $stage): array => [
                'id' => $stage->getId(),
                'name' => $stage->getName(),
                'hp' => $stage->getHp(),
                'nm' => $stage->getNm(),
                'price' => $stage->getPrice(),
                'icon' => $stage->getResolvedIconPath(),
                'active' => $stage->isActive(),
            ], $stages),
        ];
    }
}
