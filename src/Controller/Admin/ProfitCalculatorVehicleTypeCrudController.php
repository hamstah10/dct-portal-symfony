<?php

namespace App\Controller\Admin;

use App\Entity\MediaAsset;
use App\Entity\ProfitCalculatorVehicleType;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/chiptuning/profitcalculator/vehicletype', routeName: 'admin_profit_calculator_vehicle_type')]
final class ProfitCalculatorVehicleTypeCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;
    public static function getEntityFqcn(): string
    {
        return ProfitCalculatorVehicleType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Fahrzeugtyp (Profit)')
            ->setEntityLabelInPlural('Fahrzeugtypen (Profit)')
            ->setDefaultSort(['sorting' => 'ASC', 'title' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('calculator', 'Calculator')->autocomplete(),
            IntegerField::new('sorting', 'Sortierung')->setHelp('Kleinere Zahl = weiter oben.'),
            TextField::new('title', 'Titel'),
            TextField::new('identifier', 'Identifier')->hideOnIndex(),
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
            ChoiceField::new('icon', 'Icon (UX)')
                ->hideOnIndex()
                ->setRequired(false)
                ->setHelp('Optional: UX-Icon Name, z.B. fluent-mdl2:car.')
                ->setChoices($this->getUxIconChoices())
                ->setFormTypeOption('placeholder', 'UX-Icon auswaehlen')
                ->setFormTypeOption('attr', [
                    'data-stage-icon-media-select' => '1',
                    'data-placeholder' => 'UX-Icon auswaehlen',
                ])
                ->setFormTypeOption('choice_attr', fn (?string $choice, string $label, mixed $value): array => $this->buildUxIconChoiceAttributes($choice)),
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
            NumberField::new('purchasePrice', 'EK Credits')->setHelp('Anzahl Credits (kein Euro-Wert).'),
            MoneyField::new('endPrice', 'VK')->setCurrency('EUR')->setStoredAsCents(false),
            BooleanField::new('purchasePriceIncludeVat', 'EK inkl. MwSt')->hideOnIndex(),
            BooleanField::new('endPriceIncludeVat', 'VK inkl. MwSt')->hideOnIndex(),
            IntegerField::new('optionsCount', 'Optionen')->hideOnForm(),
        ];
    }

    /**
     * @return array<string, string>
     */
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
            $collectionPath = $collectionDir->getPathname();

            foreach (new \DirectoryIterator($collectionPath) as $iconFile) {
                if (!$iconFile->isFile() || strtolower($iconFile->getExtension()) !== 'svg') {
                    continue;
                }

                $iconName = $iconFile->getBasename('.svg');
                $iconId = sprintf('%s:%s', $collection, $iconName);
                $choices[$iconId] = $iconId;
            }
        }

        if ([] !== $choices) {
            ksort($choices, SORT_NATURAL | SORT_FLAG_CASE);
        }

        return $choices;
    }

    /**
     * @return array<string, string>
     */
    private function buildUxIconChoiceAttributes(?string $choice): array
    {
        $iconId = trim((string) ($choice ?? ''));
        if ('' === $iconId || !str_contains($iconId, ':')) {
            return [];
        }

        [$collection, $iconName] = explode(':', $iconId, 2);
        $collection = trim($collection);
        $iconName = trim($iconName);
        if ('' === $collection || '' === $iconName) {
            return [];
        }

        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $sourcePath = sprintf('%s/assets/icons/%s/%s.svg', $projectDir, $collection, $iconName);
        if (!is_file($sourcePath)) {
            return [];
        }

        $svg = @file_get_contents($sourcePath);
        if (false === $svg || '' === trim($svg)) {
            return [];
        }

        $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);

        return [
            'data-image' => $dataUri,
        ];
    }
    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ProfitCalculatorVehicleType) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/profitcalculator/vehicle-types');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }

            $entityInstance->setUploadedImage(null);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ProfitCalculatorVehicleType) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/profitcalculator/vehicle-types');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }

            $entityInstance->setUploadedImage(null);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
