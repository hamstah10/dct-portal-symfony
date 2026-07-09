import React from 'react';

const STYLE_ID = 'cdf-modal-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-modal__scrim{position:fixed;inset:0;background:color-mix(in oklch,var(--carbon-950) 62%,transparent);
    backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;padding:var(--space-5);z-index:1000;
    animation:cdf-modal-fade var(--dur-base) var(--ease-out);}
  .cdf-modal{background:var(--surface);border:var(--bw-hair) solid var(--border);
    border-radius:var(--radius-xl);box-shadow:var(--shadow-xl);width:100%;max-width:520px;
    max-height:calc(100vh - 2*var(--space-5));display:flex;flex-direction:column;overflow:hidden;
    animation:cdf-modal-pop var(--dur-base) var(--ease-out);}
  .cdf-modal--sm{max-width:400px;} .cdf-modal--lg{max-width:720px;}
  .cdf-modal__head{display:flex;align-items:flex-start;justify-content:space-between;gap:var(--space-4);
    padding:var(--space-5) var(--space-5) var(--space-3);}
  .cdf-modal__title{font-family:var(--font-display);text-transform:uppercase;letter-spacing:-.01em;
    font-weight:var(--fw-bold);font-size:var(--text-2xl);color:var(--text);margin:0;line-height:1.05;}
  .cdf-modal__x{flex:none;border:0;background:transparent;cursor:pointer;color:var(--text-muted);
    width:34px;height:34px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;}
  .cdf-modal__x:hover{background:var(--surface-2);color:var(--text);}
  .cdf-modal__x svg,.cdf-modal__x i{width:20px;height:20px;}
  .cdf-modal__body{padding:0 var(--space-5) var(--space-5);overflow:auto;color:var(--text-muted);}
  .cdf-modal__foot{display:flex;justify-content:flex-end;gap:var(--space-3);
    padding:var(--space-4) var(--space-5);border-top:var(--bw-hair) solid var(--border);background:var(--surface-2);}
  @keyframes cdf-modal-fade{from{opacity:0}to{opacity:1}}
  @keyframes cdf-modal-pop{from{opacity:0;transform:translateY(12px) scale(.98)}to{opacity:1;transform:none}}
  @media (prefers-reduced-motion:reduce){.cdf-modal,.cdf-modal__scrim{animation:none}}
  `;
  document.head.appendChild(el);
}

export function Modal({ open, onClose = () => {}, title, size = 'md', footer, children }) {
  React.useEffect(() => {
    if (!open) return undefined;
    const onKey = (e) => e.key === 'Escape' && onClose();
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [open, onClose]);
  if (!open) return null;
  return (
    <div className="cdf-modal__scrim" onMouseDown={(e) => e.target === e.currentTarget && onClose()}>
      <div className={`cdf-modal cdf-modal--${size}`} role="dialog" aria-modal="true">
        <div className="cdf-modal__head">
          {title && <h2 className="cdf-modal__title">{title}</h2>}
          <button className="cdf-modal__x" aria-label="Schließen" onClick={onClose}><i data-lucide="x" /></button>
        </div>
        <div className="cdf-modal__body">{children}</div>
        {footer && <div className="cdf-modal__foot">{footer}</div>}
      </div>
    </div>
  );
}
