import { useState, useEffect } from 'react';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '../components/ui/dropdown-menu';
import { cn } from '../lib/utils';
import { 
  CurrencyCircleDollar, 
  ClockCounterClockwise, 
  CheckCircle, 
  Wallet,
  ArrowRight,
  CarProfile,
  Package,
  Clock,
  Ticket,
  Warning,
  ChatCircle,
  Newspaper,
  CaretLeft,
  CaretRight,
  CaretDown,
  Circle,
  Upload,
  Download,
  Cpu,
  Headset,
  XCircle
} from '@phosphor-icons/react';
import { useNavigate } from 'react-router-dom';

const liveStepperTranslations = {
  en: {
    currentOrder: 'Current Order',
    details: 'Details',
    orderStatus: 'Order Status',
    step_received: 'File received',
    step_check_db: 'Check Database',
    step_no_match: 'No match found',
    step_manual: 'Manual processing',
    step_support_notified: 'Support notified',
    support_handling: 'Being handled by support',
    support_info: 'This configuration needs manual creation. Our support team has been notified.',
    support_time: 'Estimated processing time: 30-60 minutes',
  },
  de: {
    currentOrder: 'Aktueller Auftrag',
    details: 'Details',
    orderStatus: 'Auftragsstatus',
    step_received: 'Datei empfangen',
    step_check_db: 'Check Database',
    step_no_match: 'Kein Match gefunden',
    step_manual: 'Manuelle Bearbeitung',
    step_support_notified: 'Support benachrichtigt',
    support_handling: 'Wird vom Support bearbeitet',
    support_info: 'Diese Konfiguration muss manuell erstellt werden. Unser Support-Team wurde benachrichtigt.',
    support_time: 'Geschätzte Bearbeitungszeit: 30-60 Minuten',
  },
};

const AUTOTUNER_MOCK_NEWS = [
  {
    type: 'product',
    date: '3. Apr 2026',
    imageUrl: 'https://www.autotuner.com/cdn/shop/articles/20260328_Tool_PSA_MG1CS042_BENCH_After_Mail-in_Unlock_-_Horizontal_86545368-04fe-4a93-b21e-dd216b4120c5.jpg?v=1775208019&width=1920',
    articleUrl: 'https://www.autotuner.com/de/blogs/neuigkeiten/bosch-mg1cs042',
    title: {
      de: 'Bosch MG1CS042',
      en: 'Bosch MG1CS042',
    },
    content: {
      de: 'Neue Tool-Veröffentlichung von AutoTuner. Direkter Link zum Originalartikel inklusive Bildvorschau.',
      en: 'New AutoTuner tool release with direct article link and preview image.',
    },
  },
  {
    type: 'product',
    date: '30. Mär 2026',
    imageUrl: 'https://www.autotuner.com/cdn/shop/articles/20260326_Tool_PSA_MD1CS003_BENCH_After_Mail-in_unlock_-_Horizontal_c61ac00b-0ee5-4f85-b736-c0a02f9d8a02.jpg?v=1775201767&width=1920',
    articleUrl: 'https://www.autotuner.com/de/blogs/neuigkeiten/bosch-md1cs003',
    title: {
      de: 'Bosch MD1CS003',
      en: 'Bosch MD1CS003',
    },
    content: {
      de: 'Aktuelle AutoTuner-News aus der Tool-Kategorie mit verlinktem Originalbeitrag.',
      en: 'Current AutoTuner news from the tool category with direct source link.',
    },
  },
  {
    type: 'product',
    date: '25. Mär 2026',
    imageUrl: 'https://www.autotuner.com/cdn/shop/articles/20260324_Tool_VAG_ALx52_2022_Gen2_BENCH_NR_-_Horizontal_3fd58d97-3d9b-42c9-80fa-b31fdfa8951d.jpg?v=1774457480&width=1920',
    articleUrl: 'https://www.autotuner.com/de/blogs/neuigkeiten/zf-alx52',
    title: {
      de: 'ZF ALx52 2022+/Gen2',
      en: 'ZF ALx52 2022+/Gen2',
    },
    content: {
      de: 'Mock-News aus der AutoTuner-Quelle für das Dashboard, bis echte News aus dem Backend kommen.',
      en: 'Dashboard mock news pulled from AutoTuner until backend-driven news are available.',
    },
  },
  {
    type: 'product',
    date: '25. Mär 2026',
    imageUrl: 'https://www.autotuner.com/cdn/shop/articles/20260317_Tool_VAG_DQ38x_2022_BENCH_OBD_After_BENCH_Unlock_-_Horizontal_05da237a-2ed1-4a67-93c7-ee9ddbe0524f.jpg?v=1774451343&width=1920',
    articleUrl: 'https://www.autotuner.com/de/blogs/neuigkeiten/bosch-dq38x',
    title: {
      de: 'Bosch DQ38x 2022+',
      en: 'Bosch DQ38x 2022+',
    },
    content: {
      de: 'Weitere AutoTuner-Tool-News mit Bildvorschau und direktem Sprung zum Beitrag.',
      en: 'Additional AutoTuner tool news with preview image and direct article link.',
    },
  },
  {
    type: 'product',
    date: '16. Mär 2026',
    imageUrl: 'https://www.autotuner.com/cdn/shop/articles/20260311_Tool_Ford_SID212_Bench_OBD_-_Horizontal_1039262b-584e-4552-b613-7bb821e097a4.jpg?v=1773673637&width=1920',
    articleUrl: 'https://www.autotuner.com/de/blogs/neuigkeiten/continental-sid212-sid212evo-obd-after-bench-unlock',
    title: {
      de: 'Continental SID212 / SID212evo',
      en: 'Continental SID212 / SID212evo',
    },
    content: {
      de: 'AutoTuner-Artikel aus der Tool-Rubrik als visuelle Mock-News für das Portal-Dashboard.',
      en: 'AutoTuner tool-category article used as visual mock news for the portal dashboard.',
    },
  },
];

const ALIENTECH_MOCK_NEWS = [
  {
    type: 'partner',
    date: '3. April 2026',
    imageUrl: 'https://www.alientech-tools.com/wp-content/uploads/2026/04/Preview_DL_KESS3-768x510.jpg',
    articleUrl: 'https://www.alientech-tools.com/de/news/datalogger-bmw-specific-protocol/',
    title: {
      de: 'Neue spezifische Protokolle für KESS3 DataLogger: BMW Gruppe.',
      en: 'New dedicated protocols for KESS3 DataLogger: BMW Group.',
    },
    content: {
      de: 'Die erste große Veröffentlichung einer langen Reihe von Entwicklungen für die DataLogger-Funktion.',
      en: 'The first major release in a long series of developments for the DataLogger feature.',
    },
  },
  {
    type: 'partner',
    date: '30. März 2026',
    imageUrl: 'https://www.alientech-tools.com/wp-content/uploads/2026/03/3.02_Preview-768x509.jpg',
    articleUrl: 'https://www.alientech-tools.com/de/news/upgrade-3-02/',
    title: {
      de: 'Neues BENCH-Protokoll Marelli 11GF: 1.0 Hybrid.',
      en: 'New BENCH protocol Marelli 11GF: 1.0 Hybrid.',
    },
    content: {
      de: 'KESS3 erweitert die Unterstützung für Steuergeräte, die in weit verbreiteten Hybrid-Kleinwagen der FCA-Gruppe verbaut sind.',
      en: 'KESS3 expands support for ECUs installed in popular FCA hybrid city cars.',
    },
  },
  {
    type: 'partner',
    date: '27. März 2026',
    imageUrl: 'https://www.alientech-tools.com/wp-content/uploads/2026/03/MD1_Preview-1-768x509.jpg',
    articleUrl: 'https://www.alientech-tools.com/de/news/upgrade-3-01/',
    title: {
      de: 'KESS3 führt das BENCH-Protokoll für Bosch MD1CS069 auf Iveco Daily ein.',
      en: 'KESS3 introduces the BENCH protocol for Bosch MD1CS069 on Iveco Daily.',
    },
    content: {
      de: 'Eine stark verbreitete Anwendung im Bereich leichter Nutzfahrzeuge kann jetzt direkt über den ECU-Anschluss bearbeitet werden.',
      en: 'A widely used light commercial vehicle application can now be handled directly via the ECU connector.',
    },
  },
  {
    type: 'partner',
    date: '23. März 2026',
    imageUrl: 'https://www.alientech-tools.com/wp-content/uploads/2026/03/DataLogger_Preview-768x510.jpg',
    articleUrl: 'https://www.alientech-tools.com/de/news/upgrade-3-00-datalogger/',
    title: {
      de: 'KESS3 führt den DataLogger ein: Analyse von ECU-Parametern unter realen Bedingungen.',
      en: 'KESS3 introduces DataLogger: analysis of ECU parameters in real conditions.',
    },
    content: {
      de: 'Verstehen Sie das Fahrzeugverhalten für maximale Effizienz in der Werkstatt.',
      en: 'Understand vehicle behavior for maximum workshop efficiency.',
    },
  },
  {
    type: 'partner',
    date: '20. März 2026',
    imageUrl: 'https://www.alientech-tools.com/wp-content/uploads/2026/03/2.99_Preview-768x510.jpg',
    articleUrl: 'https://www.alientech-tools.com/de/news/upgrade-2-99/',
    title: {
      de: 'Neues OBD-Protokoll für Bosch ZF8HP75 TCU – Alfa Romeo Quadrifoglio Verde.',
      en: 'New OBD protocol for Bosch ZF8HP75 TCU – Alfa Romeo Quadrifoglio Verde.',
    },
    content: {
      de: 'Die Entwicklung an ZF8HP-Automatikgetrieben in hochperformanten Anwendungen wird weiter ausgebaut.',
      en: 'Development for ZF8HP automatic transmissions in high-performance applications continues to expand.',
    },
  },
];

// Opening hours configuration
const openingHours = {
  1: { open: '08:00', close: '18:00' }, // Monday
  2: { open: '08:00', close: '18:00' }, // Tuesday
  3: { open: '08:00', close: '18:00' }, // Wednesday
  4: { open: '08:00', close: '18:00' }, // Thursday
  5: { open: '08:00', close: '18:00' }, // Friday
  6: { open: '09:00', close: '13:00' }, // Saturday
  0: null, // Sunday - closed
};

const isBusinessOpen = () => {
  const now = new Date();
  const day = now.getDay();
  const hours = openingHours[day];
  
  if (!hours) return false;
  
  const currentTime = now.getHours() * 60 + now.getMinutes();
  const [openH, openM] = hours.open.split(':').map(Number);
  const [closeH, closeM] = hours.close.split(':').map(Number);
  const openTime = openH * 60 + openM;
  const closeTime = closeH * 60 + closeM;
  
  return currentTime >= openTime && currentTime < closeTime;
};

const resolveBrandLogoUrl = (value) => {
  const raw = String(value ?? '').trim();
  if (raw === '') {
    return '';
  }

  if (raw.startsWith('http://') || raw.startsWith('https://')) {
    return raw;
  }

  return `https://portal.dct-files.de/${raw.replace(/^\/+/, '')}`;
};

const splitVehicleLabel = (value) => {
  const parts = String(value ?? '')
    .split(' - ')
    .map((part) => part.trim())
    .filter(Boolean);

  if (parts.length <= 1) {
    return { primary: String(value ?? '').trim() || '—', secondary: '' };
  }

  if (parts.length === 2) {
    return {
      primary: parts[0],
      secondary: parts[1],
    };
  }

  return {
    primary: parts.slice(0, 2).join(' - '),
    secondary: parts.slice(2).join(' - '),
  };
};

// Opening Hours Component
const OpeningHoursCard = ({ language, className }) => {
  const isOpen = isBusinessOpen();
  const now = new Date();
  const currentDay = now.getDay();
  
  const dayNames = language === 'de' 
    ? ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa']
    : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  
  const closedText = language === 'de' ? 'Geschlossen' : 'Closed';
  const openText = language === 'de' ? 'Geöffnet' : 'Open';
  const hoursTitle = language === 'de' ? 'Öffnungszeiten' : 'Opening Hours';

  return (
    <Card className={cn("h-full bg-card border-border flex flex-col", className)} data-testid="opening-hours-card">
      <CardHeader className="border-b border-border pb-4">
        <div className="flex items-center justify-between">
          <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
            <Clock weight="fill" className="w-5 h-5 text-primary" />
            {hoursTitle}
          </CardTitle>
          <Badge className={`${isOpen ? 'bg-green-500/20 text-green-600 dark:text-green-400 border-green-500/30' : 'bg-red-500/20 text-red-600 dark:text-red-400 border-red-500/30'} border`}>
            <span className={`w-2 h-2 rounded-full mr-2 ${isOpen ? 'bg-green-500 animate-pulse' : 'bg-red-500'}`} />
            {isOpen ? openText : closedText}
          </Badge>
        </div>
      </CardHeader>
      <CardContent className="flex-1 p-4">
        <div className="space-y-2 text-sm">
          {/* Mo-Fr */}
          <div className={`flex justify-between py-1.5 px-2 rounded-sm ${currentDay >= 1 && currentDay <= 5 ? 'bg-primary/10 border border-primary/20' : ''}`}>
            <span className={currentDay >= 1 && currentDay <= 5 ? 'text-foreground font-medium' : 'text-muted-foreground'}>
              {language === 'de' ? 'Mo - Fr' : 'Mon - Fri'}
            </span>
            <span className={currentDay >= 1 && currentDay <= 5 ? 'text-foreground font-medium' : 'text-muted-foreground'}>
              08:00 - 18:00
            </span>
          </div>
          {/* Sa */}
          <div className={`flex justify-between py-1.5 px-2 rounded-sm ${currentDay === 6 ? 'bg-primary/10 border border-primary/20' : ''}`}>
            <span className={currentDay === 6 ? 'text-foreground font-medium' : 'text-muted-foreground'}>
              {language === 'de' ? 'Sa' : 'Sat'}
            </span>
            <span className={currentDay === 6 ? 'text-foreground font-medium' : 'text-muted-foreground'}>
              09:00 - 13:00
            </span>
          </div>
          {/* So */}
          <div className={`flex justify-between py-1.5 px-2 rounded-sm ${currentDay === 0 ? 'bg-red-500/10 border border-red-500/20' : ''}`}>
            <span className={currentDay === 0 ? 'text-red-600 dark:text-red-400 font-medium' : 'text-muted-foreground'}>
              {language === 'de' ? 'So' : 'Sun'}
            </span>
            <span className={currentDay === 0 ? 'text-red-600 dark:text-red-400 font-medium' : 'text-muted-foreground'}>
              {closedText}
            </span>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};

// News Carousel Component
const NewsCarousel = ({ language, newsItems, className }) => {
  const [currentIndex, setCurrentIndex] = useState(0);
  
  const newsTitle = language === 'de' ? 'News' : 'News';
  const hasNews = Array.isArray(newsItems) && newsItems.length > 0;
  
  // Auto-advance carousel
  useEffect(() => {
    if (!hasNews) {
      return undefined;
    }

    const timer = setInterval(() => {
      setCurrentIndex((prev) => (prev + 1) % newsItems.length);
    }, 8000);
    return () => clearInterval(timer);
  }, [hasNews, newsItems]);

  const goToNext = () => {
    if (!hasNews) {
      return;
    }
    setCurrentIndex((prev) => (prev + 1) % newsItems.length);
  };

  const goToPrev = () => {
    if (!hasNews) {
      return;
    }
    setCurrentIndex((prev) => (prev - 1 + newsItems.length) % newsItems.length);
  };

  const currentNews = hasNews ? newsItems[currentIndex] : null;

  const getTypeColor = (type) => {
    const colors = {
      product: 'bg-green-500/20 text-green-400 border-green-500/30',
      maintenance: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
      promotion: 'bg-primary/20 text-primary border-primary/30',
      partner: 'bg-blue-500/20 text-blue-300 border-blue-500/30',
    };
    return colors[type] || colors.product;
  };

  const getTypeLabel = (type) => {
    const labels = {
      product: language === 'de' ? 'Produkt' : 'Product',
      maintenance: language === 'de' ? 'Wartung' : 'Maintenance',
      promotion: language === 'de' ? 'Aktion' : 'Promotion',
      partner: language === 'de' ? 'Partner-News' : 'Partner news',
    };
    return labels[type] || type;
  };

  const articleLabel = language === 'de' ? 'Zum Artikel' : 'Open article';

  return (
    <Card className={cn("bg-card border-border flex flex-col self-start", className)} data-testid="news-carousel">
      <CardHeader className="border-b border-border pb-4">
        <div className="flex items-center justify-between">
          <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
            <Newspaper weight="fill" className="w-5 h-5 text-primary" />
            {newsTitle}
          </CardTitle>
          <div className="flex items-center gap-1">
            <Button 
              variant="ghost" 
              size="icon"
              className="h-7 w-7 text-muted-foreground hover:text-foreground hover:bg-secondary"
              onClick={goToPrev}
              data-testid="news-prev-btn"
            >
              <CaretLeft weight="bold" className="w-4 h-4" />
            </Button>
            <Button 
              variant="ghost" 
              size="icon"
              className="h-7 w-7 text-muted-foreground hover:text-foreground hover:bg-secondary"
              onClick={goToNext}
              data-testid="news-next-btn"
            >
              <CaretRight weight="bold" className="w-4 h-4" />
            </Button>
          </div>
        </div>
      </CardHeader>
      <CardContent className="p-4">
        {hasNews && currentNews ? (
          <>
            <div className="space-y-3">
              {currentNews.imageUrl ? (
                <a
                  href={currentNews.articleUrl || '#'}
                  target="_blank"
                  rel="noreferrer"
                  className="block overflow-hidden rounded-sm border border-white/10 bg-white"
                >
                  <img
                    src={currentNews.imageUrl}
                    alt={currentNews.title?.[language] ?? currentNews.title?.de ?? 'News'}
                    className="max-h-[34rem] w-full object-contain object-top"
                    loading="lazy"
                  />
                </a>
              ) : null}
              <div className="flex items-center justify-between">
                <Badge variant="outline" className={getTypeColor(currentNews.type)}>
                  {getTypeLabel(currentNews.type)}
                </Badge>
                <span className="text-xs text-muted-foreground">{currentNews.date}</span>
              </div>
              <h4 className="font-semibold text-foreground">
                {currentNews.title?.[language] ?? ''}
              </h4>
              <p className="text-sm text-muted-foreground leading-relaxed">
                {currentNews.content?.[language] ?? ''}
              </p>
              {currentNews.articleUrl ? (
                <Button asChild variant="outline" size="sm" className="border-border bg-secondary/30">
                  <a href={currentNews.articleUrl} target="_blank" rel="noreferrer">
                    {articleLabel}
                    <ArrowRight weight="bold" className="ml-1 h-4 w-4" />
                  </a>
                </Button>
              ) : null}
            </div>
            
            {/* Dots Indicator */}
            <div className="flex items-center justify-center gap-2 mt-4 pt-4 border-t border-white/5">
              {newsItems.map((_, index) => (
                <button
                  key={index}
                  onClick={() => setCurrentIndex(index)}
                  className="p-1"
                  data-testid={`news-dot-${index}`}
                >
                  <Circle 
                    weight={index === currentIndex ? 'fill' : 'regular'} 
                    className={`w-2 h-2 transition-colors ${
                      index === currentIndex ? 'text-primary' : 'text-muted-foreground'
                    }`} 
                  />
                </button>
              ))}
            </div>
          </>
        ) : (
          <div className="text-sm text-muted-foreground">
            {language === 'de' ? 'Aktuell keine News verfügbar.' : 'No news available right now.'}
          </div>
        )}
      </CardContent>
    </Card>
  );
};

// Current Tickets Component
const CurrentTicketsCard = ({ language, navigate, tickets, className }) => {
  const openTickets = Array.isArray(tickets) ? tickets.slice(0, 3) : [];
  
  const getStatusConfig = (status) => {
    const configs = {
      open: { bg: 'bg-yellow-500/20', text: 'text-yellow-400', border: 'border-yellow-500/30', icon: Clock },
      answered: { bg: 'bg-green-500/20', text: 'text-green-400', border: 'border-green-500/30', icon: ChatCircle },
    };
    return configs[status] || configs.open;
  };

  const ticketsTitle = language === 'de' ? 'Aktuelle Tickets' : 'Current Tickets';
  const noTickets = language === 'de' ? 'Keine offenen Tickets' : 'No open tickets';
  const viewAll = language === 'de' ? 'Alle ansehen' : 'View all';

  return (
    <Card className={cn("h-full bg-card border-border flex flex-col", className)} data-testid="current-tickets-card">
      <CardHeader className="border-b border-border pb-4">
        <div className="flex items-center justify-between">
          <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
            <Ticket weight="fill" className="w-5 h-5 text-primary" />
            {ticketsTitle}
            {openTickets.length > 0 && (
              <Badge className="bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 ml-2">
                {openTickets.length}
              </Badge>
            )}
          </CardTitle>
          <Button 
            variant="ghost" 
            size="sm"
            className="text-muted-foreground hover:text-foreground"
            onClick={() => { window.location.href = '/tickets'; }}
          >
            {viewAll}
            <ArrowRight weight="bold" className="w-4 h-4 ml-1" />
          </Button>
        </div>
      </CardHeader>
      <CardContent className="flex-1 overflow-auto p-0">
        {openTickets.length > 0 ? (
          <div className="divide-y divide-border">
            {openTickets.map((ticket) => {
              const statusConfig = getStatusConfig(ticket.status);
              const StatusIcon = statusConfig.icon;
              
              return (
                <div 
                  key={ticket.id}
                  className="p-3 hover:bg-secondary/50 transition-colors duration-200 cursor-pointer"
                  onClick={() => { window.location.href = '/tickets'; }}
                >
                  <div className="flex items-center gap-3">
                    <div className={`w-8 h-8 rounded-sm flex items-center justify-center flex-shrink-0 ${statusConfig.bg}`}>
                      <StatusIcon weight="fill" className={`w-4 h-4 ${statusConfig.text}`} />
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2">
                        <span className="font-mono text-xs text-primary">{ticket.id}</span>
                        {ticket.priority === 'high' && (
                          <Warning weight="fill" className="w-3 h-3 text-red-400" />
                        )}
                      </div>
                      <p className="text-sm text-foreground truncate">{ticket.subject}</p>
                    </div>
                    <ArrowRight weight="bold" className="w-4 h-4 text-muted-foreground flex-shrink-0" />
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <div className="p-4 text-center text-sm text-muted-foreground">
            {noTickets}
          </div>
        )}
      </CardContent>
    </Card>
  );
};

// Horizontal Progress Component
const HorizontalOrderProgress = ({ order, language, navigate, className }) => {
  const text = liveStepperTranslations[language] || liveStepperTranslations.en;
  const isCancelled = order.status === 'cancelled';

  if (isCancelled) return null;

  const status = String(order?.status || '').toLowerCase();
  const isCompleted = status === 'completed';

  const steps = isCompleted
    ? [
        { id: 'received', label: text.step_received, icon: Upload, status: 'done' },
        { id: 'check', label: text.step_check_db, icon: Cpu, status: 'done' },
        { id: 'match', label: language === 'de' ? 'File erstellt' : 'File created', icon: CheckCircle, status: 'done' },
        { id: 'complete', label: language === 'de' ? 'Download bereit' : 'Download ready', icon: Download, status: 'done' },
      ]
    : [
        { id: 'received', label: text.step_received, icon: Upload, status: 'done' },
        { id: 'check', label: text.step_check_db, icon: Cpu, status: 'done' },
        { id: 'nomatch', label: text.step_no_match, icon: XCircle, status: 'error' },
        { id: 'manual', label: text.step_manual, sub: text.step_support_notified, icon: Headset, status: 'active' },
      ];

  return (
    <div className={className}>
      <Card className="bg-card border-border" data-testid="current-order-progress">
        <CardHeader className="border-b border-border pb-4">
          <div className="flex items-center justify-between">
            <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
              <Package weight="fill" className="w-5 h-5 text-primary" />
              {text.currentOrder}
            </CardTitle>
            <Button
              variant="ghost"
              size="sm"
              className="text-muted-foreground hover:text-foreground"
              onClick={() => {
                const target = order?.id ? `/orders/${encodeURIComponent(order.id)}` : '/orders';
                window.location.href = target;
              }}
            >
              {text.details}
              <ArrowRight weight="bold" className="w-4 h-4 ml-1" />
            </Button>
          </div>
        </CardHeader>
        <CardContent className="p-6">
          {/* Order Info */}
          <div className="mb-6 flex items-center justify-between">
            <div className="flex items-center gap-4">
              <div className="w-12 h-12 rounded-sm bg-white/5 border border-white/10 flex items-center justify-center overflow-hidden">
                {resolveBrandLogoUrl(order.brandLogoUrl) !== '' ? (
                  <img
                    src={resolveBrandLogoUrl(order.brandLogoUrl)}
                    alt={order.vehicleLabel || 'Brand'}
                    className="max-h-8 max-w-8 object-contain"
                    loading="lazy"
                  />
                ) : (
                  <CarProfile weight="fill" className="w-6 h-6 text-primary" />
                )}
              </div>
              <div>
                <h4 className="font-heading font-bold text-lg leading-snug text-foreground">
                  {order.vehicleLabel || (language === 'de' ? 'Fahrzeug' : 'Vehicle')}
                </h4>
                <p className="mt-1 text-sm text-muted-foreground">
                  #{order.id} · {order.statusLabel || order.status}
                </p>
              </div>
            </div>
            <p className="text-sm font-medium text-foreground">
              {order.timelineLabel || ''}
            </p>
          </div>

          <div className="mt-10" data-testid="stepper-row">
            <div className="relative">
              <div className="absolute left-0 right-0 top-5 h-[2px] bg-border" />
              <div className="relative flex items-start justify-between">
                {steps.map((step) => {
                  const Icon = step.icon;
                  const iconClasses = cn(
                    "h-5 w-5",
                    step.status === 'done' && "text-green-500",
                    step.status === 'error' && "text-red-400",
                    step.status === 'active' && "text-yellow-400",
                  );

                  return (
                    <div
                      key={step.id}
                      className="flex w-10 flex-shrink-0 justify-center"
                      data-testid={`stepper-${step.id}`}
                    >
                      <div className={cn(
                        "relative z-10 flex h-10 w-10 items-center justify-center rounded-full border-2 bg-card transition-all",
                        step.status === 'done' && "border-border",
                        step.status === 'error' && "border-border",
                        step.status === 'active' && "border-border",
                      )}>
                        {step.status === 'done' && <CheckCircle weight="fill" className={iconClasses} />}
                        {step.status === 'error' && <Icon weight="fill" className={iconClasses} />}
                        {step.status === 'active' && <Icon weight="fill" className={iconClasses} />}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

            <div className="mt-7 grid grid-cols-4 gap-6">
              {steps.map((step) => (
                <div key={`${step.id}-label`} className="flex flex-col items-center text-center">
                  <p className="max-w-[9.5rem] text-xs font-semibold leading-tight text-foreground">
                    {step.label}
                  </p>
                  {step.sub && (
                    <p className="mt-1 max-w-[9.5rem] text-[10px] font-medium leading-tight text-yellow-400">
                      {step.sub}
                    </p>
                  )}
                </div>
              ))}
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default function Dashboard() {
  const { t, language } = useLanguage();
  const navigate = useNavigate();
  const dashboardBootstrap = window?.portalReactBootstrap?.dashboard ?? null;
  const credits = Number.isFinite(dashboardBootstrap?.credits) ? dashboardBootstrap.credits : 0;
  const pendingOrders = Number.isFinite(dashboardBootstrap?.openJobs) ? dashboardBootstrap.openJobs : 0;
  const completedFiles = Number.isFinite(dashboardBootstrap?.completedJobs) ? dashboardBootstrap.completedJobs : 0;
  const jobsThisMonth = Number.isFinite(dashboardBootstrap?.jobsThisMonth) ? dashboardBootstrap.jobsThisMonth : 0;
  const currentJob = dashboardBootstrap?.currentJob ?? null;
  const ticketItems = Array.isArray(dashboardBootstrap?.tickets) ? dashboardBootstrap.tickets : [];
  const newsItems = Array.isArray(dashboardBootstrap?.news) && dashboardBootstrap.news.length > 0
    ? dashboardBootstrap.news
    : [...AUTOTUNER_MOCK_NEWS, ...ALIENTECH_MOCK_NEWS];
  const recentActivity = Array.isArray(dashboardBootstrap?.recentJobs) && dashboardBootstrap.recentJobs.length > 0
    ? dashboardBootstrap.recentJobs.slice(0, 5).map((job, index) => ({
      id: job.id ?? `job-${index}`,
      orderNumber: job.orderNumber ?? job.id ?? `job-${index}`,
      type: 'file',
      vehicle: job.vehicleLabel ?? (language === 'de' ? 'Fahrzeug' : 'Vehicle'),
      stageLabel: job.stageLabel ?? '',
      status: job.status ?? 'pending',
      date: job.timelineLabel ?? job.createdAt ?? '',
      credits: Number.isFinite(job.credits) ? job.credits : null,
      options: Array.isArray(job.options) ? job.options.filter(Boolean).slice(0, 4) : [],
      originalDownloadUrl: job.files?.originalDownloadUrl ?? '',
      modifiedDownloadUrl: job.files?.modifiedDownloadUrl ?? '',
    }))
    : [];
  const parkedOrders = Array.isArray(dashboardBootstrap?.parkedOrders) ? dashboardBootstrap.parkedOrders : [];
  const parkedOrderItems = parkedOrders.slice(0, 4);
  const totalSpentValue = `${jobsThisMonth} ${language === 'de' ? 'neue Dateien' : 'new files'}`;
  const activityText = {
    details: language === 'de' ? 'Details' : 'Details',
    openOrder: language === 'de' ? 'Auftrag öffnen' : 'Open order',
    originalFile: language === 'de' ? 'Originaldatei' : 'Original file',
    modifiedFile: language === 'de' ? 'Modifizierte Datei' : 'Modified file',
    filesMenu: language === 'de' ? 'Dateien' : 'Files',
    tuning: language === 'de' ? 'Tuning' : 'Tuning',
    options: language === 'de' ? 'Optionen' : 'Options',
    dateOrder: language === 'de' ? 'Datum / Auftrag' : 'Date / Order',
    vehicle: language === 'de' ? 'Fahrzeug' : 'Vehicle',
    files: language === 'de' ? 'Dateien' : 'Files',
    credits: 'Credits',
    status: language === 'de' ? 'Status' : 'Status',
    allOrders: language === 'de' ? 'Alle Aufträge' : 'All orders',
  };

  const stats = [
    { 
      label: t('availableCredits'), 
      value: credits.toLocaleString(), 
      icon: CurrencyCircleDollar,
      valueColor: 'text-foreground',
      iconColor: 'text-primary',
      iconBgColor: 'bg-primary/10'
    },
    {
      label: t('pendingOrders'),
      value: pendingOrders,
      icon: ClockCounterClockwise,
      valueColor: 'text-foreground',
      iconColor: 'text-yellow-500',
      iconBgColor: 'bg-yellow-500/10'
    },
    {
      label: t('completedFiles'),
      value: completedFiles,
      icon: CheckCircle,
      valueColor: 'text-foreground',
      iconColor: 'text-green-500',
      iconBgColor: 'bg-green-500/10'
    },
    { 
      label: t('totalSpent'), 
      value: totalSpentValue, 
      icon: Wallet,
      valueColor: 'text-muted-foreground',
      iconColor: 'text-muted-foreground',
      iconBgColor: 'bg-secondary'
    },
  ];

  const getStatusBadge = (status) => {
    const styles = {
      completed: 'bg-green-500/20 text-green-400 border-green-500/30',
      processing: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
      parked: 'bg-orange-500/20 text-orange-300 border-orange-500/30',
      pending: 'bg-blue-500/20 text-blue-300 border-blue-500/30',
      cancelled: 'bg-red-500/20 text-red-400 border-red-500/30',
    };
    return styles[status] || styles.pending;
  };

  const getStatusLabel = (status) => {
    const labels = language === 'de'
      ? {
          completed: 'Abgeschlossen',
          processing: 'In Bearbeitung',
          parked: 'Geparkt',
          pending: 'Eingegangen',
          cancelled: 'Storniert',
        }
      : {
          completed: 'Completed',
          processing: 'Processing',
          parked: 'Parked',
          pending: 'Received',
          cancelled: 'Cancelled',
        };

    return labels[status] || t(status);
  };

  const getStatusIcon = (status) => {
    const iconClasses = {
      completed: 'text-green-400',
      processing: 'text-yellow-400',
      parked: 'text-orange-300',
      pending: 'text-blue-300',
      cancelled: 'text-red-400',
    };

    return iconClasses[status] || iconClasses.pending;
  };

  return (
    <DashboardLayout title={t('dashboard')}>
      <div className="space-y-6">
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-12">
          <div className="xl:col-span-8">
            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4" data-testid="stats-grid">
              {stats.map((stat, index) => {
                const Icon = stat.icon;
                return (
                  <Card
                    key={index}
                    className="bg-card border-border hover:border-primary/30 transition-colors duration-200"
                    data-testid={`stat-card-${index}`}
                  >
                    <CardContent className="p-5 md:p-6">
                      <div className="flex items-start justify-between gap-3">
                        <div className="min-w-0">
                          <p className="text-xs uppercase tracking-[0.14em] text-muted-foreground">{stat.label}</p>
                          <p className={`mt-3 font-heading text-2xl font-bold leading-snug ${stat.valueColor}`}>
                            {stat.value}
                          </p>
                        </div>
                        <div className={`flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-sm ${stat.iconBgColor}`}>
                          <Icon weight="fill" className={`h-5 w-5 ${stat.iconColor}`} />
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                );
              })}
            </div>
          </div>

          <OpeningHoursCard language={language} className="xl:col-span-4" />
        </div>

        <div className="grid grid-cols-1 items-start gap-6 xl:grid-cols-4">
          {currentJob && (
            <HorizontalOrderProgress
              order={currentJob}
              language={language}
              navigate={navigate}
              className="xl:col-span-2"
            />
          )}

          <CurrentTicketsCard
            language={language}
            navigate={navigate}
            tickets={ticketItems}
            className="xl:col-span-1"
          />

          {parkedOrders.length > 0 && (
            <Card className="bg-card border-border xl:col-span-1" data-testid="parked-orders-card">
              <CardHeader className="border-b border-border pb-4">
                <div className="flex items-center justify-between gap-3">
                  <div className="flex items-center gap-2">
                    <Package weight="fill" className="w-5 h-5 text-primary" />
                    <CardTitle className="font-heading font-semibold text-lg">
                      {language === 'de' ? 'Geparkte Aufträge' : 'Parked orders'}
                    </CardTitle>
                  </div>
                  <Badge className="bg-amber-500/20 text-amber-400 border border-amber-500/30">
                    {parkedOrders.length}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-3 p-4">
                {parkedOrderItems.map((order) => (
                  <div key={order.id} className="rounded-sm border border-white/5 bg-white/[0.02] p-3">
                    <div className="space-y-1.5">
                      <p className="font-mono text-[11px] text-primary">{order.orderNumber}</p>
                      <p className="line-clamp-2 text-sm font-semibold text-foreground">
                        {order.vehicleLabel || '—'}
                      </p>
                      <div className="flex flex-wrap gap-2 text-[11px] text-muted-foreground">
                        <span>{order.stageLabel || (language === 'de' ? 'Unbekannt' : 'Unknown')}</span>
                        <span>•</span>
                        <span>
                          {order.updatedAt ? new Date(order.updatedAt).toLocaleDateString(language === 'de' ? 'de-DE' : 'en-US') : '-'}
                        </span>
                      </div>
                    </div>
                    <Button
                      variant="ghost"
                      size="sm"
                      className="mt-3 h-8 px-0 text-muted-foreground hover:text-foreground"
                      onClick={() => { window.location.href = `/auftrag?parkedOrderId=${order.id}`; }}
                    >
                      {language === 'de' ? 'Weiterführen' : 'Continue'}
                      <ArrowRight weight="bold" className="w-4 h-4 ml-1" />
                    </Button>
                  </div>
                ))}
                {parkedOrders.length > parkedOrderItems.length && (
                  <p className="text-xs text-muted-foreground">
                    {language === 'de'
                      ? `+${parkedOrders.length - parkedOrderItems.length} weitere geparkte Aufträge`
                      : `+${parkedOrders.length - parkedOrderItems.length} more parked orders`}
                  </p>
                )}
              </CardContent>
            </Card>
          )}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <Card className="lg:col-span-2 bg-card border-border" data-testid="recent-activity-card">
            <CardHeader className="border-b border-border pb-4">
              <div className="flex items-center justify-between gap-4">
                <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
                  <ClockCounterClockwise weight="fill" className="w-5 h-5 text-primary" />
                  {t('recentActivity')}
                </CardTitle>
                <Button
                  variant="ghost"
                  size="sm"
                  className="text-muted-foreground hover:text-foreground"
                  onClick={() => { window.location.href = '/orders'; }}
                >
                  {activityText.allOrders}
                  <ArrowRight weight="bold" className="ml-1 h-4 w-4" />
                </Button>
              </div>
            </CardHeader>
            <CardContent className="p-0">
              {recentActivity.length > 0 ? (
                <div className="overflow-x-auto">
                  <table className="w-full min-w-[980px] text-sm">
                    <thead className="bg-secondary/35 text-xs uppercase tracking-[0.16em] text-muted-foreground">
                      <tr>
                        <th className="px-4 py-3 text-left font-medium">{activityText.dateOrder}</th>
                        <th className="px-4 py-3 text-left font-medium">{activityText.vehicle}</th>
                        <th className="px-4 py-3 text-left font-medium">{activityText.tuning}</th>
                        <th className="px-4 py-3 text-left font-medium">{activityText.credits}</th>
                        <th className="px-4 py-3 text-right font-medium">{activityText.files}</th>
                      </tr>
                    </thead>
                    <tbody>
                      {recentActivity.map((activity) => {
                        const vehicleLabel = splitVehicleLabel(activity.vehicle);
                        const hasFiles = Boolean(activity.originalDownloadUrl || activity.modifiedDownloadUrl);

                        return (
                          <tr
                            key={activity.id}
                            className="border-t border-border align-top transition-colors hover:bg-secondary/30"
                          >
                            <td className="px-4 py-3 whitespace-nowrap">
                              <div className="flex min-w-[9.5rem] flex-col gap-1">
                                <span className="text-xs text-muted-foreground">
                                  {activity.date || '—'}
                                </span>
                                <button
                                  type="button"
                                  className="font-mono text-[11px] text-primary hover:underline text-left"
                                  onClick={() => { window.location.href = `/orders/${encodeURIComponent(activity.id)}`; }}
                                >
                                  {activity.orderNumber}
                                </button>
                              </div>
                            </td>
                            <td className="px-4 py-3">
                              <div className="flex min-w-0 items-start gap-3">
                                <div className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-sm bg-primary/10">
                                  <CarProfile weight="fill" className={`h-4 w-4 ${getStatusIcon(activity.status)}`} />
                                </div>
                                <div className="min-w-0">
                                  <div className="min-w-0">
                                    <p className="font-semibold leading-tight text-foreground">
                                      {vehicleLabel.primary}
                                    </p>
                                    {vehicleLabel.secondary ? (
                                      <p className="mt-1 text-sm leading-tight text-foreground">
                                        {vehicleLabel.secondary}
                                      </p>
                                    ) : null}
                                  </div>
                                </div>
                              </div>
                            </td>
                            <td className="px-4 py-3">
                              <div className="space-y-2">
                                <p className="text-sm text-foreground">
                                  {activity.stageLabel || '—'}
                                </p>
                                {activity.options.length > 0 && (
                                  <div className="flex flex-wrap gap-1.5">
                                    {activity.options.map((option) => (
                                      <Badge key={`${activity.id}-${option}`} variant="outline" className="border-border bg-secondary text-foreground">
                                        {option}
                                      </Badge>
                                    ))}
                                  </div>
                                )}
                              </div>
                            </td>
                            <td className="px-4 py-3 whitespace-nowrap text-sm text-muted-foreground">
                              {activity.credits !== null ? `${activity.credits} Credits` : '—'}
                            </td>
                            <td className="px-4 py-3 text-right">
                              <div className="flex flex-col items-end gap-2">
                                {hasFiles ? (
                                  <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                      <Button variant="outline" size="sm" className="border-border bg-secondary/30">
                                        <Download weight="regular" className="mr-2 h-4 w-4" />
                                        {activityText.filesMenu}
                                        <CaretDown weight="bold" className="ml-2 h-4 w-4" />
                                      </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" className="w-56 border-border bg-card text-foreground">
                                      {activity.originalDownloadUrl ? (
                                        <DropdownMenuItem asChild>
                                          <a href={activity.originalDownloadUrl} className="flex items-center">
                                            <Download weight="regular" className="mr-2 h-4 w-4" />
                                            {activityText.originalFile}
                                          </a>
                                        </DropdownMenuItem>
                                      ) : null}
                                      {activity.modifiedDownloadUrl ? (
                                        <DropdownMenuItem asChild>
                                          <a href={activity.modifiedDownloadUrl} className="flex items-center">
                                            <Download weight="bold" className="mr-2 h-4 w-4" />
                                            {activityText.modifiedFile}
                                          </a>
                                        </DropdownMenuItem>
                                      ) : null}
                                    </DropdownMenuContent>
                                  </DropdownMenu>
                                ) : (
                                  <span className="text-sm text-muted-foreground">—</span>
                                )}
                                <Button
                                  variant="ghost"
                                  size="sm"
                                  className="px-0 text-muted-foreground hover:text-foreground"
                                  onClick={() => { window.location.href = `/orders/${encodeURIComponent(activity.id)}`; }}
                                >
                                  {activityText.openOrder}
                                  <ArrowRight weight="bold" className="ml-1 h-4 w-4" />
                                </Button>
                              </div>
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div className="p-8 text-center text-muted-foreground">
                  {t('noActivity')}
                </div>
              )}
            </CardContent>
          </Card>

          <NewsCarousel language={language} newsItems={newsItems} className="self-start" />
        </div>
      </div>
    </DashboardLayout>
  );
}
