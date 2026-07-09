<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\KundeCreditTransaction;
use App\Portals\Chiptuningfile\Service\PortalPrepaymentSettlementService;
use Doctrine\DBAL\Connection;
use EasyCorp\Bundle\EasyAdminBundle\Config\ActionGroup;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[AdminCrud(routePath: '/credits/historie', routeName: 'admin_kunde_credit_transaction')]
final class KundeCreditTransactionCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly PortalPrepaymentSettlementService $prepaymentSettlementService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return KundeCreditTransaction::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Credit-Buchung')
            ->setEntityLabelInPlural('Credit-Historie')
            ->setDefaultRowAction(Action::DETAIL)
            ->overrideTemplate('crud/index', 'admin/credits/history_index.html.twig')
            ->setDefaultSort([
                'createdAt' => 'DESC',
                'id' => 'DESC',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        $markPrepaymentPaidAction = Action::new('markPrepaymentPaid', 'Als bezahlt markieren')
            ->setIcon('mdi:cash-check')
            ->linkToCrudAction('markPrepaymentPaid')
            ->displayIf(fn (KundeCreditTransaction $transaction): bool => $this->prepaymentSettlementService->isPendingPrepayment($transaction))
            ->setCssClass('btn btn-success');

        $indexActionGroup = ActionGroup::new('creditActions', 'Aktionen')
            ->addAction(Action::new(Action::DETAIL, 'Anzeigen', 'internal:detail')->linkToCrudAction(Action::DETAIL))
            ->addAction($markPrepaymentPaidAction);

        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $indexActionGroup)
            ->add(Crud::PAGE_DETAIL, $markPrepaymentPaidAction);
    }

    #[AdminRoute(path: '/{entityId}/mark-paid', name: 'mark_paid', options: ['methods' => ['GET', 'POST']])]
    public function markPrepaymentPaid(AdminContext $context): RedirectResponse
    {
        $entity = $context->getEntity()?->getInstance();
        if (!$entity instanceof KundeCreditTransaction) {
            $this->addFlash('danger', 'Vorkasse-Buchung konnte nicht geladen werden.');

            return $this->redirectToCreditsIndex();
        }

        try {
            $user = $this->getUser();
            $actor = $user instanceof User ? $user : null;
            $result = $this->prepaymentSettlementService->settlePendingPrepayment($entity, $actor);
            if (true === ($result['lexofficeSynced'] ?? false)) {
                $this->addFlash(
                    'success',
                    sprintf(
                        'Vorkasse als bezahlt markiert. %d Credits wurden gutgeschrieben und Lexoffice wurde aktualisiert.',
                        (int) $result['creditsAdded']
                    )
                );
            } else {
                $this->addFlash(
                    'warning',
                    sprintf(
                        'Vorkasse wurde lokal auf bezahlt gesetzt und %d Credits wurden gutgeschrieben. Lexoffice-Sync bitte pruefen (%s).',
                        (int) $result['creditsAdded'],
                        (string) (($result['lexofficeMessage'] ?? '') ?: 'keine Rueckmeldung')
                    )
                );
            }
        } catch (\Throwable $exception) {
            $this->addFlash('danger', 'Vorkasse konnte nicht als bezahlt verarbeitet werden: ' . $exception->getMessage());
        }

        return $this->redirectToCreditsIndex();
    }

    public function configureFields(string $pageName): iterable
    {
        $moneyFormatter = static function (mixed $value): string {
            if (null === $value || '' === (string) $value) {
                return '-';
            }

            return number_format((float) $value, 2, ',', '.') . ' €';
        };

        $percentFormatter = static function (mixed $value): string {
            if (null === $value || '' === (string) $value) {
                return '-';
            }

            return number_format((float) $value, 2, ',', '.') . ' %';
        };

        $amountField = IntegerField::new('amount', 'Betrag')
            ->formatValue(function (mixed $value, ?KundeCreditTransaction $transaction): string {
                $amount = (int) $value;

                if ($transaction instanceof KundeCreditTransaction && $this->prepaymentSettlementService->isPendingPrepayment($transaction)) {
                    $requested = $this->extractRequestedCreditsFromDescription((string) ($transaction->getDescription() ?? ''));
                    if ($requested > 0) {
                        return sprintf('+%d (angefragt)', $requested);
                    }
                }

                $prefix = $amount >= 0 ? '+' : '';

                return $prefix . $amount;
            });

        return [
            IdField::new('id')->onlyOnDetail(),
            DateTimeField::new('createdAt', 'Erstellt am')
                ->setFormat('dd.MM.yyyy HH:mm'),
            AssociationField::new('kunde', 'Kunde')->autocomplete(),
            $amountField,
            TextField::new('transactionType', 'Typ')
                ->formatValue(static function (mixed $value, ?KundeCreditTransaction $transaction): string {
                    $normalized = trim((string) $value);
                    $baseLabel = match ($normalized) {
                        KundeCreditTransaction::TYPE_ADMIN_TOPUP => 'Admin Aufladung',
                        KundeCreditTransaction::TYPE_CUSTOMER_TOPUP => 'Kunde Aufladung',
                        KundeCreditTransaction::TYPE_USAGE => 'Verbrauch',
                        KundeCreditTransaction::TYPE_MANUAL_ADJUSTMENT => 'Manuelle Anpassung',
                        KundeCreditTransaction::TYPE_REVERSAL => 'Storno',
                        default => '' !== $normalized ? $normalized : '-',
                    };

                    if (!$transaction instanceof KundeCreditTransaction) {
                        return $baseLabel;
                    }

                    $isPrepayment = 'vorkasse' === strtolower(trim((string) ($transaction->getPaymentMethod() ?? '')));
                    $description = strtolower((string) ($transaction->getDescription() ?? ''));
                    $isPending = $isPrepayment && (str_contains($description, 'status: pending') || str_contains($description, 'vorkasse anfrage'));

                    if ($isPending) {
                        return sprintf(
                            '%s <span class="badge text-bg-warning ms-1">Vorkasse offen</span>',
                            htmlspecialchars($baseLabel, ENT_QUOTES)
                        );
                    }

                    return $baseLabel;
                })
                ->renderAsHtml(),
            TextField::new('paymentMethod', 'Zahlart')->hideOnIndex(),
            IntegerField::new('balanceBefore', 'Saldo vor Buchung'),
            IntegerField::new('balanceAfter', 'Saldo nach Buchung'),
            TextField::new('priceNet', 'Preis netto')->formatValue($moneyFormatter)->hideOnIndex(),
            TextField::new('priceGross', 'Preis brutto')->formatValue($moneyFormatter)->hideOnIndex(),
            TextField::new('taxRate', 'MwSt %')->formatValue($percentFormatter)->hideOnIndex(),
            TextField::new('paymentFee', 'Gebuehr')->formatValue($moneyFormatter)->hideOnIndex(),
            TextareaField::new('description', 'Notiz')
                ->hideOnIndex()
                ->setRequired(false),
            AssociationField::new('createdBy', 'Erstellt von')
                ->setRequired(false),
        ];
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        $pageName = (string) $responseParameters->get('pageName');
        if (Crud::PAGE_INDEX !== $pageName) {
            return $responseParameters;
        }

        $responseParameters->set('kpi', $this->getKpiStats());

        return $responseParameters;
    }

    /**
     * @return array{total:int,creditsIn:int,creditsOut:int,net:int,topupToday:int,topupLast7Days:int,topupMonth:int}
     */
    private function getKpiStats(): array
    {
        $now = new \DateTimeImmutable();
        $todayStart = $now->setTime(0, 0, 0);
        $last7DaysStart = $todayStart->modify('-6 days');
        $monthStart = $todayStart->modify('first day of this month');

        $row = $this->connection->fetchAssociative(
            'SELECT
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) AS credits_in,
                COALESCE(SUM(CASE WHEN amount < 0 THEN -amount ELSE 0 END), 0) AS credits_out,
                COALESCE(SUM(amount), 0) AS net,
                COALESCE(SUM(CASE
                    WHEN amount > 0
                     AND transaction_type IN (:adminTopupType, :customerTopupType)
                     AND created_at >= :todayStart
                    THEN amount ELSE 0 END), 0) AS topup_today,
                COALESCE(SUM(CASE
                    WHEN amount > 0
                     AND transaction_type IN (:adminTopupType, :customerTopupType)
                     AND created_at >= :last7DaysStart
                    THEN amount ELSE 0 END), 0) AS topup_last_7_days,
                COALESCE(SUM(CASE
                    WHEN amount > 0
                     AND transaction_type IN (:adminTopupType, :customerTopupType)
                     AND created_at >= :monthStart
                    THEN amount ELSE 0 END), 0) AS topup_month
            FROM kunde_credit_transaction',
            [
                'adminTopupType' => KundeCreditTransaction::TYPE_ADMIN_TOPUP,
                'customerTopupType' => KundeCreditTransaction::TYPE_CUSTOMER_TOPUP,
                'todayStart' => $todayStart->format('Y-m-d H:i:s'),
                'last7DaysStart' => $last7DaysStart->format('Y-m-d H:i:s'),
                'monthStart' => $monthStart->format('Y-m-d H:i:s'),
            ]
        ) ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'creditsIn' => (int) ($row['credits_in'] ?? 0),
            'creditsOut' => (int) ($row['credits_out'] ?? 0),
            'net' => (int) ($row['net'] ?? 0),
            'topupToday' => (int) ($row['topup_today'] ?? 0),
            'topupLast7Days' => (int) ($row['topup_last_7_days'] ?? 0),
            'topupMonth' => (int) ($row['topup_month'] ?? 0),
        ];
    }

    private function redirectToCreditsIndex(): RedirectResponse
    {
        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    private function extractRequestedCreditsFromDescription(string $description): int
    {
        if (preg_match('/Vorkasse Anfrage:\s*(\d+)\s*Credits/i', $description, $matches) !== 1) {
            return 0;
        }

        return max(0, (int) ($matches[1] ?? 0));
    }
}
