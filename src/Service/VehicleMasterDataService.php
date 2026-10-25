<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\GearboxDtcCode;
use App\Entity\GearboxStageInfo;
use App\Entity\GearboxStageOption;
use App\Entity\ToolManufacturer;
use App\Entity\ToolMethode;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Stammdaten der Fahrzeugdatenbank (global, nicht pool-gebunden).
 *
 * Fehler-Mapping fuer den Controller:
 *  - InvalidArgumentException -> 422 (Pflichtfeld fehlt)
 *  - OutOfBoundsException     -> 404 (Eintrag nicht gefunden)
 *  - LogicException           -> 409 (Eintrag wird noch verwendet)
 */
final class VehicleMasterDataService
{
    /**
     * Felder: type = string (Pflicht, nicht leer) | nullable (leer -> null) | int | bool
     *
     * @var array<string, array{class: class-string, label: string, fields: array<string, array{type: string}>}>
     */
    public const RESOURCES = [
        'tool-manufacturers' => [
            'class' => ToolManufacturer::class,
            'label' => 'Tool-Hersteller',
            'fields' => [
                'name' => ['type' => 'string'],
                'beschreibung' => ['type' => 'nullable'],
                'adresse' => ['type' => 'nullable'],
                'plz' => ['type' => 'nullable'],
                'stadt' => ['type' => 'nullable'],
                'land' => ['type' => 'nullable'],
                'telefon' => ['type' => 'nullable'],
                'email' => ['type' => 'nullable'],
                'active' => ['type' => 'bool'],
            ],
        ],
        'tool-methodes' => [
            'class' => ToolMethode::class,
            'label' => 'Tool-Methode',
            'fields' => [
                'name' => ['type' => 'string'],
                'beschreibung' => ['type' => 'nullable'],
                'active' => ['type' => 'bool'],
            ],
        ],
        'gearbox-dtc-codes' => [
            'class' => GearboxDtcCode::class,
            'label' => 'DTC-Code',
            'fields' => [
                'obdCode' => ['type' => 'string'],
                'vagCode' => ['type' => 'nullable'],
                'description' => ['type' => 'string'],
                'sorting' => ['type' => 'int'],
                'active' => ['type' => 'bool'],
            ],
        ],
        'gearbox-stage-infos' => [
            'class' => GearboxStageInfo::class,
            'label' => 'Getriebe-Stufe',
            'fields' => [
                'name' => ['type' => 'string'],
                'description' => ['type' => 'nullable'],
                'sorting' => ['type' => 'int'],
                'active' => ['type' => 'bool'],
            ],
        ],
        'gearbox-stage-options' => [
            'class' => GearboxStageOption::class,
            'label' => 'Getriebe-Option',
            'fields' => [
                'gearboxName' => ['type' => 'string'],
                'category' => ['type' => 'string'],
                'categoryDescription' => ['type' => 'nullable'],
                'name' => ['type' => 'string'],
                'nameEn' => ['type' => 'string'],
                'fahrbarkeit' => ['type' => 'nullable'],
                'sorting' => ['type' => 'int'],
                'active' => ['type' => 'bool'],
            ],
        ],
    ];

    private PropertyAccessorInterface $accessor;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public static function isResource(string $resource): bool
    {
        return array_key_exists($resource, self::RESOURCES);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(string $resource): array
    {
        $config = $this->config($resource);
        $items = $this->entityManager->getRepository($config['class'])->findBy([], ['id' => 'ASC']);

        return array_map(fn (object $entity): array => $this->serialize($resource, $entity), $items);
    }

    /**
     * Legt an (id <= 0) oder aktualisiert. Fehlende Schluessel bei Update bleiben unveraendert.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function save(string $resource, array $payload): array
    {
        $config = $this->config($resource);
        $id = (int) ($payload['id'] ?? 0);

        if ($id > 0) {
            $entity = $this->entityManager->find($config['class'], $id);
            if (!is_object($entity)) {
                throw new \OutOfBoundsException($config['label'] . ' nicht gefunden.');
            }
        } else {
            $entity = new ($config['class'])();
            $this->entityManager->persist($entity);
        }

        foreach ($config['fields'] as $key => $definition) {
            if ($id > 0 && !array_key_exists($key, $payload)) {
                continue;
            }

            // Neue Datensaetze sind standardmaessig aktiv, sofern nichts anderes uebergeben wird.
            $raw = array_key_exists($key, $payload) ? $payload[$key] : ($key === 'active' ? true : null);
            $this->accessor->setValue($entity, $key, $this->normalize($key, $definition['type'], $raw, $config['label']));
        }

        if ($resource === 'tool-methodes' && array_key_exists('toolManufacturerIds', $payload)) {
            $this->syncToolManufacturers($entity, $payload['toolManufacturerIds']);
        }

        $this->entityManager->flush();

        return $this->serialize($resource, $entity);
    }

    public function delete(string $resource, int $id): void
    {
        $config = $this->config($resource);
        $entity = $this->entityManager->find($config['class'], $id);
        if (!is_object($entity)) {
            throw new \OutOfBoundsException($config['label'] . ' nicht gefunden.');
        }

        if ($entity instanceof ToolManufacturer && $entity->getEcus()->count() > 0) {
            throw new \LogicException(sprintf('%s wird von %d ECU(s) verwendet.', $config['label'], $entity->getEcus()->count()));
        }

        $this->entityManager->remove($entity);

        try {
            $this->entityManager->flush();
        } catch (ForeignKeyConstraintViolationException) {
            throw new \LogicException($config['label'] . ' wird an anderer Stelle noch verwendet und kann nicht geloescht werden.');
        }
    }

    /**
     * @return array{class: class-string, label: string, fields: array<string, array{type: string}>}
     */
    private function config(string $resource): array
    {
        if (!self::isResource($resource)) {
            throw new \OutOfBoundsException('Unbekannte Stammdaten-Ressource.');
        }

        return self::RESOURCES[$resource];
    }

    private function normalize(string $key, string $type, mixed $value, string $label): mixed
    {
        return match ($type) {
            'string' => $this->requireString($key, $value, $label),
            'nullable' => $this->nullableString($value),
            'int' => (int) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value,
            default => throw new \LogicException('Unbekannter Feldtyp: ' . $type),
        };
    }

    private function requireString(string $key, mixed $value, string $label): string
    {
        $text = is_scalar($value) ? trim((string) $value) : '';
        if ($text === '') {
            throw new \InvalidArgumentException(sprintf('%s: Feld "%s" ist erforderlich.', $label, $key));
        }

        return $text;
    }

    private function nullableString(mixed $value): ?string
    {
        $text = is_scalar($value) ? trim((string) $value) : '';

        return $text === '' ? null : $text;
    }

    /**
     * @param mixed $ids
     */
    private function syncToolManufacturers(object $method, mixed $ids): void
    {
        if (!$method instanceof ToolMethode) {
            return;
        }

        $wanted = is_array($ids) ? array_map('intval', $ids) : [];
        $wanted = array_values(array_unique(array_filter($wanted, static fn (int $id): bool => $id > 0)));

        foreach ($method->getToolManufacturers()->toArray() as $current) {
            if (!in_array((int) $current->getId(), $wanted, true)) {
                $method->removeToolManufacturer($current);
            }
        }

        $currentIds = array_map(static fn (ToolManufacturer $m): int => (int) $m->getId(), $method->getToolManufacturers()->toArray());
        foreach ($wanted as $id) {
            if (in_array($id, $currentIds, true)) {
                continue;
            }
            $manufacturer = $this->entityManager->find(ToolManufacturer::class, $id);
            if ($manufacturer instanceof ToolManufacturer) {
                $method->addToolManufacturer($manufacturer);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(string $resource, object $entity): array
    {
        $config = $this->config($resource);
        $row = ['id' => $entity->getId()];

        foreach (array_keys($config['fields']) as $key) {
            $row[$key] = $this->accessor->getValue($entity, $key);
        }

        if ($entity instanceof ToolMethode) {
            $row['toolManufacturerIds'] = array_values(array_map(
                static fn (ToolManufacturer $m): int => (int) $m->getId(),
                $entity->getToolManufacturers()->toArray()
            ));
        }

        return $row;
    }
}
