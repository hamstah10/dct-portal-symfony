<?php

namespace App\Service\ToolNews;

use App\Entity\DetectedEcu;
use App\Entity\Ecu;
use App\Entity\ToolNewsItem;
use App\Entity\VehicleCoverageMatch;
use App\Entity\VehicleEngine;
use App\Entity\VehiclePlatformAlias;
use App\Repository\DetectedEcuRepository;
use App\Repository\EcuRepository;
use App\Repository\ToolProviderRepository;
use App\Repository\VehicleCoverageMatchRepository;
use App\Repository\VehicleEngineRepository;
use App\Repository\VehiclePlatformAliasRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Matches DetectedEcu records (recognized from tool provider news) against
 * the existing, read-only vehicle database to produce coverage suggestions.
 *
 * This service never modifies the vehicle database and never auto-publishes
 * anything — it only ever creates/updates VehicleCoverageMatch suggestion
 * rows, which must be reviewed manually in EasyAdmin.
 */
final class VehicleMatcher
{
    private const MIN_SCORE_TO_STORE = 0.35;

    private const SCORE_ECU_NAME = 0.35;
    private const SCORE_ECU_FAMILY = 0.20;
    private const SCORE_MCU = 0.15;
    private const SCORE_VEHICLE_HINT = 0.15;
    private const SCORE_MANUFACTURER = 0.10;
    private const SCORE_PROTOCOL_METHOD = 0.05;

    private const LEVEL_EXACT_THRESHOLD = 0.85;
    private const LEVEL_STRONG_THRESHOLD = 0.70;
    private const LEVEL_LIKELY_THRESHOLD = 0.50;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DetectedEcuRepository $detectedEcuRepository,
        private readonly EcuRepository $ecuRepository,
        private readonly VehicleEngineRepository $vehicleEngineRepository,
        private readonly VehicleCoverageMatchRepository $vehicleCoverageMatchRepository,
        private readonly VehiclePlatformAliasRepository $vehiclePlatformAliasRepository,
        private readonly ToolProviderRepository $toolProviderRepository,
    ) {
    }

    /**
     * Matches a single DetectedEcu against the vehicle database and persists
     * the resulting VehicleCoverageMatch suggestions (skipping duplicates).
     *
     * @return list<array{
     *     vehicleBrand: ?string,
     *     vehicleSeries: ?string,
     *     vehicleModel: ?string,
     *     vehicleEngine: ?string,
     *     vehicleEngineId: ?int,
     *     ecuName: ?string,
     *     mcu: ?string,
     *     matchScore: float,
     *     matchLevel: string,
     *     matchReason: string,
     *     matchData: array<string, mixed>
     * }>
     */
    public function matchDetectedEcu(DetectedEcu $detectedEcu, bool $force = false): array
    {
        if ($force) {
            $this->vehicleCoverageMatchRepository->removeAllForDetectedEcu($detectedEcu);
            $this->entityManager->flush();
        }

        $candidates = $this->collectCandidateEngines($detectedEcu);
        if ([] === $candidates) {
            return [];
        }

        $results = [];
        $seenDedupKeys = [];

        foreach ($candidates as $candidateEntry) {
            $engine = $candidateEntry['engine'];
            $alias = $candidateEntry['alias'];

            $evaluation = $this->evaluateCandidate($detectedEcu, $engine, $alias);
            if ($evaluation['score'] < self::MIN_SCORE_TO_STORE) {
                continue;
            }

            $dedupKey = $this->buildDedupKey($engine);
            if (isset($seenDedupKeys[$dedupKey])) {
                continue;
            }
            $seenDedupKeys[$dedupKey] = true;

            if (null !== $this->findExistingMatch($detectedEcu, $engine)) {
                continue;
            }

            $match = $this->buildMatchEntity($detectedEcu, $engine, $evaluation);
            $this->entityManager->persist($match);

            $results[] = [
                'vehicleBrand' => $match->getVehicleBrand(),
                'vehicleSeries' => $match->getVehicleSeries(),
                'vehicleModel' => $match->getVehicleModel(),
                'vehicleEngine' => $match->getVehicleEngine(),
                'vehicleEngineId' => $match->getVehicleEngineId(),
                'ecuName' => $match->getEcuName(),
                'mcu' => $match->getMcu(),
                'matchScore' => $match->getMatchScore(),
                'matchLevel' => $match->getMatchLevel(),
                'matchReason' => (string) $match->getMatchReason(),
                'matchData' => $match->getMatchData() ?? [],
            ];
        }

        $detectedEcu->setStatus([] !== $results ? DetectedEcu::STATUS_REVIEW : DetectedEcu::STATUS_NEW);

        $this->entityManager->flush();

        return $results;
    }

    /**
     * Matches all DetectedEcu records, optionally scoped to a single provider.
     *
     * @return int number of DetectedEcu records processed
     */
    public function matchAllNew(?string $providerSlug = null, bool $force = false): int
    {
        $criteria = [];

        if (null !== $providerSlug && '' !== trim($providerSlug)) {
            $provider = $this->toolProviderRepository->findOneBySlug(trim($providerSlug));
            if (null === $provider) {
                return 0;
            }
            $criteria['provider'] = $provider;
        }

        $criteria['status'] = [DetectedEcu::STATUS_NEW, DetectedEcu::STATUS_REVIEW];

        $detectedEcus = $this->detectedEcuRepository->findBy($criteria);

        $processed = 0;
        foreach ($detectedEcus as $detectedEcu) {
            $this->matchDetectedEcu($detectedEcu, $force);
            ++$processed;
        }

        return $processed;
    }

    /**
     * @return list<array{engine: VehicleEngine, alias: ?VehiclePlatformAlias}>
     */
    private function collectCandidateEngines(DetectedEcu $detectedEcu): array
    {
        /** @var array<int, array{engine: VehicleEngine, alias: ?VehiclePlatformAlias}> $byEngineId */
        $byEngineId = [];

        // Signal 1: candidates found via ECU name/family matching in the Ecu table.
        $ecuTerms = array_values(array_filter([
            $detectedEcu->getEcuName(),
            $detectedEcu->getEcuFamily(),
        ], static fn (?string $term): bool => null !== $term && '' !== trim($term)));

        if ([] !== $ecuTerms) {
            $ecuCandidates = $this->ecuRepository->findCandidatesByTerms($ecuTerms);
            foreach ($ecuCandidates as $ecu) {
                foreach ($ecu->getVehicleEngines() as $engine) {
                    $engineId = $engine->getId();
                    if (null === $engineId || isset($byEngineId[$engineId])) {
                        continue;
                    }
                    $byEngineId[$engineId] = ['engine' => $engine, 'alias' => null];
                }

                // Also consider the direct Ecu -> VehicleEngine relation (legacy single-assignment field).
                $directEngine = $ecu->getVehicleEngine();
                if (null !== $directEngine) {
                    $engineId = $directEngine->getId();
                    if (null !== $engineId && !isset($byEngineId[$engineId])) {
                        $byEngineId[$engineId] = ['engine' => $directEngine, 'alias' => null];
                    }
                }
            }
        }

        // Signal 2: candidates found via a free-text vehicle hint (brand name), plus platform aliases.
        $vehicleHint = trim((string) $detectedEcu->getVehicleHint());
        if ('' !== $vehicleHint) {
            $hintBrand = $this->extractLeadingBrandWord($vehicleHint);

            if (null !== $hintBrand) {
                $brandEngines = $this->vehicleEngineRepository->findActiveCandidatesByBrandTerms([$hintBrand]);
                foreach ($brandEngines as $engine) {
                    $engineId = $engine->getId();
                    if (null === $engineId || isset($byEngineId[$engineId])) {
                        continue;
                    }
                    $byEngineId[$engineId] = ['engine' => $engine, 'alias' => null];
                }

                foreach ($this->vehiclePlatformAliasRepository->findActiveBySourceBrand($hintBrand) as $platformAlias) {
                    $aliasEngines = $this->vehicleEngineRepository->findActiveCandidatesByBrandTerms([(string) $platformAlias->getAliasBrand()]);
                    foreach ($aliasEngines as $engine) {
                        $engineId = $engine->getId();
                        if (null === $engineId || isset($byEngineId[$engineId])) {
                            continue;
                        }
                        $byEngineId[$engineId] = ['engine' => $engine, 'alias' => $platformAlias];
                    }
                }
            }
        }

        return array_values($byEngineId);
    }

    /**
     * Extracts the first capitalized word of a free-text vehicle hint as the
     * probable brand name, e.g. "Fiat Ducato" -> "Fiat".
     */
    private function extractLeadingBrandWord(string $vehicleHint): ?string
    {
        if (preg_match('/^([A-Za-zÀ-ÿ]+)/u', trim($vehicleHint), $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @return array{
     *     score: float,
     *     level: string,
     *     reason: string,
     *     signals: array<string, bool>,
     *     scoreParts: array<string, float>,
     *     alias: ?VehiclePlatformAlias
     * }
     */
    private function evaluateCandidate(DetectedEcu $detectedEcu, VehicleEngine $engine, ?VehiclePlatformAlias $alias): array
    {
        $signals = [
            'ecuName' => false,
            'ecuFamily' => false,
            'mcu' => false,
            'vehicleHint' => false,
            'manufacturer' => false,
            'protocol' => false,
        ];
        $scoreParts = [
            'ecuName' => 0.0,
            'ecuFamily' => 0.0,
            'mcu' => 0.0,
            'vehicleHint' => 0.0,
            'manufacturer' => 0.0,
            'protocol' => 0.0,
        ];
        $reasonParts = [];

        $engineEcuNames = [];
        $engineEcuTypes = [];
        $engineManufacturers = [];
        foreach ($engine->getEcuRelations() as $relatedEcu) {
            $engineEcuNames[] = mb_strtolower($relatedEcu->getName());
            if (null !== $relatedEcu->getType()) {
                $engineEcuTypes[] = mb_strtolower($relatedEcu->getType());
            }
            $manufacturerName = $relatedEcu->getManufacturer()?->getName();
            if (null !== $manufacturerName) {
                $engineManufacturers[] = mb_strtolower($manufacturerName);
            }
        }

        $ecuName = mb_strtolower(trim((string) $detectedEcu->getEcuName()));
        if ('' !== $ecuName && $this->containsMatch($engineEcuNames, $ecuName)) {
            $signals['ecuName'] = true;
            $scoreParts['ecuName'] = self::SCORE_ECU_NAME;
            $reasonParts[] = sprintf('ECU-Name "%s" stimmt mit Motor-ECU überein.', $detectedEcu->getEcuName());
        }

        $ecuFamily = mb_strtolower(trim((string) $detectedEcu->getEcuFamily()));
        if ('' !== $ecuFamily && !$signals['ecuName'] && $this->containsPartialMatch(array_merge($engineEcuNames, $engineEcuTypes), $ecuFamily)) {
            $signals['ecuFamily'] = true;
            $scoreParts['ecuFamily'] = self::SCORE_ECU_FAMILY;
            $reasonParts[] = sprintf('ECU-Familie "%s" im Motor-ECU-Typ gefunden.', $detectedEcu->getEcuFamily());
        }

        $mcu = mb_strtolower(trim((string) $detectedEcu->getMcu()));
        if ('' !== $mcu && $this->containsPartialMatch($engineEcuTypes, $mcu)) {
            $signals['mcu'] = true;
            $scoreParts['mcu'] = self::SCORE_MCU;
            $reasonParts[] = sprintf('MCU "%s" im ECU-Typ des Motors gefunden.', $detectedEcu->getMcu());
        }

        $vehicleHint = trim((string) $detectedEcu->getVehicleHint());
        if ('' !== $vehicleHint) {
            if (null !== $alias) {
                $signals['vehicleHint'] = true;
                $scoreParts['vehicleHint'] = self::SCORE_VEHICLE_HINT * $alias->getScoreWeight();
                $reasonParts[] = sprintf(
                    'Plattform-Alias: Fahrzeug-Hinweis "%s" (%s) wird auch von "%s" %s geteilt.',
                    $vehicleHint,
                    (string) $alias->getSourceBrand(),
                    $alias->getAliasBrand(),
                    null !== $alias->getAliasModel() ? $alias->getAliasModel() : ''
                );
            } elseif ($this->vehicleHintMatchesEngine($vehicleHint, $engine)) {
                $signals['vehicleHint'] = true;
                $scoreParts['vehicleHint'] = self::SCORE_VEHICLE_HINT;
                $reasonParts[] = sprintf('Fahrzeug-Hinweis "%s" stimmt mit Marke/Modell überein.', $vehicleHint);
            }
        }

        $manufacturer = mb_strtolower(trim((string) $detectedEcu->getManufacturer()));
        if ('' !== $manufacturer && $this->containsPartialMatch($engineManufacturers, $manufacturer)) {
            $signals['manufacturer'] = true;
            $scoreParts['manufacturer'] = self::SCORE_MANUFACTURER;
            $reasonParts[] = sprintf('ECU-Hersteller "%s" stimmt überein.', $detectedEcu->getManufacturer());
        }

        $protocolOrMethod = trim((string) $detectedEcu->getProtocol()) . ' ' . trim((string) $detectedEcu->getReadMethod());
        if ('' !== trim($protocolOrMethod) && ($signals['ecuName'] || $signals['ecuFamily'] || $signals['mcu'])) {
            // Protocol/method alone is too weak a signal, but reinforces an already-plausible ECU match.
            $signals['protocol'] = true;
            $scoreParts['protocol'] = self::SCORE_PROTOCOL_METHOD;
            $reasonParts[] = sprintf('Methode/Protokoll "%s" passt zum ECU-Kontext.', trim($protocolOrMethod));
        }

        $score = array_sum($scoreParts);
        $score = min(1.0, $score);

        if ([] === $reasonParts) {
            $reasonParts[] = 'Schwacher Treffer ohne eindeutiges Signal.';
        }

        return [
            'score' => $score,
            'level' => $this->resolveLevel($score),
            'reason' => implode(' ', $reasonParts),
            'signals' => $signals,
            'scoreParts' => $scoreParts,
            'alias' => $alias,
        ];
    }

    private function vehicleHintMatchesEngine(string $vehicleHint, VehicleEngine $engine): bool
    {
        $hintLower = mb_strtolower($vehicleHint);
        $brandName = mb_strtolower($engine->getBrandGroup());
        $modelName = mb_strtolower($engine->getModelGroup());
        $seriesName = mb_strtolower($engine->getSeriesGroup());

        if ('' !== $brandName && str_contains($hintLower, $brandName)) {
            return true;
        }

        if ('' !== $modelName && str_contains($hintLower, $modelName)) {
            return true;
        }

        return '' !== $seriesName && str_contains($hintLower, $seriesName);
    }

    /**
     * @param list<string> $haystacks
     */
    private function containsMatch(array $haystacks, string $needle): bool
    {
        return in_array($needle, $haystacks, true);
    }

    /**
     * @param list<string> $haystacks
     */
    private function containsPartialMatch(array $haystacks, string $needle): bool
    {
        foreach ($haystacks as $haystack) {
            if ('' !== $haystack && (str_contains($haystack, $needle) || str_contains($needle, $haystack))) {
                return true;
            }
        }

        return false;
    }

    private function resolveLevel(float $score): string
    {
        if ($score >= self::LEVEL_EXACT_THRESHOLD) {
            return VehicleCoverageMatch::LEVEL_EXACT;
        }

        if ($score >= self::LEVEL_STRONG_THRESHOLD) {
            return VehicleCoverageMatch::LEVEL_STRONG;
        }

        if ($score >= self::LEVEL_LIKELY_THRESHOLD) {
            return VehicleCoverageMatch::LEVEL_LIKELY;
        }

        return VehicleCoverageMatch::LEVEL_POSSIBLE;
    }

    private function buildDedupKey(VehicleEngine $engine): string
    {
        return 'engine:' . $engine->getId();
    }

    private function findExistingMatch(DetectedEcu $detectedEcu, VehicleEngine $engine): ?VehicleCoverageMatch
    {
        $engineId = $engine->getId();
        if (null === $engineId) {
            return null;
        }

        return $this->vehicleCoverageMatchRepository->findOneByEngineId($detectedEcu, $engineId);
    }

    /**
     * @param array{
     *     score: float,
     *     level: string,
     *     reason: string,
     *     signals: array<string, bool>,
     *     scoreParts: array<string, float>,
     *     alias: ?VehiclePlatformAlias
     * } $evaluation
     */
    private function buildMatchEntity(DetectedEcu $detectedEcu, VehicleEngine $engine, array $evaluation): VehicleCoverageMatch
    {
        $model = $engine->getVehicleModel();
        $series = $model?->getVehicleSeries();
        $brand = $series?->getVehicleBrand();
        $vehicleType = $brand?->getVehicleType();

        $newsItem = $detectedEcu->getNewsItem();

        $matchData = [
            'signals' => $evaluation['signals'],
            'scoreParts' => $evaluation['scoreParts'],
            'source' => [
                'detectedEcuId' => $detectedEcu->getId(),
                'toolNewsItemId' => $newsItem?->getId(),
            ],
        ];

        if (null !== $evaluation['alias']) {
            $alias = $evaluation['alias'];
            $matchData['alias'] = [
                'used' => true,
                'source' => $alias->getSourceBrand(),
                'target' => $alias->getAliasBrand(),
                'weight' => $alias->getScoreWeight(),
            ];
        }

        $match = (new VehicleCoverageMatch())
            ->setDetectedEcu($detectedEcu)
            ->setNewsItem($newsItem)
            ->setProvider($detectedEcu->getProvider())
            ->setVehicleType($vehicleType?->getSlug() ?? $vehicleType?->getName())
            ->setVehicleBrand($brand?->getName())
            ->setVehicleSeries($series?->getName())
            ->setVehicleModel($model?->getName())
            ->setVehicleEngine($engine->getName())
            ->setVehicleEngineId($engine->getId())
            ->setVehicleModelId($model?->getId())
            ->setVehicleSeriesId($series?->getId())
            ->setVehicleBrandId($brand?->getId())
            ->setEngineCode($engine->getEngineCode())
            ->setEcuName($detectedEcu->getEcuName())
            ->setEcuFamily($detectedEcu->getEcuFamily())
            ->setMcu($detectedEcu->getMcu())
            ->setFuel($engine->getFuel())
            ->setHp($engine->getHorsepower())
            ->setNm($engine->getTorque())
            ->setMatchScore($evaluation['score'])
            ->setMatchLevel($evaluation['level'])
            ->setMatchReason($evaluation['reason'])
            ->setMatchData($matchData)
            ->setStatus(VehicleCoverageMatch::STATUS_NEW);

        return $match;
    }
}
