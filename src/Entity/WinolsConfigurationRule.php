<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WinolsConfigurationRuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WinolsConfigurationRuleRepository::class)]
#[ORM\Table(name: 'winols_configuration_rule')]
class WinolsConfigurationRule
{
    public const MODE_SLAVE = 'slave';
    public const MODE_MASTER = 'master';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rules')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?WinolsConfiguration $configuration = null;

    #[ORM\Column(length: 190)]
    private string $name = '';

    /**
     * @var Collection<int, ToolManufacturer>
     */
    #[ORM\ManyToMany(targetEntity: ToolManufacturer::class)]
    #[ORM\JoinTable(name: 'winols_configuration_rule_tool_manufacturer')]
    #[ORM\JoinColumn(name: 'rule_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'tool_manufacturer_id', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    private Collection $toolManufacturers;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $allowedModes = [self::MODE_SLAVE, self::MODE_MASTER];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $allowedExtensions = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private int $sortOrder = 0;

    public function __construct()
    {
        $this->toolManufacturers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('WinOLS-Regel #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfiguration(): ?WinolsConfiguration
    {
        return $this->configuration;
    }

    public function setConfiguration(?WinolsConfiguration $configuration): static
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    /**
     * @return Collection<int, ToolManufacturer>
     */
    public function getToolManufacturers(): Collection
    {
        return $this->toolManufacturers;
    }

    public function addToolManufacturer(ToolManufacturer $toolManufacturer): static
    {
        if (!$this->toolManufacturers->contains($toolManufacturer)) {
            $this->toolManufacturers->add($toolManufacturer);
        }

        return $this;
    }

    public function removeToolManufacturer(ToolManufacturer $toolManufacturer): static
    {
        $this->toolManufacturers->removeElement($toolManufacturer);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getAllowedModes(): array
    {
        return $this->allowedModes;
    }

    /**
     * @param list<string> $allowedModes
     */
    public function setAllowedModes(array $allowedModes): static
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            static fn (mixed $mode): string => strtolower(trim((string) $mode)),
            $allowedModes
        ))));

        $this->allowedModes = array_values(array_intersect($normalized, [self::MODE_SLAVE, self::MODE_MASTER]));

        return $this;
    }

    public function getAllowedExtensions(): ?string
    {
        return $this->allowedExtensions;
    }

    public function setAllowedExtensions(?string $allowedExtensions): static
    {
        $this->allowedExtensions = $this->normalizeExtensionsText($allowedExtensions);

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getAllowedExtensionList(): array
    {
        $raw = (string) ($this->allowedExtensions ?? '');
        if ('' === trim($raw)) {
            return [];
        }

        $parts = preg_split('/[\r\n,;]+/', $raw) ?: [];
        $normalized = [];
        foreach ($parts as $part) {
            $extension = strtolower(trim($part));
            $extension = ltrim($extension, '.');
            if ('' !== $extension) {
                $normalized[] = $extension;
            }
        }

        return array_values(array_unique($normalized));
    }

    public function getSummary(): string
    {
        $manufacturers = [];
        foreach ($this->toolManufacturers as $manufacturer) {
            if ($manufacturer instanceof ToolManufacturer) {
                $manufacturers[] = $manufacturer->getName();
            }
        }

        $extensions = $this->getAllowedExtensionList();
        $modes = array_map(
            static fn (string $mode): string => match ($mode) {
                self::MODE_MASTER => 'Master',
                self::MODE_SLAVE => 'Slave',
                default => ucfirst($mode),
            },
            $this->allowedModes
        );

        return trim(sprintf(
            '%s | Hersteller: %s | Modi: %s | Endungen: %s',
            $this->name ?: ('Regel #' . ($this->id ?? 0)),
            [] !== $manufacturers ? implode(', ', $manufacturers) : 'alle',
            [] !== $modes ? implode(', ', $modes) : 'keine',
            [] !== $extensions ? implode(', ', $extensions) : 'keine'
        ));
    }

    public function matchesToolManufacturerName(?string $toolName): bool
    {
        $normalizedToolName = strtolower(trim((string) $toolName));
        if ('' === $normalizedToolName) {
            return false;
        }

        foreach ($this->toolManufacturers as $manufacturer) {
            if (!$manufacturer instanceof ToolManufacturer) {
                continue;
            }

            $manufacturerName = strtolower(trim((string) $manufacturer->getName()));
            if ('' !== $manufacturerName && str_contains($normalizedToolName, $manufacturerName)) {
                return true;
            }
        }

        return false;
    }

    public function matchesMode(?string $mode): bool
    {
        $normalizedMode = strtolower(trim((string) $mode));
        if ('' === $normalizedMode) {
            return false;
        }

        return in_array($normalizedMode, $this->allowedModes, true);
    }

    public function matchesExtension(?string $fileName): bool
    {
        $normalizedFileName = strtolower(trim((string) $fileName));
        if ('' === $normalizedFileName) {
            return false;
        }

        $extension = strtolower(pathinfo($normalizedFileName, \PATHINFO_EXTENSION));
        if ('' === $extension) {
            return false;
        }

        return in_array($extension, $this->getAllowedExtensionList(), true);
    }

    public function matchesOrder(Auftrag $order): bool
    {
        if (!$this->active) {
            return false;
        }

        if (!$this->matchesMode($order->getSlaveOrMaster())) {
            return false;
        }

        if (!$this->matchesExtension($order->getFileName())) {
            return false;
        }

        $toolName = trim((string) $order->getTuningTool());
        if ('' !== $toolName && !$this->matchesToolManufacturerName($toolName)) {
            return false;
        }

        return true;
    }

    private function normalizeExtensionsText(?string $allowedExtensions): ?string
    {
        if (null === $allowedExtensions) {
            return null;
        }

        $parts = preg_split('/[\r\n,;]+/', $allowedExtensions) ?: [];
        $normalized = [];
        foreach ($parts as $part) {
            $extension = strtolower(trim($part));
            $extension = ltrim($extension, '.');
            if ('' !== $extension) {
                $normalized[] = $extension;
            }
        }

        return '' !== implode("\n", $normalized) ? implode("\n", array_values(array_unique($normalized))) : null;
    }
}
