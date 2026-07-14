<?php

namespace Tfd\PartnerWizardBundle\Service;

use App\Entity\ShopProduct;
use App\Entity\VehicleType;
use App\Repository\VehicleTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardCombination;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardPackage;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardTool;

final class PartnerWizardRecommendationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VehicleTypeRepository $vehicleTypeRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function recommend(PartnerWizardClient $client, array $payload): array
    {
        $currentTool = (string) ($payload['currentTool'] ?? '');
        $vehicleTypeIds = array_map('intval', (array) ($payload['vehicleCategories'] ?? []));
        $services = $payload['services'] ?? [];
        $website = (string) ($payload['website'] ?? '');
        $goal = (string) ($payload['goal'] ?? '');
        $experience = (string) ($payload['experience'] ?? '');
        $mainVehicleTypeId = (int) ($payload['mainVehicleCategory'] ?? 0);
        $count = (int) ($payload['monthlyVehicleCount'] ?? 0);
        $desiredToolLevel = (string) ($payload['desiredToolLevel'] ?? '');

        $noTool = $client->isToolRequired() && ($currentTool === '' || $currentTool === 'none');
        $recommendationStatus = $noTool ? 'tool_required' : 'ready';
        $estimatedMonthlyRevenue = $count * $client->getDefaultVehiclePrice();

        $tools = $this->recommendTools($client, $vehicleTypeIds, $services, $desiredToolLevel);
        $package = $this->selectPackage($client, $recommendationStatus, $goal, $experience, $vehicleTypeIds, $count);
        if (!$noTool) {
            $package = $this->selectWebsitePackage($client, $website) ?? $package;
        }

        $toolDirection = $this->buildToolDirection($noTool, $tools, $vehicleTypeIds, $mainVehicleTypeId, $desiredToolLevel, $currentTool);

        return [
            'status' => $recommendationStatus,
            'packageKey' => $package?->getPackageKey() ?? ($noTool ? 'tool_starter' : 'workshop_slave_partner'),
            'packageTitle' => $package?->getTitle() ?? ($noTool ? 'Tool Starter' : 'Werkstatt / Slave Partner'),
            'packageText' => $package?->getDescription() ?? ($noTool
                ? 'Erst Tool-Beratung und Protokollprüfung, danach Händlerfreischaltung.'
                : 'Für Werkstätten und Händler mit Kundenkontakt und eigenem Tool.'),
            'packageFeatures' => $package?->getFeatures() ?? [],
            'recommendedTools' => $tools,
            'toolDirection' => $toolDirection,
            'nextStep' => $package?->getCtaLabel() ?? ($noTool ? 'Tool-Beratung anfragen' : 'Händleranfrage senden'),
            'estimatedMonthlyRevenue' => $estimatedMonthlyRevenue,
            'disclaimer' => $client->getToolDisclaimer(),
            'recommendedProducts' => $this->recommendedProducts($client),
        ];
    }

    private function recommendedProducts(PartnerWizardClient $client): array
    {
        $products = array_filter(
            $client->getRecommendedProducts()->toArray(),
            static fn (ShopProduct $product): bool => $product->isActive()
        );

        return array_values(array_map(
            fn (ShopProduct $product): array => $this->serializeShopProduct($product),
            $products
        ));
    }

    /**
     * @param int[] $vehicleTypeIds
     */
    private function recommendTools(PartnerWizardClient $client, array $vehicleTypeIds, array $services, string $desiredToolLevel): array
    {
        $vehicleTypeIds = array_values(array_unique(array_filter(
            array_map('intval', $vehicleTypeIds),
            static fn (int $id): bool => $id > 0
        )));

        $tools = $this->em->getRepository(PartnerWizardTool::class)->findBy(
            ['client' => $client, 'active' => true],
            ['priority' => 'ASC']
        );

        $combinationToolIds = $this->matchCombinationToolIds($client, $vehicleTypeIds, $services);

        $scored = [];
        foreach ($tools as $tool) {
            $score = 0;
            $toolVehicleTypeIds = $this->vehicleTypeIds($tool->getRecommendedForVehicleCategories()->toArray());
            foreach ($vehicleTypeIds as $vehicleTypeId) {
                if (in_array($vehicleTypeId, $toolVehicleTypeIds, true)) {
                    $score += 10;
                }
            }
            foreach ($services as $service) {
                if (in_array($service, $tool->getRecommendedForServices(), true)) {
                    $score += 3;
                }
            }
            if ($desiredToolLevel !== '' && $desiredToolLevel !== 'unknown' && $tool->getLevel() === $desiredToolLevel) {
                $score += 8;
            }
            if ($tool->getLevel() === 'universal') {
                $score += 2;
            }

            $matchedCombination = in_array($tool->getId(), $combinationToolIds, true);
            if ($matchedCombination) {
                $score += 100;
            }

            // A tool only fully matches if it covers every selected vehicle type, not just some of them.
            $fullVehicleMatch = array_diff($vehicleTypeIds, $toolVehicleTypeIds) === [];

            $scored[] = [
                'key' => $tool->getToolKey(),
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'level' => $tool->getLevel(),
                'score' => $score,
                'imageUrl' => $tool->getImageUrl(),
                'product' => $this->serializeProduct($tool),
                'fromCombination' => $matchedCombination,
                'fullVehicleMatch' => $fullVehicleMatch,
            ];
        }

        if ($vehicleTypeIds !== []) {
            $result = array_values(array_filter(
                $scored,
                fn (array $tool): bool => $tool['score'] > 0 && $tool['fullVehicleMatch']
            ));
        } else {
            $result = array_values(array_filter($scored, fn (array $tool): bool => $tool['score'] > 0));
            if ($result === []) {
                $result = $scored;
            }
        }

        foreach ($result as &$tool) {
            unset($tool['fullVehicleMatch']);
        }
        unset($tool);

        usort($result, fn ($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($result, 0, 4);
    }

    /**
     * @param int[] $vehicleTypeIds
     * @return int[] IDs of tools linked to the best-matching combination (subset match: all of
     * the combination's required categories/services must be present in the customer's selection).
     */
    private function matchCombinationToolIds(PartnerWizardClient $client, array $vehicleTypeIds, array $services): array
    {
        $combinations = $this->em->getRepository(PartnerWizardCombination::class)->findBy(
            ['client' => $client, 'active' => true],
            ['priority' => 'ASC']
        );

        $best = null;
        $bestMatchSize = -1;

        foreach ($combinations as $combination) {
            $requiredVehicleTypeIds = $this->vehicleTypeIds($combination->getVehicleCategories()->toArray());
            $requiredServices = $combination->getServices();

            if ($requiredVehicleTypeIds === [] && $requiredServices === []) {
                continue;
            }
            if (array_diff($requiredVehicleTypeIds, $vehicleTypeIds) !== []) {
                continue;
            }
            if (array_diff($requiredServices, $services) !== []) {
                continue;
            }

            $matchSize = count($requiredVehicleTypeIds) + count($requiredServices);
            if ($matchSize > $bestMatchSize) {
                $bestMatchSize = $matchSize;
                $best = $combination;
            }
        }

        if (!$best instanceof PartnerWizardCombination) {
            return [];
        }

        return array_map(static fn (PartnerWizardTool $tool) => $tool->getId(), $best->getTools()->toArray());
    }

    private function serializeProduct(PartnerWizardTool $tool): ?array
    {
        $product = $tool->getShopProduct();
        if (!$product instanceof ShopProduct || !$product->isActive()) {
            return null;
        }

        return $this->serializeShopProduct($product);
    }

    private function serializeShopProduct(ShopProduct $product): array
    {
        return [
            'uid' => $product->getUid(),
            'title' => $product->getTitle(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'specialPrice' => $this->resolveSpecialPrice($product),
            'slug' => $product->getSlug(),
            'imageUrl' => $this->firstProductImageUrl($product),
        ];
    }

    private function resolveSpecialPrice(ShopProduct $product): ?string
    {
        $specialPrice = (float) $product->getSpecialPrice();

        return $specialPrice > 0 ? $product->getSpecialPrice() : null;
    }

    private function firstProductImageUrl(ShopProduct $product): ?string
    {
        foreach ($product->getMediaAssets() as $asset) {
            $path = trim((string) $asset->getFilePath());
            if ('' === $path) {
                continue;
            }

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            $request = $this->requestStack->getCurrentRequest();
            $baseUrl = null !== $request ? $request->getSchemeAndHttpHost() : '';

            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }

        return null;
    }

    /**
     * @param int[] $vehicleTypeIds
     */
    private function selectWebsitePackage(PartnerWizardClient $client, string $website): ?PartnerWizardPackage
    {
        $websiteKey = match ($website) {
            'full_package', 'website_only', 'configurator_only' => $website,
            default => '',
        };

        if ('' === $websiteKey) {
            return null;
        }

        foreach ($this->em->getRepository(PartnerWizardPackage::class)->findBy(
            ['client' => $client, 'active' => true],
            ['priority' => 'ASC']
        ) as $package) {
            if ($package->getPackageKey() === $websiteKey) {
                return $package;
            }
        }

        return null;
    }

    /**
     * @param int[] $vehicleTypeIds
     */
    private function selectPackage(
        PartnerWizardClient $client,
        string $status,
        string $goal,
        string $experience,
        array $vehicleTypeIds,
        int $count
    ): ?PartnerWizardPackage {
        $packages = $this->em->getRepository(PartnerWizardPackage::class)->findBy(
            ['client' => $client, 'active' => true],
            ['priority' => 'ASC']
        );

        $best = null;
        $bestScore = -999;

        foreach ($packages as $package) {
            if ($package->getRequiredStatus() !== 'any' && $package->getRequiredStatus() !== $status) {
                continue;
            }
            if ($count < $package->getMinVehiclesPerMonth() || $count > $package->getMaxVehiclesPerMonth()) {
                continue;
            }

            $score = 0;

            if ($package->getRequiredStatus() === $status) {
                $score += 20;
            }
            if ($package->getRequiredGoals() !== [] && in_array($goal, $package->getRequiredGoals(), true)) {
                $score += 12;
            }
            if ($package->getRequiredExperience() !== [] && in_array($experience, $package->getRequiredExperience(), true)) {
                $score += 12;
            }

            $packageVehicleTypeIds = $this->vehicleTypeIds($package->getRecommendedVehicleCategories()->toArray());
            foreach ($vehicleTypeIds as $vehicleTypeId) {
                if (in_array($vehicleTypeId, $packageVehicleTypeIds, true)) {
                    $score += 4;
                }
            }

            if ($package->getPriority() < 100) {
                $score += (100 - $package->getPriority()) / 10;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $package;
            }
        }

        return $best;
    }

    /**
     * @param int[] $vehicleTypeIds
     */
    private function buildToolDirection(bool $noTool, array $tools, array $vehicleTypeIds, int $mainVehicleTypeId, string $desiredToolLevel, string $currentTool): string
    {
        $slugs = $this->vehicleTypeSlugs($vehicleTypeIds);
        $mainSlug = $mainVehicleTypeId > 0 ? ($this->vehicleTypeSlugs([$mainVehicleTypeId])[0] ?? '') : '';

        $heavy = array_intersect($slugs, ['lkw', 'agrar']) !== [] || $mainSlug === 'lkw' || $mainSlug === 'agrar';
        $marine = array_intersect($slugs, ['boat', 'jetski']) !== [] || $mainSlug === 'boat' || $mainSlug === 'jetski';

        $activeCount = count($this->vehicleTypeRepository->findBy(['active' => true]));
        $mixed = $activeCount > 0 && count($slugs) >= $activeCount;

        $direction = 'Universelles Tool für PKW prüfen.';
        if ($heavy) {
            $direction = 'Tool mit LKW- oder Agrar-Abdeckung prüfen.';
        }
        if ($marine) {
            $direction = 'Marine- und JetSki-Protokolle gezielt prüfen.';
        }
        if ($mixed) {
            $direction = 'Modulares Tool mit breiter Fahrzeugabdeckung und passenden Protokollpaketen prüfen.';
        }

        $toolNames = array_map(fn (array $tool) => $tool['name'], $tools);
        $toolText = $toolNames !== [] ? ' Empfohlene Tools/Richtungen: ' . implode(', ', $toolNames) . '.' : '';

        if ($noTool) {
            $level = match ($desiredToolLevel) {
                'slave' => 'Slave-Tool',
                'master' => 'Master-Tool',
                default => 'Slave- oder Master-Tool',
            };

            return 'Da noch kein eigenes Tool vorhanden ist, ist vor der Händlerfreischaltung eine Tool-Auswahl erforderlich. Empfohlene Richtung: ' . $level . '. ' . $direction . $toolText;
        }

        return 'Vorhandenes Tool: ' . $currentTool . '. Bitte Protokolle und Fahrzeugabdeckung prüfen. ' . $direction . $toolText;
    }

    /**
     * @param VehicleType[] $vehicleTypes
     * @return int[]
     */
    private function vehicleTypeIds(array $vehicleTypes): array
    {
        return array_map(static fn (VehicleType $vehicleType) => $vehicleType->getId(), $vehicleTypes);
    }

    /**
     * @param int[] $vehicleTypeIds
     * @return string[]
     */
    private function vehicleTypeSlugs(array $vehicleTypeIds): array
    {
        if ($vehicleTypeIds === []) {
            return [];
        }

        $vehicleTypes = $this->vehicleTypeRepository->findBy(['id' => $vehicleTypeIds]);

        return array_values(array_filter(array_map(
            static fn (VehicleType $vehicleType) => $vehicleType->getSlug(),
            $vehicleTypes
        )));
    }
}
