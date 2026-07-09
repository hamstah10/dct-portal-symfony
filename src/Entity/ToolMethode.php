<?php

namespace App\Entity;

use App\Repository\ToolMethodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolMethodeRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_domain_model_toolmethode')]
class ToolMethode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    /**
     * @var Collection<int, ToolManufacturer>
     */
    #[ORM\ManyToMany(targetEntity: ToolManufacturer::class, inversedBy: 'toolMethodes')]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $toolManufacturers;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $beschreibung = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    private mixed $uploadedImage = null;

    public function __construct()
    {
        $this->toolManufacturers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('ToolMethode #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToolManufacturer(): ?ToolManufacturer
    {
        $first = $this->toolManufacturers->first();

        return $first instanceof ToolManufacturer ? $first : null;
    }

    public function setToolManufacturer(?ToolManufacturer $toolManufacturer): static
    {
        $this->toolManufacturers->clear();
        if (null !== $toolManufacturer) {
            $this->toolManufacturers->add($toolManufacturer);
        }

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
            $toolManufacturer->addToolMethode($this);
        }
        return $this;
    }

    public function removeToolManufacturer(ToolManufacturer $toolManufacturer): static
    {
        if ($this->toolManufacturers->removeElement($toolManufacturer)) {
            $toolManufacturer->removeToolMethode($this);
        }

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

    public function getBeschreibung(): ?string
    {
        return $this->beschreibung;
    }

    public function setBeschreibung(?string $beschreibung): static
    {
        $this->beschreibung = $beschreibung;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getUploadedImage(): mixed
    {
        return $this->uploadedImage;
    }

    public function setUploadedImage(mixed $uploadedImage): static
    {
        $this->uploadedImage = $uploadedImage;

        return $this;
    }
}
