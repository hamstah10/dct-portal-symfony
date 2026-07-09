<?php

namespace App\Controller\Admin;

use App\Entity\ShopShippingMethod;
use App\Repository\ShopShippingMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ecommerce/versand', routeName: 'admin_shop_shipping')]
final class ShopShippingMethodCrudController extends AbstractCrudController
{
    use HandlesManualUidTrait;

    public function __construct(private readonly ShopShippingMethodRepository $repository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ShopShippingMethod::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Versandart')
            ->setEntityLabelInPlural('Versandarten')
            ->setDefaultSort(['sortOrder' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('uid')->hideOnForm(),
            TextField::new('title', 'Titel'),
            TextField::new('provider', 'Provider')->hideOnIndex(),
            MoneyField::new('price', 'Preis')->setCurrency('EUR')->setStoredAsCents(false),
            NumberField::new('minWeight', 'Min Gewicht')->setNumDecimals(2)->hideOnIndex(),
            NumberField::new('maxWeight', 'Max Gewicht')->setNumDecimals(2)->hideOnIndex(),
            BooleanField::new('isActive', 'Aktiv'),
            BooleanField::new('legacyIsActive', 'Legacy Aktiv')->hideOnIndex(),
            IntegerField::new('sortOrder', 'Sortierung'),
            IntegerField::new('legacySortOrder', 'Legacy Sort')->hideOnIndex(),
            TextField::new('logo', 'Logo')->hideOnIndex(),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
            BooleanField::new('hidden', 'Versteckt')->hideOnIndex(),
            BooleanField::new('deleted', 'Geloescht')->hideOnIndex(),
            IntegerField::new('pid')->onlyOnDetail(),
            IntegerField::new('tstamp')->onlyOnDetail(),
            IntegerField::new('crdate')->onlyOnDetail(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopShippingMethod) {
            $this->applyCreateDefaults($entityInstance, $this->repository->getNextUid());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopShippingMethod) {
            $this->applyUpdateDefaults($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
