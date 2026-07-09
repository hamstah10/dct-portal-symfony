<?php

namespace App\Controller\Admin;

use App\Entity\ShopProductOptionValue;
use App\Repository\ShopProductOptionValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ecommerce/option-werte', routeName: 'admin_shop_product_option_value')]
final class ShopProductOptionValueCrudController extends AbstractCrudController
{
    use HandlesManualUidTrait;

    public function __construct(private readonly ShopProductOptionValueRepository $repository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ShopProductOptionValue::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Optionswert')
            ->setEntityLabelInPlural('Optionswerte')
            ->setDefaultSort(['sorting' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $priceModifier = MoneyField::new('priceModifier', 'Preisaufschlag')->setCurrency('EUR')->setStoredAsCents(false);
        $specialPrice = MoneyField::new('specialPrice', 'Sonderpreis')->setCurrency('EUR')->setStoredAsCents(false)->hideOnIndex();

        return [
            IdField::new('uid')->hideOnForm(),
            TextField::new('title', 'Titel'),
            AssociationField::new('productOption', 'Option')->autocomplete(),
            $priceModifier,
            $specialPrice,
            TextField::new('image', 'Bild')->hideOnIndex(),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
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
        if ($entityInstance instanceof ShopProductOptionValue) {
            $this->applyCreateDefaults($entityInstance, $this->repository->getNextUid());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopProductOptionValue) {
            $this->applyUpdateDefaults($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
