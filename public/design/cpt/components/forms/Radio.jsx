import React from 'react';

const STYLE_ID = 'cdf-radio-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-radio{display:inline-flex;align-items:flex-start;gap:.6em;cursor:pointer;font-family:var(--font-sans);font-size:var(--text-md);color:var(--text);line-height:1.35;}
  .cdf-radio input{position:absolute;opacity:0;width:0;height:0;}
  .cdf-radio__dot{flex:none;width:20px;height:20px;margin-top:1px;border-radius:50%;
    border:var(--bw-thick) solid var(--border-strong);background:var(--surface);
    display:inline-flex;align-items:center;justify-content:center;
    transition:border-color var(--dur-fast) var(--ease-out);}
  .cdf-radio__dot::after{content:"";width:10px;height:10px;border-radius:50%;background:var(--accent);transform:scale(0);transition:transform var(--dur-fast) var(--ease-snap);}
  .cdf-radio input:checked+.cdf-radio__dot{border-color:var(--accent);}
  .cdf-radio input:checked+.cdf-radio__dot::after{transform:scale(1);}
  .cdf-radio input:focus-visible+.cdf-radio__dot{outline:var(--bw-thick) solid var(--focus-ring);outline-offset:2px;}
  .cdf-radio--disabled{opacity:.5;cursor:not-allowed;}
  `;
  document.head.appendChild(el);
}

export function Radio({ label, name, value, checked, disabled = false, ...rest }) {
  return (
    <label className={`cdf-radio${disabled ? ' cdf-radio--disabled' : ''}`}>
      <input type="radio" name={name} value={value} checked={checked} disabled={disabled} {...rest} />
      <span className="cdf-radio__dot" />
      {label && <span>{label}</span>}
    </label>
  );
}
