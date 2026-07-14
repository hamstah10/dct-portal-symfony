<?php

namespace App\Controller\Admin;

use App\EasyAdmin\Field\CkEditor5Field;
use App\Entity\BrandMaster;
use App\Entity\VehicleBrand;
use App\Entity\VehicleSeries;
use App\Service\BrandMasterImageResolver;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/vehiclebrand', routeName: 'admin_vehiclebrand')]
final class VehicleBrandCrudController extends AbstractCrudController
{
    use ActiveVehicleDataPoolIndexFilterTrait;
    use HandlesImageUploadsTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly BrandMasterImageResolver $brandMasterImageResolver,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return VehicleBrand::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $promoteToMasterAction = Action::new('promoteToMaster', 'Zu Master-Brand machen')
            ->setIcon('mdi:tag-plus')
            ->setCssClass('btn btn-outline-primary')
            ->linkToCrudAction('promoteToMaster');

        $promoteAllToMasterAction = Action::new('promoteAllToMaster', 'Alle zu Master-Brand machen')
            ->setIcon('mdi:tag-multiple')
            ->setCssClass('btn btn-primary')
            ->linkToCrudAction('promoteAllToMaster')
            ->createAsGlobalAction();

        $promoteToMasterBatchAction = Action::new('promoteToMasterBatch', 'Zu Master-Brand machen')
            ->setIcon('mdi:tag-plus')
            ->setCssClass('btn btn-outline-primary')
            ->linkToCrudAction('promoteToMasterBatch')
            ->createAsBatchAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $promoteAllToMasterAction)
            ->add(Crud::PAGE_DETAIL, $promoteToMasterAction)
            ->addBatchAction($promoteToMasterBatchAction);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Marke')
            ->setEntityLabelInPlural('Marken')
            ->showEntityActionsInlined()
            ->setDefaultRowAction(null);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('vehicleDataPool')
            ->add(NullFilter::new('masterBrand', 'Master-Referenz')->setChoiceLabels('Master fehlt', 'Master gesetzt'));
    }

    public function configureFields(string $pageName): iterable
    {
        $nameField = TextField::new('name');
        $masterActionField = TextField::new('masterBrand', 'Master')
            ->hideOnForm()
            ->renderAsHtml()
            ->formatValue(function (mixed $value, ?VehicleBrand $entity): string {
                if (!$entity instanceof VehicleBrand || null === $entity->getId()) {
                    return '-';
                }

                if (null === $entity->getVehicleDataPool()) {
                    return '<span class="text-muted">Kein Pool</span>';
                }

                if (null !== $entity->getMasterBrand()) {
                    return sprintf(
                        '<span class="badge text-bg-success">Master: %s</span>',
                        htmlspecialchars((string) $entity->getMasterBrand(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    );
                }

                $url = $this->adminUrlGenerator
                    ->unsetAll()
                    ->setRoute('admin_vehicle_brand_promote_to_master', ['entityId' => (int) $entity->getId()])
                    ->generateUrl();

                return sprintf(
                    '<a href="%s" class="btn btn-sm btn-outline-primary">Zu Master-Brand machen</a>',
                    htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                );
            });
        if (Crud::PAGE_INDEX === $pageName) {
            $nameField = $nameField
                ->renderAsHtml()
                ->formatValue(static function (?string $value, ?VehicleBrand $entity): string {
                    $label = htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $path = trim((string) ($entity?->getImage() ?? ''));

                    if ('' === $path) {
                        return $label;
                    }

                    $url = '/' . ltrim($path, '/');
                    $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $isSvg = str_ends_with(strtolower($path), '.svg');

                    return sprintf(
                        '<span style="display:inline-flex;align-items:center;gap:10px;"><img src="%s" alt="" style="width:56px;height:56px;object-fit:contain;"><span>%s</span></span>',
                        $escapedUrl,
                        $label
                    );
                });
        }

        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            AssociationField::new('vehicleDataPool', 'Datenbestand'),
            AssociationField::new('masterBrand', 'Master-Brand')->setRequired(false),
            AssociationField::new('vehicleType'),
            $nameField,
            $masterActionField,
            Field::new('uploadedImage', 'Bild hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            TextField::new('image', 'Bild')
                ->hideOnForm()
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderSingleImagePreview($value)),
            CkEditor5Field::new('description'),
            TextField::new('slug'),
            TextareaField::new('synonyms', 'Synonyme')
                ->hideOnIndex()
                ->setRequired(false)
                ->setHelp('Alternative Suchbegriffe, kommagetrennt oder zeilenweise – z.B. "vw, volkswagon". Die Marke wird dann auch über diese Begriffe gefunden.'),
            IntegerField::new('sorting'),
            BooleanField::new('active'),
            BooleanField::new('showInTopMenu', 'Top Menü'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleBrand) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/vehicle-brand');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }
            $entityInstance->setUploadedImage(null);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleBrand) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/vehicle-brand');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }
            $entityInstance->setUploadedImage(null);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof VehicleBrand && null !== $entityInstance->getId()) {
            $seriesRepository = $entityManager->getRepository(VehicleSeries::class);
            $seriesItems = $seriesRepository->findBy(['vehicleBrand' => $entityInstance]);

            foreach ($seriesItems as $series) {
                if (!$series instanceof VehicleSeries) {
                    continue;
                }

                $series->setVehicleBrand(null);
            }

            $entityManager->flush();
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    #[AdminRoute(path: '/{entityId}/promote-to-master', name: 'promote_to_master', options: ['methods' => ['GET', 'POST']])]
    public function promoteToMaster(AdminContext $context): Response
    {
        $brand = $context->getEntity()->getInstance();
        if (!$brand instanceof VehicleBrand || null === $brand->getId()) {
            $this->addFlash('danger', 'Die ausgewaehlte Marke konnte nicht geladen werden.');

            return $this->redirectToIndex();
        }

        $summary = $this->promoteBrandsToMaster([(int) $brand->getId()]);
        $this->addPromotionSummaryFlashMessages($summary);

        return $this->redirectToIndex();
    }

    #[AdminRoute(path: '/promote-to-master/batch', name: 'promote_to_master_batch', options: ['methods' => ['POST']])]
    public function promoteToMasterBatch(BatchActionDto $batchActionDto): Response
    {
        if (!$this->isCsrfTokenValid('ea-batch-action-' . $batchActionDto->getName(), $batchActionDto->getCsrfToken())) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token fuer die Mehrfachaktion.');

            return $this->redirectToIndex();
        }

        $summary = $this->promoteBrandsToMaster($batchActionDto->getEntityIds());
        $this->addPromotionSummaryFlashMessages($summary);

        return $this->redirectToIndex();
    }

    #[AdminRoute(path: '/promote-to-master/all', name: 'promote_to_master_all', options: ['methods' => ['GET', 'POST']])]
    public function promoteAllToMaster(): Response
    {
        $brandIds = $this->entityManager->getRepository(VehicleBrand::class)
            ->createQueryBuilder('b')
            ->select('b.id')
            ->andWhere('b.vehicleDataPool IS NOT NULL')
            ->andWhere('b.masterBrand IS NULL')
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        $summary = $this->promoteBrandsToMaster($brandIds);
        $this->addPromotionSummaryFlashMessages($summary);

        return $this->redirectToIndex();
    }

    /**
     * @param array<mixed> $entityIds
     *
     * @return array{created:int,linkedExistingMaster:int,skippedAlreadyLinked:int,skippedWithoutPool:int,skippedMissing:int}
     */
    private function promoteBrandsToMaster(array $entityIds): array
    {
        $repository = $this->entityManager->getRepository(VehicleBrand::class);
        $brandMasterRepository = $this->entityManager->getRepository(BrandMaster::class);
        $created = 0;
        $linkedExistingMaster = 0;
        $skippedAlreadyLinked = 0;
        $skippedWithoutPool = 0;
        $skippedMissing = 0;

        foreach ($entityIds as $id) {
            $brand = $repository->find($id);
            if (!$brand instanceof VehicleBrand || null === $brand->getId()) {
                ++$skippedMissing;
                continue;
            }

            if (null === $brand->getVehicleDataPool()) {
                ++$skippedWithoutPool;
                continue;
            }

            if (null !== $brand->getMasterBrand()) {
                ++$skippedAlreadyLinked;
                continue;
            }

            $slug = trim((string) ($brand->getSlug() ?? ''));
            $name = trim($brand->getName());

            $existingMaster = null;
            if ('' !== $slug) {
                $existingMaster = $brandMasterRepository->findOneBy(['slug' => $slug]);
            }
            if (!$existingMaster instanceof BrandMaster && '' !== $name) {
                $existingMaster = $brandMasterRepository->createQueryBuilder('bm')
                    ->andWhere('LOWER(bm.name) = :nameLower')
                    ->setParameter('nameLower', mb_strtolower($name))
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
            }

            if ($existingMaster instanceof BrandMaster) {
                $brand->setMasterBrand($existingMaster);

                $masterImage = trim((string) ($existingMaster->getImage() ?? ''));
                if ('' !== $masterImage) {
                    $brand->setImage($masterImage);
                }

                ++$linkedExistingMaster;
                continue;
            }

            $masterBrand = (new BrandMaster())
                ->setName($name)
                ->setSlug('' !== $slug ? $slug : null)
                ->setImage($this->brandMasterImageResolver->resolve($slug, $name, $brand->getImage()))
                ->setDescription($brand->getDescription())
                ->setVehicleType($brand->getVehicleType());

            $brand->setMasterBrand($masterBrand);
            $this->entityManager->persist($masterBrand);
            ++$created;
        }

        $this->entityManager->flush();

        return [
            'created' => $created,
            'linkedExistingMaster' => $linkedExistingMaster,
            'skippedAlreadyLinked' => $skippedAlreadyLinked,
            'skippedWithoutPool' => $skippedWithoutPool,
            'skippedMissing' => $skippedMissing,
        ];
    }

    /**
     * @param array{created:int,linkedExistingMaster:int,skippedAlreadyLinked:int,skippedWithoutPool:int,skippedMissing:int} $summary
     */
    private function addPromotionSummaryFlashMessages(array $summary): void
    {
        if ($summary['created'] > 0) {
            $this->addFlash('success', sprintf('%d Marken wurden als neue Master-Brands angelegt. Die Pool-Eintraege bleiben erhalten.', $summary['created']));
        }

        if ($summary['linkedExistingMaster'] > 0) {
            $this->addFlash('info', sprintf('%d Marken wurden mit einem bereits vorhandenen Master-Brand verknuepft.', $summary['linkedExistingMaster']));
        }

        if ($summary['skippedAlreadyLinked'] > 0) {
            $this->addFlash('info', sprintf('%d Marken wurden uebersprungen, weil bereits ein Master-Brand gesetzt ist.', $summary['skippedAlreadyLinked']));
        }

        if ($summary['skippedWithoutPool'] > 0) {
            $this->addFlash('info', sprintf('%d Marken wurden uebersprungen, weil sie keinem Datenpool zugeordnet sind.', $summary['skippedWithoutPool']));
        }

        if ($summary['skippedMissing'] > 0) {
            $this->addFlash('warning', sprintf('%d Marken konnten nicht geladen werden.', $summary['skippedMissing']));
        }
    }

    private function redirectToIndex(): Response
    {
        return $this->redirect($this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}
