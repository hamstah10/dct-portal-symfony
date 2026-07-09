import React from 'react';

const STYLE_ID = 'cdf-logo-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-logo{display:inline-flex;align-items:center;gap:.55em;text-decoration:none;color:inherit;}
  .cdf-logo__mark{flex:none;display:block;}
  .cdf-logo__wm{display:flex;flex-direction:column;line-height:1;}
  .cdf-logo__a{font-family:var(--font-display);font-weight:var(--fw-black);text-transform:uppercase;letter-spacing:-.01em;color:var(--text);}
  .cdf-logo__a em{font-style:normal;color:var(--accent);}
  .cdf-logo__b{font-family:var(--font-mono);letter-spacing:.32em;text-transform:uppercase;color:var(--text-subtle);margin-top:.35em;}
  .cdf-logo--sm .cdf-logo__a{font-size:18px;}
  .cdf-logo--sm .cdf-logo__b{font-size:7.5px;}
  .cdf-logo--md .cdf-logo__a{font-size:23px;}
  .cdf-logo--md .cdf-logo__b{font-size:9px;}
  .cdf-logo--lg .cdf-logo__a{font-size:30px;}
  .cdf-logo--lg .cdf-logo__b{font-size:10.5px;}
  `;
  document.head.appendChild(el);
}

const SIZE_PX = { sm: 30, md: 38, lg: 48 };

export function Logo({ size = 'md', showWordmark = true, tagline = 'ECU Performance Files', solidMark = false, href = null, assetBase = '', ...rest }) {
  const px = SIZE_PX[size] || 38;
  const src = `${assetBase}assets/${solidMark ? 'logo-mark-solid.svg' : 'logo-mark.svg'}`;
  const Tag = href ? 'a' : 'span';
  return (
    <Tag className={`cdf-logo cdf-logo--${size}`} href={href || undefined} {...rest}>
      <img className="cdf-logo__mark" src={src} width={px} height={px} alt="Chiptuningfile" />
      {showWordmark && (
        <span className="cdf-logo__wm">
          <span className="cdf-logo__a">Chip<em>tuning</em>file</span>
          {tagline && <span className="cdf-logo__b">{tagline}</span>}
        </span>
      )}
    </Tag>
  );
}
