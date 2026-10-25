<?php

namespace App\Controller\Admin;

use App\Entity\ToolMethode;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/tool-methode', routeName: 'admin_tool_methode')]
final class ToolMethodeCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ToolMethode::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->query->getBoolean('embed')) {
            $crud->overrideTemplate('layout', 'admin/embedded/layout.html.twig');
        }

        return $crud
            ->setEntityLabelInSingular('Tool-Methode')
            ->setEntityLabelInPlural('Tool-Methoden');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('toolManufacturers', 'Tool-Hersteller')
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('by_reference', false),
            TextField::new('name'),
            BooleanField::new('active', 'Aktiv')->setHelp('Inaktive Methoden werden in den Frontends nicht ausgegeben.'),
            TextField::new('image', 'Bild')
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderToolMethodeImagePreview($value)),
            TextareaField::new('beschreibung'),
            Field::new('uploadedImage', 'Bild hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ToolMethode) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/tool-methode');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }
            $entityInstance->setUploadedImage(null);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ToolMethode) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/tool-methode');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }
            $entityInstance->setUploadedImage(null);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function renderToolMethodeImagePreview(?string $value): string
    {
        $path = trim((string) ($value ?? ''));
        if ('' === $path) {
            return '<span class="text-muted">Kein Bild</span>';
        }

        $url = '/' . ltrim($path, '/');
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<a href="%1$s" data-lightbox-url="%1$s" data-lightbox-type="image" data-lightbox-caption="Bild Vorschau" title="Bild Vorschau" aria-label="Bild Vorschau" style="display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border:1px solid #d9e0ea;background:#121212;"><img src="%1$s" alt="" style="width:40px;height:40px;object-fit:contain;"></a>',
            $escapedUrl
        );
    }
}
