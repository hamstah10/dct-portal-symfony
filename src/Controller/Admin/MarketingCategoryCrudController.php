<?php

namespace App\Controller\Admin;

use App\Entity\MarketingCategory;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/marketing/kategorien', routeName: 'admin_marketing_category')]
final class MarketingCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MarketingCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Marketing-Kategorie')
            ->setEntityLabelInPlural('Marketing-Kategorien')
            ->setDefaultSort(['title' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $uxIconField = ChoiceField::new('uxIcon', 'UX Icon')
            ->setChoices($this->buildUxIconChoices())
            ->allowMultipleChoices(false)
            ->renderExpanded(false);


        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titel'),
            AssociationField::new('icon', 'Media Icon'),
            $uxIconField,
            TextField::new('uxIcon', 'UX Icon Vorschau')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(static function (?string $value): string {
                    $icon = trim((string) $value);
                    if ('' === $icon) {
                        return '<span class="text-muted">—</span>';
                    }

                    $safe = htmlspecialchars($icon, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    return sprintf(
                        '<span style="display:inline-flex;align-items:center;gap:8px;"><iconify-icon icon="%s" width="18" height="18"></iconify-icon><code>%s</code></span>',
                        $safe,
                        $safe
                    );
                }),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildUxIconChoices(): array
    {
        $basePath = dirname(__DIR__, 3) . '/assets/icons';
        if (!is_dir($basePath)) {
            return [];
        }

        $choices = [];
        $collections = glob($basePath . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($collections as $collectionDir) {
            $collection = basename($collectionDir);
            if ('' === $collection) {
                continue;
            }

            $icons = glob($collectionDir . '/*.svg') ?: [];
            foreach ($icons as $iconFile) {
                $name = pathinfo($iconFile, PATHINFO_FILENAME);
                if ('' === $name) {
                    continue;
                }

                $iconId = $collection . ':' . $name;
                $choices[$iconId] = $iconId;
            }
        }

        ksort($choices, SORT_NATURAL | SORT_FLAG_CASE);

        return $choices;
    }
}
