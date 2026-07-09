import { useMemo } from 'react';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { getInvoicesData } from '../lib/invoicesBootstrap';
import {
  ArrowLeft,
  Download,
  Receipt,
  CalendarBlank,
  CurrencyCircleDollar,
  CheckCircle,
  Clock,
  XCircle,
} from '@phosphor-icons/react';
import { useParams } from 'react-router-dom';

const pageTranslations = {
  en: {
    title: 'Invoice Document',
    notFound: 'Invoice not found',
    back: 'Back to invoices',
    invoiceNumber: 'Invoice Number',
    invoiceDate: 'Invoice Date',
    paymentMethod: 'Payment Method',
    status: 'Status',
    billingSummary: 'Billing Summary',
    position: 'Item',
    quantity: 'Qty',
    unitPrice: 'Unit Price',
    net: 'Net',
    tax: 'VAT (19%)',
    gross: 'Total',
    credits: 'Credits',
    download: 'Download PDF',
    paid: 'Paid',
    pending: 'Pending',
    cancelled: 'Cancelled',
    issuer: 'Issuer',
    customer: 'Customer',
  },
  de: {
    title: 'Rechnungsdokument',
    notFound: 'Rechnung nicht gefunden',
    back: 'Zurück zu Rechnungen',
    invoiceNumber: 'Rechnungsnummer',
    invoiceDate: 'Rechnungsdatum',
    paymentMethod: 'Zahlungsmethode',
    status: 'Status',
    billingSummary: 'Abrechnungsübersicht',
    position: 'Position',
    quantity: 'Menge',
    unitPrice: 'Einzelpreis',
    net: 'Netto',
    tax: 'MwSt. (19%)',
    gross: 'Gesamt',
    credits: 'Credits',
    download: 'PDF herunterladen',
    paid: 'Bezahlt',
    pending: 'Ausstehend',
    cancelled: 'Storniert',
    issuer: 'Aussteller',
    customer: 'Kunde',
  },
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(Number(amount || 0));
};

const formatDate = (value, language) => {
  const parsed = new Date(String(value || ''));
  if (Number.isNaN(parsed.getTime())) {
    return '-';
  }

  return new Intl.DateTimeFormat(language === 'de' ? 'de-DE' : 'en-US', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(parsed);
};

const getStatusBadge = (status, t) => {
  const normalized = String(status || '').toLowerCase();
  if (normalized === 'paid') {
    return (
      <Badge className="bg-success-soft text-success-theme border border-success-soft">
        <CheckCircle weight="fill" className="w-3 h-3 mr-1" />
        {t('paid')}
      </Badge>
    );
  }

  if (normalized === 'cancelled') {
    return (
      <Badge className="bg-danger-soft text-danger-theme border border-danger-soft">
        <XCircle weight="fill" className="w-3 h-3 mr-1" />
        {t('cancelled')}
      </Badge>
    );
  }

  return (
    <Badge className="bg-warning-soft text-warning-theme border border-warning-soft">
      <Clock weight="fill" className="w-3 h-3 mr-1" />
      {t('pending')}
    </Badge>
  );
};

export default function InvoiceDetail() {
  const { language } = useLanguage();
  const t = (key) => pageTranslations[language]?.[key] || key;
  const { id } = useParams();
  const invoices = useMemo(() => getInvoicesData(), []);
  const invoice = useMemo(
    () => invoices.find((item) => String(item.id) === String(id || '')) || null,
    [id, invoices]
  );

  if (!invoice) {
    return (
      <DashboardLayout>
        <div className="max-w-3xl mx-auto py-14">
          <Card className="bg-card border-border">
            <CardContent className="p-8 text-center space-y-4">
              <Receipt weight="fill" className="w-12 h-12 text-muted-foreground mx-auto" />
              <p className="text-lg font-semibold text-foreground">{t('notFound')}</p>
              <Button variant="outline" className="border-border" onClick={() => { window.location.href = '/invoices'; }}>
                <ArrowLeft weight="bold" className="w-4 h-4 mr-2" />
                {t('back')}
              </Button>
            </CardContent>
          </Card>
        </div>
      </DashboardLayout>
    );
  }

  const unitPrice = invoice.credits > 0 ? Number(invoice.amount || 0) / Number(invoice.credits || 1) : 0;
  const issuer = invoice.issuer && typeof invoice.issuer === 'object' ? invoice.issuer : {};
  const customer = invoice.customer && typeof invoice.customer === 'object' ? invoice.customer : {};

  return (
    <DashboardLayout title={t('title')}>
      <div className="space-y-6 max-w-[1200px] mx-auto">
        <div className="flex items-center justify-between">
          <Button variant="outline" className="border-border" onClick={() => { window.location.href = '/invoices'; }}>
            <ArrowLeft weight="bold" className="w-4 h-4 mr-2" />
            {t('back')}
          </Button>
          {invoice.status === 'paid' && (
            <Button
              className="btn-gradient font-semibold"
              onClick={() => {
                if (invoice.downloadUrl) {
                  window.location.href = invoice.downloadUrl;
                }
              }}
              disabled={!invoice.downloadUrl}
            >
              <Download weight="regular" className="w-4 h-4 mr-2" />
              {t('download')}
            </Button>
          )}
        </div>

        <div className="rounded-md border border-neutral-300 bg-white text-neutral-900 shadow-[0_16px_40px_rgba(0,0,0,0.35)]" data-testid="invoice-document">
          <div className="px-8 py-7 border-b border-neutral-200">
            <div className="flex items-start justify-between gap-6">
              <div>
                <p className="text-[11px] uppercase tracking-[0.2em] text-neutral-500">{t('issuer')}</p>
                <h1 className="text-2xl font-black tracking-tight mt-2">{String(issuer.name || 'Tuningfux')}</h1>
                <p className="text-sm text-neutral-600 mt-1">
                  {[issuer.addressLine1, issuer.addressLine2, issuer.country].filter(Boolean).join(' · ') || '-'}
                </p>
                <p className="text-xs text-neutral-500 mt-2">
                  {[issuer.email, issuer.phone, issuer.website].filter(Boolean).join(' · ')}
                </p>
              </div>
              <div className="text-right">
                <p className="text-[11px] uppercase tracking-[0.2em] text-neutral-500">{t('invoiceNumber')}</p>
                <p className="text-xl font-bold font-mono mt-2">{invoice.id}</p>
                <div className="mt-3 inline-flex">{getStatusBadge(invoice.status, t)}</div>
              </div>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 px-8 py-6 border-b border-neutral-200">
            <div>
              <p className="text-[11px] uppercase tracking-[0.2em] text-neutral-500">{t('customer')}</p>
              <p className="text-sm font-semibold mt-2">{String(customer.name || customer.contactName || 'Portal Kunde')}</p>
              <p className="text-sm text-neutral-600">
                {[customer.addressLine1, customer.addressLine2, customer.country].filter(Boolean).join(' · ') || '-'}
              </p>
              <p className="text-xs text-neutral-500 mt-2">
                {[customer.email, customer.phone].filter(Boolean).join(' · ')}
              </p>
            </div>
            <div className="space-y-2">
              <div className="flex items-center justify-between text-sm">
                <span className="text-neutral-500">{t('invoiceDate')}</span>
                <span className="font-semibold inline-flex items-center gap-1.5"><CalendarBlank className="w-4 h-4" />{formatDate(invoice.date, language)}</span>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span className="text-neutral-500">{t('paymentMethod')}</span>
                <span className="font-semibold">{invoice.paymentMethod || '-'}</span>
              </div>
            </div>
          </div>

          <div className="px-8 py-6">
            <p className="text-[11px] uppercase tracking-[0.2em] text-neutral-500 mb-3">{t('billingSummary')}</p>
            <div className="overflow-hidden rounded-md border border-neutral-200">
              <table className="w-full">
                <thead className="bg-neutral-100 text-neutral-600 text-xs uppercase tracking-wide">
                  <tr>
                    <th className="px-4 py-3 text-left">{t('position')}</th>
                    <th className="px-4 py-3 text-center">{t('quantity')}</th>
                    <th className="px-4 py-3 text-right">{t('unitPrice')}</th>
                    <th className="px-4 py-3 text-right">{t('net')}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr className="border-t border-neutral-200">
                    <td className="px-4 py-3 text-sm font-semibold">{invoice.credits} {t('credits')}</td>
                    <td className="px-4 py-3 text-sm text-center">1</td>
                    <td className="px-4 py-3 text-sm text-right">{formatCurrency(unitPrice)}</td>
                    <td className="px-4 py-3 text-sm text-right font-semibold">{formatCurrency(invoice.amount)}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div className="mt-5 ml-auto w-full max-w-sm rounded-md border border-neutral-200 bg-neutral-50 p-4">
              <div className="flex items-center justify-between text-sm py-1">
                <span className="text-neutral-500">{t('net')}</span>
                <span className="font-semibold">{formatCurrency(invoice.amount)}</span>
              </div>
              <div className="flex items-center justify-between text-sm py-1">
                <span className="text-neutral-500">{t('tax')}</span>
                <span className="font-semibold">{formatCurrency(invoice.tax)}</span>
              </div>
              <div className="flex items-center justify-between text-base py-2 mt-2 border-t border-neutral-200">
                <span className="font-bold">{t('gross')}</span>
                <span className="font-black text-lg inline-flex items-center gap-1.5"><CurrencyCircleDollar className="w-5 h-5" />{formatCurrency(invoice.total)}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}
