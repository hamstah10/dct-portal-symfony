<?php

namespace App\Entity;

use App\Repository\DtcCodeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DtcCodeRepository::class)]
#[ORM\Table(name: 'tx_tuningdtccodes_domain_model_dtccode')]
class DtcCode
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private int $uid = 0;

    #[ORM\Column]
    private int $pid = 0;

    #[ORM\Column]
    private int $tstamp = 0;

    #[ORM\Column]
    private int $crdate = 0;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $deleted = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $hidden = false;

    #[ORM\Column(length: 20)]
    private string $fehlercode = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $beschreibung = null;

    #[ORM\ManyToOne(inversedBy: 'codes')]
    #[ORM\JoinColumn(name: 'category', referencedColumnName: 'uid', nullable: false, onDelete: 'CASCADE')]
    private ?DtcCategory $category = null;

    public function __toString(): string
    {
        return '' !== $this->fehlercode ? $this->fehlercode : ('DTC #' . $this->uid);
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setUid(int $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function setPid(int $pid): static
    {
        $this->pid = $pid;

        return $this;
    }

    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    public function setTstamp(int $tstamp): static
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    public function getCrdate(): int
    {
        return $this->crdate;
    }

    public function setCrdate(int $crdate): static
    {
        $this->crdate = $crdate;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getFehlercode(): string
    {
        return $this->fehlercode;
    }

    public function setFehlercode(string $fehlercode): static
    {
        $this->fehlercode = $fehlercode;

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

    public function getCategory(): ?DtcCategory
    {
        return $this->category;
    }

    public function setCategory(?DtcCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getSearchableArray(): array
    {
        return [
            'id' => $this->uid,
            'fehlercode' => $this->fehlercode,
            'beschreibung' => $this->beschreibung,
            'categoryName' => $this->category?->getName() ?? '',
            'categoryLetter' => $this->category?->getBuchstabe() ?? '',
            'deleted' => $this->deleted,
            'hidden' => $this->hidden,
        ];
    }
}
