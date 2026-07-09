<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\Ecu;
use App\Entity\Gearbox;
use App\Entity\MediaAsset;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/gearbox', routeName: 'admin_gearbox')]
final class GearboxCrudController extends AbstractCrudController
{
    use ActiveVehicleDataPoolIndexFilterTrait;
    use HandlesImageUploadsTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Gearbox::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->query->getBoolean('embed')) {
            $crud->overrideTemplate('layout', 'admin/embedded/layout.html.twig');
        }

        return $crud
            ->setEntityLabelInSingular('Getriebe')
            ->setEntityLabelInPlural('Getriebe');
    }

    public function createEntity(string $entityFqcn): Gearbox
    {
        $gearbox = new Gearbox();

        $ecuId = $this->resolveFromEcuId();
        if (null === $ecuId) {
            return $gearbox;
        }

        $ecu = $this->entityManager->getRepository(Ecu::class)->find($ecuId);
        if (!$ecu instanceof Ecu) {
            return $gearbox;
        }

        $gearbox
            ->setTitle($ecu->getName())
            ->setManufacturer($ecu->getManufacturer())
            ->setVehicleDataPool($ecu->getVehicleDataPool())
            ->addEcu($ecu);

        return $gearbox;
    }

    private function resolveFromEcuId(): ?int
    {
        $raw = $this->requestStack->getCurrentRequest()?->query->get('fromEcu');
        if (!is_scalar($raw)) {
            return null;
        }

        $id = filter_var((string) $raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return false === $id ? null : (int) $id;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('vehicleDataPool', 'Datenbestand'),
            AssociationField::new('gearboxType'),
            AssociationField::new('manufacturer', 'ECU-Hersteller')->autocomplete(),
            AssociationField::new('ecus')
                ->autocomplete()
                ->setFormTypeOption('autocomplete_callback', static function (Ecu $ecu): string {
                    $manufacturerName = $ecu->getManufacturer()?->getName();

                    if (null === $manufacturerName || '' === trim($manufacturerName)) {
                        return $ecu->getName();
                    }

                    return sprintf('%s - %s', $manufacturerName, $ecu->getName());
                }),
            TextField::new('title'),
            CkEditor5Field::new('description'),
            Field::new('uploadedImage', 'Icon hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            AssociationField::new('iconMediaAsset', 'Icon (Media Manager)')
                ->renderAsNativeWidget()
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
                ->setHelp('Optional: manueller Pfad, falls kein Media-Asset ausgewaehlt ist.'),
            TextField::new('resolvedIconPath', 'Icon Vorschau')
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(static function (?string $value): string {
                    $path = trim((string) ($value ?? ''));
                    if ('' === $path) {
                        return '<span class="text-muted">-</span>';
                    }

                    if (!str_starts_with($path, '/') && !preg_match('#^https?://#i', $path)) {
                        $path = '/' . $path;
                    }

                    $escapedPath = htmlspecialchars($path, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    return sprintf(
                        '<img src="%s" alt="Icon" style="width:42px;height:42px;object-fit:cover;border:1px solid #d9e0ea;">',
                        $escapedPath
                    );
                }),
            Field::new('uploadedImages', 'Bilder hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'multiple' => true,
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            TextField::new('images', 'Bilderserie')
                ->hideOnForm()
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderMultipleImagePreview($value)),
            TextareaField::new('images', 'Bildpfade')
                ->onlyOnForms(),
            IntegerField::new('sorting'),
            BooleanField::new('active'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Gearbox) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/gearbox');
            if (null !== $path) {
                $entityInstance->setIcon($path);
                $entityInstance->setIconMediaAsset(null);
            }

            $existing = $this->toPathList($entityInstance->getImages());
            $uploaded = $this->storeMultipleUploads($entityInstance->getUploadedImages(), 'uploads/gearbox');
            if ([] !== $uploaded) {
                $entityInstance->setImages(implode("\n", array_values(array_unique(array_merge($existing, $uploaded)))));
            }

            $entityInstance->setUploadedImage(null);
            $entityInstance->setUploadedImages([]);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Gearbox) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/gearbox');
            if (null !== $path) {
                $entityInstance->setIcon($path);
                $entityInstance->setIconMediaAsset(null);
            }

            $existing = $this->toPathList($entityInstance->getImages());
            $uploaded = $this->storeMultipleUploads($entityInstance->getUploadedImages(), 'uploads/gearbox');
            if ([] !== $uploaded) {
                $entityInstance->setImages(implode("\n", array_values(array_unique(array_merge($existing, $uploaded)))));
            }

            $entityInstance->setUploadedImage(null);
            $entityInstance->setUploadedImages([]);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
