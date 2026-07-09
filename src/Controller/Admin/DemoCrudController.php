<?php

namespace App\Controller\Admin;

use App\Entity\Demo;
use App\Form\KundeAutocompleteField;
use App\Form\SoftwareAutocompleteField;
use App\Service\WebsiteScreenshotService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/demos', routeName: 'admin_demos')]
final class DemoCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly WebsiteScreenshotService $websiteScreenshotService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Demo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Demo')
            ->setEntityLabelInPlural('Demos')
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
        $linkDisplay = TextField::new('link', 'Link')
            ->hideOnForm()
            ->renderAsHtml()
            ->formatValue(fn (?string $value): string => $this->renderLinkWithButton($value));

        $galleryIndex = TextField::new('vorschaubilder', 'Galerie')
            ->onlyOnIndex()
            ->renderAsHtml()
            ->formatValue(fn (?string $value): string => $this->renderGallery($value, 64));

        $galleryDetail = TextField::new('vorschaubilder', 'Galerie')
            ->onlyOnDetail()
            ->renderAsHtml()
            ->formatValue(fn (?string $value, ?Demo $entity): string => $this->renderDetailGallery($value, $entity));

        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('name', 'Name'),
            TextareaField::new('beschreibung')->setRequired(false),
            TextField::new('link', 'Link')->onlyOnForms()->setRequired(false),
            $linkDisplay,
            Field::new('uploadedImages', 'Vorschaubilder Upload')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'multiple' => true,
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ])
                ->setHelp('Mehrfachauswahl moeglich.'),
            $galleryIndex,
            $galleryDetail,
            AssociationField::new('kunde', 'Kunde')
                ->setRequired(false)
                ->setFormType(KundeAutocompleteField::class),
            AssociationField::new('software', 'Software')
                ->setRequired(false)
                ->setFormType(SoftwareAutocompleteField::class)
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('by_reference', false)
                ->setHelp('Mehrfachauswahl moeglich.'),
            BooleanField::new('eigeneWebsite', 'Eigene Website'),
            BooleanField::new('aktiv'),
            DateTimeField::new('createdAt')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Demo) {
            $this->storeUploadedImages($entityInstance, 'uploads/demos');
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Demo) {
            $this->storeUploadedImages($entityInstance, 'uploads/demos');
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function storeUploadedImages(Demo $entity, string $relativeUploadDir): void
    {
        $uploadedFiles = array_values(array_filter(
            $entity->getUploadedImages(),
            static fn (mixed $file): bool => $file instanceof UploadedFile
        ));

        $existing = $this->toImageList($entity->getVorschaubilder());
        $newPaths = [];

        if ([] !== $uploadedFiles) {
            $absoluteUploadDir = $this->getParameter('kernel.project_dir') . '/public/' . $relativeUploadDir;
            if (!is_dir($absoluteUploadDir)) {
                mkdir($absoluteUploadDir, 0775, true);
            }

            foreach ($uploadedFiles as $uploadedFile) {
                $extension = $uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension() ?: 'bin';
                $fileName = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), strtolower($extension));
                $uploadedFile->move($absoluteUploadDir, $fileName);
                $newPaths[] = $relativeUploadDir . '/' . $fileName;
            }
        }

        $screenshotPath = $this->createScreenshotIfPossible($entity, $relativeUploadDir . '/screenshots', false);
        if (null !== $screenshotPath) {
            $newPaths[] = $screenshotPath;
        }

        if ([] === $newPaths) {
            return;
        }

        $entity->setVorschaubilder(implode("\n", array_values(array_unique(array_merge($existing, $newPaths)))));
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

    private function renderGallery(?string $value, int $size = 64): string
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
                '<button type="button" data-lightbox-url="%1$s" data-lightbox-caption="Demo Bild" style="border:0;background:transparent;padding:0;margin:0;cursor:zoom-in;"><img src="%1$s" alt="" style="width:%2$dpx;height:%2$dpx;object-fit:cover;border-radius:6px;border:1px solid #d5d9e0;margin:0 8px 8px 0;"></button>',
                $escapedUrl,
                $size
            );
        }

        return implode('', $html);
    }

    private function renderDetailGallery(?string $value, ?Demo $entity = null): string
    {
        $images = $this->toImageList($value);

        if ($entity instanceof Demo) {
            $images = $this->ensureScreenshotInDetails($entity, $images);
        }

        if ([] === $images) {
            return '<span class="text-muted">Keine Bilder</span>';
        }

        $latestScreenshot = null;
        foreach (array_reverse($images) as $image) {
            if (str_contains($image, '/screenshots/auto-shot-')) {
                $latestScreenshot = $image;
                break;
            }
        }

        $parts = [];
        if (null !== $latestScreenshot) {
            $url = '/' . ltrim($latestScreenshot, '/');
            $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $parts[] = sprintf(
                '<div style="margin-bottom:12px;"><div style="font-weight:600;margin-bottom:6px;">Letzter Screenshot</div><button type="button" data-lightbox-url="%1$s" data-lightbox-caption="Letzter Screenshot" style="border:0;background:transparent;padding:0;cursor:zoom-in;"><img src="%1$s" alt="" style="max-width:420px;width:100%%;height:auto;border-radius:8px;border:1px solid #d5d9e0;"></button></div>',
                $escapedUrl
            );
        }

        $parts[] = '<div style="font-weight:600;margin:10px 0 6px;">Alle Bilder</div>';
        $parts[] = $this->renderGallery(implode("\n", $images), 120);

        return implode('', $parts);
    }

    private function createScreenshotIfPossible(Demo $entity, string $relativeScreenshotDir, bool $force): ?string
    {
        $url = trim((string) $entity->getLink());
        if ('' === $url || false === filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        if (!$force) {
            foreach ($this->toImageList($entity->getVorschaubilder()) as $image) {
                if (str_contains($image, '/screenshots/auto-shot-')) {
                    return null;
                }
            }
        }

        return $this->websiteScreenshotService->capture($url, $relativeScreenshotDir);
    }

    /**
     * @param array<int, string> $images
     *
     * @return array<int, string>
     */
    private function ensureScreenshotInDetails(Demo $entity, array $images): array
    {
        foreach ($images as $image) {
            if (str_contains($image, '/screenshots/auto-shot-')) {
                return $images;
            }
        }

        $screenshot = $this->createScreenshotIfPossible($entity, 'uploads/demos/screenshots', true);
        if (null === $screenshot) {
            return $images;
        }

        $merged = array_values(array_unique(array_merge($images, [$screenshot])));
        $entity->setVorschaubilder(implode("\n", $merged));
        $this->entityManager->flush();

        return $merged;
    }

    private function renderLinkWithButton(?string $value): string
    {
        $url = trim((string) $value);
        if ('' === $url) {
            return '<span class="text-muted">Kein Link</span>';
        }

        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            return sprintf('<code>%s</code>', $escapedUrl);
        }

        return sprintf(
            '<span style="margin-right:8px;">%1$s</span><a href="%1$s" target="_blank" rel="noopener" class="btn btn-tuningfux">Website aufrufen</a>',
            $escapedUrl
        );
    }
}
