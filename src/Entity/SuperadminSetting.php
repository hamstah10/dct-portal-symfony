<?php

namespace App\Entity;

use App\Repository\SuperadminSettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Generische Key-Value-Einstellungen für das Superadmin-Portal.
 */
#[ORM\Entity(repositoryClass: SuperadminSettingRepository::class)]
#[ORM\Table(name: 'superadmin_setting')]
class SuperadminSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'setting_key', length: 100, unique: true)]
    private string $settingKey = '';

    #[ORM\Column(name: 'setting_value', type: Types::JSON, nullable: true)]
    private mixed $settingValue = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSettingKey(): string
    {
        return $this->settingKey;
    }

    public function setSettingKey(string $settingKey): static
    {
        $this->settingKey = $settingKey;

        return $this;
    }

    public function getSettingValue(): mixed
    {
        return $this->settingValue;
    }

    public function setSettingValue(mixed $settingValue): static
    {
        $this->settingValue = $settingValue;

        return $this;
    }
}
