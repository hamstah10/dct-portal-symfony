<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\MediaAsset;
use App\Entity\VehicleStage;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/vehiclestage', routeName: 'admin_vehiclestage')]
final class VehicleStageCrudController extends AbstractCrudController
{
    use ActiveVehicleDataPoolIndexFilterTrait;
    use HandlesImageUploadsTrait;

    public function __construct(
        private readonly Connection $connection,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return VehicleStage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Leistungsstufe')
            ->setEntityLabelInPlural('Leistungsstufen')
            ->overrideTemplate('crud/index', 'admin/vehiclestage/index.html.twig')
            ->overrideTemplate('crud/edit', 'admin/vehiclestage/edit.html.twig')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $copyAction = Action::new('copyStage', 'Kopieren')
            ->setIcon('iconamoon:copy-bold')
            ->linkToUrl(fn (VehicleStage $stage): string => $this->adminUrlGenerator
                ->unsetAll()
                ->setController(self::class)
                ->setAction(Action::NEW)
                ->set('copyFrom', (string) $stage->getId())
                ->generateUrl())
            ->displayIf(static fn (VehicleStage $stage): bool => null !== $stage->getId());

        $previewAction = Action::new('frontendPreview', 'Vorschau')
            ->setIcon('fluent:eye-16-filled')
            ->linkToUrl(function (VehicleStage $stage): string {
                $engineId = $stage->getVehicleEngine()?->getId();

                return $this->generateUrl('app_vehicle_database', array_filter([
                    'engineId' => $engineId,
                    'stageId' => $stage->getId(),
                ], static fn (mixed $value): bool => null !== $value && '' !== (string) $value));
            })
            ->setHtmlAttributes([
                'data-lightbox' => 'preview',
                'data-lightbox-type' => 'page',
                'data-lightbox-caption' => 'Frontend-Vorschau',
                'title' => 'Frontend-Vorschau',
            ])
            ->displayIf(static fn (VehicleStage $stage): bool => null !== $stage->getVehicleEngine()?->getId());

        return $actions
            ->add(Crud::PAGE_INDEX, $copyAction)
            ->add(Crud::PAGE_DETAIL, $copyAction)
            ->add(Crud::PAGE_EDIT, $copyAction)
            ->add(Crud::PAGE_INDEX, $previewAction);
    }

    public function createEntity(string $entityFqcn): VehicleStage
    {
        $entity = new VehicleStage();
        $copyFromId = $this->resolveCopyFromId();
        if (null === $copyFromId) {
            return $entity;
        }

        $source = $this->entityManager->getRepository(VehicleStage::class)->find($copyFromId);
        if (!$source instanceof VehicleStage) {
            return $entity;
        }

        return $entity
            ->setVehicleDataPool($source->getVehicleDataPool())
            ->setVehicleEngine($source->getVehicleEngine())
            ->setName($source->getName())
            ->setDescription($source->getDescription())
            ->setPrice($source->getPrice())
            ->setHp($source->getHp())
            ->setNm($source->getNm())
            ->setImages($source->getImages())
            ->setVideos($source->getVideos())
            ->setEcu($source->getEcu())
            ->setIcon($source->getIcon())
            ->setIconMediaAsset($source->getIconMediaAsset())
            ->setActive($source->isActive());
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            AssociationField::new('vehicleDataPool', 'Datenbestand')
                ->hideOnIndex(),
            AssociationField::new('vehicleEngine')
                ->autocomplete()
                ->formatValue(static function (mixed $value, ?VehicleStage $stage): string {
                    $engine = $stage?->getVehicleEngine();
                    if (null === $engine) {
                        return '-';
                    }

                    $engineName = trim((string) $engine->getName());
                    $model = $engine->getVehicleModel();
                    $series = $model?->getVehicleSeries();
                    $brand = $series?->getVehicleBrand();

                    $path = array_values(array_filter([
                        trim((string) ($brand?->getName() ?? '')),
                        trim((string) ($series?->getName() ?? '')),
                        trim((string) ($model?->getName() ?? '')),
                    ], static fn (string $item): bool => '' !== $item));

                    $base = '' !== $engineName ? $engineName : (string) $engine;
                    if ([] === $path) {
                        return $base;
                    }

                    return sprintf('%s (%s)', $base, implode(' -> ', $path));
                }),
            TextField::new('name')
                ->renderAsHtml()
                ->formatValue(static function (mixed $value): string {
                    $name = trim((string) ($value ?? ''));
                    if ('' === $name) {
                        return '-';
                    }

                    return sprintf('<strong>%s</strong>', htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                }),
            TextField::new('resolvedIconPath', 'Icon Vorschau')
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderIconPreviewOnly($value)),
            CkEditor5Field::new('description')->hideOnIndex(),
            MoneyField::new('price')->setCurrency('EUR')->setStoredAsCents(false),
            IntegerField::new('hp')
                ->formatValue(static function (mixed $value): string {
                    if (null === $value || '' === (string) $value) {
                        return '-';
                    }

                    return sprintf('%s PS', (string) $value);
                }),
            IntegerField::new('nm')
                ->formatValue(static function (mixed $value): string {
                    if (null === $value || '' === (string) $value) {
                        return '-';
                    }

                    return sprintf('%s nm', (string) $value);
                }),
            Field::new('uploadedImages', 'Bilder hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'multiple' => true,
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            TextField::new('images', 'Bilder')
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderImagesPreviewOnly($value)),
            TextareaField::new('images', 'Bildpfade')
                ->onlyOnForms(),
            TextareaField::new('videos')->hideOnIndex(),
            IntegerField::new('ecu'),
            AssociationField::new('iconMediaAsset', 'Icon (Media Manager)')
                ->renderAsNativeWidget()
                ->hideOnIndex()
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'Icon auswaehlen')
                ->setFormTypeOption('attr', [
                    'data-stage-icon-media-select' => '1',
                    'data-placeholder' => 'Icon auswaehlen',
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
                        ->setParameter('mediaTypes', [MediaAsset::TYPE_ICON, MediaAsset::TYPE_IMAGE])
                        ->orderBy(sprintf('%s.name', $rootAlias), 'ASC');
                }),
            TextField::new('icon', 'Icon Pfad (Fallback)')
                ->hideOnIndex()
                ->setHelp('Optional: manueller Pfad, falls kein Media-Asset ausgewaehlt ist.'),
            BooleanField::new('active'),
        ];
    }

    private function renderImagesPreviewOnly(?string $value): string
    {
        $paths = $this->toPathList($value);
        if ([] === $paths) {
            return '<span class="text-muted">Keine Bilder</span>';
        }

        $maxThumbs = 4;
        $thumbs = [];

        foreach (array_slice($paths, 0, $maxThumbs) as $path) {
            $url = '/' . ltrim($path, '/');
            $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $thumbs[] = sprintf(
                '<img src="%s" alt="" style="width:42px;height:42px;object-fit:cover;border:1px solid #d9e0ea;">',
                $escapedUrl
            );
        }

        if (count($paths) > $maxThumbs) {
            $thumbs[] = sprintf('<span class="text-muted small">+%d</span>', count($paths) - $maxThumbs);
        }

        return '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">' . implode('', $thumbs) . '</div>';
    }

    private function renderIconPreviewOnly(?string $value): string
    {
        $path = trim((string) ($value ?? ''));
        if ('' === $path) {
            return '<span class="text-muted">Kein Icon</span>';
        }

        if (!str_starts_with($path, '/') && !preg_match('#^https?://#i', $path)) {
            $path = '/' . $path;
        }

        $escapedPath = htmlspecialchars($path, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<img src="%s" alt="" style="width:42px;height:42px;object-fit:contain;">',
            $escapedPath
        );
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleStage) {
            $existing = $this->toPathList($entityInstance->getImages());
            $uploaded = $this->storeMultipleUploads($entityInstance->getUploadedImages(), 'uploads/vehicle-stage');
            if ([] !== $uploaded) {
                $entityInstance->setImages(implode("\n", array_values(array_unique(array_merge($existing, $uploaded)))));
            }
            $entityInstance->setUploadedImages([]);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleStage) {
            $existing = $this->toPathList($entityInstance->getImages());
            $uploaded = $this->storeMultipleUploads($entityInstance->getUploadedImages(), 'uploads/vehicle-stage');
            if ([] !== $uploaded) {
                $entityInstance->setImages(implode("\n", array_values(array_unique(array_merge($existing, $uploaded)))));
            }
            $entityInstance->setUploadedImages([]);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        $pageName = (string) $responseParameters->get('pageName');
        if (Crud::PAGE_INDEX !== $pageName) {
            return $responseParameters;
        }

        $counts = $this->connection->fetchAssociative(
            'SELECT
                SUM(CASE WHEN LOWER(REPLACE(TRIM(COALESCE(name, \'\')), \' \', \'\')) LIKE \'stage1%\' THEN 1 ELSE 0 END) AS stage1_count,
                SUM(CASE WHEN LOWER(REPLACE(TRIM(COALESCE(name, \'\')), \' \', \'\')) LIKE \'stage2%\' THEN 1 ELSE 0 END) AS stage2_count,
                SUM(CASE WHEN LOWER(REPLACE(TRIM(COALESCE(name, \'\')), \' \', \'\')) LIKE \'stage3%\' THEN 1 ELSE 0 END) AS stage3_count
             FROM tx_chiptuningdatabase_vehiclestage'
        );

        $responseParameters->set('stage1Count', (int) ($counts['stage1_count'] ?? 0));
        $responseParameters->set('stage2Count', (int) ($counts['stage2_count'] ?? 0));
        $responseParameters->set('stage3Count', (int) ($counts['stage3_count'] ?? 0));

        return $responseParameters;
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
}
