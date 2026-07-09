<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MarketingAdSizeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarketingAdSizeRepository::class)]
class MarketingAdSize
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column]
    private ?int $width = null;

    #[ORM\Column]
    private ?int $height = null;

    #[ORM\ManyToOne(inversedBy: 'adSizes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MarketingCategory $category = null;

    public function __toString(): string
    {
        $label = $this->title ?? 'Anzeige #' . $this->id;

        if (null !== $this->width && null !== $this->height) {
            $label .= sprintf(' (%dx%d)', $this->width, $this->height);
        }

        return $label;
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
        $this->title = trim($title);

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getCategory(): ?MarketingCategory
    {
        return $this->category;
    }

    public function setCategory(?MarketingCategory $category): static
    {
        $this->category = $category;

        return $this;
    }
}
