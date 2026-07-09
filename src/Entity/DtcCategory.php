<?php

namespace App\Entity;

use App\Repository\DtcCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DtcCategoryRepository::class)]
#[ORM\Table(name: 'tx_tuningdtccodes_domain_model_category')]
class DtcCategory
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

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 10)]
    private string $buchstabe = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $beschreibung = null;

    /**
     * @var Collection<int, DtcCode>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: DtcCode::class)]
    private Collection $codes;

    public function __construct()
    {
        $this->codes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('Kategorie #' . $this->uid);
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBuchstabe(): string
    {
        return $this->buchstabe;
    }

    public function setBuchstabe(string $buchstabe): static
    {
        $this->buchstabe = $buchstabe;

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

    /**
     * @return Collection<int, DtcCode>
     */
    public function getCodes(): Collection
    {
        return $this->codes;
    }

    public function addCode(DtcCode $code): static
    {
        if (!$this->codes->contains($code)) {
            $this->codes->add($code);
            $code->setCategory($this);
        }

        return $this;
    }

    public function removeCode(DtcCode $code): static
    {
        if ($this->codes->removeElement($code)) {
            if ($code->getCategory() === $this) {
                $code->setCategory(null);
            }
        }

        return $this;
    }
}
