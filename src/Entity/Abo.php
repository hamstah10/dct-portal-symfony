<?php

namespace App\Entity;

use App\Repository\AboRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: AboRepository::class)]
#[Assert\Callback('validateSoftwareOderPaket')]
class Abo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'abos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Kunde $kunde = null;

    #[ORM\ManyToOne(inversedBy: 'abos')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Software $software = null;

    #[ORM\ManyToOne]
    private ?AboAuswahl $aboAuswahl = null;

    #[ORM\ManyToOne]
    private ?AboAuswahlPreisstaffel $preisstaffel = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $leistungen = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startDatum = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endDatum = null;

    #[ORM\Column(length: 50)]
    private string $status = 'aktiv';

    #[ORM\Column(length: 20)]
    private string $zahlweise = 'monatlich';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $preis = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notizen = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $link = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->startDatum = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf(
            'Abo #%d (%s)',
            $this->id ?? 0,
            $this->kunde?->__toString() ?? 'ohne Kunde'
        );
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSoftware(): ?Software
    {
        return $this->software;
    }

    public function setSoftware(?Software $software): static
    {
        $this->software = $software;

        return $this;
    }

    public function getAboAuswahl(): ?AboAuswahl
    {
        return $this->aboAuswahl;
    }

    public function setAboAuswahl(?AboAuswahl $aboAuswahl): static
    {
        $this->aboAuswahl = $aboAuswahl;

        return $this;
    }

    public function getPreisstaffel(): ?AboAuswahlPreisstaffel
    {
        return $this->preisstaffel;
    }

    public function setPreisstaffel(?AboAuswahlPreisstaffel $preisstaffel): static
    {
        $this->preisstaffel = $preisstaffel;
        if (null !== $preisstaffel) {
            $this->aboAuswahl = $preisstaffel->getAboAuswahl();
        }

        return $this;
    }

    public function getLeistungen(): ?string
    {
        return $this->leistungen;
    }

    public function setLeistungen(string $leistungen): static
    {
        $this->leistungen = $leistungen;

        return $this;
    }

    public function getStartDatum(): ?\DateTimeImmutable
    {
        return $this->startDatum;
    }

    public function setStartDatum(\DateTimeImmutable $startDatum): static
    {
        $this->startDatum = $startDatum;

        return $this;
    }

    public function getEndDatum(): ?\DateTimeImmutable
    {
        return $this->endDatum;
    }

    public function setEndDatum(?\DateTimeImmutable $endDatum): static
    {
        $this->endDatum = $endDatum;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getZahlweise(): string
    {
        return $this->zahlweise;
    }

    public function setZahlweise(string $zahlweise): static
    {
        $this->zahlweise = $zahlweise;

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

    public function getNotizen(): ?string
    {
        return $this->notizen;
    }

    public function setNotizen(?string $notizen): static
    {
        $this->notizen = $notizen;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function validateSoftwareOderPaket(ExecutionContextInterface $context): void
    {
        $hasSoftware = null !== $this->software;
        $hasPaket = null !== $this->preisstaffel;

        if (!$hasSoftware && !$hasPaket) {
            $context->buildViolation('Bitte entweder eine Software oder ein Paket mit Laufzeit auswaehlen.')
                ->atPath('preisstaffel')
                ->addViolation();

            return;
        }

        if ($hasSoftware && $hasPaket) {
            $context->buildViolation('Bitte nur eines auswaehlen: Software oder Paket mit Laufzeit.')
                ->atPath('preisstaffel')
                ->addViolation();
        }
    }
}
