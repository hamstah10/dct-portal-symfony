<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Filter\BrandMasterLogoFilter;
use App\Entity\BrandMaster;
use App\Entity\MediaAsset;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/brand-master', routeName: 'admin_brand_master')]
final class BrandMasterCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public static function getEntityFqcn(): string
    {
        return BrandMaster::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Brand-Master')
            ->setEntityLabelInPlural('Brand-Master');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('vehicleType', 'Typ'))
            ->add(BrandMasterLogoFilter::new('image', 'Logo'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('vehicleType', 'Type')->setRequired(false),
            TextField::new('resolvedImagePath', 'Icon')
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
                        '<img src="%s" alt="Icon" style="width:42px;height:42px;object-fit:contain;border:1px solid #d9e0ea;border-radius:6px;background:#fff;padding:4px;">',
                        $escapedImage
                    );
                }),
            TextField::new('name'),
            TextField::new('slug'),
            BooleanField::new('active', 'Aktiv'),
            Field::new('uploadedImage', 'Bild hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            AssociationField::new('imageMediaAsset', 'Bild/Icon (Media Manager)')
                ->renderAsNativeWidget()
                ->hideOnIndex()
                ->setRequired(false)
                ->setFormTypeOption('placeholder', 'Bild oder Icon auswaehlen')
                ->setFormTypeOption('attr', [
                    'data-stage-icon-media-select' => '1',
                    'data-placeholder' => 'Bild oder Icon auswaehlen',
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
            TextField::new('image', 'Bild Pfad (Fallback)')
                ->hideOnIndex()
                ->setHelp('Optional: manueller Pfad. Bei Upload wird dieser Pfad automatisch gesetzt.'),
            TextField::new('resolvedImagePath', 'Bild Vorschau')
                ->hideOnForm()
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderSingleImagePreview($value)),
            TextareaField::new('description')->hideOnIndex(),
            TextareaField::new('synonyms', 'Synonyme')
                ->hideOnIndex()
                ->setRequired(false)
                ->setHelp('Alternative Suchbegriffe, kommagetrennt oder zeilenweise – z.B. "vw, volkswagon". Die Marke wird dann auch über diese Begriffe gefunden.'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof BrandMaster) {
            $this->handleImageUpload($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof BrandMaster) {
            $this->handleImageUpload($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleImageUpload(BrandMaster $brandMaster): void
    {
        $path = $this->storeSingleUpload($brandMaster->getUploadedImage(), 'uploads/brand-master');
        if (null !== $path) {
            $brandMaster->setImage($path);
            $brandMaster->setImageMediaAsset(null);
        }

        $brandMaster->setUploadedImage(null);
    }
}
