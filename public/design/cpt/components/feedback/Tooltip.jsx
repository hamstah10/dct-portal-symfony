import React from 'react';

const STYLE_ID = 'cdf-tooltip-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-tip{position:relative;display:inline-flex;}
  .cdf-tip__pop{position:absolute;z-index:900;left:50%;transform:translateX(-50%) translateY(4px);
    bottom:calc(100% + 8px);opacity:0;pointer-events:none;
    background:var(--carbon-900);color:var(--carbon-50);border:var(--bw-hair) solid var(--carbon-700);
    font-family:var(--font-mono);font-size:var(--text-2xs);letter-spacing:.02em;line-height:1.3;
    padding:.45em .65em;border-radius:var(--radius-sm);white-space:nowrap;box-shadow:var(--shadow-md);
    transition:opacity var(--dur-fast) var(--ease-out),transform var(--dur-fast) var(--ease-out);}
  .cdf-tip__pop::after{content:"";position:absolute;top:100%;left:50%;transform:translateX(-50%);
    border:5px solid transparent;border-top-color:var(--carbon-900);}
  .cdf-tip:hover .cdf-tip__pop,.cdf-tip:focus-within .cdf-tip__pop{opacity:1;transform:translateX(-50%) translateY(0);}
  .cdf-tip--bottom .cdf-tip__pop{bottom:auto;top:calc(100% + 8px);transform:translateX(-50%) translateY(-4px);}
  .cdf-tip--bottom .cdf-tip__pop::after{top:auto;bottom:100%;border-top-color:transparent;border-bottom-color:var(--carbon-900);}
  .cdf-tip--bottom:hover .cdf-tip__pop{transform:translateX(-50%) translateY(0);}
  `;
  document.head.appendChild(el);
}

export function Tooltip({ label, placement = 'top', children }) {
  return (
    <span className={`cdf-tip cdf-tip--${placement}`} tabIndex={0}>
      {children}
      <span className="cdf-tip__pop" role="tooltip">{label}</span>
    </span>
  );
}
