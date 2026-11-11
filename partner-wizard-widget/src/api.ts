import type { AppointmentDay, AppointmentSlot, RecommendedTool, TextChange, ToolProduct, VehicleTypeOption, WizardConfig, WizardFieldType, WizardOptionConfig, WizardQuestionConfig, WizardQuestionType, WizardRecommendation, WizardStepConfig, WizardStepType } from './types';

type ApiError = Error & { status?: number; body?: string };

function normalizeBaseUrl(apiBaseUrl: string): string {
  return apiBaseUrl.replace(/\/+$/, '');
}

async function readError(response: Response, prefix: string): Promise<ApiError> {
  let body = '';
  try {
    body = await response.text();
  } catch {
    body = '';
  }

  const error = new Error(`${prefix} failed with ${response.status}${body ? `: ${body.slice(0, 500)}` : ''}`) as ApiError;
  error.status = response.status;
  error.body = body;
  return error;
}

function asRecord(value: unknown): Record<string, unknown> {
  return value && typeof value === 'object' && !Array.isArray(value) ? (value as Record<string, unknown>) : {};
}

function unwrapApiData(data: unknown): Record<string, unknown> {
  const root = asRecord(data);
  const candidates = [
    root.config,
    root.client,
    root.data,
    root.result,
    root.item,
    root,
  ];

  for (const candidate of candidates) {
    const record = asRecord(candidate);
    if (Object.keys(record).length > 0) return record;
  }

  return root;
}

function asString(value: unknown, fallback = ''): string {
  if (value === null || value === undefined) return fallback;
  return String(value);
}

function asNumber(value: unknown, fallback = 0): number {
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : fallback;
}

function asBool(value: unknown, fallback = false): boolean {
  if (typeof value === 'boolean') return value;
  if (typeof value === 'number') return value === 1;
  if (typeof value === 'string') return ['1', 'true', 'yes', 'ja', 'on'].includes(value.toLowerCase());
  return fallback;
}

function normalizeArray(value: unknown): unknown[] {
  if (Array.isArray(value)) return value;

  if (typeof value === 'string') {
    const trimmed = value.trim();
    if (!trimmed) return [];
    try {
      const parsed = JSON.parse(trimmed);
      const parsedArray = normalizeArray(parsed);
      if (parsedArray.length > 0) return parsedArray;
    } catch {
      // keep CSV fallback
    }
    return trimmed.split(/[;,\n]/).map((item) => item.trim()).filter(Boolean);
  }

  if (value && typeof value === 'object') {
    const record = value as Record<string, unknown>;

    // EasyAdmin/Doctrine JSON fields can arrive as {"0":"stage1","1":"stage2"}.
    const numericKeys = Object.keys(record).filter((key) => /^\d+$/.test(key));
    if (numericKeys.length > 0) {
      return numericKeys
        .sort((a, b) => Number(a) - Number(b))
        .map((key) => record[key])
        .filter((item) => item !== null && item !== undefined && String(item).trim() !== '');
    }

    for (const key of ['items', 'data', 'values', 'list', 'results']) {
      const nested = normalizeArray(record[key]);
      if (nested.length > 0) return nested;
    }
  }

  return [];
}

function pickArray(record: Record<string, unknown>, keys: string[]): unknown[] {
  for (const key of keys) {
    const arr = normalizeArray(record[key]);
    if (arr.length > 0) return arr;
  }
  return [];
}

function normalizeVehicleTypes(value: unknown): VehicleTypeOption[] {
  const result: VehicleTypeOption[] = [];

  for (const item of normalizeArray(value)) {
    const record = asRecord(item);
    const id = asNumber(record.id, NaN);
    const name = asString(record.name ?? record.title, '');
    if (!Number.isFinite(id) || !name) continue;

    result.push({
      id,
      name,
      slug: typeof record.slug === 'string' ? record.slug : undefined,
      icon: typeof record.icon === 'string' ? record.icon : undefined,
    });
  }

  return result;
}

function normalizeQuestionOption(value: unknown): WizardOptionConfig | undefined {
  const record = asRecord(value);
  const optionValue = asString(record.value ?? record.slug ?? (record.id !== undefined ? String(record.id) : ''), '').trim();
  const title = asString(record.title ?? record.name, '').trim();
  if (!optionValue || !title) return undefined;

  const numericRaw = record.numericValue ?? record.numeric_value;
  const numericValue = numericRaw !== undefined && numericRaw !== null ? asNumber(numericRaw, NaN) : NaN;

  return {
    value: optionValue,
    title,
    text: asString(record.text ?? record.description, '').trim() || undefined,
    icon: asString(record.icon, '').trim() || undefined,
    imageUrl: asString(record.imageUrl ?? record.image_url, '').trim() || undefined,
    numericValue: Number.isFinite(numericValue) ? numericValue : undefined,
    id: typeof record.id === 'number' ? record.id : undefined,
    optionId: typeof record.optionId === 'number' ? record.optionId : undefined,
  };
}

function normalizeQuestion(value: unknown): WizardQuestionConfig | undefined {
  const record = asRecord(value);
  const answerKey = asString(record.answerKey ?? record.answer_key, '').trim();
  const title = asString(record.title, '').trim();
  if (!answerKey || !title) return undefined;

  const questionType = asString(record.questionType ?? record.question_type, 'generic') as WizardQuestionType;
  const fieldTypeRaw = asString(record.fieldType ?? record.field_type, '').trim();
  const options = normalizeArray(record.options)
    .map(normalizeQuestionOption)
    .filter((option): option is WizardOptionConfig => Boolean(option));

  return {
    id: typeof record.id === 'number' ? record.id : undefined,
    answerKey,
    title,
    helpText: asString(record.helpText ?? record.help_text, '').trim() || null,
    questionType,
    multiSelect: asBool(record.multiSelect ?? record.multi_select, false),
    fieldType: fieldTypeRaw ? (fieldTypeRaw as WizardFieldType) : null,
    required: asBool(record.required, false),
    dependsOnAnswerKey: asString(record.dependsOnAnswerKey ?? record.depends_on_answer_key, '').trim() || null,
    dependsOnValue: asString(record.dependsOnValue ?? record.depends_on_value, '').trim() || null,
    options,
  };
}

function normalizeSteps(value: unknown): WizardStepConfig[] {
  const result: WizardStepConfig[] = [];

  for (const item of normalizeArray(value)) {
    const record = asRecord(item);
    const key = asString(record.key ?? record.stepKey ?? record.step_key, '').trim();
    const label = asString(record.label ?? record.title, '').trim();
    if (!key || !label) continue;

    result.push({
      id: typeof record.id === 'number' ? record.id : undefined,
      key,
      label,
      type: asString(record.type, 'questions') as WizardStepType,
      questions: normalizeArray(record.questions)
        .map(normalizeQuestion)
        .filter((question): question is WizardQuestionConfig => Boolean(question)),
    });
  }

  return result;
}

export async function fetchConfig(apiBaseUrl: string, clientId: string): Promise<WizardConfig> {
  if (!apiBaseUrl) {
    const error = new Error('No apiBaseUrl configured') as ApiError;
    error.status = 0;
    throw error;
  }

  const url = `${normalizeBaseUrl(apiBaseUrl)}/api/tfd-partner-wizard/config/${encodeURIComponent(clientId)}`;
  const response = await fetch(url, {
    method: 'GET',
    credentials: 'omit',
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw await readError(response, 'Config request');
  }

  const json = await response.json();
  const data = unwrapApiData(json);

  const intro = asString(data.intro ?? data.introText ?? data.description, 'Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.');

  return {
    clientId: asString(data.clientId ?? data.client_id ?? clientId, clientId),
    name: asString(data.name, ''),
    headline: asString(data.headline ?? data.title, 'Chiptuning Händler werden'),
    intro,
    introText: intro,
    primaryColor: asString(data.primaryColor ?? data.primary_color, '#E31E24'),
    theme: asString(data.theme, 'light'),
    toolRequired: asBool(data.toolRequired ?? data.tool_required, true),
    defaultVehiclePrice: asNumber(data.defaultVehiclePrice ?? data.default_vehicle_price, 0),
    enabledVehicleCategories: normalizeVehicleTypes(
      pickArray(data, ['enabledVehicleCategories', 'enabled_vehicle_categories', 'vehicleCategories', 'vehicle_categories', 'vehicles']),
    ),
    enabledServices: pickArray(data, ['enabledServices', 'enabled_services', 'services']),
    currentToolOptions: pickArray(data, ['currentToolOptions', 'current_tool_options', 'toolOptions', 'tool_options', 'currentTools', 'current_tools']),
    customLabels: data.customLabels ?? data.custom_labels ?? {},
    labels: normalizeLabels(data.labels),
    privacyNotice: asString(
      data.privacyNotice ?? data.privacy_notice,
      'Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung der Anfrage gespeichert werden und wir dich kontaktieren.',
    ),
    toolDisclaimer: asString(data.toolDisclaimer ?? data.tool_disclaimer, ''),
    ctaLabel: asString(data.ctaLabel ?? data.cta_label, 'Anfrage senden'),
    contactTarget: asString(data.contactTarget ?? data.contact_target, ''),
    tools: pickArray(data, ['tools', 'toolRecommendations', 'tool_recommendations', 'recommendedTools']),
    packages: pickArray(data, ['packages', 'partnerPackages', 'partner_packages']),
    steps: normalizeSteps(data.steps),
    appointment: normalizeAppointmentConfig(data.appointment),
    raw: json,
  };
}

function normalizeLabels(value: unknown): Record<string, string> {
  const result: Record<string, string> = {};
  for (const [key, text] of Object.entries(asRecord(value))) {
    if (typeof text === 'string') result[key] = text;
  }
  return result;
}

/** Speichert Text-Änderungen (nur Admin-Session + CSRF, Bearbeitungsmodus der Vorschau). */
async function adminRequest(apiBaseUrl: string, path: string, method: string, csrfToken: string, body?: unknown): Promise<Record<string, unknown>> {
  const response = await fetch(`${normalizeBaseUrl(apiBaseUrl)}/api/tfd-partner-wizard/admin/${path}`, {
    method,
    credentials: 'same-origin',
    // Abgelaufene Session leitet zum Login um – das darf nicht als Erfolg gelten.
    redirect: 'manual',
    headers: {
      Accept: 'application/json',
      ...(body !== undefined ? { 'Content-Type': 'application/json' } : {}),
      'X-CSRF-Token': csrfToken,
    },
    body: body !== undefined ? JSON.stringify(body) : undefined,
  });

  if (response.type === 'opaqueredirect' || response.status === 0) {
    throw new Error('Speichern fehlgeschlagen: Sitzung abgelaufen. Bitte im Admin neu einloggen und die Seite neu laden.');
  }

  const json = await response.json().catch(() => null) as { ok?: boolean; error?: string; errors?: (string | { error: string })[] } | null;
  if (!response.ok || !json?.ok) {
    let message = `Fehlgeschlagen (${response.status})`;
    if (json?.errors?.length) {
      message += `: ${json.errors.map((item) => (typeof item === 'string' ? item : item.error)).join(' · ')}`;
    } else if (json?.error) {
      message += `: ${json.error}`;
    } else if (response.ok) {
      message = 'Fehlgeschlagen: unerwartete Antwort vom Server.';
    }
    const error = new Error(message) as ApiError;
    error.status = response.status;
    throw error;
  }

  return json as Record<string, unknown>;
}

export async function saveTexts(apiBaseUrl: string, clientId: string, csrfToken: string, changes: TextChange[]): Promise<void> {
  await adminRequest(apiBaseUrl, `texts/${encodeURIComponent(clientId)}`, 'PATCH', csrfToken, { changes });
}

export type NewQuestionInput = {
  stepId: number;
  answerKey: string;
  title: string;
  helpText?: string | null;
  questionType?: string;
  fieldType?: string | null;
  multiSelect?: boolean;
  required?: boolean;
  options?: { value: string; title: string; text?: string | null }[];
};

export async function createQuestion(apiBaseUrl: string, clientId: string, csrfToken: string, input: NewQuestionInput): Promise<void> {
  await adminRequest(apiBaseUrl, `questions/${encodeURIComponent(clientId)}`, 'POST', csrfToken, input);
}

export async function deleteQuestion(apiBaseUrl: string, clientId: string, csrfToken: string, questionId: number): Promise<void> {
  await adminRequest(apiBaseUrl, `questions/${encodeURIComponent(clientId)}/${questionId}`, 'DELETE', csrfToken);
}

export async function createOption(apiBaseUrl: string, clientId: string, csrfToken: string, questionId: number, input: { value: string; title: string; text?: string | null }): Promise<void> {
  await adminRequest(apiBaseUrl, `questions/${encodeURIComponent(clientId)}/${questionId}/options`, 'POST', csrfToken, input);
}

export async function deleteOption(apiBaseUrl: string, clientId: string, csrfToken: string, optionId: number): Promise<void> {
  await adminRequest(apiBaseUrl, `options/${encodeURIComponent(clientId)}/${optionId}`, 'DELETE', csrfToken);
}

function normalizeAppointmentConfig(value: unknown): WizardConfig['appointment'] {
  const record = asRecord(value);
  const enabled = asBool(record.enabled, false);

  return {
    enabled,
    required: enabled && asBool(record.required, false),
    title: asString(record.title, 'Beratungstermin'),
    durationMinutes: asNumber(record.durationMinutes, 30),
    timezone: asString(record.timezone, 'Europe/Berlin'),
  };
}

export async function fetchAppointmentDays(apiBaseUrl: string, clientId: string): Promise<AppointmentDay[]> {
  if (!apiBaseUrl) {
    return [];
  }

  const url = `${normalizeBaseUrl(apiBaseUrl)}/api/tfd-partner-wizard/appointments/${encodeURIComponent(clientId)}`;
  const response = await fetch(url, {
    method: 'GET',
    credentials: 'omit',
    cache: 'no-store',
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw await readError(response, 'Appointment request');
  }

  const data = asRecord(await response.json());

  return normalizeArray(data.days)
    .map((day): AppointmentDay => {
      const record = asRecord(day);
      return {
        date: asString(record.date),
        weekday: asNumber(record.weekday),
        slots: normalizeArray(record.slots)
          .map((slot): AppointmentSlot => {
            const slotRecord = asRecord(slot);
            return {
              start: asString(slotRecord.start),
              end: asString(slotRecord.end),
              time: asString(slotRecord.time),
            };
          })
          .filter((slot) => slot.start !== ''),
      };
    })
    .filter((day) => day.date !== '' && day.slots.length > 0);
}

export async function fetchRecommendation(apiBaseUrl: string, payload: unknown): Promise<WizardRecommendation> {
  if (!apiBaseUrl) {
    throw new Error('No apiBaseUrl configured');
  }

  const url = `${normalizeBaseUrl(apiBaseUrl)}/api/tfd-partner-wizard/recommend`;
  const response = await fetch(url, {
    method: 'POST',
    credentials: 'omit',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });

  if (!response.ok) {
    throw await readError(response, 'Recommendation request');
  }

  const data = await response.json();
  return normalizeRecommendation(data);
}

export async function submitLead(apiBaseUrl: string, payload: unknown): Promise<unknown> {
  if (!apiBaseUrl) {
    throw new Error('No apiBaseUrl configured');
  }

  const url = `${normalizeBaseUrl(apiBaseUrl)}/api/tfd-partner-wizard/leads`;
  const response = await fetch(url, {
    method: 'POST',
    credentials: 'omit',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });

  if (!response.ok) {
    throw await readError(response, 'Lead request');
  }

  try {
    return await response.json();
  } catch {
    return null;
  }
}

function normalizeStringArray(value: unknown): string[] {
  return normalizeArray(value)
    .map((item) => {
      if (typeof item === 'string') return item.trim();
      const record = asRecord(item);
      return asString(record.name ?? record.title ?? record.label ?? record.text ?? record.description).trim();
    })
    .filter(Boolean);
}

function asToolProduct(value: unknown): ToolProduct | undefined {
  const record = asRecord(value);
  const title = asString(record.title).trim();
  if (!title) return undefined;

  return {
    uid: typeof record.uid === 'number' ? record.uid : undefined,
    title,
    sku: typeof record.sku === 'string' ? record.sku : undefined,
    price: record.price !== undefined && record.price !== null ? String(record.price) : undefined,
    specialPrice: record.specialPrice !== undefined && record.specialPrice !== null ? String(record.specialPrice) : undefined,
    slug: typeof record.slug === 'string' ? record.slug : undefined,
    imageUrl: asString(record.imageUrl ?? record.image_url).trim() || undefined,
  };
}

function normalizeProductArray(value: unknown): ToolProduct[] {
  const result: ToolProduct[] = [];

  normalizeArray(value).forEach((item) => {
    const product = asToolProduct(item);
    if (product) result.push(product);
  });

  return result;
}

function normalizeToolArray(value: unknown): RecommendedTool[] {
  const result: RecommendedTool[] = [];

  normalizeArray(value).forEach((item, index) => {
    if (typeof item === 'string') {
      const name = item.trim();
      if (name) result.push({ key: `tool-${index}`, name });
      return;
    }

    const record = asRecord(item);
    const name = asString(record.name ?? record.title ?? record.label).trim();
    if (!name) return;
    const product = asToolProduct(record.product);
    const imageUrl = asString(record.imageUrl ?? record.image_url).trim() || product?.imageUrl;

    result.push({
      key: asString(record.key ?? record.toolKey ?? `tool-${index}`),
      name,
      description: asString(record.description ?? record.text, '').trim() || undefined,
      level: typeof record.level === 'string' ? record.level : undefined,
      imageUrl,
      product,
      fromCombination: record.fromCombination === true,
    });
  });

  return result;
}

export function normalizeRecommendation(data: unknown): WizardRecommendation {
  const root = asRecord(data);
  const recommendation = asRecord(root.recommendation);
  const result = asRecord(root.result);
  const source = Object.keys(recommendation).length > 0
    ? recommendation
    : (Object.keys(result).length > 0 ? result : root);
  const packageData = asRecord(source.package ?? source.recommendedPackage ?? source.packageData ?? source.recommended_package);

  const packageName =
    source.packageName ??
    source.packageTitle ??
    source.package_title ??
    source.recommendedPackageTitle ??
    source.recommended_package_title ??
    source.title ??
    source.name ??
    packageData.name ??
    packageData.title ??
    packageData.packageTitle ??
    'Partner Empfehlung';

  const packageKey =
    source.packageKey ??
    source.package_key ??
    source.key ??
    source.slug ??
    packageData.key ??
    packageData.slug ??
    asString(packageName).toLowerCase().replace(/[^a-z0-9]+/gi, '-');

  const bullets = normalizeStringArray(
    source.bullets ??
      source.items ??
      source.benefits ??
      source.packageFeatures ??
      source.package_features ??
      source.features ??
      packageData.bullets ??
      packageData.benefits ??
      packageData.features ??
      [],
  );

  const recommendedTools = normalizeToolArray(
    source.recommendedTools ??
      source.recommended_tools ??
      source.tools ??
      source.toolRecommendations ??
      source.tool_recommendations ??
      [],
  );

  const recommendedProducts = normalizeProductArray(
    source.recommendedProducts ?? source.recommended_products ?? [],
  );

  const rawStatus = asString(source.status ?? source.recommendationStatus ?? source.recommendation_status, 'ready');
  const status = rawStatus === 'tool_required' || source.toolRequired === true ? 'tool_required' : 'ready';

  return {
    status,
    packageKey: asString(packageKey),
    packageName: asString(packageName),
    leadStatus: status === 'tool_required' ? 'tool_consulting' : 'new_partner_lead',
    cta: asString(
      source.cta ?? source.ctaLabel ?? source.cta_label ?? source.nextStep ?? source.next_step ?? packageData.ctaLabel,
      status === 'tool_required' ? 'Tool-Beratung anfragen' : 'Anfrage senden',
    ),
    summary: asString(
      source.summary ?? source.packageText ?? source.package_text ?? source.shortDescription ?? source.description ?? packageData.shortDescription ?? packageData.description,
      'Auf Basis deiner Angaben bereiten wir die passende Empfehlung vor.',
    ),
    bullets,
    recommendedTools,
    recommendedProducts,
    toolDirection: asString(source.toolDirection ?? source.tool_direction, ''),
    disclaimer: asString(source.disclaimer ?? source.toolDisclaimer ?? source.tool_disclaimer, ''),
    raw: data,
  };
}
