<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\KonzernRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KonzernRepository::class)]
#[ORM\Table(name: 'konzern')]
class Konzern
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $strasse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hausnummer = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $plz = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $stadt = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $land = 'Deutschland';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'logo_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $logoMediaAsset = null;

    private mixed $uploadedLogo = null;

    #[ORM\Column]
    private bool $active = true;

    /**
     * @var Collection<int, BrandMaster>
     */
    #[ORM\ManyToMany(targetEntity: BrandMaster::class)]
    #[ORM\JoinTable(name: 'konzern_brand_master')]
    #[ORM\JoinColumn(name: 'konzern_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'brand_master_id', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    private Collection $brandMasters;

    public function __construct()
    {
        $this->brandMasters = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('Konzern #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getLogoMediaAsset(): ?MediaAsset
    {
        return $this->logoMediaAsset;
    }

    public function setLogoMediaAsset(?MediaAsset $logoMediaAsset): static
    {
        $this->logoMediaAsset = $logoMediaAsset;

        return $this;
    }

    public function getResolvedLogoPath(): ?string
    {
        if ($this->logoMediaAsset instanceof MediaAsset) {
            return $this->logoMediaAsset->getFilePath();
        }

        return $this->logo;
    }

    public function getUploadedLogo(): mixed
    {
        return $this->uploadedLogo;
    }

    public function setUploadedLogo(mixed $uploadedLogo): static
    {
        $this->uploadedLogo = $uploadedLogo;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, BrandMaster>
     */
    public function getBrandMasters(): Collection
    {
        return $this->brandMasters;
    }

    public function addBrandMaster(BrandMaster $brandMaster): static
    {
        if (!$this->brandMasters->contains($brandMaster)) {
            $this->brandMasters->add($brandMaster);
        }

        return $this;
    }

    public function removeBrandMaster(BrandMaster $brandMaster): static
    {
        $this->brandMasters->removeElement($brandMaster);

        return $this;
    }
}
