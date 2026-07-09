<?php

namespace App\Controller\Admin;

use App\Entity\Ecu;
use App\Entity\EcuMaster;
use App\Form\EcuToolEntryType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Dropzone\Form\DropzoneType;

#[AdminCrud(routePath: '/fahrzeugdatenbank/ecu', routeName: 'admin_ecu')]
final class EcuCrudController extends AbstractCrudController
{
    use ActiveVehicleDataPoolIndexFilterTrait;
    use HandlesImageUploadsTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Ecu::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request?->query->getBoolean('embed')) {
            $crud->overrideTemplate('layout', 'admin/embedded/layout.html.twig');
        }

        return $crud
            ->setEntityLabelInSingular('ECU')
            ->setEntityLabelInPlural('ECUs')
            ->setSearchFields(['name', 'type', 'controlUnitKind', 'manufacturer.name'])
            ->overrideTemplate('crud/index', 'admin/ecu/index.html.twig')
            ->overrideTemplate('crud/detail', 'admin/ecu/detail.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $assignMasterAction = Action::new('assignMasterEcu', 'Master-ECU zuordnen')
            ->setIcon('mdi:link-variant')
            ->setCssClass('btn btn-outline-primary')
            ->linkToCrudAction('assignMasterEcuBatch')
            ->createAsBatchAction();

        $promoteToMasterAction = Action::new('promoteToMasterEcu', 'Zu Master-ECU machen')
            ->setIcon('mdi:tag-plus')
            ->setCssClass('btn btn-outline-primary')
            ->linkToCrudAction('promoteToMasterEcu')
            ->displayIf(static fn (Ecu $ecu): bool => null === $ecu->getMasterEcu());

        $newGearboxAction = Action::new('newGearbox', 'Neues Getriebe anlegen')
            ->setIcon('mdi:plus-circle')
            ->setCssClass('btn btn-outline-primary')
            ->linkToUrl(fn (): string => $this->adminUrlGenerator
                ->unsetAll()
                ->setController(GearboxCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl())
            ->createAsGlobalAction();

        $createGearboxFromEcuAction = Action::new('createGearboxFromEcu', 'Getriebe anlegen (Daten übernehmen)')
            ->setIcon('mdi:plus-circle-outline')
            ->setCssClass('btn btn-outline-primary')
            ->linkToUrl(fn (Ecu $ecu): string => $this->adminUrlGenerator
                ->unsetAll()
                ->setController(GearboxCrudController::class)
                ->setAction(Action::NEW)
                ->set('fromEcu', (string) $ecu->getId())
                ->generateUrl())
            ->displayIf(static fn (Ecu $ecu): bool => Ecu::CONTROL_UNIT_KIND_TCU === $ecu->getControlUnitKind());

        return $actions
            ->addBatchAction($assignMasterAction)
            ->add(Crud::PAGE_EDIT, $promoteToMasterAction)
            ->add(Crud::PAGE_DETAIL, $promoteToMasterAction)
            ->add(Crud::PAGE_EDIT, $createGearboxFromEcuAction)
            ->add(Crud::PAGE_DETAIL, $createGearboxFromEcuAction)
            ->add(Crud::PAGE_INDEX, $newGearboxAction);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('vehicleDataPool')
            ->add('manufacturer')
            ->add(NullFilter::new('masterEcu', 'Master-Referenz')->setChoiceLabels('Master fehlt', 'Master gesetzt'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('vehicleDataPool', 'Datenbestand'),
            AssociationField::new('masterEcu', 'Master-ECU')->setRequired(false),
            TextField::new('name'),
            TextField::new('type'),
            ChoiceField::new('controlUnitKind', 'TCU/MCU')
                ->setChoices(Ecu::getControlUnitKindChoices())
                ->setRequired(false)
                ->renderExpanded(false),
            Field::new('uploadedImage', 'Bild hochladen')
                ->onlyOnForms()
                ->setFormType(DropzoneType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'attr' => ['accept' => 'image/*'],
                ]),
            TextField::new('image')->hideOnForm(),
            AssociationField::new('manufacturer')->autocomplete(),
            CollectionField::new('toolEntries', 'Tool-Hersteller & Methoden')
                ->setEntryType(EcuToolEntryType::class)
                ->allowAdd()
                ->allowDelete()
                ->renderExpanded()
                ->onlyOnForms(),
            AssociationField::new('toolEntries', 'Tool-Hersteller')
                ->hideOnForm()
                ->formatValue(static function (mixed $value, mixed $entity): string {
                    if (!$entity instanceof Ecu) {
                        return '';
                    }
                    $names = [];
                    foreach ($entity->getToolEntries() as $entry) {
                        $names[] = htmlspecialchars($entry->getToolManufacturer()?->getName() ?? '–', ENT_QUOTES, 'UTF-8');
                    }
                    return implode(', ', $names) ?: '–';
                })
                ->renderAsHtml(),
            AssociationField::new('options')->autocomplete(),
        ];
    }

    public function assignMasterEcuBatch(BatchActionDto $batchActionDto): Response
    {
        if (!$this->isCsrfTokenValid('ea-batch-action-' . $batchActionDto->getName(), $batchActionDto->getCsrfToken())) {
            $this->addFlash('danger', 'Ungültiges CSRF-Token.');
            return $this->redirectToIndex();
        }

        $ids = $batchActionDto->getEntityIds();
        if ([] === $ids) {
            return $this->redirectToIndex();
        }

        $indexUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($this->generateUrl('admin_ecu_ecu_assign_master', [
            'ids' => implode(',', $ids),
            'referrer' => $indexUrl,
        ]));
    }

    #[AdminRoute(path: '/master-zuordnen', name: 'ecu_assign_master', options: ['methods' => ['GET', 'POST']])]
    public function assignMasterEcuPage(Request $request): Response
    {
        $idsParam = trim((string) $request->query->get('ids', ''));
        $ids = array_filter(array_map('intval', explode(',', $idsParam)));
        $referrer = (string) $request->query->get('referrer', '');

        $indexUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction('index')
            ->generateUrl();

        if ([] === $ids) {
            return $this->redirect($referrer ?: $indexUrl);
        }

        $masterOptions = $this->entityManager
            ->getRepository(EcuMaster::class)
            ->createQueryBuilder('m')
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('ecu_assign_master', $request->request->get('_token'))) {
                $this->addFlash('danger', 'Ungültiges CSRF-Token.');
                return $this->redirect($referrer ?: $indexUrl);
            }

            $masterEcuId = (int) $request->request->get('masterEcuId', 0);
            $masterEcu = $masterEcuId > 0
                ? $this->entityManager->find(EcuMaster::class, $masterEcuId)
                : null;

            $ecus = $this->entityManager
                ->getRepository(Ecu::class)
                ->findBy(['id' => $ids]);

            foreach ($ecus as $ecu) {
                $ecu->setMasterEcu($masterEcu);
            }
            $this->entityManager->flush();

            $count = count($ecus);
            $masterLabel = $masterEcu?->getName() ?? '(keine)';
            $this->addFlash('success', sprintf('%d ECU(s) wurden Master-ECU „%s" zugeordnet.', $count, $masterLabel));

            return $this->redirect($referrer ?: $indexUrl);
        }

        return $this->render('admin/ecu/assign_master.html.twig', [
            'ids' => $ids,
            'idsParam' => $idsParam,
            'referrer' => $referrer,
            'masterOptions' => $masterOptions,
        ]);
    }

    #[AdminRoute(path: '/{entityId}/promote-to-master', name: 'ecu_promote_to_master', options: ['methods' => ['GET', 'POST']])]
    public function promoteToMasterEcu(AdminContext $context): Response
    {
        $ecu = $context->getEntity()->getInstance();
        if (!$ecu instanceof Ecu || null === $ecu->getId()) {
            $this->addFlash('danger', 'Die ausgewaehlte ECU konnte nicht geladen werden.');

            return $this->redirectToIndex();
        }

        if (null !== $ecu->getMasterEcu()) {
            $this->addFlash('info', 'Diese ECU ist bereits einem Master-ECU zugeordnet.');

            return $this->redirectToEdit($ecu);
        }

        $name = trim($ecu->getName());
        $type = trim((string) ($ecu->getType() ?? ''));

        $existingMaster = '' !== $name
            ? $this->entityManager->getRepository(EcuMaster::class)
                ->createQueryBuilder('m')
                ->andWhere('LOWER(m.name) = :nameLower')
                ->setParameter('nameLower', mb_strtolower($name))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            : null;

        if ($existingMaster instanceof EcuMaster) {
            $ecu->setMasterEcu($existingMaster);
            $this->entityManager->flush();

            $this->addFlash('info', sprintf('ECU wurde mit vorhandenem Master-ECU „%s" verknuepft.', $existingMaster->getName()));

            return $this->redirectToEdit($ecu);
        }

        $masterEcu = (new EcuMaster())
            ->setName($name)
            ->setType('' !== $type ? $type : null)
            ->setControlUnitKind($ecu->getControlUnitKind())
            ->setImage($ecu->getImage())
            ->setManufacturerMaster($ecu->getManufacturer()?->getMasterManufacturer());

        $ecu->setMasterEcu($masterEcu);
        $this->entityManager->persist($masterEcu);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('ECU wurde als neues Master-ECU „%s" angelegt.', '' !== $name ? $name : ('ECU #' . $ecu->getId())));

        return $this->redirectToEdit($ecu);
    }

    private function redirectToEdit(Ecu $ecu): Response
    {
        return $this->redirect($this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($ecu->getId())
            ->generateUrl());
    }

    private function redirectToIndex(): Response
    {
        return $this->redirect($this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Ecu) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/ecu');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }
            $entityInstance->setUploadedImage(null);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Ecu) {
            $path = $this->storeSingleUpload($entityInstance->getUploadedImage(), 'uploads/ecu');
            if (null !== $path) {
                $entityInstance->setImage($path);
            }
            $entityInstance->setUploadedImage(null);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
