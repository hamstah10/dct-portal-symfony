<?php

namespace App\Entity;

use App\Repository\UserGroupRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserGroupRepository::class)]
#[ORM\Table(name: 'user_group')]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_GROUP_NAME', columns: ['name'])]
class UserGroup
{
    /**
     * Preset roles shown as checkboxes.
     *
     * @var list<string>
     */
    public const PRESET_ROLES = [
        'ROLE_ADMIN',
        'ROLE_USER',
        'ROLE_PORTAL_TFD_USER',
        'ROLE_PORTAL_TFD_ADMIN',
        'ROLE_PORTAL_CPT_USER',
        'ROLE_PORTAL_CPT_ADMIN',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name = '';

    /**
     * @var list<string>
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var list<string>
     */
    private array $rolesPreset = [];

    /**
     * @var list<string>
     */
    private array $rolesExtra = [];

    #[ORM\Column]
    private int $sorting = 0;

    public function __toString(): string
    {
        $name = trim($this->name);
        if ('' !== $name) {
            return $name;
        }

        return 'Gruppe #' . ($this->id ?? 0);
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

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return array_values(array_unique($this->roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $this->normalizeRoles($roles);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getRolesPreset(): array
    {
        return array_values(array_intersect($this->roles, self::PRESET_ROLES));
    }

    /**
     * @param list<string> $rolesPreset
     */
    public function setRolesPreset(array $rolesPreset): static
    {
        $normalized = $this->normalizeRoles($rolesPreset);
        $this->rolesPreset = array_values(array_intersect($normalized, self::PRESET_ROLES));
        $this->syncRoles();

        return $this;
    }

    public function getRolesInput(): string
    {
        $extras = array_values(array_diff($this->roles, self::PRESET_ROLES));

        return implode(', ', $extras);
    }

    public function setRolesInput(?string $rolesInput): static
    {
        $raw = trim((string) ($rolesInput ?? ''));
        if ('' === $raw) {
            $this->rolesExtra = [];
            $this->syncRoles();

            return $this;
        }

        $parts = preg_split('/[,\n;]+/', $raw) ?: [];
        $normalized = $this->normalizeRoles($parts);
        $this->rolesExtra = array_values(array_diff($normalized, self::PRESET_ROLES));
        $this->syncRoles();

        return $this;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * @param iterable<string> $roles
     * @return list<string>
     */
    private function normalizeRoles(iterable $roles): array
    {
        $normalized = [];
        foreach ($roles as $role) {
            $value = strtoupper(trim((string) $role));
            if ('' === $value) {
                continue;
            }

            if (!str_starts_with($value, 'ROLE_')) {
                $value = 'ROLE_' . $value;
            }

            $normalized[] = $value;
        }

        return array_values(array_unique($normalized));
    }

    private function syncRoles(): void
    {
        $this->roles = array_values(array_unique(array_merge($this->rolesPreset, $this->rolesExtra)));
    }
}
