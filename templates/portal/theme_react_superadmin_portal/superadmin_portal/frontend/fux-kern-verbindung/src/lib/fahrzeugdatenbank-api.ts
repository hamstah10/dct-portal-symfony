// Fahrzeugdatenbank API service
// Uses the same-origin Symfony proxy under /vehicle-api

const API_BASE = '/vehicle-api';

const headers: Record<string, string> = {
  'Content-Type': 'application/json',
};

export interface VehicleType {
  id: number;
  name: string;
  image?: string | null;
}

export interface VehicleBrand {
  id: number;
  name: string;
  image?: string | null;
  showInTopMenu?: boolean;
}

export interface PerformanceBrand {
  id: number;
  name: string;
  logo: string | null;
}

export interface VehicleSeries {
  id: number;
  name: string;
  performanceBrand?: PerformanceBrand | null;
}

export interface VehicleModel {
  id: number;
  name: string;
}

export interface VehicleEngine {
  id: number;
  name: string;
  fuel?: string | null;
  performanceBrand?: PerformanceBrand | null;
}

export interface EngineDetails {
  editingAllowed?: boolean;
  engine: Record<string, unknown>;
  brand: Record<string, unknown>;
  series: Record<string, unknown>;
  model?: Record<string, unknown>;
  stages: unknown[];
  ecus: unknown[];
  engineOptions?: unknown[];
  gearboxes: unknown[];
  einbauHinweise?: unknown[];
  editOptions?: Record<string, unknown>;
  settings: Record<string, unknown>;
  showPrices?: boolean;
  showEcoStage?: boolean;
}

async function apiFetch<T>(path: string): Promise<T> {
  const res = await fetch(`${API_BASE}${path}`, { headers });
  if (!res.ok) {
    throw new Error(`API error ${res.status}: ${res.statusText}`);
  }
  const json = await res.json();
  // API wraps responses in { data: ... }
  return json.data !== undefined ? json.data : json;
}

async function apiPost<T>(path: string, payload: Record<string, unknown>): Promise<T> {
  const res = await fetch(`${API_BASE}${path}`, {
    method: 'POST',
    headers,
    body: JSON.stringify(payload),
  });

  if (!res.ok) {
    throw new Error(`API error ${res.status}: ${res.statusText}`);
  }

  const json = await res.json();
  return json.data !== undefined ? json.data : json;
}

export function isConfigured(): boolean {
  return true;
}

export async function fetchTypes(): Promise<VehicleType[]> {
  return apiFetch<VehicleType[]>('/types');
}

export async function fetchBrands(typeId: number): Promise<VehicleBrand[]> {
  return apiFetch<VehicleBrand[]>(`/brands/${typeId}`);
}

export async function fetchSeries(brandId: number): Promise<VehicleSeries[]> {
  return apiFetch<VehicleSeries[]>(`/series/${brandId}`);
}

export async function fetchModels(seriesId: number): Promise<VehicleModel[]> {
  return apiFetch<VehicleModel[]>(`/models/${seriesId}`);
}

export async function fetchEngines(modelId: number): Promise<VehicleEngine[]> {
  return apiFetch<VehicleEngine[]>(`/engines/${modelId}`);
}

export async function fetchEngineDetails(engineId: number, stageId?: number, templateVariant?: string): Promise<EngineDetails> {
  const params = new URLSearchParams();
  if (stageId !== undefined) params.set('stageId', String(stageId));
  if (templateVariant) params.set('templateVariant', templateVariant);
  const qs = params.toString();
  return apiFetch<EngineDetails>(`/engine-details/${engineId}${qs ? `?${qs}` : ''}`);
}

export async function trackEvent(data: Record<string, unknown>): Promise<void> {
  await fetch(`${API_BASE}/track`, {
    method: 'POST',
    headers,
    body: JSON.stringify(data),
  });
}

export type EditableStagePayload = {
  id?: number;
  engineId: number;
  copyFromStageId?: number;
  name: string;
  description?: string | null;
  price?: string | null;
  hp?: number | null;
  nm?: number | null;
  ecu?: number | null;
  slug?: string | null;
  originalPs?: number | null;
  originalNm?: number | null;
  rpmStart?: number | null;
  rpmEnd?: number | null;
  engineType?: string | null;
  active?: boolean;
};

export type EditableSeriesPayload = {
  id: number;
  name: string;
  description?: string | null;
  slug?: string | null;
  active?: boolean;
  sorting?: number | null;
};

export type EditableModelPayload = {
  id: number;
  name: string;
  description?: string | null;
  slug?: string | null;
  active?: boolean;
  sorting?: number | null;
};

export type EditableEnginePayload = {
  id: number;
  name: string;
  description?: string | null;
  slug?: string | null;
  active?: boolean;
  sorting?: number | null;
  horsepower?: number | null;
  torque?: number | null;
  ecus?: number | null;
  fuel?: string | null;
  displacement?: string | null;
};

export type EditableOptionPayload = {
  id: number;
  name: string;
  description?: string | null;
  tooltipText?: string | null;
  slug?: string | null;
  price?: string | null;
  icon?: string | null;
  active?: boolean;
  isMainOption?: boolean;
};

export type EditableEcuPayload = {
  id: number;
  name: string;
  description?: string | null;
  type?: string | null;
  controlUnitKind?: string | null;
  image?: string | null;
  active?: boolean;
  manufacturerId?: number | null;
  toolManufacturerId?: number | null;
};

export type EditableGearboxPayload = {
  id: number;
  title: string;
  description?: string | null;
  icon?: string | null;
  active?: boolean;
  sorting?: number | null;
  gearboxTypeId?: number | null;
  manufacturerId?: number | null;
};

export type EditableHintPayload = {
  id: number;
  title: string;
  description?: string | null;
  active?: boolean;
  ecuOptionMasterId?: number | null;
  activeEcuId?: number | null;
  imageIds?: number[] | null;
};

export type EditableToolEntryPayload = {
  id: number;
  kind: 'ecu' | 'gearbox';
  position?: number | null;
  toolManufacturerId?: number | null;
  methodIds?: number[] | null;
};

export async function saveStage(payload: EditableStagePayload): Promise<Record<string, unknown>> {
  return apiPost<Record<string, unknown>>('/admin/stages/save', payload);
}

export async function deactivateStage(id: number): Promise<Record<string, unknown>> {
  return apiPost<Record<string, unknown>>(`/admin/stages/${id}/inactive`, {});
}

export async function saveSeries(payload: EditableSeriesPayload): Promise<Record<string, unknown>> {
  return apiPost<Record<string, unknown>>('/admin/series/save', payload);
}

export async function saveModel(payload: EditableModelPayload): Promise<Record<string, unknown>> {
  return apiPost<Record<string, unknown>>('/admin/models/save', payload);
}

export async function saveEngine(payload: EditableEnginePayload): Promise<Record<string, unknown>> {
  return apiPost<Record<string, unknown>>('/admin/engines/save', payload);
}

export async function saveOption(payload: EditableOptionPayload): Promise<Record<string, unknown>> {
  return apiPost<Record<string, unknown>>('/admin/options/save', payload);
}

export async function saveEcu(payload: EditableEcuPayload): Promise<Record<string, unknown>> {
  return apiPost<Record<string, unknown>>('/admin/ecus/save', payload);
}

export async function saveGearbox(payload: EditableGearboxPayload): Promise<Record<string, unknown>> {
  return apiPost<Record<string, unknown>>('/admin/gearboxes/save', payload);
}

export async function saveHint(payload: EditableHintPayload): Promise<Record<string, unknown>> {
  return apiPost<Record<string, unknown>>('/admin/hints/save', payload);
}

export async function saveToolEntry(payload: EditableToolEntryPayload): Promise<Record<string, unknown>> {
  return apiPost<Record<string, unknown>>('/admin/tool-entries/save', payload);
}
