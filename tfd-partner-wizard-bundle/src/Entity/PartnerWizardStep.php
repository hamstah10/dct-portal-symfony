<?php

namespace Tfd\PartnerWizardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tfd_partner_wizard_step')]
class PartnerWizardStep
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartnerWizardClient::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PartnerWizardClient $client = null;

    #[ORM\Column(length: 80)]
    private string $stepKey = '';

    #[ORM\Column(length: 140)]
    private string $label = '';

    #[ORM\Column(length: 20)]
    private string $type = 'questions'; // questions, recommendation, contact

    #[ORM\Column]
    private int $priority = 100;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    #[ORM\OneToMany(mappedBy: 'step', targetEntity: PartnerWizardQuestion::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->label;
    }

    public function getId(): ?int { return $this->id; }
    public function getClient(): ?PartnerWizardClient { return $this->client; }
    public function setClient(?PartnerWizardClient $client): self { $this->client = $client; return $this; }
    public function getStepKey(): string { return $this->stepKey; }
    public function setStepKey(string $stepKey): self { $this->stepKey = $stepKey; return $this; }
    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): self { $this->label = $label; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function getPriority(): int { return $this->priority; }
    public function setPriority(int $priority): self { $this->priority = $priority; return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): self { $this->active = $active; return $this; }

    /**
     * @return Collection<int, PartnerWizardQuestion>
     */
    public function getQuestions(): Collection { return $this->questions; }
}
