<?php

namespace App\Entity;

use App\Repository\AboAuswahlRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AboAuswahlRepository::class)]
class AboAuswahl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $beschreibung = null;

    #[ORM\Column(length: 20)]
    private string $art = 'aktiv';

    #[ORM\Column]
    private bool $aktiv = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Software>
     */
    #[ORM\ManyToMany(targetEntity: Software::class, inversedBy: 'aboAuswahlen')]
    #[ORM\JoinTable(name: 'abo_auswahl_software')]
    private Collection $software;

    /**
     * @var Collection<int, AboAuswahlPreisstaffel>
     */
    #[ORM\OneToMany(
        mappedBy: 'aboAuswahl',
        targetEntity: AboAuswahlPreisstaffel::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $preisstaffeln;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->software = new ArrayCollection();
        $this->preisstaffeln = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? ('Abo-Auswahl #' . $this->id);
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

    public function getArt(): string
    {
        return $this->art;
    }

    public function setArt(string $art): static
    {
        $this->art = $art;

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
            $software->addAboAuswahl($this);
        }

        return $this;
    }

    public function removeSoftware(Software $software): static
    {
        if ($this->software->removeElement($software)) {
            $software->removeAboAuswahl($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, AboAuswahlPreisstaffel>
     */
    public function getPreisstaffeln(): Collection
    {
        return $this->preisstaffeln;
    }

    public function addPreisstaffel(AboAuswahlPreisstaffel $preisstaffel): static
    {
        if (!$this->preisstaffeln->contains($preisstaffel)) {
            $this->preisstaffeln->add($preisstaffel);
            $preisstaffel->setAboAuswahl($this);
        }

        return $this;
    }

    public function removePreisstaffel(AboAuswahlPreisstaffel $preisstaffel): static
    {
        if ($this->preisstaffeln->removeElement($preisstaffel) && $preisstaffel->getAboAuswahl() === $this) {
            $preisstaffel->setAboAuswahl(null);
        }

        return $this;
    }

    /**
     * @param iterable<int, AboAuswahlPreisstaffel> $preisstaffeln
     */
    public function setPreisstaffeln(iterable $preisstaffeln): static
    {
        foreach ($this->preisstaffeln as $existing) {
            $this->removePreisstaffel($existing);
        }

        foreach ($preisstaffeln as $preisstaffel) {
            $this->addPreisstaffel($preisstaffel);
        }

        return $this;
    }
}
