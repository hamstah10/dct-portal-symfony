<?php

namespace Tfd\PartnerWizardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tfd_partner_wizard_question')]
class PartnerWizardQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartnerWizardStep::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PartnerWizardStep $step = null;

    #[ORM\Column(length: 180)]
    private string $title = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $helpText = null;

    #[ORM\Column(length: 40)]
    private string $answerKey = '';

    #[ORM\Column(length: 20)]
    private string $questionType = 'generic'; // generic, vehicleType, toolManufacturer, volume

    #[ORM\Column(type: 'boolean')]
    private bool $multiSelect = false;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $fieldType = null; // only used for questions in a "contact" step: text, textarea, email, select, checkbox, radio

    #[ORM\Column(type: 'boolean')]
    private bool $required = false;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $dependsOnAnswerKey = null; // answerKey of the triggering question; null = always visible

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $dependsOnValue = null; // value the triggering answer must equal/include for this question to show

    #[ORM\Column]
    private int $priority = 100;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: PartnerWizardQuestionOption::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): ?int { return $this->id; }
    public function getStep(): ?PartnerWizardStep { return $this->step; }
    public function setStep(?PartnerWizardStep $step): self { $this->step = $step; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function getHelpText(): ?string { return $this->helpText; }
    public function setHelpText(?string $helpText): self { $this->helpText = $helpText; return $this; }
    public function getAnswerKey(): string { return $this->answerKey; }
    public function setAnswerKey(string $answerKey): self { $this->answerKey = $answerKey; return $this; }
    public function getQuestionType(): string { return $this->questionType; }
    public function setQuestionType(string $questionType): self { $this->questionType = $questionType; return $this; }
    public function isMultiSelect(): bool { return $this->multiSelect; }
    public function setMultiSelect(bool $multiSelect): self { $this->multiSelect = $multiSelect; return $this; }
    public function getFieldType(): ?string { return $this->fieldType; }
    public function setFieldType(?string $fieldType): self { $this->fieldType = $fieldType; return $this; }
    public function isRequired(): bool { return $this->required; }
    public function setRequired(bool $required): self { $this->required = $required; return $this; }
    public function getDependsOnAnswerKey(): ?string { return $this->dependsOnAnswerKey; }
    public function setDependsOnAnswerKey(?string $dependsOnAnswerKey): self { $this->dependsOnAnswerKey = $dependsOnAnswerKey; return $this; }
    public function getDependsOnValue(): ?string { return $this->dependsOnValue; }
    public function setDependsOnValue(?string $dependsOnValue): self { $this->dependsOnValue = $dependsOnValue; return $this; }
    public function getPriority(): int { return $this->priority; }
    public function setPriority(int $priority): self { $this->priority = $priority; return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): self { $this->active = $active; return $this; }

    /**
     * @return Collection<int, PartnerWizardQuestionOption>
     */
    public function getOptions(): Collection { return $this->options; }
}
