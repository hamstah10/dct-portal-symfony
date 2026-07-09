<?php

namespace App\Entity;

use App\Repository\EmailLabelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailLabelRepository::class)]
#[ORM\Table(name: 'email_label')]
#[ORM\UniqueConstraint(name: 'UNIQ_EMAIL_LABEL_NAME', columns: ['name'])]
class EmailLabel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name = '';

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $color = null;

    #[ORM\Column]
    private int $sorting = 0;

    /**
     * @var Collection<int, EmailKonto>
     */
    #[ORM\ManyToMany(targetEntity: EmailKonto::class, mappedBy: 'labels')]
    private Collection $emailKonten;

    public function __construct()
    {
        $this->emailKonten = new ArrayCollection();
    }

    public function __toString(): string
    {
        $name = trim($this->name);
        if ('' !== $name) {
            return $name;
        }

        return 'Label #' . ($this->id ?? 0);
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = null === $color ? null : trim($color);

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

    /**
     * @return Collection<int, EmailKonto>
     */
    public function getEmailKonten(): Collection
    {
        return $this->emailKonten;
    }

    /**
     * @param iterable<EmailKonto> $emailKonten
     */
    public function setEmailKonten(iterable $emailKonten): static
    {
        foreach ($this->emailKonten->toArray() as $existingEmailKonto) {
            if ($existingEmailKonto instanceof EmailKonto) {
                $this->removeEmailKonto($existingEmailKonto);
            }
        }

        foreach ($emailKonten as $emailKonto) {
            if ($emailKonto instanceof EmailKonto) {
                $this->addEmailKonto($emailKonto);
            }
        }

        return $this;
    }

    public function addEmailKonto(EmailKonto $emailKonto): static
    {
        if (!$this->emailKonten->contains($emailKonto)) {
            $this->emailKonten->add($emailKonto);
            $emailKonto->addLabel($this);
        }

        return $this;
    }

    public function removeEmailKonto(EmailKonto $emailKonto): static
    {
        if ($this->emailKonten->removeElement($emailKonto)) {
            $emailKonto->removeLabel($this);
        }

        return $this;
    }
}
