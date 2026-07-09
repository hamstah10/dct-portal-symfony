import React from 'react';

const STYLE_ID = 'cdf-switch-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-switch{display:inline-flex;align-items:center;gap:.65em;cursor:pointer;font-family:var(--font-sans);font-size:var(--text-md);color:var(--text);}
  .cdf-switch input{position:absolute;opacity:0;width:0;height:0;}
  .cdf-switch__track{flex:none;width:42px;height:24px;border-radius:var(--radius-pill);
    background:var(--carbon-300);position:relative;transition:background var(--dur-base) var(--ease-out);}
  .cdf-switch__thumb{position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;
    background:#fff;box-shadow:var(--shadow-sm);transition:transform var(--dur-base) var(--ease-snap);}
  .cdf-switch input:checked+.cdf-switch__track{background:var(--accent);}
  .cdf-switch input:checked+.cdf-switch__track .cdf-switch__thumb{transform:translateX(18px);}
  .cdf-switch input:focus-visible+.cdf-switch__track{outline:var(--bw-thick) solid var(--focus-ring);outline-offset:2px;}
  .cdf-switch--disabled{opacity:.5;cursor:not-allowed;}
  `;
  document.head.appendChild(el);
}

export function Switch({ label, checked, disabled = false, ...rest }) {
  return (
    <label className={`cdf-switch${disabled ? ' cdf-switch--disabled' : ''}`}>
      <input type="checkbox" role="switch" checked={checked} disabled={disabled} {...rest} />
      <span className="cdf-switch__track"><span className="cdf-switch__thumb" /></span>
      {label && <span>{label}</span>}
    </label>
  );
}
