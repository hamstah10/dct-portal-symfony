<?php

namespace App\Websites\Chiptuningfile\Controller;

use App\Entity\VehicleBrand;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use App\Entity\VehicleStage;
use App\Repository\VehicleBrandRepository;
use App\Repository\VehicleEngineRepository;
use App\Repository\VehicleModelRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApiController
{
    public function __construct(
        private readonly VehicleBrandRepository $vehicleBrandRepository,
        private readonly VehicleModelRepository $vehicleModelRepository,
        private readonly VehicleEngineRepository $vehicleEngineRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(path: '/api/faq', name: 'cpt_website_api_faq', methods: ['GET'])]
    public function faq(): JsonResponse
    {
        return new JsonResponse([
            [
                'question' => 'Was bringt Chiptuning bei meinem Fahrzeug?',
                'answer' => 'Je nach Motor und Stage sind spuerbar mehr Leistung, besseres Ansprechverhalten und in vielen Faellen ein optimierter Durchzug moeglich.',
            ],
            [
                'question' => 'Ist ein Stage 1 Tuning ohne Hardware-Umbauten moeglich?',
                'answer' => 'Ja. Stage 1 basiert in der Regel auf einer reinen Softwareoptimierung und benoetigt normalerweise keine zusaetzlichen Hardware-Aenderungen.',
            ],
            [
                'question' => 'Wie schnell erhalte ich mein Tuning-File?',
                'answer' => 'Die Website kommuniziert eine typische Bearbeitungszeit von 30 bis 60 Minuten. Der genaue Zeitraum haengt vom Fahrzeug und vom Leistungsumfang ab.',
            ],
            [
                'question' => 'Sind auch DSG-, DPF- oder AGR-Loesungen verfuegbar?',
                'answer' => 'Ja. Neben klassischen Leistungsstufen werden auf der Website auch DSG-/S-Tronic-Optimierungen sowie weitere Sonderloesungen vorgestellt.',
            ],
            [
                'question' => 'Kann ich als Werkstatt oder Haendler Partner werden?',
                'answer' => 'Ja. Ueber den Haendlerbereich koennen Partnerkonditionen, Support und die Zusammenarbeit mit dem File-Service angefragt werden.',
            ],
        ]);
    }

    #[Route(path: '/api/contact', name: 'cpt_website_api_contact', methods: ['POST'])]
    public function contact(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse([
                'error' => 'Ungueltige Anfrage.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $subject = trim((string) ($payload['subject'] ?? ''));
        $message = trim((string) ($payload['message'] ?? ''));

        if ('' === $name || '' === $email || '' === $subject || '' === $message) {
            return new JsonResponse([
                'error' => 'Bitte alle Pflichtfelder ausfuellen.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->logger->info('Chiptuningfile website contact request received.', [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'phone' => trim((string) ($payload['phone'] ?? '')),
        ]);

        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route(path: '/api/brands', name: 'cpt_website_api_brands', methods: ['GET'])]
    public function brands(): JsonResponse
    {
        $brands = $this->vehicleBrandRepository->createQueryBuilder('brand')
            ->leftJoin('brand.vehicleType', 'vehicleType')
            ->where('brand.active = :active')
            ->andWhere('(vehicleType.id IS NULL OR vehicleType.active = :active)')
            ->setParameter('active', true)
            ->orderBy('brand.sorting', 'ASC')
            ->addOrderBy('brand.name', 'ASC')
            ->setMaxResults(48)
            ->getQuery()
            ->getResult();

        return new JsonResponse(array_map(
            fn (VehicleBrand $brand): array => [
                'id' => $brand->getId(),
                'name' => $brand->getName(),
                'logo' => $this->resolveMediaPath($brand->getImage()),
            ],
            $brands
        ));
    }

    #[Route(path: '/api/models/{brandId}', name: 'cpt_website_api_models', requirements: ['brandId' => '\d+'], methods: ['GET'])]
    public function models(int $brandId): JsonResponse
    {
        $models = $this->vehicleModelRepository->createQueryBuilder('model')
            ->leftJoin('model.vehicleSeries', 'series')
            ->leftJoin('series.vehicleBrand', 'brand')
            ->where('brand.id = :brandId')
            ->andWhere('model.active = :active')
            ->andWhere('series.active = :active')
            ->andWhere('brand.active = :active')
            ->setParameter('brandId', $brandId)
            ->setParameter('active', true)
            ->orderBy('series.sorting', 'ASC')
            ->addOrderBy('series.name', 'ASC')
            ->addOrderBy('model.sorting', 'ASC')
            ->addOrderBy('model.name', 'ASC')
            ->setMaxResults(120)
            ->getQuery()
            ->getResult();

        return new JsonResponse(array_map(
            static function (VehicleModel $model): array {
                $seriesName = trim((string) $model->getVehicleSeries()?->getName());

                return [
                    'id' => $model->getId(),
                    'name' => $model->getName(),
                    'years' => '' !== $seriesName ? $seriesName : 'Modellreihe',
                ];
            },
            $models
        ));
    }

    #[Route(path: '/api/engines/{modelId}', name: 'cpt_website_api_engines', requirements: ['modelId' => '\d+'], methods: ['GET'])]
    public function engines(int $modelId): JsonResponse
    {
        $engines = $this->vehicleEngineRepository->createQueryBuilder('engine')
            ->leftJoin('engine.vehicleModel', 'model')
            ->where('model.id = :modelId')
            ->andWhere('engine.active = :active')
            ->andWhere('model.active = :active')
            ->setParameter('modelId', $modelId)
            ->setParameter('active', true)
            ->orderBy('engine.sorting', 'ASC')
            ->addOrderBy('engine.name', 'ASC')
            ->setMaxResults(120)
            ->getQuery()
            ->getResult();

        return new JsonResponse(array_map(
            static fn (VehicleEngine $engine): array => [
                'id' => $engine->getId(),
                'name' => $engine->getName(),
                'power_hp' => $engine->getHorsepower() ?? 0,
                'torque_nm' => $engine->getTorque() ?? 0,
            ],
            $engines
        ));
    }

    #[Route(path: '/api/tuning/{engineId}', name: 'cpt_website_api_tuning', requirements: ['engineId' => '\d+'], methods: ['GET'])]
    public function tuning(int $engineId): JsonResponse
    {
        $engine = $this->vehicleEngineRepository->find($engineId);
        if (!$engine instanceof VehicleEngine || !$engine->isActive()) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $stages = array_values(array_filter(
            $engine->getStages()->toArray(),
            static fn (mixed $stage): bool => $stage instanceof VehicleStage && $stage->isActive()
        ));

        usort($stages, static fn (VehicleStage $left, VehicleStage $right): int => strnatcasecmp($left->getName(), $right->getName()));

        return new JsonResponse(array_map(
            fn (VehicleStage $stage): array => $this->mapStage($engine, $stage),
            array_slice($stages, 0, 3)
        ));
    }

    private function mapStage(VehicleEngine $engine, VehicleStage $stage): array
    {
        $originalHp = $stage->getOriginalPs() ?? $engine->getHorsepower() ?? 0;
        $originalTorque = $stage->getOriginalNm() ?? $engine->getTorque() ?? 0;
        $tunedHp = $stage->getHp() ?? $originalHp;
        $tunedTorque = $stage->getNm() ?? $originalTorque;
        $price = $stage->getPrice();

        return [
            'stage' => $stage->getName(),
            'price' => null !== $price ? (float) $price : 0,
            'original_hp' => $originalHp,
            'tuned_hp' => $tunedHp,
            'original_torque' => $originalTorque,
            'tuned_torque' => $tunedTorque,
            'features' => $this->extractFeatures($stage),
        ];
    }

    /**
     * @return list<string>
     */
    private function extractFeatures(VehicleStage $stage): array
    {
        $description = strip_tags((string) ($stage->getDescription() ?? ''));
        $segments = preg_split('/[\r\n]+|[.!?]+/', $description) ?: [];
        $features = [];

        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ('' === $segment) {
                continue;
            }

            $features[] = $segment;
            if (count($features) >= 3) {
                break;
            }
        }

        if ([] !== $features) {
            return $features;
        }

        $stageName = mb_strtolower($stage->getName());
        if (str_contains($stageName, 'stage 1')) {
            return ['Softwareoptimierung ohne Hardware-Umbau', 'Spuerbar besseres Ansprechverhalten', 'Ausgewogene Alltags-Performance'];
        }

        if (str_contains($stageName, 'stage 2')) {
            return ['Optimiert fuer weitergehende Performance', 'Abstimmung fuer passende Hardware-Upgrades', 'Deutlich hoeheres Leistungsniveau'];
        }

        if (str_contains($stageName, 'stage 3')) {
            return ['Maximale Performance-Optimierung', 'Geeignet fuer umfangreiche Umbauten', 'Fokus auf Spitzenleistung'];
        }

        return ['Individuelle Optimierung', 'Passend fuer das gewaehlte Fahrzeug', 'Abgestimmt auf die vorhandene Motorbasis'];
    }

    private function resolveMediaPath(?string $path): ?string
    {
        $value = trim((string) $path);
        if ('' === $value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            return $value;
        }

        return '/' . ltrim($value, '/');
    }
}
