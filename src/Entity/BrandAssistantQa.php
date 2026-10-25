<?php

namespace App\Entity;

use App\Repository\BrandAssistantQaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Eine Frage an den Marken-Assistenten mit der gegebenen Antwort. Grundlage für das Wissensprojekt „Marken-Wissen“.
 */
#[ORM\Entity(repositoryClass: BrandAssistantQaRepository::class)]
#[ORM\Table(name: 'brand_assistant_qa')]
#[ORM\Index(name: 'IDX_BRAND_ASSISTANT_QA_CREATED', columns: ['created_at'])]
class BrandAssistantQa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $question = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $answer = '';

    #[ORM\Column(length: 120)]
    private string $model = '';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $question, string $answer, string $model)
    {
        $this->question = $question;
        $this->answer = $answer;
        $this->model = $model;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
