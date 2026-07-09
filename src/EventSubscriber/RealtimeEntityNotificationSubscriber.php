<?php

namespace App\EventSubscriber;

use App\Entity\Auftrag;
use App\Entity\Kunde;
use App\Entity\KundeCreditTransaction;
use App\Entity\ShopOrder;
use App\Entity\SupportTicket;
use App\Service\Realtime\PortalMercureTopics;
use App\Service\Realtime\PortalRealtimePublisher;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist)]
final class RealtimeEntityNotificationSubscriber
{
    public function __construct(
        private readonly PortalRealtimePublisher $portalRealtimePublisher,
        private readonly PortalMercureTopics $portalMercureTopics,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Auftrag) {
            $this->portalRealtimePublisher->publish(
                $this->portalMercureTopics->adminNotifications(),
                sprintf('Neuer Auftrag %s', $entity->getOrderNumber()),
                [
                    'body' => $this->buildAuftragBody($entity),
                    'tag' => sprintf('order-%d', (int) ($entity->getId() ?? 0)),
                ],
            );

            return;
        }

        if ($entity instanceof SupportTicket) {
            $this->portalRealtimePublisher->publish(
                $this->portalMercureTopics->adminNotifications(),
                sprintf('Neues Ticket: %s', $entity->getSubject()),
                [
                    'body' => $this->buildTicketBody($entity),
                    'tag' => sprintf('support-ticket-%d', (int) ($entity->getId() ?? 0)),
                ],
            );

            return;
        }

        if ($entity instanceof Kunde) {
            $this->portalRealtimePublisher->publish(
                $this->portalMercureTopics->adminNotifications(),
                sprintf('Neuer Kunde %s', trim((string) $entity) ?: sprintf('#%d', (int) ($entity->getId() ?? 0))),
                [
                    'body' => $this->buildCustomerBody($entity),
                    'tag' => sprintf('customer-%d', (int) ($entity->getId() ?? 0)),
                ],
            );

            return;
        }

        if ($entity instanceof KundeCreditTransaction && $this->shouldNotifyCreditTopup($entity)) {
            $this->portalRealtimePublisher->publish(
                $this->portalMercureTopics->adminNotifications(),
                sprintf('Credits aufgeladen: +%d', $entity->getAmount()),
                [
                    'body' => $this->buildCreditTopupBody($entity),
                    'tag' => sprintf('credit-topup-%d', (int) ($entity->getId() ?? 0)),
                ],
            );

            return;
        }

        if ($entity instanceof ShopOrder) {
            $this->portalRealtimePublisher->publish(
                $this->portalMercureTopics->adminNotifications(),
                sprintf('Neue Bestellung %s', trim((string) $entity) ?: sprintf('#%d', $entity->getUid())),
                [
                    'body' => $this->buildShopOrderBody($entity),
                    'tag' => sprintf('shop-order-%d', $entity->getUid()),
                ],
            );
        }
    }

    private function buildAuftragBody(Auftrag $auftrag): string
    {
        $bodyParts = [];

        $vehicleDisplay = trim((string) $auftrag->getVehicleDisplay());
        if ('' !== $vehicleDisplay) {
            $bodyParts[] = $vehicleDisplay;
        }

        $stage = trim((string) $auftrag->getStageLabel());
        if ('' !== $stage) {
            $bodyParts[] = $stage;
        }

        $kundeLabel = trim((string) ($auftrag->getKunde() ?? ''));
        if ('' !== $kundeLabel) {
            $bodyParts[] = $kundeLabel;
        }

        return [] !== $bodyParts ? implode(' | ', $bodyParts) : 'Ein neuer Auftrag ist eingegangen.';
    }

    private function buildTicketBody(SupportTicket $ticket): string
    {
        $bodyParts = [];

        $firmaLabel = trim((string) ($ticket->getFirma()?->getName() ?? ''));
        if ('' !== $firmaLabel) {
            $bodyParts[] = $firmaLabel;
        }

        $kundeLabel = trim((string) ($ticket->getKunde() ?? ''));
        if ('' !== $kundeLabel) {
            $bodyParts[] = $kundeLabel;
        }

        $priority = trim((string) $ticket->getPriority());
        if ('' !== $priority) {
            $bodyParts[] = sprintf('Prioritaet %s', $priority);
        }

        return [] !== $bodyParts ? implode(' | ', $bodyParts) : 'Ein neues Support-Ticket wurde erstellt.';
    }

    private function buildCustomerBody(Kunde $customer): string
    {
        $bodyParts = [];

        $company = trim((string) ($customer->getFirmenname() ?? ''));
        if ('' !== $company) {
            $bodyParts[] = $company;
        } elseif (null !== $customer->getFirma()) {
            $firmaLabel = trim((string) $customer->getFirma());
            if ('' !== $firmaLabel) {
                $bodyParts[] = $firmaLabel;
            }
        }

        $email = trim((string) ($customer->getEmail() ?? ''));
        if ('' !== $email) {
            $bodyParts[] = $email;
        }

        $kundennummer = trim((string) ($customer->getKundennummer() ?? ''));
        if ('' !== $kundennummer) {
            $bodyParts[] = sprintf('Nr. %s', $kundennummer);
        }

        return [] !== $bodyParts ? implode(' | ', $bodyParts) : 'Ein neuer Kunde wurde angelegt.';
    }

    private function shouldNotifyCreditTopup(KundeCreditTransaction $transaction): bool
    {
        if ($transaction->getAmount() <= 0) {
            return false;
        }

        return in_array(
            $transaction->getTransactionType(),
            [
                KundeCreditTransaction::TYPE_ADMIN_TOPUP,
                KundeCreditTransaction::TYPE_CUSTOMER_TOPUP,
            ],
            true,
        );
    }

    private function buildCreditTopupBody(KundeCreditTransaction $transaction): string
    {
        $bodyParts = [];

        $kundeLabel = trim((string) ($transaction->getKunde() ?? ''));
        if ('' !== $kundeLabel) {
            $bodyParts[] = $kundeLabel;
        }

        $description = trim((string) ($transaction->getDescription() ?? ''));
        if ('' !== $description) {
            $bodyParts[] = $description;
        }

        $paymentMethod = trim((string) ($transaction->getPaymentMethod() ?? ''));
        if ('' !== $paymentMethod) {
            $bodyParts[] = sprintf('Zahlart %s', strtoupper($paymentMethod));
        }

        return [] !== $bodyParts ? implode(' | ', $bodyParts) : 'Credits wurden aufgeladen.';
    }

    private function buildShopOrderBody(ShopOrder $order): string
    {
        $bodyParts = [];

        $customer = trim((string) ($order->getCustomerName() ?? ''));
        if ('' === $customer) {
            $customer = trim(sprintf(
                '%s %s',
                (string) ($order->getCustomerFirstName() ?? ''),
                (string) ($order->getCustomerLastName() ?? '')
            ));
        }
        if ('' !== $customer) {
            $bodyParts[] = $customer;
        }

        $email = trim((string) ($order->getCustomerEmail() ?? ''));
        if ('' !== $email) {
            $bodyParts[] = $email;
        }

        $total = trim((string) $order->getTotal());
        if ('' !== $total) {
            $bodyParts[] = sprintf('%s EUR', $total);
        }

        return [] !== $bodyParts ? implode(' | ', $bodyParts) : 'Eine neue eCommerce-Bestellung wurde angelegt.';
    }
}
