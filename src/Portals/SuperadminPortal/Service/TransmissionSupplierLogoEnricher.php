<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Service;

use App\Repository\EcuManufacturerRepository;

/**
 * Ergänzt in den Getriebe-Wissensdateien (BMW, Mercedes) zu jedem Steuergerät-Lieferanten das Logo
 * aus den Steuergerät-Herstellern der Fahrzeugdatenbank (`supplier_logo`, öffentlicher Pfad oder null).
 */
final class TransmissionSupplierLogoEnricher
{
    public function __construct(private readonly EcuManufacturerRepository $ecuManufacturerRepository)
    {
    }

    /**
     * @param array<string, mixed> $database
     *
     * @return array<string, mixed>
     */
    public function enrich(array $database): array
    {
        $logos = [];
        foreach ($this->ecuManufacturerRepository->findAll() as $manufacturer) {
            $logo = trim((string) $manufacturer->getLogo());
            $key = mb_strtolower(trim($manufacturer->getName()));
            if ('' !== $logo && '' !== $key && !isset($logos[$key])) {
                $logos[$key] = '/' . ltrim($logo, '/');
            }
        }

        foreach ($database['control_units'] ?? [] as $index => $controlUnit) {
            $supplier = mb_strtolower(trim((string) ($controlUnit['supplier'] ?? '')));
            $logo = $logos[$supplier] ?? null;
            if (null === $logo && '' !== $supplier) {
                // „Siemens VDO“ -> Logo von „Siemens“
                $logo = $logos[explode(' ', $supplier)[0]] ?? null;
            }
            $database['control_units'][$index]['supplier_logo'] = $logo;
        }

        return $database;
    }
}
