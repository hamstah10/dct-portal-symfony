import { useState, useEffect, useCallback, useRef } from 'react';
import { useLocation } from 'react-router-dom';
import { motion } from 'framer-motion';
import { toast } from 'sonner';
import { Car, ChevronRight, Loader2, AlertCircle, Zap, Settings2, Info, Gauge, Fuel, BadgeEuro, Search, XCircle, X, ChevronLeft, Leaf, Cog, Bookmark, Cpu, Wrench, PinIcon, Home, Pencil, Plus, Save, Wind, Stethoscope, Volume2, Sparkles } from 'lucide-react';
import { DataCard, StatusBadge } from '@/components/DataComponents';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import DynoChartWidget from '@/components/DynoChart';
import {
  isConfigured,
  fetchTypes, fetchBrands, fetchSeries, fetchModels, fetchEngines, fetchEngineDetails,
  saveStage, saveSeries, saveModel, saveEngine, saveOption, saveEcu, saveGearbox, saveHint, saveToolEntry,
  type VehicleType, type VehicleBrand, type VehicleSeries, type VehicleModel, type VehicleEngine, type EngineDetails, type PerformanceBrand,
} from '@/lib/fahrzeugdatenbank-api';
import {
  fetchRememberedVehicles, saveRememberedVehicle, deleteRememberedVehicle,
  fetchPinnedVehicleType, savePinnedVehicleType, deletePinnedVehicleType,
  type RememberedVehicle,
} from '@/lib/operations-api';


type Step = 'type' | 'brand' | 'series' | 'model' | 'engine' | 'details';
const STEP_LABELS: Record<Step, string> = {
  type: 'Fahrzeugtyp',
  brand: 'Marke',
  series: 'Baureihe',
  model: 'Modell',
  engine: 'Motor',
  details: 'Details',
};

interface Selection {
  type?: VehicleType;
  brand?: VehicleBrand;
  series?: VehicleSeries;
  model?: VehicleModel;
  engine?: VehicleEngine;
}

function filterByName<T extends { name: string }>(items: T[], normalizedSearch: string): T[] {
  if (!normalizedSearch) return items;
  return items.filter(item => item.name.toLowerCase().includes(normalizedSearch));
}

function renderTypeBreadcrumbIcon(type?: VehicleType) {
  if (type?.icon) {
    return <img src={type.icon} alt="" className="h-4 w-4 object-contain" />;
  }

  if (type?.image) {
    return <img src={type.image} alt="" className="h-4 w-4 object-contain" />;
  }

  return <Home className="h-4 w-4 text-muted-foreground" />;
}

function renderBrandBreadcrumbIcon(brand?: VehicleBrand) {
  if (brand?.image) {
    return <img src={brand.image} alt="" className="h-4 w-4 object-contain" />;
  }

  return null;
}

export default function OperationsVehiclesPage() {
  const location = useLocation();
  const [step, setStep] = useState<Step>('type');
  const [selection, setSelection] = useState<Selection>({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Data for each step
  const [types, setTypes] = useState<VehicleType[]>([]);
  const [brands, setBrands] = useState<VehicleBrand[]>([]);
  const [series, setSeries] = useState<VehicleSeries[]>([]);
  const [models, setModels] = useState<VehicleModel[]>([]);
  const [engines, setEngines] = useState<VehicleEngine[]>([]);
  const [details, setDetails] = useState<EngineDetails | null>(null);
  const [stepSearch, setStepSearch] = useState('');
  const [pinnedTypeId, setPinnedTypeId] = useState<number | null>(null);
  const [pinBusy, setPinBusy] = useState(false);
  const appliedInitialPinRef = useRef(false);

  const configured = isConfigured();

  useEffect(() => {
    setStepSearch('');
  }, [step]);

  const loadData = useCallback(async (loader: () => Promise<void>) => {
    setLoading(true);
    setError(null);
    try {
      await loader();
    } catch (e: unknown) {
      setError(e instanceof Error ? e.message : 'Unbekannter Fehler');
    } finally {
      setLoading(false);
    }
  }, []);

  const engineId = selection.engine?.id;
  const refreshEngineDetails = useCallback(async () => {
    if (!engineId) {
      return;
    }

    const updated = await fetchEngineDetails(Number(engineId));
    setDetails(updated);
  }, [engineId]);

  // Load types on mount; falls kein Deep-Link zu einem gemerkten Fahrzeug ansteht und ein
  // Fahrzeugtyp gepinnt ist, direkt bei dessen Marken starten statt bei der Typ-Übersicht.
  useEffect(() => {
    if (!configured) return;
    loadData(async () => {
      const [typesData, pinned] = await Promise.all([
        fetchTypes(),
        fetchPinnedVehicleType().catch(() => null),
      ]);
      setTypes(typesData);
      setPinnedTypeId(pinned?.typeId ?? null);

      if (!appliedInitialPinRef.current) {
        appliedInitialPinRef.current = true;
        const hasDeepLink = Boolean((location.state as { selection?: Selection } | null)?.selection?.engine?.id);
        const pinnedType = !hasDeepLink && pinned ? typesData.find(t => t.id === pinned.typeId) : undefined;
        if (pinnedType) {
          setSelection({ type: pinnedType });
          setStep('brand');
          const brandsData = await fetchBrands(pinnedType.id);
          setBrands(brandsData);
        }
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [configured, loadData]);

  // Deep-link: springe direkt zur Motor-Detailseite, wenn eine gemerkte Auswahl übergeben wurde.
  // Hängt an location.key statt [] – wird man von der Merkliste erneut auf denselben Pfad
  // navigiert (/operations/vehicles), remountet die Seite nicht, nur location.key ändert sich.
  useEffect(() => {
    const restoredSelection = (location.state as { selection?: Selection } | null)?.selection;
    const engineId = restoredSelection?.engine?.id;
    if (!engineId) return;

    setSelection(restoredSelection);
    setStep('details');
    loadData(async () => {
      const data = await fetchEngineDetails(engineId);
      setDetails(data);
    });
    window.history.replaceState({}, document.title);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [location.key]);

  const togglePinType = async (e: React.MouseEvent, t: VehicleType) => {
    e.stopPropagation();
    if (pinBusy) return;
    setPinBusy(true);
    try {
      if (pinnedTypeId === t.id) {
        await deletePinnedVehicleType();
        setPinnedTypeId(null);
        toast.info(`Start-Pin für "${t.name}" entfernt`);
      } else {
        await savePinnedVehicleType(t.id);
        setPinnedTypeId(t.id);
        toast.success(`"${t.name}" als Start-Typ angeheftet`);
      }
    } catch {
      toast.error('Pin konnte nicht gespeichert werden.');
    } finally {
      setPinBusy(false);
    }
  };

  const selectType = (t: VehicleType) => {
    setSelection({ type: t });
    setStep('brand');
    loadData(async () => {
      const data = await fetchBrands(t.id);
      setBrands(data);
    });
  };

  const selectBrand = (b: VehicleBrand) => {
    setSelection(prev => ({ ...prev, brand: b }));
    setStep('series');
    loadData(async () => {
      const data = await fetchSeries(b.id);
      setSeries(data);
    });
  };

  const selectSeries = (s: VehicleSeries) => {
    setSelection(prev => ({ ...prev, series: s }));
    setStep('model');
    loadData(async () => {
      const data = await fetchModels(s.id);
      setModels(data);
    });
  };

  const selectModel = (m: VehicleModel) => {
    setSelection(prev => ({ ...prev, model: m }));
    setStep('engine');
    loadData(async () => {
      const data = await fetchEngines(m.id);
      setEngines(data);
    });
  };

  const selectEngine = (e: VehicleEngine) => {
    setSelection(prev => ({ ...prev, engine: e }));
    setStep('details');
    loadData(async () => {
      const data = await fetchEngineDetails(e.id);
      setDetails(data);
    });
  };

  const goToStep = (targetStep: Step) => {
    if (targetStep === 'type') {
      setSelection({});
      setStep('type');
    } else if (targetStep === 'brand' && selection.type) {
      setSelection({ type: selection.type });
      setStep('brand');
    } else if (targetStep === 'series' && selection.brand) {
      setSelection({ type: selection.type, brand: selection.brand });
      setStep('series');
    } else if (targetStep === 'model' && selection.series) {
      setSelection({ type: selection.type, brand: selection.brand, series: selection.series });
      setStep('model');
    } else if (targetStep === 'engine' && selection.model) {
      setSelection({ type: selection.type, brand: selection.brand, series: selection.series, model: selection.model });
      setStep('engine');
    }
  };

  const steps: Step[] = ['type', 'brand', 'series', 'model', 'engine', 'details'];
  const currentStepIdx = steps.indexOf(step);

  // Suche oben rechts: bei allen Auswahl-Schritten, nur nicht bei Details
  const searchableCount = step === 'type' ? types.length
    : step === 'brand' ? brands.length
    : step === 'series' ? series.length
    : step === 'model' ? models.length
    : step === 'engine' ? engines.length
    : 0;
  const normalizedSearch = stepSearch.trim().toLowerCase();

  // Build breadcrumb items
  const breadcrumbItems: { step: Step; label: string }[] = [];
  breadcrumbItems.push({ step: 'type', label: selection.type?.name ?? STEP_LABELS.type });
  if (currentStepIdx >= 1) breadcrumbItems.push({ step: 'brand', label: selection.brand?.name ?? STEP_LABELS.brand });
  if (currentStepIdx >= 2) breadcrumbItems.push({ step: 'series', label: selection.series?.name ?? STEP_LABELS.series });
  if (currentStepIdx >= 3) breadcrumbItems.push({ step: 'model', label: selection.model?.name ?? STEP_LABELS.model });
  if (currentStepIdx >= 4) breadcrumbItems.push({ step: 'engine', label: stripFuelSuffix(selection.engine?.name ?? STEP_LABELS.engine) });
  if (currentStepIdx >= 5) breadcrumbItems.push({ step: 'details', label: 'Details' });

  if (!configured) {
    return (
      <div className="p-6 max-w-7xl mx-auto">
        <DataCard>
          <div className="flex items-start gap-3 p-2">
            <AlertCircle className="h-5 w-5 text-destructive shrink-0 mt-0.5" />
            <div>
              <p className="text-sm font-medium text-foreground mb-1">API nicht konfiguriert</p>
              <p className="text-xs text-muted-foreground">
                Trage den API Token und die Token ID in <code className="text-xs bg-secondary px-1 py-0.5 rounded-sm">src/lib/fahrzeugdatenbank-api.ts</code> ein.
              </p>
              <p className="text-xs text-muted-foreground mt-1">
                Tokens werden unter <strong>Verwaltung → API Tokens</strong> auf verwaltung.tuningfux.de erstellt.
              </p>
            </div>
          </div>
        </DataCard>
      </div>
    );
  }

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.4 }}>
        {/* Breadcrumb Navigation + Suche */}
        <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
          <div className="flex flex-wrap items-center gap-2 text-[15px] font-medium leading-none text-muted-foreground">
            {breadcrumbItems.map((item, i) => (
              <div key={item.step} className="flex items-center gap-2">
                {i > 0 && <ChevronRight className="h-4 w-4 text-muted-foreground/50" />}
                <button
                  onClick={() => item.step !== step && goToStep(item.step)}
                  disabled={item.step === step}
                  className={`inline-flex items-center gap-1.5 transition-colors ${
                    item.step === step
                      ? 'text-foreground'
                      : 'hover:text-foreground cursor-pointer'
                  }`}
                >
                  {item.step === 'type' ? (
                    renderTypeBreadcrumbIcon(selection.type)
                  ) : item.step === 'brand' ? (
                    <span className="inline-flex items-center gap-1.5">
                      {selection.brand ? renderBrandBreadcrumbIcon(selection.brand) : null}
                      <span>{item.label}</span>
                    </span>
                  ) : (
                    <span>{item.label}</span>
                  )}
                </button>
              </div>
            ))}
          </div>
          <div className="w-full sm:w-72 shrink-0 self-center">
            {searchableCount > 0 ? (
              <div className="relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <input
                value={stepSearch}
                onChange={e => setStepSearch(e.target.value)}
                placeholder={`${searchableCount} ${STEP_LABELS[step]} durchsuchen...`}
                className="w-full rounded-sm border border-border bg-input py-2.5 pl-9 pr-8 text-[15px] text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
              />
              {stepSearch && (
                <button onClick={() => setStepSearch('')} className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">
                  <XCircle className="h-3.5 w-3.5" />
                </button>
              )}
              </div>
            ) : (
              <div className="h-[42px]" aria-hidden="true" />
            )}
          </div>
        </div>

        {/* Error */}
        {error && (
          <DataCard className="mb-4">
            <div className="flex items-center gap-2 text-destructive">
              <AlertCircle className="h-4 w-4" />
              <p className="text-sm">{error}</p>
            </div>
          </DataCard>
        )}

        {/* Loading */}
        {loading && (
          <div className="flex items-center justify-center py-16">
            <Loader2 className="h-6 w-6 text-muted-foreground animate-spin" />
          </div>
        )}

        {/* Content */}
        {!loading && (
          <div className="min-h-[38rem] lg:min-h-[44rem]">
            <motion.div
              key={step}
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ duration: 0.12 }}
              className="min-h-[38rem] lg:min-h-[44rem]"
            >
              {step === 'type' && (
                <TypesGrid
                  types={filterByName(types, normalizedSearch)}
                  onSelect={selectType}
                  pinnedTypeId={pinnedTypeId}
                  onTogglePin={togglePinType}
                  pinBusy={pinBusy}
                />
              )}
              {step === 'brand' && (
                <BrandsGrid brands={brands} onSelect={selectBrand} search={stepSearch} />
              )}
              {step === 'series' && (
                <SelectionGrid items={filterByName(series, normalizedSearch)} onSelect={selectSeries} icon={<Car className="h-4 w-4" />} cols={6} />
              )}
              {step === 'model' && (
                <SelectionGrid items={filterByName(models, normalizedSearch)} onSelect={selectModel} icon={<Car className="h-4 w-4" />} />
              )}
              {step === 'engine' && (
                <EnginesGrid engines={filterByName(engines, normalizedSearch)} onSelect={selectEngine} />
              )}
              {step === 'details' && details && (
                <EngineDetailsView
                  details={details}
                  selection={selection}
                  onRefresh={refreshEngineDetails}
                />
              )}
            </motion.div>
          </div>
        )}
      </motion.div>
    </div>
  );
}

const PERFORMANCE_BRAND_ORDER = ['e-tron', 's', 'rs'];

// Performance-Marken sind in der DB herstellerpräfixiert gespeichert ("Audi RS", "BMW M", ...).
// Der Hersteller steht in der Sidebar/Breadcrumb bereits fest, daher hier nur das letzte Wort
// ("RS", "M", ...) für Anzeige und Sortierung verwenden statt den Namen doppelt auszuschreiben.
function shortPerformanceBrandLabel(name: string): string {
  const parts = name.trim().split(/\s+/);
  return parts[parts.length - 1] || name;
}

function performanceBrandRank(shortLabel: string): number {
  const idx = PERFORMANCE_BRAND_ORDER.indexOf(shortLabel.trim().toLowerCase());
  return idx === -1 ? PERFORMANCE_BRAND_ORDER.length : idx;
}

type PerformanceBrandGroup<T> = { key: string; label: string; logo: string | null; items: T[] };

function groupByPerformanceBrand<T extends { performanceBrand?: PerformanceBrand | null }>(items: T[]): PerformanceBrandGroup<T>[] {
  const groups = new Map<string, PerformanceBrandGroup<T>>();
  for (const item of items) {
    const pb = item.performanceBrand;
    const key = pb ? String(pb.id) : '__none__';
    let group = groups.get(key);
    if (!group) {
      group = { key, label: pb ? shortPerformanceBrandLabel(pb.name) : '', logo: pb?.logo ?? null, items: [] };
      groups.set(key, group);
    }
    group.items.push(item);
  }

  return Array.from(groups.values()).sort((a, b) => {
    if (a.key === '__none__' || b.key === '__none__') {
      return a.key === b.key ? 0 : a.key === '__none__' ? -1 : 1;
    }
    const rankDiff = performanceBrandRank(a.label) - performanceBrandRank(b.label);
    return rankDiff !== 0 ? rankDiff : a.label.localeCompare(b.label);
  });
}

function asPerformanceBrand(value: unknown): PerformanceBrand | null {
  if (!value || typeof value !== 'object') return null;

  const candidate = value as Partial<PerformanceBrand>;
  return typeof candidate.name === 'string' ? candidate as PerformanceBrand : null;
}

function SelectionGrid<T extends { id: number; name: string; performanceBrand?: PerformanceBrand | null }>({ items, onSelect, icon, cols = 3 }: {
  items: T[];
  onSelect: (item: T) => void;
  icon: React.ReactNode;
  cols?: 3 | 4 | 6;
}) {
  if (items.length === 0) {
    return <p className="text-sm text-muted-foreground italic py-8 text-center">Keine Einträge gefunden</p>;
  }

  const gridClass = cols === 6
    ? 'grid gap-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6'
    : cols === 4
    ? 'grid gap-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4'
    : 'grid gap-2 md:grid-cols-2 xl:grid-cols-3';

  const sortedGroups = groupByPerformanceBrand(items);

  return (
    <div className="space-y-5">
      {sortedGroups.map((group) => (
        <div key={group.key}>
          {group.key !== '__none__' && (
            <h3 className="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {group.logo && (
                <img src={group.logo} alt={group.label} className="h-4 w-10 object-contain object-left" />
              )}
              <span>{group.label} ({group.items.length})</span>
            </h3>
          )}
          <div className={gridClass}>
            {group.items.map((item, i) => (
              <motion.div key={item.id} initial={{ opacity: 0, y: 6 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: Math.min(i * 0.02, 0.4) }}>
                <div onClick={() => onSelect(item)} className="cursor-pointer">
                  <DataCard className="hover:border-destructive/60 hover:bg-secondary/40 transition-colors">
                    <div className="flex items-center gap-2">
                      <span className="text-muted-foreground">{icon}</span>
                      <span className="text-sm font-medium text-foreground">{item.name}</span>
                    </div>
                  </DataCard>
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}

function EnginesGrid({ engines, onSelect }: { engines: VehicleEngine[]; onSelect: (e: VehicleEngine) => void }) {
  if (engines.length === 0) {
    return <p className="text-sm text-muted-foreground italic py-8 text-center">Keine Motoren gefunden</p>;
  }

  const FUEL_ORDER = ['Benzin', 'Diesel', 'Hybrid', 'Elektro'];
  const groups = engines.reduce<Record<string, VehicleEngine[]>>((acc, engine) => {
    const key = engine.fuel?.trim() || 'Sonstige';
    if (!acc[key]) acc[key] = [];
    acc[key].push(engine);
    return acc;
  }, {});

  const sortedKeys = Object.keys(groups).sort((a, b) => {
    const ai = FUEL_ORDER.indexOf(a);
    const bi = FUEL_ORDER.indexOf(b);
    if (ai === -1 && bi === -1) return a.localeCompare(b);
    if (ai === -1) return 1;
    if (bi === -1) return -1;
    return ai - bi;
  });

  return (
    <div className="space-y-5">
      {sortedKeys.map(fuel => {
        const pbGroups = groupByPerformanceBrand(groups[fuel]);
        return (
          <div key={fuel}>
            <h3 className="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              <Fuel className="h-3.5 w-3.5" /> {fuel} ({groups[fuel].length})
            </h3>
            <div className="space-y-4">
              {pbGroups.map(pbGroup => (
                <div key={pbGroup.key}>
                  {pbGroup.key !== '__none__' && (
                    <h4 className="mb-1.5 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground/70">
                      {pbGroup.logo && (
                        <img src={pbGroup.logo} alt={pbGroup.label} className="h-3.5 w-9 object-contain object-left" />
                      )}
                      <span>{pbGroup.label} ({pbGroup.items.length})</span>
                    </h4>
                  )}
                  <div className="grid gap-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {pbGroup.items.map((engine, i) => {
                      const displayName = engine.name.replace(/\s*[-–]\s*\S*$/i, s => {
                        const suffix = s.replace(/^\s*[-–]\s*/, '');
                        return suffix.toLowerCase() === (engine.fuel ?? '').toLowerCase() ? '' : s;
                      }).trim();
                      return (
                        <motion.div key={engine.id} initial={{ opacity: 0, y: 6 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: Math.min(i * 0.02, 0.4) }}>
                          <div onClick={() => onSelect(engine)} className="cursor-pointer">
                            <DataCard className="hover:border-destructive/60 hover:bg-secondary/40 transition-colors">
                              <div className="flex items-center gap-2">
                                <Zap className="h-4 w-4 text-muted-foreground shrink-0" />
                                <span className="text-sm font-medium text-foreground">{displayName}</span>
                              </div>
                            </DataCard>
                          </div>
                        </motion.div>
                      );
                    })}
                  </div>
                </div>
              ))}
            </div>
          </div>
        );
      })}
    </div>
  );
}

function TypesGrid({ types, onSelect, pinnedTypeId, onTogglePin, pinBusy }: {
  types: VehicleType[];
  onSelect: (t: VehicleType) => void;
  pinnedTypeId: number | null;
  onTogglePin: (e: React.MouseEvent, t: VehicleType) => void;
  pinBusy: boolean;
}) {
  if (types.length === 0) {
    return <p className="text-sm text-muted-foreground italic py-8 text-center">Keine Fahrzeugtypen gefunden</p>;
  }

  return (
    <div className="grid gap-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
      {types.map((type, i) => {
        const isPinned = pinnedTypeId === type.id;
        return (
          <motion.div key={type.id} initial={{ opacity: 0, y: 6 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: Math.min(i * 0.04, 0.4) }}>
            <div onClick={() => onSelect(type)} className="cursor-pointer">
              <DataCard className="relative hover:border-destructive/60 hover:bg-secondary/40 transition-colors py-5">
                <button
                  onClick={(e) => onTogglePin(e, type)}
                  disabled={pinBusy}
                  title={isPinned ? 'Start-Pin entfernen' : 'Als Start-Typ anheften'}
                  className={`absolute top-2 right-2 p-1 rounded-sm transition-colors ${
                    isPinned
                      ? 'text-destructive hover:text-destructive/80'
                      : 'text-muted-foreground/50 hover:text-foreground hover:bg-secondary'
                  }`}
                >
                  <PinIcon className={`h-3.5 w-3.5 ${isPinned ? 'fill-destructive' : ''}`} />
                </button>
                <div className="flex flex-col items-center gap-3">
                  <div className="h-12 w-12 rounded-sm bg-secondary flex items-center justify-center shrink-0 overflow-hidden">
                    {type.image ? (
                      <img
                        src={type.image}
                        alt={type.name}
                        className="h-10 w-10 object-contain brightness-0 dark:invert"
                        onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
                      />
                    ) : (
                      <Car className="h-5 w-5 text-muted-foreground" />
                    )}
                  </div>
                  <span className="text-sm font-medium text-foreground text-center">{type.name}</span>
                </div>
              </DataCard>
            </div>
          </motion.div>
        );
      })}
    </div>
  );
}

function BrandsGrid({ brands, onSelect, search }: {
  brands: VehicleBrand[];
  onSelect: (brand: VehicleBrand) => void;
  search: string;
}) {
  if (brands.length === 0) {
    return <p className="text-sm text-muted-foreground italic py-8 text-center">Keine Marken gefunden</p>;
  }

  const filtered = search.trim()
    ? brands.filter(b => b.name.toLowerCase().includes(search.toLowerCase()))
    : brands;

  if (filtered.length === 0) {
    return <p className="text-sm text-muted-foreground italic py-8 text-center">Keine Marke für „{search}" gefunden</p>;
  }

  if (search.trim()) {
    return <BrandTileGrid brands={filtered} onSelect={onSelect} />;
  }

  const topBrands = filtered.filter(b => b.showInTopMenu);
  const otherBrands = filtered.filter(b => !b.showInTopMenu);

  if (topBrands.length === 0) {
    return <BrandTileGrid brands={filtered} onSelect={onSelect} />;
  }

  return (
    <div className="space-y-6">
      <div>
        <h3 className="mb-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
          Top Marken
        </h3>
        <BrandTileGrid brands={topBrands} onSelect={onSelect} />
      </div>
      {otherBrands.length > 0 && (
        <div>
          <div className="border-t border-border mb-4" />
          <h3 className="mb-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
            Alle Marken
          </h3>
          <BrandTileGrid brands={otherBrands} onSelect={onSelect} />
        </div>
      )}
    </div>
  );
}

function BrandTileGrid({ brands, onSelect }: { brands: VehicleBrand[]; onSelect: (brand: VehicleBrand) => void }) {
  return (
    <div className="grid gap-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
      {brands.map((brand, i) => (
        <motion.div key={brand.id} initial={{ opacity: 0, y: 6 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: Math.min(i * 0.02, 0.4) }}>
          <div onClick={() => onSelect(brand)} className="cursor-pointer">
            <DataCard className="hover:border-destructive/60 hover:bg-secondary/40 transition-colors py-5">
              <div className="flex flex-col items-center gap-3">
                <div className="h-16 w-16 rounded-sm bg-secondary flex items-center justify-center shrink-0 overflow-hidden">
                  {brand.image ? (
                    <img
                      src={brand.image}
                      alt={brand.name}
                      className="h-14 w-14 object-contain"
                      onError={(e) => {
                        (e.currentTarget as HTMLImageElement).style.display = 'none';
                        (e.currentTarget.nextSibling as HTMLElement | null)?.removeAttribute('style');
                      }}
                    />
                  ) : null}
                  <Car className="h-4 w-4 text-muted-foreground" style={brand.image ? { display: 'none' } : undefined} />
                </div>
                <span className="text-sm font-medium text-foreground text-center">{brand.name}</span>
              </div>
            </DataCard>
          </div>
        </motion.div>
      ))}
    </div>
  );
}

function EngineDetailsView({ details, selection, onRefresh }: { details: EngineDetails; selection: Selection; onRefresh: () => Promise<void> }) {
  const engineId = selection.engine?.id;
  const [remembered, setRemembered] = useState<RememberedVehicle | null>(null);
  const [rememberedBusy, setRememberedBusy] = useState(false);
  const [editMode, setEditMode] = useState(false);
  const [stageEditorOpen, setStageEditorOpen] = useState(false);
  const [stageEditorBusy, setStageEditorBusy] = useState(false);
  const [stageEditorSourceId, setStageEditorSourceId] = useState<number | null>(null);
  const [stageEditorDraft, setStageEditorDraft] = useState<StageEditorDraft | null>(null);
  const [entityEditorOpen, setEntityEditorOpen] = useState(false);
  const [entityEditorBusy, setEntityEditorBusy] = useState(false);
  const [entityEditorKind, setEntityEditorKind] = useState<EntityEditorKind | null>(null);
  const [entityEditorDraft, setEntityEditorDraft] = useState<EntityEditorDraft | null>(null);

  useEffect(() => {
    if (!engineId) return;
    let cancelled = false;
    fetchRememberedVehicles().then(list => {
      if (cancelled) return;
      setRemembered(list.find(v => v.engine_id === engineId) ?? null);
    }).catch(() => {});
    return () => { cancelled = true; };
  }, [engineId]);

  useEffect(() => {
    if (details.editingAllowed !== true) {
      setEditMode(false);
      setStageEditorOpen(false);
      setStageEditorDraft(null);
      setStageEditorSourceId(null);
      setEntityEditorOpen(false);
      setEntityEditorDraft(null);
      setEntityEditorKind(null);
    }
  }, [details.editingAllowed]);

  const toggleRemember = async () => {
    if (rememberedBusy || !engineId) return;
    setRememberedBusy(true);
    try {
      if (remembered) {
        await deleteRememberedVehicle(remembered.id);
        setRemembered(null);
      } else {
        const saved = await saveRememberedVehicle(selection as unknown as Record<string, unknown>);
        setRemembered(saved);
      }
    } catch {
      // ignorieren – Button bleibt im vorherigen Zustand
    } finally {
      setRememberedBusy(false);
    }
  };

  const engine = details.engine as Record<string, unknown>;
  const brand = details.brand as Record<string, unknown>;
  const series = details.series as Record<string, unknown>;
  const stages = details.stages as Array<Record<string, unknown>>;
  const ecus = details.ecus as Array<Record<string, unknown>>;
  const engineOptions = Array.isArray(details.engineOptions) ? details.engineOptions as Array<Record<string, unknown>> : [];
  const gearboxes = details.gearboxes as Array<Record<string, unknown>>;
  const actualStageCount = stages.length;
  const canCreateStage = actualStageCount < 2;
  const engineName = stripFuelSuffix(asText(engine.name, 'Unbekannter Motor'));
  const brandName = asText(brand.name, 'Unbekannte Marke');
  const seriesName = asText(series.name, 'Unbekannte Baureihe');
  const enginePerformanceBrand = asPerformanceBrand(engine.performanceBrand);
  const seriesPerformanceBrand = asPerformanceBrand(series.performanceBrand);
  const motorHeaderPerformanceBrand = enginePerformanceBrand?.logo
    ? enginePerformanceBrand
    : seriesPerformanceBrand?.logo
      ? seriesPerformanceBrand
      : enginePerformanceBrand ?? seriesPerformanceBrand;
  const model = details.model as Record<string, unknown> | undefined;
  const modelName = model ? asText(model.name, '') : '';
  const horsepower = asNumber(engine.horsepower);
  const torque = asNumber(engine.torque);
  const displacement = asText(engine.displacement);
  const fuel = asText(engine.fuel);
  const showPrices = details.showPrices !== false;
  const showOptionCategories = details.showOptionCategories !== false;
  const canEdit = details.editingAllowed === true;
  const editOptions = (details.editOptions ?? {}) as Partial<EditOptions>;
  const toolManufacturers = editOptions.toolManufacturers ?? [];
  const ecuManufacturers = editOptions.ecuManufacturers ?? [];
  const gearboxTypes = editOptions.gearboxTypes ?? [];
  const optionMasters = editOptions.optionMasters ?? [];
  const toolMethods = editOptions.toolMethods ?? [];
  const mediaAssets = editOptions.mediaAssets ?? [];
  const hintEcus = editOptions.ecus ?? [];
  const stageCardsBase = stages.map((stage, index) => ({
    id: asNumber(stage.id) ?? index,
    name: asText(stage.name, `Stage ${index + 1}`),
    description: stripHtml(asText(stage.description)),
    price: showPrices ? formatEuro(stage.price) : '',
    hp: asNumber(stage.hp),
    nm: asNumber(stage.nm),
    imageCount: Array.isArray(stage.images) ? stage.images.length : 0,
    hasVideo: Boolean(stage.videos),
    active: stage.active !== false,
    isEco: false,
    dynoChart: isDynoChart(stage.dynoChart) ? stage.dynoChart : null,
    slug: asText(stage.slug, ''),
    originalPs: asNumber(stage.originalPs),
    originalNm: asNumber(stage.originalNm),
    rpmStart: asNumber(stage.rpmStart),
    rpmEnd: asNumber(stage.rpmEnd),
    engineType: asText(stage.engineType, ''),
  }));
  const isDiesel = fuel.toLowerCase().includes('diesel');
  const showEcoStage = details.showEcoStage !== false;
  const ecoBaseHp = stageCardsBase[0]?.hp ?? horsepower;
  const ecoBaseNm = stageCardsBase[0]?.nm ?? torque;
  const stageCards = isDiesel && showEcoStage
    ? [
        ...stageCardsBase,
        {
          id: -1,
          name: 'Eco',
          description: '',
          price: '',
          hp: ecoBaseHp !== null ? Math.round(ecoBaseHp * 0.95) : null,
          nm: ecoBaseNm !== null ? Math.round(ecoBaseNm * 0.95) : null,
          imageCount: 0,
          hasVideo: false,
          active: true,
          isEco: true,
          dynoChart: null,
        },
      ]
    : stageCardsBase;
  const ecuCards = ecus.map((ecu, index) => {
    const options = Array.isArray(ecu.options) ? ecu.options as Array<Record<string, unknown>> : [];

    return {
      id: asNumber(ecu.id) ?? index,
      name: asText(ecu.name, `Steuergerät ${index + 1}`),
      kind: asText(ecu.controlUnitKind, asText(ecu.type, 'ECU')),
      manufacturer: asText(ecu.manufacturer, 'Unbekannt'),
      manufacturerId: asNumber(ecu.manufacturerId),
      toolManufacturerId: asNumber(ecu.toolManufacturerId),
      manufacturerLogo: asText(ecu.manufacturerLogo, ''),
      optionCount: options.length,
      options: mapOptionRows(options, showPrices, showOptionCategories),
      toolEntries: mapToolEntries(ecu.toolEntries),
      description: stripHtml(asText(ecu.description, '')),
      image: asText(ecu.image, ''),
      active: ecu.active !== false,
      controlUnitKind: asText(ecu.controlUnitKind, asText(ecu.type, '')),
      type: asText(ecu.type, ''),
    };
  });
  const engineOptionCards = mapOptionRows(engineOptions, showPrices, showOptionCategories);
  const gearboxCards = gearboxes.map((gearbox, index) => ({
    id: asNumber(gearbox.id) ?? index,
    title: asText(gearbox.title, asText(gearbox.name, `Getriebe ${index + 1}`)),
    type: asText(gearbox.type, 'Unbekannt'),
    typeId: asNumber(gearbox.typeId),
    manufacturer: asText(gearbox.manufacturer, 'Unbekannt'),
    manufacturerId: asNumber(gearbox.manufacturerId),
    description: stripHtml(asText(gearbox.description)),
    toolEntries: mapToolEntries(gearbox.toolEntries),
    active: gearbox.active !== false,
    sorting: asNumber(gearbox.sorting) ?? 0,
    icon: asText(gearbox.icon, ''),
  }));

  const findRawStage = (stageId?: number): Record<string, unknown> | null => {
    if (stageId === undefined) {
      return stages[0] ?? null;
    }

    return stages.find(item => asNumber(item.id) === stageId) ?? null;
  };

  const openStageEditor = (stage?: (typeof stageCardsBase)[number]) => {
    if (!stage && !canCreateStage) {
      toast.error('Es sind bereits 2 Stages vorhanden. Neue Stage kann nicht angelegt werden.');
      return;
    }

    const source = findRawStage(stage?.id);
    const sourceId = asNumber(source?.id) ?? null;
    const copySourceId = stage ? sourceId : asNumber(stages[0]?.id) ?? null;
    setStageEditorSourceId(copySourceId);
    setStageEditorDraft({
      id: stage ? sourceId ?? undefined : undefined,
      name: asText(source?.name, ''),
      description: asText(source?.description, ''),
      price: asText(source?.price, '').replace(/[^\d,.-]/g, '').replace(',', '.'),
      hp: source?.hp !== null && source?.hp !== undefined ? String(source.hp) : '',
      nm: source?.nm !== null && source?.nm !== undefined ? String(source.nm) : '',
      ecu: source?.ecu !== null && source?.ecu !== undefined ? String(source.ecu) : '',
      slug: asText(source?.slug, ''),
      originalPs: source?.originalPs !== null && source?.originalPs !== undefined ? String(source.originalPs) : '',
      originalNm: source?.originalNm !== null && source?.originalNm !== undefined ? String(source.originalNm) : '',
      rpmStart: source?.rpmStart !== null && source?.rpmStart !== undefined ? String(source.rpmStart) : '',
      rpmEnd: source?.rpmEnd !== null && source?.rpmEnd !== undefined ? String(source.rpmEnd) : '',
      engineType: asText(source?.engineType, ''),
      active: source?.active !== false,
    });
    setStageEditorOpen(true);
  };

  const saveStageDraft = async () => {
    if (!stageEditorDraft || !selection.engine?.id) {
      return;
    }

    setStageEditorBusy(true);
    try {
      const payload = {
        id: stageEditorDraft.id,
        engineId: Number(selection.engine.id),
        copyFromStageId: stageEditorDraft.id ? undefined : stageEditorSourceId ?? undefined,
        name: stageEditorDraft.name.trim(),
        description: stageEditorDraft.description.trim() || null,
        price: stageEditorDraft.price.trim() || null,
        hp: stageEditorDraft.hp.trim() !== '' ? Number(stageEditorDraft.hp) : null,
        nm: stageEditorDraft.nm.trim() !== '' ? Number(stageEditorDraft.nm) : null,
        ecu: stageEditorDraft.ecu.trim() !== '' ? Number(stageEditorDraft.ecu) : null,
        slug: stageEditorDraft.slug.trim() || null,
        originalPs: stageEditorDraft.originalPs.trim() !== '' ? Number(stageEditorDraft.originalPs) : null,
        originalNm: stageEditorDraft.originalNm.trim() !== '' ? Number(stageEditorDraft.originalNm) : null,
        rpmStart: stageEditorDraft.rpmStart.trim() !== '' ? Number(stageEditorDraft.rpmStart) : null,
        rpmEnd: stageEditorDraft.rpmEnd.trim() !== '' ? Number(stageEditorDraft.rpmEnd) : null,
        engineType: stageEditorDraft.engineType.trim() || null,
        active: stageEditorDraft.active,
      };
      await saveStage(payload);
      toast.success(stageEditorDraft.id ? 'Stage gespeichert' : 'Stage angelegt');
      setStageEditorOpen(false);
      await onRefresh();
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Stage konnte nicht gespeichert werden');
    } finally {
      setStageEditorBusy(false);
    }
  };

  const handleEditOption = (option: OptionItem) => {
    openEntityEditor('option', {
      id: option.id,
      name: option.name,
      description: option.description,
      slug: option.slug,
      price: option.price,
      icon: option.icon,
      active: option.active,
      isMainOption: option.isMain,
    });
  };

  const handleEditEcu = (ecu: EcuCard) => {
    openEntityEditor('ecu', {
      id: ecu.id,
      name: ecu.name,
      description: ecu.description,
      type: ecu.type,
      controlUnitKind: ecu.controlUnitKind,
      image: ecu.image,
      active: ecu.active,
      manufacturerId: ecu.manufacturerId,
      toolManufacturerId: ecu.toolManufacturerId,
    });
  };

  const handleEditGearbox = (gearbox: GearboxCard) => {
    openEntityEditor('gearbox', {
      id: gearbox.id,
      title: gearbox.title,
      name: gearbox.title,
      description: gearbox.description,
      icon: gearbox.icon,
      active: gearbox.active,
      sorting: gearbox.sorting,
      manufacturer: gearbox.manufacturer,
      manufacturerId: gearbox.manufacturerId,
      gearboxTypeId: gearbox.typeId,
    });
  };

  const handleEditHint = (hint: HintItem) => {
    openEntityEditor('hint', {
      id: hint.id,
      name: hint.title,
      title: hint.title,
      description: hint.description,
      active: hint.active,
      ecuOptionMasterId: hint.ecuOptionMasterId,
      activeEcuId: hint.activeEcuId,
      imageIds: hint.imageIds.map(String),
    });
  };

  const handleEditToolEntry = (entry: ToolEntryItem, kind: 'ecu' | 'gearbox', parent: { id: number; name: string }) => {
    openEntityEditor('tool', {
      id: entry.id,
      name: parent.name,
      position: entry.position !== null ? String(entry.position) : '',
      kind,
      toolManufacturerId: entry.toolManufacturerId,
      methodIds: entry.methodIds.map(String),
      manufacturerId: null,
    });
  };

  const openEntityEditor = (kind: EntityEditorKind, source?: Record<string, unknown>) => {
    const current = source ?? (kind === 'series'
      ? series
      : kind === 'model'
        ? model ?? {}
        : kind === 'engine'
          ? engine
          : kind === 'ecu'
            ? ecus[0] ?? {}
            : kind === 'gearbox'
              ? gearboxes[0] ?? {}
              : {});

    setEntityEditorKind(kind);
    setEntityEditorDraft({
      id: asNumber(current.id) ?? undefined,
      name: asText(current.name, ''),
      description: stripHtml(asText(current.description, '')),
      slug: asText(current.slug, ''),
      active: current.active !== false,
      sorting: current.sorting !== null && current.sorting !== undefined ? String(current.sorting) : '',
      price: kind === 'option' ? asText(current.price, '').replace(/[^\d,.-]/g, '').replace(',', '.') : '',
      tooltipText: kind === 'option' ? stripHtml(asText(current.tooltipText, '')) : '',
      icon: kind === 'option' ? asText(current.icon, '') : '',
      horsepower: kind === 'engine' ? (current.horsepower !== null && current.horsepower !== undefined ? String(current.horsepower) : '') : '',
      torque: kind === 'engine' ? (current.torque !== null && current.torque !== undefined ? String(current.torque) : '') : '',
      ecus: kind === 'engine' ? (current.ecus !== null && current.ecus !== undefined ? String(current.ecus) : '') : '',
      fuel: kind === 'engine' ? asText(current.fuel, '') : '',
      displacement: kind === 'engine' ? asText(current.displacement, '') : '',
      isMainOption: kind === 'option' ? Boolean(current.isMainOption) : false,
      controlUnitKind: kind === 'ecu' ? asText(current.controlUnitKind, '') : '',
      type: kind === 'ecu' ? asText(current.type, '') : '',
      title: kind === 'gearbox' ? asText(current.title, '') : '',
      position: kind === 'tool' ? asText(current.position, '') : '',
      ecuOptionMasterId: kind === 'hint' ? asText(current.ecuOptionMasterId, '') : '',
      activeEcuId: kind === 'hint' ? asText(current.activeEcuId, '') : '',
      manufacturer: kind === 'gearbox' ? asText(current.manufacturer, '') : '',
      manufacturerId: kind === 'ecu' || kind === 'gearbox' ? asText(current.manufacturerId, '') : '',
      toolManufacturerId: kind === 'tool' ? asText(current.toolManufacturerId, '') : '',
      gearboxTypeId: kind === 'gearbox' ? asText(current.gearboxTypeId, '') : '',
      methodIds: kind === 'tool' ? (Array.isArray(current.methodIds) ? current.methodIds.map((value: unknown) => asText(value, '')).filter(Boolean) : []) : [],
      imageIds: kind === 'hint' ? (Array.isArray(current.imageIds) ? current.imageIds.map((value: unknown) => asText(value, '')).filter(Boolean) : []) : [],
      toolKind: kind === 'tool' ? (asText(current.kind, 'ecu') === 'gearbox' ? 'gearbox' : 'ecu') : 'ecu',
    });
    setEntityEditorOpen(true);
  };

  const saveEntityDraft = async () => {
    if (!entityEditorDraft || !entityEditorKind) {
      return;
    }

    setEntityEditorBusy(true);
    try {
      if (entityEditorKind === 'series') {
        await saveSeries({
          id: entityEditorDraft.id ?? 0,
          name: entityEditorDraft.name.trim(),
          description: entityEditorDraft.description.trim() || null,
          slug: entityEditorDraft.slug.trim() || null,
          active: entityEditorDraft.active,
          sorting: entityEditorDraft.sorting.trim() !== '' ? Number(entityEditorDraft.sorting) : null,
        });
        toast.success('Baureihe gespeichert');
      } else if (entityEditorKind === 'model') {
        await saveModel({
          id: entityEditorDraft.id ?? 0,
          name: entityEditorDraft.name.trim(),
          description: entityEditorDraft.description.trim() || null,
          slug: entityEditorDraft.slug.trim() || null,
          active: entityEditorDraft.active,
          sorting: entityEditorDraft.sorting.trim() !== '' ? Number(entityEditorDraft.sorting) : null,
        });
        toast.success('Modell gespeichert');
      } else if (entityEditorKind === 'engine') {
        await saveEngine({
          id: entityEditorDraft.id ?? 0,
          name: entityEditorDraft.name.trim(),
          description: entityEditorDraft.description.trim() || null,
          slug: entityEditorDraft.slug.trim() || null,
          active: entityEditorDraft.active,
          sorting: entityEditorDraft.sorting.trim() !== '' ? Number(entityEditorDraft.sorting) : null,
          horsepower: entityEditorDraft.horsepower.trim() !== '' ? Number(entityEditorDraft.horsepower) : null,
          torque: entityEditorDraft.torque.trim() !== '' ? Number(entityEditorDraft.torque) : null,
          ecus: entityEditorDraft.ecus.trim() !== '' ? Number(entityEditorDraft.ecus) : null,
          fuel: entityEditorDraft.fuel.trim() || null,
          displacement: entityEditorDraft.displacement.trim() || null,
        });
        toast.success('Motor gespeichert');
      } else if (entityEditorKind === 'option') {
        await saveOption({
          id: entityEditorDraft.id ?? 0,
          name: entityEditorDraft.name.trim(),
          description: entityEditorDraft.description.trim() || null,
          tooltipText: entityEditorDraft.tooltipText.trim() || null,
          slug: entityEditorDraft.slug.trim() || null,
          price: entityEditorDraft.price.trim() || null,
          icon: entityEditorDraft.icon.trim() || null,
          active: entityEditorDraft.active,
          isMainOption: entityEditorDraft.isMainOption,
        });
        toast.success('Option gespeichert');
      } else if (entityEditorKind === 'ecu') {
        await saveEcu({
          id: entityEditorDraft.id ?? 0,
          name: entityEditorDraft.name.trim(),
          description: entityEditorDraft.description.trim() || null,
          type: entityEditorDraft.type.trim() || null,
          controlUnitKind: entityEditorDraft.controlUnitKind.trim() || null,
          image: entityEditorDraft.icon.trim() || null,
          active: entityEditorDraft.active,
          manufacturerId: entityEditorDraft.manufacturerId.trim() !== '' ? Number(entityEditorDraft.manufacturerId) : null,
          toolManufacturerId: entityEditorDraft.toolManufacturerId.trim() !== '' ? Number(entityEditorDraft.toolManufacturerId) : null,
        });
        toast.success('ECU gespeichert');
      } else if (entityEditorKind === 'gearbox') {
        await saveGearbox({
          id: entityEditorDraft.id ?? 0,
          title: entityEditorDraft.title.trim() || entityEditorDraft.name.trim(),
          description: entityEditorDraft.description.trim() || null,
          icon: entityEditorDraft.icon.trim() || null,
          active: entityEditorDraft.active,
          sorting: entityEditorDraft.sorting.trim() !== '' ? Number(entityEditorDraft.sorting) : null,
          gearboxTypeId: entityEditorDraft.gearboxTypeId.trim() !== '' ? Number(entityEditorDraft.gearboxTypeId) : null,
          manufacturerId: entityEditorDraft.manufacturerId.trim() !== '' ? Number(entityEditorDraft.manufacturerId) : null,
        });
        toast.success('Getriebe gespeichert');
      } else if (entityEditorKind === 'hint') {
        await saveHint({
          id: entityEditorDraft.id ?? 0,
          title: entityEditorDraft.name.trim(),
          description: entityEditorDraft.description.trim() || null,
          active: entityEditorDraft.active,
          ecuOptionMasterId: entityEditorDraft.ecuOptionMasterId.trim() !== '' ? Number(entityEditorDraft.ecuOptionMasterId) : null,
          activeEcuId: entityEditorDraft.activeEcuId.trim() !== '' ? Number(entityEditorDraft.activeEcuId) : null,
          imageIds: entityEditorDraft.imageIds.map(id => Number(id)).filter(id => Number.isFinite(id) && id > 0),
        });
        toast.success('Hinweis gespeichert');
      } else if (entityEditorKind === 'tool') {
        await saveToolEntry({
          id: entityEditorDraft.id ?? 0,
          kind: entityEditorDraft.toolKind,
          position: entityEditorDraft.position.trim() !== '' ? Number(entityEditorDraft.position) : null,
          toolManufacturerId: entityEditorDraft.toolManufacturerId.trim() !== '' ? Number(entityEditorDraft.toolManufacturerId) : null,
          methodIds: entityEditorDraft.methodIds.map(id => Number(id)).filter(id => Number.isFinite(id) && id > 0),
        });
        toast.success('Tool-Eintrag gespeichert');
      }

      setEntityEditorOpen(false);
      setEntityEditorDraft(null);
      setEntityEditorKind(null);
      await onRefresh();
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Änderung konnte nicht gespeichert werden');
    } finally {
      setEntityEditorBusy(false);
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <div className="mb-3 flex items-center justify-between">
          <h3 className="flex items-center gap-1 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
            {motorHeaderPerformanceBrand?.logo ? (
              <img
                src={motorHeaderPerformanceBrand.logo}
                alt=""
                title={motorHeaderPerformanceBrand.name}
                className="h-4 w-4 rounded-sm object-contain"
              />
            ) : (
              <Cog className="h-3.5 w-3.5" />
            )}
            Motor
          </h3>
          <div className="flex items-center gap-2">
            {canEdit && (
              <button
                type="button"
                onClick={() => setEditMode(value => !value)}
                aria-label={editMode ? 'Bearbeiten deaktivieren' : 'Bearbeiten aktivieren'}
                title={editMode ? 'Bearbeiten deaktivieren' : 'Bearbeiten aktivieren'}
                className={`flex items-center justify-center rounded-sm border p-2 transition-colors ${
                  editMode
                    ? 'border-destructive bg-destructive/10 text-destructive'
                    : 'border-destructive/70 bg-background text-destructive hover:bg-destructive/10'
                }`}
              >
                <Pencil className="h-3.5 w-3.5" />
              </button>
            )}
            {engineId && (
              <button
                onClick={toggleRemember}
                disabled={rememberedBusy}
                className={`flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-sm transition-colors disabled:opacity-60 ${
                  remembered
                    ? 'bg-secondary text-secondary-foreground'
                    : 'bg-secondary/40 text-muted-foreground hover:bg-secondary/60'
                }`}
              >
                <Bookmark className={`h-3.5 w-3.5 ${remembered ? 'fill-current' : ''}`} />
                {remembered ? 'Gemerkt' : 'Merken'}
              </button>
            )}
          </div>
        </div>
        <DataCard>
          <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div className="flex items-center gap-4">
              {asText(brand.image, '') !== '–' && asText(brand.image, '') !== '' && (
                <div className="h-16 w-16 rounded-md bg-secondary flex items-center justify-center shrink-0 overflow-hidden self-stretch">
                  <img
                    src={asText(brand.image)}
                    alt={brandName}
                    className="h-12 w-12 object-contain"
                    onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
                  />
                </div>
              )}
              <div>
                <p className="text-sm text-muted-foreground">{brandName} · {seriesName}{modelName ? ` · ${modelName}` : ''}</p>
                <h2 className="text-2xl font-semibold text-foreground">{engineName}</h2>
                {canEdit && editMode && (
                  <div className="mt-3 flex flex-wrap gap-2">
                    <button
                      type="button"
                      onClick={() => openEntityEditor('series', series)}
                      aria-label="Baureihe bearbeiten"
                      title="Baureihe bearbeiten"
                      className="inline-flex items-center gap-1 rounded-sm border border-destructive/70 bg-background px-2 py-1.5 text-xs font-medium text-destructive hover:bg-destructive/10"
                    >
                      <Pencil className="h-3 w-3" />
                      Baureihe
                    </button>
                    {model && (
                      <button
                        type="button"
                        onClick={() => openEntityEditor('model', model)}
                        aria-label="Modell bearbeiten"
                        title="Modell bearbeiten"
                        className="inline-flex items-center gap-1 rounded-sm border border-destructive/70 bg-background px-2 py-1.5 text-xs font-medium text-destructive hover:bg-destructive/10"
                      >
                        <Pencil className="h-3 w-3" />
                        Modell
                      </button>
                    )}
                    <button
                      type="button"
                      onClick={() => openEntityEditor('engine', engine)}
                      aria-label="Motor bearbeiten"
                      title="Motor bearbeiten"
                      className="inline-flex items-center gap-1 rounded-sm border border-destructive/70 bg-background px-2 py-1.5 text-xs font-medium text-destructive hover:bg-destructive/10"
                    >
                      <Pencil className="h-3 w-3" />
                      Motor
                    </button>
                  </div>
                )}
              </div>
            </div>

            <div className="grid gap-3 sm:grid-cols-3 lg:min-w-[560px]">
              {fuel && <MetricCard label="Kraftstoff" value={fuel} icon={<Fuel className="h-4 w-4" />} />}
              <MetricCard label="Leistung" value={horsepower ? `${horsepower} PS` : '–'} icon={<Gauge className="h-4 w-4" />} />
              <MetricCard label="Drehmoment" value={torque ? `${torque} Nm` : '–'} icon={<Zap className="h-4 w-4" />} />
            </div>
          </div>
        </DataCard>
      </div>

      {stageCards.length > 0 && (() => {
        const maxHpGain = Math.max(1, ...stageCards.map(s => (s.hp && horsepower ? s.hp - horsepower : s.hp ?? 0)));
        const maxNmGain = Math.max(1, ...stageCards.map(s => (s.nm && torque ? s.nm - torque : s.nm ?? 0)));
        return (
          <div>
            <div className="mb-3 flex items-center justify-between gap-3">
              <h3 className="flex items-center gap-1 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                <Zap className="h-3.5 w-3.5" /> Tuning-Stufen ({actualStageCount}{isDiesel && showEcoStage ? ' + Eco' : ''})
              </h3>
              {canEdit && editMode && canCreateStage && (
                <button
                  type="button"
                  onClick={() => openStageEditor()}
                  className="inline-flex items-center gap-1.5 rounded-sm border border-border bg-primary px-3 py-1.5 text-xs font-semibold text-primary-foreground"
                >
                  <Plus className="h-3.5 w-3.5" />
                  Neue Stage
                </button>
              )}
            </div>
            <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
              {stageCards.map((stage, idx) => {
                return (
	                  <DataCard key={stage.id} className={`space-y-5 ${stage.active ? '' : 'opacity-60'}`}>
	                    <div className="flex items-start justify-between gap-3">
	                      <div className="flex items-center gap-3">
                        {stage.isEco
                          ? <Leaf className="h-8 w-8 shrink-0 text-green-500" />
                          : <StageIcon index={idx} className="h-8 w-auto shrink-0" isEco={false} />
                        }
                        <div>
                          <p className={`text-lg font-semibold ${stage.isEco ? 'text-green-500' : 'text-foreground'}`}>{stage.name}</p>
                          {!stage.active && <p className="text-xs uppercase tracking-[0.24em] text-muted-foreground">Inaktiv</p>}
                        </div>
	                      </div>
	                      <div className="flex shrink-0 flex-col items-end gap-2">
	                        {!stage.isEco && editMode && canEdit && (
	                          <div className="flex items-center gap-2">
                            <button
                              type="button"
                              onClick={() => openStageEditor(stage)}
                              aria-label="Stage bearbeiten"
                              title="Stage bearbeiten"
                              className="inline-flex items-center justify-center rounded-sm border border-destructive/70 bg-background p-2 text-destructive hover:bg-destructive/10"
                            >
                              <Pencil className="h-3 w-3" />
                            </button>
                          </div>
                        )}
                      </div>
                    </div>
	                    {stage.description && stage.description !== '–' && (
	                      <p className="text-sm text-muted-foreground">{stage.description}</p>
	                    )}
	                    <div className="space-y-4 pt-1">
	                      <StagePowerBar
                        label="Leistung"
                        unit="PS"
                        base={horsepower}
                        value={stage.hp}
                        maxGain={maxHpGain}
                        isEco={stage.isEco}
                      />
                      <StagePowerBar
                        label="Drehmoment"
                        unit="Nm"
                        base={torque}
                        value={stage.nm}
                        maxGain={maxNmGain}
                        isEco={stage.isEco}
	                        isTorque={!stage.isEco}
	                      />
	                    </div>
	                    {stage.price !== '' && (
	                      <div className="flex items-center justify-between border-t border-border/60 pt-3 text-sm">
	                        <span className="font-medium text-muted-foreground">Preis</span>
	                        <span className="inline-flex items-center gap-1 font-semibold tabular-nums text-foreground">
	                          <BadgeEuro className="h-3.5 w-3.5 text-muted-foreground" />
	                          {stage.price}
	                        </span>
	                      </div>
	                    )}
	                  </DataCard>
                );
              })}
            </div>
          </div>
        );
      })()}

      <Dialog open={stageEditorOpen} onOpenChange={open => {
        setStageEditorOpen(open);
        if (!open) {
          setStageEditorDraft(null);
          setStageEditorSourceId(null);
        }
      }}>
        <DialogContent
          className="vdb-edit-modal max-w-3xl"
          onCloseAutoFocus={event => event.preventDefault()}
        >
          <DialogHeader>
            <DialogTitle>{stageEditorDraft?.id ? 'Stage bearbeiten' : 'Neue Stage anlegen'}</DialogTitle>
            <DialogDescription>
              Änderungen werden direkt im Datenkontext gespeichert. Löschen ist hier nicht vorgesehen.
            </DialogDescription>
          </DialogHeader>
          {stageEditorDraft && (
            <div className="grid gap-4 md:grid-cols-2">
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">Name</span>
                <input
                  value={stageEditorDraft.name}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, name: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">Slug</span>
                <input
                  value={stageEditorDraft.slug}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, slug: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm md:col-span-2">
                <span className="text-muted-foreground">Beschreibung</span>
                <textarea
                  value={stageEditorDraft.description}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, description: event.target.value } : current)}
                  className="min-h-24 rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">Preis</span>
                <input
                  value={stageEditorDraft.price}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, price: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">ECU</span>
                <input
                  value={stageEditorDraft.ecu}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, ecu: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">Leistung (HP)</span>
                <input
                  value={stageEditorDraft.hp}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, hp: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">Drehmoment (Nm)</span>
                <input
                  value={stageEditorDraft.nm}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, nm: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">Original PS</span>
                <input
                  value={stageEditorDraft.originalPs}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, originalPs: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">Original Nm</span>
                <input
                  value={stageEditorDraft.originalNm}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, originalNm: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">RPM Start</span>
                <input
                  value={stageEditorDraft.rpmStart}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, rpmStart: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">RPM Ende</span>
                <input
                  value={stageEditorDraft.rpmEnd}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, rpmEnd: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm md:col-span-2">
                <span className="text-muted-foreground">Engine Type</span>
                <input
                  value={stageEditorDraft.engineType}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, engineType: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="flex items-center gap-2 text-sm md:col-span-2">
                <input
                  type="checkbox"
                  checked={stageEditorDraft.active}
                  onChange={event => setStageEditorDraft(current => current ? { ...current, active: event.target.checked } : current)}
                />
                <span>Aktiv</span>
              </label>
            </div>
          )}
          <DialogFooter>
            <button
              type="button"
              onClick={() => setStageEditorOpen(false)}
              className="rounded-sm border border-border px-4 py-2 text-sm text-muted-foreground"
              disabled={stageEditorBusy}
            >
              Abbrechen
            </button>
            <button
              type="button"
              onClick={saveStageDraft}
              disabled={stageEditorBusy || !stageEditorDraft?.name.trim()}
              className="inline-flex items-center gap-2 rounded-sm bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-60"
            >
              <Save className="h-4 w-4" />
              Speichern
            </button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {(ecuCards.length > 0 || ecuCards.some(ecu => ecu.options.length > 0) || engineOptionCards.length > 0 || stageCards.some(s => s.dynoChart) || gearboxCards.length > 0) && (
        <EcuTabSection
          ecuCards={ecuCards}
          engineOptions={engineOptionCards}
          optionHints={
            ecus.flatMap(ecu => Array.isArray(ecu.optionHints) ? ecu.optionHints as Array<Record<string, unknown>> : [])
              .filter((group, i, arr) => arr.findIndex(g => g.masterOption === group.masterOption) === i)
          }
          einbauHinweise={Array.isArray(details.einbauHinweise) ? details.einbauHinweise as Array<Record<string, unknown>> : []}
          dynoStages={stageCards.filter(s => s.dynoChart).map(s => ({ id: s.id, name: s.name, dynoChart: s.dynoChart as DynoChart }))}
          gearboxCards={gearboxCards}
          apiData={details}
          editMode={editMode}
          canEdit={canEdit}
          onEditOption={handleEditOption}
          onEditEcu={handleEditEcu}
          onEditGearbox={handleEditGearbox}
          onEditHint={handleEditHint}
          onEditToolEntry={handleEditToolEntry}
        />
      )}

      <Dialog open={entityEditorOpen} onOpenChange={open => {
        setEntityEditorOpen(open);
        if (!open) {
          setEntityEditorDraft(null);
          setEntityEditorKind(null);
        }
      }}>
        <DialogContent
          className="vdb-edit-modal max-w-3xl"
          onCloseAutoFocus={event => event.preventDefault()}
        >
          <DialogHeader>
            <DialogTitle>
              {entityEditorKind === 'series' ? 'Baureihe bearbeiten'
                : entityEditorKind === 'model' ? 'Modell bearbeiten'
                  : entityEditorKind === 'engine' ? 'Motor bearbeiten'
                    : entityEditorKind === 'option' ? 'Option bearbeiten'
                      : entityEditorKind === 'ecu' ? 'ECU bearbeiten'
                        : entityEditorKind === 'gearbox' ? 'Getriebe bearbeiten'
                          : entityEditorKind === 'hint' ? 'Hinweis bearbeiten'
                            : entityEditorKind === 'tool' ? 'Tool-Eintrag bearbeiten'
                      : 'Bearbeiten'}
            </DialogTitle>
            <DialogDescription>
              Änderungen werden direkt im Datenkontext gespeichert.
            </DialogDescription>
          </DialogHeader>
          {entityEditorDraft && entityEditorKind && (
            <div className="grid gap-4 md:grid-cols-2">
              {entityEditorKind !== 'gearbox' && (
                <label className="grid gap-1 text-sm md:col-span-2">
                  <span className="text-muted-foreground">
                    {entityEditorKind === 'hint' ? 'Titel' : 'Name'}
                  </span>
                  <input
                    value={entityEditorDraft.name}
                    onChange={event => setEntityEditorDraft(current => current ? { ...current, name: event.target.value } : current)}
                    className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                  />
                </label>
              )}
              {entityEditorKind === 'gearbox' && (
                <label className="grid gap-1 text-sm md:col-span-2">
                  <span className="text-muted-foreground">Titel</span>
                  <input
                    value={entityEditorDraft.title}
                    onChange={event => setEntityEditorDraft(current => current ? { ...current, title: event.target.value } : current)}
                    className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                  />
                </label>
              )}
              <label className="grid gap-1 text-sm md:col-span-2">
                <span className="text-muted-foreground">Beschreibung</span>
                <textarea
                  value={entityEditorDraft.description}
                  onChange={event => setEntityEditorDraft(current => current ? { ...current, description: event.target.value } : current)}
                  className="min-h-24 rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">Slug</span>
                <input
                  value={entityEditorDraft.slug}
                  onChange={event => setEntityEditorDraft(current => current ? { ...current, slug: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>
              <label className="grid gap-1 text-sm">
                <span className="text-muted-foreground">Sortierung</span>
                <input
                  value={entityEditorDraft.sorting}
                  onChange={event => setEntityEditorDraft(current => current ? { ...current, sorting: event.target.value } : current)}
                  className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                />
              </label>

              {entityEditorKind === 'engine' && (
                <>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Leistung (PS)</span>
                    <input
                      value={entityEditorDraft.horsepower}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, horsepower: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Drehmoment (Nm)</span>
                    <input
                      value={entityEditorDraft.torque}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, torque: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Kraftstoff</span>
                    <input
                      value={entityEditorDraft.fuel}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, fuel: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Hubraum</span>
                    <input
                      value={entityEditorDraft.displacement}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, displacement: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">ECUs</span>
                    <input
                      value={entityEditorDraft.ecus}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, ecus: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                </>
              )}

              {entityEditorKind === 'ecu' && (
                <>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Hersteller</span>
                    <select
                      value={entityEditorDraft.manufacturerId}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, manufacturerId: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    >
                      <option value="">-</option>
                      {ecuManufacturers.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Tool-Hersteller</span>
                    <select
                      value={entityEditorDraft.toolManufacturerId}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, toolManufacturerId: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    >
                      <option value="">-</option>
                      {toolManufacturers.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                  </label>
                </>
              )}

              {entityEditorKind === 'ecu' && (
                <>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Typ</span>
                    <input
                      value={entityEditorDraft.type}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, type: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Steuergerät-Kind</span>
                    <input
                      value={entityEditorDraft.controlUnitKind}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, controlUnitKind: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                  <label className="grid gap-1 text-sm md:col-span-2">
                    <span className="text-muted-foreground">Bild</span>
                    <input
                      value={entityEditorDraft.icon}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, icon: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                </>
              )}

              {entityEditorKind === 'gearbox' && (
                <>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Typ</span>
                    <select
                      value={entityEditorDraft.gearboxTypeId}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, gearboxTypeId: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    >
                      <option value="">-</option>
                      {gearboxTypes.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Hersteller</span>
                    <select
                      value={entityEditorDraft.manufacturerId}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, manufacturerId: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    >
                      <option value="">-</option>
                      {ecuManufacturers.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Icon</span>
                    <input
                      value={entityEditorDraft.icon}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, icon: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                </>
              )}

              {entityEditorKind === 'hint' && (
                <>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Master-Option</span>
                    <select
                      value={entityEditorDraft.ecuOptionMasterId}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, ecuOptionMasterId: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    >
                      <option value="">-</option>
                      {optionMasters.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Aktive ECU</span>
                    <select
                      value={entityEditorDraft.activeEcuId}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, activeEcuId: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    >
                      <option value="">-</option>
                      {hintEcus.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                  </label>
                  <label className="grid gap-1 text-sm md:col-span-2">
                    <span className="text-muted-foreground">Bilder</span>
                    <select
                      multiple
                      value={entityEditorDraft.imageIds}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, imageIds: Array.from(event.currentTarget.selectedOptions).map(option => option.value) } : current)}
                      className="min-h-36 rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    >
                      {mediaAssets.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                    <p className="text-xs text-muted-foreground">Mehrfachauswahl mit Strg/Cmd.</p>
                  </label>
                  <label className="grid gap-1 text-sm md:col-span-2">
                    <span className="text-muted-foreground">Hinweis-Text</span>
                    <textarea
                      value={entityEditorDraft.description}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, description: event.target.value } : current)}
                      className="min-h-24 rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                </>
              )}

              {entityEditorKind === 'tool' && (
                <>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Tool-Hersteller</span>
                    <select
                      value={entityEditorDraft.toolManufacturerId}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, toolManufacturerId: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    >
                      <option value="">-</option>
                      {toolManufacturers.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                  </label>
                  <label className="grid gap-1 text-sm md:col-span-2">
                    <span className="text-muted-foreground">Methoden</span>
                    <select
                      multiple
                      value={entityEditorDraft.methodIds}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, methodIds: Array.from(event.currentTarget.selectedOptions).map(option => option.value) } : current)}
                      className="min-h-36 rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    >
                      {toolMethods.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                    <p className="text-xs text-muted-foreground">Mehrfachauswahl mit Strg/Cmd.</p>
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Position</span>
                    <input
                      value={entityEditorDraft.position}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, position: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                </>
              )}

              {entityEditorKind === 'option' && (
                <>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Preis</span>
                    <input
                      value={entityEditorDraft.price}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, price: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                  <label className="grid gap-1 text-sm">
                    <span className="text-muted-foreground">Icon</span>
                    <input
                      value={entityEditorDraft.icon}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, icon: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                  <label className="grid gap-1 text-sm md:col-span-2">
                    <span className="text-muted-foreground">Tooltip</span>
                    <input
                      value={entityEditorDraft.tooltipText}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, tooltipText: event.target.value } : current)}
                      className="rounded-sm border border-border bg-background px-3 py-2 text-sm"
                    />
                  </label>
                  <label className="flex items-center gap-2 text-sm">
                    <input
                      type="checkbox"
                      checked={entityEditorDraft.isMainOption}
                      onChange={event => setEntityEditorDraft(current => current ? { ...current, isMainOption: event.target.checked } : current)}
                    />
                    <span>Hauptoption</span>
                  </label>
                </>
              )}

              <label className="flex items-center gap-2 text-sm md:col-span-2">
                <input
                  type="checkbox"
                  checked={entityEditorDraft.active}
                  onChange={event => setEntityEditorDraft(current => current ? { ...current, active: event.target.checked } : current)}
                />
                <span>Aktiv</span>
              </label>
            </div>
          )}
          <DialogFooter>
            <button
              type="button"
              onClick={() => setEntityEditorOpen(false)}
              className="rounded-sm border border-border px-4 py-2 text-sm text-muted-foreground"
              disabled={entityEditorBusy}
            >
              Abbrechen
            </button>
            <button
              type="button"
              onClick={saveEntityDraft}
              disabled={entityEditorBusy || !entityEditorDraft?.name.trim()}
              className="inline-flex items-center gap-2 rounded-sm bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-60"
            >
              <Save className="h-4 w-4" />
              Speichern
            </button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <ApiDebugBlock data={details} />
    </div>
  );
}

function ApiDebugBlock({ data }: { data: unknown }) {
  const [open, setOpen] = useState(false);

  return (
    <div className="rounded-sm border border-border/50">
      <button
        onClick={() => setOpen(o => !o)}
        className="w-full flex items-center justify-between px-4 py-2.5 text-xs font-mono text-muted-foreground hover:text-foreground transition-colors"
      >
        <span className="uppercase tracking-[0.2em]">API Response (raw)</span>
        <span>{open ? '▲' : '▼'}</span>
      </button>
      {open && (
        <pre className="overflow-x-auto p-4 text-[11px] leading-relaxed font-mono text-muted-foreground bg-background/40 border-t border-border/50 max-h-[600px]">
          {JSON.stringify(data, null, 2)}
        </pre>
      )}
    </div>
  );
}

function PanelHeading({ title, subtitle }: { title: string; subtitle: string }) {
  return (
    <div className="mb-4">
      <p className="text-xs font-semibold uppercase tracking-[0.24em] text-muted-foreground">{title}</p>
      <p className="mt-1 text-sm text-muted-foreground">{subtitle}</p>
    </div>
  );
}

function MetricCard({ label, value, icon }: { label: string; value: string; icon: React.ReactNode }) {
  return (
    <div className="rounded-md border border-border bg-background/50 px-4 py-3 flex flex-col items-center text-center">
      <div className="mb-2 flex items-center justify-center gap-2 text-muted-foreground">
        {icon}
        <span className="text-xs uppercase tracking-[0.24em]">{label}</span>
      </div>
      <p className="text-lg font-semibold text-foreground">{value}</p>
    </div>
  );
}

function MetricInline({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-md bg-background/50 px-3 py-2">
      <p className="text-[10px] uppercase tracking-[0.24em] text-muted-foreground">{label}</p>
      <p className="mt-1 text-sm font-medium text-foreground">{value}</p>
    </div>
  );
}

function MetaBadge({ icon, label }: { icon: React.ReactNode; label: string }) {
  return (
    <span className="inline-flex items-center gap-2 rounded-full border border-border bg-background/60 px-3 py-1.5 text-xs font-medium text-foreground">
      <span className="text-muted-foreground">{icon}</span>
      {label}
    </span>
  );
}

function DetailRow({ label, value, mono = false }: { label: string; value: string; mono?: boolean }) {
  return (
    <div className="flex items-start justify-between gap-6 border-b border-border/60 pb-3 last:border-b-0 last:pb-0">
      <span className="text-xs uppercase tracking-[0.24em] text-muted-foreground">{label}</span>
      <span className={`text-right text-sm text-foreground ${mono ? 'font-mono-data break-all' : ''}`}>{value || '–'}</span>
    </div>
  );
}

type HintImage = { src: string; label: string };
type HintItem = {
  id: number;
  title: string;
  description: string;
  ecuOptionMaster: string;
  ecuOptionMasterId: number | null;
  activeEcuId: number | null;
  active: boolean;
  images: HintImage[];
  imageIds: number[];
};

function Lightbox({ images, startIndex, onClose }: { images: HintImage[]; startIndex: number; onClose: () => void }) {
  const [idx, setIdx] = useState(startIndex);

  useEffect(() => {
    const handler = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose();
      if (e.key === 'ArrowRight') setIdx(i => (i + 1) % images.length);
      if (e.key === 'ArrowLeft') setIdx(i => (i - 1 + images.length) % images.length);
    };
    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, [images.length, onClose]);

  return (
    <div className="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/90" onClick={onClose}>
      <button onClick={onClose} className="absolute top-4 right-4 text-white/70 hover:text-white transition-colors">
        <X className="h-7 w-7" />
      </button>
      {images.length > 1 && (
        <>
          <button
            onClick={e => { e.stopPropagation(); setIdx(i => (i - 1 + images.length) % images.length); }}
            className="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-colors bg-black/40 rounded-full p-2"
          >
            <ChevronLeft className="h-6 w-6" />
          </button>
          <button
            onClick={e => { e.stopPropagation(); setIdx(i => (i + 1) % images.length); }}
            className="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-colors bg-black/40 rounded-full p-2"
          >
            <ChevronRight className="h-6 w-6" />
          </button>
        </>
      )}
      <div className="flex flex-col items-center gap-3" onClick={e => e.stopPropagation()}>
        {images[idx].label && (
          <p className="text-sm font-semibold text-white/80 tracking-wide">{images[idx].label}</p>
        )}
        <img
          src={images[idx].src}
          alt={images[idx].label}
          className="max-h-[80vh] max-w-[90vw] object-contain rounded-sm shadow-2xl"
        />
      </div>
      {images.length > 1 && (
        <div className="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2">
          {images.map((_, i) => (
            <button
              key={i}
              onClick={e => { e.stopPropagation(); setIdx(i); }}
              className={`h-1.5 rounded-full transition-all ${i === idx ? 'w-6 bg-white' : 'w-1.5 bg-white/40'}`}
            />
          ))}
        </div>
      )}
    </div>
  );
}

function HintsTab({ hints, options, onEditHint }: { hints: HintItem[]; options: OptionItem[]; onEditHint?: (hint: HintItem) => void }) {
  const [lightbox, setLightbox] = useState<{ images: HintImage[]; index: number } | null>(null);
  const hintsKey = hints.map(hint => hint.id).join('|');
  const [openHints, setOpenHints] = useState<Record<number, boolean>>({});
  const normalizeOptionLabel = (value: string): string => value.trim().toLowerCase();

  useEffect(() => {
    setOpenHints(previous => {
      const next: Record<number, boolean> = {};
      for (const hint of hints) {
        next[hint.id] = previous[hint.id] ?? false;
      }

      return next;
    });
  }, [hintsKey]);

  return (
    <>
      <div className="space-y-3">
        {hints.map(hint => {
          const isOpen = openHints[hint.id] === true;
          const hintOptionName = normalizeOptionLabel(hint.ecuOptionMaster);
          const matchingOption = options.find(option => {
            const optionName = normalizeOptionLabel(option.name);
            const masterName = normalizeOptionLabel(option.masterOptionName);

            return optionName === hintOptionName || masterName === hintOptionName;
          });
          const optionIcon = matchingOption?.icon ?? '';

          return (
            <DataCard key={hint.id} className="overflow-visible p-0">
              <button
                type="button"
                onClick={() => setOpenHints(previous => ({
                  ...previous,
                  [hint.id]: previous[hint.id] === false,
                }))}
                className="flex w-full items-center justify-between gap-4 px-4 py-4 text-left transition-colors hover:bg-muted/30"
                aria-expanded={isOpen}
              >
                <span className="flex min-w-0 items-center gap-3">
                  <span className="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-sm bg-destructive/10 text-destructive">
                    {optionIcon && optionIcon !== '–' ? (
                      <img
                        src={optionIcon}
                        alt=""
                        className="h-7 w-7 object-contain brightness-0 [filter:invert(18%)_sepia(92%)_saturate(3564%)_hue-rotate(347deg)_brightness(88%)_contrast(92%)]"
                        onError={event => { (event.currentTarget as HTMLImageElement).style.display = 'none'; }}
                      />
                    ) : (
                      <Info className="h-4 w-4 text-destructive" />
                    )}
                  </span>
                  <span className="min-w-0">
                    <span className="block truncate text-sm font-extrabold uppercase tracking-[0.16em] text-foreground">
                      {hint.title || 'Hinweis'}
                    </span>
                    <span className="block truncate text-xs text-muted-foreground">
                      {hint.ecuOptionMaster}
                    </span>
                  </span>
                </span>
                <span className="flex shrink-0 items-center gap-2">
                  {onEditHint && (
                    <span
                      role="button"
                      tabIndex={0}
                      onClick={event => {
                        event.stopPropagation();
                        onEditHint(hint);
                      }}
                      onKeyDown={event => {
                        if (event.key === 'Enter' || event.key === ' ') {
                          event.preventDefault();
                          event.stopPropagation();
                          onEditHint(hint);
                        }
                      }}
                      aria-label="Hinweis bearbeiten"
                      title="Hinweis bearbeiten"
                      className="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-destructive/70 bg-background text-destructive hover:bg-destructive/10"
                    >
                      <Pencil className="h-3 w-3" />
                    </span>
                  )}
                  <ChevronRight className={`h-5 w-5 text-muted-foreground transition-transform ${isOpen ? 'rotate-90' : ''}`} />
                </span>
              </button>

              {isOpen && (
                <div className="space-y-4 border-t border-border/60 p-4">
                  {hint.description && (
                    <div className="whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{hint.description}</div>
                  )}
                  {hint.images.length > 0 && (
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                      {hint.images.map((img, i) => (
                        <button
                          key={i}
                          onClick={() => setLightbox({ images: hint.images, index: i })}
                          className="group flex flex-col gap-1.5 text-left"
                        >
                          {img.label && (
                            <span className="text-[10px] uppercase tracking-[0.2em] text-muted-foreground transition-colors group-hover:text-foreground">{img.label}</span>
                          )}
                          <div className="rounded-sm border border-border bg-background/60 p-2 transition-colors group-hover:border-muted-foreground/40">
                            <img src={img.src} alt={img.label} className="block h-auto max-h-[360px] w-full object-contain" />
                          </div>
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              )}
            </DataCard>
          );
        })}
      </div>
      {lightbox && (
        <Lightbox images={lightbox.images} startIndex={lightbox.index} onClose={() => setLightbox(null)} />
      )}
    </>
  );
}

type ToolEntryItem = { id: number; toolManufacturer: string; toolManufacturerLogo: string; toolManufacturerId: number | null; methodIds: number[]; methods: string[]; position: number | null; kind: 'ecu' | 'gearbox' };
type SelectItem = { id: number; name: string };
type EditOptions = {
  toolManufacturers: SelectItem[];
  ecuManufacturers: SelectItem[];
  gearboxTypes: SelectItem[];
  optionMasters: SelectItem[];
  toolMethods: SelectItem[];
  mediaAssets: SelectItem[];
  ecus: SelectItem[];
};
type StageEditorDraft = {
  id?: number;
  name: string;
  description: string;
  price: string;
  hp: string;
  nm: string;
  ecu: string;
  slug: string;
  originalPs: string;
  originalNm: string;
  rpmStart: string;
  rpmEnd: string;
  engineType: string;
  active: boolean;
};
type EntityEditorKind = 'series' | 'model' | 'engine' | 'option' | 'ecu' | 'gearbox' | 'hint' | 'tool';
type EntityEditorDraft = {
  id?: number;
  name: string;
  description: string;
  slug: string;
  active: boolean;
  sorting: string;
  price: string;
  tooltipText: string;
  icon: string;
  horsepower: string;
  torque: string;
  ecus: string;
  fuel: string;
  displacement: string;
  isMainOption: boolean;
  controlUnitKind: string;
  type: string;
  title: string;
  position: string;
  ecuOptionMasterId: string;
  activeEcuId: string;
  manufacturer: string;
  manufacturerId: string;
  toolManufacturerId: string;
  gearboxTypeId: string;
  methodIds: string[];
  imageIds: string[];
  toolKind: 'ecu' | 'gearbox';
};
type EcuCard = { id: number; name: string; kind: string; manufacturer: string; manufacturerId: number | null; toolManufacturerId: number | null; manufacturerLogo: string; optionCount: number; options: OptionItem[]; toolEntries: ToolEntryItem[]; description: string; image: string; active: boolean; controlUnitKind: string; type: string };
type GearboxCard = { id: number; title: string; type: string; typeId: number | null; manufacturer: string; manufacturerId: number | null; description: string; toolEntries: ToolEntryItem[]; active: boolean; sorting: number; icon: string };
type DynoCurve = { power?: Array<[number, number]>; torque?: Array<[number, number]> };
type DynoChart = { original?: DynoCurve; optimized?: DynoCurve };
type DynoStage = { id: number; name: string; dynoChart: DynoChart };

function isDynoChart(value: unknown): value is DynoChart {
  return typeof value === 'object' && value !== null;
}

function dedupeOptions(options: OptionItem[]): OptionItem[] {
  const seen = new Set<string>();
  return options.filter(option => {
    const key = option.masterOptionName || option.name;
    if (seen.has(key)) return false;
    seen.add(key);
    return true;
  });
}

function resolveCategoryIcon(icon: string, category = ''): string {
  const value = icon.trim();
  if (value !== '' && value !== '–') {
    return value;
  }

  switch (category.toLowerCase()) {
    case 'abgassystem':
      return 'lucide:wind';
    case 'diagnose':
      return 'lucide:stethoscope';
    case 'kraftstoff':
      return 'lucide:fuel';
    case 'motor':
      return 'lucide:cog';
    case 'performance':
      return 'lucide:gauge';
    case 'service':
      return 'lucide:wrench';
    case 'sound':
      return 'lucide:volume-2';
    case 'spezial':
      return 'lucide:sparkles';
    default:
      return '';
  }
}

function CategoryIcon({ icon, category = '', className = 'h-3.5 w-3.5 text-muted-foreground' }: { icon: string; category?: string; className?: string }) {
  const value = resolveCategoryIcon(icon, category);
  if (value !== '' && !value.includes(':')) {
    const src = value.startsWith('/') || /^https?:\/\//i.test(value) ? value : `/${value}`;
    return <img src={src} alt="" className={`${className} object-contain`} onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }} />;
  }

  switch (value) {
    case 'lucide:wind':
      return <Wind className={className} />;
    case 'lucide:stethoscope':
      return <Stethoscope className={className} />;
    case 'lucide:fuel':
      return <Fuel className={className} />;
    case 'lucide:cog':
      return <Cog className={className} />;
    case 'lucide:gauge':
      return <Gauge className={className} />;
    case 'lucide:wrench':
      return <Wrench className={className} />;
    case 'lucide:volume-2':
      return <Volume2 className={className} />;
    case 'lucide:sparkles':
      return <Sparkles className={className} />;
    default:
      return <Settings2 className={className} />;
  }
}

function groupOptionsByCategory(options: OptionItem[]): Array<{ category: string; icon: string; sorting: number; showHeading: boolean; options: OptionItem[] }> {
  const hasAnyCategory = options.some(option => option.category !== '' && option.category !== '–');
  if (!hasAnyCategory) {
    return [{ category: 'Optionen', icon: '', sorting: 0, showHeading: false, options }];
  }

  const groups = new Map<string, { icon: string; sorting: number; options: OptionItem[] }>();
  for (const option of options) {
    const category = option.category !== '' && option.category !== '–' ? option.category : 'Ohne Kategorie';
    const existing = groups.get(category) ?? { icon: '', sorting: Number.MAX_SAFE_INTEGER, options: [] };
    groups.set(category, {
      icon: existing.icon || resolveCategoryIcon(option.categoryIcon, category),
      sorting: Math.min(existing.sorting, option.categorySorting),
      options: [...existing.options, option],
    });
  }

  return Array.from(groups.entries())
    .sort(([left, leftGroup], [right, rightGroup]) => {
      if (left === 'Ohne Kategorie') return 1;
      if (right === 'Ohne Kategorie') return -1;
      if (leftGroup.sorting !== rightGroup.sorting) return leftGroup.sorting - rightGroup.sorting;
      return left.localeCompare(right, 'de');
    })
    .map(([category, group]) => ({
      category,
      icon: group.icon,
      sorting: group.sorting,
      showHeading: true,
      options: group.options,
    }));
}

function EcuTabSection({ ecuCards, engineOptions, optionHints, einbauHinweise, dynoStages, gearboxCards, apiData, editMode, canEdit, onEditOption, onEditEcu, onEditGearbox, onEditHint, onEditToolEntry }: { ecuCards: EcuCard[]; engineOptions: OptionItem[]; optionHints: Array<Record<string, unknown>>; einbauHinweise: Array<Record<string, unknown>>; dynoStages: DynoStage[]; gearboxCards: GearboxCard[]; apiData: Record<string, unknown>; editMode: boolean; canEdit: boolean; onEditOption: (option: OptionItem) => void; onEditEcu: (ecu: EcuCard) => void; onEditGearbox: (gearbox: GearboxCard) => void; onEditHint: (hint: HintItem) => void; onEditToolEntry: (entry: ToolEntryItem, kind: 'ecu' | 'gearbox', parent: { id: number; name: string }) => void; }) {
  // Build enrichment map: ecuOptionMaster name -> {images, text} from EinbauHinweise
  const einbauMap = new Map<string, { images: HintImage[]; texts: string[] }>();
  for (const h of einbauHinweise) {
    const masterName = asText(h.ecuOptionMaster, '');
    if (masterName === '–' || masterName === '') continue;
    const imgs = (Array.isArray(h.images) ? h.images as string[] : [])
      .filter(src => src !== '' && src !== '–')
      .map(src => ({ src, label: asText(h.title, masterName) }));
    const desc = asText(h.description, '');
    const extraText = desc !== '–' && desc !== '' ? desc : '';
    if (imgs.length === 0 && extraText === '') continue;
    const existing = einbauMap.get(masterName) ?? { images: [], texts: [] };
    einbauMap.set(masterName, {
      images: [...existing.images, ...imgs],
      texts: extraText ? [...existing.texts, extraText] : existing.texts,
    });
  }

  const hints: HintItem[] = einbauHinweise.map((hint, i) => {
    const label = asText(hint.ecuOptionMaster, `Hinweis ${i + 1}`);
    const enrich = einbauMap.get(label);
    return {
      id: asNumber(hint.id) ?? i,
      title: asText(hint.title, label),
      description: stripHtml(asText(hint.description, '')),
      ecuOptionMaster: label,
      ecuOptionMasterId: asNumber(hint.ecuOptionMasterId),
      activeEcuId: asNumber(hint.activeEcuId),
      active: hint.active !== false,
      images: enrich?.images ?? [],
      imageIds: Array.isArray(hint.imageIds) ? hint.imageIds.map(value => asNumber(value) ?? 0).filter(value => value > 0) : [],
    };
  }).filter(h => h.title !== '' || h.description !== '' || h.images.length > 0);

  const hasHints = hints.length > 0;
  const hasDyno = dynoStages.length > 0;
  const hasGearboxes = gearboxCards.length > 0;
  const hintMasterNames = new Set(optionHints.map(group => asText(group.masterOption, '')));
  const [tab, setTab] = useState<'options' | 'ecus' | 'tools' | 'hints' | 'dyno' | 'gearboxes'>('options');
  const allOptions = dedupeOptions([...ecuCards.flatMap(ecu => ecu.options), ...engineOptions]);
  const optionGroups = groupOptionsByCategory(allOptions);
  const optionGroupKey = optionGroups.map(group => group.category).join('|');
  const [openOptionCategories, setOpenOptionCategories] = useState<Record<string, boolean>>({});
  useEffect(() => {
    setOpenOptionCategories(previous => {
      const next: Record<string, boolean> = {};
      for (const group of optionGroups) {
        next[group.category] = previous[group.category] ?? true;
      }

      return next;
    });
  }, [optionGroupKey]);

  // Group by manufacturer, then list MCU/TCU per group
  const groups = Object.values(
    ecuCards.reduce<Record<string, { manufacturer: string; logo: string; ecus: EcuCard[] }>>((acc, ecu) => {
      const key = ecu.manufacturer.toLowerCase();
      if (!acc[key]) acc[key] = { manufacturer: ecu.manufacturer, logo: ecu.manufacturerLogo, ecus: [] };
      acc[key].ecus.push(ecu);
      return acc;
    }, {})
  );

  // Tools tab: ECUs and gearboxes that carry tool entries, grouped by their (hardware) manufacturer
  type ToolableItem = { id: string; name: string; kind: 'ecu' | 'gearbox'; manufacturer: string; manufacturerLogo: string; toolEntries: ToolEntryItem[] };
  const toolableItems: ToolableItem[] = [
    ...ecuCards.map(e => ({ id: `ecu-${e.id}`, name: e.name, kind: 'ecu' as const, manufacturer: e.manufacturer, manufacturerLogo: e.manufacturerLogo, toolEntries: e.toolEntries })),
    ...gearboxCards.map(g => ({ id: `gearbox-${g.id}`, name: g.title, kind: 'gearbox' as const, manufacturer: g.manufacturer, manufacturerLogo: '', toolEntries: g.toolEntries })),
  ].filter(item => item.toolEntries.length > 0);
  const hasTools = toolableItems.length > 0;
  const toolGroups = Object.values(
    toolableItems.reduce<Record<string, { manufacturer: string; logo: string; items: ToolableItem[] }>>((acc, item) => {
      const key = item.manufacturer.toLowerCase();
      if (!acc[key]) acc[key] = { manufacturer: item.manufacturer, logo: item.manufacturerLogo, items: [] };
      if (!acc[key].logo && item.manufacturerLogo) acc[key].logo = item.manufacturerLogo;
      acc[key].items.push(item);
      return acc;
    }, {})
  );

  const tabs = [
    { key: 'options' as const, label: `Optionen (${allOptions.length})`, icon: <Settings2 className="h-3.5 w-3.5" />, redIcon: false },
    { key: 'ecus' as const, label: `Steuergeräte (${ecuCards.length})`, icon: <Zap className="h-3.5 w-3.5" />, redIcon: false },
    ...(hasTools ? [{ key: 'tools' as const, label: 'Tools', icon: <Wrench className="h-3.5 w-3.5" />, redIcon: false }] : []),
    ...(hasHints ? [{ key: 'hints' as const, label: 'Hinweise', icon: <Info className="h-3.5 w-3.5" />, redIcon: true }] : []),
    ...(hasGearboxes ? [{ key: 'gearboxes' as const, label: 'Getriebe', icon: <Cog className="h-3.5 w-3.5" />, redIcon: false }] : []),
    ...(hasDyno ? [{ key: 'dyno' as const, label: 'Dyno-Chart', icon: <Gauge className="h-3.5 w-3.5" />, redIcon: false }] : []),
  ];

  return (
    <div>
      <div className="flex gap-1 mb-4 border-b border-border">
        {tabs.map(t => (
          <button
            key={t.key}
            onClick={() => setTab(t.key)}
            className={`flex items-center gap-1.5 px-4 py-2 text-xs font-semibold uppercase tracking-wider transition-colors border-b-2 -mb-px ${
              tab === t.key
                ? 'border-destructive text-foreground'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            }`}
          >
            <span className={t.redIcon ? 'text-destructive' : ''}>{t.icon}</span>
            {t.label}
          </button>
        ))}
      </div>

      {tab === 'options' && allOptions.length > 0 && (
        <div className="space-y-3">
          {optionGroups.map(group => {
            if (!group.showHeading) {
              return (
                <DataCard key={group.category}>
	                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
	                      {group.options.map(option => (
	                        <OptionListItem
	                          key={option.id}
	                          option={option}
	                          hasHint={hintMasterNames.has(option.masterOptionName)}
                        onHintClick={hasHints ? () => setTab('hints') : undefined}
                        onEdit={editMode && canEdit ? () => onEditOption(option) : undefined}
                      />
                    ))}
                  </div>
                </DataCard>
              );
            }

            const isOpen = openOptionCategories[group.category] !== false;

            return (
              <div key={group.category} className="space-y-3">
                <DataCard className="overflow-visible p-0">
                  <button
                    type="button"
                    onClick={() => setOpenOptionCategories(previous => ({
                      ...previous,
                      [group.category]: previous[group.category] === false,
                    }))}
                    className="flex w-full items-center justify-between gap-4 px-4 py-4 text-left transition-colors hover:bg-muted/30"
                    aria-expanded={isOpen}
                  >
                    <span className="flex min-w-0 items-center gap-3">
                      <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-destructive/10 text-destructive">
                        <CategoryIcon icon={group.icon} category={group.category} className="h-4 w-4" />
                      </span>
                      <span className="min-w-0">
                        <span className="block truncate text-sm font-extrabold uppercase tracking-[0.16em] text-foreground">
                          {group.category}
                        </span>
                        <span className="block text-xs text-muted-foreground">
                          {group.options.length} {group.options.length === 1 ? 'Option' : 'Optionen'}
                        </span>
                      </span>
                    </span>
                    <ChevronRight className={`h-5 w-5 shrink-0 text-muted-foreground transition-transform ${isOpen ? 'rotate-90' : ''}`} />
                  </button>
                </DataCard>
                {isOpen && (
                  <div>
                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                      {group.options.map(option => (
                        <OptionListItem
                          key={option.id}
                          option={option}
                          hasHint={hintMasterNames.has(option.masterOptionName)}
                          onHintClick={hasHints ? () => setTab('hints') : undefined}
                          onEdit={editMode && canEdit ? () => onEditOption(option) : undefined}
                        />
                      ))}
                    </div>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}

      {tab === 'ecus' && (
        <div className="grid gap-3 lg:grid-cols-2">
          {[...groups].sort((a, b) => {
            const aIsMcu = a.ecus.some(e => e.kind === 'MCU');
            const bIsMcu = b.ecus.some(e => e.kind === 'MCU');
            return aIsMcu === bIsMcu ? 0 : aIsMcu ? -1 : 1;
          }).map(group => {
            const hasMcu = group.ecus.some(e => e.kind === 'MCU');
            const hasTcu = group.ecus.some(e => e.kind === 'TCU');
            return (
            <DataCard key={group.manufacturer} className="space-y-3">
              <div className="flex items-center justify-between gap-3">
                <div className="flex items-center gap-3">
                  {group.logo && group.logo !== '–' && (
                    <div className="h-10 w-10 rounded-sm bg-secondary flex items-center justify-center shrink-0 overflow-hidden">
                      <img
                        src={group.logo}
                        alt={group.manufacturer}
                        className="h-7 w-7 object-contain brightness-0 dark:invert"
                        onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
                      />
                    </div>
                  )}
                  <p className="text-base font-semibold text-foreground">{group.manufacturer}</p>
                </div>
                <div className="flex items-center gap-1.5 text-muted-foreground shrink-0">
                  {hasMcu && <Cpu className="h-4 w-4" />}
                  {hasTcu && <Cog className="h-4 w-4" />}
                </div>
              </div>
              <div className="space-y-2">
                {group.ecus.map(ecu => (
                  <div key={ecu.id} className="flex items-center justify-between gap-3 rounded-sm bg-background/40 px-3 py-2">
                    <div className="min-w-0">
                      <p className="text-sm text-foreground">{ecu.name}</p>
                      <p className="text-[11px] text-muted-foreground">{ecu.description || ecu.type}</p>
                    </div>
                    <div className="flex items-center gap-2">
                      <StatusBadge
                        status={ecu.kind === 'TCU' ? 'warning' : 'new'}
                        label={ecu.kind === 'TCU' ? 'TCU (Getriebe)' : ecu.kind === 'MCU' ? 'MCU (Motorsteuergerät)' : ecu.kind}
                      />
                      {editMode && canEdit && (
                        <button
                          type="button"
                          onClick={() => onEditEcu(ecu)}
                          aria-label="ECU bearbeiten"
                          title="ECU bearbeiten"
                          className="inline-flex items-center justify-center rounded-sm border border-destructive/70 bg-background p-2 text-destructive hover:bg-destructive/10"
                        >
                          <Pencil className="h-3 w-3" />
                        </button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </DataCard>
            );
          })}
        </div>
      )}

      {tab === 'tools' && hasTools && (
        <div className="grid gap-3 lg:grid-cols-2">
          {toolGroups.map(group => (
            <DataCard key={group.manufacturer} className="space-y-3">
              <div className="flex items-center gap-3">
                {group.logo && group.logo !== '–' && (
                  <div className="h-10 w-10 rounded-sm bg-secondary flex items-center justify-center shrink-0 overflow-hidden">
                    <img
                      src={group.logo}
                      alt={group.manufacturer}
                      className="h-7 w-7 object-contain brightness-0 dark:invert"
                      onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
                    />
                  </div>
                )}
                <p className="text-base font-semibold text-foreground">{group.manufacturer}</p>
              </div>
              <div className="space-y-3">
                {group.items.map(item => (
                  <div key={item.id} className="rounded-sm bg-background/40 p-3 space-y-2.5">
                    <div className="flex items-center justify-between gap-3">
                      <p className="text-sm font-medium text-foreground">{item.name}</p>
                      <div className="flex items-center gap-2">
                        <StatusBadge
                          status={item.kind === 'gearbox' ? 'warning' : 'new'}
                          label={item.kind === 'gearbox' ? 'Getriebe' : 'ECU'}
                        />
                        {editMode && canEdit && item.toolEntries.map(entry => (
                          <button
                            key={entry.id}
                            type="button"
                            onClick={() => onEditToolEntry(entry, item.kind, { id: Number(item.id.replace(/^[^0-9]+/, '')), name: item.name })}
                            aria-label="Tool-Eintrag bearbeiten"
                            title="Tool-Eintrag bearbeiten"
                            className="inline-flex items-center justify-center rounded-sm border border-destructive/70 bg-background p-2 text-destructive hover:bg-destructive/10"
                          >
                            <Pencil className="h-3 w-3" />
                          </button>
                        ))}
                      </div>
                    </div>
                    <div className="space-y-2">
                      {item.toolEntries.map(entry => (
                        <div key={entry.id} className="rounded-sm border border-border bg-card/60 p-2.5">
                          <div className="flex items-center justify-between gap-2 mb-1.5">
                            <div className="flex items-center gap-2">
                            {entry.toolManufacturerLogo && entry.toolManufacturerLogo !== '–' && (
                              <img
                                src={entry.toolManufacturerLogo}
                                alt={entry.toolManufacturer}
                                className="h-4 w-auto object-contain"
                                onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
                              />
                            )}
                              <p className="text-xs font-semibold text-foreground">{entry.toolManufacturer}</p>
                            </div>
                            {editMode && canEdit && (
                              <button
                                type="button"
                                onClick={() => onEditToolEntry(entry, item.kind, { id: Number(item.id.replace(/^[^0-9]+/, '')), name: item.name })}
                                aria-label="Tool-Eintrag bearbeiten"
                                title="Tool-Eintrag bearbeiten"
                                className="inline-flex items-center justify-center rounded-sm border border-destructive/70 bg-background p-2 text-destructive hover:bg-destructive/10"
                              >
                                <Pencil className="h-3 w-3" />
                              </button>
                            )}
                          </div>
                          {entry.methods.length > 0 ? (
                            <div className="flex flex-wrap gap-1.5">
                              {entry.methods.map((method, i) => (
                                <span
                                  key={i}
                                  className="inline-flex items-center gap-1 rounded-full border border-border bg-secondary/40 px-2 py-0.5 text-[10px] text-muted-foreground"
                                >
                                  {method}
                                </span>
                              ))}
                            </div>
                          ) : (
                            <p className="text-[11px] text-muted-foreground">Keine Methode hinterlegt.</p>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                ))}
              </div>
            </DataCard>
          ))}
        </div>
      )}

      {tab === 'hints' && hasHints && (
        <HintsTab hints={hints} options={allOptions} onEditHint={editMode && canEdit ? onEditHint : undefined} />
      )}

      {tab === 'gearboxes' && hasGearboxes && (
        <div className="grid gap-3">
          {gearboxCards.map((gearbox) => (
            <DataCard key={gearbox.id} className="space-y-3">
              <div className="flex items-start justify-between gap-3">
                <div>
                  <p className="text-lg font-medium text-foreground">{gearbox.title}</p>
                  <p className="mt-1 text-sm text-muted-foreground">{gearbox.manufacturer}</p>
                </div>
                <div className="flex items-center gap-2">
                  <StatusBadge status="processing" label={gearbox.type} />
                  {editMode && canEdit && (
                    <button
                      type="button"
                      onClick={() => onEditGearbox(gearbox)}
                      aria-label="Getriebe bearbeiten"
                      title="Getriebe bearbeiten"
                      className="inline-flex items-center justify-center rounded-sm border border-destructive/70 bg-background p-2 text-destructive hover:bg-destructive/10"
                    >
                      <Pencil className="h-3 w-3" />
                    </button>
                  )}
                </div>
              </div>
              {gearbox.description && (
                <p className="text-sm leading-6 text-muted-foreground">{gearbox.description}</p>
              )}
            </DataCard>
          ))}
        </div>
      )}

      {tab === 'dyno' && hasDyno && (
        <DynoChartTab stages={dynoStages} apiData={apiData} />
      )}
    </div>
  );
}

function hasDynoData(dynoChart: DynoChart): boolean {
  return (dynoChart.original?.power?.length ?? 0) > 0
    || (dynoChart.original?.torque?.length ?? 0) > 0
    || (dynoChart.optimized?.power?.length ?? 0) > 0
    || (dynoChart.optimized?.torque?.length ?? 0) > 0;
}

function dynoChartSignature(dynoChart: DynoChart): string {
  return JSON.stringify({
    originalPower: dynoChart.original?.power ?? [],
    originalTorque: dynoChart.original?.torque ?? [],
    optimizedPower: dynoChart.optimized?.power ?? [],
    optimizedTorque: dynoChart.optimized?.torque ?? [],
  });
}

function DynoChartTab({ stages, apiData }: { stages: DynoStage[]; apiData: Record<string, unknown> }) {
  const seenCharts = new Set<string>();
  const validStages = stages
    .map((stage, index) => ({ stage, index }))
    .filter(({ stage }) => {
      if (!hasDynoData(stage.dynoChart)) {
        return false;
      }

      const signature = dynoChartSignature(stage.dynoChart);
      if (seenCharts.has(signature)) {
        return false;
      }

      seenCharts.add(signature);
      return true;
    });
  const [activeIndex, setActiveIndex] = useState(0);

  if (validStages.length === 0) return null;
  const safeIndex = Math.min(activeIndex, validStages.length - 1);
  const active = validStages[safeIndex];

  // Scope apiData.stages down to just the active stage so the widget's internal
  // stage lookup can't fall back to the wrong stage on an id mismatch — the outer
  // buttons here are the single source of truth for which stage is shown.
  const chartApiData = {
    ...apiData,
    stages: [{ id: active.stage.id, name: active.stage.name, dynoChart: active.stage.dynoChart }],
  };

  return (
    <div className="space-y-3">
      <div className="flex items-center gap-2">
        {validStages.map(({ stage, index }) => (
          <button
            key={stage.id}
            onClick={() => {
              const matchingIndex = validStages.findIndex(item => item.stage.id === stage.id);
              if (matchingIndex >= 0) {
                setActiveIndex(matchingIndex);
              }
            }}
            className={`flex items-center gap-1.5 rounded-sm px-3 py-1.5 text-xs font-semibold transition-colors ${
              validStages[safeIndex]?.stage.id === stage.id
                ? 'bg-destructive text-white'
                : 'bg-secondary/40 text-muted-foreground hover:bg-secondary/60'
            }`}
          >
            <StageIcon index={index} className="h-4 w-auto" />
            {stage.name}
          </button>
        ))}
      </div>
      <DynoChartWidget
        key={active.stage.id}
        apiData={chartApiData}
        showTabs={false}
        title={active.stage.name}
        subtitle="Original vs. Optimiert"
        height={360}
      />
    </div>
  );
}

function StagePowerBar({ label, unit, base, value, maxGain, isEco = false, isTorque = false }: {
  label: string;
  unit: string;
  base: number | null;
  value: number | null;
  maxGain: number;
  isEco?: boolean;
  isTorque?: boolean;
}) {
  if (!value) return null;
  const gain = base && value > base ? value - base : null;
  const gainPercent = base && gain ? Math.round((gain / base) * 100) : null;
  const targetW = maxGain > 0 ? Math.max(10, Math.round(((gain ?? value) / maxGain) * 100)) : 10;
  const [barW, setBarW] = useState(0);
  useEffect(() => {
    const t = requestAnimationFrame(() => setBarW(targetW));
    return () => cancelAnimationFrame(t);
  }, [targetW]);
  const gainColor = isEco ? 'text-green-500' : isTorque ? 'text-blue-400' : 'text-destructive';
  const barColor = isEco ? 'bg-green-500' : isTorque ? 'bg-blue-500' : 'bg-destructive';

  return (
    <div className="space-y-1">
      <div className="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
        <span className="text-sm font-medium text-foreground">{label}</span>
        <span className={`text-sm font-extrabold tabular-nums ${gainColor}`}>
          {gainPercent !== null ? `+${gainPercent}%` : ''}
        </span>
        {gain !== null && (
          <span className={`text-right text-sm font-extrabold tabular-nums ${gainColor}`}>+{gain} {unit}</span>
        )}
      </div>
      <div className="h-3 bg-muted/40 rounded-sm overflow-hidden">
        <div className={`h-full rounded-sm transition-[width] duration-700 ease-out ${barColor}`} style={{ width: `${barW}%` }} />
      </div>
      <div className="flex items-center justify-between">
        <span className="text-xs text-muted-foreground tabular-nums">{base} {unit}</span>
        <span className="text-xs text-muted-foreground tabular-nums">{value} {unit}</span>
      </div>
    </div>
  );
}

interface OptionItem {
  id: number;
  name: string;
  isMain: boolean;
  description: string;
  icon: string;
  category: string;
  categoryIcon: string;
  categorySorting: number;
  masterOptionName: string;
  price: string;
  slug: string;
  tooltipText: string;
  active: boolean;
}

function OptionCard({ option, hasHint, onHintClick, onEdit }: { option: OptionItem; hasHint?: boolean; onHintClick?: () => void; onEdit?: () => void }) {
  const [open, setOpen] = useState(false);

  return (
    <div className="relative flex flex-col items-center gap-3 rounded-md border border-border bg-background/40 p-4 text-center cursor-default">
      {hasHint && onHintClick && (
        <div className="ribbon-corner">
          <button
            onClick={e => { e.stopPropagation(); onHintClick(); }}
            className="ribbon-corner-label hover:brightness-90"
          >
            <Info className="h-2.5 w-2.5" /> Hinweis
          </button>
        </div>
      )}
      {onEdit && (
        <button
          type="button"
          onClick={e => { e.stopPropagation(); onEdit(); }}
          aria-label="Option bearbeiten"
          title="Option bearbeiten"
          className="absolute right-2 top-2 inline-flex items-center justify-center rounded-full border border-destructive/70 bg-background/80 p-2 text-destructive hover:bg-destructive/10"
        >
          <Pencil className="h-2.5 w-2.5" />
        </button>
      )}
      <div className="h-14 w-14 rounded-full bg-muted flex items-center justify-center shrink-0 overflow-hidden">
        {option.icon && option.icon !== '–' ? (
          <img
            src={option.icon}
            alt={option.name}
            className="h-8 w-8 object-contain brightness-0 dark:invert"
            onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
          />
        ) : (
          <Settings2 className="h-5 w-5 text-muted-foreground" />
        )}
      </div>
      <div className="flex min-h-[44px] flex-col items-center justify-center gap-1">
        <p className="text-xs font-semibold text-foreground leading-tight">{option.name}</p>
        {option.price !== '' && (
          <span className="inline-flex items-center gap-1 rounded-sm border border-border bg-secondary/40 px-2 py-0.5 text-[11px] font-semibold tabular-nums text-foreground">
            <BadgeEuro className="h-3 w-3 text-muted-foreground" />
            {option.price}
          </span>
        )}
      </div>
      <div className="relative">
        <button
          className="inline-flex items-center gap-1 rounded-full border border-border bg-background/60 px-2.5 py-1 text-[10px] text-muted-foreground hover:text-foreground transition-colors"
          onMouseEnter={() => setOpen(true)}
          onMouseLeave={() => setOpen(false)}
        >
          <Info className="h-3 w-3" /> Details
        </button>

        {open && option.description && option.description !== '–' && (
          <div className="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-50 w-56 rounded-md border border-border bg-popover p-3 shadow-lg text-left">
            <p className="text-xs font-semibold text-foreground mb-1">{option.name}</p>
            <p className="text-xs text-muted-foreground leading-relaxed">{option.description}</p>
            <div className="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-border" style={{ marginTop: '-1px' }} />
          </div>
        )}
      </div>
    </div>
  );
}

function OptionListItem({ option, hasHint, onHintClick, onEdit }: { option: OptionItem; hasHint?: boolean; onHintClick?: () => void; onEdit?: () => void }) {
  const [open, setOpen] = useState(false);
  const hasDetails = option.description && option.description !== '–';

  return (
    <div className={`relative flex min-h-[54px] items-center gap-3 rounded-md border border-border bg-white px-4 py-3 ${open ? 'z-[70]' : ''}`}>
      {hasHint && onHintClick && (
        <div className="ribbon-corner-compact">
          <button
            type="button"
            onClick={e => { e.stopPropagation(); onHintClick(); }}
            className="ribbon-corner-compact-label hover:brightness-95"
            aria-label="Hinweis anzeigen"
            title="Hinweis anzeigen"
          >
            Hinweis
          </button>
        </div>
      )}
      <div className="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden">
        {option.icon && option.icon !== '–' ? (
          <img
            src={option.icon}
            alt={option.name}
            className="h-7 w-7 object-contain brightness-0 dark:invert"
            onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none'; }}
          />
        ) : (
          <Settings2 className="h-6 w-6 text-muted-foreground" />
        )}
      </div>

      <p className="min-w-0 flex-1 truncate text-sm font-semibold text-foreground">{option.name}</p>

      <div className="flex shrink-0 items-center gap-2">
        {onEdit && (
          <button
            type="button"
            onClick={e => { e.stopPropagation(); onEdit(); }}
            aria-label="Option bearbeiten"
            title="Option bearbeiten"
            className="inline-flex h-7 w-7 items-center justify-center rounded-full border border-destructive/70 bg-background/80 text-destructive hover:bg-destructive/10"
          >
            <Pencil className="h-3 w-3" />
          </button>
        )}

        <div className="relative">
          <button
            type="button"
            className="inline-flex h-7 w-7 items-center justify-center text-muted-foreground transition-colors hover:text-foreground"
            onMouseEnter={() => setOpen(true)}
            onMouseLeave={() => setOpen(false)}
            onClick={hasHint && onHintClick ? e => { e.stopPropagation(); onHintClick(); } : undefined}
            aria-label="Option Details"
            title="Option Details"
          >
            <Info className="h-5 w-5" />
          </button>

          {open && hasDetails && (
            <div className="absolute bottom-full right-0 z-[90] mb-2 w-56 rounded-md border border-border bg-popover p-3 text-left shadow-lg">
              <p className="mb-1 text-xs font-semibold text-foreground">{option.name}</p>
              <p className="text-xs leading-relaxed text-muted-foreground">{option.description}</p>
              <div className="absolute right-5 top-full border-4 border-transparent border-t-border" style={{ marginTop: '-1px' }} />
            </div>
          )}
        </div>

        {option.price !== '' && (
          <span className="inline-flex min-w-[92px] items-center justify-end gap-1 text-sm font-semibold tabular-nums text-foreground">
            <BadgeEuro className="h-3.5 w-3.5 text-muted-foreground" />
            {option.price}
          </span>
        )}
      </div>
    </div>
  );
}

function StageIcon({ index, className, isEco = false }: { index: number; className?: string; isEco?: boolean }) {
  const fillColor = isEco ? '#16a34a' : '#c70000';

  if (index === 0) {
    return (
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 145 100" className={className} aria-hidden="true">
        <path fill={fillColor} d="M46.636,44.193h31.823c2.172,0,4.16,1,5.456,2.742s1.681,3.935,1.056,6.016c-.87,2.897-3.487,4.843-6.512,4.843H29.462l-3.587,14h52.585c9.252,0,17.258-5.954,19.92-14.814,1.911-6.362.734-13.068-3.229-18.399-3.965-5.331-10.048-8.388-16.69-8.388h-31.823c-2.172,0-4.161-1-5.457-2.742s-1.681-3.935-1.056-6.015c.87-2.897,3.487-4.843,6.513-4.843h49.039l3.587-14h-52.627c-9.253,0-17.259,5.954-19.921,14.814-1.911,6.362-.734,13.068,3.229,18.398,3.965,5.331,10.048,8.388,16.691,8.388Z"/>
        <path fill={fillColor} d="M100.755,78.483H24.902l-4.849,18.925h75.853l4.849-18.925ZM36.82,84.598c-.029.272-.08.527-.152.764h-2.414c.045-.189.07-.319.077-.391.041-.391-.036-.697-.23-.923-.196-.225-.483-.337-.861-.337h-1.988c-.497,0-.929.106-1.293.319-.366.213-.597.497-.693.852l-.142.497c-.017.048-.029.112-.038.195-.05.474.268.775.952.905l3.082.567c1.856.344,2.72,1.143,2.588,2.396-.009.083-.036.23-.082.443l-.318,1.172c-.271,1.006-.83,1.789-1.676,2.352-.846.562-1.891.843-3.133.843h-2.023c-1.171,0-2.061-.296-2.666-.888-.607-.591-.857-1.39-.751-2.396.031-.296.084-.579.16-.852h2.414c-.046.212-.073.36-.082.443-.05.473.041.846.272,1.118s.572.408,1.021.408h1.953c.521,0,.976-.109,1.366-.329.389-.218.633-.511.73-.878l.172-.621.034-.16c.025-.236-.049-.435-.222-.594-.173-.16-.433-.27-.781-.329l-3.083-.55c-1.825-.319-2.67-1.118-2.535-2.396.019-.178.055-.355.109-.533l.281-.993c.282-.994.84-1.769,1.673-2.325.834-.557,1.849-.835,3.043-.835h1.988c1.101,0,1.94.275,2.521.826.582.55.823,1.292.726,2.227ZM44.924,94.111h-2.449l2.892-10.294h-3.939l.597-2.13h10.329l-.598,2.13h-3.939l-2.892,10.294ZM60.4,94.111l-.265-2.894h-4.667l-1.901,2.894h-2.645l8.352-12.424h1.101c.769,0,1.193.355,1.271,1.065l1.221,11.358h-2.467ZM80.43,85.06c-.03.284-.081.545-.152.781h-2.414c.046-.214.073-.361.081-.444.056-.521-.055-.932-.331-1.233-.275-.302-.68-.452-1.213-.452h-1.97c-.698,0-1.31.236-1.831.71-.523.474-.899,1.124-1.129,1.952l-.853,3.052c-.083.332-.131.562-.145.693-.062.603.044,1.082.32,1.437.275.355.691.533,1.247.533h2.095c.65,0,1.217-.201,1.696-.604.48-.402.823-.965,1.029-1.687l.112-.39h-3.443l.597-2.13h5.856l-.708,2.52c-.396,1.396-1.075,2.488-2.04,3.274-.963.787-2.108,1.181-3.434,1.181h-2.095c-1.277,0-2.253-.337-2.928-1.012s-.951-1.586-.831-2.733c.029-.271.093-.597.191-.976l.911-3.266c.416-1.479,1.126-2.636,2.13-3.47,1.005-.834,2.2-1.252,3.584-1.252h1.971c.827,0,1.53.146,2.11.436.579.29,1.006.701,1.28,1.233.276.532.377,1.147.304,1.846ZM94.403,86.692l-.596,2.13h-6.479l-.882,3.159h7.011l-.614,2.13h-7.738c-.45,0-.792-.122-1.026-.364-.235-.242-.331-.564-.289-.967.006-.06.029-.172.07-.338l3.03-10.755h9.459l-.596,2.13h-7.012l-.816,2.875h6.478Z"/>
        <polygon fill={fillColor} points="59.651 84.687 56.714 89.159 60.05 89.159 59.668 84.687 59.651 84.687"/>
        <polygon fill={fillColor} points="109.494 97.393 114.963 97.393 124.199 64.673 119.899 64.673 109.923 69.301 108.499 74.396 117.031 70.609 109.494 97.393"/>
      </svg>
    );
  }
  if (index === 1) {
    return (
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 145 100" className={className} aria-hidden="true">
        <path fill={fillColor} d="M42.667,44.193h31.823c2.172,0,4.16,1,5.456,2.742s1.681,3.935,1.056,6.016c-.87,2.897-3.487,4.843-6.512,4.843H25.492l-3.587,14h52.585c9.252,0,17.258-5.954,19.92-14.814,1.911-6.362.734-13.068-3.229-18.399-3.965-5.331-10.048-8.388-16.69-8.388h-31.823c-2.172,0-4.161-1-5.457-2.742s-1.681-3.935-1.056-6.015c.87-2.897,3.487-4.843,6.513-4.843h49.039l3.587-14h-52.627c-9.253,0-17.259,5.954-19.921,14.814-1.911,6.362-.734,13.068,3.229,18.398,3.965,5.331,10.048,8.388,16.691,8.388Z"/>
        <path fill={fillColor} d="M96.786,78.483H20.933l-4.849,18.925h75.853l4.849-18.925ZM32.85,84.598c-.029.272-.08.527-.152.764h-2.414c.045-.189.07-.319.077-.391.041-.391-.036-.697-.23-.923-.196-.225-.483-.337-.861-.337h-1.988c-.497,0-.929.106-1.293.319-.366.213-.597.497-.693.852l-.142.497c-.017.048-.029.112-.038.195-.05.474.268.775.952.905l3.082.567c1.856.344,2.72,1.143,2.588,2.396-.009.083-.036.23-.082.443l-.318,1.172c-.271,1.006-.83,1.789-1.676,2.352-.846.562-1.891.843-3.133.843h-2.023c-1.171,0-2.061-.296-2.666-.888-.607-.591-.857-1.39-.751-2.396.031-.296.084-.579.16-.852h2.414c-.046.212-.073.36-.082.443-.05.473.041.846.272,1.118s.572.408,1.021.408h1.953c.521,0,.976-.109,1.366-.329.389-.218.633-.511.73-.878l.172-.621.034-.16c.025-.236-.049-.435-.222-.594-.173-.16-.433-.27-.781-.329l-3.083-.55c-1.825-.319-2.67-1.118-2.535-2.396.019-.178.055-.355.109-.533l.281-.993c.282-.994.84-1.769,1.673-2.325.834-.557,1.849-.835,3.043-.835h1.988c1.101,0,1.94.275,2.521.826.582.55.823,1.292.726,2.227ZM40.954,94.111h-2.449l2.892-10.294h-3.939l.597-2.13h10.329l-.598,2.13h-3.939l-2.892,10.294ZM56.43,94.111l-.265-2.894h-4.667l-1.901,2.894h-2.645l8.352-12.424h1.101c.769,0,1.193.355,1.271,1.065l1.221,11.358h-2.467ZM76.46,85.06c-.03.284-.081.545-.152.781h-2.414c.046-.214.073-.361.081-.444.056-.521-.055-.932-.331-1.233-.275-.302-.68-.452-1.213-.452h-1.97c-.698,0-1.31.236-1.831.71-.523.474-.899,1.124-1.129,1.952l-.853,3.052c-.083.332-.131.562-.145.693-.062.603.044,1.082.32,1.437.275.355.691.533,1.247.533h2.095c.65,0,1.217-.201,1.696-.604.48-.402.823-.965,1.029-1.687l.112-.39h-3.443l.597-2.13h5.856l-.708,2.52c-.396,1.396-1.075,2.488-2.04,3.274-.963.787-2.108,1.181-3.434,1.181h-2.095c-1.277,0-2.253-.337-2.928-1.012s-.951-1.586-.831-2.733c.029-.271.093-.597.191-.976l.911-3.266c.416-1.479,1.126-2.636,2.13-3.47,1.005-.834,2.2-1.252,3.584-1.252h1.971c.827,0,1.53.146,2.11.436.579.29,1.006.701,1.28,1.233.276.532.377,1.147.304,1.846ZM90.433,86.692l-.596,2.13h-6.479l-.882,3.159h7.011l-.614,2.13h-7.738c-.45,0-.792-.122-1.026-.364-.235-.242-.331-.564-.289-.967.006-.06.029-.172.07-.338l3.03-10.755h9.459l-.596,2.13h-7.012l-.816,2.875h6.478Z"/>
        <polygon fill={fillColor} points="55.681 84.687 52.744 89.159 56.08 89.159 55.699 84.687 55.681 84.687"/>
        <path fill={fillColor} d="M109.494,97.393h16.293l1.378-4.928h-10.044l8.513-11.677c.857-1.172,1.285-2.39,1.285-3.652,0-1.015-.276-1.897-.828-2.647-.552-.75-1.297-1.124-2.234-1.124h-9.626l-1.421,4.928h8.094l-8.646,11.568c-.87,1.163-1.306,2.384-1.306,3.664,0,2.578,1.181,3.868,3.542,3.868Z"/>
      </svg>
    );
  }
  return null;
}

function mapOptionRows(options: Array<Record<string, unknown>>, showPrices: boolean, showOptionCategories: boolean): OptionItem[] {
  return options.map((option, optionIndex) => ({
    id: asNumber(option.id) ?? optionIndex,
    name: asText(option.name, `Option ${optionIndex + 1}`),
    isMain: Boolean(option.isMainOption),
    description: stripHtml(asText(option.description)),
    icon: asText(option.icon, ''),
    category: showOptionCategories && typeof option.category === 'object' && option.category !== null
      ? asText((option.category as Record<string, unknown>).name, '')
      : '',
    categoryIcon: showOptionCategories && typeof option.category === 'object' && option.category !== null
      ? asText((option.category as Record<string, unknown>).icon, '')
      : '',
    categorySorting: showOptionCategories && typeof option.category === 'object' && option.category !== null
      ? asNumber((option.category as Record<string, unknown>).sorting) ?? Number.MAX_SAFE_INTEGER
      : Number.MAX_SAFE_INTEGER,
    masterOptionName: asText(option.masterOptionName, ''),
    price: showPrices ? formatEuro(option.price) : '',
    slug: asText(option.slug, ''),
    tooltipText: stripHtml(asText(option.tooltipText, '')),
    active: option.active !== false,
  }));
}

function mapToolEntries(raw: unknown): ToolEntryItem[] {
  const entries = Array.isArray(raw) ? raw as Array<Record<string, unknown>> : [];
  return entries.map((entry, index) => ({
    id: asNumber(entry.id) ?? index,
    toolManufacturer: asText(entry.toolManufacturer, 'Unbekannt'),
    toolManufacturerLogo: asText(entry.toolManufacturerLogo, ''),
    toolManufacturerId: asNumber(entry.toolManufacturerId),
    methodIds: Array.isArray(entry.methodIds)
      ? (entry.methodIds as unknown[]).map(m => asNumber(m)).filter((m): m is number => m !== null)
      : [],
    methods: Array.isArray(entry.methods)
      ? (entry.methods as unknown[]).map(m => asText(m)).filter(m => m !== '' && m !== '–')
      : [],
    position: asNumber(entry.position),
    kind: asText(entry.kind, 'ecu') === 'gearbox' ? 'gearbox' : 'ecu',
  })).filter(entry => entry.toolManufacturer !== '' && entry.toolManufacturer !== '–');
}

function asText(value: unknown, fallback = '–'): string {
  if (value === null || value === undefined || value === '') {
    return fallback;
  }

  return String(value);
}

function asNumber(value: unknown): number | null {
  if (typeof value === 'number' && Number.isFinite(value)) {
    return value;
  }

  if (typeof value === 'string' && value.trim() !== '') {
    const parsed = Number(value);
    if (Number.isFinite(parsed)) {
      return parsed;
    }
  }

  return null;
}

function formatEuro(value: unknown): string {
  const parsed = asNumber(value);
  if (parsed === null) {
    return '';
  }

  return `${parsed.toLocaleString('de-DE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })} €`;
}

function stripFuelSuffix(name: string): string {
  return name.replace(/\s*[-–]\s*(Diesel|Benzin|Hybrid|Elektro|Electric|Petrol|LPG|CNG)\s*$/i, '').trim();
}

function stripHtml(value: string): string {
  return value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

function assetLabel(value: unknown): string {
  const text = asText(value, '–');
  if (text === '–') {
    return text;
  }

  return text.split('/').pop() ?? text;
}
