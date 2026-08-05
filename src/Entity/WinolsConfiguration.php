<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WinolsConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WinolsConfigurationRepository::class)]
#[ORM\Table(name: 'winols_configuration')]
class WinolsConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 190)]
    private string $name = '';

    #[ORM\Column(length: 255)]
    private string $apiBaseUrl = '';

    #[ORM\Column(length: 255)]
    private string $apiToken = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $active = true;

    /**
     * @var Collection<int, Portal>
     */
    #[ORM\ManyToMany(targetEntity: Portal::class)]
    #[ORM\JoinTable(name: 'winols_configuration_portal')]
    #[ORM\JoinColumn(name: 'configuration_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'portal_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $portals;

    /**
     * @var Collection<int, WinolsConfigurationRule>
     */
    #[ORM\OneToMany(mappedBy: 'configuration', targetEntity: WinolsConfigurationRule::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC', 'id' => 'ASC'])]
    private Collection $rules;

    public function __construct()
    {
        $this->portals = new ArrayCollection();
        $this->rules = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('WinOLS #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getApiBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }

    public function setApiBaseUrl(string $apiBaseUrl): static
    {
        $normalized = rtrim(trim($apiBaseUrl), '/');
        if ('' === $normalized) {
            $this->apiBaseUrl = '';

            return $this;
        }

        $parts = parse_url($normalized);
        if (false !== $parts && isset($parts['scheme'], $parts['host'])) {
            $url = $parts['scheme'] . '://' . $parts['host'];
            if (isset($parts['port'])) {
                $url .= ':' . $parts['port'];
            }

            $this->apiBaseUrl = $url;

            return $this;
        }

        $this->apiBaseUrl = $normalized;

        return $this;
    }

    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): static
    {
        $this->apiToken = trim($apiToken);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    /**
     * @return Collection<int, Portal>
     */
    public function getPortals(): Collection
    {
        return $this->portals;
    }

    public function addPortal(Portal $portal): static
    {
        if (!$this->portals->contains($portal)) {
            $this->portals->add($portal);
        }

        return $this;
    }

    public function removePortal(Portal $portal): static
    {
        $this->portals->removeElement($portal);

        return $this;
    }

    public function getPortalSummary(): string
    {
        $portals = [];
        foreach ($this->portals as $portal) {
            if ($portal instanceof Portal) {
                $portals[] = (string) $portal;
            }
        }

        return [] !== $portals ? implode(', ', $portals) : 'alle Portale';
    }

    /**
     * @return Collection<int, WinolsConfigurationRule>
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function addRule(WinolsConfigurationRule $rule): static
    {
        if (!$this->rules->contains($rule)) {
            $this->rules->add($rule);
            $rule->setConfiguration($this);
        }

        return $this;
    }

    public function removeRule(WinolsConfigurationRule $rule): static
    {
        if ($this->rules->removeElement($rule) && $rule->getConfiguration() === $this) {
            $rule->setConfiguration(null);
        }

        return $this;
    }

    public function getRuleCount(): int
    {
        return $this->rules->count();
    }

    public function getRuleSummary(): string
    {
        if (0 === $this->rules->count()) {
            return 'Keine Regeln hinterlegt';
        }

        $parts = [];
        foreach ($this->rules as $rule) {
            if ($rule instanceof WinolsConfigurationRule) {
                $parts[] = $rule->getSummary();
            }
        }

        return implode(' | ', array_filter($parts));
    }

    public function getMatchingRule(Auftrag $order): ?WinolsConfigurationRule
    {
        if (!$this->active || 0 === $this->rules->count()) {
            return null;
        }

        foreach ($this->rules as $rule) {
            if (!$rule instanceof WinolsConfigurationRule || !$rule->isActive()) {
                continue;
            }

            if ($rule->matchesOrder($order)) {
                return $rule;
            }
        }

        return null;
    }

    public function matchesOrder(Auftrag $order): bool
    {
        return null !== $this->getMatchingRule($order);
    }
}
