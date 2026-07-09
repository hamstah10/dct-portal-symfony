import { useEffect, useState } from 'react';
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
  ClockCounterClockwise, 
  CarProfile,
  MagnifyingGlass,
  FunnelSimple,
  Download,
  Clock,
  CheckCircle,
  Spinner,
  XCircle,
  ArrowRight,
} from '@phosphor-icons/react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { buildOrdersTopic, getOrdersData, mapPortalStatusToReact, readOrdersCustomerId } from '../lib/ordersBootstrap';

// Translations
const pageTranslations = {
  en: {
    pageTitle: 'Orders',
    allOrders: 'All Orders',
    searchPlaceholder: 'Search order or vehicle...',
    filterByStatus: 'Status',
    allStatus: 'All Status',
    pending: 'Pending',
    processing: 'Processing',
    completed: 'Completed',
    cancelled: 'Cancelled',
    parked: 'Parked',
    orderNumber: 'Order #',
    vehicle: 'Vehicle',
    tuningType: 'Tuning',
    status: 'Status',
    date: 'Date',
    credits: 'Credits',
    actions: 'Actions',
    viewDetails: 'View Details',
    download: 'Download',
    noOrders: 'No orders found',
    totalOrders: 'Total Orders',
    inProgress: 'In Progress',
    awaitingReview: 'Awaiting Review',
  },
  de: {
    pageTitle: 'Aufträge',
    allOrders: 'Alle Aufträge',
    searchPlaceholder: 'Auftrag oder Fahrzeug suchen...',
    filterByStatus: 'Status',
    allStatus: 'Alle Status',
    pending: 'Wartend',
    processing: 'In Bearbeitung',
    completed: 'Abgeschlossen',
    cancelled: 'Storniert',
    parked: 'Geparkt',
    orderNumber: 'Auftrag #',
    vehicle: 'Fahrzeug',
    tuningType: 'Tuning',
    status: 'Status',
    date: 'Datum',
    credits: 'Credits',
    actions: 'Aktionen',
    viewDetails: 'Details ansehen',
    download: 'Download',
    noOrders: 'Keine Aufträge gefunden',
    totalOrders: 'Gesamt Aufträge',
    inProgress: 'In Bearbeitung',
    awaitingReview: 'Warten auf Prüfung',
  }
};

export default function OrdersNew() {
  const { language } = useLanguage();
  const t = (key) => pageTranslations[language][key] || key;
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const [orders, setOrders] = useState(() => getOrdersData());
  const [searchQuery, setSearchQuery] = useState(() => searchParams.get('search') || '');
  const [statusFilter, setStatusFilter] = useState(() => searchParams.get('status') || 'all');
  const customerId = readOrdersCustomerId();
  const parkedOrders = Array.isArray(window?.portalReactBootstrap?.orders?.parkedOrders)
    ? window.portalReactBootstrap.orders.parkedOrders
    : [];

  useEffect(() => {
    setSearchQuery(searchParams.get('search') || '');
    setStatusFilter(searchParams.get('status') || 'all');
  }, [searchParams]);

  useEffect(() => {
    let isActive = true;
    let timerId = null;
    let after = 0;
    let consecutiveFailures = 0;

    const applyStatusUpdate = (orderNumber, status, changedAt, autoModStatus, autoModFailureReason) => {
      if (!orderNumber || !status) {
        return;
      }

      setOrders((currentOrders) => currentOrders.map((order) => {
        if (order.id !== orderNumber) {
          return order;
        }

        return {
          ...order,
          status: mapPortalStatusToReact(status),
          autoMod: {
            ...order.autoMod,
            status: typeof autoModStatus === 'number' ? autoModStatus : order.autoMod?.status ?? null,
            failureReason: autoModFailureReason ?? order.autoMod?.failureReason ?? null,
          },
          updatedAt: changedAt || order.updatedAt || order.createdAt,
        };
      }));
    };

    const scheduleNextPoll = (delay = 3000) => {
      if (!isActive) {
        return;
      }
      timerId = window.setTimeout(runPoll, delay);
    };

    const runPoll = async () => {
      const topic = buildOrdersTopic(customerId);
      if (!topic) {
        return;
      }

      const url = `/mercure/poll?topic=${encodeURIComponent(topic)}&after=${after}`;

      try {
        const response = await fetch(url, {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
          consecutiveFailures += 1;

          if ([401, 403, 404].includes(response.status) || consecutiveFailures >= 5) {
            return;
          }

          scheduleNextPoll(Math.min(15000, 3000 * (consecutiveFailures + 1)));
          return;
        }

        const payload = await response.json();
        const events = Array.isArray(payload?.events) ? payload.events : [];
        consecutiveFailures = 0;

        if (Number.isInteger(payload?.latestSequence)) {
          after = payload.latestSequence;
        }

        for (const event of events) {
          const content = event?.payload?.content;
          if (!content || content.kind !== 'order.status_changed') {
            continue;
          }

          applyStatusUpdate(content.orderNumber, content.status, content.changedAt, content.autoModStatus, content.autoModFailureReason);
        }
      } catch (_error) {
        consecutiveFailures += 1;
        if (consecutiveFailures >= 5) {
          return;
        }
      }

      scheduleNextPoll(consecutiveFailures > 0 ? Math.min(15000, 3000 * (consecutiveFailures + 1)) : 3000);
    };

    runPoll();

    return () => {
      isActive = false;
      if (timerId !== null) {
        window.clearTimeout(timerId);
      }
    };
  }, [customerId]);

  const updateFilters = (nextSearch, nextStatus) => {
    setSearchQuery(nextSearch);
    setStatusFilter(nextStatus);

    const nextParams = new URLSearchParams();
    if (nextSearch.trim()) {
      nextParams.set('search', nextSearch.trim());
    }
    if (nextStatus !== 'all') {
      nextParams.set('status', nextStatus);
    }

    setSearchParams(nextParams, { replace: true });
  };

  // Filter orders
  const filteredOrders = orders.filter(order => {
    const statusMatch = statusFilter === 'all' || order.status === statusFilter;
    const normalizedSearch = searchQuery.trim().toLowerCase();
    const searchableText = [
      order.id,
      order.vehicle.manufacturer,
      order.vehicle.model,
      order.vehicle.series,
      order.vehicle.engine,
      order.vehicle.power,
      order.vehicle.ecu,
      order.tuning.type,
      ...(order.tuning.options || []),
      order.files.original,
      order.files.modified,
      order.notes,
      order.status,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase();
    const searchMatch = normalizedSearch === '' || searchableText.includes(normalizedSearch);
    return statusMatch && searchMatch;
  });

  // Stats
  const stats = {
    total: orders.length,
    completed: orders.filter(o => o.status === 'completed').length,
    processing: orders.filter(o => o.status === 'processing').length,
    pending: orders.filter(o => o.status === 'pending').length,
    parked: orders.filter(o => o.status === 'parked').length,
  };

  const getStatusConfig = (status) => {
    const configs = {
      pending: { 
        bg: 'bg-yellow-500/20', 
        text: 'text-yellow-400', 
        border: 'border-yellow-500/30', 
        icon: Clock,
        label: t('pending')
      },
      processing: { 
        bg: 'bg-blue-500/20', 
        text: 'text-blue-400', 
        border: 'border-blue-500/30', 
        icon: Spinner,
        label: t('processing')
      },
      parked: {
        bg: 'bg-amber-500/20',
        text: 'text-amber-400',
        border: 'border-amber-500/30',
        icon: Clock,
        label: t('parked'),
      },
      completed: { 
        bg: 'bg-green-500/20', 
        text: 'text-green-400', 
        border: 'border-green-500/30', 
        icon: CheckCircle,
        label: t('completed')
      },
      cancelled: { 
        bg: 'bg-red-500/20', 
        text: 'text-red-400', 
        border: 'border-red-500/30', 
        icon: XCircle,
        label: t('cancelled')
      },
    };
    return configs[status] || configs.pending;
  };

  const formatDate = (dateStr) => {
    return new Intl.DateTimeFormat(language === 'de' ? 'de-DE' : 'en-US', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(new Date(dateStr));
  };

  return (
    <DashboardLayout title={t('pageTitle')}>
      <div className="space-y-6 max-w-[1400px]">
        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4" data-testid="orders-stats">
          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('totalOrders')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{stats.total}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-primary/10 flex items-center justify-center">
                  <ClockCounterClockwise weight="fill" className="w-6 h-6 text-primary" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('completed')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{stats.completed}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-green-500/10 flex items-center justify-center">
                  <CheckCircle weight="fill" className="w-6 h-6 text-green-500" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('inProgress')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{stats.processing}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-blue-500/10 flex items-center justify-center">
                  <Spinner weight="fill" className="w-6 h-6 text-blue-500" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('awaitingReview')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{stats.pending}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-yellow-500/10 flex items-center justify-center">
                  <Clock weight="fill" className="w-6 h-6 text-yellow-500" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card className="bg-card border-border">
            <CardContent className="p-5">
              <div className="flex items-start justify-between">
                <div>
                  <p className="text-sm text-muted-foreground">{t('parked')}</p>
                  <p className="font-heading font-bold text-3xl text-foreground mt-2">{stats.parked}</p>
                </div>
                <div className="w-12 h-12 rounded-sm bg-amber-500/10 flex items-center justify-center">
                  <ClockCounterClockwise weight="fill" className="w-6 h-6 text-amber-500" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {parkedOrders.length > 0 && (
          <Card className="bg-card border-border" data-testid="parked-orders-section">
            <CardHeader className="border-b border-border pb-3 flex items-center justify-between">
              <CardTitle className="font-heading text-lg font-semibold flex items-center gap-2">
                <ClockCounterClockwise weight="fill" className="w-5 h-5 text-primary" />
                {language === 'de' ? 'Geparkte Aufträge' : 'Parked orders'}
              </CardTitle>
              <Badge className="bg-amber-500/20 text-amber-400 border border-amber-500/30">
                {parkedOrders.length}
              </Badge>
            </CardHeader>
            <CardContent className="p-4 space-y-3">
              {parkedOrders.map((order) => (
                <div key={order.id} className="flex items-center justify-between gap-4">
                  <div>
                    <p className="text-sm font-semibold text-foreground">{order.orderNumber}</p>
                    <p className="text-xs text-muted-foreground">
                      {order.vehicleLabel || '—'}
                      {' · '}
                      {order.stageLabel || (language === 'de' ? 'Unbekannt' : 'Unknown')}
                    </p>
                    <p className="text-[11px] text-muted-foreground">
                      {language === 'de' ? 'Letzte Änderung: ' : 'Last updated: '}
                      {order.updatedAt ? new Date(order.updatedAt).toLocaleString(language === 'de' ? 'de-DE' : 'en-US') : '-'}
                    </p>
                  </div>
                  <Button
                    variant="ghost"
                    size="sm"
                    className="text-muted-foreground hover:text-foreground"
                    onClick={(e) => {
                      e.stopPropagation();
                      window.location.href = `/auftrag?parkedOrderId=${order.id}`;
                    }}
                  >
                    {language === 'de' ? 'Weiterführen' : 'Continue'}
                  </Button>
                </div>
              ))}
            </CardContent>
          </Card>
        )}

        {/* Filters & Orders List */}
        <Card className="bg-card border-border" data-testid="orders-list-card">
          <CardHeader className="border-b border-border pb-4">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
                <ClockCounterClockwise weight="fill" className="w-5 h-5 text-primary" />
                {t('allOrders')}
              </CardTitle>
              
              <div className="flex items-center gap-3">
                {/* Search */}
                <div className="relative">
                  <MagnifyingGlass className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                  <Input
                    type="search"
                    placeholder={t('searchPlaceholder')}
                    value={searchQuery}
                    onChange={(e) => updateFilters(e.target.value, statusFilter)}
                    className="w-64 pl-9 bg-secondary border-border"
                    data-testid="orders-search"
                  />
                </div>
                
                {/* Status Filter */}
                <FunnelSimple weight="bold" className="w-4 h-4 text-muted-foreground" />
                <Select value={statusFilter} onValueChange={(value) => updateFilters(searchQuery, value)}>
                  <SelectTrigger className="w-40 bg-secondary border-border" data-testid="status-filter">
                    <SelectValue placeholder={t('filterByStatus')} />
                  </SelectTrigger>
                  <SelectContent className="bg-card border-border">
                    <SelectItem value="all">{t('allStatus')}</SelectItem>
                    <SelectItem value="pending">{t('pending')}</SelectItem>
                    <SelectItem value="processing">{t('processing')}</SelectItem>
                    <SelectItem value="parked">{t('parked')}</SelectItem>
                    <SelectItem value="completed">{t('completed')}</SelectItem>
                    <SelectItem value="cancelled">{t('cancelled')}</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardHeader>
          <CardContent className="p-0">
            {filteredOrders.length > 0 ? (
              <Table className="text-sm">
                <TableHeader>
                  <TableRow className="border-border hover:bg-transparent">
                    <TableHead className="h-14 px-4 font-semibold text-muted-foreground">{t('orderNumber')}</TableHead>
                    <TableHead className="h-14 px-4 font-semibold text-muted-foreground">{t('date')}</TableHead>
                    <TableHead className="h-14 px-4 font-semibold text-muted-foreground">{t('vehicle')}</TableHead>
                    <TableHead className="h-14 px-4 font-semibold text-muted-foreground">{t('tuningType')}</TableHead>
                    <TableHead className="h-14 px-4 font-semibold text-muted-foreground">{t('status')}</TableHead>
                    <TableHead className="h-14 px-4 text-right font-semibold text-muted-foreground">{t('credits')}</TableHead>
                    <TableHead className="h-14 px-4 text-right font-semibold text-muted-foreground">{t('actions')}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredOrders.map((order) => {
                    const statusConfig = getStatusConfig(order.status);
                    const StatusIcon = statusConfig.icon;
                    const vehicleLabel = [
                      order.vehicle.manufacturer,
                      order.vehicle.model,
                      order.vehicle.series,
                    ].filter(Boolean).join(' ');
                    const tuningOptions = Array.isArray(order.tuning.options) ? order.tuning.options : [];

                    return (
                      <TableRow
                        key={order.id}
                        className="border-white/5 hover:bg-secondary/40"
                        data-testid={`order-item-${order.id}`}
                      >
                        <TableCell className="px-4 py-4">
                          <button
                            type="button"
                            className="font-mono text-sm font-semibold text-primary text-left hover:underline"
                            onClick={() => { window.location.href = `/orders/${order.id}`; }}
                          >
                            {order.id}
                          </button>
                        </TableCell>
                        <TableCell className="px-4 py-4 text-muted-foreground whitespace-nowrap">
                          {formatDate(order.createdAt)}
                        </TableCell>
                        <TableCell className="px-4 py-4">
                          <div className="flex items-center gap-3 min-w-[18rem]">
                            <div className="w-10 h-10 rounded-sm bg-secondary flex items-center justify-center border border-border flex-shrink-0">
                              <CarProfile weight="fill" className="w-5 h-5 text-primary" />
                            </div>
                            <div className="min-w-0">
                              <p className="font-semibold text-foreground truncate">{vehicleLabel || '-'}</p>
                              <p className="text-xs text-muted-foreground truncate">
                                {[order.vehicle.engine, order.vehicle.power, order.vehicle.ecu].filter(Boolean).join(' · ') || '-'}
                              </p>
                            </div>
                          </div>
                        </TableCell>
                        <TableCell className="px-4 py-4">
                          <div className="space-y-1">
                            <p className="text-foreground">{order.tuning.type || '-'}</p>
                            {tuningOptions.length > 0 && (
                              <div className="flex flex-wrap gap-1.5">
                                {tuningOptions.slice(0, 2).map((opt, idx) => (
                                  <Badge key={idx} variant="outline" className="bg-secondary border-border text-xs">
                                    {opt}
                                  </Badge>
                                ))}
                                {tuningOptions.length > 2 && (
                                  <Badge variant="outline" className="bg-secondary border-border text-xs">
                                    +{tuningOptions.length - 2}
                                  </Badge>
                                )}
                              </div>
                            )}
                          </div>
                        </TableCell>
                        <TableCell className="px-4 py-4">
                          <Badge variant="outline" className={`${statusConfig.bg} ${statusConfig.text} border ${statusConfig.border} gap-1`}>
                            <StatusIcon weight="fill" className="w-3 h-3" />
                            {statusConfig.label}
                          </Badge>
                        </TableCell>
                        <TableCell className="px-4 py-4 text-right">
                          <div>
                            <p className="font-heading font-bold text-lg text-foreground">{order.credits}</p>
                            <p className="text-xs text-muted-foreground">{t('credits')}</p>
                          </div>
                        </TableCell>
                        <TableCell className="px-4 py-4">
                          <div className="flex items-center justify-end gap-2">
                            {order.status === 'completed' && (
                              <Button
                                variant="ghost"
                                size="icon"
                                className="h-9 w-9 text-muted-foreground hover:text-primary hover:bg-primary/10"
                                onClick={(e) => { e.stopPropagation(); }}
                                data-testid={`download-${order.id}`}
                              >
                                <Download weight="regular" className="w-5 h-5" />
                              </Button>
                            )}
                            <Button
                              variant="ghost"
                              size="icon"
                              className="h-9 w-9 text-muted-foreground hover:text-foreground hover:bg-secondary"
                              onClick={(e) => { e.stopPropagation(); window.location.href = `/orders/${order.id}`; }}
                              data-testid={`view-${order.id}`}
                            >
                              <ArrowRight weight="bold" className="w-5 h-5" />
                            </Button>
                          </div>
                        </TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            ) : (
              <div className="p-8 text-center text-muted-foreground">
                {t('noOrders')}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
