import React from 'react';

const STYLE_ID = 'cdf-toast-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-toast{display:flex;align-items:flex-start;gap:var(--space-3);
    background:var(--carbon-900);color:var(--carbon-50);
    border:var(--bw-hair) solid var(--carbon-700);border-left:var(--bw-thick) solid var(--accent);
    border-radius:var(--radius-md);box-shadow:var(--shadow-lg);
    padding:var(--space-3) var(--space-4);min-width:280px;max-width:420px;font-family:var(--font-sans);
    animation:cdf-toast-in var(--dur-base) var(--ease-out);}
  .cdf-toast--success{border-left-color:var(--dyno-500);}
  .cdf-toast--warning{border-left-color:var(--amber-500);}
  .cdf-toast--danger{border-left-color:var(--redline-500);}
  .cdf-toast__icon{flex:none;margin-top:1px;}
  .cdf-toast__icon svg,.cdf-toast__icon i{width:18px;height:18px;}
  .cdf-toast--accent .cdf-toast__icon{color:var(--power-400);}
  .cdf-toast--success .cdf-toast__icon{color:var(--dyno-300);}
  .cdf-toast--warning .cdf-toast__icon{color:var(--amber-300);}
  .cdf-toast--danger .cdf-toast__icon{color:var(--redline-300);}
  .cdf-toast__body{flex:1;min-width:0;}
  .cdf-toast__title{font-weight:var(--fw-semibold);font-size:var(--text-sm);}
  .cdf-toast__msg{font-size:var(--text-sm);color:var(--carbon-300);margin-top:2px;}
  .cdf-toast__x{flex:none;border:0;background:transparent;color:var(--carbon-400);cursor:pointer;padding:0;display:inline-flex;}
  .cdf-toast__x:hover{color:#fff;}
  .cdf-toast__x svg,.cdf-toast__x i{width:16px;height:16px;}
  @keyframes cdf-toast-in{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
  @media (prefers-reduced-motion:reduce){.cdf-toast{animation:none}}
  `;
  document.head.appendChild(el);
}

const ICON = { accent: 'info', success: 'check-circle', warning: 'alert-triangle', danger: 'x-circle' };

export function Toast({ tone = 'accent', title, children, onClose }) {
  return (
    <div className={`cdf-toast cdf-toast--${tone}`} role="status">
      <span className="cdf-toast__icon"><i data-lucide={ICON[tone] || 'info'} /></span>
      <div className="cdf-toast__body">
        {title && <div className="cdf-toast__title">{title}</div>}
        {children && <div className="cdf-toast__msg">{children}</div>}
      </div>
      {onClose && <button className="cdf-toast__x" aria-label="Schließen" onClick={onClose}><i data-lucide="x" /></button>}
    </div>
  );
}
