<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\EcuOption;
use App\Entity\MediaAsset;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/ecu-option', routeName: 'admin_ecu_option')]
final class EcuOptionCrudController extends AbstractCrudController
{
    use ActiveVehicleDataPoolIndexFilterTrait;
    use HandlesImageUploadsTrait;

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public static function getEntityFqcn(): string
    {
        return EcuOption::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->query->getBoolean('embed')) {
            $crud->overrideTemplate('layout', 'admin/embedded/layout.html.twig');
        }

        return $crud
            ->setEntityLabelInSingular('ECU-Option')
            ->setEntityLabelInPlural('ECU-Optionen')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action): Action => $action
                ->setLabel('Loeschen')
                ->setIcon('internal:delete')
                ->asDangerAction()
                ->askConfirmation())
            ->update(Crud::PAGE_DETAIL, Action::DELETE, static fn (Action $action): Action => $action
                ->setLabel('Loeschen')
                ->setIcon('internal:delete')
                ->asDangerAction()
                ->askConfirmation());
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('vehicleDataPool')
            ->add(NullFilter::new('masterOption', 'Master-Referenz')->setChoiceLabels('Master fehlt', 'Master gesetzt'));
    }

    public function configureFields(string $pageName): iterable
    {
        $nameField = TextField::new('name');
        if (Crud::PAGE_INDEX === $pageName) {
            $nameField = $nameField
                ->renderAsHtml()
                ->formatValue(static function (?string $value, ?EcuOption $entity): string {
                    $label = htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $path = trim((string) ($entity?->getResolvedIconPath() ?? ''));

                    if ('' === $path) {
                        return $label;
                    }

                    if (!str_starts_with($path, '/') && !preg_match('#^https?://#i', $path)) {
                        $path = '/' . $path;
                    }

                    $escapedPath = htmlspecialchars($path, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    return sprintf(
                        '<span style="display:inline-flex;align-items:center;gap:8px;"><img src="%s" alt="" style="width:42px;height:42px;object-fit:cover;border:1px solid #d9e0ea;"><span>%s</span></span>',
                        $escapedPath,
                        $label
                    );
                });
        }

        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('vehicleDataPool', 'Datenbestand'),
            AssociationField::new('masterOption', 'Master-Option')->setRequired(false),
            $nameField,
            CkEditor5Field::new('description')->hideOnIndex(),
            CkEditor5Field::new('tooltipText'),
            MoneyField::new('price')->setCurrency('EUR')->setStoredAsCents(false),
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
            TextField::new('resolvedIconPath', 'Icon Vorschau')
                ->hideOnForm()
                ->hideOnIndex()
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
            Field::new('uploadedImage', 'Bild hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            TextField::new('image', 'Bild')
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderSingleImagePreview($value)),
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
            TextField::new('slug')->hideOnIndex(),
            BooleanField::new('isMainOption'),
            BooleanField::new('active', 'Aktiv'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof EcuOption) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/ecu-option');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }

            $existing = $this->toPathList($entityInstance->getImages());
            $uploaded = $this->storeMultipleUploads($entityInstance->getUploadedImages(), 'uploads/ecu-option');
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
        if ($entityInstance instanceof EcuOption) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/ecu-option');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }

            $existing = $this->toPathList($entityInstance->getImages());
            $uploaded = $this->storeMultipleUploads($entityInstance->getUploadedImages(), 'uploads/ecu-option');
            if ([] !== $uploaded) {
                $entityInstance->setImages(implode("\n", array_values(array_unique(array_merge($existing, $uploaded)))));
            }

            $entityInstance->setUploadedImage(null);
            $entityInstance->setUploadedImages([]);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof EcuOption) {
            foreach ($entityInstance->getEcus() as $ecu) {
                $ecu->removeOption($entityInstance);
            }

            $entityManager->flush();
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }
}
