import React, { useEffect, useMemo, useState } from 'react';
import { fetchConfig, fetchRecommendation, submitLead } from './api';
import {
  experienceOptions,
  fallbackConfig,
  goalOptions,
  serviceOptions,
  toolOptions,
  vehicleOptions,
  volumeOptions,
} from './fallbackConfig';
import { getLocalRecommendation } from './localRecommendation';
import type { RecommendedTool, SelectOption, VehicleTypeOption, WizardAnswers, WizardConfig, WizardRecommendation, WizardStepKey } from './types';

type Props = {
  clientId?: string;
  apiBaseUrl?: string;
  primaryColor?: string;
  theme?: 'light' | 'dark';
};


const serviceTitleMap: Record<string, string> = {
  stage1: 'Stage 1',
  stage2: 'Stage 2',
  eco: 'Eco Tuning',
  agr: 'AGR OFF',
  egr: 'EGR OFF',
  dpf: 'DPF OFF',
  adblue: 'AdBlue OFF',
  vmax: 'VMax OFF',
  pops: 'Pops & Bangs',
  hardcut: 'Hardcut',
  launch: 'Launch Control',
  dsg: 'DSG / Getriebe',
  getriebe: 'Getriebe',
};

const vehicleTextMap: Record<string, string> = {
  pkw: 'Normale Kundenfahrzeuge und Performance-Umbauten.',
  lkw: 'Truck- und Nutzfahrzeug-Anfragen.',
  agrar: 'Traktoren und Agrartechnik.',
  motorrad: 'Motorräder und Zweiräder.',
  atv: 'ATV, Quad und Side-by-Side.',
  boat: 'Boote und Marine-Anwendungen.',
  jetski: 'JetSki und Wassersport-Fahrzeuge.',
};

const serviceTextMap: Record<string, string> = {
  stage1: 'Leistungsoptimierung für Kundenfahrzeuge.',
  stage2: 'Erweiterte Optimierungen und Umbauten.',
  eco: 'Verbrauchsoptimierte Abstimmung.',
  agr: 'AGR-bezogene Anfragen.',
  egr: 'EGR-bezogene Anfragen.',
  dpf: 'DPF-bezogene Anfragen.',
  adblue: 'SCR/AdBlue-bezogene Anfragen.',
  vmax: 'VMax-bezogene Anfragen.',
  pops: 'Sound- und Motorsport-Features.',
  hardcut: 'Hardcut / Drehzahlbegrenzung.',
  launch: 'Launch-Control-Anfragen.',
  dsg: 'Getriebe- und DSG-Anpassungen.',
  getriebe: 'Getriebe- und Schaltlogik-Anfragen.',
};

function labelFromCustomLabels(config: WizardConfig, key: string, fallback: string): string {
  const labels = config.customLabels;
  if (labels && typeof labels === 'object' && !Array.isArray(labels)) {
    const record = labels as Record<string, unknown>;
    const value = record[key] ?? record[key.toLowerCase()] ?? record[fallback];
    if (typeof value === 'string' && value.trim()) return value.trim();
  }
  return fallback;
}

function textFromUnknown(item: unknown): string {
  if (typeof item === 'string') return item;
  if (item && typeof item === 'object') {
    const record = item as Record<string, unknown>;
    const value = record.key ?? record.value ?? record.slug ?? record.name ?? record.title ?? record.label;
    return typeof value === 'string' ? value : String(value ?? '');
  }
  return '';
}

function buildVehicleOptionsFromConfig(
  vehicleTypes: VehicleTypeOption[] | undefined,
  fallbackItems: SelectOption[],
): SelectOption[] {
  if (!Array.isArray(vehicleTypes) || vehicleTypes.length === 0) return fallbackItems;

  const options: SelectOption[] = [];
  for (const vehicleType of vehicleTypes) {
    if (!vehicleType.name.trim()) continue;
    const value = (vehicleType.slug ?? String(vehicleType.id)).toLowerCase();

    options.push({
      value,
      id: vehicleType.id,
      title: vehicleType.name,
      text: vehicleTextMap[value] ?? 'Aus deinen Admin-Einstellungen aktiviert.',
      icon: vehicleType.icon,
    });
  }

  return options.length > 0 ? options : fallbackItems;
}

function buildOptionsFromConfig(
  sourceItems: unknown[] | undefined,
  fallbackItems: SelectOption[],
  titleMap: Record<string, string>,
  textMap: Record<string, string>,
  config: WizardConfig,
): SelectOption[] {
  if (!Array.isArray(sourceItems) || sourceItems.length === 0) return fallbackItems;

  const options = sourceItems
    .map((item) => {
      if (item && typeof item === 'object') {
        const record = item as Record<string, unknown>;
        const value = String(record.key ?? record.value ?? record.slug ?? record.name ?? record.title ?? '').trim();
        if (!value) return null;
        const normalized = value.toLowerCase();
        const title = String(record.title ?? record.label ?? record.name ?? labelFromCustomLabels(config, normalized, titleMap[normalized] ?? value)).trim();
        const text = String(record.text ?? record.description ?? textMap[normalized] ?? 'Aus deinen Admin-Einstellungen aktiviert.').trim();
        const icon = typeof record.icon === 'string' && record.icon.trim() ? record.icon.trim() : undefined;
        const imageUrl = typeof record.imageUrl === 'string' && record.imageUrl.trim()
          ? record.imageUrl.trim()
          : typeof record.image_url === 'string' && record.image_url.trim()
            ? record.image_url.trim()
            : undefined;
        return { value: normalized, title, text, icon, imageUrl };
      }

      const raw = textFromUnknown(item).trim();
      if (!raw) return null;
      const normalized = raw.toLowerCase();
      const title = labelFromCustomLabels(config, normalized, titleMap[normalized] ?? raw);
      const text = textMap[normalized] ?? 'Aus deinen Admin-Einstellungen aktiviert.';
      return { value: normalized, title, text };
    })
    .filter(Boolean) as SelectOption[];

  return options.length > 0 ? options : fallbackItems;
}

function buildToolOptionsFromConfig(config: WizardConfig): SelectOption[] {
  const noneOption = toolOptions[0];
  const configuredOptions = buildOptionsFromConfig(
    config.currentToolOptions,
    [],
    {},
    {},
    config,
  ).filter((option) => option.value !== 'none');

  return configuredOptions.length > 0 ? [noneOption, ...configuredOptions] : toolOptions;
}

const steps: Array<{ key: WizardStepKey; label: string }> = [
  { key: 'entry', label: 'Einstieg' },
  { key: 'vehicleTool', label: 'Fahrzeug & Tool' },
  { key: 'offerVolume', label: 'Angebot & Volumen' },
  { key: 'recommendation', label: 'Empfehlung' },
  { key: 'contact', label: 'Kontakt' },
];

const includedExtras = [
  'Fahrzeugdatenbank API für 1 Jahr mit drin',
];

function toggleValue(values: string[] | undefined, value: string): string[] {
  const current = values ?? [];
  return current.includes(value)
    ? current.filter((item) => item !== value)
    : [...current, value];
}

function isReadyForRecommendation(answers: WizardAnswers): boolean {
  return Boolean(
    answers.vehicles?.length &&
    answers.tool,
  );
}


function formatPrice(price?: string): string | undefined {
  if (!price) return undefined;
  const value = Number(price);
  if (!Number.isFinite(value)) return undefined;
  return `${value.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;
}

function volumeToMonthlyCount(value?: string): number {
  switch (value) {
    case 'low':
      return 3;
    case 'medium':
      return 12;
    case 'high':
      return 25;
    case 'network':
      return 40;
    default:
      return 0;
  }
}

function toolToDesiredLevel(value?: string): string {
  if (!value || value === 'none') return 'unknown';
  if (value === 'other') return 'unknown';
  return 'slave';
}

function createApiPayload(
  clientId: string,
  answers: WizardAnswers,
  vehicleSlugToId: Record<string, number>,
  recommendation?: WizardRecommendation,
) {
  const contact = answers.contact ?? {};
  const vehicleSlugs = answers.vehicles ?? [];
  const vehicleTypeIds = vehicleSlugs
    .map((slug) => vehicleSlugToId[slug])
    .filter((id): id is number => typeof id === 'number');
  const services = answers.services ?? [];
  const currentTool = answers.tool ?? '';
  const monthlyVehicleCount = volumeToMonthlyCount(answers.volume);
  const desiredToolLevel = toolToDesiredLevel(answers.tool);

  return {
    clientId,
    contact,
    answers,
    recommendation,
    source: 'no1_partner_wizard',
    selectedTools: answers.selectedTools ?? [],

    // New Symfony recommendation service fields (VehicleType IDs, not slugs).
    currentTool,
    vehicleCategories: vehicleTypeIds,
    mainVehicleCategory: vehicleTypeIds[0] ?? '',
    services,
    goal: answers.goal ?? '',
    experience: answers.experience ?? '',
    monthlyVehicleCount,
    desiredToolLevel,

    // Lead / compatibility fields.
    packageName: recommendation?.packageName,
    packageKey: recommendation?.packageKey,
    leadStatus: recommendation?.leadStatus,
    status: recommendation?.status,
    name: contact.name,
    company: contact.company,
    email: contact.email,
    phone: contact.phone,
    message: contact.message,
    privacyAccepted: answers.privacyAccepted === true,
    vehicles: vehicleSlugs,
    tool: currentTool,
    volume: answers.volume,

    payload: {
      clientId,
      contact,
      answers,
      recommendation,
      currentTool,
      vehicleCategories: vehicleTypeIds,
      mainVehicleCategory: vehicleTypeIds[0] ?? '',
      services,
      monthlyVehicleCount,
      desiredToolLevel,
      privacyAccepted: answers.privacyAccepted === true,
      source: 'no1_partner_wizard',
    },
  };
}

function OptionCard({
  option,
  selected,
  onClick,
}: {
  option: SelectOption;
  selected: boolean;
  onClick: () => void;
}) {
  const iconUrl = option.imageUrl ?? option.icon;

  return (
    <button
      type="button"
      className={[
        'tfdWizard__option',
        iconUrl ? 'tfdWizard__option--withIcon' : '',
        selected ? 'selected' : '',
      ].filter(Boolean).join(' ')}
      onClick={onClick}
      data-testid={`option-${option.value}`}
    >
      {iconUrl && (
        <img className="tfdWizard__optionIcon" src={iconUrl} alt="" loading="lazy" aria-hidden="true" />
      )}
      <div className="tfdWizard__optionBody">
        <strong>{option.title}</strong>
        <span>{option.text}</span>
      </div>
    </button>
  );
}

export function PartnerWizard({
  clientId = 'no1',
  apiBaseUrl = '',
  primaryColor = '#E31E24',
  theme = 'light',
}: Props) {
  const [config, setConfig] = useState(fallbackConfig);
  const [configError, setConfigError] = useState('');
  const [stepIndex, setStepIndex] = useState(0);
  const [answers, setAnswers] = useState<WizardAnswers>({});
  const [sending, setSending] = useState(false);
  const [sendMessage, setSendMessage] = useState('');
  const [apiRecommendation, setApiRecommendation] = useState<WizardRecommendation | null>(null);
  const [recommendationError, setRecommendationError] = useState('');
  const [recommendationLoading, setRecommendationLoading] = useState(false);
  const [productPreview, setProductPreview] = useState<RecommendedTool | null>(null);

  useEffect(() => {
    let mounted = true;

    fetchConfig(apiBaseUrl, clientId)
      .then((loadedConfig) => {
        if (!mounted) return;
        setConfig({ ...fallbackConfig, ...loadedConfig });
        setConfigError('');
      })
      .catch((error: unknown) => {
        if (!mounted) return;
        setConfig(fallbackConfig);
        const status = typeof error === 'object' && error !== null && 'status' in error
          ? String((error as { status?: number }).status ?? 'offline')
          : 'offline';
        setConfigError(`Config konnte nicht geladen werden (${status}). Lokale Fallback-Konfiguration aktiv.`);
      });

    return () => {
      mounted = false;
    };
  }, [apiBaseUrl, clientId]);

  const localRecommendation = useMemo(
    () => getLocalRecommendation(answers, config),
    [answers, config],
  );
  const vehicleOptionsFromConfig = useMemo(
    () => buildVehicleOptionsFromConfig(config.enabledVehicleCategories, vehicleOptions),
    [config],
  );
  const vehicleSlugToId = useMemo(
    () => Object.fromEntries(
      vehicleOptionsFromConfig
        .filter((option) => typeof option.id === 'number')
        .map((option) => [option.value, option.id as number]),
    ),
    [vehicleOptionsFromConfig],
  );

  useEffect(() => {
    if (!isReadyForRecommendation(answers)) {
      setApiRecommendation(null);
      setRecommendationError('');
      return;
    }

    let mounted = true;
    setRecommendationLoading(true);
    setRecommendationError('');

    const payload = createApiPayload(clientId, answers, vehicleSlugToId, localRecommendation);

    fetchRecommendation(apiBaseUrl, payload)
      .then((recommendation) => {
        if (!mounted) return;
        setApiRecommendation(recommendation);
        setRecommendationError('');
      })
      .catch((error: unknown) => {
        if (!mounted) return;
        setApiRecommendation(null);
        const message = error instanceof Error ? error.message : 'Recommendation API nicht erreichbar.';
        setRecommendationError(`${message} Lokale Empfehlung aktiv.`);
      })
      .finally(() => {
        if (mounted) setRecommendationLoading(false);
      });

    return () => {
      mounted = false;
    };
  }, [answers, apiBaseUrl, clientId, localRecommendation, vehicleSlugToId]);

  const currentStep = steps[stepIndex];
  const recommendation = apiRecommendation ?? localRecommendation;
  const recommendationToolKeys = useMemo(
    () => (recommendation.recommendedTools ?? []).map((item) => item.key),
    [recommendation],
  );
  const serviceOptionsFromConfig = useMemo(
    () => buildOptionsFromConfig(config.enabledServices, serviceOptions, serviceTitleMap, serviceTextMap, config),
    [config],
  );
  const toolOptionsFromConfig = useMemo(
    () => buildToolOptionsFromConfig(config),
    [config],
  );
  const progress = Math.round(((stepIndex + 1) / steps.length) * 100);

  useEffect(() => {
    if (!productPreview) return;

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        setProductPreview(null);
      }
    };

    window.addEventListener('keydown', onKeyDown);
    return () => window.removeEventListener('keydown', onKeyDown);
  }, [productPreview]);

  useEffect(() => {
    setAnswers((old) => {
      const selectedTools = old.selectedTools ?? [];
      if (selectedTools.length === 0) return old;

      const nextSelectedTools = selectedTools.filter((key) => recommendationToolKeys.includes(key));
      return nextSelectedTools.length === selectedTools.length
        ? old
        : { ...old, selectedTools: nextSelectedTools };
    });
  }, [recommendationToolKeys]);

  const canGoNext = useMemo(() => {
    switch (currentStep.key) {
      case 'entry':
        return Boolean(answers.goal && answers.experience);
      case 'vehicleTool':
        return Boolean(answers.vehicles?.length && answers.tool);
      case 'offerVolume':
        return Boolean(answers.services?.length && answers.volume);
      case 'recommendation':
        return true;
      case 'contact':
        return Boolean(answers.contact?.name && answers.contact?.email && answers.privacyAccepted);
      default:
        return true;
    }
  }, [answers, currentStep.key]);

  const selectSingle = (key: keyof WizardAnswers, value: string) => {
    setAnswers((old) => ({ ...old, [key]: value }));
    setSendMessage('');
  };

  const selectMulti = (key: 'vehicles' | 'services', value: string) => {
    setAnswers((old) => ({
      ...old,
      [key]: toggleValue(old[key], value),
    }));
    setSendMessage('');
  };

  const allVehiclesSelected = vehicleOptionsFromConfig.length > 0
    && vehicleOptionsFromConfig.every((option) => (answers.vehicles ?? []).includes(option.value));

  const toggleAllVehicles = () => {
    setAnswers((old) => ({
      ...old,
      vehicles: allVehiclesSelected ? [] : vehicleOptionsFromConfig.map((option) => option.value),
    }));
    setSendMessage('');
  };

  const renderOptions = (items: SelectOption[], key: keyof WizardAnswers, multi = false) => (
    <div className="tfdWizard__grid">
      {items.map((option) => {
        const rawValue = answers[key];
        const selected = Array.isArray(rawValue)
          ? rawValue.includes(option.value)
          : rawValue === option.value;

        return (
          <OptionCard
            key={option.value}
            option={option}
            selected={selected}
            onClick={() => {
              if (multi && (key === 'vehicles' || key === 'services')) {
                selectMulti(key, option.value);
              } else {
                selectSingle(key, option.value);
              }
            }}
          />
        );
      })}
    </div>
  );

  const next = () => {
    if (canGoNext) {
      setStepIndex((old) => Math.min(old + 1, steps.length - 1));
      setSendMessage('');
    }
  };

  const back = () => {
    setStepIndex((old) => Math.max(old - 1, 0));
    setSendMessage('');
  };

  const updateContact = (field: keyof NonNullable<WizardAnswers['contact']>, value: string) => {
    setAnswers((old) => ({
      ...old,
      contact: {
        ...old.contact,
        [field]: value,
      },
    }));
    setSendMessage('');
  };

  const updatePrivacyAccepted = (checked: boolean) => {
    setAnswers((old) => ({ ...old, privacyAccepted: checked }));
    setSendMessage('');
  };

  const toggleRecommendedTool = (toolKey: string) => {
    setAnswers((old) => ({
      ...old,
      selectedTools: toggleValue(old.selectedTools, toolKey),
    }));
    setSendMessage('');
  };

  const openProductPreview = (tool: RecommendedTool) => {
    setProductPreview(tool);
  };

  const renderProductPreview = () => {
    if (!productPreview) return null;

    const product = productPreview.product;
    const priceLabel = formatPrice(product?.price);
    const imageUrl = productPreview.imageUrl ?? product?.imageUrl;
    const selected = (answers.selectedTools ?? []).includes(productPreview.key);

    return (
      <div
        className="tfdWizard__modalBackdrop"
        role="presentation"
        onClick={() => setProductPreview(null)}
      >
        <div
          className="tfdWizard__productModal"
          role="dialog"
          aria-modal="true"
          aria-label={product?.title ?? productPreview.name}
          onClick={(event) => event.stopPropagation()}
        >
          <button
            type="button"
            className="tfdWizard__modalClose"
            onClick={() => setProductPreview(null)}
            aria-label="Produktansicht schließen"
          >
            ×
          </button>

          <div className="tfdWizard__productModalMedia">
            {imageUrl ? (
              <img src={imageUrl} alt={product?.title ?? productPreview.name} />
            ) : (
              <div className="tfdWizard__productModalPlaceholder">No1</div>
            )}
          </div>

          <div className="tfdWizard__productModalBody">
            <span>Produktdetails</span>
            <h3>{product?.title ?? productPreview.name}</h3>
            {priceLabel && <strong>{priceLabel}</strong>}
            {productPreview.description && <p>{productPreview.description}</p>}

            <div className="tfdWizard__productFacts">
              {product?.sku && (
                <div>
                  <em>SKU</em>
                  <b>{product.sku}</b>
                </div>
              )}
              {productPreview.level && (
                <div>
                  <em>Tool-Level</em>
                  <b>{productPreview.level}</b>
                </div>
              )}
              <div>
                <em>Empfehlung</em>
                <b>{productPreview.fromCombination ? 'Exakt passend' : 'Passend'}</b>
              </div>
            </div>

            <button
              type="button"
              className="tfdWizard__modalSelect"
              onClick={() => toggleRecommendedTool(productPreview.key)}
              aria-pressed={selected}
            >
              {selected ? 'Aus Auswahl entfernen' : 'Zur Auswahl hinzufügen'}
            </button>
          </div>
        </div>
      </div>
    );
  };

  const handleSend = async () => {
    if (!canGoNext || sending) return;

    setSending(true);
    setSendMessage('');

    const payload = createApiPayload(clientId, answers, vehicleSlugToId, recommendation);

    try {
      await submitLead(apiBaseUrl, payload);
      setSendMessage('Anfrage wurde gespeichert. Wir melden uns mit der passenden Empfehlung.');
    } catch (error: unknown) {
      const message = error instanceof Error ? error.message : 'Unbekannter API-Fehler';
      setSendMessage(`Anfrage konnte nicht gesendet werden: ${message}`);
    } finally {
      setSending(false);
    }
  };

  const renderSidePanel = () => {
    const selectedVehicles = (answers.vehicles ?? [])
      .map((value) => vehicleOptionsFromConfig.find((option) => option.value === value)?.title ?? value)
      .slice(0, 4);
    const selectedServices = (answers.services ?? [])
      .map((value) => serviceOptionsFromConfig.find((option) => option.value === value)?.title ?? value)
      .slice(0, 4);
    const selectedTool = toolOptionsFromConfig.find((option) => option.value === answers.tool)?.title;
    const selectedProductTools = (recommendation.recommendedTools ?? [])
      .filter((item) => (answers.selectedTools ?? []).includes(item.key));
    const selectedProductsTotal = selectedProductTools.reduce((sum, item) => {
      const price = Number(item.product?.price);
      return Number.isFinite(price) ? sum + price : sum;
    }, 0);

    const hasToolAnswers = Boolean(answers.vehicles?.length && answers.tool);
    const hasExistingTool = Boolean(answers.tool && answers.tool !== 'none');

    return (
      <aside className="tfdWizard__promoAside" aria-label="Tool Empfehlungen">
        {(selectedVehicles.length > 0 || selectedServices.length > 0 || selectedTool) && (
          <div className="tfdWizard__promoCard tfdWizard__summaryCard">
            <span>Deine Auswahl</span>
            {selectedTool && <p><b>Tool:</b> {selectedTool}</p>}
            {selectedVehicles.length > 0 && <p><b>Fahrzeuge:</b> {selectedVehicles.join(', ')}</p>}
            {selectedServices.length > 0 && <p><b>Leistungen:</b> {selectedServices.join(', ')}</p>}
          </div>
        )}

        {!hasExistingTool && (
          <div className="tfdWizard__promoCard tfdWizard__promoCard--accent">
            <span>Tool Werbung</span>
            <strong>Passendes Leseschreibtool</strong>
            <p>Für den Händlerstart brauchst du ein unterstütztes Tool. Wir prüfen Protokolle und Fahrzeugbereiche.</p>

            {hasToolAnswers && recommendationError && (
              <div className="tfdWizard__miniNotice">{recommendationError}</div>
            )}

            {answers.tool === 'none' && (
              <div className="tfdWizard__miniNotice">
                Ohne eigenes Tuning-Tool ist noch keine Händler-Aktivierung möglich.
              </div>
            )}

            {!hasToolAnswers ? (
              <div className="tfdWizard__miniNotice">
                Beantworte Fahrzeuge und Tool-Status, um passende Tools zu sehen.
              </div>
            ) : recommendationLoading ? (
              <div className="tfdWizard__miniNotice">
                Empfehlung wird aus dem Backend geladen...
              </div>
            ) : recommendation.recommendedTools && recommendation.recommendedTools.length > 0 ? (
              <div className="tfdWizard__adList">
                {recommendation.recommendedTools.slice(0, 4).map((item) => {
                  const priceLabel = formatPrice(item.product?.price);
                  return (
                    <article key={item.key} className={item.imageUrl ? 'tfdWizard__adItem--withImage' : undefined}>
                      {item.imageUrl && (
                        <img className="tfdWizard__adImage" src={item.imageUrl} alt={item.name} loading="lazy" />
                      )}
                      <div className="tfdWizard__adBody">
                        <b>{item.name}</b>
                        {item.fromCombination && (
                          <span className="tfdWizard__adBadge">Passt zu deiner Auswahl</span>
                        )}
                        {item.description && <small>{item.description}</small>}
                        {item.product && (
                          <div className="tfdWizard__adProduct">
                            {priceLabel && <span className="tfdWizard__adPrice">{priceLabel}</span>}
                            <button
                              type="button"
                              className="tfdWizard__adLink"
                              onClick={() => openProductPreview(item)}
                            >
                              Produkt ansehen
                            </button>
                          </div>
                        )}
                      </div>
                    </article>
                  );
                })}
              </div>
            ) : hasToolAnswers ? (
              <div className="tfdWizard__miniNotice">
                Für diese Fahrzeugauswahl ist noch keine passende Tool-Empfehlung hinterlegt.
              </div>
            ) : (
              <div className="tfdWizard__tagList">
                <em>Autotuner</em>
                <em>KESS3</em>
                <em>FLEX</em>
              </div>
            )}
          </div>
        )}

        {config.toolDisclaimer && currentStep.key === 'vehicleTool' && (
          <div className="tfdWizard__miniNotice">{config.toolDisclaimer}</div>
        )}

        {currentStep.key === 'contact' && (
          <div className="tfdWizard__promoCard tfdWizard__selectedProductsCard">
            <span>Ausgewählte Produkte</span>
            {selectedProductTools.length > 0 ? (
              <>
                <strong>
                  {selectedProductTools.length} Position{selectedProductTools.length === 1 ? '' : 'en'}
                </strong>
                <div className="tfdWizard__selectedProductsList">
                  {selectedProductTools.map((item) => {
                    const priceLabel = formatPrice(item.product?.price);
                    return (
                      <article key={item.key}>
                        {(item.imageUrl || item.product?.imageUrl) && (
                          <img src={item.imageUrl ?? item.product?.imageUrl} alt={item.product?.title ?? item.name} loading="lazy" />
                        )}
                        <div>
                          <b>{item.product?.title ?? item.name}</b>
                          {priceLabel && <em>{priceLabel}</em>}
                          <button type="button" onClick={() => openProductPreview(item)}>
                            Details
                          </button>
                        </div>
                      </article>
                    );
                  })}
                </div>
                {selectedProductsTotal > 0 && (
                  <p className="tfdWizard__selectedProductsTotal">
                    <b>Summe:</b> {formatPrice(String(selectedProductsTotal))}
                  </p>
                )}
              </>
            ) : (
              <p>Keine Produkte ausgewählt. Du kannst im vorherigen Schritt passende Tools auswählen.</p>
            )}
          </div>
        )}
      </aside>
    );
  };

  const renderStep = () => {
    switch (currentStep.key) {
      case 'entry':
        return (
          <>
            <section className="tfdWizard__block">
              <h3 className="tfdWizard__title">Was ist dein Ziel?</h3>
              {renderOptions(goalOptions, 'goal')}
            </section>

            <section className="tfdWizard__block">
              <h3 className="tfdWizard__title">Wie viel Erfahrung hast du?</h3>
              {renderOptions(experienceOptions, 'experience')}
            </section>
          </>
        );

      case 'vehicleTool':
        return (
          <>
            <section className="tfdWizard__block">
              <h3 className="tfdWizard__title">Welche Fahrzeuge möchtest du bearbeiten?</h3>
              <button
                type="button"
                className="tfdWizard__selectAllButton"
                onClick={toggleAllVehicles}
                data-testid="vehicles-select-all-button"
              >
                {allVehiclesSelected ? 'Alle abwählen' : 'Alle auswählen'}
              </button>
              {renderOptions(vehicleOptionsFromConfig, 'vehicles', true)}
            </section>

            <section className="tfdWizard__block">
              <h3 className="tfdWizard__title">Hast du bereits ein eigenes Tool?</h3>
              <p className="tfdWizard__lead">
                Ohne eigenes Tuning-Tool ist keine direkte Händler-Aktivierung möglich.
                Dann geht es zuerst in die Tool-Beratung.
              </p>
              {renderOptions(toolOptionsFromConfig, 'tool')}
            </section>
          </>
        );

      case 'offerVolume':
        return (
          <>
            <section className="tfdWizard__block">
              <h3 className="tfdWizard__title">Welche Leistungen willst du anbieten?</h3>
              {renderOptions(serviceOptionsFromConfig, 'services', true)}
            </section>

            <section className="tfdWizard__block">
              <h3 className="tfdWizard__title">Wie viele Fahrzeuge erwartest du?</h3>
              {renderOptions(volumeOptions, 'volume')}
            </section>
          </>
        );

      case 'recommendation': {
        const extras = Array.from(new Set([...(recommendation.bullets ?? []), ...includedExtras].filter(Boolean)));
        const recommendedTools = recommendation.recommendedTools?.slice(0, 4) ?? [];
        const selectedToolKeys = answers.selectedTools ?? [];
        const selectedRecommendedTools = recommendedTools.filter((item) => selectedToolKeys.includes(item.key));
        const selectedTotal = selectedRecommendedTools.reduce((sum, item) => {
          const price = Number(item.product?.price);
          return Number.isFinite(price) ? sum + price : sum;
        }, 0);

        return (
          <section className="tfdWizard__block tfdWizard__recommendationStep">
            <div className="tfdWizard__recommendationHero">
              <span>Deine Empfehlung</span>
              <h3>{recommendation.packageName}</h3>
              <p>{recommendation.summary}</p>
            </div>

            <div className="tfdWizard__recommendationPanel">
              <div className="tfdWizard__recommendationPanelHeader">
                <span>Passende Tools</span>
                {recommendationLoading && <em>Wird geladen...</em>}
              </div>

              {recommendedTools.length > 0 ? (
                <div className="tfdWizard__toolShowcase">
                  {recommendedTools.map((item) => {
                    const priceLabel = formatPrice(item.product?.price);
                    const selected = selectedToolKeys.includes(item.key);
                    return (
                      <article
                        key={item.key}
                        className={[
                          'tfdWizard__toolShowcaseItem',
                          item.imageUrl ? 'tfdWizard__toolShowcaseItem--withImage' : '',
                          selected ? 'tfdWizard__toolShowcaseItem--selected' : '',
                        ].filter(Boolean).join(' ')}
                      >
                        {item.imageUrl && (
                          <img className="tfdWizard__toolShowcaseImage" src={item.imageUrl} alt={item.name} loading="lazy" />
                        )}
                        <div>
                          <div className="tfdWizard__toolShowcaseTop">
                            <b>{item.name}</b>
                            <button
                              type="button"
                              className="tfdWizard__cartToggle"
                              onClick={() => toggleRecommendedTool(item.key)}
                              aria-pressed={selected}
                            >
                              {selected ? 'Ausgewählt' : 'Auswählen'}
                            </button>
                          </div>
                          {item.fromCombination && <small>Passt exakt zu deiner Auswahl</small>}
                          {item.description && <p>{item.description}</p>}
                          <div className="tfdWizard__toolShowcaseMeta">
                            {item.level && <span>{item.level}</span>}
                            {priceLabel && <strong>{priceLabel}</strong>}
                            {item.product && (
                              <button type="button" onClick={() => openProductPreview(item)}>
                                Produkt ansehen
                              </button>
                            )}
                          </div>
                        </div>
                      </article>
                    );
                  })}
                </div>
              ) : (
                <div className="tfdWizard__miniNotice">
                  Für diese Auswahl ist noch keine passende Tool-Empfehlung hinterlegt.
                </div>
              )}

              <div className="tfdWizard__cartSummary">
                <span>Auswahl</span>
                {selectedRecommendedTools.length > 0 ? (
                  <>
                    <strong>
                      {selectedRecommendedTools.length} Position{selectedRecommendedTools.length === 1 ? '' : 'en'}
                      {selectedTotal > 0 ? ` · ${formatPrice(String(selectedTotal))}` : ''}
                    </strong>
                    <p>{selectedRecommendedTools.map((item) => item.name).join(', ')}</p>
                  </>
                ) : (
                  <p>Wähle ein oder mehrere passende Tools aus. Die Auswahl wird mit deiner Anfrage übergeben.</p>
                )}
              </div>
            </div>

            <div className="tfdWizard__extrasPanel">
              <span>Mit drin</span>
              <ul>
                {extras.map((extra) => (
                  <li key={extra}>{extra}</li>
                ))}
              </ul>
            </div>
          </section>
        );
      }

      case 'contact':
        return (
          <section className="tfdWizard__block">
            <h3 className="tfdWizard__title">Kontakt aufnehmen</h3>
            <p className="tfdWizard__lead">
              Sende uns deine Angaben. Wir prüfen dein Partner-Modell und melden uns
              mit der passenden Empfehlung.
            </p>

            <div className="tfdWizard__form">
              <label>
                <span>Name *</span>
                <input
                  value={answers.contact?.name ?? ''}
                  onChange={(event) => updateContact('name', event.target.value)}
                  data-testid="contact-name-input"
                />
              </label>

              <label>
                <span>Firma</span>
                <input
                  value={answers.contact?.company ?? ''}
                  onChange={(event) => updateContact('company', event.target.value)}
                  data-testid="contact-company-input"
                />
              </label>

              <label>
                <span>E-Mail *</span>
                <input
                  type="email"
                  value={answers.contact?.email ?? ''}
                  onChange={(event) => updateContact('email', event.target.value)}
                  data-testid="contact-email-input"
                />
              </label>

              <label>
                <span>Telefon</span>
                <input
                  value={answers.contact?.phone ?? ''}
                  onChange={(event) => updateContact('phone', event.target.value)}
                  data-testid="contact-phone-input"
                />
              </label>

              <label className="tfdWizard__fieldFull">
                <span>Nachricht</span>
                <textarea
                  value={answers.contact?.message ?? ''}
                  onChange={(event) => updateContact('message', event.target.value)}
                  data-testid="contact-message-input"
                />
              </label>
            </div>

            <label className="tfdWizard__privacy">
              <input
                type="checkbox"
                checked={answers.privacyAccepted === true}
                onChange={(event) => updatePrivacyAccepted(event.target.checked)}
                data-testid="privacy-accepted-checkbox"
              />
              <span>{config.privacyNotice}</span>
            </label>

            {sendMessage && <div className="tfdWizard__notice">{sendMessage}</div>}
          </section>
        );

      default:
        return null;
    }
  };

  return (
    <div
      className={`tfdWizard tfdWizard--${theme}`}
      style={{ ['--partner-wizard-primary' as string]: primaryColor }}
      data-testid="partner-wizard"
    >
      <header className="tfdWizard__header">
        <span>Händler werden</span>
        <h2>{config.headline}</h2>
        <p>{config.intro}</p>
      </header>

      {configError && <div className="tfdWizard__error">{configError}</div>}

      <div className="tfdWizard__progress">
        <div style={{ width: `${progress}%` }} />
      </div>

      <div className="tfdWizard__layout">
        <aside className="tfdWizard__steps" aria-label="Wizard Schritte">
          {steps.map((step, index) => {
            const className = [
              index === stepIndex ? 'active' : '',
              index < stepIndex ? 'done' : '',
            ].filter(Boolean).join(' ');

            return (
              <button
                type="button"
                key={step.key}
                className={className}
                onClick={() => {
                  if (index <= stepIndex) {
                    setStepIndex(index);
                    setSendMessage('');
                  }
                }}
                data-testid={`step-${step.key}`}
              >
                <b>
                  {index < stepIndex ? (
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                      <path d="M5 12.5l4.2 4.2L19 7" />
                    </svg>
                  ) : (
                    index + 1
                  )}
                </b>
                <span>{step.label}</span>
              </button>
            );
          })}
        </aside>

        <div className={`tfdWizard__body ${currentStep.key === 'recommendation' ? 'tfdWizard__body--full' : ''}`}>
          <main className="tfdWizard__mainPane">{renderStep()}</main>
          {currentStep.key !== 'recommendation' && renderSidePanel()}
        </div>
      </div>

      <div className="tfdWizard__actions">
        <button
          type="button"
          onClick={back}
          disabled={stepIndex === 0}
          data-testid="wizard-back-button"
        >
          Zurück
        </button>

        {currentStep.key === 'contact' ? (
          <button
            type="button"
            onClick={handleSend}
            disabled={!canGoNext || sending}
            data-testid="wizard-submit-button"
          >
            {sending ? 'Senden...' : recommendation.cta}
          </button>
        ) : (
          <button
            type="button"
            onClick={next}
            disabled={!canGoNext}
            data-testid="wizard-next-button"
          >
            Weiter
          </button>
        )}
      </div>

      {renderProductPreview()}
    </div>
  );
}
