import { useState } from 'react';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Badge } from '../components/ui/badge';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '../components/ui/popover';
import { 
  ListBullets,
  CarProfile,
  Truck,
  Gear,
  Leaf,
  Lightning,
  Warning,
  Plus,
  Drop,
  Prohibit,
  Gauge,
  Power,
  Fire,
  RocketLaunch,
  Fan,
  Thermometer,
  CheckCircle,
  Info,
} from '@phosphor-icons/react';
import { cn } from '../lib/utils';

const KNOWN_OPTION_ICON_KEYS = new Set([
  'drop',
  'fan',
  'gauge',
  'warning',
  'power',
  'fire',
  'rocket',
  'prohibit',
  'thermometer',
  'gear',
]);

const PRICE_LIST_ICON_BY_CODE = {
  adblue: 'drop',
  auspuffklappen: 'fire',
  bmw_mini_garantie_patch: 'warning',
  cat: 'prohibit',
  cvn_patch: 'warning',
  dpf: 'drop',
  drehmomentueberwachung_off: 'gauge',
  dtc: 'warning',
  egr: 'fan',
  hardcut_popcorn_limiter: 'fire',
  harter_begrenzer: 'fire',
  hotstart: 'thermometer',
  kaltstart_off: 'power',
  launch: 'rocket',
  launch_control_anti_lag_me7: 'rocket',
  launch_control_anti_lag_me9: 'rocket',
  launch_control_anti_lag_med17: 'rocket',
  luftmengenmesser_off: 'fan',
  nox_off: 'prohibit',
  oeldruck_fix: 'thermometer',
  opf_gpf_off: 'prohibit',
  opf_off: 'prohibit',
  pop_bang_sport: 'fire',
  pop_bang_tempomat_me9: 'fire',
  pops: 'fire',
  sekundaerluftpumpe_off: 'fan',
  sport_display: 'gauge',
  startstop: 'power',
  swirl: 'fan',
  tuning_auf_sport_button: 'gauge',
  vmax: 'gauge',
  vmax_limit_setzen: 'gauge',
  wegfahrsperre_immo_off: 'prohibit',
  zylinderabschaltung_off: 'power',
  dsg_furzen: 'fire',
};

const normalizeDescription = (value) => {
  const content = String(value ?? '').trim();
  return content === '-' ? '' : content;
};

const resolveIconUrl = (value) => {
  const raw = String(value ?? '').trim();
  if (raw === '' || KNOWN_OPTION_ICON_KEYS.has(raw.toLowerCase())) {
    return '';
  }

  if (raw.startsWith('http://') || raw.startsWith('https://')) {
    return raw;
  }

  if (!raw.startsWith('/') && !/\.(svg|png|jpe?g|gif|webp)$/i.test(raw)) {
    return '';
  }

  return `https://portal.dct-files.de/${raw.replace(/^\/+/, '')}`;
};

const resolvePriceListIconKey = (item) => {
  const code = String(item?.code || item?.id || '').trim().toLowerCase();
  if (code && PRICE_LIST_ICON_BY_CODE[code]) {
    return PRICE_LIST_ICON_BY_CODE[code];
  }

  const rawIcon = String(item?.icon || '').trim().toLowerCase();
  if (KNOWN_OPTION_ICON_KEYS.has(rawIcon)) {
    return rawIcon;
  }

  const haystack = `${code} ${String(item?.name || '').trim().toLowerCase()}`;

  if (haystack.includes('dpf') || haystack.includes('adblue') || haystack.includes('scr')) return 'drop';
  if (haystack.includes('egr') || haystack.includes('agr') || haystack.includes('swirl') || haystack.includes('drall')) return 'fan';
  if (haystack.includes('vmax') || haystack.includes('speed')) return 'gauge';
  if (haystack.includes('dtc') || haystack.includes('error') || haystack.includes('warn')) return 'warning';
  if (haystack.includes('start')) return 'power';
  if (haystack.includes('pops') || haystack.includes('bang') || haystack.includes('hardcut')) return 'fire';
  if (haystack.includes('launch') || haystack.includes('anti lag')) return 'rocket';
  if (haystack.includes('cat') || haystack.includes('nox') || haystack.includes('o2') || haystack.includes('lambda') || haystack.includes('opf') || haystack.includes('gpf')) return 'prohibit';
  if (haystack.includes('oeldruck') || haystack.includes('öldruck') || haystack.includes('oil') || haystack.includes('hot')) return 'thermometer';

  return 'gear';
};

const HtmlDescription = ({ html, className = '' }) => {
  const content = String(html ?? '').trim();
  if (!content) {
    return null;
  }

  return (
    <div
      className={`text-xs text-muted-foreground mt-0.5 [&_a]:text-primary [&_a]:underline [&_p]:mb-2 last:[&_p]:mb-0 [&_ul]:list-disc [&_ul]:pl-4 [&_ol]:list-decimal [&_ol]:pl-4 ${className}`.trim()}
      dangerouslySetInnerHTML={{ __html: content }}
    />
  );
};

const readPriceListBootstrap = () => {
  if (typeof window === 'undefined') {
    return null;
  }

  const value = window?.portalReactBootstrap?.priceList;
  return value && typeof value === 'object' ? value : null;
};

// Tuning Options with prices and icons
const tuningOptions = [
  { id: 'dpf', name: { de: 'DPF-Off', en: 'DPF-Off' }, credits: 0, included: true, icon: 'drop', description: { de: 'Partikelfilter', en: 'Particle Filter' } },
  { id: 'egr', name: { de: 'EGR-Off', en: 'EGR-Off' }, credits: 0, included: true, icon: 'fan', description: { de: 'AGR Ventil', en: 'EGR Valve' } },
  { id: 'adblue', name: { de: 'AdBlue-Off', en: 'AdBlue-Off' }, credits: 0, included: true, icon: 'drop', description: { de: 'SCR System', en: 'SCR System' } },
  { id: 'vmax', name: { de: 'Vmax-Off', en: 'Vmax-Off' }, credits: 0, included: true, icon: 'gauge', description: { de: 'Geschwindigkeit', en: 'Speed Limiter' } },
  { id: 'dtc', name: { de: 'DTC-Off', en: 'DTC-Off' }, credits: 25, included: false, icon: 'warning', description: { de: 'Fehlercode', en: 'Error Code' } },
  { id: 'startstop', name: { de: 'Start-Stop-Off', en: 'Start-Stop-Off' }, credits: 20, included: false, icon: 'power', description: { de: 'Start-Stop', en: 'Start-Stop' } },
  { id: 'pops', name: { de: 'Pops & Bangs', en: 'Pops & Bangs' }, credits: 45, included: false, icon: 'fire', description: { de: 'Auspuff Knallen', en: 'Exhaust Crackle' } },
  { id: 'launch', name: { de: 'Launch Control', en: 'Launch Control' }, credits: 55, included: false, icon: 'rocket', description: { de: 'Rennstart', en: 'Race Start' } },
  { id: 'swirl', name: { de: 'Swirl Flaps-Off', en: 'Swirl Flaps-Off' }, credits: 30, included: false, icon: 'fan', description: { de: 'Drallklappen', en: 'Swirl Flaps' } },
  { id: 'cat', name: { de: 'Kat-Off', en: 'Cat-Off' }, credits: 35, included: false, icon: 'prohibit', description: { de: 'Katalysator', en: 'Catalyst' } },
  { id: 'o2', name: { de: 'O2-Off', en: 'O2-Off' }, credits: 25, included: false, icon: 'thermometer', description: { de: 'Lambdasonde', en: 'O2 Sensor' } },
  { id: 'hotstart', name: { de: 'Hot Start Fix', en: 'Hot Start Fix' }, credits: 30, included: false, icon: 'thermometer', description: { de: 'Heißstart', en: 'Hot Start' } },
];

// Translations
const pageTranslations = {
  en: {
    pageTitle: 'Price List',
    pkw: 'Cars (PKW)',
    lkw: 'Truck / Agri / Bus',
    stage1: 'Stage 1',
    stage2: 'Stage 2',
    eco: 'Eco',
    gearbox: 'Gearbox',
    optionsSingle: 'Single Options',
    tuning: 'Tuning',
    optionsOnly: 'Options Only',
    basePrice: 'Base Price',
    option: 'Option',
    credits: 'Credits',
    description: 'Description',
    availableOptions: 'Available Options',
    included: 'Included',
    gearboxNote: 'Only available for VW, Audi, Seat, Cupra, Skoda and BMW',
    allOptions: 'All Tuning Options',
    note: 'Prices may vary depending on vehicle and ECU type.',
  },
  de: {
    pageTitle: 'Preisliste',
    pkw: 'PKW',
    lkw: 'LKW / Agrar / Bus',
    stage1: 'Stage 1',
    stage2: 'Stage 2',
    eco: 'Öko',
    gearbox: 'Getriebe',
    optionsSingle: 'Optionen einzeln',
    tuning: 'Tuning',
    optionsOnly: 'Nur Optionen',
    basePrice: 'Grundpreis',
    option: 'Option',
    credits: 'Credits',
    description: 'Beschreibung',
    availableOptions: 'Verfügbare Optionen',
    included: 'Inklusive',
    gearboxNote: 'Nur bei VW, Audi, Seat, Cupra, Skoda und BMW möglich',
    allOptions: 'Alle Tuning-Optionen',
    note: 'Preise können je nach Fahrzeug und Steuergerät variieren.',
  }
};

// Option Icon Component
const OptionIcon = ({ iconType, className }) => {
  const iconProps = { weight: "fill", className: className || "w-6 h-6" };
  
  switch(iconType) {
    case 'drop': return <Drop {...iconProps} />;
    case 'fan': return <Fan {...iconProps} />;
    case 'gauge': return <Gauge {...iconProps} />;
    case 'warning': return <Warning {...iconProps} />;
    case 'power': return <Power {...iconProps} />;
    case 'fire': return <Fire {...iconProps} />;
    case 'rocket': return <RocketLaunch {...iconProps} />;
    case 'prohibit': return <Prohibit {...iconProps} />;
    case 'thermometer': return <Thermometer {...iconProps} />;
    default: return <Gear {...iconProps} />;
  }
};

// Option Card Component
const OptionCard = ({ option, language, t }) => {
  const isIncluded = option.included;
  const iconUrl = resolveIconUrl(option.iconPath || '');
  const description = normalizeDescription(option.description?.[language]);
  
  return (
    <div 
      className={`p-3 rounded-sm border transition-colors ${
        isIncluded 
          ? 'bg-success-soft border-success-soft' 
          : 'bg-secondary border-border hover:border-white/20'
      }`}
    >
      <div className="flex flex-col items-center text-center">
        <div className={`w-14 h-14 rounded-full flex items-center justify-center mb-2 ${
          isIncluded ? 'bg-success-soft' : 'bg-primary/20'
        }`}>
          {iconUrl ? (
            <img src={iconUrl} alt="" className="h-8 w-8 object-contain brightness-0 invert" loading="lazy" />
          ) : (
            <OptionIcon 
              iconType={option.icon} 
              className={`w-7 h-7 ${isIncluded ? 'text-success-theme' : 'text-white'}`} 
            />
          )}
        </div>
        <p className={`text-sm font-semibold ${isIncluded ? 'text-success-theme' : 'text-foreground'}`}>
          {option.name[language]}
        </p>
        {description ? (
          <Popover>
            <PopoverTrigger asChild>
              <button
                type="button"
                className="mt-1 inline-flex items-center gap-1 rounded-full border border-border bg-background/60 px-2 py-1 text-[10px] font-semibold text-muted-foreground transition-colors hover:text-foreground"
              >
                <Info weight="fill" className="w-3.5 h-3.5" />
                {language === 'de' ? 'Details' : 'Details'}
              </button>
            </PopoverTrigger>
            <PopoverContent className="w-64 border-border bg-card text-foreground">
              <div className="space-y-2">
                <p className="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                  {option.name[language]}
                </p>
                <HtmlDescription html={description} className="mt-0" />
              </div>
            </PopoverContent>
          </Popover>
        ) : null}
        {isIncluded ? (
          <Badge className="mt-2 bg-success-soft text-success-theme border border-success-soft text-xs">
            <CheckCircle weight="fill" className="w-3 h-3 mr-1" />
            {t('included')}
          </Badge>
        ) : (
          <Badge className="mt-2 bg-primary/20 text-primary border border-primary/30 font-mono text-xs">
            +{option.credits}
          </Badge>
        )}
      </div>
    </div>
  );
};

// Options Grid Component
const OptionsGrid = ({ options, language, t }) => {
  return (
    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
      {options.map((option) => (
        <OptionCard key={option.id} option={option} language={language} t={t} />
      ))}
    </div>
  );
};

// PKW Stage Card Component
const StageCard = ({ title, basePrice, icon: Icon, language, t, options }) => {
  return (
    <Card className="bg-card border-border">
      <CardHeader className="border-b border-border pb-4">
        <div className="flex items-center justify-between">
          <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
            <Icon weight="fill" className="w-5 h-5 text-primary" />
            {title}
          </CardTitle>
          <Badge className="bg-primary/20 text-primary border border-primary/30 font-heading font-bold text-lg px-3 py-1">
            {basePrice} Credits
          </Badge>
        </div>
      </CardHeader>
      <CardContent className="p-4">
        <p className="text-sm text-muted-foreground mb-4">{t('availableOptions')}</p>
        <OptionsGrid options={options} language={language} t={t} />
      </CardContent>
    </Card>
  );
};

// Gearbox Card Component
const GearboxCard = ({ language, t, items }) => {
  return (
    <Card className="bg-card border-border">
      <CardHeader className="border-b border-border pb-4">
        <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
          <Gear weight="fill" className="w-5 h-5 text-primary" />
          {t('gearbox')}
        </CardTitle>
      </CardHeader>
      <CardContent className="p-4">
        {/* Warning Note */}
        <div className="flex items-start gap-3 p-3 bg-warning-soft border border-warning-soft rounded-sm mb-4">
          <Warning weight="fill" className="w-5 h-5 text-warning-theme flex-shrink-0 mt-0.5" />
          <p className="text-sm text-warning-theme">{t('gearboxNote')}</p>
        </div>
        
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          {items.map((item) => (
            <div key={item.id || item.stage} className="p-4 bg-secondary rounded-sm border border-border text-center">
              <p className="font-semibold text-foreground text-lg">{item.name?.[language] || item.stage}</p>
              <Badge className="mt-2 bg-primary/20 text-primary border border-primary/30 font-heading font-bold text-lg px-3 py-1">
                {item.credits} Credits
              </Badge>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
};

// Single Options Card Component - now with grid
const SingleOptionsCard = ({ language, t, options }) => {
  return (
    <Card className="bg-card border-border">
      <CardHeader className="border-b border-border pb-4">
        <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
          <Plus weight="fill" className="w-5 h-5 text-primary" />
          {t('allOptions')}
        </CardTitle>
      </CardHeader>
      <CardContent className="p-4">
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
          {options.map((option) => {
            const iconUrl = resolveIconUrl(option.iconPath || '');
            const description = normalizeDescription(option.description?.[language]);

            return (
              <div 
                key={option.id}
                className="p-3 rounded-sm border bg-secondary border-border hover:border-white/20 transition-colors"
              >
                <div className="flex flex-col items-center text-center">
                  <div className="w-10 h-10 rounded-sm bg-secondary flex items-center justify-center mb-2">
                    {iconUrl ? (
                      <img src={iconUrl} alt="" className="h-8 w-8 object-contain brightness-0 invert" loading="lazy" />
                    ) : (
                      <OptionIcon iconType={option.icon} className="w-7 h-7 text-white" />
                    )}
                  </div>
                  <p className="text-sm font-semibold text-foreground">{option.name[language]}</p>
                  {description ? (
                    <Popover>
                      <PopoverTrigger asChild>
                        <button
                          type="button"
                          className="mt-1 inline-flex items-center gap-1 rounded-full border border-border bg-background/60 px-2 py-1 text-[10px] font-semibold text-muted-foreground transition-colors hover:text-foreground"
                        >
                          <Info weight="fill" className="w-3.5 h-3.5" />
                          {language === 'de' ? 'Details' : 'Details'}
                        </button>
                      </PopoverTrigger>
                      <PopoverContent className="w-64 border-border bg-card text-foreground">
                        <div className="space-y-2">
                          <p className="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                            {option.name[language]}
                          </p>
                          <HtmlDescription html={description} className="mt-0" />
                        </div>
                      </PopoverContent>
                    </Popover>
                  ) : null}
                  <Badge className="mt-2 bg-primary/20 text-primary border border-primary/30 font-mono text-xs">
                    {option.credits}
                  </Badge>
                </div>
              </div>
            );
          })}
        </div>
      </CardContent>
    </Card>
  );
};

// LKW Options
const lkwOptions = [
  { id: 'dpf', name: { de: 'DPF-Off', en: 'DPF-Off' }, credits: 0, included: true, icon: 'drop', description: { de: 'Partikelfilter', en: 'Particle Filter' } },
  { id: 'egr', name: { de: 'EGR-Off', en: 'EGR-Off' }, credits: 0, included: true, icon: 'fan', description: { de: 'AGR Ventil', en: 'EGR Valve' } },
  { id: 'adblue', name: { de: 'AdBlue-Off', en: 'AdBlue-Off' }, credits: 0, included: true, icon: 'drop', description: { de: 'SCR System', en: 'SCR System' } },
  { id: 'vmax', name: { de: 'Vmax-Off', en: 'Vmax-Off' }, credits: 0, included: true, icon: 'gauge', description: { de: 'Geschwindigkeit', en: 'Speed Limiter' } },
  { id: 'dtc', name: { de: 'DTC-Off', en: 'DTC-Off' }, credits: 35, included: false, icon: 'warning', description: { de: 'Fehlercode', en: 'Error Code' } },
  { id: 'nox', name: { de: 'NOx-Off', en: 'NOx-Off' }, credits: 50, included: false, icon: 'prohibit', description: { de: 'NOx Sensor', en: 'NOx Sensor' } },
];

// LKW Section Component
const LkwSection = ({ language, t }) => {
  const lkwSingleOptions = lkwOptions.map(opt => ({
    ...opt,
    credits: opt.included ? 55 : opt.credits,
    included: false
  }));

  return (
    <div className="space-y-6">
      {/* Eco with Options */}
      <Card className="bg-card border-border">
        <CardHeader className="border-b border-border pb-4">
          <div className="flex items-center justify-between">
            <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
              <Leaf weight="fill" className="w-5 h-5 text-green-500" />
              {t('eco')}
            </CardTitle>
            <Badge className="bg-green-500/20 text-green-400 border border-green-500/30 font-heading font-bold text-lg px-3 py-1">
              120 Credits
            </Badge>
          </div>
        </CardHeader>
        <CardContent className="p-4">
          <p className="text-sm text-muted-foreground mb-4">{t('availableOptions')}</p>
          <OptionsGrid options={lkwOptions} language={language} t={t} />
        </CardContent>
      </Card>

      {/* Tuning */}
      <Card className="bg-card border-border">
        <CardHeader className="border-b border-border pb-4">
          <div className="flex items-center justify-between">
            <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
              <Lightning weight="fill" className="w-5 h-5 text-primary" />
              {t('tuning')}
            </CardTitle>
            <Badge className="bg-primary/20 text-primary border border-primary/30 font-heading font-bold text-lg px-3 py-1">
              150 Credits
            </Badge>
          </div>
        </CardHeader>
        <CardContent className="p-4">
          <p className="text-sm text-muted-foreground mb-4">{t('availableOptions')}</p>
          <OptionsGrid options={lkwOptions} language={language} t={t} />
        </CardContent>
      </Card>

      {/* Options Only */}
      <Card className="bg-card border-border">
        <CardHeader className="border-b border-border pb-4">
          <CardTitle className="font-heading font-semibold text-lg flex items-center gap-2">
            <Plus weight="fill" className="w-5 h-5 text-primary" />
            {t('optionsOnly')}
          </CardTitle>
        </CardHeader>
        <CardContent className="p-4">
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            {lkwSingleOptions.map((option) => (
              <div 
                key={option.id}
                className="p-3 rounded-sm border bg-secondary border-border hover:border-white/20 transition-colors"
              >
                <div className="flex flex-col items-center text-center">
                  <div className="w-10 h-10 rounded-sm bg-secondary flex items-center justify-center mb-2">
                    <OptionIcon iconType={option.icon} className="w-5 h-5 text-muted-foreground" />
                  </div>
                  <p className="text-sm font-semibold text-foreground">{option.name[language]}</p>
                  <HtmlDescription html={option.description[language]} />
                  <Badge className="mt-2 bg-primary/20 text-primary border border-primary/30 font-mono text-xs">
                    {option.credits}
                  </Badge>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default function PriceList() {
  const { language } = useLanguage();
  const t = (key) => pageTranslations[language][key] || key;
  const priceListBootstrap = readPriceListBootstrap();
  const [activeTab, setActiveTab] = useState('pkw');
  const [pkwSubTab, setPkwSubTab] = useState('stage1');
  const [lkwSubTab, setLkwSubTab] = useState('eco');

  const mapBootstrapOption = (item) => ({
    id: item.id,
    stageCode: item.stageCode || '',
    name: { de: item.name, en: item.name },
    credits: item.credits,
    included: Boolean(item.included),
    icon: resolvePriceListIconKey(item),
    iconPath: resolveIconUrl(item.iconPath || '') ? item.iconPath : (resolveIconUrl(item.icon || '') ? item.icon : ''),
    description: {
      de: normalizeDescription(item.description),
      en: normalizeDescription(item.description),
    },
  });

  const filterStageOptions = (items, stageCode) => items.filter((item) => {
    const itemStageCode = String(item.stageCode || '').trim();
    return itemStageCode === '' || itemStageCode === stageCode;
  });

  const fallbackPriceList = {
    pkw: {
      stage: [
        { id: 'stage1', name: { de: 'Stage 1', en: 'Stage 1' }, credits: 100, iconComponent: Lightning },
        { id: 'stage2', name: { de: 'Stage 2', en: 'Stage 2' }, credits: 100, iconComponent: Lightning },
        { id: 'eco', name: { de: 'Öko', en: 'Eco' }, credits: 100, iconComponent: Leaf },
      ],
      gearbox: [
        { id: 'stage1', name: { de: 'Stage 1', en: 'Stage 1' }, credits: 120 },
        { id: 'stage2', name: { de: 'Stage 2', en: 'Stage 2' }, credits: 160 },
        { id: 'stage3', name: { de: 'Stage 3', en: 'Stage 3' }, credits: 200 },
      ],
      option: tuningOptions,
      options_only: tuningOptions.map((opt) => ({ ...opt, credits: opt.included ? 40 : opt.credits, included: false })),
    },
    lkw: {
      stage: [
        { id: 'eco', name: { de: 'Öko', en: 'Eco' }, credits: 120, iconComponent: Leaf },
        { id: 'tuning', name: { de: 'Tuning', en: 'Tuning' }, credits: 150, iconComponent: Lightning },
      ],
      option: lkwOptions,
      options_only: lkwOptions.map((opt) => ({ ...opt, credits: opt.included ? 55 : opt.credits, included: false })),
    },
  };

  const sectionData = {
    pkw: {
      stage: Array.isArray(priceListBootstrap?.sections?.pkw?.stage) && priceListBootstrap.sections.pkw.stage.length > 0
        ? priceListBootstrap.sections.pkw.stage.map((item) => ({
            ...mapBootstrapOption(item),
            iconComponent: /eco/i.test(item.name) ? Leaf : Lightning,
          }))
        : fallbackPriceList.pkw.stage,
      gearbox: Array.isArray(priceListBootstrap?.sections?.pkw?.gearbox) && priceListBootstrap.sections.pkw.gearbox.length > 0
        ? priceListBootstrap.sections.pkw.gearbox.map(mapBootstrapOption)
        : fallbackPriceList.pkw.gearbox,
      option: Array.isArray(priceListBootstrap?.sections?.pkw?.option) && priceListBootstrap.sections.pkw.option.length > 0
        ? priceListBootstrap.sections.pkw.option.map(mapBootstrapOption)
        : fallbackPriceList.pkw.option,
      optionsOnly: Array.isArray(priceListBootstrap?.sections?.pkw?.options_only) && priceListBootstrap.sections.pkw.options_only.length > 0
        ? priceListBootstrap.sections.pkw.options_only.map(mapBootstrapOption)
        : fallbackPriceList.pkw.options_only,
    },
    lkw: {
      stage: Array.isArray(priceListBootstrap?.sections?.lkw?.stage) && priceListBootstrap.sections.lkw.stage.length > 0
        ? priceListBootstrap.sections.lkw.stage.map((item) => ({
            ...mapBootstrapOption(item),
            iconComponent: /eco/i.test(item.name) ? Leaf : Lightning,
          }))
        : fallbackPriceList.lkw.stage,
      option: Array.isArray(priceListBootstrap?.sections?.lkw?.option) && priceListBootstrap.sections.lkw.option.length > 0
        ? priceListBootstrap.sections.lkw.option.map(mapBootstrapOption)
        : fallbackPriceList.lkw.option,
      optionsOnly: Array.isArray(priceListBootstrap?.sections?.lkw?.options_only) && priceListBootstrap.sections.lkw.options_only.length > 0
        ? priceListBootstrap.sections.lkw.options_only.map(mapBootstrapOption)
        : fallbackPriceList.lkw.options_only,
    },
  };

  const mainTabs = [
    { id: 'pkw', Icon: CarProfile, label: t('pkw') },
    { id: 'lkw', Icon: Truck, label: t('lkw') },
  ];

  const pkwSubTabs = [
    ...sectionData.pkw.stage.map((item) => ({ id: item.id, label: item.name[language] })),
    ...(sectionData.pkw.gearbox.length > 0 ? [{ id: 'gearbox', label: t('gearbox') }] : []),
    ...(sectionData.pkw.optionsOnly.length > 0 ? [{ id: 'options', label: t('optionsSingle') }] : []),
  ];

  const lkwSubTabs = [
    ...sectionData.lkw.stage.map((item) => ({ id: item.id, label: item.name[language] })),
    ...(sectionData.lkw.optionsOnly.length > 0 ? [{ id: 'options', label: t('optionsSingle') }] : []),
  ];

  return (
    <DashboardLayout>
      <div className="space-y-6 max-w-[1400px]">
        {/* Header */}
        <div>
          <h1 className="font-heading font-bold text-2xl tracking-tight text-foreground flex items-center gap-3">
            <ListBullets weight="fill" className="w-7 h-7 text-primary" />
            {t('pageTitle')}
          </h1>
          {priceListBootstrap?.name && (
            <p className="text-sm text-muted-foreground mt-1">{priceListBootstrap.name}</p>
          )}
        </div>

        {/* Main Category Tabs (horizontal) */}
        <div className="flex gap-2" data-testid="main-tabs">
          {mainTabs.map(tab => {
            const Icon = tab.Icon;
            const isActive = activeTab === tab.id;
            return (
              <button
                key={tab.id}
                type="button"
                onClick={() => setActiveTab(tab.id)}
                data-testid={`tab-${tab.id}`}
                className={cn(
                  "flex items-center gap-2.5 px-5 py-3 rounded-sm border-2 transition-all text-left",
                  isActive ? "bg-primary/8 border-primary/40" : "bg-card border-border hover:border-muted-foreground/40"
                )}
              >
                <Icon weight={isActive ? "fill" : "regular"} className={cn("w-5 h-5 flex-shrink-0", isActive ? "text-primary" : "text-muted-foreground")} />
                <span className={cn("text-sm font-semibold", isActive ? "text-foreground" : "text-muted-foreground")}>{tab.label}</span>
              </button>
            );
          })}
        </div>

        {/* Sub-Tabs (horizontal) */}
        <div className="flex flex-wrap gap-2" data-testid="sub-tabs">
          {(activeTab === 'pkw' ? pkwSubTabs : lkwSubTabs).map(sub => {
            const currentSub = activeTab === 'pkw' ? pkwSubTab : lkwSubTab;
            const isActive = currentSub === sub.id;
            return (
              <button
                key={sub.id}
                type="button"
                onClick={() => activeTab === 'pkw' ? setPkwSubTab(sub.id) : setLkwSubTab(sub.id)}
                data-testid={`subtab-${sub.id}`}
                className={cn(
                  "px-4 py-2 rounded-sm border-2 text-sm font-semibold transition-all",
                  isActive ? "bg-primary/8 border-primary/40 text-foreground" : "bg-card border-border text-muted-foreground hover:border-muted-foreground/40"
                )}
              >
                {sub.label}
              </button>
            );
          })}
        </div>

        {/* Content */}
        {activeTab === 'pkw' && (
          <>
            {sectionData.pkw.stage.filter((item) => item.id === pkwSubTab).map((item) => (
              <StageCard
                key={item.id}
                title={item.name[language]}
                basePrice={item.credits}
                icon={item.iconComponent || Lightning}
                language={language}
                t={t}
                options={filterStageOptions(sectionData.pkw.option, item.id)}
              />
            ))}
            {pkwSubTab === 'gearbox' && <GearboxCard language={language} t={t} items={sectionData.pkw.gearbox} />}
            {pkwSubTab === 'options' && <SingleOptionsCard language={language} t={t} options={sectionData.pkw.optionsOnly} />}
          </>
        )}

        {activeTab === 'lkw' && (
          <>
            {sectionData.lkw.stage.filter((item) => item.id === lkwSubTab).map((item) => (
              <StageCard
                key={item.id}
                title={item.name[language]}
                basePrice={item.credits}
                icon={item.iconComponent || Lightning}
                language={language}
                t={t}
                options={filterStageOptions(sectionData.lkw.option, item.id)}
              />
            ))}
            {lkwSubTab === 'options' && <SingleOptionsCard language={language} t={t} options={sectionData.lkw.optionsOnly} />}
          </>
        )}

        {/* Note */}
        <p className="text-sm text-muted-foreground text-center pt-4">{t('note')}</p>
      </div>
    </DashboardLayout>
  );
}
