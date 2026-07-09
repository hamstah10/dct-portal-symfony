<?php

namespace App\Entity;

use App\Repository\FirmaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FirmaRepository::class)]
class Firma
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $kuerzel = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $rechtsform = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $ustId = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $steuernummer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $strasse = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $hausnummer = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $plz = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $stadt = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $land = 'Deutschland';

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $telefon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $bic = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $bankname = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $zahlungsbedingungen = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column]
    private bool $aktiv = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Kunde>
     */
    #[ORM\OneToMany(mappedBy: 'firma', targetEntity: Kunde::class)]
    private Collection $kunden;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->kunden = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? ('Firma #' . $this->id);
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

    public function getKuerzel(): ?string
    {
        return $this->kuerzel;
    }

    public function setKuerzel(?string $kuerzel): static
    {
        $this->kuerzel = $kuerzel;

        return $this;
    }

    public function getRechtsform(): ?string
    {
        return $this->rechtsform;
    }

    public function setRechtsform(?string $rechtsform): static
    {
        $this->rechtsform = $rechtsform;

        return $this;
    }

    public function getUstId(): ?string
    {
        return $this->ustId;
    }

    public function setUstId(?string $ustId): static
    {
        $this->ustId = $ustId;

        return $this;
    }

    public function getSteuernummer(): ?string
    {
        return $this->steuernummer;
    }

    public function setSteuernummer(?string $steuernummer): static
    {
        $this->steuernummer = $steuernummer;

        return $this;
    }

    public function getStrasse(): ?string
    {
        return $this->strasse;
    }

    public function setStrasse(?string $strasse): static
    {
        $this->strasse = $strasse;

        return $this;
    }

    public function getHausnummer(): ?string
    {
        return $this->hausnummer;
    }

    public function setHausnummer(?string $hausnummer): static
    {
        $this->hausnummer = $hausnummer;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): static
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(?string $bic): static
    {
        $this->bic = $bic;

        return $this;
    }

    public function getBankname(): ?string
    {
        return $this->bankname;
    }

    public function setBankname(?string $bankname): static
    {
        $this->bankname = $bankname;

        return $this;
    }

    public function getZahlungsbedingungen(): ?string
    {
        return $this->zahlungsbedingungen;
    }

    public function setZahlungsbedingungen(?string $zahlungsbedingungen): static
    {
        $this->zahlungsbedingungen = $zahlungsbedingungen;

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
     * @return Collection<int, Kunde>
     */
    public function getKunden(): Collection
    {
        return $this->kunden;
    }

    public function addKunde(Kunde $kunde): static
    {
        if (!$this->kunden->contains($kunde)) {
            $this->kunden->add($kunde);
            $kunde->setFirma($this);
        }

        return $this;
    }

    public function removeKunde(Kunde $kunde): static
    {
        if ($this->kunden->removeElement($kunde)) {
            if ($kunde->getFirma() === $this) {
                $kunde->setFirma(null);
            }
        }

        return $this;
    }
}
