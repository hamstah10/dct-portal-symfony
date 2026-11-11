export type WizardStepKey = string;

export type SelectOption = {
  value: string;
  title: string;
  text: string;
  id?: number;
  optionId?: number;
  icon?: string;
  imageUrl?: string;
  numericValue?: number;
};

export type WizardQuestionType = 'generic' | 'vehicleType' | 'toolManufacturer' | 'volume';

export type WizardFieldType = 'text' | 'textarea' | 'email' | 'select' | 'checkbox' | 'radio';

export type WizardOptionConfig = {
  value: string;
  title: string;
  text?: string;
  icon?: string;
  imageUrl?: string;
  numericValue?: number;
  id?: number;
  /** ID der PartnerWizardQuestionOption (nur bei eigenen Optionen, für den Text-Editor). */
  optionId?: number;
};

export type WizardQuestionConfig = {
  id?: number;
  answerKey: string;
  title: string;
  helpText?: string | null;
  questionType: WizardQuestionType;
  multiSelect: boolean;
  fieldType?: WizardFieldType | null;
  required?: boolean;
  dependsOnAnswerKey?: string | null;
  dependsOnValue?: string | null;
  options: WizardOptionConfig[];
};

export type WizardStepType = 'questions' | 'recommendation' | 'contact';

export type WizardStepConfig = {
  id?: number;
  key: string;
  label: string;
  type: WizardStepType;
  questions: WizardQuestionConfig[];
};

export type WizardContact = {
  name?: string;
  company?: string;
  email?: string;
  phone?: string;
  message?: string;
  [answerKey: string]: string | string[] | undefined;
};

export type WizardAnswers = {
  goal?: string;
  experience?: string;
  vehicles?: string[];
  tool?: string;
  services?: string[];
  website?: string;
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
  specialPrice?: string;
  slug?: string;
  imageUrl?: string;
};

export type VehicleTypeOption = {
  id: number;
  name: string;
  slug?: string;
  icon?: string;
};

export type WizardAppointmentConfig = {
  enabled: boolean;
  required: boolean;
  title: string;
  durationMinutes: number;
  timezone: string;
};

export type AppointmentSlot = {
  start: string;
  end: string;
  time: string;
};

export type AppointmentDay = {
  date: string;
  weekday: number;
  slots: AppointmentSlot[];
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
  /** Aufgelöste UI-Texte (Standard + Überschreibungen des Clients). */
  labels?: Record<string, string>;
  tools?: unknown[];
  packages?: unknown[];
  steps?: WizardStepConfig[];
  appointment?: WizardAppointmentConfig;
  raw?: unknown;
};

export type EditTarget =
  | { target: 'label'; key: string }
  | { target: 'client'; field: 'headline' | 'introText' | 'privacyNotice' | 'toolDisclaimer' }
  | { target: 'step' | 'question' | 'option' | 'package' | 'tool'; id: number; field: string };

export type TextChange = EditTarget & { value: string | null };

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
  packageText?: string;
  leadStatus: 'tool_consulting' | 'new_partner_lead';
  cta: string;
  summary: string;
  bullets: string[];
  recommendedTools?: RecommendedTool[];
  recommendedProducts?: ToolProduct[];
  toolDirection?: string;
  disclaimer?: string;
  raw?: unknown;
};
