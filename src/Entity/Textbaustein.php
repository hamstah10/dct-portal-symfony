<?php

namespace App\Entity;

use App\Repository\TextbausteinRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TextbausteinRepository::class)]
#[ORM\Table(name: 'tfd_textbaustein')]
#[ORM\UniqueConstraint(name: 'uniq_textbaustein_key', columns: ['tkey'])]
class Textbaustein
{
    public const TYPE_SHORT = 'short';
    public const TYPE_LONG = 'long';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'tkey', length: 120)]
    private string $key = '';

    #[ORM\Column(name: 'ttype', length: 10)]
    private string $type = self::TYPE_SHORT;

    #[ORM\Column(name: 'text_de', type: Types::TEXT, nullable: true)]
    private ?string $textDe = null;

    #[ORM\Column(name: 'text_en', type: Types::TEXT, nullable: true)]
    private ?string $textEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private int $sorting = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTextDe(): ?string
    {
        return $this->textDe;
    }

    public function setTextDe(?string $textDe): static
    {
        $this->textDe = $textDe;

        return $this;
    }

    public function getTextEn(): ?string
    {
        return $this->textEn;
    }

    public function setTextEn(?string $textEn): static
    {
        $this->textEn = $textEn;

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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }

    public static function getTypeChoices(): array
    {
        return [
            'Kurztext' => self::TYPE_SHORT,
            'Langtext' => self::TYPE_LONG,
        ];
    }
}
