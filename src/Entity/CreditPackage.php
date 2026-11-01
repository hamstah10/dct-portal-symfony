<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CreditPackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditPackageRepository::class)]
class CreditPackage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column]
    private int $credits = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $pricePerFile = '0.00';

    #[ORM\ManyToOne(inversedBy: 'packages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CreditCategory $category = null;

    #[ORM\ManyToMany(targetEntity: Firma::class)]
    #[ORM\JoinTable(name: 'credit_package_firma')]
    private Collection $firmas;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $lexwareArticleId = null;

    public function __construct()
    {
        $this->firmas = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? ('Paket #' . $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getPricePerFile(): string
    {
        return $this->pricePerFile;
    }

    public function setPricePerFile(string $pricePerFile): static
    {
        $this->pricePerFile = $pricePerFile;

        return $this;
    }

    public function getLexwareArticleId(): ?string
    {
        return $this->lexwareArticleId;
    }

    public function setLexwareArticleId(?string $lexwareArticleId): static
    {
        $this->lexwareArticleId = $lexwareArticleId;

        return $this;
    }

    public function getCategory(): ?CreditCategory
    {
        return $this->category;
    }

    public function setCategory(?CreditCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Firma>
     */
    public function getFirmas(): Collection
    {
        return $this->firmas;
    }

    public function addFirma(Firma $firma): static
    {
        if (!$this->firmas->contains($firma)) {
            $this->firmas->add($firma);
        }

        return $this;
    }

    public function removeFirma(Firma $firma): static
    {
        $this->firmas->removeElement($firma);

        return $this;
    }

    public function getPaypalFee(): string
    {
        $price = (float) $this->price;
        $fee = round($price * 0.0199 + 0.35, 2);
        return number_format($fee, 2, ',', '.');
    }

    public function getPaypalNet(): string
    {
        $price = (float) $this->price;
        $fee = round($price * 0.0199 + 0.35, 2);
        return number_format($price - $fee, 2, ',', '.');
    }
}

