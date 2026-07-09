import React from 'react';

const STYLE_ID = 'cdf-select-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-select-wrap{position:relative;display:flex;align-items:center;background:var(--surface);
    border:var(--bw-hair) solid var(--border-strong);border-radius:var(--radius-md);height:42px;
    transition:border-color var(--dur-fast) var(--ease-out),box-shadow var(--dur-fast) var(--ease-out);}
  .cdf-select-wrap:focus-within{border-color:var(--accent);box-shadow:0 0 0 3px var(--focus-ring);}
  .cdf-select-wrap--disabled{background:var(--surface-2);opacity:.6;}
  .cdf-select{appearance:none;-webkit-appearance:none;border:0;background:transparent;outline:none;
    font-family:var(--font-sans);font-size:var(--text-md);color:var(--text);
    height:100%;width:100%;padding:0 38px 0 var(--space-3);cursor:pointer;}
  .cdf-select:invalid{color:var(--text-subtle);}
  .cdf-select-wrap__chev{position:absolute;right:12px;pointer-events:none;color:var(--text-muted);display:inline-flex;}
  .cdf-select-wrap__chev svg,.cdf-select-wrap__chev i{width:18px;height:18px;}
  `;
  document.head.appendChild(el);
}

export function Select({
  label, hint, error, required = false, disabled = false,
  placeholder, options = [], children, id, value, ...rest
}) {
  const fieldId = id || (label ? `cdf-sel-${label.replace(/\s+/g, '-').toLowerCase()}` : undefined);
  return (
    <div className="cdf-field">
      {label && (
        <label className="cdf-field__label" htmlFor={fieldId}>
          {label}{required && <span className="cdf-field__req">*</span>}
        </label>
      )}
      <div className={`cdf-select-wrap${disabled ? ' cdf-select-wrap--disabled' : ''}`}>
        <select id={fieldId} className="cdf-select" disabled={disabled} required={required} defaultValue={value === undefined ? '' : undefined} value={value} {...rest}>
          {placeholder && <option value="" disabled>{placeholder}</option>}
          {options.map((o) => {
            const val = typeof o === 'string' ? o : o.value;
            const lbl = typeof o === 'string' ? o : o.label;
            return <option key={val} value={val}>{lbl}</option>;
          })}
          {children}
        </select>
        <span className="cdf-select-wrap__chev"><i data-lucide="chevron-down" /></span>
      </div>
      {(error || hint) && (
        <span className={`cdf-field__hint${error ? ' cdf-field__hint--err' : ''}`}>{error || hint}</span>
      )}
    </div>
  );
}
