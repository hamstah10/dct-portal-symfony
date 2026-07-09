<?php

namespace App\Controller\Admin;

use App\Entity\Abo;
use App\Form\KundeAutocompleteField;
use App\Form\SoftwareAutocompleteField;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[AdminCrud(routePath: '/abos', routeName: 'admin_abos')]
final class AboCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly Connection $connection,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Abo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Abo')
            ->setEntityLabelInPlural('Abos')
            ->overrideTemplate('crud/index', 'admin/abo/index.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('kunde')
                ->setFormType(KundeAutocompleteField::class),
            AssociationField::new('software')
                ->setRequired(false)
                ->setFormType(SoftwareAutocompleteField::class)
                ->setHelp('Alternative zu Paket + Laufzeit. Nur eines von beiden auswaehlen.'),
            AssociationField::new('preisstaffel', 'Paket + Laufzeit')
                ->setRequired(false)
                ->setHelp('Alternative zu Software. Nur eines von beiden auswaehlen.'),
            AssociationField::new('aboAuswahl', 'Paket')->hideOnForm(),
            TextareaField::new('leistungen'),
            DateTimeField::new('startDatum'),
            DateTimeField::new('endDatum'),
            ChoiceField::new('status')->setChoices([
                'Aktiv' => 'aktiv',
                'Pausiert' => 'pausiert',
                'Gekuendigt' => 'gekuendigt',
            ]),
            ChoiceField::new('zahlweise', 'Zahlweise')->setChoices([
                'Monatlich' => 'monatlich',
                'Jaehrlich' => 'jaehrlich',
            ]),
            TextField::new('link', 'Link')->setRequired(false)->setHelp('Optionaler Abo-Link, z.B. Portal oder Vertragsseite.'),
            MoneyField::new('preis')->setCurrency('EUR')->setStoredAsCents(false)->hideOnForm(),
            TextareaField::new('notizen'),
            DateTimeField::new('createdAt')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Abo) {
            $this->syncAboAuswahlAndPreis($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Abo) {
            $this->syncAboAuswahlAndPreis($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function syncAboAuswahlAndPreis(Abo $abo): void
    {
        $preisstaffel = $abo->getPreisstaffel();
        if (null === $preisstaffel) {
            $abo->setAboAuswahl(null);
            $abo->setPreis(null);
            return;
        }

        $abo->setSoftware(null);
        $abo->setAboAuswahl($preisstaffel->getAboAuswahl());

        $laufzeit = max(1, $preisstaffel->getLaufzeitMonate());

        if ('jaehrlich' === $abo->getZahlweise()) {
            if (null !== $preisstaffel->getPreisJaehrlich()) {
                $monatlicherBasisPreis = (float) $preisstaffel->getPreisJaehrlich();
            } elseif (null !== $preisstaffel->getPreisMonatlich()) {
                $monatlicherBasisPreis = (float) $preisstaffel->getPreisMonatlich();
            } else {
                $monatlicherBasisPreis = 0.0;
            }
        } elseif (null !== $preisstaffel->getPreisMonatlich()) {
            $monatlicherBasisPreis = (float) $preisstaffel->getPreisMonatlich();
        } elseif (null !== $preisstaffel->getPreisJaehrlich()) {
            $monatlicherBasisPreis = (float) $preisstaffel->getPreisJaehrlich();
        } else {
            $monatlicherBasisPreis = 0.0;
        }

        $preisEinrichtung = null !== $preisstaffel->getPreisEinrichtung()
            ? (float) $preisstaffel->getPreisEinrichtung()
            : 0.0;

        $gesamtpreis = ($monatlicherBasisPreis * $laufzeit) + $preisEinrichtung;

        $abo->setPreis(number_format($gesamtpreis, 2, '.', ''));
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        $pageName = (string) $responseParameters->get('pageName');
        if (Crud::PAGE_INDEX !== $pageName) {
            return $responseParameters;
        }

        $currentYear = (int) (new \DateTimeImmutable())->format('Y');
        $yearStart = (new \DateTimeImmutable(sprintf('%d-01-01 00:00:00', $currentYear)));
        $yearEnd = (new \DateTimeImmutable(sprintf('%d-12-31 23:59:59', $currentYear)));

        $months = [];
        $monthLabels = [];
        $monthStarts = [];
        $monthEnds = [];
        $cursor = $yearStart;
        for ($i = 0; $i < 12; ++$i) {
            $key = $cursor->format('Y-m');
            $months[] = $key;
            $monthLabels[] = $cursor->format('m/Y');
            $monthStarts[$key] = $cursor;
            $monthEnds[$key] = $cursor->modify('last day of this month')->setTime(23, 59, 59);
            $cursor = $cursor->modify('+1 month');
        }

        $abos = $this->connection->fetchAllAssociative(
            'SELECT a.start_datum, a.end_datum, a.zahlweise, ps.preis_monatlich, ps.preis_jaehrlich, ps.preis_einrichtung
             FROM abo a
             LEFT JOIN abo_auswahl_preisstaffel ps ON ps.id = a.preisstaffel_id
             WHERE a.start_datum <= :yearEnd
               AND (a.end_datum IS NULL OR a.end_datum >= :yearStart)',
            [
                'yearStart' => $yearStart->format('Y-m-d H:i:s'),
                'yearEnd' => $yearEnd->format('Y-m-d H:i:s'),
            ]
        );

        $aboRevenueMap = array_fill_keys($months, 0.0);
        $setupRevenueMap = array_fill_keys($months, 0.0);

        foreach ($abos as $row) {
            $start = new \DateTimeImmutable((string) $row['start_datum']);
            $ende = null !== $row['end_datum'] ? new \DateTimeImmutable((string) $row['end_datum']) : null;
            $zahlweise = (string) ($row['zahlweise'] ?? 'monatlich');

            $preisMonatlich = null !== $row['preis_monatlich'] ? (float) $row['preis_monatlich'] : null;
            $preisJaehrlich = null !== $row['preis_jaehrlich'] ? (float) $row['preis_jaehrlich'] : null;

            if ('jaehrlich' === $zahlweise) {
                $monatlicherPreis = $preisJaehrlich ?? $preisMonatlich ?? 0.0;
            } else {
                $monatlicherPreis = $preisMonatlich ?? $preisJaehrlich ?? 0.0;
            }

            $einrichtungsgebuehr = null !== $row['preis_einrichtung'] ? (float) $row['preis_einrichtung'] : 0.0;

            foreach ($months as $monthKey) {
                $monthStart = $monthStarts[$monthKey];
                $monthEnd = $monthEnds[$monthKey];

                if ($start > $monthEnd) {
                    continue;
                }
                if (null !== $ende && $ende < $monthStart) {
                    continue;
                }

                $aboRevenueMap[$monthKey] += $monatlicherPreis;
            }

            $startKey = $start->format('Y-m');
            if (isset($setupRevenueMap[$startKey])) {
                $setupRevenueMap[$startKey] += $einrichtungsgebuehr;
            }
        }

        $aboRevenueValues = array_map(static fn (string $month): float => $aboRevenueMap[$month], $months);
        $setupRevenueValues = array_map(static fn (string $month): float => $setupRevenueMap[$month], $months);

        $aboRevenueChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $aboRevenueChart->setData([
            'labels' => $monthLabels,
            'datasets' => [[
                'label' => 'Abo Umsatz pro Monat (EUR)',
                'data' => $aboRevenueValues,
                'borderColor' => '#20c997',
                'backgroundColor' => 'rgba(32, 201, 151, 0.18)',
                'fill' => true,
                'tension' => 0.35,
            ]],
        ]);
        $aboRevenueChart->setOptions([
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $setupRevenueChart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $setupRevenueChart->setData([
            'labels' => $monthLabels,
            'datasets' => [[
                'label' => 'Einrichtungsgebuehr pro Monat (EUR)',
                'data' => $setupRevenueValues,
                'backgroundColor' => '#fd7e14',
            ]],
        ]);
        $setupRevenueChart->setOptions([
            'scales' => ['y' => ['beginAtZero' => true]],
            'maintainAspectRatio' => false,
        ]);

        $responseParameters->set('aboRevenueChart', $aboRevenueChart);
        $responseParameters->set('setupRevenueChart', $setupRevenueChart);
        $responseParameters->set('aboRevenueSum', array_sum($aboRevenueValues));
        $responseParameters->set('setupRevenueSum', array_sum($setupRevenueValues));
        $responseParameters->set('chartYear', $currentYear);

        return $responseParameters;
    }
}
