<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\MediaAsset;
use App\Entity\ProfitCalculatorOption;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/chiptuning/profitcalculator/options', routeName: 'admin_profit_calculator_options')]
final class ProfitCalculatorOptionCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public static function getEntityFqcn(): string
    {
        return ProfitCalculatorOption::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Option')
            ->setEntityLabelInPlural('Optionen')
            ->setDefaultSort(['vehicleType' => 'ASC', 'vehicleType.id' => 'ASC', 'title' => 'ASC'])
            ->overrideTemplate('crud/index', 'admin/profit_calculator/options_index.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('resolvedIconPath', 'Icon')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(static function (?string $value, ?ProfitCalculatorOption $option): string {
                    $path = trim((string) ($value ?? ''));
                    if ('' === $path && $option instanceof ProfitCalculatorOption) {
                        $path = trim((string) $option->getImage());
                    }

                    if ('' === $path) {
                        return '<span class="text-muted">-</span>';
                    }

                    if (!str_starts_with($path, '/') && !preg_match('#^https?://#i', $path)) {
                        $path = '/' . $path;
                    }

                    $escapedPath = htmlspecialchars($path, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    return sprintf(
                        '<img src="%s" alt="Icon" style="width:40px;height:40px;object-fit:cover;border:1px solid #d9e0ea;border-radius:6px;">',
                        $escapedPath
                    );
                }),
            AssociationField::new('vehicleType', 'Fahrzeugtyp')
                ->autocomplete()
                ->setSortProperty('sorting'),
            TextField::new('title', 'Titel'),
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
            NumberField::new('purchasePrice', 'EK Credits')->setHelp('Anzahl Credits (kein Euro-Wert).'),
            MoneyField::new('endPrice', 'VK')->setCurrency('EUR')->setStoredAsCents(false),
            MoneyField::new('price', 'Preis')->setCurrency('EUR')->setStoredAsCents(false)->hideOnIndex(),
            BooleanField::new('endPriceIncludeVat', 'VK inkl. MwSt')->hideOnIndex(),
            CkEditor5Field::new('description', 'Beschreibung')->hideOnIndex(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ProfitCalculatorOption) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/profitcalculator/options');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }

            $entityInstance->setUploadedImage(null);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ProfitCalculatorOption) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/profitcalculator/options');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }

            $entityInstance->setUploadedImage(null);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
