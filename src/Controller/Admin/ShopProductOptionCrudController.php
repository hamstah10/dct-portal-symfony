<?php

namespace App\Controller\Admin;

use App\Entity\ShopProductOption;
use App\Repository\ShopProductOptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ecommerce/optionen', routeName: 'admin_shop_product_option')]
final class ShopProductOptionCrudController extends AbstractCrudController
{
    use HandlesManualUidTrait;

    public function __construct(private readonly ShopProductOptionRepository $repository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ShopProductOption::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Produktoption')
            ->setEntityLabelInPlural('Produktoptionen')
            ->setDefaultSort(['sorting' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $typeChoices = [
            'Checkbox' => 'checkbox',
            'Select' => 'select',
            'Radio' => 'radio',
            'Text' => 'text',
        ];

        return [
            IdField::new('uid')->hideOnForm(),
            TextField::new('title', 'Titel'),
            ChoiceField::new('type', 'Typ')->setChoices($typeChoices)->hideOnIndex(),
            BooleanField::new('isRequired', 'Pflicht'),
            IntegerField::new('valuesCount', 'Werte (Anzahl)')->hideOnIndex(),
            AssociationField::new('product', 'Produkt')->autocomplete(),
            IntegerField::new('sorting', 'Sortierung'),
            BooleanField::new('hidden', 'Versteckt')->hideOnIndex(),
            BooleanField::new('deleted', 'Geloescht')->hideOnIndex(),
            IntegerField::new('pid')->onlyOnDetail(),
            IntegerField::new('tstamp')->onlyOnDetail(),
            IntegerField::new('crdate')->onlyOnDetail(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopProductOption) {
            $this->applyCreateDefaults($entityInstance, $this->repository->getNextUid());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopProductOption) {
            $this->applyUpdateDefaults($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
