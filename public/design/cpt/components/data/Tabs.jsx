import React from 'react';

const STYLE_ID = 'cdf-tabs-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-tabs{display:flex;gap:2px;border-bottom:var(--bw-hair) solid var(--border);}
  .cdf-tab{appearance:none;border:0;background:transparent;cursor:pointer;
    font-family:var(--font-sans);font-weight:var(--fw-semibold);font-size:var(--text-sm);
    color:var(--text-muted);padding:var(--space-3) var(--space-4);position:relative;
    display:inline-flex;align-items:center;gap:.5em;
    transition:color var(--dur-fast) var(--ease-out);}
  .cdf-tab:hover{color:var(--text);}
  .cdf-tab[aria-selected="true"]{color:var(--text);}
  .cdf-tab[aria-selected="true"]::after{content:"";position:absolute;left:0;right:0;bottom:-1px;
    height:var(--bw-thick);background:var(--accent);}
  .cdf-tab svg,.cdf-tab i{width:16px;height:16px;}
  .cdf-tab__count{font-family:var(--font-mono);font-size:var(--text-2xs);background:var(--surface-2);
    color:var(--text-muted);border-radius:var(--radius-pill);padding:.15em .5em;}
  .cdf-tab[aria-selected="true"] .cdf-tab__count{background:var(--accent-soft);color:var(--accent-text);}
  `;
  document.head.appendChild(el);
}

export function Tabs({ tabs = [], value, onChange = () => {}, ...rest }) {
  return (
    <div className="cdf-tabs" role="tablist" {...rest}>
      {tabs.map((t) => {
        const id = typeof t === 'string' ? t : t.id;
        const label = typeof t === 'string' ? t : t.label;
        const icon = typeof t === 'string' ? null : t.icon;
        const count = typeof t === 'string' ? null : t.count;
        return (
          <button key={id} role="tab" aria-selected={value === id}
                  className="cdf-tab" onClick={() => onChange(id)}>
            {icon && <i data-lucide={icon} />}{label}
            {count != null && <span className="cdf-tab__count">{count}</span>}
          </button>
        );
      })}
    </div>
  );
}
