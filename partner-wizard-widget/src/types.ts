export type WizardStepKey = string;

export type SelectOption = {
  value: string;
  title: string;
  text: string;
  id?: number;
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
};

export type WizardQuestionConfig = {
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
  steps?: WizardStepConfig[];
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
  recommendedProducts?: ToolProduct[];
  toolDirection?: string;
  disclaimer?: string;
  raw?: unknown;
};
