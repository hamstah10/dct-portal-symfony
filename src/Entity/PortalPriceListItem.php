<?php

namespace App\Entity;

use App\Repository\PortalPriceListItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortalPriceListItemRepository::class)]
#[ORM\Table(name: 'portal_price_list_item')]
#[ORM\UniqueConstraint(name: 'uniq_portal_price_list_item_key', columns: ['price_list_id', 'section', 'category', 'stage_code', 'code'])]
class PortalPriceListItem
{
    public const SECTION_PKW = 'pkw';
    public const SECTION_LKW = 'lkw';

    public const CATEGORY_STAGE = 'stage';
    public const CATEGORY_GEARBOX = 'gearbox';
    public const CATEGORY_OPTION = 'option';
    public const CATEGORY_OPTIONS_ONLY = 'options_only';

    public const STAGE_NONE = '';
    public const STAGE_STAGE1 = 'stage1';
    public const STAGE_STAGE2 = 'stage2';
    public const STAGE_ECO = 'eco';
    public const STAGE_TUNING = 'tuning';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PortalPriceList $priceList = null;

    #[ORM\Column(length: 20)]
    private string $section = self::SECTION_PKW;

    #[ORM\Column(length: 30)]
    private string $category = self::CATEGORY_OPTION;

    #[ORM\Column(name: 'stage_code', length: 30, options: ['default' => ''])]
    private string $stageCode = self::STAGE_NONE;

    #[ORM\Column(length: 80)]
    private string $code = '';

    #[ORM\Column(length: 190)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private int $credits = 0;

    #[ORM\Column]
    private bool $included = false;

    #[ORM\Column]
    private int $sortOrder = 0;

    #[ORM\Column]
    private bool $active = true;

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('Preisliste-Position #' . ($this->id ?? 0));
    }

    public static function getSectionChoices(): array
    {
        return [
            'PKW' => self::SECTION_PKW,
            'LKW / Agrar / Bus' => self::SECTION_LKW,
        ];
    }

    public static function getCategoryChoices(): array
    {
        return [
            'Stage' => self::CATEGORY_STAGE,
            'Getriebe' => self::CATEGORY_GEARBOX,
            'Option (im Tuning)' => self::CATEGORY_OPTION,
            'Option (Einzelkauf)' => self::CATEGORY_OPTIONS_ONLY,
        ];
    }

    public static function getStageCodeChoices(): array
    {
        return [
            'Allgemein / keine Stufe' => self::STAGE_NONE,
            'Stage 1' => self::STAGE_STAGE1,
            'Stage 2' => self::STAGE_STAGE2,
            'Eco' => self::STAGE_ECO,
            'Tuning' => self::STAGE_TUNING,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPriceList(): ?PortalPriceList
    {
        return $this->priceList;
    }

    public function setPriceList(?PortalPriceList $priceList): static
    {
        $this->priceList = $priceList;

        return $this;
    }

    public function getSection(): string
    {
        return $this->section;
    }

    public function setSection(string $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getStageCode(): string
    {
        return $this->stageCode;
    }

    public function setStageCode(string $stageCode): static
    {
        $this->stageCode = trim($stageCode);

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCredits(): int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): static
    {
        $this->credits = $credits;

        return $this;
    }

    public function isIncluded(): bool
    {
        return $this->included;
    }

    public function setIncluded(bool $included): static
    {
        $this->included = $included;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

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
}
