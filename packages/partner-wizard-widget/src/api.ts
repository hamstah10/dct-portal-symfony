import type { RecommendedTool, ToolProduct, VehicleTypeOption, WizardConfig, WizardRecommendation } from './types';

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
    privacyNotice: asString(
      data.privacyNotice ?? data.privacy_notice,
      'Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung der Anfrage gespeichert werden und wir dich kontaktieren.',
    ),
    toolDisclaimer: asString(data.toolDisclaimer ?? data.tool_disclaimer, ''),
    ctaLabel: asString(data.ctaLabel ?? data.cta_label, 'Anfrage senden'),
    contactTarget: asString(data.contactTarget ?? data.contact_target, ''),
    tools: pickArray(data, ['tools', 'toolRecommendations', 'tool_recommendations', 'recommendedTools']),
    packages: pickArray(data, ['packages', 'partnerPackages', 'partner_packages']),
    raw: json,
  };
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
    slug: typeof record.slug === 'string' ? record.slug : undefined,
    imageUrl: asString(record.imageUrl ?? record.image_url).trim() || undefined,
  };
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
    toolDirection: asString(source.toolDirection ?? source.tool_direction, ''),
    disclaimer: asString(source.disclaimer ?? source.toolDisclaimer ?? source.tool_disclaimer, ''),
    raw: data,
  };
}
