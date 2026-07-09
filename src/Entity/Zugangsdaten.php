<?php

namespace App\Entity;

use App\Repository\ZugangsdatenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZugangsdatenRepository::class)]
#[ORM\Table(name: 'zugangsdaten')]
class Zugangsdaten
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Firma $firma = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $benutzer = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $passwort = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notizen = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private int $sorting = 0;

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('Zugangsdaten #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirma(): ?Firma
    {
        return $this->firma;
    }

    public function setFirma(?Firma $firma): static
    {
        $this->firma = $firma;

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

    public function getBenutzer(): ?string
    {
        return $this->benutzer;
    }

    public function setBenutzer(?string $benutzer): static
    {
        $this->benutzer = $benutzer;

        return $this;
    }

    public function getPasswort(): ?string
    {
        return $this->passwort;
    }

    public function setPasswort(?string $passwort): static
    {
        $this->passwort = $passwort;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getNotizen(): ?string
    {
        return $this->notizen;
    }

    public function setNotizen(?string $notizen): static
    {
        $this->notizen = $notizen;

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

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }
}
