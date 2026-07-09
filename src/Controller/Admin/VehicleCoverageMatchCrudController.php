<?php

namespace App\Controller\Admin;

use App\Entity\VehicleCoverageMatch;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[AdminCrud(routePath: '/ecu-intelligence/vehicle-coverage-matches', routeName: 'admin_vehicle_coverage_matches')]
final class VehicleCoverageMatchCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return VehicleCoverageMatch::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Fahrzeug-Match')
            ->setEntityLabelInPlural('Fahrzeug-Matches')
            ->setDefaultSort(['matchScore' => 'DESC', 'createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('status')
            ->add('provider')
            ->add('detectedEcu')
            ->add('vehicleType')
            ->add('vehicleBrand')
            ->add('ecuFamily')
            ->add('ecuName')
            ->add('mcu')
            ->add('matchLevel')
            ->add('matchScore')
            ->add('createdAt');
    }

    public function configureActions(Actions $actions): Actions
    {
        $confirmAction = Action::new('confirm', 'Bestätigen')
            ->setIcon('fa fa-check')
            ->linkToRoute('admin_vehicle_coverage_matches_confirm', static fn (VehicleCoverageMatch $match): array => ['id' => $match->getId()])
            ->displayIf(static fn (VehicleCoverageMatch $match): bool => VehicleCoverageMatch::STATUS_CONFIRMED !== $match->getStatus());

        $rejectAction = Action::new('reject', 'Ablehnen')
            ->setIcon('fa fa-xmark')
            ->linkToRoute('admin_vehicle_coverage_matches_reject', static fn (VehicleCoverageMatch $match): array => ['id' => $match->getId()])
            ->displayIf(static fn (VehicleCoverageMatch $match): bool => VehicleCoverageMatch::STATUS_REJECTED !== $match->getStatus());

        $ignoreAction = Action::new('ignore', 'Ignorieren')
            ->setIcon('fa fa-eye-slash')
            ->linkToRoute('admin_vehicle_coverage_matches_ignore', static fn (VehicleCoverageMatch $match): array => ['id' => $match->getId()])
            ->displayIf(static fn (VehicleCoverageMatch $match): bool => VehicleCoverageMatch::STATUS_IGNORED !== $match->getStatus());

        $publishAction = Action::new('publish', 'Veröffentlichen')
            ->setIcon('fa fa-bullhorn')
            ->linkToRoute('admin_vehicle_coverage_matches_publish', static fn (VehicleCoverageMatch $match): array => ['id' => $match->getId()])
            ->displayIf(static fn (VehicleCoverageMatch $match): bool => VehicleCoverageMatch::STATUS_PUBLISHED !== $match->getStatus());

        $openNewsAction = Action::new('openNews', 'Zur News')
            ->setIcon('fa fa-newspaper')
            ->linkToUrl(static fn (VehicleCoverageMatch $match): string => $match->getNewsItem()?->getUrl() ?? '#')
            ->setHtmlAttributes(['target' => '_blank', 'rel' => 'noopener'])
            ->displayIf(static fn (VehicleCoverageMatch $match): bool => null !== $match->getNewsItem());

        $openDetectedEcuAction = Action::new('openDetectedEcu', 'Zur erkannten ECU')
            ->setIcon('fa fa-microchip')
            ->linkToUrl(function (VehicleCoverageMatch $match): string {
                $detectedEcu = $match->getDetectedEcu();
                if (null === $detectedEcu) {
                    return '#';
                }

                return $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(DetectedEcuCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId((string) $detectedEcu->getId())
                    ->generateUrl();
            });

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $confirmAction)
            ->add(Crud::PAGE_DETAIL, $confirmAction)
            ->add(Crud::PAGE_INDEX, $rejectAction)
            ->add(Crud::PAGE_DETAIL, $rejectAction)
            ->add(Crud::PAGE_INDEX, $ignoreAction)
            ->add(Crud::PAGE_DETAIL, $ignoreAction)
            ->add(Crud::PAGE_INDEX, $publishAction)
            ->add(Crud::PAGE_DETAIL, $publishAction)
            ->add(Crud::PAGE_INDEX, $openNewsAction)
            ->add(Crud::PAGE_DETAIL, $openNewsAction)
            ->add(Crud::PAGE_INDEX, $openDetectedEcuAction)
            ->add(Crud::PAGE_DETAIL, $openDetectedEcuAction);
    }

    public function configureFields(string $pageName): iterable
    {
        $matchDataField = Field::new('matchData', 'Match-Details (JSON)')
            ->onlyOnDetail()
            ->setTemplatePath('@EasyAdmin/crud/field/text.html.twig')
            ->formatValue(fn (mixed $value): \Twig\Markup => new \Twig\Markup($this->renderMatchData($value), 'UTF-8'));

        return [
            IdField::new('id')->onlyOnDetail(),
            ChoiceField::new('status', 'Status')
                ->setChoices([
                    'Neu' => VehicleCoverageMatch::STATUS_NEW,
                    'Zu prüfen' => VehicleCoverageMatch::STATUS_REVIEW,
                    'Bestätigt' => VehicleCoverageMatch::STATUS_CONFIRMED,
                    'Abgelehnt' => VehicleCoverageMatch::STATUS_REJECTED,
                    'Ignoriert' => VehicleCoverageMatch::STATUS_IGNORED,
                    'Veröffentlicht' => VehicleCoverageMatch::STATUS_PUBLISHED,
                ]),
            ChoiceField::new('matchLevel', 'Match-Level')
                ->setChoices([
                    'Möglich' => VehicleCoverageMatch::LEVEL_POSSIBLE,
                    'Wahrscheinlich' => VehicleCoverageMatch::LEVEL_LIKELY,
                    'Stark' => VehicleCoverageMatch::LEVEL_STRONG,
                    'Exakt' => VehicleCoverageMatch::LEVEL_EXACT,
                ]),
            NumberField::new('matchScore', 'Score')->setNumDecimals(2),
            AssociationField::new('provider', 'Anbieter')->setRequired(false),
            AssociationField::new('newsItem', 'Anbieter-News')->setRequired(false)->hideOnIndex(),
            AssociationField::new('detectedEcu', 'Erkannte ECU')->hideOnIndex(),
            TextField::new('vehicleType', 'Fahrzeugtyp')->setRequired(false)->hideOnIndex(),
            TextField::new('vehicleBrand', 'Marke')->setRequired(false),
            TextField::new('vehicleSeries', 'Baureihe')->setRequired(false),
            TextField::new('vehicleModel', 'Modell')->setRequired(false),
            TextField::new('vehicleEngine', 'Motor')->setRequired(false),
            TextField::new('ecuName', 'ECU-Name')->setRequired(false),
            TextField::new('ecuFamily', 'ECU-Familie')->setRequired(false)->hideOnIndex(),
            TextField::new('mcu', 'MCU')->setRequired(false),
            Field::new('detectedEcu', 'Lese-/Schreibmethode')
                ->onlyOnIndex()
                ->formatValue(static fn (mixed $value, VehicleCoverageMatch $match): string => (string) ($match->getDetectedEcu()?->getReadMethod() ?? '')),
            TextField::new('engineCode', 'Motor-Code')->setRequired(false)->hideOnIndex(),
            TextField::new('fuel', 'Kraftstoff')->setRequired(false)->hideOnIndex(),
            NumberField::new('hp', 'PS')->setRequired(false)->hideOnIndex(),
            NumberField::new('kw', 'kW')->setRequired(false)->hideOnIndex(),
            NumberField::new('nm', 'Nm')->setRequired(false)->hideOnIndex(),
            NumberField::new('yearFrom', 'Baujahr von')->setRequired(false)->hideOnIndex(),
            NumberField::new('yearTo', 'Baujahr bis')->setRequired(false)->hideOnIndex(),
            TextField::new('matchReason', 'Match-Begründung')->hideOnIndex(),
            $matchDataField,
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm()->onlyOnDetail(),
        ];
    }

    #[Route('/admin/ecu-intelligence/vehicle-coverage-matches/{id}/confirm', name: 'admin_vehicle_coverage_matches_confirm', methods: ['GET'])]
    public function confirm(VehicleCoverageMatch $match, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        $match->setStatus(VehicleCoverageMatch::STATUS_CONFIRMED);
        $match->setUpdatedAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Fahrzeug-Match wurde bestätigt.');

        return $this->redirectBack($request, $match);
    }

    #[Route('/admin/ecu-intelligence/vehicle-coverage-matches/{id}/reject', name: 'admin_vehicle_coverage_matches_reject', methods: ['GET'])]
    public function reject(VehicleCoverageMatch $match, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        $match->setStatus(VehicleCoverageMatch::STATUS_REJECTED);
        $match->setUpdatedAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Fahrzeug-Match wurde abgelehnt.');

        return $this->redirectBack($request, $match);
    }

    #[Route('/admin/ecu-intelligence/vehicle-coverage-matches/{id}/ignore', name: 'admin_vehicle_coverage_matches_ignore', methods: ['GET'])]
    public function ignore(VehicleCoverageMatch $match, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        $match->setStatus(VehicleCoverageMatch::STATUS_IGNORED);
        $match->setUpdatedAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Fahrzeug-Match wurde ignoriert.');

        return $this->redirectBack($request, $match);
    }

    #[Route('/admin/ecu-intelligence/vehicle-coverage-matches/{id}/publish', name: 'admin_vehicle_coverage_matches_publish', methods: ['GET'])]
    public function publish(VehicleCoverageMatch $match, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        $match->setStatus(VehicleCoverageMatch::STATUS_PUBLISHED);
        $match->setUpdatedAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Fahrzeug-Match wurde als freigegeben markiert (noch keine automatische Uebernahme in Konfigurator/API).');

        return $this->redirectBack($request, $match);
    }

    private function redirectBack(Request $request, VehicleCoverageMatch $match): RedirectResponse
    {
        $referer = $request->headers->get('referer');
        if (null !== $referer) {
            return $this->redirect($referer);
        }

        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId((string) $match->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    private function renderMatchData(mixed $value): string
    {
        if (!is_array($value) || [] === $value) {
            return '<span class="text-muted">Keine Match-Details vorhanden.</span>';
        }

        $json = json_encode($value, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        $escaped = htmlspecialchars((string) $json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<pre style="white-space:pre-wrap;max-height:520px;overflow:auto;margin:0;background:#0f1621;color:#dce4ef;padding:12px;border-radius:0;border:1px solid #29384d;">%s</pre>',
            $escaped
        );
    }
}
