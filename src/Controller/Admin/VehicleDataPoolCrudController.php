<?php

namespace App\Controller\Admin;

use App\Entity\VehicleDataPool;
use App\Service\VehicleDataPoolPurger;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminCrud(routePath: '/fahrzeugdatenbank/data-pools', routeName: 'admin_vehicle_data_pool')]
final class VehicleDataPoolCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly VehicleDataPoolPurger $vehicleDataPoolPurger,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return VehicleDataPool::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $purgePoolAction = Action::new('purgePool', 'Datenpool löschen')
            ->setIcon('iconamoon:trash-bold')
            ->setCssClass('btn btn-outline-danger')
            ->setHtmlAttributes([
                'data-confirm' => 'Möchten Sie diesen Datenpool inklusive aller importierten Motoren, ECUs & Optionen wirklich unwiderruflich löschen?',
            ])
            ->linkToCrudAction('purge')
            ->displayIf(static fn (?VehicleDataPool $pool): bool => null !== $pool);

        return $actions
            ->add(Crud::PAGE_INDEX, $purgePoolAction)
            ->add(Crud::PAGE_DETAIL, $purgePoolAction);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Datenbestand')
            ->setEntityLabelInPlural('Datenbestände');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('name'),
            TextField::new('poolKey', 'Key'),
            TextareaField::new('description')->setRequired(false),
            AssociationField::new('vehicleTypes', 'Fahrzeugtypen'),
            BooleanField::new('active'),

            FormField::addFieldset('Sichtbare Inhalte')
                ->setHelp('Steuert, welche Inhalte in der API-Antwort dieses Datenbestands enthalten sind.'),
            BooleanField::new('showPrices', 'Preise anzeigen'),
            BooleanField::new('showHints', 'Hinweise anzeigen'),
            BooleanField::new('showGearbox', 'Getriebe anzeigen'),
            BooleanField::new('showDyno', 'Dyno-Chart anzeigen'),
            BooleanField::new('showEcoStage', 'Eco-Stage anzeigen'),
        ];
    }

    #[AdminRoute(path: '/{entityId}/purge', name: 'vehicle_data_pool_purge', options: ['methods' => ['GET']])]
    public function purge(AdminContext $context): Response
    {
        $pool = $context->getEntity()->getInstance();
        if (!$pool instanceof VehicleDataPool || null === $pool->getId()) {
            $this->addFlash('danger', 'Der ausgewählte Datenpool konnte nicht gefunden werden.');

            return $this->redirect($this->adminUrlGenerator
                ->unsetAll()
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl());
        }

        try {
            $this->vehicleDataPoolPurger->purge($pool);
            $this->entityManager->clear();
            $this->addFlash('success', sprintf('Der Datenpool "%s" wurde vollständig entfernt.', $pool->getName()));
        } catch (\Throwable $exception) {
            $this->addFlash('danger', 'Der Datenpool konnte nicht gelöscht werden: ' . $exception->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}
