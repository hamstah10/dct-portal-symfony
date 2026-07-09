<?php

namespace App\Entity;

use App\Repository\DynoCustomerRepository;
use App\Util\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DynoCustomerRepository::class)]
#[ORM\Table(name: 'dyno_customer')]
class DynoCustomer
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 180)]
    private string $email = '';

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?DynoVehicle $vehicle = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : $this->id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

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

    public function getVehicle(): ?DynoVehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?DynoVehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }
}
