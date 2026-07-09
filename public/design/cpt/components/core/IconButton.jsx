import React from 'react';

const STYLE_ID = 'cdf-iconbutton-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-iconbtn{
    display:inline-flex;align-items:center;justify-content:center;cursor:pointer;
    border:var(--bw-hair) solid transparent;background:transparent;color:var(--text);
    border-radius:var(--radius-md);
    transition:background var(--dur-fast) var(--ease-out),color var(--dur-fast) var(--ease-out),border-color var(--dur-fast) var(--ease-out),transform var(--dur-fast) var(--ease-out);
  }
  .cdf-iconbtn:focus-visible{outline:var(--bw-thick) solid var(--focus-ring);outline-offset:2px;}
  .cdf-iconbtn:active{transform:scale(.94);}
  .cdf-iconbtn:disabled{opacity:.4;cursor:not-allowed;}
  .cdf-iconbtn--sm{width:34px;height:34px;}
  .cdf-iconbtn--md{width:42px;height:42px;}
  .cdf-iconbtn--lg{width:48px;height:48px;}
  .cdf-iconbtn--ghost:hover:not(:disabled){background:var(--surface-2);}
  .cdf-iconbtn--outline{border-color:var(--border-strong);background:var(--surface);}
  .cdf-iconbtn--outline:hover:not(:disabled){background:var(--surface-2);border-color:var(--carbon-400);}
  .cdf-iconbtn--solid{background:var(--accent);color:var(--text-on-accent);}
  .cdf-iconbtn--solid:hover:not(:disabled){background:var(--accent-hover);}
  .cdf-iconbtn svg,.cdf-iconbtn i{width:1.25em;height:1.25em;}
  `;
  document.head.appendChild(el);
}

export function IconButton({
  children,
  variant = 'ghost',
  size = 'md',
  label,
  disabled = false,
  ...rest
}) {
  const cls = ['cdf-iconbtn', `cdf-iconbtn--${variant}`, `cdf-iconbtn--${size}`].join(' ');
  return (
    <button type="button" className={cls} aria-label={label} title={label} disabled={disabled} {...rest}>
      {children}
    </button>
  );
}
