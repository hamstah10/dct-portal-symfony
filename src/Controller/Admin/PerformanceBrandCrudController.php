<?php

namespace App\Controller\Admin;

use App\Entity\MediaAsset;
use App\Entity\PerformanceBrand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/performance-brand', routeName: 'admin_performance_brand')]
final class PerformanceBrandCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public static function getEntityFqcn(): string
    {
        return PerformanceBrand::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Sportmarke')
            ->setEntityLabelInPlural('Sportmarken');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('resolvedImagePath', 'Logo')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(static function (?string $value): string {
                    $image = trim((string) ($value ?? ''));
                    if ('' === $image) {
                        return '<span class="text-muted">-</span>';
                    }

                    if (!str_starts_with($image, '/') && !preg_match('#^https?://#i', $image)) {
                        $image = '/' . $image;
                    }

                    $escapedImage = htmlspecialchars($image, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    return sprintf(
                        '<img src="%s" alt="Logo" style="width:42px;height:42px;object-fit:contain;border:1px solid #d9e0ea;border-radius:6px;background:#fff;padding:4px;">',
                        $escapedImage
                    );
                }),
            TextField::new('name'),
            BooleanField::new('active', 'Aktiv'),
            Field::new('uploadedImage', 'Logo hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            AssociationField::new('imageMediaAsset', 'Logo (Media Manager)')
                ->renderAsNativeWidget()
                ->hideOnIndex()
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'Logo auswaehlen')
                ->setFormTypeOption('attr', [
                    'data-stage-icon-media-select' => '1',
                    'data-placeholder' => 'Logo auswaehlen',
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
            TextField::new('image', 'Logo Pfad (Fallback)')
                ->hideOnIndex()
                ->setHelp('Optional: manueller Pfad. Bei Upload wird dieser Pfad automatisch gesetzt.'),
            TextField::new('resolvedImagePath', 'Logo Vorschau')
                ->hideOnForm()
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderSingleImagePreview($value)),
            TextareaField::new('description')->hideOnIndex(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof PerformanceBrand) {
            $this->handleImageUpload($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof PerformanceBrand) {
            $this->handleImageUpload($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleImageUpload(PerformanceBrand $performanceBrand): void
    {
        $path = $this->storeSingleUpload($performanceBrand->getUploadedImage(), 'uploads/performance-brand');
        if (null !== $path) {
            $performanceBrand->setImage($path);
            $performanceBrand->setImageMediaAsset(null);
        }

        $performanceBrand->setUploadedImage(null);
    }
}
