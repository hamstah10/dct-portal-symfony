<?php

namespace App\Entity;

use App\Repository\AuftragRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuftragRepository::class)]
#[ORM\Table(name: 'auftrag')]
#[ORM\HasLifecycleCallbacks]
class Auftrag
{
    public const STATUS_RECEIVED = 'eingegangen';
    public const STATUS_IN_PROGRESS = 'in_bearbeitung';
    public const STATUS_COMPLETED = 'abgeschlossen';
    public const STATUS_REJECTED = 'abgelehnt';
    public const STATUS_PARKED = 'parked';

    public const AUTOMOD_STATUS_UNAVAILABLE = 0;
    public const AUTOMOD_STATUS_PENDING = 1;
    public const AUTOMOD_STATUS_DECRYPTED = 2;
    public const AUTOMOD_STATUS_ENCRYPTED = 3;
    public const AUTOMOD_STATUS_COMPLETED = 4;
    public const AUTOMOD_STATUS_FAILED = 9;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'auftraege')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Kunde $kunde = null;

    #[ORM\Column(length: 180)]
    private ?string $customerEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $fileData = null;

    #[ORM\Column]
    private ?int $fileSize = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $finishedFilePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $finishedFileName = null;

    #[ORM\Column(nullable: true)]
    private ?int $finishedFileSize = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finishedFileUploadedAt = null;

    private mixed $uploadedFinishedFile = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $finishedFiles = null;

    /**
     * @var array<int, mixed>
     */
    private array $uploadedFinishedFiles = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $vehicleData = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $vehicleType = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $manufacturer = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $built = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $engine = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vehicleDisplay = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $tuningTool = null;

    #[ORM\Column(name: 'tuning_method', length: 80, nullable: true)]
    private ?string $tuningMethod = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $slaveOrMaster = null;

    #[ORM\Column(name: 'stage_label', length: 80, nullable: true)]
    private ?string $stageLabel = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $deviceName = null;

    #[ORM\Column(length: 50)]
    private string $status = self::STATUS_RECEIVED;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    private ?string $adminOnHoldMessage = null;

    #[ORM\Column(length: 26, unique: true, nullable: true)]
    private ?string $ulid = null;

    #[ORM\Column]
    private bool $autoModEnabled = false;

    #[ORM\Column(nullable: true)]
    private ?int $autoModStatus = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $winolsId = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $autoModSubmittedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $autoModFailureReason = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->status = self::STATUS_RECEIVED;
    }

    public function __toString(): string
    {
        return sprintf(
            'Auftrag #%d - %s',
            $this->id ?? 0,
            (string) ($this->fileName ?? '')
        );
    }

    public static function getStatusChoices(): array
    {
        return [
            'Eingegangen' => self::STATUS_RECEIVED,
            'In Bearbeitung' => self::STATUS_IN_PROGRESS,
            'Geparkt' => self::STATUS_PARKED,
            'Abgeschlossen' => self::STATUS_COMPLETED,
            'Abgelehnt' => self::STATUS_REJECTED,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): string
    {
        if (null === $this->id) {
            return 'A-NEU';
        }

        return sprintf('A-%06d', $this->id);
    }

    public function getKunde(): ?Kunde
    {
        return $this->kunde;
    }

    public function setKunde(?Kunde $kunde): static
    {
        $this->kunde = $kunde;

        return $this;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): static
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileData(): ?string
    {
        return $this->fileData;
    }

    public function setFileData(string $fileData): static
    {
        $this->fileData = $fileData;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getFinishedFilePath(): ?string
    {
        return $this->finishedFilePath;
    }

    public function setFinishedFilePath(?string $finishedFilePath): static
    {
        $this->finishedFilePath = $finishedFilePath;

        return $this;
    }

    public function getFinishedFileName(): ?string
    {
        return $this->finishedFileName;
    }

    public function setFinishedFileName(?string $finishedFileName): static
    {
        $this->finishedFileName = $finishedFileName;

        return $this;
    }

    public function getFinishedFileSize(): ?int
    {
        return $this->finishedFileSize;
    }

    public function setFinishedFileSize(?int $finishedFileSize): static
    {
        $this->finishedFileSize = $finishedFileSize;

        return $this;
    }

    public function getFinishedFileUploadedAt(): ?\DateTimeImmutable
    {
        return $this->finishedFileUploadedAt;
    }

    public function setFinishedFileUploadedAt(?\DateTimeImmutable $finishedFileUploadedAt): static
    {
        $this->finishedFileUploadedAt = $finishedFileUploadedAt;

        return $this;
    }

    public function getUploadedFinishedFile(): mixed
    {
        return $this->uploadedFinishedFile;
    }

    public function setUploadedFinishedFile(mixed $uploadedFinishedFile): static
    {
        $this->uploadedFinishedFile = $uploadedFinishedFile;

        return $this;
    }

    /**
     * @return array<int, mixed>
     */
    public function getUploadedFinishedFiles(): array
    {
        return $this->uploadedFinishedFiles;
    }

    /**
     * @param array<int, mixed> $uploadedFinishedFiles
     */
    public function setUploadedFinishedFiles(array $uploadedFinishedFiles): static
    {
        $this->uploadedFinishedFiles = $uploadedFinishedFiles;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFinishedFiles(): array
    {
        return is_array($this->finishedFiles) ? $this->finishedFiles : [];
    }

    /**
     * @param array<int, array<string, mixed>>|null $finishedFiles
     */
    public function setFinishedFiles(?array $finishedFiles): static
    {
        $this->finishedFiles = [] === ($finishedFiles ?? []) ? null : $finishedFiles;

        return $this;
    }

    public function getVehicleData(): ?array
    {
        return $this->vehicleData;
    }

    public function getVehicleDataJson(): string
    {
        if (!is_array($this->vehicleData) || [] === $this->vehicleData) {
            return 'Keine Fahrzeug-Daten uebermittelt.';
        }

        try {
            return (string) json_encode(
                $this->vehicleData,
                \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            return 'Fahrzeug-Daten konnten nicht als JSON dargestellt werden.';
        }
    }

    public function setVehicleData(?array $vehicleData): static
    {
        $this->vehicleData = [] === ($vehicleData ?? []) ? null : $vehicleData;

        return $this;
    }

    public function getVehicleSeries(): ?string
    {
        $value = $this->getVehicleDataValue('series', 'vehicleSeries');

        return is_string($value) && '' !== trim($value) ? trim($value) : null;
    }

    public function getEcu(): ?string
    {
        $value = $this->getVehicleDataValue('ecu', 'ecuVal');

        return is_string($value) && '' !== trim($value) ? trim($value) : null;
    }

    public function getReadingType(): ?string
    {
        $value = $this->getVehicleDataValue('readingType', 'readType');

        return is_string($value) && '' !== trim($value) ? trim($value) : null;
    }

    public function getPriorityLabel(): ?string
    {
        $value = $this->getVehicleDataValue('priority');

        return is_string($value) && '' !== trim($value) ? trim($value) : null;
    }

    public function getSelectedOptionsSummary(): string
    {
        $value = $this->getVehicleDataValue('selectedOptions');
        if (!is_array($value) || [] === $value) {
            return '-';
        }

        $labels = [];
        foreach ($value as $item) {
            if (is_string($item) && '' !== trim($item)) {
                $labels[] = trim($item);
            }
        }

        return [] === $labels ? '-' : implode(', ', $labels);
    }

    public function getOrderComment(): ?string
    {
        $value = $this->getVehicleDataValue('comment', 'notes');

        return is_string($value) && '' !== trim($value) ? trim($value) : null;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getVehicleType(): ?string
    {
        return $this->vehicleType;
    }

    public function setVehicleType(?string $vehicleType): static
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?string $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getBuilt(): ?string
    {
        return $this->built;
    }

    public function setBuilt(?string $built): static
    {
        $this->built = $built;

        return $this;
    }

    public function getEngine(): ?string
    {
        return $this->engine;
    }

    public function setEngine(?string $engine): static
    {
        $this->engine = $engine;

        return $this;
    }

    public function getVehicleDisplay(): ?string
    {
        return $this->vehicleDisplay;
    }

    public function setVehicleDisplay(?string $vehicleDisplay): static
    {
        $this->vehicleDisplay = $vehicleDisplay;

        return $this;
    }

    public function getTuningTool(): ?string
    {
        return $this->tuningTool;
    }

    public function setTuningTool(?string $tuningTool): static
    {
        $this->tuningTool = $tuningTool;

        return $this;
    }

    public function getTuningMethod(): ?string
    {
        return $this->tuningMethod;
    }

    public function setTuningMethod(?string $tuningMethod): static
    {
        $this->tuningMethod = $tuningMethod;

        return $this;
    }

    public function getSlaveOrMaster(): ?string
    {
        return $this->slaveOrMaster;
    }

    public function getTuningSetupSummary(): string
    {
        $parts = [];

        foreach ([$this->tuningTool, $this->tuningMethod, $this->slaveOrMaster] as $value) {
            $normalized = trim((string) $value);
            if ('' !== $normalized) {
                $parts[] = $normalized;
            }
        }

        if ([] === $parts) {
            return '-';
        }

        return implode(' / ', $parts);
    }

    public function setSlaveOrMaster(?string $slaveOrMaster): static
    {
        $this->slaveOrMaster = $slaveOrMaster;

        return $this;
    }

    public function getStageLabel(): ?string
    {
        return $this->stageLabel;
    }

    public function setStageLabel(?string $stageLabel): static
    {
        $this->stageLabel = $stageLabel;

        return $this;
    }

    public function getDeviceName(): ?string
    {
        return $this->deviceName;
    }

    public function setDeviceName(?string $deviceName): static
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    public function setStatus(string $status): static
    {
        $normalized = strtolower(trim($status));
        $validValues = array_values(self::getStatusChoices());
        $this->status = in_array($normalized, $validValues, true) ? $normalized : self::STATUS_RECEIVED;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getAdminOnHoldMessage(): ?string
    {
        return $this->adminOnHoldMessage;
    }

    public function setAdminOnHoldMessage(?string $adminOnHoldMessage): static
    {
        $this->adminOnHoldMessage = null !== $adminOnHoldMessage ? trim($adminOnHoldMessage) : null;

        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function ensureTimestampsOnPersist(): void
    {
        $now = new \DateTimeImmutable();
        if (null === $this->createdAt) {
            $this->createdAt = $now;
        }
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function getVehicleDataValue(string ...$keys): mixed
    {
        $vehicleData = $this->getVehicleData();
        if (!is_array($vehicleData)) {
            return null;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $vehicleData)) {
                return $vehicleData[$key];
            }
        }

        return null;
    }

    public function getUlid(): ?string
    {
        return $this->ulid;
    }

    public function setUlid(?string $ulid): static
    {
        $this->ulid = $ulid;

        return $this;
    }

    public function isAutoModEnabled(): bool
    {
        return $this->autoModEnabled;
    }

    public function setAutoModEnabled(bool $autoModEnabled): static
    {
        $this->autoModEnabled = $autoModEnabled;

        return $this;
    }

    public function getAutoModStatus(): ?int
    {
        return $this->autoModStatus;
    }

    public function setAutoModStatus(?int $autoModStatus): static
    {
        $this->autoModStatus = $autoModStatus;

        return $this;
    }

    public function getWinolsId(): ?string
    {
        return $this->winolsId;
    }

    public function setWinolsId(?string $winolsId): static
    {
        $this->winolsId = $winolsId;

        return $this;
    }

    public function getAutoModSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->autoModSubmittedAt;
    }

    public function setAutoModSubmittedAt(?\DateTimeImmutable $autoModSubmittedAt): static
    {
        $this->autoModSubmittedAt = $autoModSubmittedAt;

        return $this;
    }

    public function getAutoModFailureReason(): ?string
    {
        return $this->autoModFailureReason;
    }

    public function setAutoModFailureReason(?string $autoModFailureReason): static
    {
        $this->autoModFailureReason = $autoModFailureReason;

        return $this;
    }

    public function getAutoModModificationName(): string
    {
        $stage = trim((string) $this->getVehicleDataValue('selectedStage'));
        if ('' !== $stage && preg_match('/eco/i', $stage)) {
            return 'Stage 1';
        }

        return '' !== $stage ? $stage : 'Stage 1';
    }

    /** @return array<int, string> */
    public function getAutoModModificationOptions(): array
    {
        $codes = $this->getVehicleDataValue('selectedOptionCodes');
        $options = is_array($codes) && [] !== $codes ? $codes : $this->getVehicleDataValue('selectedOptions');
        if (!is_array($options)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $value): ?string => is_string($value) ? strtoupper(trim($value)) : null,
            $options
        ), static fn (?string $value): bool => null !== $value && '' !== $value && 'VMAX' !== $value));
    }

    /** @return array<string, mixed> */
    public function getSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'customerEmail' => $this->customerEmail,
            'fileName' => $this->fileName,
            'vehicleDisplay' => $this->vehicleDisplay,
            'vehicleType' => $this->vehicleType,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'engine' => $this->engine,
            'tuningTool' => $this->tuningTool,
            'status' => $this->status,
            'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
