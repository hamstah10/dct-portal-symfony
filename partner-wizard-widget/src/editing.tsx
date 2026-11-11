import React, { createContext, useContext, useEffect, useRef, useState } from 'react';
import { LABEL_DEFINITIONS, interpolate } from './labels';
import type { EditTarget } from './types';

/**
 * Inline-Text-Editor des Partner-Wizards.
 * Außerhalb des Bearbeitungsmodus rendern <Txt>/<Editable> nur den reinen Text.
 */

export type EditRequest = {
  target: EditTarget;
  /** Beschreibung im Editor-Dialog, z. B. "Frage: Titel". */
  title: string;
  /** Aktueller Rohtext (bei Labels mit Platzhaltern wie {date}). */
  current: string;
  /** Standardtext (nur UI-Texte) – ermöglicht „Auf Standard zurücksetzen“. */
  defaultValue?: string;
  multiline?: boolean;
  /** Feld darf leer sein. */
  nullable?: boolean;
};

export type PendingChange = { target: EditTarget; value: string | null };

type EditContextValue = {
  enabled: boolean;
  /** Fertiger Text eines UI-Labels (inkl. ungespeicherter Änderungen, Platzhalter ersetzt). */
  t: (key: string, vars?: Record<string, string | number>) => string;
  /** Rohtext eines UI-Labels (Platzhalter nicht ersetzt). */
  raw: (key: string) => string;
  pending: Record<string, PendingChange>;
  open: (request: EditRequest) => void;
};

export function targetId(target: EditTarget): string {
  if (target.target === 'label') return `label:${target.key}`;
  if (target.target === 'client') return `client:${target.field}`;
  return `${target.target}:${target.id}:${target.field}`;
}

const EditContext = createContext<EditContextValue>({
  enabled: false,
  t: (key, vars) => interpolate(LABEL_DEFINITIONS[key]?.default ?? key, vars),
  raw: (key) => LABEL_DEFINITIONS[key]?.default ?? key,
  pending: {},
  open: () => undefined,
});

export const EditProvider = EditContext.Provider;

export function useEdit(): EditContextValue {
  return useContext(EditContext);
}

function editableProps(onOpen: () => void, isPending: boolean, extraClass?: string) {
  return {
    className: ['tfdEdit__text', isPending ? 'tfdEdit__text--pending' : '', extraClass ?? ''].filter(Boolean).join(' '),
    role: 'button',
    tabIndex: 0,
    title: 'Klicken zum Bearbeiten',
    onClick: (event: React.MouseEvent) => {
      // Texte sitzen oft in Buttons/Labels – deren Aktion im Bearbeitungsmodus nicht auslösen.
      event.preventDefault();
      event.stopPropagation();
      onOpen();
    },
    onKeyDown: (event: React.KeyboardEvent) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        event.stopPropagation();
        onOpen();
      }
    },
  };
}

/** Fester UI-Text über seinen Schlüssel (labels.de.json). */
export function Txt({ k, vars }: { k: string; vars?: Record<string, string | number> }) {
  const edit = useEdit();
  const text = edit.t(k, vars);
  if (!edit.enabled) return <>{text}</>;

  const definition = LABEL_DEFINITIONS[k];
  const isPending = targetId({ target: 'label', key: k }) in edit.pending;

  return (
    <span
      {...editableProps(() => edit.open({
        target: { target: 'label', key: k },
        title: definition ? `${definition.group}: ${definition.label}` : k,
        current: edit.raw(k),
        defaultValue: definition?.default,
        multiline: definition?.multiline,
        nullable: true,
      }), isPending)}
    >
      {text || <em className="tfdEdit__empty">(leer)</em>}
    </span>
  );
}

/** Text aus der Datenbank (Client, Schritt, Frage, Option, Paket, Tool). */
export function Editable({
  target,
  value,
  title,
  multiline,
  nullable,
}: {
  target: EditTarget | null | undefined;
  value: string | null | undefined;
  title: string;
  multiline?: boolean;
  nullable?: boolean;
}) {
  const edit = useEdit();
  if (!edit.enabled || !target) return <>{value ?? ''}</>;

  const pending = edit.pending[targetId(target)];
  const shown = pending ? pending.value : value;

  return (
    <span
      {...editableProps(() => edit.open({
        target,
        title,
        current: shown ?? '',
        multiline,
        nullable,
      }), Boolean(pending))}
    >
      {shown || <em className="tfdEdit__empty">(leer – klicken zum Ausfüllen)</em>}
    </span>
  );
}

export function EditorDialog({
  request,
  onApply,
  onClose,
}: {
  request: EditRequest;
  onApply: (value: string | null) => void;
  onClose: () => void;
}) {
  const [draft, setDraft] = useState(request.current);
  const inputRef = useRef<HTMLTextAreaElement | HTMLInputElement | null>(null);
  const multiline = request.multiline || request.current.length > 80 || request.current.includes('\n');
  const isEmpty = draft.trim() === '';
  const canApply = request.nullable || !isEmpty;

  useEffect(() => {
    inputRef.current?.focus();
    inputRef.current?.select();
  }, []);

  const apply = () => {
    if (!canApply) return;
    onApply(isEmpty ? (request.target.target === 'label' ? '' : null) : draft);
  };

  const onKeyDown = (event: React.KeyboardEvent) => {
    if (event.key === 'Escape') {
      event.stopPropagation();
      onClose();
    } else if (event.key === 'Enter' && (!multiline || event.metaKey || event.ctrlKey)) {
      event.preventDefault();
      apply();
    }
  };

  return (
    <div className="tfdEdit__backdrop" role="presentation" onClick={onClose}>
      <div className="tfdEdit__dialog" role="dialog" aria-modal="true" aria-label="Text bearbeiten" onClick={(event) => event.stopPropagation()}>
        <span className="tfdEdit__dialogTitle">{request.title}</span>
        {multiline ? (
          <textarea
            ref={(element) => { inputRef.current = element; }}
            value={draft}
            rows={5}
            onChange={(event) => setDraft(event.target.value)}
            onKeyDown={onKeyDown}
          />
        ) : (
          <input
            ref={(element) => { inputRef.current = element; }}
            type="text"
            value={draft}
            onChange={(event) => setDraft(event.target.value)}
            onKeyDown={onKeyDown}
          />
        )}
        {request.defaultValue !== undefined && (
          <small className="tfdEdit__hint">Standard: {request.defaultValue || '(leer)'}</small>
        )}
        {/\{\w+\}/.test(request.defaultValue ?? request.current) && (
          <small className="tfdEdit__hint">Platzhalter in geschweiften Klammern (z. B. {'{date}'}) werden automatisch ersetzt.</small>
        )}
        {!canApply && <small className="tfdEdit__hint tfdEdit__hint--error">Dieses Feld darf nicht leer sein.</small>}
        <div className="tfdEdit__dialogActions">
          {request.defaultValue !== undefined && (
            <button type="button" onClick={() => onApply(null)}>Standard</button>
          )}
          <span />
          <button type="button" onClick={onClose}>Abbrechen</button>
          <button type="button" className="tfdEdit__primary" onClick={apply} disabled={!canApply}>Übernehmen</button>
        </div>
      </div>
    </div>
  );
}

export type ClientTextField = {
  field: 'headline' | 'introText' | 'privacyNotice' | 'toolDisclaimer';
  label: string;
  value: string;
};

/** Liste aller UI-Texte – auch der, die gerade nicht sichtbar sind (Meldungen, Platzhalter …). */
export function AllTextsPanel({ clientFields, onClose }: { clientFields: ClientTextField[]; onClose: () => void }) {
  const edit = useEdit();
  const groups: Record<string, string[]> = {};
  for (const [key, definition] of Object.entries(LABEL_DEFINITIONS)) {
    (groups[definition.group] ??= []).push(key);
  }

  return (
    <div className="tfdEdit__panel" role="dialog" aria-label="Alle Texte">
      <div className="tfdEdit__panelHead">
        <b>Alle Texte</b>
        <button type="button" onClick={onClose} aria-label="Schließen">×</button>
      </div>
      <div className="tfdEdit__panelBody">
        <section>
          <h4>Client</h4>
          {clientFields.map((item) => {
            const pending = edit.pending[targetId({ target: 'client', field: item.field })];
            const value = pending ? pending.value ?? '' : item.value;
            return (
              <button
                type="button"
                key={item.field}
                className={pending ? 'tfdEdit__row tfdEdit__row--pending' : 'tfdEdit__row'}
                onClick={() => edit.open({
                  target: { target: 'client', field: item.field },
                  title: `Client: ${item.label}`,
                  current: value,
                  multiline: item.field !== 'headline',
                })}
              >
                <small>{item.label}</small>
                <span>{value || '(leer)'}</span>
              </button>
            );
          })}
        </section>
        {Object.entries(groups).map(([group, keys]) => (
          <section key={group}>
            <h4>{group}</h4>
            {keys.map((key) => {
              const definition = LABEL_DEFINITIONS[key];
              const isPending = targetId({ target: 'label', key }) in edit.pending;
              const value = edit.raw(key);
              return (
                <button
                  type="button"
                  key={key}
                  className={isPending ? 'tfdEdit__row tfdEdit__row--pending' : 'tfdEdit__row'}
                  onClick={() => edit.open({
                    target: { target: 'label', key },
                    title: `${definition.group}: ${definition.label}`,
                    current: value,
                    defaultValue: definition.default,
                    multiline: definition.multiline,
                    nullable: true,
                  })}
                >
                  <small>{definition.label}{value !== definition.default ? ' · angepasst' : ''}</small>
                  <span>{value || '(leer)'}</span>
                </button>
              );
            })}
          </section>
        ))}
      </div>
    </div>
  );
}
