<?php

namespace App\Entity;

use App\Entity\MediaAsset;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tx_chiptuningdatabase_ecu_option_master')]
class EcuOptionMaster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'category_master_id', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?EcuOptionCategoryMaster $categoryMaster = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'icon_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $iconMediaAsset = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $moduleSlug = null;

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('EcuOptionMaster #' . ($this->id ?? 0));
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
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategoryMaster(): ?EcuOptionCategoryMaster
    {
        return $this->categoryMaster;
    }

    public function setCategoryMaster(?EcuOptionCategoryMaster $categoryMaster): static
    {
        $this->categoryMaster = $categoryMaster;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getModuleSlug(): ?string
    {
        return $this->moduleSlug;
    }

    public function setModuleSlug(?string $moduleSlug): static
    {
        $this->moduleSlug = $moduleSlug;

        return $this;
    }

    public function getIconMediaAsset(): ?MediaAsset
    {
        return $this->iconMediaAsset;
    }

    public function setIconMediaAsset(?MediaAsset $iconMediaAsset): static
    {
        $this->iconMediaAsset = $iconMediaAsset;

        return $this;
    }

    public function getResolvedIconPath(): ?string
    {
        $mediaAssetPath = trim((string) ($this->iconMediaAsset?->getFilePath() ?? ''));
        if ('' !== $mediaAssetPath) {
            return $mediaAssetPath;
        }

        $icon = trim((string) ($this->icon ?? ''));

        return '' !== $icon ? $icon : null;
    }
}
