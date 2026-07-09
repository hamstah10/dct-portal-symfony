<?php

namespace App\Entity;

use App\Repository\SoftwareRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SoftwareRepository::class)]
class Software
{
    public const OPTION_TYPO3 = 'TYPO3';
    public const OPTION_WORDPRESS = 'Wordpress';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $version = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $beschreibung = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $preis = null;

    /**
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $optionen = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bilder = null;

    #[ORM\Column]
    private bool $aktiv = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Abo>
     */
    #[ORM\OneToMany(mappedBy: 'software', targetEntity: Abo::class, orphanRemoval: true)]
    private Collection $abos;

    /**
     * @var Collection<int, AboAuswahl>
     */
    #[ORM\ManyToMany(targetEntity: AboAuswahl::class, mappedBy: 'software')]
    private Collection $aboAuswahlen;

    /**
     * @var Collection<int, Demo>
     */
    #[ORM\ManyToMany(targetEntity: Demo::class, mappedBy: 'software')]
    private Collection $demos;

    /**
     * @var array<int, mixed>
     */
    private array $uploadedImages = [];

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->abos = new ArrayCollection();
        $this->aboAuswahlen = new ArrayCollection();
        $this->demos = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? ('Software #' . $this->id);
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

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): static
    {
        $this->version = $version;

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

    public function getPreis(): ?string
    {
        return $this->preis;
    }

    public function setPreis(?string $preis): static
    {
        $this->preis = $preis;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getOptionen(): array
    {
        return $this->optionen;
    }

    /**
     * @param array<int, string> $optionen
     */
    public function setOptionen(array $optionen): static
    {
        $this->optionen = array_values(array_unique(array_filter(array_map(static fn (mixed $value): string => trim((string) $value), $optionen))));

        return $this;
    }

    public function getBilder(): ?string
    {
        return $this->bilder;
    }

    public function setBilder(?string $bilder): static
    {
        $this->bilder = $bilder;

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
     * @return Collection<int, Abo>
     */
    public function getAbos(): Collection
    {
        return $this->abos;
    }

    public function addAbo(Abo $abo): static
    {
        if (!$this->abos->contains($abo)) {
            $this->abos->add($abo);
            $abo->setSoftware($this);
        }

        return $this;
    }

    public function removeAbo(Abo $abo): static
    {
        if ($this->abos->removeElement($abo)) {
            if ($abo->getSoftware() === $this) {
                $abo->setSoftware(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AboAuswahl>
     */
    public function getAboAuswahlen(): Collection
    {
        return $this->aboAuswahlen;
    }

    public function addAboAuswahl(AboAuswahl $aboAuswahl): static
    {
        if (!$this->aboAuswahlen->contains($aboAuswahl)) {
            $this->aboAuswahlen->add($aboAuswahl);
        }

        return $this;
    }

    public function removeAboAuswahl(AboAuswahl $aboAuswahl): static
    {
        $this->aboAuswahlen->removeElement($aboAuswahl);

        return $this;
    }

    /**
     * @return Collection<int, Demo>
     */
    public function getDemos(): Collection
    {
        return $this->demos;
    }

    public function addDemo(Demo $demo): static
    {
        if (!$this->demos->contains($demo)) {
            $this->demos->add($demo);
            $demo->addSoftware($this);
        }

        return $this;
    }

    public function removeDemo(Demo $demo): static
    {
        if ($this->demos->removeElement($demo)) {
            $demo->removeSoftware($this);
        }

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
