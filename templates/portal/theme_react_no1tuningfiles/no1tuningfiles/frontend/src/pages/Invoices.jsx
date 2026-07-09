import { useEffect, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { Input } from '../components/ui/input';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '../components/ui/table';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '../components/ui/select';
import {
  Receipt,
  Download,
  Eye,
  CurrencyCircleDollar,
  CalendarBlank,
  FunnelSimple,
  FileText,
  CheckCircle,
  Clock,
  XCircle,
} from '@phosphor-icons/react';
import { getInvoicesData } from '../lib/invoicesBootstrap';

const pageTranslations = {
  en: {
    pageTitle: 'Invoices & Purchases',
    totalPurchases: 'Total Purchases',
    totalCredits: 'Credits Purchased',
    totalSpent: 'Total Spent',
    pendingInvoices: 'Pending',
    invoiceList: 'Invoice History',
    invoiceNumber: 'Invoice #',
    date: 'Date',
    credits: 'Credits',
    netAmount: 'Net',
    tax: 'VAT (19%)',
    total: 'Total',
    status: 'Status',
    payment: 'Payment',
    actions: 'Actions',
    paid: 'Paid',
    pending: 'Pending',
    cancelled: 'Cancelled',
    download: 'Download PDF',
    filterByYear: 'Filter by Year',
    allYears: 'All Years',
    filterByStatus: 'Filter by Status',
    searchPlaceholder: 'Search invoice, payment or amount...',
    allStatus: 'All Status',
    noInvoices: 'No invoices found',
  },
  de: {
    pageTitle: 'Rechnungen & Käufe',
    totalPurchases: 'Gesamte Käufe',
    totalCredits: 'Gekaufte Credits',
    totalSpent: 'Gesamtausgaben',
    pendingInvoices: 'Ausstehend',
    invoiceList: 'Rechnungsverlauf',
    invoiceNumber: 'Rechnung #',
    date: 'Datum',
    credits: 'Credits',
    netAmount: 'Netto',
    tax: 'MwSt. (19%)',
    total: 'Gesamt',
    status: 'Status',
    payment: 'Zahlung',
    actions: 'Aktionen',
    paid: 'Bezahlt',
    pending: 'Ausstehend',
    cancelled: 'Storniert',
    download: 'PDF herunterladen',
    filterByYear: 'Nach Jahr filtern',
    allYears: 'Alle Jahre',
    filterByStatus: 'Nach Status filtern',
    searchPlaceholder: 'Rechnung, Zahlung oder Betrag suchen...',
    allStatus: 'Alle Status',
    noInvoices: 'Keine Rechnungen gefunden',
  },
};

export default function Invoices() {
  const { language } = useLanguage();
  const t = (key) => pageTranslations[language][key] || key;
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const invoices = getInvoicesData();

  const [yearFilter, setYearFilter] = useState(() => searchParams.get('year') || 'all');
  const [statusFilter, setStatusFilter] = useState(() => searchParams.get('status') || 'all');
  const [searchQuery, setSearchQuery] = useState(() => searchParams.get('search') || '');

  useEffect(() => {
    setYearFilter(searchParams.get('year') || 'all');
    setStatusFilter(searchParams.get('status') || 'all');
    setSearchQuery(searchParams.get('search') || '');
  }, [searchParams]);

  const updateFilters = (nextSearch, nextYear, nextStatus) => {
    setSearchQuery(nextSearch);
    setYearFilter(nextYear);
    setStatusFilter(nextStatus);

    const nextParams = new URLSearchParams();
    if (nextSearch.trim()) {
      nextParams.set('search', nextSearch.trim());
    }
    if (nextYear !== 'all') {
      nextParams.set('year', nextYear);
    }
    if (nextStatus !== 'all') {
      nextParams.set('status', nextStatus);
    }

    setSearchParams(nextParams, { replace: true });
  };

  const paidInvoices = invoices.filter((inv) => inv.status === 'paid');
  const totalCredits = paidInvoices.reduce((sum, inv) => sum + inv.credits, 0);
  const totalSpent = paidInvoices.reduce((sum, inv) => sum + inv.total, 0);
  const pendingCount = invoices.filter((inv) => inv.status === 'pending').length;

  const filteredInvoices = invoices.filter((inv) => {
    const normalizedSearch = searchQuery.trim().toLowerCase();
    const dateYear = typeof inv.date === 'string' && inv.date.length >= 4 ? inv.date.slice(0, 4) : '';
    const yearMatch = yearFilter === 'all' || dateYear === yearFilter;
    const statusMatch = statusFilter === 'all' || inv.status === statusFilter;
    const searchMatch = normalizedSearch === '' || [
      inv.id,
      inv.paymentMethod,
      inv.status,
      inv.date,
      String(inv.credits),
      inv.amount.toFixed(2),
      inv.tax.toFixed(2),
      inv.total.toFixed(2),
      inv.customer?.name,
      inv.customer?.company,
      inv.customer?.email,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
      .includes(normalizedSearch);
    return yearMatch && statusMatch && searchMatch;
  });

  const yearOptions = Array.from(new Set(
    invoices
      .map((invoice) => (typeof invoice.date === 'string' && invoice.date.length >= 4 ? invoice.date.slice(0, 4) : null))
      .filter((year) => typeof year === 'string' && year !== '')
  )).sort((a, b) => b.localeCompare(a));

  const getStatusBadge = (status) => {
    const styles = {
      paid: { bg: 'bg-green-500/20', text: 'text-green-400', border: 'border-green-500/30', icon: CheckCircle },
      pending: { bg: 'bg-yellow-500/20', text: 'text-yellow-400', border: 'border-yellow-500/30', icon: Clock },
      cancelled: { bg: 'bg-red-500/20', text: 'text-red-400', border: 'border-red-500/30', icon: XCircle },
    };
    const style = styles[status] || styles.pending;
    const Icon = style.icon;
    return (
      <Badge variant="outline" className={`${style.bg} ${style.text} border ${style.border} gap-1`}>
        <Icon weight="fill" className="w-3 h-3" />
        {t(status)}
      </Badge>
    );
  };

  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(amount);
  };

  const formatDate = (dateStr) => {
    if (!dateStr) {
      return '-';
    }

    const parsed = new Date(dateStr);
    if (Number.isNaN(parsed.getTime())) {
      return '-';
    }

    return new Intl.DateTimeFormat(language === 'de' ? 'de-DE' : 'en-US', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    }).format(parsed);
  };

  return (
    <DashboardLayout title={t('pageTitle')}>
      <div className="space-y-6 max-w-[1400px]">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4" data-testid="invoices-stats">
          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('totalPurchases')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{paidInvoices.length}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-primary/10 flex items-center justify-center">
                  <Receipt weight="fill" className="w-6 h-6 text-primary" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('totalCredits')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{totalCredits.toLocaleString()}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-primary/10 flex items-center justify-center">
                  <CurrencyCircleDollar weight="fill" className="w-6 h-6 text-primary" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('totalSpent')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{formatCurrency(totalSpent)}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-green-500/10 flex items-center justify-center">
                  <FileText weight="fill" className="w-6 h-6 text-green-500" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('pendingInvoices')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{pendingCount}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-yellow-500/10 flex items-center justify-center">
                  <Clock weight="fill" className="w-6 h-6 text-yellow-500" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <Card className="bg-card border-border" data-testid="invoices-table-card">
          <CardHeader className="border-b border-border pb-6 pt-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
                <Receipt weight="fill" className="w-5 h-5 text-primary" />
                {t('invoiceList')}
              </CardTitle>

              <div className="flex flex-col gap-3 md:flex-row md:items-center">
                <div className="relative">
                  <FileText className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                  <Input
                    type="search"
                    placeholder={t('searchPlaceholder')}
                    value={searchQuery}
                    onChange={(event) => updateFilters(event.target.value, yearFilter, statusFilter)}
                    className="h-12 w-full min-w-64 bg-secondary border-border pl-9"
                    data-testid="invoice-search"
                  />
                </div>
                <FunnelSimple weight="bold" className="w-4 h-4 text-muted-foreground" />
                <Select value={yearFilter} onValueChange={(value) => updateFilters(searchQuery, value, statusFilter)}>
                  <SelectTrigger className="h-12 w-40 bg-secondary border-border px-4" data-testid="year-filter">
                    <SelectValue placeholder={t('filterByYear')} />
                  </SelectTrigger>
                  <SelectContent className="bg-card border-border">
                    <SelectItem value="all">{t('allYears')}</SelectItem>
                    {yearOptions.map((year) => (
                      <SelectItem key={year} value={year}>{year}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>

                <Select value={statusFilter} onValueChange={(value) => updateFilters(searchQuery, yearFilter, value)}>
                  <SelectTrigger className="h-12 w-44 bg-secondary border-border px-4" data-testid="status-filter">
                    <SelectValue placeholder={t('filterByStatus')} />
                  </SelectTrigger>
                  <SelectContent className="bg-card border-border">
                    <SelectItem value="all">{t('allStatus')}</SelectItem>
                    <SelectItem value="paid">{t('paid')}</SelectItem>
                    <SelectItem value="pending">{t('pending')}</SelectItem>
                    <SelectItem value="cancelled">{t('cancelled')}</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardHeader>
          <CardContent className="px-0 pb-0 pt-0">
            {filteredInvoices.length > 0 ? (
              <Table className="text-[15px]">
                <TableHeader>
                  <TableRow className="border-border hover:bg-transparent">
                    <TableHead className="h-14 px-4 text-base font-semibold text-muted-foreground">{t('invoiceNumber')}</TableHead>
                    <TableHead className="h-14 px-4 text-base font-semibold text-muted-foreground">{t('date')}</TableHead>
                    <TableHead className="h-14 px-4 text-base font-semibold text-muted-foreground">{t('credits')}</TableHead>
                    <TableHead className="h-14 px-4 text-right text-base font-semibold text-muted-foreground">{t('netAmount')}</TableHead>
                    <TableHead className="h-14 px-4 text-right text-base font-semibold text-muted-foreground">{t('tax')}</TableHead>
                    <TableHead className="h-14 px-4 text-right text-base font-semibold text-muted-foreground">{t('total')}</TableHead>
                    <TableHead className="h-14 px-4 text-base font-semibold text-muted-foreground">{t('payment')}</TableHead>
                    <TableHead className="h-14 px-4 text-base font-semibold text-muted-foreground">{t('status')}</TableHead>
                    <TableHead className="h-14 px-4 text-right text-base font-semibold text-muted-foreground">{t('actions')}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredInvoices.map((invoice) => (
                    <TableRow key={invoice.id} className="border-white/5 hover:bg-secondary/50" data-testid={`invoice-row-${invoice.id}`}>
                      <TableCell className="px-4 py-6 font-mono text-[17px] text-foreground">{invoice.id}</TableCell>
                      <TableCell className="px-4 py-6 text-muted-foreground">
                        <div className="flex items-center gap-2">
                          <CalendarBlank weight="regular" className="w-4 h-4" />
                          {formatDate(invoice.date)}
                        </div>
                      </TableCell>
                      <TableCell className="px-4 py-6">
                        <div className="flex items-center gap-2">
                          <CurrencyCircleDollar weight="fill" className="w-4 h-4 text-primary" />
                          <span className="font-heading text-[17px] font-semibold text-primary">{invoice.credits}</span>
                        </div>
                      </TableCell>
                      <TableCell className="px-4 py-6 text-right text-[17px] text-foreground">{formatCurrency(invoice.amount)}</TableCell>
                      <TableCell className="px-4 py-6 text-right text-[17px] text-muted-foreground">{formatCurrency(invoice.tax)}</TableCell>
                      <TableCell className="px-4 py-6 text-right text-[17px] font-semibold text-foreground">{formatCurrency(invoice.total)}</TableCell>
                      <TableCell className="px-4 py-6">
                        <Badge variant="outline" className="bg-secondary border-border">{invoice.paymentMethod}</Badge>
                      </TableCell>
                      <TableCell className="px-4 py-6">{getStatusBadge(invoice.status)}</TableCell>
                      <TableCell className="px-4 py-6 text-right">
                        <div className="flex items-center justify-end gap-3">
                          <Button
                            variant="ghost"
                            size="icon"
                            className="h-10 w-10 text-muted-foreground hover:text-foreground hover:bg-secondary"
                            data-testid={`view-invoice-${invoice.id}`}
                            onClick={() => navigate(`/invoices/${encodeURIComponent(String(invoice.id || ''))}`)}
                          >
                            <Eye weight="regular" className="w-4 h-4" />
                          </Button>
                          {invoice.status === 'paid' && (
                            <Button
                              variant="ghost"
                              size="icon"
                              className="h-10 w-10 text-muted-foreground hover:text-primary hover:bg-primary/10"
                              data-testid={`download-invoice-${invoice.id}`}
                              onClick={() => {
                                if (invoice.downloadUrl) {
                                  window.location.href = invoice.downloadUrl;
                                }
                              }}
                              disabled={!invoice.downloadUrl}
                            >
                              <Download weight="regular" className="w-4 h-4" />
                            </Button>
                          )}
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            ) : (
              <div className="p-10 text-center text-muted-foreground">{t('noInvoices')}</div>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
