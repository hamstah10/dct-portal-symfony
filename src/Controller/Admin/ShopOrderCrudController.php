<?php

namespace App\Controller\Admin;

use App\Entity\ShopOrder;
use App\Repository\ShopOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminCrud(routePath: '/ecommerce/bestellungen', routeName: 'admin_shop_order')]
final class ShopOrderCrudController extends AbstractCrudController
{
    use HandlesManualUidTrait;

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

    public function __construct(private readonly ShopOrderRepository $repository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ShopOrder::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Bestellung')
            ->setEntityLabelInPlural('Bestellungen')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->overrideTemplate('crud/index', 'admin/shop_order/index.html.twig')
            ->overrideTemplate('crud/detail', 'admin/shop_order/detail.html.twig')
            ->setDefaultRowAction(Action::DETAIL);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        $subtotal = MoneyField::new('subtotal', 'Zwischensumme')->setCurrency('EUR')->setStoredAsCents(false)->hideOnIndex();
        $taxAmount = MoneyField::new('taxAmount', 'Steuer')->setCurrency('EUR')->setStoredAsCents(false)->hideOnIndex();
        $total = MoneyField::new('total', 'Gesamt')->setCurrency('EUR')->setStoredAsCents(false);
        $discount = MoneyField::new('discount', 'Rabatt')->setCurrency('EUR')->setStoredAsCents(false)->hideOnIndex();
        $shippingCost = MoneyField::new('shippingCost', 'Versandkosten')->setCurrency('EUR')->setStoredAsCents(false)->hideOnIndex();
        $statusField = ChoiceField::new('status', 'Status')
            ->setChoices(array_flip(self::STATUS_LABELS))
            ->renderAsBadges(self::STATUS_BADGES);
        $paymentStatusField = IntegerField::new('paymentStatus', 'Zahlstatus')->hideOnIndex();
        $exportStatusField = IntegerField::new('exportStatus', 'Export')->hideOnIndex();

        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IntegerField::new('createdAt', 'Datum')
                    ->formatValue(fn ($value, ?ShopOrder $order): string => $this->formatTimestamp($order?->getCreatedAt()))
                    ->onlyOnIndex(),
                TextField::new('orderNumber', 'Bestellnummer'),
                TextField::new('itemsJson', 'Produkte')
                    ->onlyOnIndex()
                    ->formatValue(fn ($value, ?ShopOrder $order): string => $this->formatProductTitles($order)),
                TextField::new('customerName', 'Besteller')
                    ->formatValue(fn ($value, ?ShopOrder $order): string => $this->formatCustomer($order))
                    ->onlyOnIndex(),
                $statusField->onlyOnIndex(),
                $total,
                TextField::new('lexofficeInvoicePdf', 'Lexoffice')
                    ->onlyOnIndex()
                    ->renderAsHtml()
                    ->formatValue(fn ($value, ?ShopOrder $order): string => $this->formatLexofficeButton($order)),
            ];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                FormField::addFieldset('Bestellkopf'),
                IdField::new('uid', 'UID'),
                TextField::new('orderNumber', 'Bestellnummer'),
                $statusField,
                $paymentStatusField,
                $exportStatusField,
                $total,
                IntegerField::new('createdAt', 'Erstellt')
                    ->formatValue(fn ($value, ?ShopOrder $order): string => $this->formatTimestamp($order?->getCreatedAt())),
                TextField::new('transactionId', 'Transaktion'),
                FormField::addFieldset('Kunde & Abrechnung'),
                TextField::new('customerName', 'Name'),
                TextField::new('customerCompany', 'Firma'),
                TextField::new('customerEmail', 'E-Mail'),
                TextField::new('billingAddress', 'Rechnung Adresse'),
                TextField::new('billingStreet', 'Rechnung Strasse'),
                TextField::new('billingZip', 'Rechnung PLZ'),
                TextField::new('billingCity', 'Rechnung Stadt'),
                TextField::new('billingCountry', 'Rechnung Land'),
                FormField::addFieldset('Versand'),
                AssociationField::new('paymentMethod', 'Zahlungsart'),
                AssociationField::new('shippingMethod', 'Versand'),
                BooleanField::new('shippingSameAsBilling', 'Versand = Rechnung'),
                TextField::new('shippingAddress', 'Versand Adresse'),
                TextField::new('shippingStreet', 'Versand Strasse'),
                TextField::new('shippingZip', 'Versand PLZ'),
                TextField::new('shippingCity', 'Versand Stadt'),
                TextField::new('shippingCountry', 'Versand Land'),
                TextField::new('shippingFirstName', 'Versand Vorname'),
                TextField::new('shippingLastName', 'Versand Nachname'),
                TextField::new('shippingCompany', 'Versand Firma'),
                FormField::addFieldset('Werte & Metadaten'),
                $subtotal,
                $taxAmount,
                $discount,
                $shippingCost,
                TextareaField::new('itemsJson', 'Positionen JSON'),
                TextareaField::new('comment', 'Kommentar'),
                TextareaField::new('notes', 'Notizen'),
                TextField::new('lexofficeInvoiceId', 'Lexoffice ID'),
                TextField::new('lexofficeInvoicePdf', 'Lexoffice PDF'),
                BooleanField::new('hidden', 'Versteckt'),
                BooleanField::new('deleted', 'Geloescht'),
                IntegerField::new('frontendUserId', 'Frontend User'),
                IntegerField::new('pid'),
                IntegerField::new('tstamp'),
                IntegerField::new('crdate'),
            ];
        }

        return [
            FormField::addFieldset('Bestellung'),
            TextField::new('orderNumber', 'Bestellnummer'),
            $statusField->hideOnIndex(),
            $paymentStatusField,
            $exportStatusField,
            AssociationField::new('paymentMethod', 'Zahlungsart')->autocomplete(),
            AssociationField::new('shippingMethod', 'Versand')->autocomplete(),
            FormField::addFieldset('Summen'),
            $total,
            $subtotal,
            $taxAmount,
            $discount,
            $shippingCost,
            FormField::addFieldset('Kunde'),
            IntegerField::new('createdAt', 'Erstellt')
                ->hideOnIndex()
                ->formatValue(fn ($value, ?ShopOrder $order): string => $this->formatTimestamp($order?->getCreatedAt())),
            TextField::new('transactionId', 'Transaktion')->hideOnIndex(),
            TextField::new('customerFirstName', 'Vorname')->hideOnIndex(),
            TextField::new('customerLastName', 'Nachname')->hideOnIndex(),
            TextField::new('customerName', 'Name')->hideOnIndex(),
            TextField::new('customerCompany', 'Firma')->hideOnIndex(),
            TextField::new('customerEmail', 'E-Mail')->hideOnIndex(),
            TextField::new('billingAddress', 'Rechnung Adresse')->hideOnIndex(),
            TextField::new('billingStreet', 'Rechnung Strasse')->hideOnIndex(),
            TextField::new('billingCity', 'Rechnung Stadt')->hideOnIndex(),
            TextField::new('billingZip', 'Rechnung PLZ')->hideOnIndex(),
            TextField::new('billingCountry', 'Rechnung Land')->hideOnIndex(),
            TextField::new('shippingAddress', 'Versand Adresse')->hideOnIndex(),
            TextField::new('shippingStreet', 'Versand Strasse')->hideOnIndex(),
            TextField::new('shippingCity', 'Versand Stadt')->hideOnIndex(),
            TextField::new('shippingZip', 'Versand PLZ')->hideOnIndex(),
            TextField::new('shippingCountry', 'Versand Land')->hideOnIndex(),
            TextField::new('shippingFirstName', 'Versand Vorname')->hideOnIndex(),
            TextField::new('shippingLastName', 'Versand Nachname')->hideOnIndex(),
            TextField::new('shippingCompany', 'Versand Firma')->hideOnIndex(),
            BooleanField::new('shippingSameAsBilling', 'Versand = Rechnung')->hideOnIndex(),
            FormField::addFieldset('Interne Daten'),
            IntegerField::new('frontendUserId', 'Frontend User')->hideOnIndex(),
            TextareaField::new('itemsJson', 'Positionen JSON')->hideOnIndex(),
            TextareaField::new('comment', 'Kommentar')->hideOnIndex(),
            TextareaField::new('notes', 'Notizen')->hideOnIndex(),
            TextField::new('lexofficeInvoiceId', 'Lexoffice ID')->hideOnIndex(),
            TextField::new('lexofficeInvoicePdf', 'Lexoffice PDF')->hideOnIndex(),
            BooleanField::new('hidden', 'Versteckt')->hideOnIndex(),
            BooleanField::new('deleted', 'Geloescht')->hideOnIndex(),
            IntegerField::new('pid')->onlyOnDetail(),
            IntegerField::new('tstamp')->onlyOnDetail(),
            IntegerField::new('crdate')->onlyOnDetail(),
        ];
    }

    private function formatTimestamp(?int $timestamp): string
    {
        if (!$timestamp || $timestamp <= 0) {
            return '-';
        }

        $date = (new \DateTimeImmutable())->setTimestamp($timestamp);

        return $date->format('d.m.Y H:i');
    }

    private function formatCustomer(?ShopOrder $order): string
    {
        if (!$order instanceof ShopOrder) {
            return '-';
        }

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

        return '' !== $name ? $name : '-';
    }

    private function formatProductTitles(?ShopOrder $order): string
    {
        if (!$order instanceof ShopOrder) {
            return '-';
        }

        $itemsJson = $order->getItemsJson();
        if (null === $itemsJson || '' === trim($itemsJson)) {
            return '-';
        }

        $items = json_decode($itemsJson, true);
        if (!is_array($items)) {
            return '-';
        }

        $titles = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = $this->extractItemTitle($item);
            if (null === $title || '' === $title) {
                continue;
            }

            $titles[] = $title;
        }

        $titles = array_values(array_unique($titles));
        if ([] === $titles) {
            return '-';
        }

        return implode(', ', $titles);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function extractItemTitle(array $item): ?string
    {
        $title = $item['title'] ?? $item['name'] ?? $item['productTitle'] ?? $item['productName'] ?? $item['label'] ?? null;
        if (is_string($title) && '' !== trim($title)) {
            return trim($title);
        }

        $product = $item['product'] ?? null;
        if (is_array($product)) {
            $title = $product['title'] ?? $product['name'] ?? null;
            if (is_string($title) && '' !== trim($title)) {
                return trim($title);
            }
        }

        return null;
    }

    private function formatLexofficeButton(?ShopOrder $order): string
    {
        if (!$order instanceof ShopOrder) {
            return '<span class="text-muted">-</span>';
        }

        $invoicePdf = trim((string) ($order->getLexofficeInvoicePdf() ?? ''));
        $invoiceId = trim((string) ($order->getLexofficeInvoiceId() ?? ''));

        if ('' === $invoicePdf && '' === $invoiceId) {
            return '<span class="text-muted">-</span>';
        }

        if ('' !== $invoicePdf) {
            $url = $invoicePdf;
            if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                $url = '/' . ltrim($url, '/');
            }

            // Replace the icon with a static SVG or icon HTML as needed
            $iconHtml = '<svg width="16" height="16" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16"><path d="M5.5 6.5A.5.5 0 0 1 6 7v2a.5.5 0 0 1-1 0V7a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v2a.5.5 0 0 0 1 0V7zm2 .5a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0V8h-.5a.5.5 0 0 1 0-1h1z"/><path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h6.5L14 4.5zm-3.5-2.5v3a1 1 0 0 0 1 1h3l-4-4z"/></svg>';
            return sprintf(
                '<a class="" style="color: #111931 !important" href="%s" target="_blank" rel="noopener">%s Rechnung</a>',
                htmlspecialchars($url, ENT_QUOTES),
                $iconHtml
            );
        }

        return sprintf(
            '<span class="badge bg-secondary" title="Lexoffice ID %s">Lexoffice</span>',
            htmlspecialchars($invoiceId, ENT_QUOTES)
        );
    }


    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopOrder) {
            $this->applyCreateDefaults($entityInstance, $this->repository->getNextUid());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof ShopOrder) {
            $this->applyUpdateDefaults($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
