<?php

namespace App\Controller\Admin;

use App\Entity\Software;
use App\Form\DemoAutocompleteField;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/software', routeName: 'admin_software')]
final class SoftwareCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Software::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Software')
            ->setEntityLabelInPlural('Software')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        $galleryField = TextField::new('bilder', 'Bilder')
            ->hideOnForm()
            ->renderAsHtml()
            ->formatValue(fn (?string $value): string => $this->renderGallery($value));

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextField::new('version'),
            TextareaField::new('beschreibung'),
            ChoiceField::new('optionen', 'Mehrfachauswahl')
                ->setChoices([
                    'TYPO3' => Software::OPTION_TYPO3,
                    'Wordpress' => Software::OPTION_WORDPRESS,
                ])
                ->allowMultipleChoices()
                ->renderExpanded(false)
                ->setHelp('Mehrfachauswahl: TYPO3, Wordpress'),
            Field::new('uploadedImages', 'Bilder zuordnen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'multiple' => true,
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            $galleryField,
            AssociationField::new('demos', 'Links zu Demos')
                ->setRequired(false)
                ->setFormType(DemoAutocompleteField::class)
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('by_reference', false)
                ->setHelp('Mehrere Demos zuordnen moeglich.'),
            MoneyField::new('preis')->setCurrency('EUR')->setStoredAsCents(false),
            BooleanField::new('aktiv'),
            DateTimeField::new('createdAt')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Software) {
            $this->storeUploadedImages($entityInstance, 'uploads/software');
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Software) {
            $this->storeUploadedImages($entityInstance, 'uploads/software');
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function storeUploadedImages(Software $entity, string $relativeUploadDir): void
    {
        $uploadedFiles = array_values(array_filter(
            $entity->getUploadedImages(),
            static fn (mixed $file): bool => $file instanceof UploadedFile
        ));

        if ([] === $uploadedFiles) {
            return;
        }

        $absoluteUploadDir = $this->getParameter('kernel.project_dir') . '/public/' . $relativeUploadDir;
        if (!is_dir($absoluteUploadDir)) {
            mkdir($absoluteUploadDir, 0775, true);
        }

        $existing = $this->toImageList($entity->getBilder());
        $newPaths = [];

        foreach ($uploadedFiles as $uploadedFile) {
            $extension = $uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension() ?: 'bin';
            $fileName = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), strtolower($extension));
            $uploadedFile->move($absoluteUploadDir, $fileName);
            $newPaths[] = $relativeUploadDir . '/' . $fileName;
        }

        $entity->setBilder(implode("\n", array_values(array_unique(array_merge($existing, $newPaths)))));
        $entity->setUploadedImages([]);
    }

    /**
     * @return array<int, string>
     */
    private function toImageList(?string $value): array
    {
        if (null === $value || '' === trim($value)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(array_filter(array_map(static fn (string $line): string => trim($line), $lines)));
    }

    private function renderGallery(?string $value): string
    {
        $images = $this->toImageList($value);
        if ([] === $images) {
            return '<span class="text-muted">Keine Bilder</span>';
        }

        $html = [];
        foreach ($images as $image) {
            $url = '/' . ltrim($image, '/');
            $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $html[] = sprintf(
                '<button type="button" data-lightbox-url="%1$s" data-lightbox-caption="Software Bild" style="border:0;background:transparent;padding:0;margin:0;cursor:zoom-in;"><img src="%1$s" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:6px;border:1px solid #d5d9e0;margin:0 6px 6px 0;"></button>',
                $escapedUrl
            );
        }

        return implode('', $html);
    }
}
