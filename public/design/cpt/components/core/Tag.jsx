import React from 'react';

const STYLE_ID = 'cdf-tag-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-tag{
    display:inline-flex;align-items:center;gap:.45em;
    font-family:var(--font-sans);font-weight:var(--fw-medium);font-size:var(--text-sm);
    line-height:1;padding:.4em .7em;border-radius:var(--radius-pill);
    background:var(--surface-2);color:var(--text);border:var(--bw-hair) solid var(--border);
  }
  .cdf-tag--active{background:var(--accent-soft);color:var(--accent-text);border-color:var(--accent-soft-border);}
  .cdf-tag__x{display:inline-flex;align-items:center;justify-content:center;cursor:pointer;border:0;background:transparent;color:inherit;opacity:.6;padding:0;margin:-.2em -.25em -.2em 0;border-radius:var(--radius-pill);}
  .cdf-tag__x:hover{opacity:1;}
  .cdf-tag__x svg,.cdf-tag__x i{width:14px;height:14px;}
  `;
  document.head.appendChild(el);
}

export function Tag({ children, active = false, onRemove = null, ...rest }) {
  return (
    <span className={`cdf-tag${active ? ' cdf-tag--active' : ''}`} {...rest}>
      {children}
      {onRemove && (
        <button type="button" className="cdf-tag__x" aria-label="Entfernen" onClick={onRemove}>
          <i data-lucide="x" />
        </button>
      )}
    </span>
  );
}
