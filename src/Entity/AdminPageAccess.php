<?php

namespace App\Entity;

use App\Repository\AdminPageAccessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminPageAccessRepository::class)]
#[ORM\Table(name: 'admin_page_access')]
#[ORM\UniqueConstraint(name: 'UNIQ_ADMIN_PAGE_ACCESS_PAGE_KEY', columns: ['page_key'])]
class AdminPageAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'page_key', length: 120)]
    private string $pageKey = '';

    #[ORM\Column(options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(name: 'include_children', options: ['default' => true])]
    private bool $includeChildren = true;

    /**
     * @var Collection<int, UserGroup>
     */
    #[ORM\ManyToMany(targetEntity: UserGroup::class)]
    #[ORM\JoinTable(name: 'admin_page_access_user_group')]
    private Collection $allowedGroups;

    public function __construct()
    {
        $this->allowedGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPageKey(): string
    {
        return $this->pageKey;
    }

    public function setPageKey(string $pageKey): static
    {
        $this->pageKey = trim($pageKey);

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isIncludeChildren(): bool
    {
        return $this->includeChildren;
    }

    public function setIncludeChildren(bool $includeChildren): static
    {
        $this->includeChildren = $includeChildren;

        return $this;
    }

    /**
     * @return Collection<int, UserGroup>
     */
    public function getAllowedGroups(): Collection
    {
        return $this->allowedGroups;
    }

    public function addAllowedGroup(UserGroup $group): static
    {
        if (!$this->allowedGroups->contains($group)) {
            $this->allowedGroups->add($group);
        }

        return $this;
    }

    public function removeAllowedGroup(UserGroup $group): static
    {
        $this->allowedGroups->removeElement($group);

        return $this;
    }
}
