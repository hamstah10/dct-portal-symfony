<?php

namespace App\Entity;

use App\Repository\DemoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemoRepository::class)]
class Demo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $beschreibung = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $vorschaubilder = null;

    #[ORM\ManyToOne]
    private ?Kunde $kunde = null;

    /**
     * @var Collection<int, Software>
     */
    #[ORM\ManyToMany(targetEntity: Software::class, inversedBy: 'demos')]
    #[ORM\JoinTable(name: 'demo_software')]
    private Collection $software;

    #[ORM\Column]
    private bool $aktiv = true;

    #[ORM\Column]
    private bool $eigeneWebsite = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var array<int, mixed>
     */
    private array $uploadedImages = [];

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->software = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? ('Demo #' . $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
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

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getVorschaubilder(): ?string
    {
        return $this->vorschaubilder;
    }

    public function setVorschaubilder(?string $vorschaubilder): static
    {
        $this->vorschaubilder = $vorschaubilder;

        return $this;
    }

    public function getKunde(): ?Kunde
    {
        return $this->kunde;
    }

    public function setKunde(?Kunde $kunde): static
    {
        $this->kunde = $kunde;

        return $this;
    }

    /**
     * @return Collection<int, Software>
     */
    public function getSoftware(): Collection
    {
        return $this->software;
    }

    public function addSoftware(Software $software): static
    {
        if (!$this->software->contains($software)) {
            $this->software->add($software);
            $software->addDemo($this);
        }

        return $this;
    }

    public function removeSoftware(Software $software): static
    {
        if ($this->software->removeElement($software)) {
            $software->removeDemo($this);
        }

        return $this;
    }

    public function isAktiv(): bool
    {
        return $this->aktiv;
    }

    public function setAktiv(bool $aktiv): static
    {
        $this->aktiv = $aktiv;

        return $this;
    }

    public function isEigeneWebsite(): bool
    {
        return $this->eigeneWebsite;
    }

    public function setEigeneWebsite(bool $eigeneWebsite): static
    {
        $this->eigeneWebsite = $eigeneWebsite;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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
}
