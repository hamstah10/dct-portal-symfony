<?php

namespace App\Controller\Admin;

use App\Entity\ShopTax;
use App\Repository\ShopTaxRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ecommerce/steuern', routeName: 'admin_shop_tax')]
final class ShopTaxCrudController extends AbstractCrudController
{
    use HandlesManualUidTrait;

    public function __construct(private readonly ShopTaxRepository $repository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ShopTax::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Steuersatz')
            ->setEntityLabelInPlural('Steuersaetze')
            ->setDefaultSort(['sorting' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('uid')->hideOnForm(),
            TextField::new('title', 'Titel'),
            NumberField::new('rate', 'Satz')->setNumDecimals(2),
            TextField::new('country', 'Land')->hideOnIndex(),
            BooleanField::new('isDefault', 'Standard'),
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
        if ($entityInstance instanceof ShopTax) {
            $this->applyCreateDefaults($entityInstance, $this->repository->getNextUid());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopTax) {
            $this->applyUpdateDefaults($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
