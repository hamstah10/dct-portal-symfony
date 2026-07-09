<?php

namespace App\Controller\Admin;

use App\Entity\PortalPriceListItem;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/portal/preisliste-positionen', routeName: 'admin_portal_price_list_item')]
final class PortalPriceListItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PortalPriceListItem::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Preisliste-Position')
            ->setEntityLabelInPlural('Preisliste-Positionen')
            ->setDefaultSort([
                'priceList.name' => 'ASC',
                'section' => 'ASC',
                'category' => 'ASC',
                'stageCode' => 'ASC',
                'sortOrder' => 'ASC',
                'id' => 'ASC',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('priceList', 'Preisliste'),
            ChoiceField::new('section', 'Bereich')
                ->setChoices(PortalPriceListItem::getSectionChoices())
                ->renderAsNativeWidget(),
            ChoiceField::new('category', 'Kategorie')
                ->setChoices(PortalPriceListItem::getCategoryChoices())
                ->renderAsNativeWidget(),
            ChoiceField::new('stageCode', 'Stufe')
                ->setChoices(PortalPriceListItem::getStageCodeChoices())
                ->renderAsNativeWidget(),
            TextField::new('code', 'Code'),
            TextField::new('name', 'Name'),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
            IntegerField::new('credits', 'Credits'),
            BooleanField::new('included', 'Inklusive'),
            IntegerField::new('sortOrder', 'Sortierung'),
            BooleanField::new('active', 'Aktiv'),
        ];
    }
}
