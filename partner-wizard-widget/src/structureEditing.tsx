import React, { useEffect, useRef, useState } from 'react';
import type { WizardStepType } from './types';

/**
 * Bearbeitungsmodus: Fragen und Antwortoptionen hinzufügen.
 * Löschen läuft direkt über window.confirm im Wizard.
 */

const UMLAUTS: Record<string, string> = { ä: 'ae', ö: 'oe', ü: 'ue', ß: 'ss', Ä: 'Ae', Ö: 'Oe', Ü: 'Ue' };

function asciiWords(text: string): string[] {
  return text
    .replace(/[äöüßÄÖÜ]/g, (char) => UMLAUTS[char] ?? char)
    .normalize('NFKD')
    .replace(/[̀-ͯ]/g, '')
    .split(/[^A-Za-z0-9]+/)
    .filter(Boolean);
}

function makeUnique(base: string, taken: Set<string>, maxLength: number): string {
  let candidate = base.slice(0, maxLength);
  for (let index = 2; taken.has(candidate); index += 1) {
    const suffix = `_${index}`;
    candidate = base.slice(0, maxLength - suffix.length) + suffix;
  }
  return candidate;
}

/** answerKey aus dem Fragetitel: camelCase, beginnt mit Buchstabe, max. 40 Zeichen, eindeutig. */
export function makeAnswerKey(title: string, taken: Set<string>): string {
  const words = asciiWords(title).slice(0, 5).map((word) => word.toLowerCase());
  let base = words.map((word, index) => (index === 0 ? word : word.charAt(0).toUpperCase() + word.slice(1))).join('');
  if (!/^[a-z]/i.test(base)) base = `frage${base}`;
  return makeUnique(base, taken, 40);
}

/** Options-Wert aus dem Titel: klein, mit "-", max. 80 Zeichen, eindeutig innerhalb der Frage. */
export function makeOptionValue(title: string, taken: Set<string>): string {
  const base = asciiWords(title).join('-').toLowerCase() || 'option';
  return makeUnique(base, taken, 80);
}

export type QuestionKind = {
  id: string;
  label: string;
  questionType: 'generic';
  fieldType: string | null;
  multiSelect: boolean;
  withOptions: boolean;
};

const QUESTION_STEP_KINDS: QuestionKind[] = [
  { id: 'single', label: 'Auswahl – eine Antwort', questionType: 'generic', fieldType: null, multiSelect: false, withOptions: true },
  { id: 'multi', label: 'Auswahl – mehrere Antworten', questionType: 'generic', fieldType: null, multiSelect: true, withOptions: true },
];

const CONTACT_STEP_KINDS: QuestionKind[] = [
  { id: 'text', label: 'Textfeld (einzeilig)', questionType: 'generic', fieldType: 'text', multiSelect: false, withOptions: false },
  { id: 'textarea', label: 'Textfeld (mehrzeilig)', questionType: 'generic', fieldType: 'textarea', multiSelect: false, withOptions: false },
  { id: 'email', label: 'E-Mail', questionType: 'generic', fieldType: 'email', multiSelect: false, withOptions: false },
  { id: 'select', label: 'Auswahlliste (Dropdown)', questionType: 'generic', fieldType: 'select', multiSelect: false, withOptions: true },
  { id: 'radio', label: 'Auswahl – eine Antwort', questionType: 'generic', fieldType: 'radio', multiSelect: false, withOptions: true },
  { id: 'checkboxes', label: 'Auswahl – mehrere Antworten', questionType: 'generic', fieldType: 'checkbox', multiSelect: true, withOptions: true },
  { id: 'consent', label: 'Einzelne Checkbox (z. B. Zustimmung)', questionType: 'generic', fieldType: 'checkbox', multiSelect: false, withOptions: false },
];

export type NewQuestionDraft = {
  title: string;
  helpText: string | null;
  kind: QuestionKind;
  required: boolean;
  optionTitles: string[];
};

export function NewQuestionDialog({
  stepType,
  stepLabel,
  busy,
  onSubmit,
  onClose,
}: {
  stepType: WizardStepType;
  stepLabel: string;
  busy: boolean;
  onSubmit: (draft: NewQuestionDraft) => void;
  onClose: () => void;
}) {
  const kinds = stepType === 'contact' ? CONTACT_STEP_KINDS : QUESTION_STEP_KINDS;
  const [title, setTitle] = useState('');
  const [helpText, setHelpText] = useState('');
  const [kindId, setKindId] = useState(kinds[0].id);
  const [required, setRequired] = useState(false);
  const [optionsText, setOptionsText] = useState('');
  const titleRef = useRef<HTMLInputElement | null>(null);
  const kind = kinds.find((item) => item.id === kindId) ?? kinds[0];
  const optionTitles = optionsText.split('\n').map((line) => line.trim()).filter(Boolean);
  const canSubmit = !busy && title.trim() !== '' && (!kind.withOptions || optionTitles.length > 0);

  useEffect(() => {
    titleRef.current?.focus();
  }, []);

  const submit = () => {
    if (!canSubmit) return;
    onSubmit({
      title: title.trim(),
      helpText: helpText.trim() || null,
      kind,
      required,
      optionTitles: kind.withOptions ? optionTitles : [],
    });
  };

  return (
    <div className="tfdEdit__backdrop" role="presentation" onClick={busy ? undefined : onClose}>
      <div className="tfdEdit__dialog" role="dialog" aria-modal="true" aria-label="Frage hinzufügen" onClick={(event) => event.stopPropagation()}>
        <span className="tfdEdit__dialogTitle">{stepType === 'contact' ? 'Neues Kontaktfeld' : 'Neue Frage'} · {stepLabel}</span>
        <label className="tfdEdit__field">
          <small>{stepType === 'contact' ? 'Bezeichnung' : 'Frage'}</small>
          <input ref={titleRef} type="text" value={title} maxLength={180} onChange={(event) => setTitle(event.target.value)} />
        </label>
        {stepType !== 'contact' && (
          <label className="tfdEdit__field">
            <small>Hilfetext (optional)</small>
            <input type="text" value={helpText} onChange={(event) => setHelpText(event.target.value)} />
          </label>
        )}
        <label className="tfdEdit__field">
          <small>Art</small>
          <select value={kindId} onChange={(event) => setKindId(event.target.value)}>
            {kinds.map((item) => <option key={item.id} value={item.id}>{item.label}</option>)}
          </select>
        </label>
        {kind.withOptions && (
          <label className="tfdEdit__field">
            <small>Antworten – eine pro Zeile</small>
            <textarea rows={5} value={optionsText} onChange={(event) => setOptionsText(event.target.value)} placeholder={'Antwort 1\nAntwort 2'} />
          </label>
        )}
        <label className="tfdEdit__check">
          <input type="checkbox" checked={required} onChange={(event) => setRequired(event.target.checked)} />
          <span>Pflichtfeld</span>
        </label>
        <div className="tfdEdit__dialogActions">
          <span />
          <button type="button" onClick={onClose} disabled={busy}>Abbrechen</button>
          <button type="button" className="tfdEdit__primary" onClick={submit} disabled={!canSubmit}>
            {busy ? 'Speichert…' : 'Hinzufügen'}
          </button>
        </div>
      </div>
    </div>
  );
}

export function NewOptionDialog({
  questionTitle,
  withText,
  busy,
  onSubmit,
  onClose,
}: {
  questionTitle: string;
  withText: boolean;
  busy: boolean;
  onSubmit: (input: { title: string; text: string | null }) => void;
  onClose: () => void;
}) {
  const [title, setTitle] = useState('');
  const [text, setText] = useState('');
  const titleRef = useRef<HTMLInputElement | null>(null);
  const canSubmit = !busy && title.trim() !== '';

  useEffect(() => {
    titleRef.current?.focus();
  }, []);

  const submit = () => {
    if (canSubmit) onSubmit({ title: title.trim(), text: text.trim() || null });
  };

  return (
    <div className="tfdEdit__backdrop" role="presentation" onClick={busy ? undefined : onClose}>
      <div className="tfdEdit__dialog" role="dialog" aria-modal="true" aria-label="Antwort hinzufügen" onClick={(event) => event.stopPropagation()}>
        <span className="tfdEdit__dialogTitle">Neue Antwort · {questionTitle}</span>
        <label className="tfdEdit__field">
          <small>Titel</small>
          <input
            ref={titleRef}
            type="text"
            value={title}
            maxLength={140}
            onChange={(event) => setTitle(event.target.value)}
            onKeyDown={(event) => { if (event.key === 'Enter') { event.preventDefault(); submit(); } }}
          />
        </label>
        {withText && (
          <label className="tfdEdit__field">
            <small>Beschreibung (optional)</small>
            <textarea rows={3} value={text} onChange={(event) => setText(event.target.value)} />
          </label>
        )}
        <div className="tfdEdit__dialogActions">
          <span />
          <button type="button" onClick={onClose} disabled={busy}>Abbrechen</button>
          <button type="button" className="tfdEdit__primary" onClick={submit} disabled={!canSubmit}>
            {busy ? 'Speichert…' : 'Hinzufügen'}
          </button>
        </div>
      </div>
    </div>
  );
}

/** Kleiner Lösch-Knopf; sitzt teils in <button>-Karten, daher selbst kein <button>. */
export function DeleteMark({ label, onDelete }: { label: string; onDelete: () => void }) {
  const trigger = (event: React.SyntheticEvent) => {
    event.preventDefault();
    event.stopPropagation();
    onDelete();
  };

  return (
    <span
      className="tfdEdit__delete"
      role="button"
      tabIndex={0}
      title={label}
      aria-label={label}
      onClick={trigger}
      onKeyDown={(event) => { if (event.key === 'Enter' || event.key === ' ') trigger(event); }}
    >
      ×
    </span>
  );
}
