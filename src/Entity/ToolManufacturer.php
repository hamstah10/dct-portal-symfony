<?php

namespace App\Entity;

use App\Repository\ToolManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolManufacturerRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_domain_model_toolmanufacturer')]
class ToolManufacturer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $beschreibung = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $plz = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stadt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $land = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $telefon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $images = null;

    /**
     * @var Collection<int, Ecu>
     */
    #[ORM\OneToMany(mappedBy: 'toolManufacturer', targetEntity: Ecu::class)]
    private Collection $ecus;

    /**
     * @var Collection<int, ToolMethode>
     */
    #[ORM\ManyToMany(targetEntity: ToolMethode::class, mappedBy: 'toolManufacturers')]
    private Collection $toolMethodes;

    private mixed $uploadedLogo = null;

    /**
     * @var array<int, mixed>
     */
    private array $uploadedImages = [];

    public function __construct()
    {
        $this->ecus = new ArrayCollection();
        $this->toolMethodes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('ToolManufacturer #' . ($this->id ?? 0));
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
        $this->name = $name;

        return $this;
    }

    public function getBeschreibung(): ?string
    {
        return $this->beschreibung;
    }

    public function setBeschreibung(?string $beschreibung): static
    {
        $this->beschreibung = $beschreibung;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getPlz(): ?string
    {
        return $this->plz;
    }

    public function setPlz(?string $plz): static
    {
        $this->plz = $plz;

        return $this;
    }

    public function getStadt(): ?string
    {
        return $this->stadt;
    }

    public function setStadt(?string $stadt): static
    {
        $this->stadt = $stadt;

        return $this;
    }

    public function getLand(): ?string
    {
        return $this->land;
    }

    public function setLand(?string $land): static
    {
        $this->land = $land;

        return $this;
    }

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setTelefon(?string $telefon): static
    {
        $this->telefon = $telefon;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /** Inaktive Hersteller werden in den Frontends nicht mehr ausgegeben. */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $active = true;

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getImages(): ?string
    {
        return $this->images;
    }

    public function setImages(?string $images): static
    {
        $this->images = $images;

        return $this;
    }

    public function getUploadedLogo(): mixed
    {
        return $this->uploadedLogo;
    }

    public function setUploadedLogo(mixed $uploadedLogo): static
    {
        $this->uploadedLogo = $uploadedLogo;

        return $this;
    }

    /**
     * @return array<int, mixed>
     */
    public function getUploadedImages(): array
    {
        return $this->uploadedImages;
    }

    /**
     * @param array<int, mixed> $uploadedImages
     */
    public function setUploadedImages(array $uploadedImages): static
    {
        $this->uploadedImages = $uploadedImages;

        return $this;
    }

    /**
     * @return Collection<int, Ecu>
     */
    public function getEcus(): Collection
    {
        return $this->ecus;
    }

    /**
     * @return Collection<int, ToolMethode>
     */
    public function getToolMethodes(): Collection
    {
        return $this->toolMethodes;
    }

    public function addToolMethode(ToolMethode $toolMethode): static
    {
        if (!$this->toolMethodes->contains($toolMethode)) {
            $this->toolMethodes->add($toolMethode);
        }

        return $this;
    }

    public function removeToolMethode(ToolMethode $toolMethode): static
    {
        $this->toolMethodes->removeElement($toolMethode);

        return $this;
    }
}
