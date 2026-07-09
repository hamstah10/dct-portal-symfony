<?php

namespace App\Controller\Admin;

use App\Entity\ToolManufacturer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/tool-manufacturer', routeName: 'admin_tool_manufacturer')]
final class ToolManufacturerCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ToolManufacturer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->query->getBoolean('embed')) {
            $crud->overrideTemplate('layout', 'admin/embedded/layout.html.twig');
        }

        return $crud
            ->setEntityLabelInSingular('Tool-Hersteller')
            ->setEntityLabelInPlural('Tool-Hersteller');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('logo', 'Logo')
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderToolManufacturerLogo($value)),
            TextField::new('name'),
            TextareaField::new('beschreibung'),
            TextField::new('adresse'),
            TextField::new('plz'),
            TextField::new('stadt'),
            TextField::new('land'),
            TextField::new('telefon'),
            TextField::new('email'),
            Field::new('uploadedLogo', 'Logo hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            Field::new('uploadedImages', 'Bilder hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'multiple' => true,
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            TextareaField::new('images'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ToolManufacturer) {
            $logoPath = $this->storeSingleUpload($entityInstance->getUploadedLogo(), 'uploads/tool-manufacturer');
            if (null !== $logoPath) {
                $entityInstance->setLogo($logoPath);
            }

            $existing = $this->toPathList($entityInstance->getImages());
            $uploaded = $this->storeMultipleUploads($entityInstance->getUploadedImages(), 'uploads/tool-manufacturer');
            if ([] !== $uploaded) {
                $entityInstance->setImages(implode("\n", array_values(array_unique(array_merge($existing, $uploaded)))));
            }

            $entityInstance->setUploadedLogo(null);
            $entityInstance->setUploadedImages([]);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ToolManufacturer) {
            $logoPath = $this->storeSingleUpload($entityInstance->getUploadedLogo(), 'uploads/tool-manufacturer');
            if (null !== $logoPath) {
                $entityInstance->setLogo($logoPath);
            }

            $existing = $this->toPathList($entityInstance->getImages());
            $uploaded = $this->storeMultipleUploads($entityInstance->getUploadedImages(), 'uploads/tool-manufacturer');
            if ([] !== $uploaded) {
                $entityInstance->setImages(implode("\n", array_values(array_unique(array_merge($existing, $uploaded)))));
            }

            $entityInstance->setUploadedLogo(null);
            $entityInstance->setUploadedImages([]);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function renderToolManufacturerLogo(?string $value): string
    {
        $path = trim((string) ($value ?? ''));
        if ('' === $path) {
            return '<span class="text-muted">Kein Logo</span>';
        }

        $url = '/' . ltrim($path, '/');
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<a href="%1$s" data-lightbox-url="%1$s" data-lightbox-type="image" data-lightbox-caption="Logo Vorschau" title="Logo Vorschau" aria-label="Logo Vorschau" style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border:1px solid #d9e0ea;"><img src="%1$s" alt="" style="width:58px;height:58px;object-fit:contain;"></a>',
            $escapedUrl
        );
    }
}
