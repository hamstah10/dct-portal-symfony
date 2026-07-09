<?php

namespace App\Controller\Admin;

use App\Entity\DtcCode;
use App\Repository\DtcCodeRepository;
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

#[AdminCrud(routePath: '/dtc/codes', routeName: 'admin_dtc_code')]
final class DtcCodeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly DtcCodeRepository $codeRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return DtcCode::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('DTC-Code')
            ->setEntityLabelInPlural('DTC-Codes')
            ->setDefaultSort(['fehlercode' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('uid')->hideOnForm(),
            TextField::new('fehlercode', 'Code'),
            AssociationField::new('category', 'Kategorie')->autocomplete(),
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
        if ($entityInstance instanceof DtcCode) {
            if (0 === $entityInstance->getUid()) {
                $entityInstance->setUid($this->codeRepository->getNextUid());
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
        if ($entityInstance instanceof DtcCode) {
            $entityInstance->setTstamp(time());
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
