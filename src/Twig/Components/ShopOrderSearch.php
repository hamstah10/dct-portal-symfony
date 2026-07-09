<?php

namespace App\Twig\Components;

use App\Controller\Admin\ShopOrderCrudController;
use App\Entity\ShopOrder;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('shop_order_search', template: 'components/shop_order_search.html.twig')]
final class ShopOrderSearch
{
    use DefaultActionTrait;

    private const STATUS_LABELS = [
        0 => 'Neu',
        1 => 'Offen',
        2 => 'Bezahlt',
        3 => 'In Bearbeitung',
        4 => 'Versandt',
        5 => 'Abgeschlossen',
        6 => 'Storniert',
    ];

    private const STATUS_BADGES = [
        0 => 'secondary',
        1 => 'info',
        2 => 'success',
        3 => 'warning',
        4 => 'primary',
        5 => 'success',
        6 => 'danger',
    ];

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public int $limit = 40;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    /**
     * @return array<int, array{
     *   url:string,
     *   label:string,
     *   subLabel:?string,
     *   statusLabel:string,
     *   statusTone:string,
     *   createdAt:?string
     * }>
     */
    public function getResults(): array
    {
        $query = trim($this->query);
        $length = function_exists('mb_strlen') ? mb_strlen($query) : strlen($query);
        if ($length < 2) {
            return [];
        }

        $normalized = function_exists('mb_strtolower') ? mb_strtolower($query) : strtolower($query);
        $term = '%' . $normalized . '%';

        $items = $this->entityManager->createQueryBuilder()
            ->select('o')
            ->from(ShopOrder::class, 'o')
            ->andWhere('o.deleted = :deleted')
            ->andWhere('o.hidden = :hidden')
            ->andWhere(
                'LOWER(COALESCE(o.orderNumber, \'\')) LIKE :term
                 OR LOWER(COALESCE(o.customerName, \'\')) LIKE :term
                 OR LOWER(COALESCE(o.customerFirstName, \'\')) LIKE :term
                 OR LOWER(COALESCE(o.customerLastName, \'\')) LIKE :term
                 OR LOWER(COALESCE(o.customerCompany, \'\')) LIKE :term
                 OR LOWER(COALESCE(o.customerEmail, \'\')) LIKE :term
                 OR LOWER(COALESCE(o.transactionId, \'\')) LIKE :term'
            )
            ->setParameter('term', $term)
            ->setParameter('deleted', false)
            ->setParameter('hidden', false)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(max(1, $this->limit))
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($items as $order) {
            if (!$order instanceof ShopOrder) {
                continue;
            }

            $statusLabel = self::STATUS_LABELS[$order->getStatus()] ?? ('Status ' . $order->getStatus());
            $statusTone = self::STATUS_BADGES[$order->getStatus()] ?? 'secondary';

            $results[] = [
                'url' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(ShopOrderCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($order->getUid())
                    ->generateUrl(),
                'label' => $this->buildLabel($order),
                'subLabel' => $this->buildSubLabel($order),
                'statusLabel' => $statusLabel,
                'statusTone' => $statusTone,
                'createdAt' => $this->formatTimestamp($order->getCreatedAt()),
            ];
        }

        return $results;
    }

    private function buildLabel(ShopOrder $order): string
    {
        $number = trim((string) ($order->getOrderNumber() ?? ''));
        if ('' !== $number) {
            return $number;
        }

        return 'Bestellung #' . $order->getUid();
    }

    private function buildSubLabel(ShopOrder $order): ?string
    {
        $name = trim((string) ($order->getCustomerName() ?? ''));
        if ('' === $name) {
            $name = trim(sprintf(
                '%s %s',
                (string) ($order->getCustomerFirstName() ?? ''),
                (string) ($order->getCustomerLastName() ?? '')
            ));
        }

        $company = trim((string) ($order->getCustomerCompany() ?? ''));

        if ('' !== $name && '' !== $company) {
            return sprintf('%s · %s', $name, $company);
        }

        if ('' !== $company) {
            return $company;
        }

        return '' !== $name ? $name : null;
    }

    private function formatTimestamp(?int $timestamp): ?string
    {
        if (!$timestamp || $timestamp <= 0) {
            return null;
        }

        $date = (new \DateTimeImmutable())->setTimestamp($timestamp);

        return $date->format('d.m.Y H:i');
    }
}
