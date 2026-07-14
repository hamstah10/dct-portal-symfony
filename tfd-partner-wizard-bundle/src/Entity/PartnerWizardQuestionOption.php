<?php

namespace Tfd\PartnerWizardBundle\Entity;

use App\Entity\MediaAsset;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tfd_partner_wizard_question_option')]
class PartnerWizardQuestionOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartnerWizardQuestion::class, inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PartnerWizardQuestion $question = null;

    #[ORM\Column(length: 80)]
    private string $value = '';

    #[ORM\Column(length: 140)]
    private string $title = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $text = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $icon = null;

    #[ORM\ManyToOne(targetEntity: MediaAsset::class)]
    #[ORM\JoinColumn(name: 'media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $mediaAsset = null;

    /**
     * @var mixed not persisted, used as the Dropzone/FileType form target
     */
    private mixed $uploadedIcon = null;

    #[ORM\Column(nullable: true)]
    private ?int $numericValue = null; // only used when the parent question's type is "volume"

    #[ORM\Column]
    private int $priority = 100;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): ?int { return $this->id; }
    public function getQuestion(): ?PartnerWizardQuestion { return $this->question; }
    public function setQuestion(?PartnerWizardQuestion $question): self { $this->question = $question; return $this; }
    public function getValue(): string { return $this->value; }
    public function setValue(string $value): self { $this->value = $value; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function getText(): ?string { return $this->text; }
    public function setText(?string $text): self { $this->text = $text; return $this; }
    public function getIcon(): ?string { return $this->icon; }
    public function setIcon(?string $icon): self { $this->icon = $icon; return $this; }
    public function getMediaAsset(): ?MediaAsset { return $this->mediaAsset; }
    public function setMediaAsset(?MediaAsset $mediaAsset): self { $this->mediaAsset = $mediaAsset; return $this; }
    public function getUploadedIcon(): mixed { return $this->uploadedIcon; }
    public function setUploadedIcon(mixed $uploadedIcon): self { $this->uploadedIcon = $uploadedIcon; return $this; }
    public function getNumericValue(): ?int { return $this->numericValue; }
    public function setNumericValue(?int $numericValue): self { $this->numericValue = $numericValue; return $this; }
    public function getPriority(): int { return $this->priority; }
    public function setPriority(int $priority): self { $this->priority = $priority; return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): self { $this->active = $active; return $this; }
}
