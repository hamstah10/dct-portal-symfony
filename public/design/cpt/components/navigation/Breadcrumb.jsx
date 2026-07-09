import React from 'react';

const STYLE_ID = 'cdf-breadcrumb-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-bc{display:flex;align-items:center;flex-wrap:wrap;gap:.35em;
    font-family:var(--font-mono);font-size:var(--text-xs);letter-spacing:.04em;text-transform:uppercase;}
  .cdf-bc a,.cdf-bc span{color:var(--text-muted);text-decoration:none;}
  .cdf-bc a:hover{color:var(--accent-text);}
  .cdf-bc__sep{color:var(--text-subtle);display:inline-flex;}
  .cdf-bc__sep svg,.cdf-bc__sep i{width:13px;height:13px;}
  .cdf-bc__current{color:var(--text);font-weight:var(--fw-medium);}
  `;
  document.head.appendChild(el);
}

export function Breadcrumb({ items = [], ...rest }) {
  return (
    <nav className="cdf-bc" aria-label="Breadcrumb" {...rest}>
      {items.map((it, i) => {
        const last = i === items.length - 1;
        const label = typeof it === 'string' ? it : it.label;
        const href = typeof it === 'string' ? null : it.href;
        return (
          <React.Fragment key={i}>
            {last
              ? <span className="cdf-bc__current" aria-current="page">{label}</span>
              : (href ? <a href={href}>{label}</a> : <span>{label}</span>)}
            {!last && <span className="cdf-bc__sep"><i data-lucide="chevron-right" /></span>}
          </React.Fragment>
        );
      })}
    </nav>
  );
}
