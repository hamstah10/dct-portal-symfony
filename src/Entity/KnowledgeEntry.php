<?php

namespace App\Entity;

use App\Repository\KnowledgeEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Frage oder Entscheidung aus der Planung des KI-Wissens (Wissensdatenbank im Superadmin).
 */
#[ORM\Entity(repositoryClass: KnowledgeEntryRepository::class)]
#[ORM\Table(name: 'knowledge_entry')]
#[ORM\Index(name: 'IDX_KNOWLEDGE_ENTRY_POSITION', columns: ['position'])]
class KnowledgeEntry
{
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_OPEN = 'open';
    public const STATUS_CHECKED = 'checked';

    public const STATUSES = [self::STATUS_ANSWERED, self::STATUS_OPEN, self::STATUS_CHECKED];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 16, unique: true)]
    private string $code = '';

    #[ORM\Column(length: 120)]
    private string $groupTitle = '';

    #[ORM\Column(length: 16)]
    private string $status = self::STATUS_ANSWERED;

    #[ORM\Column(type: Types::TEXT)]
    private string $question = '';

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $answerLabel = null;

    /**
     * @var list<string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $answer = null;

    /**
     * @var list<string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $listItems = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGroupTitle(): string
    {
        return $this->groupTitle;
    }

    public function setGroupTitle(string $groupTitle): static
    {
        $this->groupTitle = $groupTitle;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswerLabel(): ?string
    {
        return $this->answerLabel;
    }

    public function setAnswerLabel(?string $answerLabel): static
    {
        $this->answerLabel = $answerLabel;

        return $this;
    }

    /**
     * @return list<string>|null
     */
    public function getAnswer(): ?array
    {
        return $this->answer;
    }

    /**
     * @param list<string>|null $answer
     */
    public function setAnswer(?array $answer): static
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * @return list<string>|null
     */
    public function getListItems(): ?array
    {
        return $this->listItems;
    }

    /**
     * @param list<string>|null $listItems
     */
    public function setListItems(?array $listItems): static
    {
        $this->listItems = $listItems;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
