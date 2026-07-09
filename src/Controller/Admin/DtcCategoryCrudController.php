<?php

namespace App\Controller\Admin;

use App\Entity\DtcCategory;
use App\Repository\DtcCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/dtc/kategorien', routeName: 'admin_dtc_category')]
final class DtcCategoryCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly DtcCategoryRepository $categoryRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return DtcCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('DTC-Kategorie')
            ->setEntityLabelInPlural('DTC-Kategorien')
            ->setDefaultSort(['buchstabe' => 'ASC', 'name' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('uid')->hideOnForm(),
            TextField::new('name', 'Name'),
            TextField::new('buchstabe', 'Buchstabe'),
            TextareaField::new('beschreibung', 'Beschreibung')
                ->hideOnIndex()
                ->setRequired(false),
            BooleanField::new('hidden', 'Versteckt'),
            BooleanField::new('deleted', 'Geloescht'),
            IntegerField::new('pid')->onlyOnDetail(),
            IntegerField::new('tstamp')->onlyOnDetail(),
            IntegerField::new('crdate')->onlyOnDetail(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof DtcCategory) {
            if (0 === $entityInstance->getUid()) {
                $entityInstance->setUid($this->categoryRepository->getNextUid());
            }

            if (0 === $entityInstance->getCrdate()) {
                $entityInstance->setCrdate(time());
            }

            $entityInstance->setTstamp(time());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof DtcCategory) {
            $entityInstance->setTstamp(time());
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
