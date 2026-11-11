// Gemeinsame Quelle mit dem Symfony-Bundle (PartnerWizardTextRegistry), damit Schlüssel und
// Standardtexte nie auseinanderlaufen. Die API liefert unter config.labels die fertig
// aufgelösten Texte (Standard + Überschreibungen); diese Datei ist nur der Fallback.
import definitions from '../../tfd-partner-wizard-bundle/src/Resources/config/labels.de.json';

export type LabelDefinition = {
  group: string;
  label: string;
  default: string;
  multiline?: boolean;
};

export const LABEL_DEFINITIONS = definitions as Record<string, LabelDefinition>;

export type LabelKey = keyof typeof definitions;

export const DEFAULT_LABELS: Record<string, string> = Object.fromEntries(
  Object.entries(LABEL_DEFINITIONS).map(([key, definition]) => [key, definition.default]),
);

export function interpolate(template: string, vars?: Record<string, string | number>): string {
  if (!vars) return template;
  return template.replace(/\{(\w+)\}/g, (match, name: string) => (name in vars ? String(vars[name]) : match));
}
