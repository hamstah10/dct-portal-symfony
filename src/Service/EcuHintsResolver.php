<?php

namespace App\Service;

use App\Entity\Ecu;
use App\Entity\EcuOptionHint;
use App\Entity\EcuOptionMaster;
use App\Repository\EcuOptionHintRepository;

class EcuHintsResolver
{
    public function __construct(
        private readonly EcuOptionHintRepository $hintRepository,
    ) {}

    /**
     * Returns hints grouped by EcuOptionMaster for a given ECU.
     *
     * @return array<int, array{master: EcuOptionMaster, hints: EcuOptionHint[]}>
     */
    public function resolveForEcu(Ecu $ecu): array
    {
        $manufacturerName = $ecu->getManufacturer()?->getName() ?? '';
        $ecuType = $ecu->getName();

        if ('' === $manufacturerName || '' === $ecuType) {
            return [];
        }

        $brandNames = [];
        $ecuTypes = [$ecuType];
        foreach ($ecu->getVehicleEngines() as $engine) {
            $brand = $engine->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand()?->getName();
            if (null !== $brand && '' !== $brand) {
                $brandNames[] = $brand;
            }

            foreach ($engine->getEcuRelations() as $relatedEcu) {
                if ($relatedEcu === $ecu) {
                    continue;
                }

                if ($relatedEcu->getManufacturer()?->getId() !== $ecu->getManufacturer()?->getId()) {
                    continue;
                }

                $relatedEcuType = $relatedEcu->getName();
                if ('' !== $relatedEcuType) {
                    $ecuTypes[] = $relatedEcuType;
                }
            }
        }
        $brandNames = array_unique($this->expandBrandAliases($brandNames));
        $ecuTypes = array_unique($ecuTypes);

        $groups = [];
        foreach ($ecu->getOptions() as $option) {
            $master = $option->getMasterOption();
            if (null === $master || null === $master->getModuleSlug()) {
                continue;
            }

            $masterId = $master->getId();
            if (isset($groups[$masterId])) {
                continue;
            }

            $hints = $this->filterVisibleHints($this->hintRepository->findMatchingForEcuOption($master->getModuleSlug(), $manufacturerName, $ecuType, $brandNames));
            if ([] === $hints) {
                $fallbackHints = [];
                foreach ($ecuTypes as $candidateEcuType) {
                    if ($candidateEcuType === $ecuType) {
                        continue;
                    }

                    foreach ($this->filterVisibleHints($this->hintRepository->findMatchingForEcuOption($master->getModuleSlug(), $manufacturerName, $candidateEcuType, $brandNames)) as $hint) {
                        $hintId = $hint->getId();
                        if (null !== $hintId) {
                            $fallbackHints[$hintId] = $hint;
                        }
                    }
                }
                $hints = array_values($fallbackHints);
            }

            if ([] !== $hints) {
                $groups[$masterId] = ['master' => $master, 'hints' => $hints];
            }
        }

        return array_values($groups);
    }

    /**
     * PSA group: Citroën, Peugeot and DS are stored as "PSA (CITROEN/PEUGEOT/DS)" in hint data.
     *
     * @param string[] $brandNames
     * @return string[]
     */
    private function expandBrandAliases(array $brandNames): array
    {
        $psaMembers = ['citroën', 'citroen', 'peugeot', 'ds'];
        $hasPsa = false;
        foreach ($brandNames as $name) {
            if (in_array(strtolower($name), $psaMembers, true)) {
                $hasPsa = true;
                break;
            }
        }

        if ($hasPsa) {
            $brandNames[] = 'PSA (CITROEN/PEUGEOT/DS)';
        }

        return $brandNames;
    }

    /**
     * @param EcuOptionHint[] $hints
     * @return EcuOptionHint[]
     */
    private function filterVisibleHints(array $hints): array
    {
        return array_values(array_filter($hints, static function (EcuOptionHint $hint): bool {
            $notes = trim((string) ($hint->getNotesDe() ?? $hint->getNotes() ?? ''));

            return false === stripos($notes, '*BETA*');
        }));
    }
}
