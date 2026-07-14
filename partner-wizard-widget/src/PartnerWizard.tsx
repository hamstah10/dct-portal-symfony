import React, { useEffect, useMemo, useState } from 'react';
import { fetchConfig, fetchRecommendation, submitLead } from './api';
import {
  experienceOptions,
  fallbackConfig,
  goalOptions,
  serviceOptions,
  websiteOptions,
  toolOptions,
  vehicleOptions,
  volumeOptions,
} from './fallbackConfig';
import { getLocalRecommendation } from './localRecommendation';
import type { RecommendedTool, SelectOption, ToolProduct, WizardAnswers, WizardConfig, WizardOptionConfig, WizardQuestionConfig, WizardRecommendation, WizardStepConfig } from './types';

type Props = {
  clientId?: string;
  apiBaseUrl?: string;
  primaryColor?: string;
  theme?: 'light' | 'dark';
};

const FALLBACK_VOLUME_NUMERIC: Record<string, number> = { low: 3, medium: 12, high: 25, network: 40 };

const FALLBACK_STEPS: WizardStepConfig[] = [
  {
    key: 'entry', label: 'Einstieg', type: 'questions',
    questions: [
      { answerKey: 'goal', title: 'Was ist dein Ziel?', questionType: 'generic', multiSelect: false, options: goalOptions },
      { answerKey: 'experience', title: 'Wie viel Erfahrung hast du?', questionType: 'generic', multiSelect: false, options: experienceOptions },
    ],
  },
  {
    key: 'vehicleTool', label: 'Fahrzeug & Tool', type: 'questions',
    questions: [
      { answerKey: 'vehicles', title: 'Welche Fahrzeuge möchtest du bearbeiten?', questionType: 'vehicleType', multiSelect: true, options: vehicleOptions },
      {
        answerKey: 'tool', title: 'Hast du bereits ein eigenes Tool?', questionType: 'toolManufacturer', multiSelect: false,
        helpText: 'Ohne eigenes Tuning-Tool ist keine direkte Händler-Aktivierung möglich. Dann geht es zuerst in die Tool-Beratung.',
        options: toolOptions,
      },
    ],
  },
  {
    key: 'offerVolume', label: 'Angebot & Volumen', type: 'questions',
    questions: [
      { answerKey: 'services', title: 'Welche Leistungen willst du anbieten?', questionType: 'generic', multiSelect: true, options: serviceOptions },
      { answerKey: 'website', title: 'Welche Website möchtest du anbieten?', questionType: 'generic', multiSelect: false, options: websiteOptions },
      {
        answerKey: 'volume', title: 'Wie viele Fahrzeuge erwartest du?', questionType: 'volume', multiSelect: false,
        options: volumeOptions.map((option) => ({ ...option, numericValue: FALLBACK_VOLUME_NUMERIC[option.value] })),
      },
    ],
  },
  { key: 'recommendation', label: 'Empfehlung', type: 'recommendation', questions: [] },
  {
    key: 'contact', label: 'Kontakt', type: 'contact',
    questions: [
      { answerKey: 'name', title: 'Name', questionType: 'generic', multiSelect: false, fieldType: 'text', required: true, options: [] },
      { answerKey: 'company', title: 'Firma', questionType: 'generic', multiSelect: false, fieldType: 'text', required: false, options: [] },
      { answerKey: 'email', title: 'E-Mail', questionType: 'generic', multiSelect: false, fieldType: 'email', required: true, options: [] },
      { answerKey: 'phone', title: 'Telefon', questionType: 'generic', multiSelect: false, fieldType: 'text', required: false, options: [] },
      { answerKey: 'message', title: 'Nachricht', questionType: 'generic', multiSelect: false, fieldType: 'textarea', required: false, options: [] },
      {
        answerKey: 'privacyAccepted',
        title: 'Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung der Anfrage gespeichert werden und wir dich kontaktieren.',
        questionType: 'generic', multiSelect: false, fieldType: 'checkbox', required: true, options: [],
      },
    ],
  },
];

function optionConfigToSelectOption(option: WizardOptionConfig): SelectOption {
  return {
    value: option.value,
    title: option.title,
    text: option.text ?? '',
    icon: option.icon,
    imageUrl: option.imageUrl,
    id: option.id,
    numericValue: option.numericValue,
  };
}

function getAnswerValue(answers: WizardAnswers, key: string): string | string[] | undefined {
  return (answers as unknown as Record<string, string | string[] | undefined>)[key];
}

function withAnswerValue(answers: WizardAnswers, key: string, value: string | string[]): WizardAnswers {
  return { ...(answers as unknown as Record<string, unknown>), [key]: value } as WizardAnswers;
}

function getContactFieldValue(answers: WizardAnswers, answerKey: string): string | string[] | boolean | undefined {
  if (answerKey === 'privacyAccepted') return answers.privacyAccepted;
  return answers.contact?.[answerKey];
}

function isQuestionVisible(question: WizardQuestionConfig, answers: WizardAnswers): boolean {
  const dependsOnAnswerKey = question.dependsOnAnswerKey;
  const dependsOnValue = question.dependsOnValue;
  if (!dependsOnAnswerKey || !dependsOnValue) return true;

  const triggerValue = getAnswerValue(answers, dependsOnAnswerKey) ?? getContactFieldValue(answers, dependsOnAnswerKey);

  if (Array.isArray(triggerValue)) {
    return triggerValue.includes(dependsOnValue);
  }

  return String(triggerValue ?? '') === dependsOnValue;
}

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
  return `${value.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €*`;
}

function hasSpecialPrice(product?: ToolProduct): boolean {
  const value = Number(product?.specialPrice);
  return Number.isFinite(value) && value > 0;
}

function effectivePriceValue(product?: ToolProduct): string | undefined {
  return hasSpecialPrice(product) ? product?.specialPrice : product?.price;
}

function volumeToMonthlyCount(value: string | undefined, volumeOptionsFromConfig: SelectOption[]): number {
  if (!value) return 0;
  const option = volumeOptionsFromConfig.find((item) => item.value === value);
  return option?.numericValue ?? 0;
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
  volumeOptionsFromConfig: SelectOption[],
  recommendation?: WizardRecommendation,
) {
  const contact = answers.contact ?? {};
  const vehicleSlugs = answers.vehicles ?? [];
  const vehicleTypeIds = vehicleSlugs
    .map((slug) => vehicleSlugToId[slug])
    .filter((id): id is number => typeof id === 'number');
  const services = answers.services ?? [];
  const website = answers.website ?? '';
  const currentTool = answers.tool ?? '';
  const monthlyVehicleCount = volumeToMonthlyCount(answers.volume, volumeOptionsFromConfig);
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
    website,
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
      website,
      monthlyVehicleCount,
      desiredToolLevel,
      privacyAccepted: answers.privacyAccepted === true,
      source: 'no1_partner_wizard',
    },
  };
}

function CheckIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <circle cx="10" cy="10" r="9" fill="currentColor" opacity="0.15" />
      <path d="M6 10.5L8.5 13L14 7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

function CrossIcon() {
  return (
    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <circle cx="10" cy="10" r="9" fill="currentColor" opacity="0.15" />
      <path d="M7 7L13 13M13 7L7 13" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
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
  const wizardSteps = useMemo<WizardStepConfig[]>(
    () => (config.steps && config.steps.length > 0 ? config.steps : FALLBACK_STEPS),
    [config],
  );
  const optionsByAnswerKey = useMemo(() => {
    const map: Record<string, SelectOption[]> = {};
    for (const step of wizardSteps) {
      for (const question of step.questions) {
        const options = question.options.map(optionConfigToSelectOption);
        if (question.answerKey === 'tool') {
          const hasNone = options.some((option) => option.value === 'none');
          map[question.answerKey] = hasNone ? options : [toolOptions[0], ...options];
        } else if (question.answerKey === 'website') {
          const hasNone = options.some((option) => option.value === 'none');
          map[question.answerKey] = hasNone ? options : [websiteOptions[0], ...options];
        } else {
          map[question.answerKey] = options;
        }
      }
    }
    return map;
  }, [wizardSteps]);
  const vehicleOptionsFromConfig = optionsByAnswerKey.vehicles ?? [];
  const serviceOptionsFromConfig = optionsByAnswerKey.services ?? [];
  const websiteOptionsFromConfig = optionsByAnswerKey.website ?? [];
  const toolOptionsFromConfig = optionsByAnswerKey.tool ?? [];
  const volumeOptionsFromConfig = optionsByAnswerKey.volume ?? [];
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

    const payload = createApiPayload(clientId, answers, vehicleSlugToId, volumeOptionsFromConfig, localRecommendation);

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

  const currentStep = wizardSteps[stepIndex];
  const recommendation = apiRecommendation ?? localRecommendation;
  const recommendationToolKeys = useMemo(
    () => (recommendation.recommendedTools ?? []).map((item) => item.key),
    [recommendation],
  );
  const progress = Math.round(((stepIndex + 1) / wizardSteps.length) * 100);

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
    if (currentStep.type === 'recommendation') return true;
    if (currentStep.type === 'contact') {
      return currentStep.questions
        .filter((question) => isQuestionVisible(question, answers) && question.required)
        .every((question) => {
          const value = getContactFieldValue(answers, question.answerKey);
          if (question.fieldType === 'checkbox') {
            return question.multiSelect ? Array.isArray(value) && value.length > 0 : value === true;
          }
          return Boolean(value && String(value).trim());
        });
    }

    return currentStep.questions
      .filter((question) => isQuestionVisible(question, answers))
      .every((question) => {
        const value = getAnswerValue(answers, question.answerKey);
        return question.multiSelect ? Array.isArray(value) && value.length > 0 : Boolean(value);
      });
  }, [answers, currentStep]);

  const selectSingle = (key: string, value: string) => {
    setAnswers((old) => withAnswerValue(old, key, value));
    setSendMessage('');
  };

  const selectMulti = (key: string, value: string) => {
    setAnswers((old) => withAnswerValue(old, key, toggleValue(getAnswerValue(old, key) as string[] | undefined, value)));
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

  const renderOptions = (items: SelectOption[], key: string, multi = false) => (
    <div className="tfdWizard__grid">
      {items.map((option) => {
        const rawValue = getAnswerValue(answers, key);
        const selected = Array.isArray(rawValue)
          ? rawValue.includes(option.value)
          : rawValue === option.value;

        return (
          <OptionCard
            key={option.value}
            option={option}
            selected={selected}
            onClick={() => {
              if (multi) {
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
      setStepIndex((old) => Math.min(old + 1, wizardSteps.length - 1));
      setSendMessage('');
    }
  };

  const back = () => {
    setStepIndex((old) => Math.max(old - 1, 0));
    setSendMessage('');
  };

  const updateContactField = (answerKey: string, value: string) => {
    setAnswers((old) => ({
      ...old,
      contact: {
        ...old.contact,
        [answerKey]: value,
      },
    }));
    setSendMessage('');
  };

  const updateContactCheckbox = (answerKey: string, checked: boolean) => {
    if (answerKey === 'privacyAccepted') {
      setAnswers((old) => ({ ...old, privacyAccepted: checked }));
    } else {
      setAnswers((old) => ({
        ...old,
        contact: {
          ...old.contact,
          [answerKey]: checked ? 'true' : '',
        },
      }));
    }
    setSendMessage('');
  };

  const toggleContactMultiValue = (answerKey: string, value: string) => {
    setAnswers((old) => {
      const current = old.contact?.[answerKey];
      const currentArray = Array.isArray(current) ? current : [];
      return {
        ...old,
        contact: {
          ...old.contact,
          [answerKey]: toggleValue(currentArray, value),
        },
      };
    });
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
    const priceLabel = formatPrice(effectivePriceValue(product));
    const originalPriceLabel = hasSpecialPrice(product) ? formatPrice(product?.price) : undefined;
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
            {priceLabel && (
              <p>
                {originalPriceLabel && <s className="tfdWizard__priceOriginal">{originalPriceLabel}</s>}
                <strong>{priceLabel}</strong>
              </p>
            )}
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

    const payload = createApiPayload(clientId, answers, vehicleSlugToId, volumeOptionsFromConfig, recommendation);

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
    const selectedVehicleOptions = (answers.vehicles ?? [])
      .map((value) => vehicleOptionsFromConfig.find((option) => option.value === value))
      .filter((option): option is SelectOption => Boolean(option))
      .slice(0, 4);
    const selectedServiceOptions = (answers.services ?? [])
      .map((value) => serviceOptionsFromConfig.find((option) => option.value === value))
      .filter((option): option is SelectOption => Boolean(option))
      .slice(0, 4);
    const selectedWebsite = websiteOptionsFromConfig.find((option) => option.value === answers.website);
    const selectedTool = toolOptionsFromConfig.find((option) => option.value === answers.tool)?.title;
    const selectedProductTools = (recommendation.recommendedTools ?? [])
      .filter((item) => (answers.selectedTools ?? []).includes(item.key));
    const selectedProductsTotal = selectedProductTools.reduce((sum, item) => {
      const price = Number(effectivePriceValue(item.product));
      return Number.isFinite(price) ? sum + price : sum;
    }, 0);

    const hasToolAnswers = Boolean(answers.vehicles?.length && answers.tool);
    const hasExistingTool = Boolean(answers.tool && answers.tool !== 'none');
    const hasSelectedProducts = selectedProductTools.length > 0;

    return (
      <aside className="tfdWizard__promoAside" aria-label="Tool Empfehlungen">
        {(selectedVehicleOptions.length > 0 || selectedServiceOptions.length > 0 || selectedWebsite || selectedTool) && (
          <div className="tfdWizard__promoCard tfdWizard__summaryCard">
            <span>Deine Angaben</span>
            {selectedTool && (
              <p className="tfdWizard__summaryLine">
                <b>Tool:</b>
                <span className={`tfdWizard__statusIcon ${hasExistingTool ? 'tfdWizard__statusIcon--yes' : 'tfdWizard__statusIcon--no'}`}>
                  {hasExistingTool ? <CheckIcon /> : <CrossIcon />}
                </span>
                {selectedTool}
              </p>
            )}
            {selectedVehicleOptions.length > 0 && (
              <div className="tfdWizard__summaryLine">
                <b>Fahrzeuge:</b>
                <span className="tfdWizard__summaryIconList">
                  {selectedVehicleOptions.map((option) => {
                    const iconUrl = option.imageUrl ?? option.icon;
                    return (
                      <span key={option.value} className="tfdWizard__summaryIconItem" title={option.title}>
                        {iconUrl ? (
                          <img src={iconUrl} alt="" loading="lazy" />
                        ) : (
                          <span className="tfdWizard__statusIcon tfdWizard__statusIcon--yes"><CheckIcon /></span>
                        )}
                        {option.title}
                      </span>
                    );
                  })}
                </span>
              </div>
            )}
            {selectedServiceOptions.length > 0 && (
              <div className="tfdWizard__summaryLine">
                <b>Leistungen:</b>
                <span className="tfdWizard__summaryIconList">
                  {selectedServiceOptions.map((option) => {
                    const iconUrl = option.imageUrl ?? option.icon;
                    return (
                      <span key={option.value} className="tfdWizard__summaryIconItem" title={option.title}>
                        {iconUrl ? (
                          <img src={iconUrl} alt="" loading="lazy" />
                        ) : (
                          <span className="tfdWizard__statusIcon tfdWizard__statusIcon--yes"><CheckIcon /></span>
                        )}
                        {option.title}
                      </span>
                    );
                  })}
                </span>
              </div>
            )}
            {selectedWebsite && (
              <div className="tfdWizard__summaryLine">
                <b>Website:</b>
                <span className="tfdWizard__summaryIconList">
                  <span className="tfdWizard__summaryIconItem" title={selectedWebsite.title}>
                    <span className="tfdWizard__statusIcon tfdWizard__statusIcon--yes"><CheckIcon /></span>
                    {selectedWebsite.title}
                  </span>
                </span>
              </div>
            )}
          </div>
        )}

        {currentStep.type === 'recommendation' && (
          <div className="tfdWizard__promoCard tfdWizard__selectedProductsCard">
            <span>Ausgewählte Produkte</span>
            {hasSelectedProducts ? (
              <>
                <strong>
                  {selectedProductTools.length} Position{selectedProductTools.length === 1 ? '' : 'en'}
                </strong>
                <div className="tfdWizard__selectedProductsList">
                  {selectedProductTools.map((item) => {
                    const priceLabel = formatPrice(effectivePriceValue(item.product));
                    const originalPriceLabel = hasSpecialPrice(item.product) ? formatPrice(item.product?.price) : undefined;
                    return (
                      <article key={item.key}>
                        {(item.imageUrl || item.product?.imageUrl) && (
                          <img src={item.imageUrl ?? item.product?.imageUrl} alt={item.product?.title ?? item.name} loading="lazy" />
                        )}
                        <div>
                          <b>{item.product?.title ?? item.name}</b>
                          {originalPriceLabel && <s className="tfdWizard__priceOriginal">{originalPriceLabel}</s>}
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
                <p className="tfdWizard__priceDisclaimer">* Preise netto, zzgl. gesetzl. MwSt.</p>
              </>
            ) : (
              <p>Wähle ein oder mehrere passende Tools aus. Die Auswahl wird mit deiner Anfrage übergeben.</p>
            )}
          </div>
        )}

        {!hasExistingTool && stepIndex < 3 && (
          <div className="tfdWizard__promoCard tfdWizard__promoCard--accent">
            <span>Tool Werbung</span>
            <strong>Passendes Leseschreibtool</strong>
            <p>Für den Händlerstart brauchst du ein unterstütztes Tool. Wir prüfen Protokolle und Fahrzeugbereiche.</p>

            {hasToolAnswers && recommendationError && (
              <div className="tfdWizard__miniNotice">{recommendationError}</div>
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
                  const priceLabel = formatPrice(effectivePriceValue(item.product));
                  const originalPriceLabel = hasSpecialPrice(item.product) ? formatPrice(item.product?.price) : undefined;
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
                            {originalPriceLabel && <s className="tfdWizard__priceOriginal">{originalPriceLabel}</s>}
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
            {recommendation.recommendedTools && recommendation.recommendedTools.length > 0 && (
              <p className="tfdWizard__priceDisclaimer">* Preise netto, zzgl. gesetzl. MwSt.</p>
            )}
          </div>
        )}

        {config.toolDisclaimer && currentStep.key === 'vehicleTool' && (
          <div className="tfdWizard__miniNotice">{config.toolDisclaimer}</div>
        )}

        {currentStep.type === 'contact' && (
          <div className="tfdWizard__promoCard tfdWizard__selectedProductsCard">
            <span>Ausgewählte Produkte</span>
            {hasSelectedProducts ? (
              <>
                <strong>
                  {selectedProductTools.length} Position{selectedProductTools.length === 1 ? '' : 'en'}
                </strong>
                <div className="tfdWizard__selectedProductsList">
                  {selectedProductTools.map((item) => {
                    const priceLabel = formatPrice(effectivePriceValue(item.product));
                    const originalPriceLabel = hasSpecialPrice(item.product) ? formatPrice(item.product?.price) : undefined;
                    return (
                      <article key={item.key}>
                        {(item.imageUrl || item.product?.imageUrl) && (
                          <img src={item.imageUrl ?? item.product?.imageUrl} alt={item.product?.title ?? item.name} loading="lazy" />
                        )}
                        <div>
                          <b>{item.product?.title ?? item.name}</b>
                          {originalPriceLabel && <s className="tfdWizard__priceOriginal">{originalPriceLabel}</s>}
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
                <p className="tfdWizard__priceDisclaimer">* Preise netto, zzgl. gesetzl. MwSt.</p>
              </>
            ) : (
              <p>Keine Produkte ausgewählt. Du kannst im vorherigen Schritt passende Tools auswählen.</p>
            )}
          </div>
        )}
      </aside>
    );
  };

  const renderQuestionsStep = (step: WizardStepConfig) => (
    <>
      {step.questions.filter((question) => isQuestionVisible(question, answers)).map((question) => (
        <section className="tfdWizard__block" key={question.answerKey}>
          <h3 className="tfdWizard__title">{question.title}</h3>
          {question.questionType === 'vehicleType' && (
            <button
              type="button"
              className="tfdWizard__selectAllButton"
              onClick={toggleAllVehicles}
              data-testid="vehicles-select-all-button"
            >
              {allVehiclesSelected ? 'Alle abwählen' : 'Alle auswählen'}
            </button>
          )}
          {question.helpText && <p className="tfdWizard__lead">{question.helpText}</p>}
          {renderOptions(optionsByAnswerKey[question.answerKey] ?? [], question.answerKey, question.multiSelect)}
        </section>
      ))}
    </>
  );

  const renderContactStep = (step: WizardStepConfig) => {
    const isSingleCheckbox = (question: WizardQuestionConfig) => question.fieldType === 'checkbox' && !question.multiSelect;
    const visibleQuestions = step.questions.filter((question) => isQuestionVisible(question, answers));
    const fields = visibleQuestions.filter((question) => !isSingleCheckbox(question));
    const singleCheckboxes = visibleQuestions.filter(isSingleCheckbox);

    return (
      <section className="tfdWizard__block">
        <h3 className="tfdWizard__title">Kontakt aufnehmen</h3>
        <p className="tfdWizard__lead">
          Sende uns deine Angaben. Wir prüfen dein Partner-Modell und melden uns
          mit der passenden Empfehlung.
        </p>

        <div className="tfdWizard__form">
          {fields.map((question) => {
            const rawValue = getContactFieldValue(answers, question.answerKey);
            const value = typeof rawValue === 'string' ? rawValue : '';
            const arrayValue = Array.isArray(rawValue) ? rawValue : [];
            const label = `${question.title}${question.required ? ' *' : ''}`;
            const testId = `contact-${question.answerKey}-input`;
            const isGroup = question.fieldType === 'radio' || question.fieldType === 'checkbox';
            const fullWidth = question.fieldType === 'textarea' || isGroup;

            // Radio/checkbox groups render one <label> per option, so the field wrapper
            // must be a <div> here — nesting <label> inside <label> is invalid HTML and
            // breaks layout in some browsers.
            if (isGroup) {
              return (
                <div key={question.answerKey} className={fullWidth ? 'tfdWizard__fieldFull' : undefined}>
                  <span className="tfdWizard__checkboxGroupLabel">{label}</span>
                  {question.fieldType === 'radio' ? (
                    <div className="tfdWizard__radioGroup" data-testid={testId}>
                      {question.options.map((option) => (
                        <label key={option.value} className="tfdWizard__radioOption">
                          <input
                            type="radio"
                            name={question.answerKey}
                            checked={value === option.value}
                            onChange={() => updateContactField(question.answerKey, option.value)}
                          />
                          <span>{option.title}</span>
                        </label>
                      ))}
                    </div>
                  ) : (
                    <div className="tfdWizard__checkboxGroup" data-testid={testId}>
                      {question.options.map((option) => (
                        <label key={option.value} className="tfdWizard__checkboxOption">
                          <input
                            type="checkbox"
                            checked={arrayValue.includes(option.value)}
                            onChange={() => toggleContactMultiValue(question.answerKey, option.value)}
                          />
                          <span>{option.title}</span>
                        </label>
                      ))}
                    </div>
                  )}
                </div>
              );
            }

            return (
              <label key={question.answerKey} className={fullWidth ? 'tfdWizard__fieldFull' : undefined}>
                <span>{label}</span>
                {question.fieldType === 'textarea' ? (
                  <textarea
                    value={value}
                    onChange={(event) => updateContactField(question.answerKey, event.target.value)}
                    data-testid={testId}
                  />
                ) : question.fieldType === 'select' ? (
                  <select
                    value={value}
                    onChange={(event) => updateContactField(question.answerKey, event.target.value)}
                    data-testid={testId}
                  >
                    <option value="">Bitte wählen</option>
                    {question.options.map((option) => (
                      <option key={option.value} value={option.value}>{option.title}</option>
                    ))}
                  </select>
                ) : (
                  <input
                    type={question.fieldType === 'email' ? 'email' : 'text'}
                    value={value}
                    onChange={(event) => updateContactField(question.answerKey, event.target.value)}
                    data-testid={testId}
                  />
                )}
              </label>
            );
          })}
        </div>

        {singleCheckboxes.map((question) => {
          const checked = getContactFieldValue(answers, question.answerKey) === true
            || getContactFieldValue(answers, question.answerKey) === 'true';

          return (
            <label key={question.answerKey} className="tfdWizard__privacy">
              <input
                type="checkbox"
                checked={checked}
                onChange={(event) => updateContactCheckbox(question.answerKey, event.target.checked)}
                data-testid={`contact-${question.answerKey}-checkbox`}
              />
              <span>{question.title}</span>
            </label>
          );
        })}

        {sendMessage && <div className="tfdWizard__notice">{sendMessage}</div>}
      </section>
    );
  };

  const renderStep = () => {
    switch (currentStep.type) {
      case 'questions':
        return renderQuestionsStep(currentStep);

      case 'recommendation': {
        const extras = Array.from(new Set([...(recommendation.bullets ?? []), ...includedExtras].filter(Boolean)));
        const recommendedTools = recommendation.recommendedTools?.slice(0, 4) ?? [];
        const selectedToolKeys = answers.selectedTools ?? [];
        const selectedRecommendedTools = recommendedTools.filter((item) => selectedToolKeys.includes(item.key));
        const selectedTotal = selectedRecommendedTools.reduce((sum, item) => {
          const price = Number(effectivePriceValue(item.product));
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
                    const priceLabel = formatPrice(effectivePriceValue(item.product));
                    const originalPriceLabel = hasSpecialPrice(item.product) ? formatPrice(item.product?.price) : undefined;
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
                            {originalPriceLabel && <s className="tfdWizard__priceOriginal">{originalPriceLabel}</s>}
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

            </div>

            {recommendation.recommendedProducts && recommendation.recommendedProducts.length > 0 && (
              <div className="tfdWizard__recommendationPanel">
                <div className="tfdWizard__recommendationPanelHeader">
                  <span>Produktempfehlungen</span>
                </div>

                <div className="tfdWizard__toolShowcase">
                  {recommendation.recommendedProducts.map((product) => {
                    const priceLabel = formatPrice(effectivePriceValue(product));
                    const originalPriceLabel = hasSpecialPrice(product) ? formatPrice(product.price) : undefined;
                    return (
                      <article
                        key={product.uid ?? product.title}
                        className={[
                          'tfdWizard__toolShowcaseItem',
                          product.imageUrl ? 'tfdWizard__toolShowcaseItem--withImage' : '',
                        ].filter(Boolean).join(' ')}
                      >
                        {product.imageUrl && (
                          <img className="tfdWizard__toolShowcaseImage" src={product.imageUrl} alt={product.title} loading="lazy" />
                        )}
                        <div>
                          <div className="tfdWizard__toolShowcaseTop">
                            <b>{product.title}</b>
                          </div>
                          <div className="tfdWizard__toolShowcaseMeta">
                            {product.sku && <span>{product.sku}</span>}
                            {originalPriceLabel && <s className="tfdWizard__priceOriginal">{originalPriceLabel}</s>}
                            {priceLabel && <strong>{priceLabel}</strong>}
                          </div>
                        </div>
                      </article>
                    );
                  })}
                </div>
              </div>
            )}

            {(recommendedTools.length > 0 || (recommendation.recommendedProducts?.length ?? 0) > 0) && (
              <p className="tfdWizard__priceDisclaimer">* Preise netto, zzgl. gesetzl. MwSt.</p>
            )}

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
        return renderContactStep(currentStep);

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
          {wizardSteps.map((step, index) => {
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

        <div className="tfdWizard__body">
          <main className="tfdWizard__mainPane">{renderStep()}</main>
          {renderSidePanel()}
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

        {currentStep.type === 'contact' ? (
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
