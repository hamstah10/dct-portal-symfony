import type { SelectOption, WizardConfig } from './types';

export const fallbackConfig: WizardConfig = {
  clientId: 'no1',
  headline: 'Chiptuning Händler werden',
  intro: 'Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.',
  privacyNotice:
    'Mit dem Absenden erklärst du dich einverstanden, dass deine Angaben zur Bearbeitung deiner Anfrage gespeichert werden.',
  ctaLabel: 'Anfrage senden',
};

export const goalOptions: SelectOption[] = [
  {
    value: 'start',
    title: 'Neu starten',
    text: 'Ich möchte in das Thema Chiptuning einsteigen.',
  },
  {
    value: 'workshop',
    title: 'Werkstatt erweitern',
    text: 'Ich habe Kundenkontakt und möchte Chiptuning anbieten.',
  },
  {
    value: 'active',
    title: 'Aktiver Tuner',
    text: 'Ich möchte regelmäßig Fahrzeuge bearbeiten.',
  },
  {
    value: 'network',
    title: 'Händlernetz / Master',
    text: 'Ich denke an Master, Slaves oder White-Label.',
  },
];

export const experienceOptions: SelectOption[] = [
  {
    value: 'none',
    title: 'Keine Erfahrung',
    text: 'Ich brauche einen geführten Einstieg.',
  },
  {
    value: 'diagnosis',
    title: 'Diagnose / Codierung',
    text: 'Ich arbeite schon am Fahrzeug.',
  },
  {
    value: 'workshop',
    title: 'Werkstatt-Erfahrung',
    text: 'Ich habe regelmäßig Kundenfahrzeuge.',
  },
  {
    value: 'tuning',
    title: 'Chiptuning-Erfahrung',
    text: 'Ich lese/schreibe bereits Steuergeräte.',
  },
];

/**
 * Mirrors the 7 real App\Entity\VehicleType rows (id/slug fixed in the vehicle database).
 * Used only when the backend /config endpoint is unreachable; ids allow the fallback
 * payload to still carry real VehicleType references instead of free-text keys.
 */
export const vehicleOptions: SelectOption[] = [
  { value: 'pkw', title: 'PKW', text: 'Standard-Fahrzeuge, Performance und Daily Driver.' },
  { value: 'lkw', title: 'LKW', text: 'Nutzfahrzeuge, Flotten und Speditionen.' },
  { value: 'motorrad', title: 'Motorrad', text: 'Bike-Tuning und Performance-Anwendungen.' },
  { value: 'agrar', title: 'Agrar', text: 'Traktoren, Landmaschinen und Eco-Anwendungen.' },
  { value: 'atv', title: 'Quad', text: 'ATV, Quad und Side-by-Side.' },
  { value: 'boat', title: 'Boot', text: 'Boote und Marine-Anwendungen.' },
  { value: 'jetski', title: 'Jetski', text: 'JetSki und Wassersport-Fahrzeuge.' },
];

export const toolOptions: SelectOption[] = [
  {
    value: 'none',
    title: 'Noch kein Tool',
    text: 'Ich brauche zuerst eine Tool-Beratung.',
  },
  {
    value: 'autotuner',
    title: 'Autotuner',
    text: 'Autotuner Master oder Slave vorhanden.',
  },
  {
    value: 'kess3',
    title: 'Alientech KESS3',
    text: 'KESS3 Master oder Slave vorhanden.',
  },
  {
    value: 'flex',
    title: 'Magic Motorsport FLEX',
    text: 'FLEX Tool vorhanden.',
  },
  {
    value: 'cmd',
    title: 'CMD Flash',
    text: 'CMD Tool vorhanden.',
  },
  {
    value: 'dimsport',
    title: 'Dimsport',
    text: 'New Genius / Trasdata oder Dimsport vorhanden.',
  },
  {
    value: 'autoflasher',
    title: 'AutoFlasher',
    text: 'AutoFlasher Tool vorhanden.',
  },
  {
    value: 'other',
    title: 'Anderes Tool',
    text: 'Ich nutze ein anderes Lese-/Schreibtool.',
  },
];

export const serviceOptions: SelectOption[] = [
  { value: 'stage1', title: 'Stage 1', text: 'Leistungsoptimierung für Kundenfahrzeuge.' },
  { value: 'stage2', title: 'Stage 2', text: 'Optimierung mit Hardware-Anpassungen.' },
  { value: 'eco', title: 'Eco-Tuning', text: 'Verbrauchsoptimierung für Flotten und Gewerbe.' },
  { value: 'fileService', title: 'File Service', text: 'Files lesen, prüfen lassen und schreiben.' },
  { value: 'diagnostic', title: 'Diagnose Support', text: 'Technische Rückfragen, Fehlerbilder und Fälle.' },
  { value: 'special', title: 'Sonderoptionen', text: 'VMax, DSG, Start-Stop und weitere Optionen.' },
];

export const websiteOptions: SelectOption[] = [
  {
    value: 'full_package',
    title: 'Website + Konfigurator',
    text: 'Komplette Website mit Website-Modul und Chiptuning-Konfigurator.',
  },
  {
    value: 'website_only',
    title: 'Nur Website',
    text: 'Die Händler-Website ohne Konfigurator.',
  },
  {
    value: 'configurator_only',
    title: 'Nur Chiptuning-Konfigurator',
    text: 'Das Widget für die Fahrzeug- und Leistungsanfrage.',
  },
  {
    value: 'none',
    title: 'Nicht benötigt',
    text: 'Ich brauche dafür aktuell keine Website-Empfehlung.',
  },
];

export const volumeOptions: SelectOption[] = [
  { value: 'low', title: '1–5 Fahrzeuge / Monat', text: 'Kleiner Start oder gelegentliche Anfragen.' },
  { value: 'medium', title: '6–20 Fahrzeuge / Monat', text: 'Regelmäßiges Geschäft mit Wachstum.' },
  { value: 'high', title: '20+ Fahrzeuge / Monat', text: 'Professioneller Betrieb mit Volumen.' },
  { value: 'network', title: 'Händler / Netzwerk', text: 'Mehrere Standorte, Slaves oder Partner.' },
];
