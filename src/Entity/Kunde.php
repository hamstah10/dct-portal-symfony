<?php

namespace App\Entity;

use App\Repository\KundeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KundeRepository::class)]
class Kunde
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true, nullable: true)]
    private ?string $kundennummer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firmenname = null;

    #[ORM\Column(length: 120)]
    private ?string $vorname = null;

    #[ORM\Column(length: 120)]
    private ?string $nachname = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $lexwareContactId = null;

    #[ORM\ManyToOne(inversedBy: 'kunden')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Firma $firma = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $telefon = null;

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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $firmenbeschreibung = null;

    #[ORM\Column]
    private bool $hasDyno = false;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $tuningTools = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $diagnoseTools = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bild = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $passwort = null;

    private ?string $plainPasswort = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $passwordChangedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $zahlungsbedingungen = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $registrierungsdatum = null;

    #[ORM\Column]
    private bool $newsletter = false;

    #[ORM\Column]
    private bool $gesperrt = false;

    #[ORM\Column]
    private int $credits = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $brevoSyncedAt = null;

    /**
     * @var Collection<int, Abo>
     */
    #[ORM\OneToMany(mappedBy: 'kunde', targetEntity: Abo::class, orphanRemoval: true)]
    private Collection $abos;

    /**
     * @var Collection<int, Auftrag>
     */
    #[ORM\OneToMany(mappedBy: 'kunde', targetEntity: Auftrag::class, orphanRemoval: true)]
    private Collection $auftraege;

    /**
     * @var Collection<int, KundeCreditTransaction>
     */
    #[ORM\OneToMany(mappedBy: 'kunde', targetEntity: KundeCreditTransaction::class, orphanRemoval: true)]
    private Collection $creditTransactions;

    /**
     * @var Collection<int, LexwareInvoice>
     */
    #[ORM\OneToMany(mappedBy: 'kunde', targetEntity: LexwareInvoice::class)]
    private Collection $lexwareInvoices;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->registrierungsdatum = new \DateTimeImmutable();
        $this->abos = new ArrayCollection();
        $this->auftraege = new ArrayCollection();
        $this->creditTransactions = new ArrayCollection();
        $this->lexwareInvoices = new ArrayCollection();
    }

    public function __toString(): string
    {
        return trim(sprintf('%s %s', $this->vorname ?? '', $this->nachname ?? '')) ?: ($this->firmenname ?? ('Kunde #' . $this->id));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKundennummer(): ?string
    {
        return $this->kundennummer;
    }

    public function setKundennummer(?string $kundennummer): static
    {
        $this->kundennummer = $kundennummer;

        return $this;
    }

    public function getFirmenname(): ?string
    {
        return $this->firmenname;
    }

    public function setFirmenname(?string $firmenname): static
    {
        $this->firmenname = $firmenname;

        return $this;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setVorname(string $vorname): static
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getNachname(): ?string
    {
        return $this->nachname;
    }

    public function setNachname(string $nachname): static
    {
        $this->nachname = $nachname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getLexwareContactId(): ?string
    {
        return $this->lexwareContactId;
    }

    public function setLexwareContactId(?string $lexwareContactId): static
    {
        $this->lexwareContactId = $lexwareContactId;

        return $this;
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

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setTelefon(?string $telefon): static
    {
        $this->telefon = $telefon;

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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getFirmenbeschreibung(): ?string
    {
        return $this->firmenbeschreibung;
    }

    public function setFirmenbeschreibung(?string $firmenbeschreibung): static
    {
        $this->firmenbeschreibung = $firmenbeschreibung;

        return $this;
    }

    public function hasDyno(): bool
    {
        return $this->hasDyno;
    }

    public function setHasDyno(bool $hasDyno): static
    {
        $this->hasDyno = $hasDyno;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getTuningTools(): array
    {
        if (!is_array($this->tuningTools)) {
            return [];
        }

        return $this->normalizeStringList($this->tuningTools);
    }

    public function setTuningTools(mixed $tuningTools): static
    {
        $normalized = $this->normalizeStringList($tuningTools);
        $this->tuningTools = [] === $normalized ? null : $normalized;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getDiagnoseTools(): array
    {
        if (!is_array($this->diagnoseTools)) {
            return [];
        }

        return $this->normalizeStringList($this->diagnoseTools);
    }

    public function setDiagnoseTools(mixed $diagnoseTools): static
    {
        $normalized = $this->normalizeStringList($diagnoseTools);
        $this->diagnoseTools = [] === $normalized ? null : $normalized;

        return $this;
    }

    public function getTuningToolsSummary(): string
    {
        $tools = $this->getTuningTools();

        return [] === $tools ? '-' : implode(', ', $tools);
    }

    public function getTuningToolsInput(): string
    {
        return implode("\n", $this->getTuningTools());
    }

    public function setTuningToolsInput(?string $value): static
    {
        $this->setTuningTools($value);

        return $this;
    }

    public function getDiagnoseToolsSummary(): string
    {
        $tools = $this->getDiagnoseTools();

        return [] === $tools ? '-' : implode(', ', $tools);
    }

    public function getDiagnoseToolsInput(): string
    {
        return implode("\n", $this->getDiagnoseTools());
    }

    public function setDiagnoseToolsInput(?string $value): static
    {
        $this->setDiagnoseTools($value);

        return $this;
    }

    public function getBild(): ?string
    {
        return $this->bild;
    }

    public function setBild(?string $bild): static
    {
        $this->bild = $bild;

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

    public function getPlainPasswort(): ?string
    {
        return $this->plainPasswort;
    }

    public function setPlainPasswort(?string $plainPasswort): static
    {
        $this->plainPasswort = $plainPasswort;

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

    public function getRegistrierungsdatum(): ?\DateTimeImmutable
    {
        return $this->registrierungsdatum;
    }

    public function setRegistrierungsdatum(?\DateTimeImmutable $registrierungsdatum): static
    {
        $this->registrierungsdatum = $registrierungsdatum;

        return $this;
    }

    public function isNewsletter(): bool
    {
        return $this->newsletter;
    }

    public function setNewsletter(bool $newsletter): static
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    public function isGesperrt(): bool
    {
        return $this->gesperrt;
    }

    public function setGesperrt(bool $gesperrt): static
    {
        $this->gesperrt = $gesperrt;

        return $this;
    }

    public function getCredits(): int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): static
    {
        $this->credits = max(0, $credits);

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

    public function getBrevoSyncedAt(): ?\DateTimeImmutable
    {
        return $this->brevoSyncedAt;
    }

    public function setBrevoSyncedAt(?\DateTimeImmutable $brevoSyncedAt): static
    {
        $this->brevoSyncedAt = $brevoSyncedAt;

        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPasswort = null;
    }

    public function getPasswordChangedAt(): ?\DateTimeImmutable
    {
        return $this->passwordChangedAt;
    }

    public function setPasswordChangedAt(?\DateTimeImmutable $passwordChangedAt): static
    {
        $this->passwordChangedAt = $passwordChangedAt;

        return $this;
    }

    /**
     * @return list<string>
     */
    private function normalizeStringList(mixed $value): array
    {
        $items = [];

        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value)) {
            $trimmed = trim($value);
            if ('' === $trimmed) {
                return [];
            }

            if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
                try {
                    $decoded = json_decode($trimmed, true, 512, \JSON_THROW_ON_ERROR);
                    if (is_array($decoded)) {
                        $items = $decoded;
                    }
                } catch (\JsonException) {
                    $items = preg_split('/[\r\n,;]+/', $trimmed) ?: [];
                }
            } else {
                $items = preg_split('/[\r\n,;]+/', $trimmed) ?: [];
            }
        } elseif (null === $value) {
            return [];
        }

        $normalized = [];
        foreach ($items as $item) {
            if (!is_string($item) && !is_scalar($item)) {
                continue;
            }

            $text = trim((string) $item);
            if ('' === $text) {
                continue;
            }

            $normalized[] = $text;
        }

        return array_values(array_unique($normalized));
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
            $abo->setKunde($this);
        }

        return $this;
    }

    public function removeAbo(Abo $abo): static
    {
        if ($this->abos->removeElement($abo)) {
            if ($abo->getKunde() === $this) {
                $abo->setKunde(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Auftrag>
     */
    public function getAuftraege(): Collection
    {
        return $this->auftraege;
    }

    public function addAuftrag(Auftrag $auftrag): static
    {
        if (!$this->auftraege->contains($auftrag)) {
            $this->auftraege->add($auftrag);
            $auftrag->setKunde($this);
        }

        return $this;
    }

    public function removeAuftrag(Auftrag $auftrag): static
    {
        if ($this->auftraege->removeElement($auftrag)) {
            if ($auftrag->getKunde() === $this) {
                $auftrag->setKunde(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, KundeCreditTransaction>
     */
    public function getCreditTransactions(): Collection
    {
        return $this->creditTransactions;
    }

    public function addCreditTransaction(KundeCreditTransaction $creditTransaction): static
    {
        if (!$this->creditTransactions->contains($creditTransaction)) {
            $this->creditTransactions->add($creditTransaction);
            $creditTransaction->setKunde($this);
        }

        return $this;
    }

    public function removeCreditTransaction(KundeCreditTransaction $creditTransaction): static
    {
        if ($this->creditTransactions->removeElement($creditTransaction)) {
            if ($creditTransaction->getKunde() === $this) {
                $creditTransaction->setKunde(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LexwareInvoice>
     */
    public function getLexwareInvoices(): Collection
    {
        return $this->lexwareInvoices;
    }

    public function addLexwareInvoice(LexwareInvoice $lexwareInvoice): static
    {
        if (!$this->lexwareInvoices->contains($lexwareInvoice)) {
            $this->lexwareInvoices->add($lexwareInvoice);
            $lexwareInvoice->setKunde($this);
        }

        return $this;
    }

    public function removeLexwareInvoice(LexwareInvoice $lexwareInvoice): static
    {
        if ($this->lexwareInvoices->removeElement($lexwareInvoice)) {
            if ($lexwareInvoice->getKunde() === $this) {
                $lexwareInvoice->setKunde(null);
            }
        }

        return $this;
    }

    /** @return array<string, mixed> */
    public function getSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'kundennummer' => $this->kundennummer,
            'vorname' => $this->vorname,
            'nachname' => $this->nachname,
            'email' => $this->email,
            'firmenname' => $this->firmenname,
            'telefon' => $this->telefon,
            'stadt' => $this->stadt,
            'plz' => $this->plz,
            'land' => $this->land,
        ];
    }
}
