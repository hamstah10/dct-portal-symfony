<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\MediaAsset;
use App\Entity\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/vehicletype', routeName: 'admin_vehicletype')]
final class VehicleTypeCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public static function getEntityFqcn(): string
    {
        return VehicleType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Fahrzeugtyp')
            ->setEntityLabelInPlural('Fahrzeugtypen');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('name'),
            CkEditor5Field::new('description'),
            Field::new('uploadedImage', 'Bild hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            AssociationField::new('imageMediaAsset', 'Bild (Media Library)')
                ->renderAsNativeWidget()
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'Bild auswählen')
                ->setFormTypeOption('attr', [
                    'data-stage-icon-media-select' => '1',
                    'data-placeholder' => 'Bild auswählen',
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
                })
                ->hideOnIndex(),
            ChoiceField::new('icon', 'Icon (UX Icons)')
                ->hideOnIndex()
                ->setRequired(false)
                ->setHelp('Optional: UX-Icon aus der Icon Library, z.B. fluent-mdl2:car.')
                ->setChoices($this->getUxIconChoices())
                ->setFormTypeOption('placeholder', 'UX-Icon auswählen')
                ->setFormTypeOption('attr', [
                    'data-stage-icon-media-select' => '1',
                    'data-placeholder' => 'UX-Icon auswählen',
                ])
                ->setFormTypeOption('choice_attr', fn (?string $choice, string $label, mixed $value): array => $this->buildUxIconChoiceAttributes($choice)),
            TextField::new('image', 'Bild')
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(function (?string $value, VehicleType $entity): string {
                    $icon = $entity->getIcon();
                    if (null !== $icon && '' !== $icon && str_contains($icon, ':')) {
                        [$collection, $iconName] = explode(':', $icon, 2);
                        $projectDir = (string) $this->getParameter('kernel.project_dir');
                        $svgPath = sprintf('%s/assets/icons/%s/%s.svg', $projectDir, trim($collection), trim($iconName));
                        if (is_file($svgPath)) {
                            $svg = @file_get_contents($svgPath);
                            if (false !== $svg && '' !== trim($svg)) {
                                $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);
                                return sprintf('<img src="%s" alt="%s" style="width:40px;height:40px;object-fit:contain;">', htmlspecialchars($dataUri, ENT_QUOTES), htmlspecialchars($icon, ENT_QUOTES));
                            }
                        }
                    }
                    return $this->renderSingleImagePreview($value);
                }),
            TextField::new('slug'),
            IntegerField::new('sorting'),
            BooleanField::new('active'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleType) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/vehicle-type');
            if (null !== $path) {
                $entityInstance->setImage($path);
                $entityInstance->setImageMediaAsset(null);
            } elseif (null !== $entityInstance->getImageMediaAsset()) {
                $entityInstance->setImage($entityInstance->getImageMediaAsset()->getFilePath());
            }
            $entityInstance->setUploadedImage(null);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleType) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/vehicle-type');
            if (null !== $path) {
                $entityInstance->setImage($path);
                $entityInstance->setImageMediaAsset(null);
            } elseif (null !== $entityInstance->getImageMediaAsset()) {
                $entityInstance->setImage($entityInstance->getImageMediaAsset()->getFilePath());
            }
            $entityInstance->setUploadedImage(null);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    /** @return array<string, string> */
    private function getUxIconChoices(): array
    {
        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $iconsBase = $projectDir . '/assets/icons';
        if (!is_dir($iconsBase)) {
            return [];
        }

        $choices = [];
        foreach (new \DirectoryIterator($iconsBase) as $collectionDir) {
            if (!$collectionDir->isDir() || $collectionDir->isDot()) {
                continue;
            }
            $collection = $collectionDir->getBasename();
            foreach (new \DirectoryIterator($collectionDir->getPathname()) as $iconFile) {
                if (!$iconFile->isFile() || strtolower($iconFile->getExtension()) !== 'svg') {
                    continue;
                }
                $iconId = sprintf('%s:%s', $collection, $iconFile->getBasename('.svg'));
                $choices[$iconId] = $iconId;
            }
        }

        if ([] !== $choices) {
            ksort($choices, SORT_NATURAL | SORT_FLAG_CASE);
        }

        return $choices;
    }

    /** @return array<string, string> */
    private function buildUxIconChoiceAttributes(?string $choice): array
    {
        $iconId = trim((string) ($choice ?? ''));
        if ('' === $iconId || !str_contains($iconId, ':')) {
            return [];
        }

        [$collection, $iconName] = explode(':', $iconId, 2);
        if ('' === trim($collection) || '' === trim($iconName)) {
            return [];
        }

        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $sourcePath = sprintf('%s/assets/icons/%s/%s.svg', $projectDir, trim($collection), trim($iconName));
        if (!is_file($sourcePath)) {
            return [];
        }

        $svg = @file_get_contents($sourcePath);
        if (false === $svg || '' === trim($svg)) {
            return [];
        }

        return [
            'data-image' => 'data:image/svg+xml;base64,' . base64_encode($svg),
        ];
    }
}
