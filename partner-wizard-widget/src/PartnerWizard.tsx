import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { createOption, createQuestion, deleteOption, deleteQuestion, fetchAppointmentDays, fetchConfig, fetchRecommendation, saveTexts, submitLead } from './api';
import { AllTextsPanel, Editable, EditorDialog, EditProvider, targetId, Txt, type ClientTextField, type EditRequest, type PendingChange } from './editing';
import { DEFAULT_LABELS, interpolate } from './labels';
import { DeleteMark, makeAnswerKey, makeOptionValue, NewOptionDialog, NewQuestionDialog, type NewQuestionDraft } from './structureEditing';
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
import type { AppointmentDay, EditTarget, RecommendedTool, SelectOption, TextChange, ToolProduct, WizardAnswers, WizardConfig, WizardOptionConfig, WizardQuestionConfig, WizardRecommendation, WizardStepConfig } from './types';

type Props = {
  clientId?: string;
  apiBaseUrl?: string;
  primaryColor?: string;
  theme?: 'light' | 'dark';
  /** Text-Bearbeitungsmodus (nur Vorschau für Admins, braucht editCsrf). */
  editMode?: boolean;
  editCsrf?: string;
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
    optionId: option.optionId,
    numericValue: option.numericValue,
  };
}

function optionTarget(option: { optionId?: number }, field: 'title' | 'text'): EditTarget | null {
  return option.optionId ? { target: 'option', id: option.optionId, field } : null;
}

function questionTarget(question: WizardQuestionConfig, field: 'title' | 'helpText'): EditTarget | null {
  return question.id ? { target: 'question', id: question.id, field } : null;
}

function entityTarget(target: 'package' | 'tool', id: number | undefined, field: string): EditTarget | null {
  return id ? { target, id, field } : null;
}

function idsByKey(items: unknown[] | undefined): Record<string, number> {
  const map: Record<string, number> = {};
  for (const item of items ?? []) {
    if (!item || typeof item !== 'object') continue;
    const record = item as Record<string, unknown>;
    if (typeof record.key === 'string' && typeof record.id === 'number') map[record.key] = record.id;
  }
  return map;
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

function formatAppointment(start: string, timeZone: string): string {
  try {
    const date = new Date(start);
    const day = new Intl.DateTimeFormat('de-DE', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric', timeZone }).format(date);
    const time = new Intl.DateTimeFormat('de-DE', { hour: '2-digit', minute: '2-digit', timeZone }).format(date);
    return `${day.replace(',', '')} · ${time}`;
  } catch {
    return start;
  }
}

function formatAppointmentDay(start: string, timeZone: string): { weekday: string; date: string } {
  try {
    const date = new Date(start);
    return {
      weekday: new Intl.DateTimeFormat('de-DE', { weekday: 'short', timeZone }).format(date).replace('.', ''),
      date: new Intl.DateTimeFormat('de-DE', { day: '2-digit', month: '2-digit', timeZone }).format(date),
    };
  } catch {
    return { weekday: '', date: start.slice(0, 10) };
  }
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
  onDelete,
}: {
  option: SelectOption;
  selected: boolean;
  onClick: () => void;
  onDelete?: () => void;
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
        <strong><Editable target={optionTarget(option, 'title')} value={option.title} title="Antwortoption: Titel" /></strong>
        <span><Editable target={optionTarget(option, 'text')} value={option.text} title="Antwortoption: Text" multiline nullable /></span>
      </div>
      {onDelete && <DeleteMark label="Antwort löschen" onDelete={onDelete} />}
    </button>
  );
}

export function PartnerWizard({
  clientId = 'no1',
  apiBaseUrl = '',
  primaryColor = '#E31E24',
  theme = 'light',
  editMode: editModeProp = false,
  editCsrf = '',
}: Props) {
  const editMode = Boolean(editModeProp && editCsrf);
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
  const [appointmentDays, setAppointmentDays] = useState<AppointmentDay[]>([]);
  const [appointmentLoading, setAppointmentLoading] = useState(false);
  const [appointmentError, setAppointmentError] = useState('');
  const [appointmentReload, setAppointmentReload] = useState(0);
  const [appointmentDate, setAppointmentDate] = useState('');
  const [appointmentStart, setAppointmentStart] = useState('');
  const [bookedAppointment, setBookedAppointment] = useState('');
  const [recommendationReload, setRecommendationReload] = useState(0);
  const [pendingTexts, setPendingTexts] = useState<Record<string, PendingChange>>({});
  const [editRequest, setEditRequest] = useState<EditRequest | null>(null);
  const [showAllTexts, setShowAllTexts] = useState(false);
  const [savingTexts, setSavingTexts] = useState(false);
  const [editNotice, setEditNotice] = useState('');
  const [structureDialog, setStructureDialog] = useState<
    | { kind: 'question'; step: WizardStepConfig }
    | { kind: 'option'; question: WizardQuestionConfig }
    | null
  >(null);
  const [structureBusy, setStructureBusy] = useState(false);
  const sampleAnswersFilled = useRef(false);

  const labelValues = useMemo(() => {
    const values: Record<string, string> = { ...DEFAULT_LABELS, ...(config.labels ?? {}) };
    for (const change of Object.values(pendingTexts)) {
      if (change.target.target === 'label') {
        const key = change.target.key;
        values[key] = change.value ?? DEFAULT_LABELS[key] ?? '';
      }
    }
    return values;
  }, [config.labels, pendingTexts]);
  const t = useCallback(
    (key: string, vars?: Record<string, string | number>) => interpolate(labelValues[key] ?? key, vars),
    [labelValues],
  );
  const openEditor = useCallback((request: EditRequest) => {
    setEditRequest(request);
    setEditNotice('');
  }, []);
  const editContext = useMemo(() => ({
    enabled: editMode,
    t,
    raw: (key: string) => labelValues[key] ?? '',
    pending: pendingTexts,
    open: openEditor,
  }), [editMode, t, labelValues, pendingTexts, openEditor]);
  const pendingCount = Object.keys(pendingTexts).length;

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
  }, [answers, apiBaseUrl, clientId, localRecommendation, vehicleSlugToId, recommendationReload]);

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

  const appointmentEnabled = Boolean(config.appointment?.enabled);
  const appointmentTimezone = config.appointment?.timezone || 'Europe/Berlin';

  useEffect(() => {
    if (!appointmentEnabled || currentStep.type !== 'contact') return;

    let mounted = true;
    setAppointmentLoading(true);
    setAppointmentError('');

    fetchAppointmentDays(apiBaseUrl, clientId)
      .then((days) => {
        if (!mounted) return;
        setAppointmentDays(days);
        setAppointmentDate((old) => (days.some((day) => day.date === old) ? old : days[0]?.date ?? ''));
        setAppointmentStart((old) => (days.some((day) => day.slots.some((slot) => slot.start === old)) ? old : ''));
      })
      .catch(() => {
        if (!mounted) return;
        setAppointmentDays([]);
        setAppointmentError('load');
      })
      .finally(() => {
        if (mounted) setAppointmentLoading(false);
      });

    return () => {
      mounted = false;
    };
  }, [appointmentEnabled, currentStep.type, apiBaseUrl, clientId, appointmentReload]);

  const packageIdByKey = useMemo(() => idsByKey(config.packages), [config.packages]);
  const toolIdByKey = useMemo(() => idsByKey(config.tools), [config.tools]);
  // Nur bei echter API-Empfehlung ist das Paket eindeutig einem Datensatz zugeordnet.
  const recommendedPackageId = apiRecommendation?.packageKey ? packageIdByKey[apiRecommendation.packageKey] : undefined;

  // Bearbeitungsmodus: Beispielantworten, damit Empfehlung, Tools und Seitenleiste sichtbar werden.
  useEffect(() => {
    if (!editMode || sampleAnswersFilled.current || !config.steps?.length) return;
    sampleAnswersFilled.current = true;
    setAnswers((old) => {
      if (Object.keys(old).length > 0) return old;
      let sample: WizardAnswers = {};
      for (const step of wizardSteps) {
        if (step.type !== 'questions') continue;
        for (const question of step.questions) {
          const options = optionsByAnswerKey[question.answerKey] ?? [];
          if (options.length === 0) continue;
          sample = withAnswerValue(
            sample,
            question.answerKey,
            question.multiSelect ? options.slice(0, 2).map((option) => option.value) : options[0].value,
          );
        }
      }
      return sample;
    });
  }, [editMode, config.steps, wizardSteps, optionsByAnswerKey]);

  // Ungespeicherte Text-Änderungen nicht versehentlich verlieren.
  useEffect(() => {
    if (!editMode || pendingCount === 0) return;
    const onBeforeUnload = (event: BeforeUnloadEvent) => {
      event.preventDefault();
      event.returnValue = '';
    };
    window.addEventListener('beforeunload', onBeforeUnload);
    return () => window.removeEventListener('beforeunload', onBeforeUnload);
  }, [editMode, pendingCount]);

  const applyEdit = (value: string | null) => {
    if (!editRequest) return;
    const id = targetId(editRequest.target);
    if (value !== null && value === editRequest.current && !(id in pendingTexts)) {
      setEditRequest(null);
      return;
    }
    setPendingTexts((old) => ({ ...old, [id]: { target: editRequest.target, value } }));
    setEditRequest(null);
  };

  const saveAllTexts = async () => {
    if (!editMode || pendingCount === 0 || savingTexts) return;
    setSavingTexts(true);
    setEditNotice('');
    try {
      const changes = Object.values(pendingTexts).map((change) => ({ ...change.target, value: change.value }) as TextChange);
      await saveTexts(apiBaseUrl, clientId, editCsrf, changes);
      const loadedConfig = await fetchConfig(apiBaseUrl, clientId);
      setConfig({ ...fallbackConfig, ...loadedConfig });
      setPendingTexts({});
      setRecommendationReload((value) => value + 1);
      setEditNotice(`${changes.length} ${changes.length === 1 ? 'Änderung' : 'Änderungen'} gespeichert.`);
    } catch (error: unknown) {
      setEditNotice(error instanceof Error ? error.message : 'Speichern fehlgeschlagen.');
    } finally {
      setSavingTexts(false);
    }
  };

  // ---------------------------------------------------------------- Fragen & Antworten (sofort gespeichert)

  const runStructureChange = async (action: () => Promise<void>, success: string): Promise<boolean> => {
    if (!editMode || structureBusy) return false;
    setStructureBusy(true);
    setEditNotice('');
    try {
      await action();
      const loadedConfig = await fetchConfig(apiBaseUrl, clientId);
      setConfig({ ...fallbackConfig, ...loadedConfig });
      setRecommendationReload((value) => value + 1);
      setEditNotice(success);
      return true;
    } catch (error: unknown) {
      setEditNotice(error instanceof Error ? error.message : 'Speichern fehlgeschlagen.');
      return false;
    } finally {
      setStructureBusy(false);
    }
  };

  /** Ungespeicherte Text-Änderungen an gelöschten Einträgen verwerfen, sonst scheitert das spätere Speichern. */
  const dropPendingFor = (prefixes: string[]) => {
    setPendingTexts((old) => Object.fromEntries(
      Object.entries(old).filter(([id]) => !prefixes.some((prefix) => id.startsWith(prefix))),
    ));
  };

  const removeQuestion = (question: WizardQuestionConfig) => {
    if (!question.id) return;
    const optionCount = question.options.filter((option) => option.optionId).length;
    const message = `„${question.title}“ wirklich löschen?${optionCount ? ` Die ${optionCount} Antworten werden mit gelöscht.` : ''}\n\nBereits eingegangene Anfragen bleiben unverändert.`;
    if (!window.confirm(message)) return;
    const questionId = question.id;
    void runStructureChange(() => deleteQuestion(apiBaseUrl, clientId, editCsrf, questionId), `„${question.title}“ gelöscht.`).then((ok) => {
      if (!ok) return;
      dropPendingFor([`question:${questionId}:`, ...question.options.filter((option) => option.optionId).map((option) => `option:${option.optionId}:`)]);
      setAnswers((old) => {
        const next = { ...old } as Record<string, unknown>;
        delete next[question.answerKey];
        return next as WizardAnswers;
      });
    });
  };

  const removeOption = (option: { optionId?: number; title: string }) => {
    if (!option.optionId) return;
    if (!window.confirm(`Antwort „${option.title}“ wirklich löschen?`)) return;
    const optionId = option.optionId;
    void runStructureChange(() => deleteOption(apiBaseUrl, clientId, editCsrf, optionId), `Antwort „${option.title}“ gelöscht.`).then((ok) => {
      if (ok) dropPendingFor([`option:${optionId}:`]);
    });
  };

  const addQuestion = (step: WizardStepConfig, draft: NewQuestionDraft) => {
    if (!step.id) return;
    const takenKeys = new Set(wizardSteps.flatMap((item) => item.questions.map((question) => question.answerKey)));
    const takenValues = new Set<string>();
    const stepId = step.id;
    void runStructureChange(() => createQuestion(apiBaseUrl, clientId, editCsrf, {
      stepId,
      answerKey: makeAnswerKey(draft.title, takenKeys),
      title: draft.title,
      helpText: draft.helpText,
      questionType: draft.kind.questionType,
      fieldType: draft.kind.fieldType,
      multiSelect: draft.kind.multiSelect,
      required: draft.required,
      options: draft.optionTitles.map((title) => {
        const value = makeOptionValue(title, takenValues);
        takenValues.add(value);
        return { value, title };
      }),
    }), `„${draft.title}“ hinzugefügt.`).then((ok) => {
      if (ok) setStructureDialog(null);
    });
  };

  const addOption = (question: WizardQuestionConfig, input: { title: string; text: string | null }) => {
    if (!question.id) return;
    const questionId = question.id;
    const value = makeOptionValue(input.title, new Set(question.options.map((option) => option.value)));
    void runStructureChange(
      () => createOption(apiBaseUrl, clientId, editCsrf, questionId, { value, title: input.title, ...(input.text ? { text: input.text } : {}) }),
      `Antwort „${input.title}“ hinzugefügt.`,
    ).then((ok) => {
      if (ok) setStructureDialog(null);
    });
  };

  /** Eigene Antwortoptionen sind nur bei diesen Fragen möglich (Fahrzeugtypen/Tools kommen aus der Client-Konfiguration). */
  const hasOwnOptions = (question: WizardQuestionConfig) => question.questionType === 'generic' || question.questionType === 'volume';

  const renderQuestionTools = (question: WizardQuestionConfig, kind: 'Frage' | 'Feld', withAddOption: boolean) => {
    if (!editMode || !question.id) return null;
    return (
      <div className="tfdEdit__structureBar">
        {question.dependsOnAnswerKey && (
          <small>nur sichtbar bei „{question.dependsOnAnswerKey}“{question.dependsOnValue ? ` = ${question.dependsOnValue}` : ''}</small>
        )}
        {withAddOption && (
          <button type="button" onClick={() => setStructureDialog({ kind: 'option', question })} disabled={structureBusy}>+ Antwort</button>
        )}
        <button type="button" className="tfdEdit__danger" onClick={() => removeQuestion(question)} disabled={structureBusy}>{kind} löschen</button>
      </div>
    );
  };

  const renderAddQuestion = (step: WizardStepConfig) => {
    if (!editMode || !step.id) return null;
    return (
      <button type="button" className="tfdEdit__addQuestion" onClick={() => setStructureDialog({ kind: 'question', step })} disabled={structureBusy}>
        + {step.type === 'contact' ? 'Feld hinzufügen' : 'Frage hinzufügen'}
      </button>
    );
  };

  const clientTextFields: ClientTextField[] = [
    { field: 'headline', label: 'Headline', value: config.headline },
    { field: 'introText', label: 'Intro-Text', value: config.intro },
    { field: 'toolDisclaimer', label: 'Tool-Disclaimer', value: config.toolDisclaimer ?? '' },
    { field: 'privacyNotice', label: 'DSGVO-Hinweis (Client)', value: config.privacyNotice },
  ];

  const canGoNext = useMemo(() => {
    if (editMode) return true;
    if (currentStep.type === 'recommendation') return true;
    if (currentStep.type === 'contact') {
      if (appointmentEnabled && config.appointment?.required && !appointmentStart) return false;
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
  }, [editMode, answers, currentStep, appointmentEnabled, config.appointment?.required, appointmentStart]);

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
            onDelete={editMode && option.optionId ? () => removeOption(option) : undefined}
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
            <span><Txt k="modal.eyebrow" /></span>
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
                  <em><Txt k="modal.sku" /></em>
                  <b>{product.sku}</b>
                </div>
              )}
              {productPreview.level && (
                <div>
                  <em><Txt k="modal.level" /></em>
                  <b>{productPreview.level}</b>
                </div>
              )}
              <div>
                <em><Txt k="modal.match" /></em>
                <b><Txt k={productPreview.fromCombination ? 'modal.exact' : 'modal.fit'} /></b>
              </div>
            </div>

            <button
              type="button"
              className="tfdWizard__modalSelect"
              onClick={() => toggleRecommendedTool(productPreview.key)}
              aria-pressed={selected}
            >
              <Txt k={selected ? 'modal.remove' : 'modal.add'} />
            </button>
          </div>
        </div>
      </div>
    );
  };

  const handleSend = async () => {
    if (!canGoNext || sending) return;
    if (editMode) {
      setSendMessage('Bearbeitungsmodus: Es wird keine Anfrage gesendet.');
      return;
    }

    setSending(true);
    setSendMessage('');

    const payload: Record<string, unknown> = {
      ...createApiPayload(clientId, answers, vehicleSlugToId, volumeOptionsFromConfig, recommendation),
      ...(appointmentEnabled && appointmentStart ? { appointment: { start: appointmentStart } } : {}),
    };

    try {
      const result = await submitLead(apiBaseUrl, payload) as { appointment?: { start?: string } | null } | null;
      const bookedStart = result?.appointment?.start;
      if (bookedStart) {
        const label = formatAppointment(bookedStart, appointmentTimezone);
        setBookedAppointment(label);
        setAppointmentStart('');
        setAppointmentReload((value) => value + 1);
        setSendMessage(t('send.successAppointment', { date: label }));
      } else {
        setSendMessage(t('send.success'));
      }
    } catch (error: unknown) {
      const status = typeof error === 'object' && error !== null && 'status' in error
        ? (error as { status?: number }).status
        : undefined;
      if (status === 409) {
        setAppointmentStart('');
        setAppointmentReload((value) => value + 1);
        setSendMessage(t('send.slotTaken'));
      } else {
        const message = error instanceof Error ? error.message : 'Unbekannter API-Fehler';
        setSendMessage(t('send.error', { message }));
      }
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
    const isWebsiteRecommendation = currentStep.type === 'recommendation'
      && ['full_package', 'website_only', 'configurator_only'].includes(recommendation.packageKey ?? '');

    return (
      <aside className="tfdWizard__promoAside" aria-label="Tool Empfehlungen">
        {(selectedVehicleOptions.length > 0 || selectedServiceOptions.length > 0 || selectedWebsite || selectedTool) && (
          <div className="tfdWizard__promoCard tfdWizard__summaryCard">
            <span><Txt k="summary.title" /></span>
            {selectedTool && (
              <p className="tfdWizard__summaryLine">
                <b><Txt k="summary.tool" /></b>
                <span className={`tfdWizard__statusIcon ${hasExistingTool ? 'tfdWizard__statusIcon--yes' : 'tfdWizard__statusIcon--no'}`}>
                  {hasExistingTool ? <CheckIcon /> : <CrossIcon />}
                </span>
                {selectedTool}
              </p>
            )}
            {selectedVehicleOptions.length > 0 && (
              <div className="tfdWizard__summaryLine">
                <b><Txt k="summary.vehicles" /></b>
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
                <b><Txt k="summary.services" /></b>
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
                <b><Txt k="summary.website" /></b>
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

        {currentStep.type === 'recommendation' && isWebsiteRecommendation && (
          <div className="tfdWizard__promoCard tfdWizard__promoCard--accent">
            <span><Txt k="promo.websiteTitle" /></span>
            <strong><Editable target={entityTarget('package', recommendedPackageId, 'title')} value={recommendation.packageName} title="Paket: Titel" /></strong>
            <p><Editable target={entityTarget('package', recommendedPackageId, 'description')} value={recommendation.packageText ?? recommendation.summary} title="Paket: Beschreibung" multiline /></p>
          </div>
        )}

        {currentStep.type === 'recommendation' && (
          <div className="tfdWizard__promoCard tfdWizard__selectedProductsCard">
            <span><Txt k="selected.title" /></span>
            {hasSelectedProducts ? (
              <>
                <strong>
                  {selectedProductTools.length}{' '}
                  <Txt k={selectedProductTools.length === 1 ? 'selected.positionSingular' : 'selected.positionPlural'} />
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
                            <Txt k="product.details" />
                          </button>
                        </div>
                      </article>
                    );
                  })}
                </div>
                {selectedProductsTotal > 0 && (
                  <p className="tfdWizard__selectedProductsTotal">
                    <b><Txt k="selected.total" /></b> {formatPrice(String(selectedProductsTotal))}
                  </p>
                )}
                <p className="tfdWizard__priceDisclaimer"><Txt k="price.disclaimer" /></p>
              </>
            ) : (
              <p><Txt k="selected.empty" /></p>
            )}
          </div>
        )}

        {!hasExistingTool && stepIndex < 3 && (
          <div className="tfdWizard__promoCard tfdWizard__promoCard--accent">
            <span><Txt k="toolAd.eyebrow" /></span>
            <strong><Txt k="toolAd.title" /></strong>
            <p><Txt k="toolAd.text" /></p>

            {hasToolAnswers && recommendationError && (
              <div className="tfdWizard__miniNotice">{recommendationError}</div>
            )}

            {!hasToolAnswers ? (
              <div className="tfdWizard__miniNotice">
                <Txt k="toolAd.needAnswers" />
              </div>
            ) : recommendationLoading ? (
              <div className="tfdWizard__miniNotice">
                <Txt k="toolAd.loading" />
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
                        <b><Editable target={entityTarget('tool', toolIdByKey[item.key], 'name')} value={item.name} title="Tool: Name" /></b>
                        {item.fromCombination && (
                          <span className="tfdWizard__adBadge"><Txt k="toolAd.matchBadge" /></span>
                        )}
                        {item.description && <small><Editable target={entityTarget('tool', toolIdByKey[item.key], 'description')} value={item.description} title="Tool: Beschreibung" multiline nullable /></small>}
                        {item.product && (
                          <div className="tfdWizard__adProduct">
                            {originalPriceLabel && <s className="tfdWizard__priceOriginal">{originalPriceLabel}</s>}
                            {priceLabel && <span className="tfdWizard__adPrice">{priceLabel}</span>}
                            <button
                              type="button"
                              className="tfdWizard__adLink"
                              onClick={() => openProductPreview(item)}
                            >
                              <Txt k="product.view" />
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
                <Txt k="toolAd.empty" />
              </div>
            ) : (
              <div className="tfdWizard__tagList">
                <em>Autotuner</em>
                <em>KESS3</em>
                <em>FLEX</em>
              </div>
            )}
            {recommendation.recommendedTools && recommendation.recommendedTools.length > 0 && (
              <p className="tfdWizard__priceDisclaimer"><Txt k="price.disclaimer" /></p>
            )}
          </div>
        )}

        {config.toolDisclaimer && currentStep.key === 'vehicleTool' && (
          <div className="tfdWizard__miniNotice">
            <Editable target={{ target: 'client', field: 'toolDisclaimer' }} value={config.toolDisclaimer} title="Client: Tool-Disclaimer" multiline />
          </div>
        )}

        {currentStep.type === 'contact' && (
          <div className="tfdWizard__promoCard tfdWizard__selectedProductsCard">
            <span><Txt k="selected.title" /></span>
            {hasSelectedProducts ? (
              <>
                <strong>
                  {selectedProductTools.length}{' '}
                  <Txt k={selectedProductTools.length === 1 ? 'selected.positionSingular' : 'selected.positionPlural'} />
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
                            <Txt k="product.details" />
                          </button>
                        </div>
                      </article>
                    );
                  })}
                </div>
                {selectedProductsTotal > 0 && (
                  <p className="tfdWizard__selectedProductsTotal">
                    <b><Txt k="selected.total" /></b> {formatPrice(String(selectedProductsTotal))}
                  </p>
                )}
                <p className="tfdWizard__priceDisclaimer"><Txt k="price.disclaimer" /></p>
              </>
            ) : (
              <p><Txt k="selected.emptyContact" /></p>
            )}
          </div>
        )}
      </aside>
    );
  };

  const renderQuestionsStep = (step: WizardStepConfig) => (
    <>
      {step.questions.filter((question) => editMode || isQuestionVisible(question, answers)).map((question) => (
        <section className="tfdWizard__block" key={question.answerKey}>
          <h3 className="tfdWizard__title">
            <Editable target={questionTarget(question, 'title')} value={question.title} title="Frage: Titel" />
          </h3>
          {renderQuestionTools(question, 'Frage', hasOwnOptions(question))}
          {question.questionType === 'vehicleType' && (
            <button
              type="button"
              className="tfdWizard__selectAllButton"
              onClick={toggleAllVehicles}
              data-testid="vehicles-select-all-button"
            >
              <Txt k={allVehiclesSelected ? 'vehicles.deselectAll' : 'vehicles.selectAll'} />
            </button>
          )}
          {(question.helpText || (editMode && question.id)) && (
            <p className="tfdWizard__lead">
              <Editable target={questionTarget(question, 'helpText')} value={question.helpText} title="Frage: Hilfetext" multiline nullable />
            </p>
          )}
          {renderOptions(optionsByAnswerKey[question.answerKey] ?? [], question.answerKey, question.multiSelect)}
        </section>
      ))}
      {renderAddQuestion(step)}
    </>
  );

  const renderAppointmentPicker = () => {
    const activeDay = appointmentDays.find((day) => day.date === appointmentDate) ?? appointmentDays[0];
    const title = config.appointment?.title || 'Beratungstermin';
    const duration = config.appointment?.durationMinutes ?? 30;

    return (
      <div className="tfdWizard__appointment" data-testid="appointment-picker">
        <div className="tfdWizard__appointmentHead">
          <span className="tfdWizard__checkboxGroupLabel">
            <Txt k="appointment.choose" vars={{ title }} />
            {config.appointment?.required ? ' *' : <>{' '}<Txt k="appointment.optional" /></>}
          </span>
          <small><Txt k="appointment.meta" vars={{ duration, timezone: appointmentTimezone.replace('_', ' ') }} /></small>
        </div>

        {bookedAppointment && (
          <div className="tfdWizard__appointmentBooked"><Txt k="appointment.booked" vars={{ date: bookedAppointment }} /></div>
        )}

        {appointmentLoading && appointmentDays.length === 0 ? (
          <div className="tfdWizard__appointmentEmpty"><Txt k="appointment.loading" /></div>
        ) : appointmentError ? (
          <div className="tfdWizard__appointmentEmpty"><Txt k="appointment.loadError" /></div>
        ) : appointmentDays.length === 0 ? (
          <div className="tfdWizard__appointmentEmpty"><Txt k="appointment.empty" /></div>
        ) : (
          <>
            <div className="tfdWizard__appointmentDays" role="tablist">
              {appointmentDays.map((day) => {
                const parts = formatAppointmentDay(day.slots[0].start, appointmentTimezone);
                return (
                  <button
                    type="button"
                    key={day.date}
                    role="tab"
                    aria-selected={day.date === activeDay?.date}
                    className={day.date === activeDay?.date ? 'active' : ''}
                    onClick={() => setAppointmentDate(day.date)}
                  >
                    <small>{parts.weekday}</small>
                    <b>{parts.date}</b>
                  </button>
                );
              })}
            </div>

            {activeDay && (
              <div className="tfdWizard__appointmentSlots">
                {activeDay.slots.map((slot) => {
                  const selected = slot.start === appointmentStart;
                  return (
                    <button
                      type="button"
                      key={slot.start}
                      className={selected ? 'active' : ''}
                      aria-pressed={selected}
                      onClick={() => {
                        setAppointmentStart(selected ? '' : slot.start);
                        setSendMessage('');
                      }}
                    >
                      {slot.time}
                    </button>
                  );
                })}
              </div>
            )}

            {appointmentStart && (
              <div className="tfdWizard__appointmentSelected">
                <b><Txt k="appointment.selected" vars={{ date: formatAppointment(appointmentStart, appointmentTimezone) }} /></b>
              </div>
            )}
          </>
        )}
      </div>
    );
  };

  const renderContactStep = (step: WizardStepConfig) => {
    const isSingleCheckbox = (question: WizardQuestionConfig) => question.fieldType === 'checkbox' && !question.multiSelect;
    const visibleQuestions = step.questions.filter((question) => editMode || isQuestionVisible(question, answers));
    const fieldHasOptions = (question: WizardQuestionConfig) => ['select', 'radio'].includes(question.fieldType ?? '')
      || (question.fieldType === 'checkbox' && question.multiSelect);
    const renderFieldTools = (question: WizardQuestionConfig) => {
      if (!editMode || !question.id) return null;
      const withOptions = fieldHasOptions(question) && hasOwnOptions(question);
      return (
        <>
          {withOptions && question.fieldType === 'select' && (
            <div className="tfdEdit__chips">
              <small>Antworten:</small>
              {question.options.map((option) => (
                <span key={option.value} className="tfdEdit__chip">
                  <Editable target={optionTarget(option, 'title')} value={option.title} title="Antwortoption: Titel" />
                  {option.optionId && <DeleteMark label="Antwort löschen" onDelete={() => removeOption(option)} />}
                </span>
              ))}
            </div>
          )}
          {renderQuestionTools(question, 'Feld', withOptions)}
        </>
      );
    };
    const fields = visibleQuestions.filter((question) => !isSingleCheckbox(question));
    const singleCheckboxes = visibleQuestions.filter(isSingleCheckbox);

    return (
      <section className="tfdWizard__block">
        <h3 className="tfdWizard__title"><Txt k="contact.title" /></h3>
        <p className="tfdWizard__lead"><Txt k="contact.lead" /></p>

        <div className="tfdWizard__form">
          {fields.map((question) => {
            const rawValue = getContactFieldValue(answers, question.answerKey);
            const value = typeof rawValue === 'string' ? rawValue : '';
            const arrayValue = Array.isArray(rawValue) ? rawValue : [];
            const label = (
              <>
                <Editable target={questionTarget(question, 'title')} value={question.title} title="Kontaktfeld: Bezeichnung" />
                {question.required ? ' *' : ''}
              </>
            );
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
                          <span><Editable target={optionTarget(option, 'title')} value={option.title} title="Antwortoption: Titel" /></span>
                          {editMode && option.optionId && <DeleteMark label="Antwort löschen" onDelete={() => removeOption(option)} />}
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
                          <span><Editable target={optionTarget(option, 'title')} value={option.title} title="Antwortoption: Titel" /></span>
                          {editMode && option.optionId && <DeleteMark label="Antwort löschen" onDelete={() => removeOption(option)} />}
                        </label>
                      ))}
                    </div>
                  )}
                  {renderFieldTools(question)}
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
                    <option value="">{t('contact.selectPlaceholder')}</option>
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
                {renderFieldTools(question)}
              </label>
            );
          })}
        </div>

        {appointmentEnabled && renderAppointmentPicker()}

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
              <span>
                <Editable target={questionTarget(question, 'title')} value={question.title} title="Checkbox: Text" multiline />
                {renderFieldTools(question)}
              </span>
            </label>
          );
        })}

        {renderAddQuestion(step)}

        {sendMessage && <div className="tfdWizard__notice">{sendMessage}</div>}
      </section>
    );
  };

  const renderStep = () => {
    switch (currentStep.type) {
      case 'questions':
        return renderQuestionsStep(currentStep);

      case 'recommendation': {
        const extraApi = t('recommendation.extraApi');
        const extras = Array.from(new Set([...(recommendation.bullets ?? []), extraApi].filter(Boolean)));
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
              <span><Txt k="recommendation.eyebrow" /></span>
              <h3><Editable target={entityTarget('package', recommendedPackageId, 'title')} value={recommendation.packageName} title="Paket: Titel" /></h3>
              <p><Editable target={entityTarget('package', recommendedPackageId, 'description')} value={recommendation.summary} title="Paket: Beschreibung" multiline /></p>
            </div>

            <div className="tfdWizard__recommendationPanel">
              <div className="tfdWizard__recommendationPanelHeader">
                <span><Txt k="recommendation.toolsTitle" /></span>
                {recommendationLoading && <em><Txt k="recommendation.loading" /></em>}
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
                            <b><Editable target={entityTarget('tool', toolIdByKey[item.key], 'name')} value={item.name} title="Tool: Name" /></b>
                            <button
                              type="button"
                              className="tfdWizard__cartToggle"
                              onClick={() => toggleRecommendedTool(item.key)}
                              aria-pressed={selected}
                            >
                              <Txt k={selected ? 'recommendation.selected' : 'recommendation.select'} />
                            </button>
                          </div>
                          {item.fromCombination && <small><Txt k="recommendation.exactMatch" /></small>}
                          {item.description && <p><Editable target={entityTarget('tool', toolIdByKey[item.key], 'description')} value={item.description} title="Tool: Beschreibung" multiline nullable /></p>}
                          <div className="tfdWizard__toolShowcaseMeta">
                            {item.level && <span>{item.level}</span>}
                            {originalPriceLabel && <s className="tfdWizard__priceOriginal">{originalPriceLabel}</s>}
                            {priceLabel && <strong>{priceLabel}</strong>}
                            {item.product && (
                              <button type="button" onClick={() => openProductPreview(item)}>
                                <Txt k="product.view" />
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
                  <Txt k="recommendation.noTools" />
                </div>
              )}

            </div>

            {recommendation.recommendedProducts && recommendation.recommendedProducts.length > 0 && (
              <div className="tfdWizard__recommendationPanel">
                <div className="tfdWizard__recommendationPanelHeader">
                  <span><Txt k="recommendation.productsTitle" /></span>
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
              <p className="tfdWizard__priceDisclaimer"><Txt k="price.disclaimer" /></p>
            )}

            <div className="tfdWizard__extrasPanel">
              <span><Txt k="recommendation.extrasTitle" /></span>
              <ul>
                {extras.map((extra) => (
                  <li key={extra}>{extra === extraApi ? <Txt k="recommendation.extraApi" /> : extra}</li>
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
    <EditProvider value={editContext}>
    <div
      className={`tfdWizard tfdWizard--${theme}${editMode ? ' tfdWizard--editing' : ''}`}
      style={{ ['--partner-wizard-primary' as string]: primaryColor }}
      data-testid="partner-wizard"
    >
      {editMode && (
        <div className="tfdEdit__toolbar">
          <div className="tfdEdit__toolbarInfo">
            <b>Bearbeitungsmodus</b>
            <span>Markierte Texte anklicken · Fragen/Antworten hinzufügen und löschen (sofort gespeichert) · alle Schritte frei wählbar · es wird nichts gesendet</span>
          </div>
          <div className="tfdEdit__toolbarActions">
            <button type="button" onClick={() => setShowAllTexts((value) => !value)}>Alle Texte</button>
            <button type="button" onClick={() => { setPendingTexts({}); setEditNotice(''); }} disabled={pendingCount === 0 || savingTexts}>
              Verwerfen
            </button>
            <button type="button" className="tfdEdit__primary" onClick={saveAllTexts} disabled={pendingCount === 0 || savingTexts}>
              {savingTexts ? 'Speichert…' : `Speichern (${pendingCount})`}
            </button>
          </div>
          {editNotice && <div className="tfdEdit__notice">{editNotice}</div>}
        </div>
      )}

      <header className="tfdWizard__header">
        <span><Txt k="header.eyebrow" /></span>
        <h2><Editable target={{ target: 'client', field: 'headline' }} value={config.headline} title="Client: Headline" /></h2>
        <p><Editable target={{ target: 'client', field: 'introText' }} value={config.intro} title="Client: Intro-Text" multiline /></p>
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
                  if (editMode || index <= stepIndex) {
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
                <span>
                  <Editable target={step.id ? { target: 'step', id: step.id, field: 'label' } : null} value={step.label} title="Schritt: Bezeichnung" />
                </span>
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
          <Txt k="nav.back" />
        </button>

        {currentStep.type === 'contact' ? (
          <button
            type="button"
            onClick={handleSend}
            disabled={!canGoNext || sending}
            data-testid="wizard-submit-button"
          >
            {sending ? <Txt k="nav.sending" /> : (
              <Editable target={entityTarget('package', recommendedPackageId, 'ctaLabel')} value={recommendation.cta} title="Paket: Button-Text" />
            )}
          </button>
        ) : (
          <button
            type="button"
            onClick={next}
            disabled={!canGoNext}
            data-testid="wizard-next-button"
          >
            <Txt k="nav.next" />
          </button>
        )}
      </div>

      {renderProductPreview()}

      {editMode && showAllTexts && (
        <AllTextsPanel clientFields={clientTextFields} onClose={() => setShowAllTexts(false)} />
      )}
      {editMode && structureDialog?.kind === 'question' && (
        <NewQuestionDialog
          stepType={structureDialog.step.type}
          stepLabel={structureDialog.step.label}
          busy={structureBusy}
          onSubmit={(draft) => addQuestion(structureDialog.step, draft)}
          onClose={() => setStructureDialog(null)}
        />
      )}
      {editMode && structureDialog?.kind === 'option' && (
        <NewOptionDialog
          questionTitle={structureDialog.question.title}
          withText={structureDialog.question.fieldType == null}
          busy={structureBusy}
          onSubmit={(input) => addOption(structureDialog.question, input)}
          onClose={() => setStructureDialog(null)}
        />
      )}
      {editMode && editRequest && (
        <EditorDialog
          key={targetId(editRequest.target)}
          request={editRequest}
          onApply={applyEdit}
          onClose={() => setEditRequest(null)}
        />
      )}
    </div>
    </EditProvider>
  );
}
