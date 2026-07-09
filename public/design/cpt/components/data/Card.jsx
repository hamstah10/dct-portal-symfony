import React from 'react';

const STYLE_ID = 'cdf-card-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-card{background:var(--surface);border:var(--bw-hair) solid var(--border);
    border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);overflow:hidden;
    transition:box-shadow var(--dur-base) var(--ease-out),border-color var(--dur-base) var(--ease-out),transform var(--dur-base) var(--ease-out);}
  .cdf-card--featured{border-top:var(--bw-thick) solid var(--accent);}
  .cdf-card--interactive{cursor:pointer;}
  .cdf-card--interactive:hover{box-shadow:var(--shadow-md);border-color:var(--border-strong);transform:translateY(-2px);}
  .cdf-card__media{display:block;width:100%;aspect-ratio:16/10;object-fit:cover;background:var(--surface-2);}
  .cdf-card__body{padding:var(--space-5);}
  .cdf-card__head{display:flex;align-items:flex-start;justify-content:space-between;gap:var(--space-3);margin-bottom:var(--space-2);}
  .cdf-card__title{font-family:var(--font-display);text-transform:uppercase;letter-spacing:-.01em;font-weight:var(--fw-bold);font-size:var(--text-xl);color:var(--text);margin:0;line-height:1.05;}
  .cdf-card__foot{padding:var(--space-4) var(--space-5);border-top:var(--bw-hair) solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:var(--space-3);}
  `;
  document.head.appendChild(el);
}

export function Card({
  title, media, badge, footer, featured = false, interactive = false,
  children, className = '', ...rest
}) {
  const cls = ['cdf-card', featured ? 'cdf-card--featured' : '', interactive ? 'cdf-card--interactive' : '', className].filter(Boolean).join(' ');
  return (
    <div className={cls} {...rest}>
      {media && (typeof media === 'string'
        ? <img className="cdf-card__media" src={media} alt="" />
        : <div className="cdf-card__media">{media}</div>)}
      <div className="cdf-card__body">
        {(title || badge) && (
          <div className="cdf-card__head">
            {title && <h3 className="cdf-card__title">{title}</h3>}
            {badge}
          </div>
        )}
        {children}
      </div>
      {footer && <div className="cdf-card__foot">{footer}</div>}
    </div>
  );
}
