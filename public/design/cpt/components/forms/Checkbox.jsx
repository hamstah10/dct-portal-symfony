import React from 'react';

const STYLE_ID = 'cdf-checkbox-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-check{display:inline-flex;align-items:flex-start;gap:.6em;cursor:pointer;font-family:var(--font-sans);font-size:var(--text-md);color:var(--text);line-height:1.35;}
  .cdf-check input{position:absolute;opacity:0;width:0;height:0;}
  .cdf-check__box{flex:none;width:20px;height:20px;margin-top:1px;border-radius:var(--radius-sm);
    border:var(--bw-thick) solid var(--border-strong);background:var(--surface);
    display:inline-flex;align-items:center;justify-content:center;color:#fff;
    transition:background var(--dur-fast) var(--ease-out),border-color var(--dur-fast) var(--ease-out);}
  .cdf-check__box svg{width:14px;height:14px;opacity:0;transform:scale(.6);transition:all var(--dur-fast) var(--ease-snap);}
  .cdf-check input:checked+.cdf-check__box{background:var(--accent);border-color:var(--accent);}
  .cdf-check input:checked+.cdf-check__box svg{opacity:1;transform:scale(1);}
  .cdf-check input:focus-visible+.cdf-check__box{outline:var(--bw-thick) solid var(--focus-ring);outline-offset:2px;}
  .cdf-check--disabled{opacity:.5;cursor:not-allowed;}
  `;
  document.head.appendChild(el);
}

function Tick() {
  return (<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 6 9 17 4 12" /></svg>);
}

export function Checkbox({ label, checked, disabled = false, ...rest }) {
  return (
    <label className={`cdf-check${disabled ? ' cdf-check--disabled' : ''}`}>
      <input type="checkbox" checked={checked} disabled={disabled} {...rest} />
      <span className="cdf-check__box"><Tick /></span>
      {label && <span>{label}</span>}
    </label>
  );
}
