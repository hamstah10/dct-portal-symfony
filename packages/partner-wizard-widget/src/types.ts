export type WizardStepKey = 'entry' | 'vehicleTool' | 'offerVolume' | 'recommendation' | 'contact';

export type SelectOption = {
  value: string;
  title: string;
  text: string;
  id?: number;
  icon?: string;
  imageUrl?: string;
};

export type WizardContact = {
  name?: string;
  company?: string;
  email?: string;
  phone?: string;
  message?: string;
};

export type WizardAnswers = {
  goal?: string;
  experience?: string;
  vehicles?: string[];
  tool?: string;
  services?: string[];
  volume?: string;
  selectedTools?: string[];
  contact?: WizardContact;
  privacyAccepted?: boolean;
};

export type ToolProduct = {
  uid?: number;
  title?: string;
  sku?: string;
  price?: string;
  slug?: string;
  imageUrl?: string;
};

export type VehicleTypeOption = {
  id: number;
  name: string;
  slug?: string;
  icon?: string;
};

export type WizardConfig = {
  clientId: string;
  name?: string;
  headline: string;
  intro: string;
  introText?: string;
  primaryColor?: string;
  theme?: string;
  privacyNotice: string;
  toolDisclaimer?: string;
  toolRequired?: boolean;
  defaultVehiclePrice?: number;
  ctaLabel?: string;
  contactTarget?: string;
  enabledVehicleCategories?: VehicleTypeOption[];
  enabledServices?: unknown[];
  currentToolOptions?: unknown[];
  customLabels?: unknown;
  tools?: unknown[];
  packages?: unknown[];
  raw?: unknown;
};

export type RecommendedTool = {
  key: string;
  name: string;
  description?: string;
  level?: string;
  imageUrl?: string;
  product?: ToolProduct;
  fromCombination?: boolean;
};

export type WizardRecommendation = {
  status: 'tool_required' | 'ready';
  packageKey?: string;
  packageName: string;
  leadStatus: 'tool_consulting' | 'new_partner_lead';
  cta: string;
  summary: string;
  bullets: string[];
  recommendedTools?: RecommendedTool[];
  toolDirection?: string;
  disclaimer?: string;
  raw?: unknown;
};
