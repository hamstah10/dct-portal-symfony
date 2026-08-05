<?php

namespace App\Controller\Admin;

use App\Entity\EcuOption;
use App\Entity\EcuOptionCategoryMaster;
use App\Entity\MediaAsset;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/fahrzeugdatenbank/ecu-option-category-master', routeName: 'admin_ecu_option_category_master')]
final class EcuOptionCategoryMasterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EcuOptionCategoryMaster::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ECU-Option-Kategorie-Master')
            ->setEntityLabelInPlural('ECU-Option-Kategorie-Master')
            ->setDefaultSort(['sorting' => 'ASC', 'name' => 'ASC'])
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

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('resolvedIconPath', 'Icon')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(static function (?string $value): string {
                    $icon = trim((string) ($value ?? ''));
                    if ('' === $icon) {
                        return '<span class="text-muted">-</span>';
                    }

                    if (str_contains($icon, ':') && !preg_match('#^https?://#i', $icon)) {
                        $escapedIcon = htmlspecialchars($icon, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                        return sprintf(
                            '<span style="display:inline-flex;align-items:center;gap:8px;"><iconify-icon icon="%s" width="26" height="26"></iconify-icon><code>%s</code></span>',
                            $escapedIcon,
                            $escapedIcon
                        );
                    }

                    if (!str_starts_with($icon, '/') && !preg_match('#^https?://#i', $icon)) {
                        $icon = '/' . $icon;
                    }

                    $escapedIcon = htmlspecialchars($icon, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    return sprintf(
                        '<img src="%s" alt="Icon" style="width:42px;height:42px;object-fit:contain;border:1px solid #d9e0ea;border-radius:6px;background:#fff;padding:4px;">',
                        $escapedIcon
                    );
                }),
            TextField::new('name'),
            TextField::new('slug'),
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
            TextField::new('icon', 'Icon Pfad (Fallback)')->hideOnIndex(),
            TextareaField::new('description')->hideOnIndex(),
            BooleanField::new('active', 'Aktiv'),
            IntegerField::new('sorting', 'Sortierung')->hideOnIndex(),
        ];
    }

    public function deleteEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof EcuOptionCategoryMaster) {
            $options = $entityManager->getRepository(EcuOption::class)->findBy([
                'categoryMaster' => $entityInstance,
            ]);

            foreach ($options as $option) {
                if ($option instanceof EcuOption) {
                    $option->setCategoryMaster(null);
                }
            }

            $entityManager->flush();
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }
}
