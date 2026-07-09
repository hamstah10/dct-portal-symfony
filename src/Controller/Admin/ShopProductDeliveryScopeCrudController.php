<?php

namespace App\Controller\Admin;

use App\Entity\ShopProductDeliveryScope;
use App\Repository\ShopProductDeliveryScopeRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ecommerce/lieferumfang', routeName: 'admin_shop_delivery_scope')]
final class ShopProductDeliveryScopeCrudController extends AbstractCrudController
{
    use HandlesManualUidTrait;

    public function __construct(private readonly ShopProductDeliveryScopeRepository $repository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ShopProductDeliveryScope::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Lieferumfang')
            ->setEntityLabelInPlural('Lieferumfaenge')
            ->setDefaultSort(['sorting' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('uid')->hideOnForm(),
            TextField::new('title', 'Titel'),
            AssociationField::new('product', 'Produkt')->autocomplete(),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
            IntegerField::new('sorting', 'Sortierung'),
            BooleanField::new('hidden', 'Versteckt')->hideOnIndex(),
            BooleanField::new('deleted', 'Geloescht')->hideOnIndex(),
            IntegerField::new('pid')->onlyOnDetail(),
            IntegerField::new('tstamp')->onlyOnDetail(),
            IntegerField::new('crdate')->onlyOnDetail(),
            IntegerField::new('sysLanguageUid', 'Sprache')->onlyOnDetail(),
            IntegerField::new('l10nParent', 'L10n Parent')->onlyOnDetail(),
            TextField::new('l10nState', 'L10n State')->onlyOnDetail(),
            TextareaField::new('l10nDiffsource', 'L10n Diffsource')->onlyOnDetail(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopProductDeliveryScope) {
            $this->applyCreateDefaults($entityInstance, $this->repository->getNextUid());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopProductDeliveryScope) {
            $this->applyUpdateDefaults($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
