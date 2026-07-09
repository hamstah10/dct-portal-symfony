<?php

namespace App\Controller\Admin;

use App\Entity\ShopPaymentMethod;
use App\Repository\ShopPaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ecommerce/zahlungen', routeName: 'admin_shop_payment')]
final class ShopPaymentMethodCrudController extends AbstractCrudController
{
    use HandlesManualUidTrait;

    public function __construct(private readonly ShopPaymentMethodRepository $repository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ShopPaymentMethod::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Zahlungsart')
            ->setEntityLabelInPlural('Zahlungsarten')
            ->setDefaultSort(['sortOrder' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('uid')->hideOnForm(),
            TextField::new('title', 'Titel'),
            IntegerField::new('sortOrder', 'Sortierung'),
            BooleanField::new('isActive', 'Aktiv'),
            BooleanField::new('isSandbox', 'Sandbox')->hideOnIndex(),
            TextField::new('icon', 'Icon')->hideOnIndex(),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
            TextField::new('handlerClass', 'Handler')->hideOnIndex(),
            TextField::new('clientId', 'Client ID')->hideOnIndex(),
            TextField::new('secret', 'Secret')->hideOnIndex(),
            TextField::new('apiUrl', 'API URL')->hideOnIndex(),
            BooleanField::new('hidden', 'Versteckt')->hideOnIndex(),
            BooleanField::new('deleted', 'Geloescht')->hideOnIndex(),
            IntegerField::new('pid')->onlyOnDetail(),
            IntegerField::new('tstamp')->onlyOnDetail(),
            IntegerField::new('crdate')->onlyOnDetail(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopPaymentMethod) {
            $this->applyCreateDefaults($entityInstance, $this->repository->getNextUid());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopPaymentMethod) {
            $this->applyUpdateDefaults($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
