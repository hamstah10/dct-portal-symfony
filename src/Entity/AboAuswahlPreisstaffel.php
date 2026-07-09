<?php

namespace App\Entity;

use App\Repository\AboAuswahlPreisstaffelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AboAuswahlPreisstaffelRepository::class)]
class AboAuswahlPreisstaffel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'preisstaffeln')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?AboAuswahl $aboAuswahl = null;

    #[ORM\Column]
    private int $laufzeitMonate = 1;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $preisMonatlich = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $preisJaehrlich = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $preisEinrichtung = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLaufzeitMonate(): int
    {
        return $this->laufzeitMonate;
    }

    public function setLaufzeitMonate(int $laufzeitMonate): static
    {
        $this->laufzeitMonate = max(1, $laufzeitMonate);

        return $this;
    }

    public function getPreisMonatlich(): ?string
    {
        return $this->preisMonatlich;
    }

    public function setPreisMonatlich(?string $preisMonatlich): static
    {
        $this->preisMonatlich = $preisMonatlich;

        return $this;
    }

    public function getPreisJaehrlich(): ?string
    {
        return $this->preisJaehrlich;
    }

    public function setPreisJaehrlich(?string $preisJaehrlich): static
    {
        $this->preisJaehrlich = $preisJaehrlich;

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

    public function getPreisEinrichtung(): ?string
    {
        return $this->preisEinrichtung;
    }

    public function setPreisEinrichtung(?string $preisEinrichtung): static
    {
        $this->preisEinrichtung = $preisEinrichtung;

        return $this;
    }

    public function __toString(): string
    {
        $paketName = $this->aboAuswahl?->getName() ?? 'Unbekanntes Paket';

        return sprintf(
            '%s | %d Monate | Monatlich: %s | Jaehrlich: %s | Einrichtung: %s',
            $paketName,
            $this->laufzeitMonate,
            $this->preisMonatlich ?? '-',
            $this->preisJaehrlich ?? '-',
            $this->preisEinrichtung ?? '-'
        );
    }
}
