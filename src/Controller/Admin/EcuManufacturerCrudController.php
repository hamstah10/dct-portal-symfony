<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\EcuManufacturer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/ecu-manufacturer', routeName: 'admin_ecu_manufacturer')]
final class EcuManufacturerCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public static function getEntityFqcn(): string
    {
        return EcuManufacturer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->query->getBoolean('embed')) {
            $crud->overrideTemplate('layout', 'admin/embedded/layout.html.twig');
        }

        return $crud
            ->setEntityLabelInSingular('ECU-Hersteller')
            ->setEntityLabelInPlural('ECU-Hersteller');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(NullFilter::new('masterManufacturer', 'Master-Referenz')->setChoiceLabels('Master fehlt', 'Master gesetzt'));
    }

    public function configureFields(string $pageName): iterable
    {
        $nameField = TextField::new('name');
        if (Crud::PAGE_INDEX === $pageName) {
            $nameField = $nameField
                ->renderAsHtml()
                ->formatValue(static function (?string $value, ?EcuManufacturer $entity): string {
                    $label = htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $path = trim((string) ($entity?->getLogo() ?? ''));

                    if ('' === $path) {
                        return $label;
                    }

                    $url = '/' . ltrim($path, '/');
                    $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $isSvg = str_ends_with(strtolower($path), '.svg');

                    if ($isSvg) {
                        return sprintf(
                            '<span style="display:inline-flex;align-items:center;gap:10px;"><span style="display:inline-block;width:56px;height:56px;background:#bd1e21;-webkit-mask:url(\'%s\') center / contain no-repeat;mask:url(\'%s\') center / contain no-repeat;"></span><span>%s</span></span>',
                            $escapedUrl,
                            $escapedUrl,
                            $label
                        );
                    }

                    return sprintf(
                        '<span style="display:inline-flex;align-items:center;gap:10px;"><span style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;"><img src="%s" alt="" style="width:56px;height:56px;object-fit:contain;"></span><span>%s</span></span>',
                        $escapedUrl,
                        $label
                    );
                });
        }

        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('masterManufacturer', 'Master-Hersteller')->setRequired(false),
            $nameField,
            Field::new('uploadedLogo', 'Logo hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            TextField::new('logo', 'Logo')
                ->hideOnForm()
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(static function (?string $value): string {
                    $path = trim((string) ($value ?? ''));
                    if ('' === $path) {
                        return '<span class="text-muted">Kein Bild</span>';
                    }

                    $url = '/' . ltrim($path, '/');
                    $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                    return sprintf(
                        '<span style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border:1px solid #d5d9e0;background:#121212;"><img src="%s" alt="" style="width:56px;height:56px;object-fit:contain;"></span>',
                        $escapedUrl
                    );
                }),
            CkEditor5Field::new('description'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof EcuManufacturer) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedLogo(), 'uploads/ecu-manufacturer');
            if (null !== $path) {
                $entityInstance->setLogo($path);
            }
            $entityInstance->setUploadedLogo(null);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof EcuManufacturer) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedLogo(), 'uploads/ecu-manufacturer');
            if (null !== $path) {
                $entityInstance->setLogo($path);
            }
            $entityInstance->setUploadedLogo(null);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
