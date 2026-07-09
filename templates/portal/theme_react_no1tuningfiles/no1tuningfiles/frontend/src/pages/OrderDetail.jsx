import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { Textarea } from '../components/ui/textarea';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '../components/ui/select';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '../components/ui/dialog';
import {
  ArrowLeft, CarProfile, CheckCircle, Download, FileArrowUp, FileArrowDown,
  CurrencyCircleDollar, Wrench, Note, Copy, Info, Package,
  Upload, Cpu, MagnifyingGlass, Lightning, Ticket, PaperPlaneTilt,
  Headset, Clock, Pulse, Warning, XCircle,
} from '@phosphor-icons/react';
import { cn } from '../lib/utils';
import { buildOrdersTopic, getOrdersData, mapPortalStatusToReact, readOrdersCustomerId } from '../lib/ordersBootstrap';

const t_data = {
  de: {
    backToOrders: 'Zur\u00FCck zu Auftr\u00E4gen',
    orderDetails: 'Auftragsdetails',
    orderStatus: 'Auftragsstatus',
    vehicleInfo: 'Fahrzeuginformationen',
    tuningInfo: 'Tuning-Konfiguration',
    filesInfo: 'Dateien',
    notes: 'Notizen',
    manufacturer: 'Hersteller',
    model: 'Modell',
    series: 'Baureihe',
    year: 'Baujahr',
    engine: 'Motor',
    power: 'Leistung',
    ecu: 'Steuerger\u00E4t',
    tuningType: 'Tuning-Typ',
    options: 'Optionen',
    credits: 'Credits',
    stagePower: 'Stage Leistung',
    stageTorque: 'Stage Drehmoment',
    gainPower: 'Mehrleistung',
    gainTorque: 'Mehrdrehmoment',
    stagePrice: 'Stage Preis',
    optionsPrice: 'Optionen Preis',
    totalPrice: 'Gesamtpreis',
    originalFile: 'Originaldatei',
    modifiedFile: 'Modifizierte Datei',
    downloadFile: 'Herunterladen',
    followUpOrder: 'Folgeauftrag',
    noFile: 'Noch nicht verf\u00FCgbar',
    pending: 'Wartend',
    processing: 'In Bearbeitung',
    parked: 'Geparkt',
    completed: 'Abgeschlossen',
    cancelled: 'Storniert',
    noNotes: 'Keine Notizen.',
    copyOrderId: 'Kopieren',
    orderNotFound: 'Auftrag nicht gefunden',
    orderCancelled: 'Auftrag storniert',
    liveStatus: 'LIVE STATUS',
    vehicle: 'FAHRZEUG',
    tuning: 'TUNING',
    file: 'DATEI',
    ph_receiving: 'Datei wird empfangen...',
    ph_receiving_done: 'Datei empfangen',
    ph_winols: 'WinOLS Pr\u00FCfung wird gestartet...',
    ph_scanning: 'WinOLS durchsucht Datenbank...',
    ph_match: 'Match gefunden! File existiert bereits',
    ph_no_match: 'Kein Match gefunden',
    ph_auto: 'File wird automatisch erstellt...',
    ph_support: 'Wird vom Support bearbeitet',
    ph_done_auto: 'File erstellt & bereit zum Download',
    ph_done_support: 'Support wurde benachrichtigt',
    auto_info: 'WinOLS hat eine passende Konfiguration gefunden. Das File wird automatisch generiert.',
    support_info: 'Diese Konfiguration muss manuell erstellt werden. Unser Support-Team wurde benachrichtigt.',
    support_time: 'Gesch\u00E4tzte Bearbeitungszeit: 30-60 Minuten',
    support_handling: 'Wird vom Support bearbeitet',
    download_ready: 'Dein modifiziertes File ist bereit!',
    step_received: 'Datei empfangen',
    step_check_db: 'Check Database',
    step_no_match: 'Kein Match gefunden',
    step_manual: 'Manuelle Bearbeitung',
    step_support_notified: 'Support benachrichtigt',
    step_manual_processing: 'Wird manuell bearbeitet',
    step_automod_processing: 'AutoMod verarbeitet',
    step_automod_failed: 'AutoMod fehlgeschlagen',
    // Ticket
    openTicket: 'Ticket \u00F6ffnen',
    ticketDesc: 'Support-Anfrage zu diesem Auftrag erstellen',
    ticketSubject: 'Betreff',
    ticketSubjectPlaceholder: 'Kurze Beschreibung des Problems',
    ticketCategory: 'Kategorie',
    ticketCatTechnical: 'Technisches Problem',
    ticketCatFile: 'Datei-Problem',
    ticketCatGeneral: 'Allgemeine Frage',
    ticketCatBilling: 'Abrechnung',
    ticketMessage: 'Nachricht',
    ticketMessagePlaceholder: 'Beschreibe dein Anliegen so genau wie m\u00F6glich...',
    ticketOrder: 'Auftrag',
    ticketSend: 'Ticket absenden',
    ticketSaving: 'Ticket wird gespeichert...',
    ticketSent: 'Ticket erstellt!',
    ticketSentMsg: 'Dein Ticket wurde erfolgreich erstellt. Unser Support-Team meldet sich schnellstm\u00F6glich.',
    ticketGoTo: 'Zum Ticket',
    ticketError: 'Ticket konnte nicht gespeichert werden.',
    relatedTickets: 'Verknüpfte Tickets',
    noRelatedTickets: 'Noch keine Tickets zu diesem Auftrag.',
    openTicketDetail: 'Details öffnen',
  },
  en: {
    backToOrders: 'Back to Orders',
    orderDetails: 'Order Details',
    orderStatus: 'Order Status',
    vehicleInfo: 'Vehicle Information',
    tuningInfo: 'Tuning Configuration',
    filesInfo: 'Files',
    notes: 'Notes',
    manufacturer: 'Manufacturer',
    model: 'Model',
    series: 'Series',
    year: 'Year',
    engine: 'Engine',
    power: 'Power',
    ecu: 'ECU',
    tuningType: 'Tuning Type',
    options: 'Options',
    credits: 'Credits',
    stagePower: 'Stage Power',
    stageTorque: 'Stage Torque',
    gainPower: 'Power Gain',
    gainTorque: 'Torque Gain',
    stagePrice: 'Stage Price',
    optionsPrice: 'Options Price',
    totalPrice: 'Total Price',
    originalFile: 'Original File',
    modifiedFile: 'Modified File',
    downloadFile: 'Download',
    followUpOrder: 'Follow-Up Order',
    noFile: 'Not available yet',
    pending: 'Pending',
    processing: 'Processing',
    parked: 'Parked',
    completed: 'Completed',
    cancelled: 'Cancelled',
    noNotes: 'No notes.',
    copyOrderId: 'Copy',
    orderNotFound: 'Order not found',
    orderCancelled: 'Order Cancelled',
    liveStatus: 'LIVE STATUS',
    vehicle: 'VEHICLE',
    tuning: 'TUNING',
    file: 'FILE',
    ph_receiving: 'Receiving file...',
    ph_receiving_done: 'File received',
    ph_winols: 'Starting WinOLS check...',
    ph_scanning: 'WinOLS scanning database...',
    ph_match: 'Match found! File already exists',
    ph_no_match: 'No match found',
    ph_auto: 'File is being auto-created...',
    ph_support: 'Being handled by support',
    ph_done_auto: 'File created & ready for download',
    ph_done_support: 'Support has been notified',
    auto_info: 'WinOLS found a matching configuration. The file is being auto-generated.',
    support_info: 'This configuration needs manual creation. Our support team has been notified.',
    support_time: 'Estimated processing time: 30-60 minutes',
    support_handling: 'Being handled by support',
    download_ready: 'Your modified file is ready!',
    step_received: 'File received',
    step_check_db: 'Check Database',
    step_no_match: 'No match found',
    step_manual: 'Manual processing',
    step_support_notified: 'Support notified',
    step_manual_processing: 'Being processed manually',
    step_automod_processing: 'AutoMod processing',
    step_automod_failed: 'AutoMod failed',
    // Ticket
    openTicket: 'Open Ticket',
    ticketDesc: 'Create a support request for this order',
    ticketSubject: 'Subject',
    ticketSubjectPlaceholder: 'Brief description of the issue',
    ticketCategory: 'Category',
    ticketCatTechnical: 'Technical Issue',
    ticketCatFile: 'File Problem',
    ticketCatGeneral: 'General Question',
    ticketCatBilling: 'Billing',
    ticketMessage: 'Message',
    ticketMessagePlaceholder: 'Describe your concern as precisely as possible...',
    ticketOrder: 'Order',
    ticketSend: 'Submit Ticket',
    ticketSaving: 'Saving ticket...',
    ticketSent: 'Ticket Created!',
    ticketSentMsg: 'Your ticket has been created successfully. Our support team will get back to you shortly.',
    ticketGoTo: 'Go to Ticket',
    ticketError: 'Could not save ticket.',
    relatedTickets: 'Related Tickets',
    noRelatedTickets: 'No tickets linked to this order yet.',
    openTicketDetail: 'Open details',
  },
};

const formatCreditPrice = (value) => {
  if (!Number.isFinite(value) || value < 0) {
    return null;
  }

  return `${Math.round(value)} Credits`;
};

const formatEuroPrice = (value, language) => {
  if (!Number.isFinite(value) || value < 0) {
    return null;
  }

  try {
    const locale = language === 'de' ? 'de-DE' : 'en-US';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: 'EUR' }).format(value);
  } catch (_error) {
    return `${value.toFixed(2)} EUR`;
  }
};

const isStagePerformanceVisible = (performance) => {
  if (!performance || typeof performance !== 'object') {
    return false;
  }

  return Number.isFinite(performance.tunedPs)
    || Number.isFinite(performance.tunedNm)
    || Number.isFinite(performance.gainPs)
    || Number.isFinite(performance.gainNm);
};

// ─── Horizontal Status Stepper (static, for pending/processing orders) ──
function StatusStepper({ order, t }) {
  const autoMod = order.autoMod || { enabled: false, status: null };
  const showSupportPanel = !autoMod.enabled || autoMod.status === 9;

  // Steps for horizontal display
  let steps;
  if (!autoMod.enabled) {
    steps = [
      { id: 'received', label: t('step_received'), icon: Upload, status: 'done' },
      { id: 'manual', label: t('step_manual'), sub: t('step_manual_processing'), icon: Headset, status: 'active' },
    ];
  } else if (autoMod.status === 9) {
    steps = [
      { id: 'received', label: t('step_received'), icon: Upload, status: 'done' },
      { id: 'check', label: t('step_check_db'), icon: Cpu, status: 'done' },
      { id: 'nomatch', label: t('step_automod_failed'), icon: XCircle, status: 'error' },
      { id: 'manual', label: t('step_manual'), sub: t('step_support_notified'), icon: Headset, status: 'active' },
    ];
  } else {
    steps = [
      { id: 'received', label: t('step_received'), icon: Upload, status: 'done' },
      { id: 'check', label: t('step_check_db'), icon: Cpu, status: autoMod.status >= 2 ? 'done' : 'active' },
      { id: 'processing', label: t('step_automod_processing'), icon: Cpu, status: autoMod.status === 4 ? 'done' : (autoMod.status === 2 ? 'active' : 'pending') },
    ];
  }

  return (
    <div className="space-y-6">
      {/* Horizontal stepper */}
      <Card className="bg-card border-border" data-testid="status-stepper">
        <CardContent className="p-6">
          <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-6">
            <Pulse weight="bold" className="w-3.5 h-3.5" />
            {t('orderStatus')}
          </label>

          <div className="flex items-start" data-testid="stepper-row">
            {steps.map((step, idx) => {
              const Icon = step.icon;
              const isLast = idx === steps.length - 1;
              return (
                <div key={step.id} className="flex items-start flex-1 min-w-0" data-testid={`stepper-${step.id}`}>
                  {/* Step circle + label */}
                  <div className="flex flex-col items-center text-center flex-1">
                    <div className={cn(
                      "w-10 h-10 rounded-full flex items-center justify-center border-2 mb-2 transition-all",
                      step.status === 'done' && "bg-green-500/15 border-green-500",
                      step.status === 'error' && "bg-red-500/15 border-red-500",
                      step.status === 'active' && "bg-yellow-500/15 border-yellow-500",
                      step.status === 'pending' && "bg-secondary/50 border-border",
                    )}>
                      {step.status === 'done' && <CheckCircle weight="fill" className="w-5 h-5 text-green-500" />}
                      {step.status === 'error' && <Icon weight="fill" className="w-5 h-5 text-red-400" />}
                      {step.status === 'active' && <Icon weight="fill" className="w-5 h-5 text-yellow-400" />}
                      {step.status === 'pending' && <Icon weight="regular" className="w-5 h-5 text-muted-foreground" />}
                    </div>
                    <p className={cn(
                      "text-xs font-semibold leading-tight",
                      step.status === 'done' && "text-green-400",
                      step.status === 'error' && "text-red-400",
                      step.status === 'active' && "text-foreground",
                      step.status === 'pending' && "text-muted-foreground",
                    )}>
                      {step.label}
                    </p>
                    {step.sub && (
                      <p className="text-[10px] text-yellow-400 font-medium mt-0.5">{step.sub}</p>
                    )}
                  </div>

                  {/* Connector line */}
                  {!isLast && (
                    <div className="flex items-center pt-5 -mx-1">
                      <div className={cn(
                        "h-[2px] w-8 md:w-12 lg:w-16",
                        idx < 1 ? "bg-green-500/60" :
                        idx === 1 ? "bg-red-500/40" :
                        "bg-border"
                      )} />
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </CardContent>
      </Card>

      {/* Support info panel */}
      {showSupportPanel && (
      <Card className="bg-blue-500/5 border-blue-500/20" data-testid="support-panel">
        <CardContent className="p-5">
          <div className="flex items-start gap-3">
            <Headset weight="fill" className="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" />
            <div>
              <p className="text-sm font-bold text-blue-400">{t('support_handling')}</p>
              <p className="text-xs text-muted-foreground mt-1">{t('support_info')}</p>
              <p className="text-xs text-yellow-400 font-semibold mt-2 flex items-center gap-1.5">
                <Clock weight="fill" className="w-3.5 h-3.5" />
                {t('support_time')}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
      )}
    </div>
  );
}

// ─── Ticket Dialog Card ─────────────────────────────────────────────
function TicketCard({ order, t, navigate, onCreatedTicket }) {
  const [open, setOpen] = useState(false);
  const [sent, setSent] = useState(false);
  const [subject, setSubject] = useState('');
  const [category, setCategory] = useState('');
  const [message, setMessage] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState('');
  const [createdTicketId, setCreatedTicketId] = useState('');

  const handleSubmit = async () => {
    if (!subject.trim() || !message.trim() || !category || submitting) {
      return;
    }

    setSubmitError('');
    setSubmitting(true);

    try {
      const relatedVehicle = [order?.vehicle?.manufacturer, order?.vehicle?.model].filter(Boolean).join(' ');
      const orderReference = String(order?.id || '').trim();
      const subjectPrefix = orderReference !== '' ? `[${orderReference}] ` : '';
      const normalizedSubject = `${subjectPrefix}${subject.trim()}`.trim();
      const normalizedMessage = orderReference !== ''
        ? `Auftrag: ${orderReference}${relatedVehicle ? ` (${relatedVehicle})` : ''}\n\n${message.trim()}`
        : message.trim();

      const response = await fetch('/tickets/api', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          subject: normalizedSubject,
          category,
          priority: 'normal',
          message: normalizedMessage,
          relatedOrder: orderReference,
        }),
      });

      const payload = await response.json().catch(() => null);
      if (!response.ok) {
        setSubmitError(payload?.message || t('ticketError'));
        return;
      }

      const createdId = String(payload?.ticket?.id || '').trim();
      setCreatedTicketId(createdId);
      if (payload?.ticket && typeof payload.ticket === 'object' && typeof onCreatedTicket === 'function') {
        onCreatedTicket(payload.ticket);
      }
      setSent(true);
    } catch (_error) {
      setSubmitError(t('ticketError'));
    } finally {
      setSubmitting(false);
    }
  };

  const handleReset = () => {
    setOpen(false);
    setSent(false);
    setSubject('');
    setCategory('');
    setMessage('');
    setSubmitError('');
    setSubmitting(false);
    setCreatedTicketId('');
  };

  return (
    <Card className="bg-card border-border border-dashed" data-testid="ticket-card">
      <CardContent className="p-5">
        <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-2">
          <Ticket weight="bold" className="w-3.5 h-3.5" />
          {t('openTicket')}
        </label>
        <p className="text-[11px] text-muted-foreground mb-4">{t('ticketDesc')}</p>

        <Dialog open={open} onOpenChange={(v) => { setOpen(v); if (!v) handleReset(); }}>
          <DialogTrigger asChild>
            <Button className="w-full bg-secondary/80 hover:bg-secondary border border-border text-foreground font-bold py-2.5 h-auto text-sm" data-testid="open-ticket-btn">
              <Ticket weight="fill" className="w-4 h-4 mr-2" />
              {t('openTicket')}
            </Button>
          </DialogTrigger>
          <DialogContent className="bg-card border-border max-w-lg">
            <DialogHeader>
              <DialogTitle className="font-heading text-lg flex items-center gap-2">
                <Ticket weight="fill" className="w-5 h-5 text-primary" />
                {t('openTicket')}
              </DialogTitle>
            </DialogHeader>

            {sent ? (
              <div className="py-6 text-center space-y-4">
                <CheckCircle weight="fill" className="w-14 h-14 text-green-500 mx-auto" />
                <p className="text-lg font-bold text-green-400">{t('ticketSent')}</p>
                <p className="text-sm text-muted-foreground px-4">{t('ticketSentMsg')}</p>
                <div className="flex gap-3 justify-center pt-2">
                  <Button variant="outline" className="border-border" onClick={handleReset}>OK</Button>
                  <Button
                    className="btn-gradient text-white font-semibold"
                    onClick={() => {
                      const targetId = createdTicketId;
                      handleReset();
                      if (targetId !== '') {
                        navigate(`/tickets/${encodeURIComponent(targetId)}`);
                        return;
                      }
                      window.location.href = '/tickets';
                    }}
                    data-testid="goto-ticket-btn"
                  >
                    {t('ticketGoTo')}
                  </Button>
                </div>
              </div>
            ) : (
              <div className="space-y-4 pt-2">
                {/* Order reference (read-only) */}
                <div>
                  <Label className="text-xs text-muted-foreground">{t('ticketOrder')}</Label>
                  <div className="mt-1 flex items-center gap-2 bg-secondary/50 border border-border rounded-sm px-3 py-2">
                    <Package weight="fill" className="w-4 h-4 text-primary" />
                    <span className="text-sm font-mono font-semibold text-foreground">{order.id}</span>
                    <span className="text-xs text-muted-foreground">&mdash; {order.vehicle.manufacturer} {order.vehicle.model}</span>
                  </div>
                </div>

                {/* Category */}
                <div>
                  <Label className="text-xs text-muted-foreground">{t('ticketCategory')}</Label>
                  <Select value={category} onValueChange={setCategory}>
                    <SelectTrigger className="mt-1 bg-secondary/50 border-border" data-testid="ticket-category">
                      <SelectValue placeholder={t('ticketCategory')} />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="technical">{t('ticketCatTechnical')}</SelectItem>
                      <SelectItem value="file">{t('ticketCatFile')}</SelectItem>
                      <SelectItem value="general">{t('ticketCatGeneral')}</SelectItem>
                      <SelectItem value="billing">{t('ticketCatBilling')}</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                {/* Subject */}
                <div>
                  <Label className="text-xs text-muted-foreground">{t('ticketSubject')}</Label>
                  <Input
                    className="mt-1 bg-secondary/50 border-border"
                    placeholder={t('ticketSubjectPlaceholder')}
                    value={subject}
                    onChange={(e) => setSubject(e.target.value)}
                    data-testid="ticket-subject"
                  />
                </div>

                {/* Message */}
                <div>
                  <Label className="text-xs text-muted-foreground">{t('ticketMessage')}</Label>
                  <Textarea
                    className="mt-1 bg-secondary/50 border-border min-h-[120px] resize-none"
                    placeholder={t('ticketMessagePlaceholder')}
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    data-testid="ticket-message"
                  />
                </div>

                {/* Submit */}
                {submitError !== '' && (
                  <div className="rounded-sm border border-red-500/40 bg-red-500/10 px-3 py-2 text-xs text-red-300">
                    {submitError}
                  </div>
                )}
                <Button
                  className="w-full btn-gradient text-white font-bold py-3 h-auto"
                  disabled={!subject.trim() || !message.trim() || !category || submitting}
                  onClick={handleSubmit}
                  data-testid="ticket-submit-btn"
                >
                  <PaperPlaneTilt weight="fill" className="w-4 h-4 mr-2" />
                  {submitting ? t('ticketSaving') : t('ticketSend')}
                </Button>
              </div>
            )}
          </DialogContent>
        </Dialog>
      </CardContent>
    </Card>
  );
}

function RelatedTicketsCard({ tickets, t, language }) {
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
      hour: '2-digit',
      minute: '2-digit',
    }).format(parsed);
  };

  return (
    <Card className="bg-card border-border" data-testid="related-tickets-card">
      <CardHeader className="border-b border-border pb-4">
        <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
          <Ticket weight="fill" className="w-5 h-5 text-primary" />
          {t('relatedTickets')}
        </CardTitle>
      </CardHeader>
      <CardContent className="p-5">
        {tickets.length === 0 ? (
          <p className="text-xs text-muted-foreground">{t('noRelatedTickets')}</p>
        ) : (
          <div className="space-y-2.5">
            {tickets.slice(0, 10).map((ticket) => (
              <button
                type="button"
                key={String(ticket.id)}
                className="w-full text-left rounded-sm border border-border bg-secondary/40 px-3 py-2.5 hover:bg-secondary transition-colors"
                onClick={() => { window.location.href = `/tickets/${encodeURIComponent(String(ticket.id))}`; }}
                data-testid={`related-ticket-${String(ticket.id)}`}
              >
                <p className="text-xs font-semibold text-foreground truncate">{String(ticket.subject || '-')}</p>
                <div className="mt-1 flex items-center justify-between gap-2">
                  <span className="text-[11px] text-primary font-mono">{String(ticket.id || '')}</span>
                  <span className="text-[11px] text-muted-foreground">{formatDate(ticket.updatedAt || ticket.createdAt)}</span>
                </div>
              </button>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
}

// ─── Completed View ─────────────────────────────────────────────────
function CompletedView({ order, t, language }) {
  const tuningPerformance = order?.tuning?.performance || {};
  const tuningPricing = order?.tuning?.pricing || {};
  const showPerformance = isStagePerformanceVisible(tuningPerformance);
  const stagePriceLabel = formatEuroPrice(tuningPricing.stagePriceEuro, language) || formatCreditPrice(tuningPricing.stageCredits);
  const optionsPriceLabel = formatEuroPrice(tuningPricing.optionPriceEuro, language) || formatCreditPrice(tuningPricing.optionCredits);
  const totalPriceLabel = formatEuroPrice(tuningPricing.totalPriceEuro, language) || formatCreditPrice(tuningPricing.totalCredits);

  return (
    <div className="space-y-6">
      {/* Vehicle Info */}
      <Card className="bg-card border-border" data-testid="vehicle-info-card">
        <CardHeader className="border-b border-border pb-4">
          <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
            <CarProfile weight="fill" className="w-5 h-5 text-primary" />
            {t('vehicleInfo')}
          </CardTitle>
        </CardHeader>
        <CardContent className="p-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {[
              ['manufacturer', order.vehicle.manufacturer],
              ['model', order.vehicle.model],
              ['series', order.vehicle.series],
              ['year', order.vehicle.year],
              ['engine', order.vehicle.engine],
              ['power', order.vehicle.power],
            ].map(([k, v]) => (
              <div key={k}>
                <p className="text-xs text-muted-foreground uppercase tracking-wider">{t(k)}</p>
                <p className="font-semibold text-foreground mt-1">{v}</p>
              </div>
            ))}
            <div className="md:col-span-2">
              <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('ecu')}</p>
              <p className="font-semibold text-foreground mt-1 font-mono">{order.vehicle.ecu}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Tuning Info */}
      <Card className="bg-card border-border" data-testid="tuning-info-card">
        <CardHeader className="border-b border-border pb-4">
          <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
            <Wrench weight="fill" className="w-5 h-5 text-primary" />
            {t('tuningInfo')}
          </CardTitle>
        </CardHeader>
        <CardContent className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('tuningType')}</p>
              <Badge className="mt-2 bg-primary/20 text-primary border border-primary/30 text-sm">{order.tuning.type}</Badge>
            </div>
            <div>
              <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('credits')}</p>
              <div className="flex items-center gap-2 mt-2">
                <CurrencyCircleDollar weight="fill" className="w-5 h-5 text-primary" />
                <span className="font-heading font-bold text-2xl text-primary">{order.credits}</span>
              </div>
            </div>
            {showPerformance && (
              <>
                <div>
                  <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('stagePower')}</p>
                  <p className="font-semibold text-foreground mt-1">
                    {Number.isFinite(tuningPerformance.tunedPs) ? `${tuningPerformance.tunedPs} PS` : '-'}
                  </p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('stageTorque')}</p>
                  <p className="font-semibold text-foreground mt-1">
                    {Number.isFinite(tuningPerformance.tunedNm) ? `${tuningPerformance.tunedNm} Nm` : '-'}
                  </p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('gainPower')}</p>
                  <p className="font-semibold text-green-400 mt-1">
                    {Number.isFinite(tuningPerformance.gainPs) ? `+${tuningPerformance.gainPs} PS` : '-'}
                  </p>
                </div>
                <div>
                  <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('gainTorque')}</p>
                  <p className="font-semibold text-green-400 mt-1">
                    {Number.isFinite(tuningPerformance.gainNm) ? `+${tuningPerformance.gainNm} Nm` : '-'}
                  </p>
                </div>
              </>
            )}
            {stagePriceLabel && (
              <div>
                <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('stagePrice')}</p>
                <p className="font-semibold text-foreground mt-1">{stagePriceLabel}</p>
              </div>
            )}
            {optionsPriceLabel && (
              <div>
                <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('optionsPrice')}</p>
                <p className="font-semibold text-foreground mt-1">{optionsPriceLabel}</p>
              </div>
            )}
            {totalPriceLabel && (
              <div>
                <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('totalPrice')}</p>
                <p className="font-semibold text-primary mt-1">{totalPriceLabel}</p>
              </div>
            )}
            {order.tuning.options.length > 0 && (
              <div className="md:col-span-2">
                <p className="text-xs text-muted-foreground uppercase tracking-wider mb-2">{t('options')}</p>
                <div className="flex flex-wrap gap-2">
                  {order.tuning.options.map((opt, idx) => (
                    <Badge key={idx} variant="outline" className="bg-secondary border-border">{opt}</Badge>
                  ))}
                </div>
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Files */}
      <Card className="bg-card border-border" data-testid="files-info-card">
        <CardHeader className="border-b border-border pb-4">
          <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
            <FileArrowUp weight="fill" className="w-5 h-5 text-primary" />
            {t('filesInfo')}
          </CardTitle>
        </CardHeader>
        <CardContent className="p-6 space-y-4">
          <div className="flex items-center justify-between p-4 bg-secondary rounded-sm border border-border">
            <div className="flex items-center gap-3">
              <FileArrowUp weight="fill" className="w-5 h-5 text-muted-foreground" />
              <div>
                <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('originalFile')}</p>
                <p className="font-mono text-sm text-foreground mt-0.5">{order.files.original}</p>
              </div>
            </div>
            <Button variant="ghost" size="sm" className="text-muted-foreground hover:text-foreground">
              <Download weight="regular" className="w-4 h-4 mr-2" /> {t('downloadFile')}
            </Button>
          </div>
          <div className={cn("flex items-center justify-between p-4 rounded-sm border", order.files.modified ? "bg-green-500/5 border-green-500/20" : "bg-secondary border-border")}>
            <div className="flex items-center gap-3">
              <FileArrowDown weight="fill" className={cn("w-5 h-5", order.files.modified ? "text-green-500" : "text-muted-foreground")} />
              <div>
                <p className="text-xs text-muted-foreground uppercase tracking-wider">{t('modifiedFile')}</p>
                <p className={cn("font-mono text-sm mt-0.5", order.files.modified ? "text-foreground" : "text-muted-foreground")}>
                  {order.files.modified || t('noFile')}
                </p>
              </div>
            </div>
            {order.files.modified && order.files.modifiedDownloadUrl && (
              <Button asChild className="bg-green-600 hover:bg-green-700" data-testid="download-modified">
                <a href={order.files.modifiedDownloadUrl}>
                  <Download weight="bold" className="w-4 h-4 mr-2" /> {t('downloadFile')}
                </a>
              </Button>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

// ─── Main Component ─────────────────────────────────────────────────
export default function OrderDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { language } = useLanguage();
  const t = (key) => t_data[language]?.[key] || key;
  const customerId = readOrdersCustomerId();

  const [orders, setOrders] = useState(() => getOrdersData());
  const [relatedTickets, setRelatedTickets] = useState([]);

  const extractRelatedOrder = (ticket) => {
    const direct = String(ticket?.relatedOrder || '').trim();
    if (direct !== '') {
      return direct;
    }

    const subject = String(ticket?.subject || '').trim();
    const subjectMatch = subject.match(/^\[([A-Za-z0-9\-_]+)\]/);
    if (subjectMatch && subjectMatch[1]) {
      return String(subjectMatch[1]).trim();
    }

    const messages = Array.isArray(ticket?.messages) ? ticket.messages : [];
    for (const message of messages) {
      const text = String(message?.message || '');
      const messageMatch = text.match(/Auftrag:\s*([A-Za-z0-9\-_]+)/i);
      if (messageMatch && messageMatch[1]) {
        return String(messageMatch[1]).trim();
      }
    }

    return '';
  };

  useEffect(() => {
    let isActive = true;
    let timerId = null;
    let after = 0;
    let consecutiveFailures = 0;

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

          setOrders((currentOrders) => currentOrders.map((order) => {
            if (order.id !== content.orderNumber) {
              return order;
            }

            return {
              ...order,
              status: mapPortalStatusToReact(content.status),
              autoMod: {
                ...order.autoMod,
                status: typeof content.autoModStatus === 'number' ? content.autoModStatus : order.autoMod?.status ?? null,
                failureReason: content.autoModFailureReason ?? order.autoMod?.failureReason ?? null,
              },
              updatedAt: content.changedAt || order.updatedAt || order.createdAt,
            };
          }));
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

  useEffect(() => {
    let isActive = true;

    const loadRelatedTickets = async () => {
      if (!id) {
        if (isActive) {
          setRelatedTickets([]);
        }
        return;
      }

      try {
        const response = await fetch('/tickets/api', {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
        });
        if (!response.ok) {
          return;
        }

        const payload = await response.json();
        const items = Array.isArray(payload?.items) ? payload.items : [];
        const normalizedId = String(id).trim().toLowerCase();
        const filtered = items.filter((ticket) => extractRelatedOrder(ticket).toLowerCase() === normalizedId);

        if (isActive) {
          setRelatedTickets(filtered);
        }
      } catch (_error) {
        // Keep current list on fetch errors.
      }
    };

    loadRelatedTickets();

    return () => {
      isActive = false;
    };
  }, [id]);

  const order = orders.find(o => o.id === id);

  if (!order) {
    return (
      <DashboardLayout>
        <div className="flex flex-col items-center justify-center py-20">
          <Info weight="fill" className="w-16 h-16 text-muted-foreground mb-4" />
          <h2 className="font-heading font-semibold text-xl text-foreground">{t('orderNotFound')}</h2>
          <Button variant="outline" className="mt-4 border-border" onClick={() => { window.location.href = '/orders'; }}>
            <ArrowLeft weight="bold" className="w-4 h-4 mr-2" /> {t('backToOrders')}
          </Button>
        </div>
      </DashboardLayout>
    );
  }

  const isInProgress = order.status === 'pending' || order.status === 'processing' || order.status === 'parked';

  const statusBadge = {
    pending: { cls: 'bg-yellow-500/15 text-yellow-400 border-yellow-500/30', label: t('pending') },
    processing: { cls: 'bg-primary/15 text-primary border-primary/30', label: t('processing') },
    parked: { cls: 'bg-warning-soft text-warning-theme border-warning-soft', label: t('parked') },
    completed: { cls: 'bg-green-500/15 text-green-400 border-green-500/30', label: t('completed') },
    cancelled: { cls: 'bg-red-500/15 text-red-400 border-red-500/30', label: t('cancelled') },
  }[order.status];
  const tuningPerformance = order?.tuning?.performance || {};
  const tuningPricing = order?.tuning?.pricing || {};
  const showStagePerformance = isStagePerformanceVisible(tuningPerformance);
  const stagePriceLabel = formatEuroPrice(tuningPricing.stagePriceEuro, language) || formatCreditPrice(tuningPricing.stageCredits);
  const optionsPriceLabel = formatEuroPrice(tuningPricing.optionPriceEuro, language) || formatCreditPrice(tuningPricing.optionCredits);
  const totalPriceLabel = formatEuroPrice(tuningPricing.totalPriceEuro, language) || formatCreditPrice(tuningPricing.totalCredits);

  const startFollowUpOrder = () => {
    const fileName = String(order?.files?.original || '').trim();
    const fileSize = Number.isFinite(order?.files?.originalSize) && Number(order.files.originalSize) > 0
      ? Number(order.files.originalSize)
      : 1;

    navigate('/auftrag', {
      state: {
        followUpTemplate: {
          fileName: fileName !== '' ? fileName : 'original.bin',
          fileSize,
          vehicle: {
            manufacturer: String(order?.vehicle?.manufacturer || ''),
            series: String(order?.vehicle?.series || ''),
            model: String(order?.vehicle?.model || ''),
            engine: String(order?.vehicle?.engine || ''),
            ecu: String(order?.vehicle?.ecu || ''),
          },
        },
      },
    });
  };

  return (
    <DashboardLayout>
      <div className="space-y-6 max-w-[1400px]">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="ghost" size="icon" className="text-muted-foreground hover:text-foreground hover:bg-secondary" onClick={() => { window.location.href = '/orders'; }} data-testid="back-btn">
              <ArrowLeft weight="bold" className="w-5 h-5" />
            </Button>
            <div>
              <div className="flex items-center gap-3">
                {isInProgress && (
                  <div className="relative">
                    <Pulse weight="fill" className="w-6 h-6 text-primary" />
                    <span className="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-green-500 animate-ping" />
                    <span className="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-green-500" />
                  </div>
                )}
                {!isInProgress && <CheckCircle weight="fill" className={cn("w-6 h-6", order.status === 'completed' ? "text-green-500" : "text-red-400")} />}
                <h1 className="font-heading font-bold text-2xl text-foreground">{order.id}</h1>
                <Badge className={cn("text-xs", statusBadge.cls)}>{statusBadge.label}</Badge>
              </div>
              <p className="text-sm text-muted-foreground mt-1">
                {order.vehicle.manufacturer} {order.vehicle.model} {order.vehicle.series} &mdash; {order.tuning.type}
              </p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            {order.status === 'completed' && (
              <Button
                variant="outline"
                className="border-border text-foreground hover:bg-secondary"
                onClick={startFollowUpOrder}
                data-testid="follow-up-order-btn"
              >
                <Copy weight="bold" className="w-4 h-4 mr-2" /> {t('followUpOrder')}
              </Button>
            )}
            {order.status === 'completed' && order.files.modified && order.files.modifiedDownloadUrl && (
              <Button asChild className="btn-gradient text-white font-bold" data-testid="download-modified-btn">
                <a href={order.files.modifiedDownloadUrl}>
                  <Download weight="bold" className="w-4 h-4 mr-2" /> {t('downloadFile')}
                </a>
              </Button>
            )}
          </div>
        </div>

        {/* Content: Different layout based on status */}
        {isInProgress ? (
          /* ── In Progress: Live Tracking (like OrderLive) ── */
          <div className="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6">
            {/* LEFT: Static Status Stepper */}
            <div className="space-y-4">
              <StatusStepper order={order} t={t} />

              {/* Tuning */}
              <Card className="bg-card border-border" data-testid="summary-tuning">
                <CardContent className="p-5">
                  <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
                    <Lightning weight="bold" className="w-3.5 h-3.5" />
                    {t('tuning')}
                  </label>
                  <div className="space-y-2">
                    <div className="flex justify-between py-1.5 border-b border-border/40">
                      <span className="text-xs text-muted-foreground">{t('tuningType')}</span>
                      <Badge className="bg-primary/15 text-primary border-primary/30 text-xs">{order.tuning.type}</Badge>
                    </div>
                    {order.tuning.options.length > 0 && (
                      <div className="py-1.5 border-b border-border/40">
                        <span className="text-xs text-muted-foreground block mb-2">{t('options')}</span>
                        <div className="flex flex-wrap gap-1.5">
                          {order.tuning.options.map(opt => (
                            <span key={opt} className="text-[10px] font-semibold bg-secondary/80 border border-border px-2 py-1 rounded-sm text-foreground">{opt}</span>
                          ))}
                        </div>
                      </div>
                    )}
                    <div className="flex justify-between py-1.5">
                      <span className="text-xs text-muted-foreground">{t('credits')}</span>
                      <span className="text-sm font-bold text-primary">{order.credits} Credits</span>
                    </div>
                    {showStagePerformance && (
                      <>
                        <div className="flex justify-between py-1.5 border-t border-border/40">
                          <span className="text-xs text-muted-foreground">{t('stagePower')}</span>
                          <span className="text-xs font-semibold text-foreground">
                            {Number.isFinite(tuningPerformance.tunedPs) ? `${tuningPerformance.tunedPs} PS` : '-'}
                          </span>
                        </div>
                        <div className="flex justify-between py-1.5">
                          <span className="text-xs text-muted-foreground">{t('stageTorque')}</span>
                          <span className="text-xs font-semibold text-foreground">
                            {Number.isFinite(tuningPerformance.tunedNm) ? `${tuningPerformance.tunedNm} Nm` : '-'}
                          </span>
                        </div>
                        <div className="flex justify-between py-1.5">
                          <span className="text-xs text-muted-foreground">{t('gainPower')}</span>
                          <span className="text-xs font-semibold text-green-400">
                            {Number.isFinite(tuningPerformance.gainPs) ? `+${tuningPerformance.gainPs} PS` : '-'}
                          </span>
                        </div>
                        <div className="flex justify-between py-1.5">
                          <span className="text-xs text-muted-foreground">{t('gainTorque')}</span>
                          <span className="text-xs font-semibold text-green-400">
                            {Number.isFinite(tuningPerformance.gainNm) ? `+${tuningPerformance.gainNm} Nm` : '-'}
                          </span>
                        </div>
                      </>
                    )}
                    {stagePriceLabel && (
                      <div className="flex justify-between py-1.5 border-t border-border/40">
                        <span className="text-xs text-muted-foreground">{t('stagePrice')}</span>
                        <span className="text-xs font-semibold text-foreground">{stagePriceLabel}</span>
                      </div>
                    )}
                    {optionsPriceLabel && (
                      <div className="flex justify-between py-1.5">
                        <span className="text-xs text-muted-foreground">{t('optionsPrice')}</span>
                        <span className="text-xs font-semibold text-foreground">{optionsPriceLabel}</span>
                      </div>
                    )}
                    {totalPriceLabel && (
                      <div className="flex justify-between py-1.5">
                        <span className="text-xs text-muted-foreground">{t('totalPrice')}</span>
                        <span className="text-xs font-semibold text-primary">{totalPriceLabel}</span>
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>

              {/* File */}
              <Card className="bg-card border-border" data-testid="summary-file">
                <CardContent className="p-5">
                  <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
                    <Upload weight="bold" className="w-3.5 h-3.5" />
                    {t('file')}
                  </label>
                  <div className="py-1.5">
                    <span className="text-xs text-muted-foreground block mb-1">{t('originalFile')}</span>
                    <span className="text-xs font-mono font-semibold text-foreground break-all">{order.files.original}</span>
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* RIGHT: Order Summary Cards */}
            <div className="space-y-4">
              {/* Vehicle */}
              <Card className="bg-card border-border" data-testid="summary-vehicle">
                <CardContent className="p-5">
                  <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
                    <CarProfile weight="bold" className="w-3.5 h-3.5" />
                    {t('vehicle')}
                  </label>
                  <div className="space-y-2">
                    {[
                      [t('manufacturer'), order.vehicle.manufacturer],
                      [t('series'), order.vehicle.series],
                      [t('model'), order.vehicle.model],
                      [t('engine'), order.vehicle.engine],
                      [t('power'), order.vehicle.power],
                      [t('ecu'), order.vehicle.ecu],
                    ].map(([l, v]) => (
                      <div key={l} className="flex justify-between py-1.5 border-b border-border/40 last:border-0">
                        <span className="text-xs text-muted-foreground">{l}</span>
                        <span className="text-xs font-semibold text-foreground">{v}</span>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>

              {/* Notes */}
              {order.notes && (
                <Card className="bg-card border-border" data-testid="summary-notes">
                  <CardContent className="p-5">
                    <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
                      <Note weight="bold" className="w-3.5 h-3.5" />
                      {t('notes')}
                    </label>
                    <p className="text-xs text-muted-foreground leading-relaxed">{order.notes}</p>
                  </CardContent>
                </Card>
              )}

              {/* Ticket */}
              <RelatedTicketsCard tickets={relatedTickets} t={t} language={language} />
              <TicketCard
                order={order}
                t={t}
                navigate={navigate}
                onCreatedTicket={(createdTicket) => {
                  const createdRelatedOrder = extractRelatedOrder(createdTicket).toLowerCase();
                  const currentOrderId = String(order?.id || '').trim().toLowerCase();
                  if (createdRelatedOrder !== '' && createdRelatedOrder === currentOrderId) {
                    setRelatedTickets((current) => [createdTicket, ...current.filter((item) => item?.id !== createdTicket?.id)]);
                  }
                }}
              />
            </div>
          </div>
        ) : (
          /* ── Completed/Cancelled: Detail Cards View ── */
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="lg:col-span-2">
              <CompletedView order={order} t={t} language={language} />
            </div>
            <div className="space-y-6">
              {/* Status card */}
              <Card className="bg-card border-border" data-testid="order-status-card">
                <CardHeader className="border-b border-border pb-4">
                  <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2 uppercase tracking-wider">
                    <Package weight="fill" className="w-5 h-5 text-primary" />
                    {t('orderStatus')}
                  </CardTitle>
                </CardHeader>
                <CardContent className="p-6">
                  <div className="text-center py-4">
                    {order.status === 'completed' ? (
                      <>
                        <CheckCircle weight="fill" className="w-12 h-12 text-success-theme mx-auto mb-3" />
                        <p className="text-lg font-bold text-success-theme">{t('completed')}</p>
                      </>
                    ) : (
                      <>
                        <Warning weight="fill" className="w-12 h-12 text-danger-theme mx-auto mb-3" />
                        <p className="text-lg font-bold text-danger-theme">{t('orderCancelled')}</p>
                      </>
                    )}
                    <p className="text-xs text-muted-foreground mt-2">
                      {new Intl.DateTimeFormat(language === 'de' ? 'de-DE' : 'en-US', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(order.updatedAt))}
                    </p>
                  </div>
                </CardContent>
              </Card>

              {order.notes && order.status !== 'cancelled' && (
                <Card className="bg-card border-border" data-testid="notes-card">
                  <CardHeader className="border-b border-border pb-4">
                    <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
                      <Note weight="fill" className="w-5 h-5 text-primary" />
                      {t('notes')}
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="p-6">
                    <p className="text-sm text-muted-foreground leading-relaxed">{order.notes}</p>
                  </CardContent>
                </Card>
              )}

              {/* Ticket */}
              <RelatedTicketsCard tickets={relatedTickets} t={t} language={language} />
              <TicketCard
                order={order}
                t={t}
                navigate={navigate}
                onCreatedTicket={(createdTicket) => {
                  const createdRelatedOrder = extractRelatedOrder(createdTicket).toLowerCase();
                  const currentOrderId = String(order?.id || '').trim().toLowerCase();
                  if (createdRelatedOrder !== '' && createdRelatedOrder === currentOrderId) {
                    setRelatedTickets((current) => [createdTicket, ...current.filter((item) => item?.id !== createdTicket?.id)]);
                  }
                }}
              />
            </div>
          </div>
        )}
      </div>
    </DashboardLayout>
  );
}
