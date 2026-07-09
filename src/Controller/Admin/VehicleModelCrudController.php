<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\VehicleModel;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/vehiclemodel', routeName: 'admin_vehiclemodel')]
final class VehicleModelCrudController extends AbstractCrudController
{
    use ActiveVehicleDataPoolIndexFilterTrait;
    use HandlesImageUploadsTrait;

    public static function getEntityFqcn(): string
    {
        return VehicleModel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Modell')
            ->setEntityLabelInPlural('Modelle')
            ->overrideTemplate('crud/index', 'admin/vehicle_model/index.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            AssociationField::new('vehicleDataPool', 'Datenbestand'),
            AssociationField::new('vehicleSeries')->autocomplete(),
            TextField::new('name'),
            Field::new('uploadedImage', 'Bild hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            TextField::new('image', 'Bild')
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderSingleImagePreview($value)),
            CkEditor5Field::new('description'),
            TextField::new('slug'),
            IntegerField::new('sorting'),
            BooleanField::new('active'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleModel) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/vehicle-model');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }
            $entityInstance->setUploadedImage(null);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleModel) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/vehicle-model');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }
            $entityInstance->setUploadedImage(null);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
