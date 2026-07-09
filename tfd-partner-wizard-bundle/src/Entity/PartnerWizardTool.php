<?php

namespace Tfd\PartnerWizardBundle\Entity;

use App\Entity\ShopProduct;
use App\Entity\VehicleType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tfd_partner_wizard_tool')]
class PartnerWizardTool
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartnerWizardClient::class, inversedBy: 'tools')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PartnerWizardClient $client = null;

    #[ORM\ManyToOne(targetEntity: ShopProduct::class)]
    #[ORM\JoinColumn(name: 'shop_product_uid', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?ShopProduct $shopProduct = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 80)]
    private string $toolKey = '';

    #[ORM\Column(length: 140)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: VehicleType::class)]
    #[ORM\JoinTable(name: 'tfd_partner_wizard_tool_vehicletype')]
    #[ORM\JoinColumn(name: 'tool_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'vehicle_type_id', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    private Collection $recommendedForVehicleCategories;

    #[ORM\Column(type: 'json')]
    private array $recommendedForServices = [];

    #[ORM\Column(length: 30)]
    private string $level = 'universal'; // slave, master, universal, special

    #[ORM\Column]
    private int $priority = 100;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    public function __construct()
    {
        $this->recommendedForVehicleCategories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int { return $this->id; }
    public function getClient(): ?PartnerWizardClient { return $this->client; }
    public function setClient(?PartnerWizardClient $client): self { $this->client = $client; return $this; }
    public function getShopProduct(): ?ShopProduct { return $this->shopProduct; }
    public function setShopProduct(?ShopProduct $shopProduct): self { $this->shopProduct = $shopProduct; return $this; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $imageUrl): self { $this->imageUrl = $imageUrl; return $this; }
    public function getToolKey(): string { return $this->toolKey; }
    public function setToolKey(string $toolKey): self { $this->toolKey = $toolKey; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    /**
     * @return Collection<int, VehicleType>
     */
    public function getRecommendedForVehicleCategories(): Collection { return $this->recommendedForVehicleCategories; }

    public function addRecommendedForVehicleCategory(VehicleType $vehicleType): self
    {
        if (!$this->recommendedForVehicleCategories->contains($vehicleType)) {
            $this->recommendedForVehicleCategories->add($vehicleType);
        }

        return $this;
    }

    public function removeRecommendedForVehicleCategory(VehicleType $vehicleType): self
    {
        $this->recommendedForVehicleCategories->removeElement($vehicleType);

        return $this;
    }
    public function getRecommendedForServices(): array { return $this->recommendedForServices; }
    public function setRecommendedForServices(array $recommendedForServices): self { $this->recommendedForServices = $recommendedForServices; return $this; }
    public function getLevel(): string { return $this->level; }
    public function setLevel(string $level): self { $this->level = $level; return $this; }
    public function getPriority(): int { return $this->priority; }
    public function setPriority(int $priority): self { $this->priority = $priority; return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): self { $this->active = $active; return $this; }
}
