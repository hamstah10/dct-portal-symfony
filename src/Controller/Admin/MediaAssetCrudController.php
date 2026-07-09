<?php

namespace App\Controller\Admin;

use App\Entity\MediaAsset;
use App\Entity\MediaFolder;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Cropperjs\Factory\CropperInterface;
use Symfony\UX\Cropperjs\Form\CropperType;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/media/asset', routeName: 'admin_media_asset')]
final class MediaAssetCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public function __construct(
        private readonly CropperInterface $cropper,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return MediaAsset::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Mediendatei')
            ->setEntityLabelInPlural('Mediendateien')
            ->overrideTemplate('crud/detail', 'admin/media/asset_detail.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        $pageName = (string) $responseParameters->get('pageName');
        if (Crud::PAGE_DETAIL !== $pageName) {
            return $responseParameters;
        }

        $asset = $this->getContext()?->getEntity()?->getInstance();
        if (!$asset instanceof MediaAsset) {
            return $responseParameters;
        }

        ['form' => $form, 'reason' => $reason] = $this->buildCropForm($asset);
        if (null !== $form) {
            $responseParameters->set('cropForm', $form->createView());
        } elseif (null !== $reason) {
            $responseParameters->set('cropperDisabledReason', $reason);
        }

        return $responseParameters;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IdField::new('id')->hideOnForm(),
                TextField::new('name', 'Titel'),
                AssociationField::new('folder', 'Ordner')
                    ->autocomplete(),
                ChoiceField::new('mediaType', 'Typ')
                    ->setChoices(MediaAsset::getMediaTypeChoices()),
                TextField::new('filePath', 'Datei')
                    ->hideOnForm()
                    ->renderAsHtml()
                    ->formatValue(function (?string $value, ?MediaAsset $entity): string {
                        $type = $entity instanceof MediaAsset ? $entity->getMediaType() : MediaAsset::TYPE_FILE;

                        return $this->renderMediaPreview($value, $type);
                    }),
                BooleanField::new('active'),
                DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            ];
        }

        return [
            FormField::addFieldset('Datei'),
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Titel'),
            AssociationField::new('folder', 'Ordner')
                ->autocomplete(),
            ChoiceField::new('mediaType', 'Typ')
                ->setChoices(MediaAsset::getMediaTypeChoices()),
            Field::new('uploadedFile', 'Datei hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*,.svg,.ico,video/*,.glb,.gltf,.pdf,.txt,.json,.xml,.zip,.rar,.7z'],
                ]),
            TextField::new('filePath', 'Datei')
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(function (?string $value, ?MediaAsset $entity): string {
                    $type = $entity instanceof MediaAsset ? $entity->getMediaType() : MediaAsset::TYPE_FILE;

                    return $this->renderMediaPreview($value, $type);
                }),
            FormField::addFieldset('Metadaten'),
            TextField::new('mimeType', 'MIME-Type')->hideOnForm(),
            IntegerField::new('fileSize', 'Dateigroesse (Bytes)')->hideOnForm(),
            TextareaField::new('description'),
            BooleanField::new('active'),
            IntegerField::new('sorting'),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof MediaAsset) {
            $this->handleFileUpload($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof MediaAsset) {
            $this->handleFileUpload($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleFileUpload(MediaAsset $mediaAsset): void
    {
        $uploadedFile = $mediaAsset->getUploadedFile();
        if (!$uploadedFile instanceof UploadedFile) {
            return;
        }

        $mimeType = $uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType();
        $fileSize = $uploadedFile->getSize();

        $path = $this->storeSingleUpload($uploadedFile, 'uploads/media');
        if (null !== $path) {
            $mediaAsset->setFilePath($path);
            $mediaAsset->setMimeType((null !== $mimeType && '' !== trim($mimeType)) ? trim($mimeType) : null);
            $mediaAsset->setFileSize(is_int($fileSize) ? $fileSize : null);
            $mediaAsset->setMediaType($this->detectMediaType($mimeType, $path));
        }

        $mediaAsset->setUploadedFile(null);
    }

    private function detectMediaType(?string $mimeType, ?string $path): string
    {
        $normalizedMime = strtolower((string) $mimeType);
        $extension = strtolower((string) pathinfo((string) $path, \PATHINFO_EXTENSION));

        if (
            in_array($extension, ['svg', 'ico', 'icns'], true)
            || str_contains($normalizedMime, 'svg')
            || str_contains($normalizedMime, 'icon')
        ) {
            return MediaAsset::TYPE_ICON;
        }
        if (str_starts_with($normalizedMime, 'image/')) {
            return MediaAsset::TYPE_IMAGE;
        }
        if (str_starts_with($normalizedMime, 'video/')) {
            return MediaAsset::TYPE_VIDEO;
        }
        if (in_array($extension, ['glb', 'gltf'], true) || str_contains($normalizedMime, 'gltf')) {
            return MediaAsset::TYPE_MODEL;
        }
        if (
            str_starts_with($normalizedMime, 'text/')
            || str_contains($normalizedMime, 'pdf')
            || str_contains($normalizedMime, 'json')
            || str_contains($normalizedMime, 'xml')
        ) {
            return MediaAsset::TYPE_DOCUMENT;
        }

        return MediaAsset::TYPE_FILE;
    }

    private function renderMediaPreview(?string $path, string $mediaType): string
    {
        if (MediaAsset::TYPE_ICON === $mediaType) {
            if (null === $path || '' === trim($path)) {
                return '<span class="text-muted">Kein Bild</span>';
            }

            $url = '/' . ltrim(trim($path), '/');
            $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $escapedPath = htmlspecialchars(trim($path), ENT_QUOTES, 'UTF-8');

            return sprintf(
                '<div style="display:flex;align-items:center;gap:10px;"><img src="%s" alt="" style="width:56px;height:56px;object-fit:contain;border:1px solid #d5d9e0;border-radius:6px;"><code style="font-size:11px;word-break:break-all;">%s</code></div>',
                $escapedUrl,
                $escapedPath
            );
        }

        if (MediaAsset::TYPE_IMAGE === $mediaType) {
            return $this->renderSingleImagePreview($path);
        }

        $label = match ($mediaType) {
            MediaAsset::TYPE_VIDEO => 'Video',
            MediaAsset::TYPE_MODEL => '3D Modell',
            MediaAsset::TYPE_DOCUMENT => 'Dokument',
            default => 'Datei',
        };

        return $this->renderSingleFilePreview($path, $label);
    }

    #[Route('/admin/media/asset/{id}/crop', name: 'admin_media_asset_crop', methods: ['POST'])]
    public function crop(Request $request, MediaAsset $mediaAsset, EntityManagerInterface $entityManager): Response
    {
        ['form' => $form, 'reason' => $reason] = $this->buildCropForm($mediaAsset);
        if (null === $form) {
            $this->addFlash('danger', $reason ?? 'Zuschneiden ist fuer diese Datei nicht verfuegbar.');

            return $this->redirect($this->buildDetailUrl($mediaAsset));
        }

        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'Zuschneiden fehlgeschlagen. Bitte erneut versuchen.');

            return $this->redirect($this->buildDetailUrl($mediaAsset));
        }

        $absolutePath = $this->resolveAbsolutePath($mediaAsset);
        if (null === $absolutePath) {
            $this->addFlash('danger', 'Datei nicht gefunden oder nicht lokal verfuegbar.');

            return $this->redirect($this->buildDetailUrl($mediaAsset));
        }

        $format = $this->resolveCropFormat($absolutePath);
        if (null === $format) {
            $this->addFlash('danger', 'Dateiformat wird vom Cropper nicht unterstuetzt.');

            return $this->redirect($this->buildDetailUrl($mediaAsset));
        }

        $crop = $form->get('crop')->getData();
        if (!is_object($crop)) {
            $this->addFlash('danger', 'Crop-Daten fehlen.');

            return $this->redirect($this->buildDetailUrl($mediaAsset));
        }

        $croppedImage = $crop->getCroppedImage($format, 90);
        $bytes = @file_put_contents($absolutePath, $croppedImage);
        if (false === $bytes) {
            $this->addFlash('danger', 'Bild konnte nicht gespeichert werden.');

            return $this->redirect($this->buildDetailUrl($mediaAsset));
        }

        $mediaAsset->setFileSize(is_int($bytes) ? $bytes : null);
        $mimeType = $this->resolveMimeType($format);
        if (null !== $mimeType) {
            $mediaAsset->setMimeType($mimeType);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Bild zugeschnitten.');

        return $this->redirect($this->buildDetailUrl($mediaAsset));
    }

    /**
     * @return array{form: ?\Symfony\Component\Form\FormInterface, reason: ?string}
     */
    private function buildCropForm(MediaAsset $mediaAsset): array
    {
        if (null === $mediaAsset->getId()) {
            return ['form' => null, 'reason' => 'Datei ist nicht gespeichert.'];
        }

        if (!in_array($mediaAsset->getMediaType(), [MediaAsset::TYPE_IMAGE, MediaAsset::TYPE_ICON], true)) {
            return ['form' => null, 'reason' => 'Nur Bilder koennen zugeschnitten werden.'];
        }

        $absolutePath = $this->resolveAbsolutePath($mediaAsset);
        if (null === $absolutePath) {
            return ['form' => null, 'reason' => 'Datei nicht gefunden oder nicht lokal verfuegbar.'];
        }

        $format = $this->resolveCropFormat($absolutePath);
        if (null === $format) {
            return ['form' => null, 'reason' => 'Dateiformat wird vom Cropper nicht unterstuetzt.'];
        }

        $filePath = trim((string) $mediaAsset->getFilePath());
        if ('' === $filePath) {
            return ['form' => null, 'reason' => 'Keine Datei hinterlegt.'];
        }

        $crop = $this->cropper->createCrop($absolutePath);

        $form = $this->createFormBuilder(['crop' => $crop], [
            'action' => $this->generateUrl('admin_media_asset_crop', ['id' => $mediaAsset->getId()]),
            'method' => 'POST',
        ])
            ->add('crop', CropperType::class, [
                'public_url' => '/' . ltrim($filePath, '/'),
                'cropper_options' => [
                    'viewMode' => 1,
                    'dragMode' => 'move',
                    'autoCrop' => true,
                    'autoCropArea' => 1,
                    'responsive' => true,
                    'restore' => true,
                    'background' => false,
                    'guides' => true,
                    'center' => true,
                    'highlight' => true,
                    'cropBoxMovable' => true,
                    'cropBoxResizable' => true,
                    'toggleDragModeOnDblclick' => true,
                    'movable' => true,
                    'zoomable' => true,
                    'rotatable' => true,
                    'scalable' => true,
                    'zoomOnWheel' => true,
                    'zoomOnTouch' => true,
                    'wheelZoomRatio' => 0.1,
                ],
            ])
            ->add('apply', SubmitType::class, [
                'label' => 'Zuschneiden',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm();

        return ['form' => $form, 'reason' => null];
    }

    private function resolveAbsolutePath(MediaAsset $mediaAsset): ?string
    {
        $filePath = trim((string) $mediaAsset->getFilePath());
        if ('' === $filePath) {
            return null;
        }

        if (preg_match('#^https?://#i', $filePath)) {
            return null;
        }

        $relativePath = ltrim($filePath, '/');
        $absolutePath = $this->getParameter('kernel.project_dir') . '/public/' . $relativePath;

        return is_file($absolutePath) ? $absolutePath : null;
    }

    private function resolveCropFormat(string $path): ?string
    {
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            'gif' => 'gif',
            'bmp' => 'bmp',
            'avif' => 'avif',
            default => null,
        };
    }

    private function resolveMimeType(string $format): ?string
    {
        return match ($format) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'avif' => 'image/avif',
            default => null,
        };
    }

    private function buildDetailUrl(MediaAsset $mediaAsset): string
    {
        $id = $mediaAsset->getId();
        if (null === $id) {
            return $this->generateUrl('admin_media_asset_index');
        }

        return $this->generateUrl('admin_media_asset_detail', ['entityId' => $id]);
    }
}
