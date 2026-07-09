import React from 'react';

/**
 * Button — primary action control for the Chiptuningfile system.
 * Self-contained: styling via injected stylesheet keyed to design tokens.
 */
const STYLE_ID = 'cdf-button-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-btn{
    --_bg:var(--accent);--_fg:var(--text-on-accent);--_bd:transparent;
    display:inline-flex;align-items:center;justify-content:center;gap:.55em;
    font-family:var(--font-sans);font-weight:var(--fw-bold);
    letter-spacing:.01em;line-height:1;white-space:nowrap;cursor:pointer;
    border:var(--bw-hair) solid var(--_bd);background:var(--_bg);color:var(--_fg);
    border-radius:var(--radius-md);
    transition:background var(--dur-fast) var(--ease-out),
      transform var(--dur-fast) var(--ease-out),
      box-shadow var(--dur-base) var(--ease-out),border-color var(--dur-fast) var(--ease-out);
  }
  .cdf-btn:focus-visible{outline:var(--bw-thick) solid var(--focus-ring);outline-offset:2px;}
  .cdf-btn:active{transform:scale(.975);}
  .cdf-btn:disabled{opacity:.45;cursor:not-allowed;transform:none;}
  /* sizes */
  .cdf-btn--sm{font-size:var(--text-sm);padding:0 var(--space-3);height:34px;}
  .cdf-btn--md{font-size:var(--text-sm);padding:0 var(--space-5);height:42px;}
  .cdf-btn--lg{font-size:var(--text-md);padding:0 var(--space-6);height:52px;}
  .cdf-btn--block{display:flex;width:100%;}
  /* primary */
  .cdf-btn--primary{--_bg:var(--accent);--_fg:var(--text-on-accent);}
  .cdf-btn--primary:hover:not(:disabled){--_bg:var(--accent-hover);box-shadow:var(--glow-power);}
  .cdf-btn--primary:active:not(:disabled){--_bg:var(--accent-press);}
  /* secondary (outline) */
  .cdf-btn--secondary{--_bg:var(--surface);--_fg:var(--text);--_bd:var(--border-strong);}
  .cdf-btn--secondary:hover:not(:disabled){--_bg:var(--surface-2);--_bd:var(--carbon-400);}
  /* ghost */
  .cdf-btn--ghost{--_bg:transparent;--_fg:var(--text);--_bd:transparent;}
  .cdf-btn--ghost:hover:not(:disabled){--_bg:var(--surface-2);}
  /* danger */
  .cdf-btn--danger{--_bg:var(--redline-600);--_fg:#fff;}
  .cdf-btn--danger:hover:not(:disabled){--_bg:var(--redline-700);}
  .cdf-btn__spin{width:1em;height:1em;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;animation:cdf-spin .6s linear infinite;}
  @keyframes cdf-spin{to{transform:rotate(360deg)}}
  `;
  document.head.appendChild(el);
}

export function Button({
  children,
  variant = 'primary',
  size = 'md',
  block = false,
  loading = false,
  disabled = false,
  iconLeft = null,
  iconRight = null,
  type = 'button',
  ...rest
}) {
  const cls = [
    'cdf-btn',
    `cdf-btn--${variant}`,
    `cdf-btn--${size}`,
    block ? 'cdf-btn--block' : '',
  ].filter(Boolean).join(' ');

  return (
    <button type={type} className={cls} disabled={disabled || loading} {...rest}>
      {loading && <span className="cdf-btn__spin" aria-hidden="true" />}
      {!loading && iconLeft}
      {children}
      {!loading && iconRight}
    </button>
  );
}
