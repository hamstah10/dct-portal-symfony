import React from 'react';

const STYLE_ID = 'cdf-badge-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-badge{
    display:inline-flex;align-items:center;gap:.4em;
    font-family:var(--font-mono);font-weight:var(--fw-medium);
    font-size:var(--text-2xs);letter-spacing:.06em;text-transform:uppercase;
    line-height:1;padding:.34em .6em;border-radius:var(--radius-sm);
    border:var(--bw-hair) solid transparent;white-space:nowrap;
  }
  .cdf-badge--solid{background:var(--accent);color:var(--text-on-accent);}
  .cdf-badge svg,.cdf-badge i{width:1.1em;height:1.1em;}
  .cdf-badge--neutral{background:var(--surface-2);color:var(--text-muted);border-color:var(--border);}
  .cdf-badge--accent{background:var(--accent-soft);color:var(--accent-text);border-color:var(--accent-soft-border);}
  .cdf-badge--success{background:var(--success-soft);color:var(--success);}
  .cdf-badge--warning{background:var(--warning-soft);color:var(--warning);}
  .cdf-badge--danger{background:var(--danger-soft);color:var(--danger);}
  .cdf-badge--info{background:var(--info-soft);color:var(--info);}
  .cdf-badge__dot{width:.5em;height:.5em;border-radius:50%;background:currentColor;}
  `;
  document.head.appendChild(el);
}

export function Badge({ children, tone = 'neutral', solid = false, dot = false, ...rest }) {
  const variant = solid ? 'solid' : tone;
  return (
    <span className={`cdf-badge cdf-badge--${variant}`} {...rest}>
      {dot && <span className="cdf-badge__dot" aria-hidden="true" />}
      {children}
    </span>
  );
}
