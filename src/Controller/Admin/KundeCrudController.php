<?php

namespace App\Controller\Admin;

use App\Entity\Kunde;
use App\Entity\KundeCreditTransaction;
use App\Entity\User;
use App\Portals\Chiptuningfile\Service\PortalInvoiceStatusService;
use App\Portals\Chiptuningfile\Security\ChiptuningfilePortalAccessChecker;
use App\Repository\CreditPackageRepository;
use App\Repository\KundeCreditTransactionRepository;
use App\Repository\LexwareInvoiceRepository;
use App\Service\CustomerMapBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\ActionGroup;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

#[AdminCrud(routePath: '/kunden', routeName: 'admin_kunden')]
final class KundeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
        private readonly KundeCreditTransactionRepository $kundeCreditTransactionRepository,
        private readonly LexwareInvoiceRepository $lexwareInvoiceRepository,
        private readonly CreditPackageRepository $creditPackageRepository,
        private readonly CustomerMapBuilder $customerMapBuilder,
        private readonly PortalInvoiceStatusService $portalInvoiceStatusService,
        private readonly ChiptuningfilePortalAccessChecker $chiptuningfilePortalAccessChecker,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Kunde::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Kunde')
            ->setEntityLabelInPlural('Kunden')
            ->overrideTemplate('crud/index', 'admin/kunde/index.html.twig')
            ->overrideTemplate('crud/detail', 'admin/kunde/detail.html.twig')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $topupCreditsAction = Action::new('topupCredits', 'Credits aufladen')
            ->setIcon('tabler:credits')
            ->linkToCrudAction('topupCredits')
            ->displayIf(static fn (Kunde $kunde): bool => null !== $kunde->getId());

        $creditsHistoryAction = Action::new('creditsHistory', 'Credit-Historie')
            ->setIcon('ic:baseline-history')
            ->linkToCrudAction('creditsHistory')
            ->displayIf(static fn (Kunde $kunde): bool => null !== $kunde->getId());

        $detailAction = Action::new(Action::DETAIL, 'Anzeigen', 'internal:detail')
            ->linkToCrudAction(Action::DETAIL);
        $editAction = Action::new(Action::EDIT, 'Bearbeiten', 'internal:edit')
            ->linkToCrudAction(Action::EDIT)
            ->asPrimaryAction();
        $deleteAction = Action::new(Action::DELETE, 'Loeschen', 'internal:delete')
            ->linkToCrudAction(Action::DELETE)
            ->asDangerAction()
            ->asTextLink()
            ->askConfirmation();

        $indexActionGroup = ActionGroup::new('kundeActions', 'Aktionen')
            ->addAction($detailAction)
            ->addAction($editAction)
            ->addAction($topupCreditsAction)
            ->addAction($creditsHistoryAction)
            ->addAction($deleteAction);

        return $actions
            ->add(Crud::PAGE_INDEX, $indexActionGroup)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $topupCreditsAction)
            ->add(Crud::PAGE_EDIT, $topupCreditsAction)
            ->add(Crud::PAGE_DETAIL, $creditsHistoryAction)
            ->add(Crud::PAGE_EDIT, $creditsHistoryAction)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        $passwort = TextField::new('plainPasswort', 'Passwort')
            ->onlyOnForms()
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setHelp('Bei Bearbeiten leer lassen, wenn das Passwort unveraendert bleiben soll.')
            ->setFormTypeOption('empty_data', '');

        $creditsField = IntegerField::new('credits', 'Credits')
            ->hideOnForm()
            ->formatValue(static function (mixed $value): string {
                return (string) ((int) $value);
            });

        $portalAccessField = TextField::new('email', 'Portal')
            ->renderAsHtml()
            ->formatValue(function (mixed $value, ?Kunde $entity): string {
                if (!$entity instanceof Kunde) {
                    return '-';
                }

                $enabled = $this->chiptuningfilePortalAccessChecker->canAccess($entity);
                $badgeStyle = $enabled
                    ? 'display:inline-flex;align-items:center;padding:0.25rem 0.55rem;border-radius:999px;background:#dcfce7;color:#166534;font-weight:700;font-size:0.85rem;'
                    : 'display:inline-flex;align-items:center;padding:0.25rem 0.55rem;border-radius:999px;background:#fee2e2;color:#991b1b;font-weight:700;font-size:0.85rem;';

                $label = $enabled ? 'CPT aktiv' : 'Kein CPT-Zugang';
                $html = sprintf('<span style="%s">%s</span>', $badgeStyle, htmlspecialchars($label, ENT_QUOTES));

                if (!$enabled) {
                    $companyCode = strtoupper(trim((string) $entity->getFirma()?->getKuerzel()));
                    if ('' !== $companyCode) {
                        $html .= sprintf(
                            '<div style="font-size:0.82rem;color:#64748b;margin-top:0.35rem;">Firma: %s</div>',
                            htmlspecialchars($companyCode, ENT_QUOTES)
                        );
                    }

                    return $html;
                }

                $html .= '<div style="font-size:0.82rem;color:#64748b;margin-top:0.35rem;">chiptuningfile.tuningfux.de</div>';

                return $html;
            });

        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            FormField::addFieldset('Stammdaten'),
            TextField::new('kundennummer'),
            AssociationField::new('firma')
                
                ->renderAsHtml()
                ->formatValue(static function (mixed $value, ?Kunde $entity): string {
                    $firma = $entity?->getFirma();
                    if (null === $firma) {
                        return '-';
                    }

                    $name = trim((string) ($firma->getName() ?? ''));
                    $logoPath = trim((string) ($firma->getLogo() ?? ''));
                    if ('' !== $logoPath) {
                        $normalizedPath = $logoPath;
                        if (!str_contains($normalizedPath, '/')) {
                            $normalizedPath = 'uploads/firmen/' . $normalizedPath;
                        }

                        $url = (str_starts_with($normalizedPath, 'http://') || str_starts_with($normalizedPath, 'https://'))
                            ? $normalizedPath
                            : '/' . ltrim($normalizedPath, '/');

                        $title = '' !== $name ? $name : 'Firma';

                        return sprintf(
                            '<span style="display:inline-flex;align-items:center;justify-content:center;width: 100px;height:40px;border-radius:0px;overflow:hidden;" title="%s"><img src="%s" alt="%s" style="width:100%%;height:100%%;object-fit:contain;"></span>',
                            htmlspecialchars($title, ENT_QUOTES),
                            htmlspecialchars($url, ENT_QUOTES),
                            htmlspecialchars($title, ENT_QUOTES)
                        );
                    }

                    if ('' !== $name) {
                        return htmlspecialchars($name, ENT_QUOTES);
                    }

                    return '-';
                }),
            TextField::new('firmenname', 'Firma')
                
                ->renderAsHtml()
                ->formatValue(static function (mixed $value, ?Kunde $entity): string {
                    $firmenname = trim((string) ($entity?->getFirmenname() ?? ''));
                    $vorname = trim((string) ($entity?->getVorname() ?? ''));
                    $nachname = trim((string) ($entity?->getNachname() ?? ''));

                    $html = '';
                    if ('' !== $firmenname) {
                        $html .= sprintf('<div style="font-weight:700;">%s</div>', htmlspecialchars($firmenname, ENT_QUOTES));
                    }

                    $fullname = trim(sprintf('%s %s', $vorname, $nachname));
                    if ('' !== $fullname) {
                        $html .= sprintf('<div style="font-size:0.9em;color:#666;">%s</div>', htmlspecialchars($fullname, ENT_QUOTES));
                    }

                    return $html ?: '-';
                }),
            TextField::new('vorname')
                ->hideOnIndex(),
            TextField::new('nachname')
                ->hideOnIndex(),
            $portalAccessField,
            TextField::new('hausnummer')
                ->hideOnIndex(),
            TextField::new('email')->hideOnIndex(),
            $creditsField->hideOnIndex(),
            AssociationField::new('firma')->hideOnForm()->hideOnIndex()->setRequired(true),
            TextField::new('telefon')->hideOnIndex(),
            TextField::new('lexwareContactId', 'Lexoffice Kontakt-ID')
                ->onlyOnDetail(),
            FormField::addFieldset('Adresse & Steuern'),
            TextField::new('strasse')
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(static function (mixed $value, ?Kunde $entity): string {
                    $strasse = trim((string) ($entity?->getStrasse() ?? ''));
                    $hausnummer = trim((string) ($entity?->getHausnummer() ?? ''));
                    $plz = trim((string) ($entity?->getPlz() ?? ''));
                    $stadt = trim((string) ($entity?->getStadt() ?? ''));
                    $address = trim(sprintf('%s %s', $strasse, $hausnummer, $plz, $stadt));
                    if ('' === $address) {
                        return '-';
                    }

                    return htmlspecialchars($address, ENT_QUOTES);
                }),
            TextField::new('ustId', 'USt-Id.')->hideOnIndex(),
            TextField::new('website', 'Website')->hideOnIndex(),
            TextField::new('steuernummer')->hideOnIndex(),
            TextField::new('plz')
            ->hideOnIndex(),
            TextField::new('stadt')
            ->hideOnIndex(),
            TextField::new('land'),
            BooleanField::new('hasDyno', 'Dyno vorhanden'),
            FormField::addFieldset('Tools & Zugang'),
            TextField::new('tuningToolsSummary', 'Tuning-Tools')
                ->hideOnIndex(),
            TextareaField::new('tuningToolsInput', 'Tuning-Tools')
                ->setHelp('Mehrere Tools per Komma oder Zeilenumbruch trennen.')
                ->hideOnIndex(),
            TextField::new('diagnoseToolsSummary', 'Diagnose-Tools')
                ->hideOnIndex(),
            TextareaField::new('diagnoseToolsInput', 'Diagnose-Tools')
                ->setHelp('Mehrere Tools per Komma oder Zeilenumbruch trennen.')
                ->hideOnIndex(),
            ImageField::new('bild')
                ->hideOnIndex()
                ->setBasePath('/uploads/kunden')
                ->setUploadDir('public/uploads/kunden')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[slug]-[contenthash].[extension]')
                ->setRequired(false),
            $passwort,
            FormField::addFieldset('Status & Meta'),
            TextareaField::new('zahlungsbedingungen')->hideOnIndex(),
            DateField::new('registrierungsdatum')->hideOnIndex(),
            BooleanField::new('newsletter')
            ->hideOnIndex(),
            BooleanField::new('gesperrt', 'Gesperrt'),
            DateTimeField::new('createdAt')->hideOnForm()->hideOnIndex(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->hashKundenPasswort($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->hashKundenPasswort($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        $pageName = (string) $responseParameters->get('pageName');
        if (Crud::PAGE_INDEX !== $pageName && Crud::PAGE_DETAIL !== $pageName) {
            return $responseParameters;
        }

        if (Crud::PAGE_INDEX === $pageName) {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT f.id AS id, f.name AS name, f.logo AS logo, UPPER(TRIM(COALESCE(f.kuerzel, \'\'))) AS kuerzel, COUNT(k.id) AS total
                 FROM firma f
                 LEFT JOIN kunde k ON k.firma_id = f.id
                 GROUP BY f.id, f.name, f.logo, UPPER(TRIM(COALESCE(f.kuerzel, \'\')))
                 ORDER BY f.name ASC'
            );

            $kundenFirmStats = [];
            foreach ($rows as $row) {
                $kuerzel = strtoupper(trim((string) ($row['kuerzel'] ?? '')));
                $name = trim((string) ($row['name'] ?? ''));
                $label = '' !== $kuerzel ? $kuerzel : ($name !== '' ? $name : 'Firma');
                $badgeLabel = '' !== $kuerzel ? $kuerzel : $this->buildCompanyInitials($name);
                $badgeColor = match ($kuerzel) {
                    'TFD' => '#bd1e21',
                    'CPT' => '#121212',
                    'TF' => '#111931',
                    default => '#334155',
                };

                $kundenFirmStats[] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'name' => $name,
                    'logoPath' => trim((string) ($row['logo'] ?? '')),
                    'label' => $label,
                    'badgeLabel' => $badgeLabel,
                    'badgeColor' => $badgeColor,
                    'total' => (int) ($row['total'] ?? 0),
                ];
            }

            $responseParameters->set('kundenFirmStats', $kundenFirmStats);
            $responseParameters->set('kundenCountTotal', (int) $this->connection->fetchOne('SELECT COUNT(id) FROM kunde'));

            $entities = $responseParameters->get('entities');
            $customers = [];
            if (is_iterable($entities)) {
                foreach ($entities as $entity) {
                    if (is_object($entity) && method_exists($entity, 'getInstance')) {
                        $instance = $entity->getInstance();
                        if ($instance instanceof Kunde) {
                            $customers[] = $instance;
                        }
                    }
                }
            }

            if ([] === $customers) {
                $customers = $this->entityManager->getRepository(Kunde::class)->findBy([], ['id' => 'DESC'], 100);
            }

            $mapData = $this->customerMapBuilder->build($customers);
            $responseParameters->set('customersMap', $mapData['map']);
            $responseParameters->set('customerMapMarkerCount', $mapData['markerCount']);
            $responseParameters->set('customerMapUnresolvedCount', $mapData['unresolvedCount']);
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            $entityDto = $responseParameters->get('entity');
            $kunde = null;
            if (is_object($entityDto) && method_exists($entityDto, 'getInstance')) {
                $instance = $entityDto->getInstance();
                if ($instance instanceof Kunde) {
                    $kunde = $instance;
                }
            }

            if ($kunde instanceof Kunde) {
                $lexwareInvoices = $this->lexwareInvoiceRepository->findBy(
                    ['kunde' => $kunde],
                    ['createdAt' => 'DESC']
                );
                $responseParameters->set('lexwareInvoiceViews', $this->portalInvoiceStatusService->buildInvoiceViews($lexwareInvoices));
            }
        }

        return $responseParameters;
    }

    #[AdminRoute(path: '/{entityId}/credits/topup', name: 'credits_topup', options: ['methods' => ['GET', 'POST']])]
    public function topupCredits(AdminContext $context): Response
    {
        $request = $context->getRequest();
        $entity = $context->getEntity()?->getInstance();
        if (!$entity instanceof Kunde) {
            $this->addFlash('danger', 'Kunde nicht gefunden.');

            return $this->redirect($this->buildKundeIndexUrl());
        }

        $kunde = $entity;
        $kundeId = $kunde->getId();
        if (null === $kundeId) {
            $this->addFlash('danger', 'Kunde nicht gefunden.');

            return $this->redirect($this->buildKundeIndexUrl());
        }

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_token', '');
            if (!$this->isCsrfTokenValid('kunde_credits_topup_' . (string) $kundeId, $csrfToken)) {
                $this->addFlash('danger', 'Ungueltige Anfrage. Bitte erneut versuchen.');

                return $this->redirect($this->buildKundeCrudActionUrl($kundeId, 'topupCredits'));
            }

            $amountRaw = $request->request->get('amount');
            $amount = is_numeric($amountRaw) ? (int) $amountRaw : 0;
            $description = trim((string) $request->request->get('description', ''));
            $packageId = (int) $request->request->get('packageId', 0);
            $paymentMethod = trim((string) $request->request->get('paymentMethod', 'paypal'));
            $paymentMethod = '' !== $paymentMethod ? strtolower($paymentMethod) : 'paypal';
            $taxRateRaw = $request->request->get('taxRate', 19);
            $taxRate = is_numeric($taxRateRaw) ? (float) str_replace(',', '.', (string) $taxRateRaw) : 19.0;
            if ($taxRate < 0) {
                $taxRate = 19.0;
            }

            $priceNet = null;
            $priceGross = null;
            $paymentFee = null;
            $transactionType = KundeCreditTransaction::TYPE_ADMIN_TOPUP;

            if ($packageId > 0) {
                $package = $this->creditPackageRepository->find($packageId);
                if (null === $package) {
                    $this->addFlash('danger', 'Credit-Paket nicht gefunden.');
                    return $this->redirect($this->buildKundeCrudActionUrl($kundeId, 'topupCredits'));
                }

                $amount = $package->getCredits();
                $priceNet = (float) $package->getPrice();
                if ($priceNet <= 0) {
                    $this->addFlash('danger', 'Paketpreis fehlt oder ist 0. Bitte Preis im Paket hinterlegen.');
                    return $this->redirect($this->buildKundeCrudActionUrl($kundeId, 'topupCredits'));
                }

                $priceGross = $priceNet * (1 + ($taxRate / 100));
                if ('paypal' === $paymentMethod) {
                    $paymentFee = ($priceGross * 0.0179) + 0.35;
                }

                $transactionType = KundeCreditTransaction::TYPE_CUSTOMER_TOPUP;
                if ('' === $description) {
                    $description = sprintf('Paket: %s (%d Credits)', (string) $package, $package->getCredits());
                }
            }

            if ($amount <= 0) {
                $this->addFlash('danger', 'Bitte eine positive Credit-Anzahl eingeben oder ein Paket auswählen.');
            } else {
                $balanceBefore = $kunde->getCredits();
                $balanceAfter = $balanceBefore + $amount;

                $transaction = (new KundeCreditTransaction())
                    ->setKunde($kunde)
                    ->setAmount($amount)
                    ->setTransactionType($transactionType)
                    ->setDescription('' !== $description ? $description : null)
                    ->setPaymentMethod($packageId > 0 ? $paymentMethod : null)
                    ->setPriceNet(null !== $priceNet ? number_format($priceNet, 2, '.', '') : null)
                    ->setPriceGross(null !== $priceGross ? number_format($priceGross, 2, '.', '') : null)
                    ->setTaxRate($packageId > 0 ? number_format($taxRate, 2, '.', '') : null)
                    ->setPaymentFee(null !== $paymentFee ? number_format($paymentFee, 2, '.', '') : null)
                    ->setBalanceBefore($balanceBefore)
                    ->setBalanceAfter($balanceAfter);

                $user = $this->getUser();
                if ($user instanceof User) {
                    $transaction->setCreatedBy($user);
                }

                $kunde->setCredits($balanceAfter);

                $this->entityManager->persist($transaction);
                $this->entityManager->persist($kunde);
                $this->entityManager->flush();

                $this->addFlash('success', sprintf('%d Credits wurden fuer %s aufgeladen.', $amount, (string) $kunde));

                return $this->redirect($this->buildKundeCrudActionUrl($kundeId, 'creditsHistory'));
            }
        }

        $packages = $this->creditPackageRepository->findBy([], ['credits' => 'ASC']);

        return $this->render('admin/kunde/credits_topup.html.twig', [
            'kunde' => $kunde,
            'indexUrl' => $this->buildKundeIndexUrl(),
            'detailUrl' => $this->buildKundeDetailUrl($kunde),
            'historyUrl' => $this->buildKundeCrudActionUrl($kundeId, 'creditsHistory'),
            'packages' => $packages,
        ]);
    }

    #[AdminRoute(path: '/{entityId}/credits/history', name: 'credits_history', options: ['methods' => ['GET']])]
    public function creditsHistory(AdminContext $context): Response
    {
        $entity = $context->getEntity()?->getInstance();
        if (!$entity instanceof Kunde) {
            $this->addFlash('danger', 'Kunde nicht gefunden.');

            return $this->redirect($this->buildKundeIndexUrl());
        }

        $kunde = $entity;
        $kundeId = $kunde->getId();
        if (null === $kundeId) {
            $this->addFlash('danger', 'Kunde nicht gefunden.');

            return $this->redirect($this->buildKundeIndexUrl());
        }

        $transactions = $this->kundeCreditTransactionRepository->findByKunde($kunde, 250);
        $reversedTransactionIds = [];
        foreach ($transactions as $transaction) {
            $original = $transaction->getReversalOf();
            if ($original instanceof KundeCreditTransaction && null !== $original->getId()) {
                $reversedTransactionIds[$original->getId()] = true;
            }
        }

        return $this->render('admin/kunde/credits_history.html.twig', [
            'kunde' => $kunde,
            'transactions' => $transactions,
            'reversedTransactionIds' => $reversedTransactionIds,
            'indexUrl' => $this->buildKundeIndexUrl(),
            'detailUrl' => $this->buildKundeDetailUrl($kunde),
            'topupUrl' => $this->buildKundeCrudActionUrl($kundeId, 'topupCredits'),
        ]);
    }

    #[AdminRoute(path: '/{entityId}/credits/{transactionId}/cancel', name: 'credits_cancel', options: ['methods' => ['POST']])]
    public function cancelCredits(AdminContext $context, int $transactionId): Response
    {
        $entity = $context->getEntity()?->getInstance();
        if (!$entity instanceof Kunde) {
            $this->addFlash('danger', 'Kunde nicht gefunden.');

            return $this->redirect($this->buildKundeIndexUrl());
        }

        $kunde = $entity;
        $kundeId = $kunde->getId();
        if (null === $kundeId) {
            $this->addFlash('danger', 'Kunde nicht gefunden.');

            return $this->redirect($this->buildKundeIndexUrl());
        }

        $transaction = $this->entityManager->getRepository(KundeCreditTransaction::class)->find($transactionId);
        if (!$transaction instanceof KundeCreditTransaction || $transaction->getKunde()?->getId() !== $kundeId) {
            $this->addFlash('danger', 'Credit-Buchung nicht gefunden.');

            return $this->redirect($this->buildKundeCrudActionUrl($kundeId, 'creditsHistory'));
        }

        if ($transaction->getReversalOf() instanceof KundeCreditTransaction || $transaction->getTransactionType() === KundeCreditTransaction::TYPE_REVERSAL) {
            $this->addFlash('warning', 'Storno ist für diese Buchung nicht möglich.');

            return $this->redirect($this->buildKundeCrudActionUrl($kundeId, 'creditsHistory'));
        }

        if ($this->kundeCreditTransactionRepository->hasReversalFor($transaction)) {
            $this->addFlash('warning', 'Diese Buchung wurde bereits storniert.');

            return $this->redirect($this->buildKundeCrudActionUrl($kundeId, 'creditsHistory'));
        }

        $request = $context->getRequest();
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('kunde_credits_cancel_' . (string) $transactionId, $csrfToken)) {
            $this->addFlash('danger', 'Ungueltige Anfrage. Bitte erneut versuchen.');

            return $this->redirect($this->buildKundeCrudActionUrl($kundeId, 'creditsHistory'));
        }

        $amount = -$transaction->getAmount();
        $balanceBefore = $kunde->getCredits();
        $balanceAfter = $balanceBefore + $amount;

        if ($balanceAfter < 0) {
            $this->addFlash('danger', 'Storno nicht moeglich: Der Saldo wuerde negativ werden.');

            return $this->redirect($this->buildKundeCrudActionUrl($kundeId, 'creditsHistory'));
        }

        $reversal = (new KundeCreditTransaction())
            ->setKunde($kunde)
            ->setAmount($amount)
            ->setTransactionType(KundeCreditTransaction::TYPE_REVERSAL)
            ->setDescription(sprintf('Storno zu Buchung #%d', (int) $transactionId))
            ->setBalanceBefore($balanceBefore)
            ->setBalanceAfter($balanceAfter)
            ->setReversalOf($transaction);

        $user = $this->getUser();
        if ($user instanceof User) {
            $reversal->setCreatedBy($user);
        }

        $kunde->setCredits($balanceAfter);

        $this->entityManager->persist($reversal);
        $this->entityManager->persist($kunde);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('Buchung #%d wurde storniert.', (int) $transactionId));

        return $this->redirect($this->buildKundeCrudActionUrl($kundeId, 'creditsHistory'));
    }

    private function buildKundeIndexUrl(): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
    }

    private function buildKundeDetailUrl(Kunde $kunde): string
    {
        $kundeId = $kunde->getId();
        if (null === $kundeId) {
            return $this->buildKundeIndexUrl();
        }

        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId((string) $kundeId)
            ->generateUrl();
    }

    private function buildKundeCrudActionUrl(int $kundeId, string $action): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction($action)
            ->setEntityId((string) $kundeId)
            ->generateUrl();
    }

    private function hashKundenPasswort(object $entityInstance): void
    {
        if (!$entityInstance instanceof Kunde) {
            return;
        }

        $plainPasswort = $entityInstance->getPlainPasswort();
        if (null === $plainPasswort || '' === trim($plainPasswort)) {
            return;
        }

        $entityInstance->setPasswort(password_hash($plainPasswort, PASSWORD_BCRYPT));
        $entityInstance->setPasswordChangedAt(new \DateTimeImmutable());
        $entityInstance->eraseCredentials();
    }

    private function buildCompanyInitials(string $name): string
    {
        $clean = trim(preg_replace('/\\s+/', ' ', $name) ?? '');
        if ('' === $clean) {
            return '—';
        }

        $parts = explode(' ', $clean);
        $initials = '';
        foreach ($parts as $part) {
            $part = trim($part);
            if ('' === $part) {
                continue;
            }
            $initials .= mb_substr($part, 0, 1);
        }

        $initials = mb_strtoupper($initials);

        return '' !== $initials ? $initials : '—';
    }
}
