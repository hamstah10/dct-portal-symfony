<?php

namespace App\Controller\Admin;

use App\Entity\ShopCategory;
use App\Repository\ShopCategoryRepository;
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

#[AdminCrud(routePath: '/ecommerce/kategorien', routeName: 'admin_shop_category')]
final class ShopCategoryCrudController extends AbstractCrudController
{
    use HandlesManualUidTrait;

    public function __construct(private readonly ShopCategoryRepository $repository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ShopCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Kategorie')
            ->setEntityLabelInPlural('Kategorien')
            ->setDefaultSort(['sorting' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('uid')->hideOnForm(),
            TextField::new('title', 'Titel'),
            TextField::new('slug', 'Slug')->hideOnIndex(),
            AssociationField::new('parent', 'Elternkategorie')->autocomplete()->setRequired(false),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
            TextField::new('image', 'Bild')->hideOnIndex(),
            IntegerField::new('sorting', 'Sortierung'),
            BooleanField::new('hidden', 'Versteckt'),
            BooleanField::new('deleted', 'Geloescht')->hideOnIndex(),
            IntegerField::new('starttime', 'Start')->onlyOnDetail(),
            IntegerField::new('endtime', 'Ende')->onlyOnDetail(),
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
        if ($entityInstance instanceof ShopCategory) {
            $this->applyCreateDefaults($entityInstance, $this->repository->getNextUid());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopCategory) {
            $this->applyUpdateDefaults($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
