import { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import {
  CheckCircle, CircleNotch, Upload, Cpu, MagnifyingGlass, Lightning,
  FileArrowDown, Headset, CarProfile, Gear, Sliders, CreditCard,
  Download, ArrowLeft, Clock, Pulse, Robot, UserCircle, Warning,
  Fire, Leaf, Drop, Fan, Gauge, Power, RocketLaunch, Prohibit, Thermometer,
} from '@phosphor-icons/react';
import { cn } from '../lib/utils';
import { buildOrdersTopic, readOrdersCustomerId } from '../lib/ordersBootstrap';

// Status phases
const PHASE = {
  RECEIVING: 'receiving',
  WINOLS_CHECK: 'winols_check',
  WINOLS_SCANNING: 'winols_scanning',
  MATCH_FOUND: 'match_found',
  NO_MATCH: 'no_match',
  AUTO_CREATING: 'auto_creating',
  SUPPORT_QUEUE: 'support_queue',
  DONE_AUTO: 'done_auto',
  DONE_SUPPORT: 'done_support',
};

const t_data = {
  de: {
    title: 'Auftrag gesendet',
    subtitle: 'Dein Auftrag wird verarbeitet',
    orderNum: 'Auftragsnummer',
    liveStatus: 'LIVE STATUS',
    vehicle: 'FAHRZEUG',
    tuning: 'TUNING',
    file: 'DATEI',
    device: 'LESEGERÄT',
    method: 'METHODE',
    type: 'LESEART',
    ms: 'MASTER/SLAVE',
    priority: 'PRIORITÄT',
    credits: 'CREDITS',
    options: 'OPTIONEN',
    stage: 'STAGE',
    ecu: 'STEUERGERÄT',
    gearbox: 'GETRIEBE',
    backToOrders: 'Zu Aufträgen',
    downloadFile: 'Datei herunterladen',
    // Phases
    ph_receiving: 'Datei wird empfangen...',
    ph_receiving_done: 'Datei empfangen',
    ph_winols: 'WinOLS Prüfung wird gestartet...',
    ph_scanning: 'WinOLS durchsucht Datenbank...',
    ph_match: 'Match gefunden! File existiert bereits',
    ph_no_match: 'AutoMod fehlgeschlagen',
    ph_auto: 'File wird automatisch erstellt...',
    ph_support: 'Wird vom Support bearbeitet',
    ph_done_auto: 'File erstellt & bereit zum Download',
    ph_done_support: 'Support wurde benachrichtigt',
    // Info
    auto_info: 'WinOLS hat eine passende Konfiguration gefunden. Das File wird automatisch generiert.',
    support_info: 'Die automatische Verarbeitung ist fehlgeschlagen. Unser Support-Team wurde benachrichtigt und bearbeitet deinen Auftrag.',
    support_time: 'Geschätzte Bearbeitungszeit: 30-60 Minuten',
    download_ready: 'Dein modifiziertes File ist bereit!',
  },
  en: {
    title: 'Order Submitted',
    subtitle: 'Your order is being processed',
    orderNum: 'Order Number',
    liveStatus: 'LIVE STATUS',
    vehicle: 'VEHICLE',
    tuning: 'TUNING',
    file: 'FILE',
    device: 'DEVICE',
    method: 'METHOD',
    type: 'TYPE',
    ms: 'MASTER/SLAVE',
    priority: 'PRIORITY',
    credits: 'CREDITS',
    options: 'OPTIONS',
    stage: 'STAGE',
    ecu: 'ECU',
    gearbox: 'GEARBOX',
    backToOrders: 'Go to Orders',
    downloadFile: 'Download File',
    ph_receiving: 'Receiving file...',
    ph_receiving_done: 'File received',
    ph_winols: 'Starting WinOLS check...',
    ph_scanning: 'WinOLS scanning database...',
    ph_match: 'Match found! File already exists',
    ph_no_match: 'AutoMod failed',
    ph_auto: 'File is being auto-created...',
    ph_support: 'Being handled by support',
    ph_done_auto: 'File created & ready for download',
    ph_done_support: 'Support has been notified',
    auto_info: 'WinOLS found a matching configuration. The file is being auto-generated.',
    support_info: 'Automatic processing failed. Our support team has been notified and will process your order.',
    support_time: 'Estimated processing time: 30-60 minutes',
    download_ready: 'Your modified file is ready!',
  },
};

const stageNames = { stage1: 'Stage 1', stage2: 'Stage 2', eco: 'Eco', gearbox: 'Getriebe', optionsOnly: 'Nur Optionen' };
const optionNames = { dpf: 'DPF-Off', egr: 'EGR-Off', adblue: 'AdBlue-Off', vmax: 'Vmax-Off', dtc: 'DTC-Off', startstop: 'Start-Stop-Off', pops: 'Pops & Bangs', launch: 'Launch Control', swirl: 'Swirl Flaps-Off', cat: 'Kat-Off', o2: 'O2-Off', hotstart: 'Hot Start Fix' };

function genOrderId() {
  const d = new Date();
  const num = Math.floor(10000 + Math.random() * 90000);
  return `CT-${d.getFullYear()}${String(d.getMonth()+1).padStart(2,'0')}${String(d.getDate()).padStart(2,'0')}-${num}`;
}

// Reale API kennt kein "Match gefunden/kein Match"-Unterereignis - DECRYPTED -> COMPLETED
// ersetzt den fruehreren MATCH_FOUND/AUTO_CREATING-Zwischenschritt direkt.
function mapAutoModPhaseToUiPhase(autoModStatus) {
  switch (autoModStatus) {
    case 1: return PHASE.WINOLS_CHECK;
    case 2: return PHASE.WINOLS_SCANNING;
    case 4: return PHASE.DONE_AUTO;
    case 9: return PHASE.DONE_SUPPORT;
    default: return PHASE.RECEIVING;
  }
}

export default function OrderLive() {
  const { language } = useLanguage();
  const t = (k) => t_data[language]?.[k] || k;
  const navigate = useNavigate();
  const location = useLocation();

  // Order data from wizard
  const orderData = location.state?.orderData || null;
  const [orderId] = useState(() => orderData?.orderId || genOrderId());
  const [phase, setPhase] = useState(() => mapAutoModPhaseToUiPhase(orderData?.autoModStatus));
  const [isAutoPath, setIsAutoPath] = useState(null);
  const [progress, setProgress] = useState(0);

  // Poll fuer echte AutoMod-Status-Updates ueber den bestehenden Mercure-Realtime-Kanal
  useEffect(() => {
    let isActive = true;
    let timerId = null;
    let after = 0;
    let consecutiveFailures = 0;

    const customerId = readOrdersCustomerId();
    const topic = buildOrdersTopic(customerId);
    const orderNumber = String(orderId || '').trim();

    if (!topic || '' === orderNumber) {
      return undefined;
    }

    const scheduleNextPoll = (delay = 3000) => {
      if (!isActive) {
        return;
      }
      timerId = window.setTimeout(runPoll, delay);
    };

    const runPoll = async () => {
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
          if (!content || content.kind !== 'order.status_changed' || content.orderNumber !== orderId) {
            continue;
          }

          if (typeof content.autoModStatus === 'number') {
            setPhase(mapAutoModPhaseToUiPhase(content.autoModStatus));
          }
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
  }, [orderId]);

  // Progress bar animation
  useEffect(() => {
    const phaseProgress = {
      [PHASE.RECEIVING]: 8,
      [PHASE.WINOLS_CHECK]: 20,
      [PHASE.WINOLS_SCANNING]: 45,
      [PHASE.MATCH_FOUND]: 60,
      [PHASE.NO_MATCH]: 60,
      [PHASE.AUTO_CREATING]: 80,
      [PHASE.SUPPORT_QUEUE]: 75,
      [PHASE.DONE_AUTO]: 100,
      [PHASE.DONE_SUPPORT]: 100,
    };
    setProgress(phaseProgress[phase] || 0);
  }, [phase]);

  useEffect(() => {
    if (phase === PHASE.DONE_AUTO) {
      setIsAutoPath(true);
    } else if (phase === PHASE.DONE_SUPPORT) {
      setIsAutoPath(false);
    }
  }, [phase]);

  const isDone = phase === PHASE.DONE_AUTO || phase === PHASE.DONE_SUPPORT;

  // Build step list for timeline (basiert auf echtem autoModStatus statt Fake-Timing)
  const getSteps = () => {
    const steps = [
      { id: 'receive', label: phase === PHASE.RECEIVING ? t('ph_receiving') : t('ph_receiving_done'), icon: Upload, done: phase !== PHASE.RECEIVING, active: phase === PHASE.RECEIVING },
      { id: 'winols', label: phase === PHASE.WINOLS_CHECK ? t('ph_winols') : t('ph_scanning'), icon: Cpu, done: phase === PHASE.WINOLS_SCANNING || isDone, active: phase === PHASE.WINOLS_CHECK },
    ];

    if (phase === PHASE.DONE_SUPPORT) {
      steps.push(
        { id: 'nomatch', label: t('ph_no_match'), icon: Warning, done: true, active: false, warning: true },
        { id: 'support', label: t('ph_support'), icon: Headset, done: true, active: false },
        { id: 'done', label: t('ph_done_support'), icon: UserCircle, done: true, active: false, final: true },
      );
    } else {
      steps.push(
        { id: 'auto', label: t('ph_auto'), icon: Robot, done: phase === PHASE.DONE_AUTO, active: phase === PHASE.WINOLS_SCANNING },
        { id: 'done', label: t('ph_done_auto'), icon: FileArrowDown, done: phase === PHASE.DONE_AUTO, active: false, final: true },
      );
    }

    return steps;
  };

  const steps = getSteps();

  // Default mock order if no data passed
  const od = orderData || {
    fileName: 'Audi_A4_B9_2.0TDI_EDC17CP44.bin',
    mfr: 'Audi', ser: 'A4', mod: 'B9 - 2015', eng: '2.0 TDI - 150 PS',
    ecuVal: 'Bosch EDC17CP44',
    readingDevice: 'Autotuner - Tool', readingMethod: 'OBD', readingType: 'Full Read',
    masterSlave: 'Master', priority: 'Normal',
    selectedStage: 'stage1', selectedGearboxStage: '', selectedOptions: ['dpf', 'egr'],
    totalCredits: 100,
  };

  return (
    <DashboardLayout>
      <div className="space-y-6 max-w-[1400px]">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <div className="flex items-center gap-3">
              <h1 className="font-heading text-2xl font-bold text-foreground tracking-tight flex items-center gap-3">
                {isDone ? (
                  <CheckCircle weight="fill" className="w-7 h-7 text-green-500" />
                ) : (
                  <div className="relative">
                    <Pulse weight="fill" className="w-7 h-7 text-primary" />
                    <span className="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-green-500 animate-ping" />
                    <span className="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-green-500" />
                  </div>
                )}
                {t('title')}
              </h1>
            </div>
            <p className="text-sm text-muted-foreground mt-1">{t('subtitle')}</p>
          </div>
          <div className="flex items-center gap-3">
            <div className="bg-card border border-border rounded-sm px-4 py-2">
              <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('orderNum')}</p>
              <p className="text-sm font-mono font-bold text-primary">{orderId}</p>
            </div>
          </div>
        </div>

        {/* Global progress bar */}
        <div className="relative h-2 bg-secondary/60 rounded-full overflow-hidden" data-testid="global-progress">
          <div
            className={cn(
              "h-full rounded-full transition-all duration-1000 ease-out",
              isDone ? "bg-green-500" : "bg-primary"
            )}
            style={{ width: `${progress}%` }}
          />
          {!isDone && (
            <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-pulse" />
          )}
        </div>

        {/* Main content: 2-column */}
        <div className="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6">
          {/* LEFT: Live Status Timeline */}
          <Card className="bg-card border-border" data-testid="live-status-card">
            <CardContent className="p-6">
              <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-6">
                <Pulse weight="bold" className="w-3.5 h-3.5" />
                {t('liveStatus')}
                {!isDone && <span className="ml-2 w-2 h-2 rounded-full bg-green-500 animate-pulse" />}
              </label>

              {/* Timeline */}
              <div className="space-y-0">
                {steps.map((step, idx) => {
                  const Icon = step.icon;
                  const isLast = idx === steps.length - 1;
                  return (
                    <div key={step.id} className="relative" data-testid={`step-${step.id}`}>
                      {/* Connector */}
                      {!isLast && (
                        <div className={cn(
                          "absolute left-[19px] top-[44px] w-[2px] h-[32px]",
                          step.done ? "bg-green-500/60" : "bg-border"
                        )} />
                      )}
                      <div className={cn(
                        "flex items-center gap-4 py-3 px-4 rounded-sm transition-all duration-500",
                        step.active && "bg-primary/8 border border-primary/30",
                        step.done && !step.final && "opacity-80",
                        step.done && step.final && "bg-green-500/8 border border-green-500/30",
                        step.pending && "opacity-40",
                      )}>
                        {/* Icon */}
                        <div className={cn(
                          "w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 border-2 transition-all duration-500",
                          step.done ? "bg-green-500/15 border-green-500" :
                          step.active ? "bg-primary/15 border-primary" :
                          step.warning ? "bg-yellow-500/15 border-yellow-500" :
                          "bg-secondary/50 border-border"
                        )}>
                          {step.done ? (
                            <CheckCircle weight="fill" className="w-5 h-5 text-green-500" />
                          ) : step.active ? (
                            <CircleNotch weight="bold" className="w-5 h-5 text-primary animate-spin" />
                          ) : step.warning && step.active ? (
                            <Warning weight="fill" className="w-5 h-5 text-yellow-500" />
                          ) : (
                            <Icon weight={step.pending ? "light" : "regular"} className={cn("w-5 h-5", step.warning ? "text-yellow-500" : "text-muted-foreground")} />
                          )}
                        </div>

                        {/* Label */}
                        <div className="flex-1 min-w-0">
                          <p className={cn(
                            "text-sm font-semibold transition-all duration-500",
                            step.done ? "text-green-400" :
                            step.active ? "text-foreground" :
                            step.warning ? "text-yellow-400" :
                            "text-muted-foreground"
                          )}>
                            {step.label}
                          </p>
                          {step.active && !step.pending && (
                            <div className="flex items-center gap-2 mt-1">
                              <div className="flex gap-0.5">
                                <span className="w-1.5 h-1.5 rounded-full bg-primary animate-bounce" style={{ animationDelay: '0ms' }} />
                                <span className="w-1.5 h-1.5 rounded-full bg-primary animate-bounce" style={{ animationDelay: '150ms' }} />
                                <span className="w-1.5 h-1.5 rounded-full bg-primary animate-bounce" style={{ animationDelay: '300ms' }} />
                              </div>
                            </div>
                          )}
                        </div>

                        {/* Status badge */}
                        {step.done && <Badge className="bg-green-500/15 text-green-400 border-green-500/30 text-[10px]">OK</Badge>}
                        {step.active && <Badge className="bg-primary/15 text-primary border-primary/30 text-[10px] animate-pulse">LIVE</Badge>}
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* Result info box */}
              {phase === PHASE.DONE_AUTO && (
                <div className="mt-6 p-5 bg-green-500/8 border border-green-500/30 rounded-sm" data-testid="result-auto">
                  <div className="flex items-start gap-3">
                    <Robot weight="fill" className="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" />
                    <div>
                      <p className="text-sm font-bold text-green-400">{t('download_ready')}</p>
                      <p className="text-xs text-muted-foreground mt-1">{t('auto_info')}</p>
                      <Button asChild className="mt-4 bg-green-600 hover:bg-green-700 text-white font-bold" data-testid="download-btn">
                        <a href={`/orders/${encodeURIComponent(orderId)}/download-modified`}>
                          <Download weight="bold" className="w-4 h-4 mr-2" />
                          {t('downloadFile')}
                        </a>
                      </Button>
                    </div>
                  </div>
                </div>
              )}

              {phase === PHASE.DONE_SUPPORT && (
                <div className="mt-6 p-5 bg-blue-500/8 border border-blue-500/30 rounded-sm" data-testid="result-support">
                  <div className="flex items-start gap-3">
                    <Headset weight="fill" className="w-6 h-6 text-blue-400 flex-shrink-0 mt-0.5" />
                    <div>
                      <p className="text-sm font-bold text-blue-400">{t('ph_done_support')}</p>
                      <p className="text-xs text-muted-foreground mt-1">{t('support_info')}</p>
                      <p className="text-xs text-yellow-400 font-semibold mt-2 flex items-center gap-1.5">
                        <Clock weight="fill" className="w-3.5 h-3.5" />
                        {t('support_time')}
                      </p>
                    </div>
                  </div>
                </div>
              )}

              {/* Back button */}
              {isDone && (
                <div className="mt-6 flex justify-end">
                  <Button variant="outline" className="border-border font-semibold" onClick={() => { window.location.href = '/orders'; }} data-testid="back-to-orders">
                    <ArrowLeft weight="bold" className="w-4 h-4 mr-2" />
                    {t('backToOrders')}
                  </Button>
                </div>
              )}
            </CardContent>
          </Card>

          {/* RIGHT: Order Summary */}
          <div className="space-y-4">
            {/* Vehicle info */}
            <Card className="bg-card border-border" data-testid="summary-vehicle">
              <CardContent className="p-5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
                  <CarProfile weight="bold" className="w-3.5 h-3.5" />
                  {t('vehicle')}
                </label>
                <div className="space-y-2">
                  {[
                    { l: 'Hersteller', v: od.mfr },
                    od.ser && { l: 'Baureihe', v: od.ser },
                    od.mod && { l: 'Modell', v: od.mod },
                    od.eng && { l: 'Motor', v: od.eng },
                  ].filter(Boolean).map(item => (
                    <div key={item.l} className="flex justify-between py-1.5 border-b border-border/40 last:border-0">
                      <span className="text-xs text-muted-foreground">{item.l}</span>
                      <span className="text-xs font-semibold text-foreground">{item.v}</span>
                    </div>
                  ))}
                  {od.ecuVal && (
                    <div className="flex justify-between py-1.5">
                      <span className="text-xs text-muted-foreground">{t('ecu')}</span>
                      <span className="text-xs font-mono font-semibold text-foreground">{od.ecuVal}</span>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Tuning info */}
            <Card className="bg-card border-border" data-testid="summary-tuning">
              <CardContent className="p-5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
                  <Lightning weight="bold" className="w-3.5 h-3.5" />
                  {t('tuning')}
                </label>
                <div className="space-y-2">
                  {od.selectedStage && (
                    <div className="flex justify-between py-1.5 border-b border-border/40">
                      <span className="text-xs text-muted-foreground">{t('stage')}</span>
                      <Badge className="bg-primary/15 text-primary border-primary/30 text-xs">{stageNames[od.selectedStage] || od.selectedStage}</Badge>
                    </div>
                  )}
                  {od.selectedStage === 'gearbox' && od.selectedGearboxStage && (
                    <div className="flex justify-between py-1.5 border-b border-border/40">
                      <span className="text-xs text-muted-foreground">{t('gearbox')}</span>
                      <Badge className="bg-blue-500/15 text-blue-400 border-blue-500/30 text-xs">{od.selectedGearboxStage}</Badge>
                    </div>
                  )}
                  {od.selectedOptions && od.selectedOptions.length > 0 && (
                    <div className="py-1.5 border-b border-border/40">
                      <span className="text-xs text-muted-foreground block mb-2">{t('options')}</span>
                      <div className="flex flex-wrap gap-1.5">
                        {od.selectedOptions.map(id => (
                          <span key={id} className="text-[10px] font-semibold bg-secondary/80 border border-border px-2 py-1 rounded-sm text-foreground">
                            {optionNames[id] || id}
                          </span>
                        ))}
                      </div>
                    </div>
                  )}
                  <div className="flex justify-between py-1.5">
                    <span className="text-xs text-muted-foreground">{t('credits')}</span>
                    <span className="text-sm font-bold text-primary">{od.totalCredits} Credits</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* File info */}
            <Card className="bg-card border-border" data-testid="summary-file">
              <CardContent className="p-5">
                <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase mb-3">
                  <Upload weight="bold" className="w-3.5 h-3.5" />
                  {t('file')}
                </label>
                <div className="space-y-2">
                  {od.fileName && (
                    <div className="py-1.5 border-b border-border/40">
                      <span className="text-xs text-muted-foreground block mb-1">{t('file')}</span>
                      <span className="text-xs font-mono font-semibold text-foreground break-all">{od.fileName}</span>
                    </div>
                  )}
                  {[
                    od.readingDevice && { l: t('device'), v: od.readingDevice },
                    od.readingMethod && { l: t('method'), v: od.readingMethod },
                    od.readingType && { l: t('type'), v: od.readingType },
                    od.masterSlave && { l: t('ms'), v: od.masterSlave },
                    od.priority && { l: t('priority'), v: od.priority },
                  ].filter(Boolean).map(item => (
                    <div key={item.l} className="flex justify-between py-1.5 border-b border-border/40 last:border-0">
                      <span className="text-xs text-muted-foreground">{item.l}</span>
                      <span className="text-xs font-semibold text-foreground">{item.v}</span>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}
