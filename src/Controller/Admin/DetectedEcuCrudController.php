<?php

namespace App\Controller\Admin;

use App\Entity\DetectedEcu;
use App\Repository\VehicleCoverageMatchRepository;
use App\Service\ToolNews\VehicleMatcher;
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

#[AdminCrud(routePath: '/ecu-intelligence/detected-ecus', routeName: 'admin_detected_ecus')]
final class DetectedEcuCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly VehicleCoverageMatchRepository $vehicleCoverageMatchRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return DetectedEcu::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Erkannte ECU')
            ->setEntityLabelInPlural('Erkannte ECUs')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('status')
            ->add('provider')
            ->add('manufacturer')
            ->add('ecuFamily')
            ->add('readMethod')
            ->add('protocol')
            ->add('createdAt');
    }

    public function configureActions(Actions $actions): Actions
    {
        $confirmAction = Action::new('confirm', 'Bestätigen')
            ->setIcon('fa fa-check')
            ->linkToRoute('admin_detected_ecus_confirm', static fn (DetectedEcu $ecu): array => ['id' => $ecu->getId()])
            ->displayIf(static fn (DetectedEcu $ecu): bool => DetectedEcu::STATUS_CONFIRMED !== $ecu->getStatus());

        $rejectAction = Action::new('reject', 'Ablehnen')
            ->setIcon('fa fa-xmark')
            ->linkToRoute('admin_detected_ecus_reject', static fn (DetectedEcu $ecu): array => ['id' => $ecu->getId()])
            ->displayIf(static fn (DetectedEcu $ecu): bool => DetectedEcu::STATUS_REJECTED !== $ecu->getStatus());

        $ignoreAction = Action::new('ignore', 'Ignorieren')
            ->setIcon('fa fa-eye-slash')
            ->linkToRoute('admin_detected_ecus_ignore', static fn (DetectedEcu $ecu): array => ['id' => $ecu->getId()])
            ->displayIf(static fn (DetectedEcu $ecu): bool => DetectedEcu::STATUS_IGNORED !== $ecu->getStatus());

        $openNewsAction = Action::new('openNews', 'Zur News öffnen')
            ->setIcon('fa fa-newspaper')
            ->linkToUrl(static fn (DetectedEcu $ecu): string => $ecu->getNewsItem()?->getUrl() ?? '#')
            ->setHtmlAttributes(['target' => '_blank', 'rel' => 'noopener']);

        $matchVehiclesAction = Action::new('matchVehicles', 'Fahrzeuge matchen')
            ->setIcon('fa fa-car-side')
            ->linkToRoute('admin_detected_ecus_match_vehicles', static fn (DetectedEcu $ecu): array => ['id' => $ecu->getId()]);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $confirmAction)
            ->add(Crud::PAGE_DETAIL, $confirmAction)
            ->add(Crud::PAGE_INDEX, $rejectAction)
            ->add(Crud::PAGE_DETAIL, $rejectAction)
            ->add(Crud::PAGE_INDEX, $ignoreAction)
            ->add(Crud::PAGE_DETAIL, $ignoreAction)
            ->add(Crud::PAGE_INDEX, $openNewsAction)
            ->add(Crud::PAGE_DETAIL, $openNewsAction)
            ->add(Crud::PAGE_INDEX, $matchVehiclesAction)
            ->add(Crud::PAGE_DETAIL, $matchVehiclesAction);
    }

    public function configureFields(string $pageName): iterable
    {
        $matchDataField = Field::new('matchData', 'Rohdaten (JSON)')
            ->onlyOnDetail()
            ->setTemplatePath('@EasyAdmin/crud/field/text.html.twig')
            ->formatValue(fn (mixed $value): \Twig\Markup => new \Twig\Markup($this->renderMatchData($value), 'UTF-8'));

        return [
            IdField::new('id')->onlyOnDetail(),
            ChoiceField::new('status', 'Status')
                ->setChoices([
                    'Neu' => DetectedEcu::STATUS_NEW,
                    'Zu prüfen' => DetectedEcu::STATUS_REVIEW,
                    'Bestätigt' => DetectedEcu::STATUS_CONFIRMED,
                    'Abgelehnt' => DetectedEcu::STATUS_REJECTED,
                    'Ignoriert' => DetectedEcu::STATUS_IGNORED,
                ]),
            AssociationField::new('provider', 'Anbieter')->setRequired(false),
            AssociationField::new('newsItem', 'Anbieter-News')->hideOnIndex(),
            TextField::new('manufacturer', 'Hersteller')->setRequired(false),
            TextField::new('ecuFamily', 'ECU-Familie')->setRequired(false),
            TextField::new('ecuName', 'ECU-Name')->setRequired(false),
            TextField::new('mcu', 'MCU')->setRequired(false),
            TextField::new('readMethod', 'Lese-/Schreibmethode')->setRequired(false),
            TextField::new('protocol', 'Protokoll/Typ')->setRequired(false)->hideOnIndex(),
            TextField::new('vehicleHint', 'Fahrzeug-Hinweis')->setRequired(false),
            NumberField::new('confidence', 'Konfidenz')->setNumDecimals(2),
            TextField::new('rawText', 'Analysierter Text')
                ->onlyOnDetail(),
            $matchDataField,
            Field::new('vehicleCoverageMatches', 'Fahrzeug-Matches')
                ->onlyOnDetail()
                ->formatValue(fn (mixed $value, DetectedEcu $ecu): \Twig\Markup => new \Twig\Markup($this->renderVehicleCoverageMatches($ecu), 'UTF-8')),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm()->onlyOnDetail(),
        ];
    }

    #[Route('/admin/ecu-intelligence/detected-ecus/{id}/confirm', name: 'admin_detected_ecus_confirm', methods: ['GET'])]
    public function confirm(DetectedEcu $detectedEcu, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        $detectedEcu->setStatus(DetectedEcu::STATUS_CONFIRMED);
        $detectedEcu->setUpdatedAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Erkannte ECU wurde bestätigt.');

        return $this->redirectBack($request, $detectedEcu);
    }

    #[Route('/admin/ecu-intelligence/detected-ecus/{id}/reject', name: 'admin_detected_ecus_reject', methods: ['GET'])]
    public function reject(DetectedEcu $detectedEcu, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        $detectedEcu->setStatus(DetectedEcu::STATUS_REJECTED);
        $detectedEcu->setUpdatedAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Erkannte ECU wurde abgelehnt.');

        return $this->redirectBack($request, $detectedEcu);
    }

    #[Route('/admin/ecu-intelligence/detected-ecus/{id}/ignore', name: 'admin_detected_ecus_ignore', methods: ['GET'])]
    public function ignore(DetectedEcu $detectedEcu, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        $detectedEcu->setStatus(DetectedEcu::STATUS_IGNORED);
        $detectedEcu->setUpdatedAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Erkannte ECU wurde ignoriert.');

        return $this->redirectBack($request, $detectedEcu);
    }

    #[Route('/admin/ecu-intelligence/detected-ecus/{id}/match-vehicles', name: 'admin_detected_ecus_match_vehicles', methods: ['GET'])]
    public function matchVehicles(DetectedEcu $detectedEcu, VehicleMatcher $vehicleMatcher, Request $request): RedirectResponse
    {
        $results = $vehicleMatcher->matchDetectedEcu($detectedEcu, true);

        $this->addFlash(
            [] !== $results ? 'success' : 'info',
            [] !== $results
                ? sprintf('%d Fahrzeug-Match(es) gefunden.', count($results))
                : 'Keine passenden Fahrzeuge gefunden.'
        );

        return $this->redirectBack($request, $detectedEcu);
    }

    private function redirectBack(Request $request, DetectedEcu $detectedEcu): RedirectResponse
    {
        $referer = $request->headers->get('referer');
        if (null !== $referer) {
            return $this->redirect($referer);
        }

        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId((string) $detectedEcu->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    private function renderMatchData(mixed $value): string
    {
        if (!is_array($value) || [] === $value) {
            return '<span class="text-muted">Keine Rohdaten vorhanden.</span>';
        }

        $json = json_encode($value, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        $escaped = htmlspecialchars((string) $json, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<pre style="white-space:pre-wrap;max-height:520px;overflow:auto;margin:0;background:#0f1621;color:#dce4ef;padding:12px;border-radius:0;border:1px solid #29384d;">%s</pre>',
            $escaped
        );
    }

    private function renderVehicleCoverageMatches(DetectedEcu $detectedEcu): string
    {
        $matches = $this->vehicleCoverageMatchRepository->findByDetectedEcu($detectedEcu);
        if ([] === $matches) {
            return '<span class="text-muted">Noch keine Fahrzeug-Matches. Klick auf "Fahrzeuge matchen".</span>';
        }

        $listUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(VehicleCoverageMatchCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[detectedEcu][value]', $detectedEcu->getId())
            ->generateUrl();

        $rows = '';
        foreach ($matches as $match) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%.2f</td><td>%s</td><td>%s</td></tr>',
                htmlspecialchars((string) $match->getVehicleBrand(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $match->getVehicleSeries(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $match->getVehicleModel(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $match->getVehicleEngine(), ENT_QUOTES, 'UTF-8'),
                $match->getMatchScore(),
                htmlspecialchars($match->getMatchLevel(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($match->getStatus(), ENT_QUOTES, 'UTF-8')
            );
        }

        return sprintf(
            '<table class="table table-sm mb-2"><thead><tr><th>Marke</th><th>Baureihe</th><th>Modell</th><th>Motor</th><th>Score</th><th>Level</th><th>Status</th></tr></thead><tbody>%s</tbody></table><a href="%s" class="btn btn-sm btn-secondary">Alle in "Fahrzeug-Matches" ansehen</a>',
            $rows,
            htmlspecialchars($listUrl, ENT_QUOTES, 'UTF-8')
        );
    }
}
