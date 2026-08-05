<?php

namespace App\Entity;

use App\Repository\SuperadminVehicleCompareListRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuperadminVehicleCompareListRepository::class)]
#[ORM\Table(name: 'superadmin_vehicle_compare_list')]
#[ORM\Index(name: 'IDX_SUPERADMIN_COMPARE_LIST_USER', columns: ['user_id'])]
#[ORM\Index(name: 'IDX_SUPERADMIN_COMPARE_LIST_UPDATED_AT', columns: ['updated_at'])]
class SuperadminVehicleCompareList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 190)]
    private string $name = '';

    /**
     * @var array<int, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $vehicles = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = mb_substr($name, 0, 190);

        return $this;
    }

    /**
     * @return array<int, mixed>
     */
    public function getVehicles(): array
    {
        return $this->vehicles;
    }

    /**
     * @param array<int, mixed> $vehicles
     */
    public function setVehicles(array $vehicles): static
    {
        $this->vehicles = $vehicles;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
