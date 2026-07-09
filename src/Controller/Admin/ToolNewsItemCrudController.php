<?php

namespace App\Controller\Admin;

use App\Entity\DetectedEcu;
use App\Entity\ToolNewsItem;
use App\Repository\DetectedEcuRepository;
use App\Repository\VehicleCoverageMatchRepository;
use App\Service\ToolNews\EcuDetector;
use App\Service\ToolNews\VehicleMatcher;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[AdminCrud(routePath: '/ecu-intelligence/tool-news-items', routeName: 'admin_tool_news_items')]
final class ToolNewsItemCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly DetectedEcuRepository $detectedEcuRepository,
        private readonly VehicleCoverageMatchRepository $vehicleCoverageMatchRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ToolNewsItem::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Anbieter-News')
            ->setEntityLabelInPlural('Anbieter-News')
            ->setDefaultSort(['publishedAt' => 'DESC', 'createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $analyzeAction = Action::new('analyzeEcu', 'ECU analysieren')
            ->setIcon('fa fa-magnifying-glass')
            ->linkToRoute('admin_tool_news_items_analyze', static fn (ToolNewsItem $newsItem): array => ['id' => $newsItem->getId()]);

        $analyzeAndMatchAction = Action::new('analyzeAndMatch', 'Analyse + Matching starten')
            ->setIcon('fa fa-wand-magic-sparkles')
            ->linkToRoute('admin_tool_news_items_analyze_and_match', static fn (ToolNewsItem $newsItem): array => ['id' => $newsItem->getId()]);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $analyzeAction)
            ->add(Crud::PAGE_DETAIL, $analyzeAction)
            ->add(Crud::PAGE_INDEX, $analyzeAndMatchAction)
            ->add(Crud::PAGE_DETAIL, $analyzeAndMatchAction);
    }

    public function configureFields(string $pageName): iterable
    {
        $rawDataField = Field::new('rawData', 'Rohdaten (JSON)')
            ->onlyOnDetail()
            ->setTemplatePath('@EasyAdmin/crud/field/text.html.twig')
            ->formatValue(fn (mixed $value): \Twig\Markup => new \Twig\Markup($this->renderRawData($value), 'UTF-8'));

        return [
            IdField::new('id')->onlyOnDetail(),
            AssociationField::new('provider', 'Anbieter')->setSortProperty('name'),
            AssociationField::new('source', 'Quelle')->setRequired(false)->setSortProperty('name')->onlyOnDetail(),
            TextField::new('title', 'Titel'),
            UrlField::new('url', 'URL'),
            UrlField::new('imageUrl', 'Bild')->setRequired(false),
            TextareaField::new('excerpt', 'Auszug')->hideOnIndex(),
            TextareaField::new('content', 'Inhalt')
                ->hideOnIndex()
                ->setNumOfRows(15),
            DateTimeField::new('publishedAt', 'Veröffentlicht am')->setRequired(false),
            ChoiceField::new('status', 'Status')
                ->setChoices([
                    'Neu' => ToolNewsItem::STATUS_NEW,
                    'Importiert' => ToolNewsItem::STATUS_IMPORTED,
                    'Analysiert' => ToolNewsItem::STATUS_ANALYZED,
                    'Keine ECU gefunden' => ToolNewsItem::STATUS_NO_ECU_FOUND,
                    'Geprüft' => ToolNewsItem::STATUS_REVIEWED,
                    'Zu prüfen' => ToolNewsItem::STATUS_REVIEW,
                    'Ignoriert' => ToolNewsItem::STATUS_IGNORED,
                    'Archiviert' => ToolNewsItem::STATUS_ARCHIVED,
                    'Fehler' => ToolNewsItem::STATUS_ERROR,
                ]),
            TextField::new('contentHash', 'Content-Hash')->hideOnForm()->onlyOnDetail(),
            $rawDataField,
            Field::new('detectedEcus', 'Erkannte ECUs')
                ->onlyOnDetail()
                ->formatValue(fn (mixed $value, ToolNewsItem $newsItem): \Twig\Markup => new \Twig\Markup($this->renderDetectedEcus($newsItem), 'UTF-8')),
            Field::new('vehicleCoverageMatches', 'Fahrzeug-Matches')
                ->onlyOnDetail()
                ->formatValue(fn (mixed $value, ToolNewsItem $newsItem): \Twig\Markup => new \Twig\Markup($this->renderVehicleCoverageMatches($newsItem), 'UTF-8')),
            DateTimeField::new('createdAt', 'Erstellt am')->hideOnForm(),
            DateTimeField::new('updatedAt', 'Aktualisiert am')->hideOnForm(),
        ];
    }

    #[Route('/admin/ecu-intelligence/tool-news-items/{id}/analyze', name: 'admin_tool_news_items_analyze', methods: ['GET'])]
    public function analyzeEcu(ToolNewsItem $newsItem, EcuDetector $ecuDetector, EntityManagerInterface $entityManager): RedirectResponse
    {
        $created = $this->runEcuAnalysis($newsItem, $ecuDetector, $entityManager);

        if ([] !== $created) {
            $this->addFlash('success', sprintf('%d ECU(s) erkannt.', count($created)));
        } else {
            $this->addFlash('info', 'Keine ECU im Text gefunden.');
        }

        return $this->redirectToDetail($newsItem);
    }

    #[Route('/admin/ecu-intelligence/tool-news-items/{id}/analyze-and-match', name: 'admin_tool_news_items_analyze_and_match', methods: ['GET'])]
    public function analyzeAndMatch(ToolNewsItem $newsItem, EcuDetector $ecuDetector, VehicleMatcher $vehicleMatcher, EntityManagerInterface $entityManager): RedirectResponse
    {
        $createdEcus = $this->runEcuAnalysis($newsItem, $ecuDetector, $entityManager);

        $totalVehicleMatches = 0;
        foreach ($createdEcus as $detectedEcu) {
            $totalVehicleMatches += count($vehicleMatcher->matchDetectedEcu($detectedEcu));
        }

        if ([] === $createdEcus) {
            $this->addFlash('info', 'Keine ECU im Text gefunden, Fahrzeug-Matching übersprungen.');
        } else {
            $this->addFlash('success', sprintf(
                '%d ECU(s) erkannt, %d Fahrzeug-Match(es) erzeugt.',
                count($createdEcus),
                $totalVehicleMatches
            ));
        }

        return $this->redirectToDetail($newsItem);
    }

    /**
     * Runs ECU detection for a news item, replacing any previously detected
     * ECUs, and persists the newly created DetectedEcu records.
     *
     * @return list<DetectedEcu>
     */
    private function runEcuAnalysis(ToolNewsItem $newsItem, EcuDetector $ecuDetector, EntityManagerInterface $entityManager): array
    {
        $existing = $this->detectedEcuRepository->findByNewsItem($newsItem);
        if ([] !== $existing) {
            $this->detectedEcuRepository->removeAllForNewsItem($newsItem);
        }

        $matches = $ecuDetector->analyzeNewsItem($newsItem);
        $seen = [];
        $createdEcus = [];

        foreach ($matches as $match) {
            $dedupKey = implode('|', [
                $match['ecuName'] ?? '',
                $match['ecuFamily'] ?? '',
                $match['manufacturer'] ?? '',
                $match['mcu'] ?? '',
                $match['readMethod'] ?? '',
                $match['protocol'] ?? '',
            ]);
            if (isset($seen[$dedupKey])) {
                continue;
            }
            $seen[$dedupKey] = true;

            $detectedEcu = new DetectedEcu();
            $detectedEcu->setNewsItem($newsItem);
            $detectedEcu->setProvider($newsItem->getProvider());
            $detectedEcu->setManufacturer($match['manufacturer']);
            $detectedEcu->setEcuFamily($match['ecuFamily']);
            $detectedEcu->setEcuName($match['ecuName']);
            $detectedEcu->setMcu($match['mcu']);
            $detectedEcu->setReadMethod($match['readMethod']);
            $detectedEcu->setProtocol($match['protocol']);
            $detectedEcu->setVehicleHint($match['vehicleHint']);
            $detectedEcu->setConfidence($match['confidence']);
            $detectedEcu->setRawText($match['rawText']);
            $detectedEcu->setMatchData([] !== $match['matchData'] ? $match['matchData'] : null);

            $entityManager->persist($detectedEcu);
            $createdEcus[] = $detectedEcu;
        }

        $newsItem->setStatus([] !== $createdEcus ? ToolNewsItem::STATUS_ANALYZED : ToolNewsItem::STATUS_NO_ECU_FOUND);
        $newsItem->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        return $createdEcus;
    }

    private function redirectToDetail(ToolNewsItem $newsItem): RedirectResponse
    {
        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId((string) $newsItem->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    private function renderDetectedEcus(ToolNewsItem $newsItem): string
    {
        $detectedEcus = $this->detectedEcuRepository->findByNewsItem($newsItem);
        if ([] === $detectedEcus) {
            return '<span class="text-muted">Noch keine ECUs erkannt. Klick auf "ECU analysieren".</span>';
        }

        $listUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(DetectedEcuCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[newsItem][value]', $newsItem->getId())
            ->generateUrl();

        $rows = '';
        foreach ($detectedEcus as $ecu) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                htmlspecialchars((string) $ecu->getManufacturer(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $ecu->getEcuName(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $ecu->getMcu(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $ecu->getReadMethod(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $ecu->getProtocol(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $ecu->getVehicleHint(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($ecu->getStatus(), ENT_QUOTES, 'UTF-8')
            );
        }

        return sprintf(
            '<table class="table table-sm mb-2"><thead><tr><th>Hersteller</th><th>ECU</th><th>MCU</th><th>Methode</th><th>Protokoll</th><th>Fahrzeug</th><th>Status</th></tr></thead><tbody>%s</tbody></table><a href="%s" class="btn btn-sm btn-secondary">Alle in "Erkannte ECUs" ansehen</a>',
            $rows,
            htmlspecialchars($listUrl, ENT_QUOTES, 'UTF-8')
        );
    }

    private function renderVehicleCoverageMatches(ToolNewsItem $newsItem): string
    {
        $matches = $this->vehicleCoverageMatchRepository->findBy(['newsItem' => $newsItem]);
        if ([] === $matches) {
            return '<span class="text-muted">Noch keine Fahrzeug-Matches.</span>';
        }

        $listUrl = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(VehicleCoverageMatchCrudController::class)
            ->setAction(Action::INDEX)
            ->set('filters[newsItem][value]', $newsItem->getId())
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

    private function renderRawData(mixed $value): string
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
}
