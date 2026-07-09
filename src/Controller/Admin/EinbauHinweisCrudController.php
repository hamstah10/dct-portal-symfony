<?php

namespace App\Controller\Admin;

use App\Entity\EinbauHinweis;
use App\Entity\Ecu;
use App\Entity\MediaAsset;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/einbau-hinweise', routeName: 'admin_einbau_hinweis')]
final class EinbauHinweisCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public static function getEntityFqcn(): string
    {
        return EinbauHinweis::class;
    }

    public function createEditFormQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createEditFormQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $alias = $qb->getRootAliases()[0];

        $qb
            ->leftJoin("{$alias}.vehicleEngines", 'eng')
            ->leftJoin('eng.ecuRelations', 'ecu')
            ->leftJoin('ecu.masterEcu', 'ecuMaster')
            ->leftJoin("{$alias}.activeEcu", 'activeEcu')
            ->leftJoin("{$alias}.vehicleModels", 'mod')
            ->leftJoin("{$alias}.images", 'img')
            ->addSelect('eng', 'ecu', 'ecuMaster', 'activeEcu', 'mod', 'img');

        return $qb;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Einbau-Hinweis')
            ->setEntityLabelInPlural('Einbau-Hinweise')
            ->setDefaultSort(['title' => 'ASC'])
            ->overrideTemplate('crud/new', 'admin/einbau_hinweis/form.html.twig')
            ->overrideTemplate('crud/edit', 'admin/einbau_hinweis/form.html.twig');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('title')
            ->add('ecuOptionMaster')
            ->add('active');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('title', 'Titel'),
            TextareaField::new('description', 'Beschreibung')->hideOnIndex(),
            AssociationField::new('ecuOptionMaster', 'ECU-Option (Funktion)')
                ->setRequired(false)
                ->hideOnIndex(),
            AssociationField::new('activeEcu', 'Aktive MCU')
                ->onlyOnIndex(),
            TextField::new('brandsLabel', 'Marken')->onlyOnIndex(),
            AssociationField::new('vehicleEngines', 'Motoren')
                ->onlyOnIndex()
                ->formatValue(static function (mixed $value, ?EinbauHinweis $entity): string {
                    if (null === $entity) {
                        return '–';
                    }
                    $names = $entity->getVehicleEngines()->map(fn (VehicleEngine $e) => $e->getName())->toArray();

                    return implode(', ', $names) ?: '–';
                }),
            AssociationField::new('images', 'Bilder')
                ->onlyOnIndex()
                ->formatValue(static function (mixed $value, ?EinbauHinweis $entity): string {
                    if (null === $entity) {
                        return '–';
                    }

                    return (string) $entity->getImages()->count();
                }),
            DateTimeField::new('createdAt', 'Erstellt')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert')->hideOnForm(),
            BooleanField::new('active', 'Aktiv'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->handleFormExtras($entityInstance, $entityManager);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->handleFormExtras($entityInstance, $entityManager);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    private function handleFormExtras(object $entityInstance, EntityManagerInterface $em): void
    {
        if (!$entityInstance instanceof EinbauHinweis) {
            return;
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        $deleteImageIds = array_values(array_filter(array_map('intval', $request->request->all('einbau_hinweis_delete_images'))));
        if ([] !== $deleteImageIds) {
            foreach ($entityInstance->getImages()->toArray() as $image) {
                if (null !== $image->getId() && in_array($image->getId(), $deleteImageIds, true)) {
                    $entityInstance->removeImage($image);
                }
            }
        }

        $imageOrderIds = array_values(array_filter(array_map('intval', $request->request->all('einbau_hinweis_image_order'))));
        if ([] !== $imageOrderIds) {
            $imagesById = [];
            foreach ($entityInstance->getImages()->toArray() as $image) {
                if (null !== $image->getId()) {
                    $imagesById[$image->getId()] = $image;
                    $entityInstance->removeImage($image);
                }
            }

            $position = 0;
            foreach ($imageOrderIds as $imageId) {
                $image = $imagesById[$imageId] ?? null;
                if (!$image instanceof MediaAsset) {
                    continue;
                }

                $image->setSorting($position);
                $entityInstance->addImage($image);
                unset($imagesById[$imageId]);
                $position += 10;
            }

            foreach ($imagesById as $image) {
                $image->setSorting($position);
                $entityInstance->addImage($image);
                $position += 10;
            }
        }

        // Handle uploaded images → create MediaAsset entries
        $uploadedFiles = $request->files->get('einbau_hinweis_images', []);
        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }

        $nextImageSorting = 0;
        foreach ($entityInstance->getImages() as $image) {
            $nextImageSorting = max($nextImageSorting, $image->getSorting() + 10);
        }

        foreach ($uploadedFiles as $uploadedFile) {
            if (!$uploadedFile instanceof UploadedFile) {
                continue;
            }

            // Read metadata before move() invalidates the temp file
            $mimeType = $uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType();
            $fileSize = @$uploadedFile->getSize() ?: null;
            $name = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

            $path = $this->storeSingleUpload($uploadedFile, 'uploads/media');
            if (null === $path) {
                continue;
            }

            $asset = new MediaAsset();
            $asset->setName($name ?: 'Einbau-Hinweis Bild');
            $asset->setFilePath($path);
            $asset->setMediaType(MediaAsset::TYPE_IMAGE);
            $asset->setMimeType((null !== $mimeType && '' !== trim($mimeType)) ? trim($mimeType) : null);
            $asset->setFileSize(is_int($fileSize) ? $fileSize : null);
            $asset->setSorting($nextImageSorting);
            $em->persist($asset);

            $entityInstance->addImage($asset);
            $nextImageSorting += 10;
        }

        // Handle image annotations (editable overlays)
        $annotationsRaw = $request->request->all('einbau_hinweis_image_annotations');
        if (is_array($annotationsRaw)) {
            foreach ($entityInstance->getImages() as $image) {
                if (!$image instanceof MediaAsset || null === $image->getId()) {
                    continue;
                }

                $payload = $annotationsRaw[(string) $image->getId()] ?? $annotationsRaw[$image->getId()] ?? null;
                if (null === $payload) {
                    continue;
                }

                if (is_string($payload)) {
                    $decoded = json_decode($payload, true);
                    $payload = is_array($decoded) ? $decoded : [];
                }

                if (!is_array($payload)) {
                    $payload = [];
                }

                $image->setAnnotations($payload);
            }
        }

        // Handle vehicle engine selections
        $engineIds = array_filter(array_map('intval', $request->request->all('vehicle_engine_ids')));
        $existingEngineIds = $entityInstance->getVehicleEngines()->map(fn (VehicleEngine $e) => $e->getId())->toArray();

        foreach ($entityInstance->getVehicleEngines()->toArray() as $engine) {
            if (!in_array($engine->getId(), $engineIds, true)) {
                $entityInstance->removeVehicleEngine($engine);
            }
        }

        foreach ($engineIds as $engineId) {
            if (!in_array($engineId, $existingEngineIds, true)) {
                $engine = $em->find(VehicleEngine::class, $engineId);
                if ($engine instanceof VehicleEngine) {
                    $entityInstance->addVehicleEngine($engine);
                }
            }
        }

        // Handle active ECU selection. It must be an MCU attached to one of the linked engines.
        $activeEcuId = (int) $request->request->get('active_ecu_id', 0);
        $activeEcu = null;
        if ($activeEcuId > 0) {
            foreach ($entityInstance->getVehicleEngines() as $engine) {
                foreach ($engine->getEcuRelations() as $ecu) {
                    if (
                        $ecu instanceof Ecu
                        && $ecu->getId() === $activeEcuId
                        && Ecu::CONTROL_UNIT_KIND_MCU === $ecu->getControlUnitKind()
                    ) {
                        $activeEcu = $ecu;
                        break 2;
                    }
                }
            }
        }
        $entityInstance->setActiveEcu($activeEcu);

        // Handle vehicle model selections
        $modelIds = array_filter(array_map('intval', $request->request->all('vehicle_model_ids')));
        $existingModelIds = $entityInstance->getVehicleModels()->map(fn (VehicleModel $m) => $m->getId())->toArray();

        foreach ($entityInstance->getVehicleModels()->toArray() as $model) {
            if (!in_array($model->getId(), $modelIds, true)) {
                $entityInstance->removeVehicleModel($model);
            }
        }

        foreach ($modelIds as $modelId) {
            if (!in_array($modelId, $existingModelIds, true)) {
                $model = $em->find(VehicleModel::class, $modelId);
                if ($model instanceof VehicleModel) {
                    $entityInstance->addVehicleModel($model);
                }
            }
        }
    }
}
