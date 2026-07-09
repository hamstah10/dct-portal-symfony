import React from 'react';

const STYLE_ID = 'cdf-stattile-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-stat{display:flex;flex-direction:column;gap:6px;background:var(--surface);
    border:var(--bw-hair) solid var(--border);border-radius:var(--radius-md);padding:var(--space-4) var(--space-5);}
  .cdf-stat--dark{background:var(--carbon-900);border-color:var(--carbon-800);}
  .cdf-stat__k{font-family:var(--font-mono);font-size:var(--text-2xs);letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);}
  .cdf-stat--dark .cdf-stat__k{color:var(--carbon-400);}
  .cdf-stat__v{font-family:var(--font-mono);font-weight:var(--fw-semibold);font-size:var(--text-3xl);line-height:1;color:var(--text);font-variant-numeric:tabular-nums;}
  .cdf-stat--dark .cdf-stat__v{color:#fff;}
  .cdf-stat__v .u{font-size:.45em;color:var(--text-muted);font-weight:var(--fw-regular);margin-left:.2em;}
  .cdf-stat--gain .cdf-stat__v{color:var(--accent);}
  .cdf-stat__delta{display:inline-flex;align-items:center;gap:.3em;font-family:var(--font-mono);font-size:var(--text-xs);}
  .cdf-stat__delta--up{color:var(--success);}
  .cdf-stat__delta--down{color:var(--danger);}
  .cdf-stat__delta svg,.cdf-stat__delta i{width:13px;height:13px;}
  `;
  document.head.appendChild(el);
}

export function StatTile({ label, value, unit, delta, gain = false, dark = false, ...rest }) {
  const cls = ['cdf-stat', gain ? 'cdf-stat--gain' : '', dark ? 'cdf-stat--dark' : ''].filter(Boolean).join(' ');
  const up = typeof delta === 'string' ? delta.trim().startsWith('+') : (delta > 0);
  return (
    <div className={cls} {...rest}>
      <span className="cdf-stat__k">{label}</span>
      <span className="cdf-stat__v">{value}{unit && <span className="u">{unit}</span>}</span>
      {delta != null && (
        <span className={`cdf-stat__delta cdf-stat__delta--${up ? 'up' : 'down'}`}>
          <i data-lucide={up ? 'trending-up' : 'trending-down'} />{delta}
        </span>
      )}
    </div>
  );
}
