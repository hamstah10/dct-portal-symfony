<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\Antriebsart;
use App\Entity\Ecu;
use App\Entity\MediaAsset;
use App\Entity\VehicleEngine;
use App\Filter\VehicleEngineBrandFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/vehicleengine', routeName: 'admin_vehicleengine')]
final class VehicleEngineCrudController extends AbstractCrudController
{
    use ActiveVehicleDataPoolIndexFilterTrait;
    use HandlesImageUploadsTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return VehicleEngine::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Motor')
            ->setEntityLabelInPlural('Motoren')
            ->overrideTemplate('crud/index', 'admin/vehicle_engine/index.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $copyAction = Action::new('copyEngine', 'Kopieren')
            ->setIcon('iconamoon:copy-bold')
            ->linkToUrl(fn (VehicleEngine $engine): string => $this->adminUrlGenerator
                ->unsetAll()
                ->setController(self::class)
                ->setAction(Action::NEW)
                ->set('copyFrom', (string) $engine->getId())
                ->generateUrl())
            ->displayIf(static fn (VehicleEngine $engine): bool => null !== $engine->getId());

        return $actions
            ->disable(Action::DELETE)
            ->add(Crud::PAGE_INDEX, $copyAction)
            ->add(Crud::PAGE_DETAIL, $copyAction)
            ->add(Crud::PAGE_EDIT, $copyAction);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('vehicleDataPool')
            ->add(VehicleEngineBrandFilter::new('vehicleBrand', 'Hersteller'));
    }

    public function createEntity(string $entityFqcn): VehicleEngine
    {
        $entity = new VehicleEngine();
        $copyFromId = $this->resolveCopyFromId();
        if (null === $copyFromId) {
            return $entity;
        }

        $source = $this->entityManager->getRepository(VehicleEngine::class)->find($copyFromId);
        if (!$source instanceof VehicleEngine) {
            return $entity;
        }

        $entity
            ->setVehicleDataPool($source->getVehicleDataPool())
            ->setVehicleModel($source->getVehicleModel())
            ->setPerformanceBrand($source->getPerformanceBrand())
            ->setName($source->getName())
            ->setImage($source->getImage())
            ->setGlbModel($source->getGlbModel())
            ->setImageMediaAsset($source->getImageMediaAsset())
            ->setGlbMediaAsset($source->getGlbMediaAsset())
            ->setMediaDisplayMode($source->getMediaDisplayMode())
            ->setDescription($source->getDescription())
            ->setActive($source->isActive())
            ->setHorsepower($source->getHorsepower())
            ->setTorque($source->getTorque())
            ->setEcus($source->getEcus())
            ->setFuel($source->getFuel())
            ->setDisplacement($source->getDisplacement())
            ->setCompression($source->getCompression())
            ->setBoreStroke($source->getBoreStroke())
            ->setEngineCode($source->getEngineCode())
            ->setVmax($source->getVmax())
            ->setAcceleration0100($source->getAcceleration0100())
            ->setAcceleration0200($source->getAcceleration0200())
            ->setAcceleration100200($source->getAcceleration100200())
            ->setSlug($source->getSlug())
            ->setSorting($source->getSorting());

        foreach ($source->getEcuRelations() as $ecu) {
            $entity->addEcuRelation($ecu);
        }

        foreach ($source->getGearboxes() as $gearbox) {
            $entity->addGearbox($gearbox);
        }

        return $entity;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('brandGroup', 'Hersteller')
                ->onlyOnIndex(),
            TextField::new('seriesGroup', 'Baureihe')
                ->onlyOnIndex(),
            TextField::new('modelGroup', 'Modell')
                ->onlyOnIndex(),
            AssociationField::new('vehicleDataPool', 'Datenbestand')
                ->onlyOnIndex(),
            AssociationField::new('performanceBrand', 'Sportmarke')
                ->autocomplete()
                ->setRequired(false)
                ->hideOnIndex(),
            TextField::new('name'),
            IntegerField::new('horsepower')
                ->formatValue(static function (mixed $value): string {
                    if (null === $value || '' === (string) $value) {
                        return '-';
                    }

                    return sprintf('%s PS', (string) $value);
                }),
            IntegerField::new('torque')
                ->formatValue(static function (mixed $value): string {
                    if (null === $value || '' === (string) $value) {
                        return '-';
                    }

                    return sprintf('%s NM', (string) $value);
                }),
            ChoiceField::new('fuel', 'Kraftstoff')
                ->setChoices($this->getAntriebsartChoices())
                ->autocomplete()
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'Bitte waehlen'),
            AssociationField::new('ecuRelations')
                ->autocomplete()
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('by_reference', false),
            AssociationField::new('gearboxes', 'Getriebe')
                ->autocomplete()
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('by_reference', false)
                ->hideOnIndex(),
            TextField::new('image', 'Vorschau')
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (?string $value, ?VehicleEngine $engine): string => $this->renderEngineMediaPreview($engine)),
            AssociationField::new('vehicleModel')
                ->autocomplete()
                ->formatValue(static function (mixed $value, ?VehicleEngine $engine): string {
                    $model = $engine?->getVehicleModel();
                    if (null === $model) {
                        return '-';
                    }

                    $modelName = trim((string) $model->getName());
                    $series = $model->getVehicleSeries();
                    $brand = $series?->getVehicleBrand();

                    $path = array_values(array_filter([
                        trim((string) ($brand?->getName() ?? '')),
                        trim((string) ($series?->getName() ?? '')),
                        trim((string) ($modelName ?? '')),
                    ], static fn (string $item): bool => '' !== $item));

                    if ([] === $path) {
                        return '' !== $modelName ? $modelName : (string) $model;
                    }

                    return implode(' -> ', $path);
                })
                ->hideOnIndex(),
            AssociationField::new('vehicleDataPool', 'Datenbestand')
                ->hideOnIndex(),
            Field::new('uploadedImage', 'Bild hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            AssociationField::new('imageMediaAsset', 'Bild (Media Manager)')
                ->renderAsNativeWidget()
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'Bild auswaehlen')
                ->setFormTypeOption('attr', [
                    'data-stage-icon-media-select' => '1',
                    'data-placeholder' => 'Bild auswaehlen',
                ])
                ->setFormTypeOption('choice_attr', static function (?MediaAsset $mediaAsset, string $key, mixed $value): array {
                    if (!$mediaAsset instanceof MediaAsset) {
                        return [];
                    }

                    $filePath = trim((string) $mediaAsset->getFilePath());
                    if ('' === $filePath) {
                        return [];
                    }

                    if (!str_starts_with($filePath, '/') && !preg_match('#^https?://#i', $filePath)) {
                        $filePath = '/' . $filePath;
                    }

                    return [
                        'data-image' => $filePath,
                        'data-name' => $mediaAsset->getName(),
                    ];
                })
                ->setQueryBuilder(static function (QueryBuilder $queryBuilder): QueryBuilder {
                    $rootAlias = (string) (current($queryBuilder->getRootAliases()) ?: 'entity');

                    return $queryBuilder
                        ->resetDQLPart('orderBy')
                        ->andWhere(sprintf('%s.active = :active', $rootAlias))
                        ->andWhere(sprintf('%s.mediaType IN (:mediaTypes)', $rootAlias))
                        ->setParameter('active', true)
                        ->setParameter('mediaTypes', [MediaAsset::TYPE_IMAGE, MediaAsset::TYPE_ICON])
                        ->orderBy(sprintf('%s.name', $rootAlias), 'ASC');
                }),
            TextField::new('image', 'Bild')
                ->hideOnForm()
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderSingleImagePreview($value)),
            Field::new('uploadedGlbModel', 'GLB hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => '.glb,model/gltf-binary'],
                ]),
            AssociationField::new('glbMediaAsset', 'GLB (Media Manager)')
                ->renderAsNativeWidget()
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'GLB auswaehlen')
                ->setFormTypeOption('attr', [
                    'data-stage-icon-media-select' => '1',
                    'data-placeholder' => 'GLB auswaehlen',
                ])
                ->setFormTypeOption('choice_attr', static function (?MediaAsset $mediaAsset, string $key, mixed $value): array {
                    if (!$mediaAsset instanceof MediaAsset) {
                        return [];
                    }

                    $filePath = trim((string) $mediaAsset->getFilePath());
                    if ('' === $filePath) {
                        return [];
                    }

                    return [
                        'data-name' => $mediaAsset->getName(),
                    ];
                })
                ->setQueryBuilder(static function (QueryBuilder $queryBuilder): QueryBuilder {
                    $rootAlias = (string) (current($queryBuilder->getRootAliases()) ?: 'entity');

                    return $queryBuilder
                        ->resetDQLPart('orderBy')
                        ->andWhere(sprintf('%s.active = :active', $rootAlias))
                        ->andWhere(sprintf('%s.mediaType IN (:mediaTypes)', $rootAlias))
                        ->setParameter('active', true)
                        ->setParameter('mediaTypes', [MediaAsset::TYPE_MODEL])
                        ->orderBy(sprintf('%s.name', $rootAlias), 'ASC');
                }),
            TextField::new('glbModel', 'GLB-Datei')
                ->hideOnForm()
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderSingleFilePreview($value, 'GLB Modell')),
            ChoiceField::new('mediaDisplayMode', 'Medienanzeige')
                ->setChoices(VehicleEngine::getMediaDisplayModeChoices())
                ->hideOnIndex()
                ->setHelp('Waehlt, ob in der Stage-Ansicht ein Bild oder das GLB-Modell gerendert wird.'),
            CkEditor5Field::new('description')->hideOnIndex(),
            BooleanField::new('active'),
            TextField::new('displacement')->hideOnIndex(),
            TextField::new('compression')->hideOnIndex(),
            TextField::new('boreStroke')->hideOnIndex(),
            TextField::new('engineCode')->hideOnIndex(),
            IntegerField::new('vmax')->hideOnIndex(),
            NumberField::new('acceleration0100')->hideOnIndex(),
            NumberField::new('acceleration0200')->hideOnIndex(),
            NumberField::new('acceleration100200')->hideOnIndex(),
            TextField::new('slug')->hideOnIndex(),
            IntegerField::new('sorting')->hideOnIndex(),
        ];
    }

    private function normalizeEcuRelations(VehicleEngine $engine): void
    {
        $seen = [];
        $uniqueRelations = [];

        foreach ($engine->getEcuRelations()->toArray() as $ecu) {
            if (!$ecu instanceof Ecu) {
                continue;
            }

            $nameKey = mb_strtolower(trim($ecu->getName()));
            $poolKey = (string) ($ecu->getVehicleDataPool()?->getId() ?? 0);
            $key = sprintf('%s::%s', $poolKey, $nameKey);

            if ('' === $nameKey) {
                $key = null !== $ecu->getId()
                    ? 'id:' . $ecu->getId()
                    : 'object:' . spl_object_id($ecu);
            }

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $uniqueRelations[] = $ecu;
        }

        foreach ($engine->getEcuRelations()->toArray() as $ecu) {
            if ($ecu instanceof Ecu) {
                $engine->removeEcuRelation($ecu);
            }
        }

        foreach ($uniqueRelations as $ecu) {
            $engine->addEcuRelation($ecu);
        }
    }

    private function renderEngineMediaPreview(?VehicleEngine $engine): string
    {
        if (!$engine instanceof VehicleEngine) {
            return '<span class="text-muted">Keine Vorschau</span>';
        }

        $glbPath = trim((string) $engine->getGlbModel());
        $imagePath = trim((string) $engine->getImage());

        $path = '' !== $glbPath ? $glbPath : $imagePath;
        if ('' === $path) {
            return '<span class="text-muted">Keine Vorschau</span>';
        }

        $previewType = '' !== $glbPath ? 'glb' : 'image';
        $caption = '' !== $glbPath ? 'GLB Vorschau' : 'Bild Vorschau';
        $url = '/' . ltrim($path, '/');

        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $escapedType = htmlspecialchars($previewType, ENT_QUOTES, 'UTF-8');
        $escapedCaption = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<a href="%1$s" data-lightbox-url="%1$s" data-lightbox-type="%2$s" data-lightbox-caption="%3$s" title="Vorschau oeffnen" aria-label="Vorschau oeffnen" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border:1px solid #d9e0ea;text-decoration:none;color:#0f172a;"><i class="fa fa-eye" aria-hidden="true"></i></a>',
            $escapedUrl,
            $escapedType,
            $escapedCaption
        );
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleEngine) {
            $this->normalizeEcuRelations($entityInstance);

            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/vehicle-engine');
            if (null !== $path) {
                $entityInstance->setImage($path);
                $entityInstance->setImageMediaAsset(null);
            }
            $entityInstance->setUploadedImage(null);

            $glbPath = $this->storeSingleUpload($entityInstance->getUploadedGlbModel(), 'uploads/vehicle-engine-glb');
            if (null !== $glbPath) {
                $entityInstance->setGlbModel($glbPath);
                $entityInstance->setGlbMediaAsset(null);
            }
            $entityInstance->setUploadedGlbModel(null);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleEngine) {
            $this->normalizeEcuRelations($entityInstance);

            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/vehicle-engine');
            if (null !== $path) {
                $entityInstance->setImage($path);
                $entityInstance->setImageMediaAsset(null);
            }
            $entityInstance->setUploadedImage(null);

            $glbPath = $this->storeSingleUpload($entityInstance->getUploadedGlbModel(), 'uploads/vehicle-engine-glb');
            if (null !== $glbPath) {
                $entityInstance->setGlbModel($glbPath);
                $entityInstance->setGlbMediaAsset(null);
            }
            $entityInstance->setUploadedGlbModel(null);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function resolveCopyFromId(): ?int
    {
        $raw = $this->getContext()?->getRequest()->query->get('copyFrom');
        if (!is_scalar($raw)) {
            return null;
        }

        $id = filter_var((string) $raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return false === $id ? null : (int) $id;
    }

    /**
     * @return array<string, string>
     */
    private function getAntriebsartChoices(): array
    {
        $antriebsarten = $this->entityManager->getRepository(Antriebsart::class)
            ->createQueryBuilder('a')
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();

        $choices = [];
        foreach ($antriebsarten as $antriebsart) {
            if (!$antriebsart instanceof Antriebsart) {
                continue;
            }

            $name = trim($antriebsart->getName());
            if ('' === $name) {
                continue;
            }

            $choices[$name] = $name;
        }

        $legacyFuelRows = $this->entityManager->getRepository(VehicleEngine::class)
            ->createQueryBuilder('e')
            ->select('DISTINCT e.fuel AS fuel')
            ->andWhere('e.fuel IS NOT NULL')
            ->andWhere('e.fuel <> :empty')
            ->setParameter('empty', '')
            ->orderBy('e.fuel', 'ASC')
            ->getQuery()
            ->getArrayResult();

        foreach ($legacyFuelRows as $row) {
            $value = trim((string) ($row['fuel'] ?? ''));
            if ('' === $value || array_key_exists($value, $choices)) {
                continue;
            }

            $choices[$value] = $value;
        }

        return $choices;
    }
}
