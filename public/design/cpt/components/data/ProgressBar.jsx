import React from 'react';

const STYLE_ID = 'cdf-progress-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-prog{display:flex;flex-direction:column;gap:6px;font-family:var(--font-mono);}
  .cdf-prog__top{display:flex;justify-content:space-between;font-size:var(--text-2xs);letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);}
  .cdf-prog__val{color:var(--text);font-weight:var(--fw-medium);}
  .cdf-prog__track{height:8px;border-radius:var(--radius-pill);background:var(--surface-sunken);overflow:hidden;}
  .cdf-prog__bar{height:100%;border-radius:var(--radius-pill);background:var(--accent);
    transition:width var(--dur-slow) var(--ease-out);}
  .cdf-prog__bar--success{background:var(--success);}
  .cdf-prog__bar--indeterminate{width:35%!important;animation:cdf-prog-ind 1.1s var(--ease-in-out) infinite;}
  @keyframes cdf-prog-ind{0%{margin-left:-35%}100%{margin-left:100%}}
  `;
  document.head.appendChild(el);
}

export function ProgressBar({ value = 0, label, showValue = true, tone = 'accent', indeterminate = false, ...rest }) {
  const pct = Math.max(0, Math.min(100, value));
  return (
    <div className="cdf-prog" {...rest}>
      {(label || showValue) && (
        <div className="cdf-prog__top">
          <span>{label}</span>
          {showValue && !indeterminate && <span className="cdf-prog__val">{pct}%</span>}
        </div>
      )}
      <div className="cdf-prog__track">
        <div className={`cdf-prog__bar${tone === 'success' ? ' cdf-prog__bar--success' : ''}${indeterminate ? ' cdf-prog__bar--indeterminate' : ''}`}
             style={indeterminate ? undefined : { width: `${pct}%` }} />
      </div>
    </div>
  );
}
