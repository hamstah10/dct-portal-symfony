import React from 'react';

const STYLE_ID = 'cdf-input-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-field{display:flex;flex-direction:column;gap:6px;font-family:var(--font-sans);}
  .cdf-field__label{font-size:var(--text-sm);font-weight:var(--fw-semibold);color:var(--text);}
  .cdf-field__req{color:var(--accent);margin-left:.15em;}
  .cdf-field__hint{font-size:var(--text-xs);color:var(--text-muted);}
  .cdf-field__hint--err{color:var(--danger);}
  .cdf-input-wrap{display:flex;align-items:center;gap:.5em;background:var(--surface);
    border:var(--bw-hair) solid var(--border-strong);border-radius:var(--radius-md);
    padding:0 var(--space-3);height:42px;transition:border-color var(--dur-fast) var(--ease-out),box-shadow var(--dur-fast) var(--ease-out);}
  .cdf-input-wrap:focus-within{border-color:var(--accent);box-shadow:0 0 0 3px var(--focus-ring);}
  .cdf-input-wrap--err{border-color:var(--danger);}
  .cdf-input-wrap--err:focus-within{box-shadow:0 0 0 3px color-mix(in oklch,var(--danger) 35%,transparent);}
  .cdf-input-wrap--disabled{background:var(--surface-2);opacity:.6;}
  .cdf-input{flex:1;min-width:0;border:0;background:transparent;outline:none;
    font-family:var(--font-sans);font-size:var(--text-md);color:var(--text);height:100%;}
  .cdf-input::placeholder{color:var(--text-subtle);}
  .cdf-input-wrap .cdf-aff{color:var(--text-muted);display:inline-flex;font-family:var(--font-mono);font-size:var(--text-sm);}
  .cdf-input-wrap .cdf-aff svg,.cdf-input-wrap .cdf-aff i{width:18px;height:18px;}
  `;
  document.head.appendChild(el);
}

export function Input({
  label, hint, error, required = false, disabled = false,
  prefix = null, suffix = null, id, ...rest
}) {
  const fieldId = id || (label ? `cdf-${label.replace(/\s+/g, '-').toLowerCase()}` : undefined);
  return (
    <div className="cdf-field">
      {label && (
        <label className="cdf-field__label" htmlFor={fieldId}>
          {label}{required && <span className="cdf-field__req">*</span>}
        </label>
      )}
      <div className={`cdf-input-wrap${error ? ' cdf-input-wrap--err' : ''}${disabled ? ' cdf-input-wrap--disabled' : ''}`}>
        {prefix && <span className="cdf-aff">{prefix}</span>}
        <input id={fieldId} className="cdf-input" disabled={disabled} aria-invalid={!!error} {...rest} />
        {suffix && <span className="cdf-aff">{suffix}</span>}
      </div>
      {(error || hint) && (
        <span className={`cdf-field__hint${error ? ' cdf-field__hint--err' : ''}`}>{error || hint}</span>
      )}
    </div>
  );
}
