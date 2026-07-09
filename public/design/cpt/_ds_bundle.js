/* @ds-bundle: {"format":3,"namespace":"ChiptuningfileDesignSystem_3beb42","components":[{"name":"Badge","sourcePath":"components/core/Badge.jsx"},{"name":"Button","sourcePath":"components/core/Button.jsx"},{"name":"IconButton","sourcePath":"components/core/IconButton.jsx"},{"name":"Logo","sourcePath":"components/core/Logo.jsx"},{"name":"Tag","sourcePath":"components/core/Tag.jsx"},{"name":"Card","sourcePath":"components/data/Card.jsx"},{"name":"FileUpload","sourcePath":"components/data/FileUpload.jsx"},{"name":"ProgressBar","sourcePath":"components/data/ProgressBar.jsx"},{"name":"StatTile","sourcePath":"components/data/StatTile.jsx"},{"name":"Table","sourcePath":"components/data/Table.jsx"},{"name":"Tabs","sourcePath":"components/data/Tabs.jsx"},{"name":"Modal","sourcePath":"components/feedback/Modal.jsx"},{"name":"Toast","sourcePath":"components/feedback/Toast.jsx"},{"name":"Tooltip","sourcePath":"components/feedback/Tooltip.jsx"},{"name":"Checkbox","sourcePath":"components/forms/Checkbox.jsx"},{"name":"Input","sourcePath":"components/forms/Input.jsx"},{"name":"Radio","sourcePath":"components/forms/Radio.jsx"},{"name":"Select","sourcePath":"components/forms/Select.jsx"},{"name":"Switch","sourcePath":"components/forms/Switch.jsx"},{"name":"Breadcrumb","sourcePath":"components/navigation/Breadcrumb.jsx"},{"name":"CatalogApp","sourcePath":"ui_kits/catalog/CatalogApp.jsx"},{"name":"MarketingSite","sourcePath":"ui_kits/marketing/MarketingSite.jsx"},{"name":"CreditsApp","sourcePath":"ui_kits/portal/CreditsApp.jsx"},{"name":"PortalSidebar","sourcePath":"ui_kits/portal/PortalApp.jsx"},{"name":"PortalTopBar","sourcePath":"ui_kits/portal/PortalApp.jsx"},{"name":"PortalApp","sourcePath":"ui_kits/portal/PortalApp.jsx"},{"name":"ShopApp","sourcePath":"ui_kits/shop/ShopApp.jsx"}],"sourceHashes":{"components/core/Badge.jsx":"c50696731c01","components/core/Button.jsx":"d5121b78908c","components/core/IconButton.jsx":"2621961ba668","components/core/Logo.jsx":"db443969616b","components/core/Tag.jsx":"47c5bfb969b0","components/data/Card.jsx":"7036a285fd9d","components/data/FileUpload.jsx":"083de6acd4fe","components/data/ProgressBar.jsx":"10008e6ccb3c","components/data/StatTile.jsx":"b9e6bb019794","components/data/Table.jsx":"324ff1f9880d","components/data/Tabs.jsx":"cd8fd515ea54","components/feedback/Modal.jsx":"ad4853106c5e","components/feedback/Toast.jsx":"76f6ccca842c","components/feedback/Tooltip.jsx":"5aa0fab697ee","components/forms/Checkbox.jsx":"0361b380bef6","components/forms/Input.jsx":"414292d8296a","components/forms/Radio.jsx":"4344b147644d","components/forms/Select.jsx":"101f6e2f424c","components/forms/Switch.jsx":"0aaccd3a2bc0","components/navigation/Breadcrumb.jsx":"b4e31a88a7c2","slides/deck-stage.js":"9436a2deeb46","ui_kits/catalog/CatalogApp.jsx":"918408a1bcba","ui_kits/marketing/MarketingSite.jsx":"4780a5626e8c","ui_kits/portal/CreditsApp.jsx":"8c9f3e28ad75","ui_kits/portal/PortalApp.jsx":"3b2c6b8efd38","ui_kits/shop/ShopApp.jsx":"2b5606d98b7f"},"inlinedExternals":[],"unexposedExports":[{"name":"useLucide","sourcePath":"ui_kits/marketing/MarketingSite.jsx"}]} */

(() => {

const __ds_ns = (window.ChiptuningfileDesignSystem_3beb42 = window.ChiptuningfileDesignSystem_3beb42 || {});

const __ds_scope = {};

(__ds_ns.__errors = __ds_ns.__errors || []);

// components/core/Badge.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
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
function Badge({
  children,
  tone = 'neutral',
  solid = false,
  dot = false,
  ...rest
}) {
  const variant = solid ? 'solid' : tone;
  return /*#__PURE__*/React.createElement("span", _extends({
    className: `cdf-badge cdf-badge--${variant}`
  }, rest), dot && /*#__PURE__*/React.createElement("span", {
    className: "cdf-badge__dot",
    "aria-hidden": "true"
  }), children);
}
Object.assign(__ds_scope, { Badge });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Badge.jsx", error: String((e && e.message) || e) }); }

// components/core/Button.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
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
function Button({
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
  const cls = ['cdf-btn', `cdf-btn--${variant}`, `cdf-btn--${size}`, block ? 'cdf-btn--block' : ''].filter(Boolean).join(' ');
  return /*#__PURE__*/React.createElement("button", _extends({
    type: type,
    className: cls,
    disabled: disabled || loading
  }, rest), loading && /*#__PURE__*/React.createElement("span", {
    className: "cdf-btn__spin",
    "aria-hidden": "true"
  }), !loading && iconLeft, children, !loading && iconRight);
}
Object.assign(__ds_scope, { Button });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Button.jsx", error: String((e && e.message) || e) }); }

// components/core/IconButton.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const STYLE_ID = 'cdf-iconbutton-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-iconbtn{
    display:inline-flex;align-items:center;justify-content:center;cursor:pointer;
    border:var(--bw-hair) solid transparent;background:transparent;color:var(--text);
    border-radius:var(--radius-md);
    transition:background var(--dur-fast) var(--ease-out),color var(--dur-fast) var(--ease-out),border-color var(--dur-fast) var(--ease-out),transform var(--dur-fast) var(--ease-out);
  }
  .cdf-iconbtn:focus-visible{outline:var(--bw-thick) solid var(--focus-ring);outline-offset:2px;}
  .cdf-iconbtn:active{transform:scale(.94);}
  .cdf-iconbtn:disabled{opacity:.4;cursor:not-allowed;}
  .cdf-iconbtn--sm{width:34px;height:34px;}
  .cdf-iconbtn--md{width:42px;height:42px;}
  .cdf-iconbtn--lg{width:48px;height:48px;}
  .cdf-iconbtn--ghost:hover:not(:disabled){background:var(--surface-2);}
  .cdf-iconbtn--outline{border-color:var(--border-strong);background:var(--surface);}
  .cdf-iconbtn--outline:hover:not(:disabled){background:var(--surface-2);border-color:var(--carbon-400);}
  .cdf-iconbtn--solid{background:var(--accent);color:var(--text-on-accent);}
  .cdf-iconbtn--solid:hover:not(:disabled){background:var(--accent-hover);}
  .cdf-iconbtn svg,.cdf-iconbtn i{width:1.25em;height:1.25em;}
  `;
  document.head.appendChild(el);
}
function IconButton({
  children,
  variant = 'ghost',
  size = 'md',
  label,
  disabled = false,
  ...rest
}) {
  const cls = ['cdf-iconbtn', `cdf-iconbtn--${variant}`, `cdf-iconbtn--${size}`].join(' ');
  return /*#__PURE__*/React.createElement("button", _extends({
    type: "button",
    className: cls,
    "aria-label": label,
    title: label,
    disabled: disabled
  }, rest), children);
}
Object.assign(__ds_scope, { IconButton });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/IconButton.jsx", error: String((e && e.message) || e) }); }

// components/core/Logo.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
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
const SIZE_PX = {
  sm: 30,
  md: 38,
  lg: 48
};
function Logo({
  size = 'md',
  showWordmark = true,
  tagline = 'ECU Performance Files',
  solidMark = false,
  href = null,
  assetBase = '',
  ...rest
}) {
  const px = SIZE_PX[size] || 38;
  const src = `${assetBase}assets/${solidMark ? 'logo-mark-solid.svg' : 'logo-mark.svg'}`;
  const Tag = href ? 'a' : 'span';
  return /*#__PURE__*/React.createElement(Tag, _extends({
    className: `cdf-logo cdf-logo--${size}`,
    href: href || undefined
  }, rest), /*#__PURE__*/React.createElement("img", {
    className: "cdf-logo__mark",
    src: src,
    width: px,
    height: px,
    alt: "Chiptuningfile"
  }), showWordmark && /*#__PURE__*/React.createElement("span", {
    className: "cdf-logo__wm"
  }, /*#__PURE__*/React.createElement("span", {
    className: "cdf-logo__a"
  }, "Chip", /*#__PURE__*/React.createElement("em", null, "tuning"), "file"), tagline && /*#__PURE__*/React.createElement("span", {
    className: "cdf-logo__b"
  }, tagline)));
}
Object.assign(__ds_scope, { Logo });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Logo.jsx", error: String((e && e.message) || e) }); }

// components/core/Tag.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const STYLE_ID = 'cdf-tag-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-tag{
    display:inline-flex;align-items:center;gap:.45em;
    font-family:var(--font-sans);font-weight:var(--fw-medium);font-size:var(--text-sm);
    line-height:1;padding:.4em .7em;border-radius:var(--radius-pill);
    background:var(--surface-2);color:var(--text);border:var(--bw-hair) solid var(--border);
  }
  .cdf-tag--active{background:var(--accent-soft);color:var(--accent-text);border-color:var(--accent-soft-border);}
  .cdf-tag__x{display:inline-flex;align-items:center;justify-content:center;cursor:pointer;border:0;background:transparent;color:inherit;opacity:.6;padding:0;margin:-.2em -.25em -.2em 0;border-radius:var(--radius-pill);}
  .cdf-tag__x:hover{opacity:1;}
  .cdf-tag__x svg,.cdf-tag__x i{width:14px;height:14px;}
  `;
  document.head.appendChild(el);
}
function Tag({
  children,
  active = false,
  onRemove = null,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("span", _extends({
    className: `cdf-tag${active ? ' cdf-tag--active' : ''}`
  }, rest), children, onRemove && /*#__PURE__*/React.createElement("button", {
    type: "button",
    className: "cdf-tag__x",
    "aria-label": "Entfernen",
    onClick: onRemove
  }, /*#__PURE__*/React.createElement("i", {
    "data-lucide": "x"
  })));
}
Object.assign(__ds_scope, { Tag });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/core/Tag.jsx", error: String((e && e.message) || e) }); }

// components/data/Card.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
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
function Card({
  title,
  media,
  badge,
  footer,
  featured = false,
  interactive = false,
  children,
  className = '',
  ...rest
}) {
  const cls = ['cdf-card', featured ? 'cdf-card--featured' : '', interactive ? 'cdf-card--interactive' : '', className].filter(Boolean).join(' ');
  return /*#__PURE__*/React.createElement("div", _extends({
    className: cls
  }, rest), media && (typeof media === 'string' ? /*#__PURE__*/React.createElement("img", {
    className: "cdf-card__media",
    src: media,
    alt: ""
  }) : /*#__PURE__*/React.createElement("div", {
    className: "cdf-card__media"
  }, media)), /*#__PURE__*/React.createElement("div", {
    className: "cdf-card__body"
  }, (title || badge) && /*#__PURE__*/React.createElement("div", {
    className: "cdf-card__head"
  }, title && /*#__PURE__*/React.createElement("h3", {
    className: "cdf-card__title"
  }, title), badge), children), footer && /*#__PURE__*/React.createElement("div", {
    className: "cdf-card__foot"
  }, footer));
}
Object.assign(__ds_scope, { Card });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/Card.jsx", error: String((e && e.message) || e) }); }

// components/data/FileUpload.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const STYLE_ID = 'cdf-fileupload-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-drop{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:var(--space-3);
    text-align:center;padding:var(--space-7) var(--space-5);
    border:var(--bw-thick) dashed var(--border-strong);border-radius:var(--radius-lg);
    background:var(--surface-2);cursor:pointer;font-family:var(--font-sans);color:var(--text);
    transition:border-color var(--dur-fast) var(--ease-out),background var(--dur-fast) var(--ease-out);}
  .cdf-drop:hover,.cdf-drop--over{border-color:var(--accent);background:var(--accent-soft);}
  .cdf-drop__icon{width:44px;height:44px;border-radius:var(--radius-md);background:var(--surface);
    border:var(--bw-hair) solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--accent);}
  .cdf-drop__icon svg,.cdf-drop__icon i{width:22px;height:22px;}
  .cdf-drop__t{font-weight:var(--fw-semibold);}
  .cdf-drop__t em{font-style:normal;color:var(--accent-text);}
  .cdf-drop__sub{font-family:var(--font-mono);font-size:var(--text-2xs);letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);}
  .cdf-drop input{display:none;}
  `;
  document.head.appendChild(el);
}
function FileUpload({
  title = 'Datei hier ablegen',
  hint = 'BIN · ORI · MOD — max. 30 MB',
  accept,
  multiple = false,
  onFiles = () => {},
  ...rest
}) {
  const [over, setOver] = React.useState(false);
  const inputRef = React.useRef(null);
  const pick = () => inputRef.current && inputRef.current.click();
  const drop = e => {
    e.preventDefault();
    setOver(false);
    onFiles(Array.from(e.dataTransfer.files || []));
  };
  return /*#__PURE__*/React.createElement("div", _extends({
    className: `cdf-drop${over ? ' cdf-drop--over' : ''}`,
    role: "button",
    tabIndex: 0,
    onClick: pick,
    onKeyDown: e => (e.key === 'Enter' || e.key === ' ') && pick(),
    onDragOver: e => {
      e.preventDefault();
      setOver(true);
    },
    onDragLeave: () => setOver(false),
    onDrop: drop
  }, rest), /*#__PURE__*/React.createElement("span", {
    className: "cdf-drop__icon"
  }, /*#__PURE__*/React.createElement("i", {
    "data-lucide": "file-up"
  })), /*#__PURE__*/React.createElement("span", {
    className: "cdf-drop__t"
  }, /*#__PURE__*/React.createElement("em", null, "Ausw\xE4hlen"), " oder ", title), /*#__PURE__*/React.createElement("span", {
    className: "cdf-drop__sub"
  }, hint), /*#__PURE__*/React.createElement("input", {
    ref: inputRef,
    type: "file",
    accept: accept,
    multiple: multiple,
    onChange: e => onFiles(Array.from(e.target.files || []))
  }));
}
Object.assign(__ds_scope, { FileUpload });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/FileUpload.jsx", error: String((e && e.message) || e) }); }

// components/data/ProgressBar.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
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
function ProgressBar({
  value = 0,
  label,
  showValue = true,
  tone = 'accent',
  indeterminate = false,
  ...rest
}) {
  const pct = Math.max(0, Math.min(100, value));
  return /*#__PURE__*/React.createElement("div", _extends({
    className: "cdf-prog"
  }, rest), (label || showValue) && /*#__PURE__*/React.createElement("div", {
    className: "cdf-prog__top"
  }, /*#__PURE__*/React.createElement("span", null, label), showValue && !indeterminate && /*#__PURE__*/React.createElement("span", {
    className: "cdf-prog__val"
  }, pct, "%")), /*#__PURE__*/React.createElement("div", {
    className: "cdf-prog__track"
  }, /*#__PURE__*/React.createElement("div", {
    className: `cdf-prog__bar${tone === 'success' ? ' cdf-prog__bar--success' : ''}${indeterminate ? ' cdf-prog__bar--indeterminate' : ''}`,
    style: indeterminate ? undefined : {
      width: `${pct}%`
    }
  })));
}
Object.assign(__ds_scope, { ProgressBar });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/ProgressBar.jsx", error: String((e && e.message) || e) }); }

// components/data/StatTile.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
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
function StatTile({
  label,
  value,
  unit,
  delta,
  gain = false,
  dark = false,
  ...rest
}) {
  const cls = ['cdf-stat', gain ? 'cdf-stat--gain' : '', dark ? 'cdf-stat--dark' : ''].filter(Boolean).join(' ');
  const up = typeof delta === 'string' ? delta.trim().startsWith('+') : delta > 0;
  return /*#__PURE__*/React.createElement("div", _extends({
    className: cls
  }, rest), /*#__PURE__*/React.createElement("span", {
    className: "cdf-stat__k"
  }, label), /*#__PURE__*/React.createElement("span", {
    className: "cdf-stat__v"
  }, value, unit && /*#__PURE__*/React.createElement("span", {
    className: "u"
  }, unit)), delta != null && /*#__PURE__*/React.createElement("span", {
    className: `cdf-stat__delta cdf-stat__delta--${up ? 'up' : 'down'}`
  }, /*#__PURE__*/React.createElement("i", {
    "data-lucide": up ? 'trending-up' : 'trending-down'
  }), delta));
}
Object.assign(__ds_scope, { StatTile });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/StatTile.jsx", error: String((e && e.message) || e) }); }

// components/data/Table.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const STYLE_ID = 'cdf-table-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-table-wrap{border:var(--bw-hair) solid var(--border);border-radius:var(--radius-md);overflow:auto;background:var(--surface);}
  .cdf-table{width:100%;border-collapse:collapse;font-family:var(--font-sans);font-size:var(--text-sm);}
  .cdf-table thead th{font-family:var(--font-mono);font-size:var(--text-2xs);letter-spacing:.08em;
    text-transform:uppercase;color:var(--text-muted);text-align:left;font-weight:var(--fw-medium);
    padding:var(--space-3) var(--space-4);border-bottom:var(--bw-hair) solid var(--border);
    background:var(--surface-2);white-space:nowrap;}
  .cdf-table tbody td{padding:var(--space-3) var(--space-4);border-bottom:var(--bw-hair) solid var(--border);color:var(--text);vertical-align:middle;}
  .cdf-table tbody tr:last-child td{border-bottom:0;}
  .cdf-table tbody tr{transition:background var(--dur-fast) var(--ease-out);}
  .cdf-table--hover tbody tr:hover{background:var(--surface-2);}
  .cdf-table .num{font-family:var(--font-mono);font-variant-numeric:tabular-nums;text-align:right;}
  .cdf-table th.num{text-align:right;}
  `;
  document.head.appendChild(el);
}
function Table({
  columns = [],
  rows = [],
  hover = true,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "cdf-table-wrap"
  }, /*#__PURE__*/React.createElement("table", _extends({
    className: `cdf-table${hover ? ' cdf-table--hover' : ''}`
  }, rest), /*#__PURE__*/React.createElement("thead", null, /*#__PURE__*/React.createElement("tr", null, columns.map(c => /*#__PURE__*/React.createElement("th", {
    key: c.key,
    className: c.numeric ? 'num' : '',
    style: c.width ? {
      width: c.width
    } : undefined
  }, c.header)))), /*#__PURE__*/React.createElement("tbody", null, rows.map((r, i) => /*#__PURE__*/React.createElement("tr", {
    key: r.id ?? i
  }, columns.map(c => /*#__PURE__*/React.createElement("td", {
    key: c.key,
    className: c.numeric ? 'num' : ''
  }, typeof c.render === 'function' ? c.render(r) : r[c.key])))))));
}
Object.assign(__ds_scope, { Table });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/Table.jsx", error: String((e && e.message) || e) }); }

// components/data/Tabs.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
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
function Tabs({
  tabs = [],
  value,
  onChange = () => {},
  ...rest
}) {
  return /*#__PURE__*/React.createElement("div", _extends({
    className: "cdf-tabs",
    role: "tablist"
  }, rest), tabs.map(t => {
    const id = typeof t === 'string' ? t : t.id;
    const label = typeof t === 'string' ? t : t.label;
    const icon = typeof t === 'string' ? null : t.icon;
    const count = typeof t === 'string' ? null : t.count;
    return /*#__PURE__*/React.createElement("button", {
      key: id,
      role: "tab",
      "aria-selected": value === id,
      className: "cdf-tab",
      onClick: () => onChange(id)
    }, icon && /*#__PURE__*/React.createElement("i", {
      "data-lucide": icon
    }), label, count != null && /*#__PURE__*/React.createElement("span", {
      className: "cdf-tab__count"
    }, count));
  }));
}
Object.assign(__ds_scope, { Tabs });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/data/Tabs.jsx", error: String((e && e.message) || e) }); }

// components/feedback/Modal.jsx
try { (() => {
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
function Modal({
  open,
  onClose = () => {},
  title,
  size = 'md',
  footer,
  children
}) {
  React.useEffect(() => {
    if (!open) return undefined;
    const onKey = e => e.key === 'Escape' && onClose();
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [open, onClose]);
  if (!open) return null;
  return /*#__PURE__*/React.createElement("div", {
    className: "cdf-modal__scrim",
    onMouseDown: e => e.target === e.currentTarget && onClose()
  }, /*#__PURE__*/React.createElement("div", {
    className: `cdf-modal cdf-modal--${size}`,
    role: "dialog",
    "aria-modal": "true"
  }, /*#__PURE__*/React.createElement("div", {
    className: "cdf-modal__head"
  }, title && /*#__PURE__*/React.createElement("h2", {
    className: "cdf-modal__title"
  }, title), /*#__PURE__*/React.createElement("button", {
    className: "cdf-modal__x",
    "aria-label": "Schlie\xDFen",
    onClick: onClose
  }, /*#__PURE__*/React.createElement("i", {
    "data-lucide": "x"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "cdf-modal__body"
  }, children), footer && /*#__PURE__*/React.createElement("div", {
    className: "cdf-modal__foot"
  }, footer)));
}
Object.assign(__ds_scope, { Modal });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/feedback/Modal.jsx", error: String((e && e.message) || e) }); }

// components/feedback/Toast.jsx
try { (() => {
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
const ICON = {
  accent: 'info',
  success: 'check-circle',
  warning: 'alert-triangle',
  danger: 'x-circle'
};
function Toast({
  tone = 'accent',
  title,
  children,
  onClose
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: `cdf-toast cdf-toast--${tone}`,
    role: "status"
  }, /*#__PURE__*/React.createElement("span", {
    className: "cdf-toast__icon"
  }, /*#__PURE__*/React.createElement("i", {
    "data-lucide": ICON[tone] || 'info'
  })), /*#__PURE__*/React.createElement("div", {
    className: "cdf-toast__body"
  }, title && /*#__PURE__*/React.createElement("div", {
    className: "cdf-toast__title"
  }, title), children && /*#__PURE__*/React.createElement("div", {
    className: "cdf-toast__msg"
  }, children)), onClose && /*#__PURE__*/React.createElement("button", {
    className: "cdf-toast__x",
    "aria-label": "Schlie\xDFen",
    onClick: onClose
  }, /*#__PURE__*/React.createElement("i", {
    "data-lucide": "x"
  })));
}
Object.assign(__ds_scope, { Toast });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/feedback/Toast.jsx", error: String((e && e.message) || e) }); }

// components/feedback/Tooltip.jsx
try { (() => {
const STYLE_ID = 'cdf-tooltip-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-tip{position:relative;display:inline-flex;}
  .cdf-tip__pop{position:absolute;z-index:900;left:50%;transform:translateX(-50%) translateY(4px);
    bottom:calc(100% + 8px);opacity:0;pointer-events:none;
    background:var(--carbon-900);color:var(--carbon-50);border:var(--bw-hair) solid var(--carbon-700);
    font-family:var(--font-mono);font-size:var(--text-2xs);letter-spacing:.02em;line-height:1.3;
    padding:.45em .65em;border-radius:var(--radius-sm);white-space:nowrap;box-shadow:var(--shadow-md);
    transition:opacity var(--dur-fast) var(--ease-out),transform var(--dur-fast) var(--ease-out);}
  .cdf-tip__pop::after{content:"";position:absolute;top:100%;left:50%;transform:translateX(-50%);
    border:5px solid transparent;border-top-color:var(--carbon-900);}
  .cdf-tip:hover .cdf-tip__pop,.cdf-tip:focus-within .cdf-tip__pop{opacity:1;transform:translateX(-50%) translateY(0);}
  .cdf-tip--bottom .cdf-tip__pop{bottom:auto;top:calc(100% + 8px);transform:translateX(-50%) translateY(-4px);}
  .cdf-tip--bottom .cdf-tip__pop::after{top:auto;bottom:100%;border-top-color:transparent;border-bottom-color:var(--carbon-900);}
  .cdf-tip--bottom:hover .cdf-tip__pop{transform:translateX(-50%) translateY(0);}
  `;
  document.head.appendChild(el);
}
function Tooltip({
  label,
  placement = 'top',
  children
}) {
  return /*#__PURE__*/React.createElement("span", {
    className: `cdf-tip cdf-tip--${placement}`,
    tabIndex: 0
  }, children, /*#__PURE__*/React.createElement("span", {
    className: "cdf-tip__pop",
    role: "tooltip"
  }, label));
}
Object.assign(__ds_scope, { Tooltip });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/feedback/Tooltip.jsx", error: String((e && e.message) || e) }); }

// components/forms/Checkbox.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const STYLE_ID = 'cdf-checkbox-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-check{display:inline-flex;align-items:flex-start;gap:.6em;cursor:pointer;font-family:var(--font-sans);font-size:var(--text-md);color:var(--text);line-height:1.35;}
  .cdf-check input{position:absolute;opacity:0;width:0;height:0;}
  .cdf-check__box{flex:none;width:20px;height:20px;margin-top:1px;border-radius:var(--radius-sm);
    border:var(--bw-thick) solid var(--border-strong);background:var(--surface);
    display:inline-flex;align-items:center;justify-content:center;color:#fff;
    transition:background var(--dur-fast) var(--ease-out),border-color var(--dur-fast) var(--ease-out);}
  .cdf-check__box svg{width:14px;height:14px;opacity:0;transform:scale(.6);transition:all var(--dur-fast) var(--ease-snap);}
  .cdf-check input:checked+.cdf-check__box{background:var(--accent);border-color:var(--accent);}
  .cdf-check input:checked+.cdf-check__box svg{opacity:1;transform:scale(1);}
  .cdf-check input:focus-visible+.cdf-check__box{outline:var(--bw-thick) solid var(--focus-ring);outline-offset:2px;}
  .cdf-check--disabled{opacity:.5;cursor:not-allowed;}
  `;
  document.head.appendChild(el);
}
function Tick() {
  return /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: "3.5",
    strokeLinecap: "round",
    strokeLinejoin: "round"
  }, /*#__PURE__*/React.createElement("polyline", {
    points: "20 6 9 17 4 12"
  }));
}
function Checkbox({
  label,
  checked,
  disabled = false,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("label", {
    className: `cdf-check${disabled ? ' cdf-check--disabled' : ''}`
  }, /*#__PURE__*/React.createElement("input", _extends({
    type: "checkbox",
    checked: checked,
    disabled: disabled
  }, rest)), /*#__PURE__*/React.createElement("span", {
    className: "cdf-check__box"
  }, /*#__PURE__*/React.createElement(Tick, null)), label && /*#__PURE__*/React.createElement("span", null, label));
}
Object.assign(__ds_scope, { Checkbox });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/forms/Checkbox.jsx", error: String((e && e.message) || e) }); }

// components/forms/Input.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const STYLE_ID = 'cdf-input-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-field{display:flex;flex-direction:column;gap:6px;font-family:var(--font-sans);}
  .cdf-field__label{font-size:var(--text-sm);font-weight:var(--fw-semibold);color:var(--text);}
  .cdf-field__req{color:var(--accent);margin-left:.15em;}
  .cdf-field__hint{font-size:var(--text-xs);color:var(--text-muted);}
  .cdf-field__hint--err{color:var(--danger);}
  .cdf-input-wrap{display:flex;align-items:center;gap:.5em;background:var(--surface);
    border:var(--bw-hair) solid var(--border-strong);border-radius:var(--radius-md);
    padding:0 var(--space-3);height:42px;transition:border-color var(--dur-fast) var(--ease-out),box-shadow var(--dur-fast) var(--ease-out);}
  .cdf-input-wrap:focus-within{border-color:var(--accent);box-shadow:0 0 0 3px var(--focus-ring);}
  .cdf-input-wrap--err{border-color:var(--danger);}
  .cdf-input-wrap--err:focus-within{box-shadow:0 0 0 3px color-mix(in oklch,var(--danger) 35%,transparent);}
  .cdf-input-wrap--disabled{background:var(--surface-2);opacity:.6;}
  .cdf-input{flex:1;min-width:0;border:0;background:transparent;outline:none;
    font-family:var(--font-sans);font-size:var(--text-md);color:var(--text);height:100%;}
  .cdf-input::placeholder{color:var(--text-subtle);}
  .cdf-input-wrap .cdf-aff{color:var(--text-muted);display:inline-flex;font-family:var(--font-mono);font-size:var(--text-sm);}
  .cdf-input-wrap .cdf-aff svg,.cdf-input-wrap .cdf-aff i{width:18px;height:18px;}
  `;
  document.head.appendChild(el);
}
function Input({
  label,
  hint,
  error,
  required = false,
  disabled = false,
  prefix = null,
  suffix = null,
  id,
  ...rest
}) {
  const fieldId = id || (label ? `cdf-${label.replace(/\s+/g, '-').toLowerCase()}` : undefined);
  return /*#__PURE__*/React.createElement("div", {
    className: "cdf-field"
  }, label && /*#__PURE__*/React.createElement("label", {
    className: "cdf-field__label",
    htmlFor: fieldId
  }, label, required && /*#__PURE__*/React.createElement("span", {
    className: "cdf-field__req"
  }, "*")), /*#__PURE__*/React.createElement("div", {
    className: `cdf-input-wrap${error ? ' cdf-input-wrap--err' : ''}${disabled ? ' cdf-input-wrap--disabled' : ''}`
  }, prefix && /*#__PURE__*/React.createElement("span", {
    className: "cdf-aff"
  }, prefix), /*#__PURE__*/React.createElement("input", _extends({
    id: fieldId,
    className: "cdf-input",
    disabled: disabled,
    "aria-invalid": !!error
  }, rest)), suffix && /*#__PURE__*/React.createElement("span", {
    className: "cdf-aff"
  }, suffix)), (error || hint) && /*#__PURE__*/React.createElement("span", {
    className: `cdf-field__hint${error ? ' cdf-field__hint--err' : ''}`
  }, error || hint));
}
Object.assign(__ds_scope, { Input });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/forms/Input.jsx", error: String((e && e.message) || e) }); }

// components/forms/Radio.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const STYLE_ID = 'cdf-radio-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-radio{display:inline-flex;align-items:flex-start;gap:.6em;cursor:pointer;font-family:var(--font-sans);font-size:var(--text-md);color:var(--text);line-height:1.35;}
  .cdf-radio input{position:absolute;opacity:0;width:0;height:0;}
  .cdf-radio__dot{flex:none;width:20px;height:20px;margin-top:1px;border-radius:50%;
    border:var(--bw-thick) solid var(--border-strong);background:var(--surface);
    display:inline-flex;align-items:center;justify-content:center;
    transition:border-color var(--dur-fast) var(--ease-out);}
  .cdf-radio__dot::after{content:"";width:10px;height:10px;border-radius:50%;background:var(--accent);transform:scale(0);transition:transform var(--dur-fast) var(--ease-snap);}
  .cdf-radio input:checked+.cdf-radio__dot{border-color:var(--accent);}
  .cdf-radio input:checked+.cdf-radio__dot::after{transform:scale(1);}
  .cdf-radio input:focus-visible+.cdf-radio__dot{outline:var(--bw-thick) solid var(--focus-ring);outline-offset:2px;}
  .cdf-radio--disabled{opacity:.5;cursor:not-allowed;}
  `;
  document.head.appendChild(el);
}
function Radio({
  label,
  name,
  value,
  checked,
  disabled = false,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("label", {
    className: `cdf-radio${disabled ? ' cdf-radio--disabled' : ''}`
  }, /*#__PURE__*/React.createElement("input", _extends({
    type: "radio",
    name: name,
    value: value,
    checked: checked,
    disabled: disabled
  }, rest)), /*#__PURE__*/React.createElement("span", {
    className: "cdf-radio__dot"
  }), label && /*#__PURE__*/React.createElement("span", null, label));
}
Object.assign(__ds_scope, { Radio });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/forms/Radio.jsx", error: String((e && e.message) || e) }); }

// components/forms/Select.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const STYLE_ID = 'cdf-select-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-select-wrap{position:relative;display:flex;align-items:center;background:var(--surface);
    border:var(--bw-hair) solid var(--border-strong);border-radius:var(--radius-md);height:42px;
    transition:border-color var(--dur-fast) var(--ease-out),box-shadow var(--dur-fast) var(--ease-out);}
  .cdf-select-wrap:focus-within{border-color:var(--accent);box-shadow:0 0 0 3px var(--focus-ring);}
  .cdf-select-wrap--disabled{background:var(--surface-2);opacity:.6;}
  .cdf-select{appearance:none;-webkit-appearance:none;border:0;background:transparent;outline:none;
    font-family:var(--font-sans);font-size:var(--text-md);color:var(--text);
    height:100%;width:100%;padding:0 38px 0 var(--space-3);cursor:pointer;}
  .cdf-select:invalid{color:var(--text-subtle);}
  .cdf-select-wrap__chev{position:absolute;right:12px;pointer-events:none;color:var(--text-muted);display:inline-flex;}
  .cdf-select-wrap__chev svg,.cdf-select-wrap__chev i{width:18px;height:18px;}
  `;
  document.head.appendChild(el);
}
function Select({
  label,
  hint,
  error,
  required = false,
  disabled = false,
  placeholder,
  options = [],
  children,
  id,
  value,
  ...rest
}) {
  const fieldId = id || (label ? `cdf-sel-${label.replace(/\s+/g, '-').toLowerCase()}` : undefined);
  return /*#__PURE__*/React.createElement("div", {
    className: "cdf-field"
  }, label && /*#__PURE__*/React.createElement("label", {
    className: "cdf-field__label",
    htmlFor: fieldId
  }, label, required && /*#__PURE__*/React.createElement("span", {
    className: "cdf-field__req"
  }, "*")), /*#__PURE__*/React.createElement("div", {
    className: `cdf-select-wrap${disabled ? ' cdf-select-wrap--disabled' : ''}`
  }, /*#__PURE__*/React.createElement("select", _extends({
    id: fieldId,
    className: "cdf-select",
    disabled: disabled,
    required: required,
    defaultValue: value === undefined ? '' : undefined,
    value: value
  }, rest), placeholder && /*#__PURE__*/React.createElement("option", {
    value: "",
    disabled: true
  }, placeholder), options.map(o => {
    const val = typeof o === 'string' ? o : o.value;
    const lbl = typeof o === 'string' ? o : o.label;
    return /*#__PURE__*/React.createElement("option", {
      key: val,
      value: val
    }, lbl);
  }), children), /*#__PURE__*/React.createElement("span", {
    className: "cdf-select-wrap__chev"
  }, /*#__PURE__*/React.createElement("i", {
    "data-lucide": "chevron-down"
  }))), (error || hint) && /*#__PURE__*/React.createElement("span", {
    className: `cdf-field__hint${error ? ' cdf-field__hint--err' : ''}`
  }, error || hint));
}
Object.assign(__ds_scope, { Select });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/forms/Select.jsx", error: String((e && e.message) || e) }); }

// components/forms/Switch.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const STYLE_ID = 'cdf-switch-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-switch{display:inline-flex;align-items:center;gap:.65em;cursor:pointer;font-family:var(--font-sans);font-size:var(--text-md);color:var(--text);}
  .cdf-switch input{position:absolute;opacity:0;width:0;height:0;}
  .cdf-switch__track{flex:none;width:42px;height:24px;border-radius:var(--radius-pill);
    background:var(--carbon-300);position:relative;transition:background var(--dur-base) var(--ease-out);}
  .cdf-switch__thumb{position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;
    background:#fff;box-shadow:var(--shadow-sm);transition:transform var(--dur-base) var(--ease-snap);}
  .cdf-switch input:checked+.cdf-switch__track{background:var(--accent);}
  .cdf-switch input:checked+.cdf-switch__track .cdf-switch__thumb{transform:translateX(18px);}
  .cdf-switch input:focus-visible+.cdf-switch__track{outline:var(--bw-thick) solid var(--focus-ring);outline-offset:2px;}
  .cdf-switch--disabled{opacity:.5;cursor:not-allowed;}
  `;
  document.head.appendChild(el);
}
function Switch({
  label,
  checked,
  disabled = false,
  ...rest
}) {
  return /*#__PURE__*/React.createElement("label", {
    className: `cdf-switch${disabled ? ' cdf-switch--disabled' : ''}`
  }, /*#__PURE__*/React.createElement("input", _extends({
    type: "checkbox",
    role: "switch",
    checked: checked,
    disabled: disabled
  }, rest)), /*#__PURE__*/React.createElement("span", {
    className: "cdf-switch__track"
  }, /*#__PURE__*/React.createElement("span", {
    className: "cdf-switch__thumb"
  })), label && /*#__PURE__*/React.createElement("span", null, label));
}
Object.assign(__ds_scope, { Switch });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/forms/Switch.jsx", error: String((e && e.message) || e) }); }

// components/navigation/Breadcrumb.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
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
function Breadcrumb({
  items = [],
  ...rest
}) {
  return /*#__PURE__*/React.createElement("nav", _extends({
    className: "cdf-bc",
    "aria-label": "Breadcrumb"
  }, rest), items.map((it, i) => {
    const last = i === items.length - 1;
    const label = typeof it === 'string' ? it : it.label;
    const href = typeof it === 'string' ? null : it.href;
    return /*#__PURE__*/React.createElement(React.Fragment, {
      key: i
    }, last ? /*#__PURE__*/React.createElement("span", {
      className: "cdf-bc__current",
      "aria-current": "page"
    }, label) : href ? /*#__PURE__*/React.createElement("a", {
      href: href
    }, label) : /*#__PURE__*/React.createElement("span", null, label), !last && /*#__PURE__*/React.createElement("span", {
      className: "cdf-bc__sep"
    }, /*#__PURE__*/React.createElement("i", {
      "data-lucide": "chevron-right"
    })));
  }));
}
Object.assign(__ds_scope, { Breadcrumb });
})(); } catch (e) { __ds_ns.__errors.push({ path: "components/navigation/Breadcrumb.jsx", error: String((e && e.message) || e) }); }

// slides/deck-stage.js
try { (() => {
// @ds-adherence-ignore -- omelette starter scaffold (raw elements/hex/px by design)
/* BEGIN USAGE */
/**
 * <deck-stage> — reusable web component for HTML decks.
 *
 * Handles:
 *  (a) speaker notes — reads <script type="application/json" id="speaker-notes">
 *      and posts {slideIndexChanged: N} to the parent window on nav.
 *  (b) keyboard navigation — ←/→, PgUp/PgDn, Space, Home/End, number keys.
 *      On touch devices, tapping the left/right half of the stage goes
 *      prev/next — taps on links, buttons and other interactive slide
 *      content are left alone.
 *  (c) press R to reset to slide 0 (with a tasteful keyboard hint).
 *  (d) bottom-center overlay showing slide count + hints, fades out on idle.
 *  (e) auto-scaling — inner canvas is a fixed design size (default 1920×1080)
 *      scaled with `transform: scale()` to fit the viewport, letterboxed.
 *      Set the `noscale` attribute to render at authored size (1:1) — the
 *      PPTX exporter sets this so its DOM capture sees unscaled geometry.
 *  (f) print — `@media print` lays every slide out as its own page at the
 *      design size, so the browser's Print → Save as PDF produces a clean
 *      one-page-per-slide PDF with no extra setup.
 *  (g) thumbnail rail — resizable left-hand column of per-slide thumbnails
 *      (static clones). Click to navigate; ↑/↓ with a thumbnail focused to
 *      step between slides; drag to reorder; right-click for
 *      Skip / Move up / Move down / Duplicate / Delete (Delete opens a
 *      Cancel/Delete confirm dialog). Drag the rail's right edge to resize;
 *      width persists to
 *      localStorage. Skipped slides carry `data-deck-skip`, are dimmed in
 *      the rail, omitted from prev/next navigation, and hidden at print.
 *      The rail is suppressed in presenting mode, in the host's Preview
 *      mode (ViewerMode='none'), on `noscale`, on narrow viewports
 *      (≤640px), and via the `no-rail` attribute. Rail mutations dispatch
 *      a `deckchange`
 *      CustomEvent on the element: detail = {action, from, to, slide}.
 *
 * Slides are HIDDEN, not unmounted. Non-active slides stay in the DOM with
 * `visibility: hidden` + `opacity: 0`, so their state (videos, iframes,
 * form inputs, React trees) is preserved across navigation.
 *
 * Lifecycle event — the component dispatches a `slidechange` CustomEvent on
 * itself whenever the active slide changes (including the initial mount).
 * The event bubbles and composes out of shadow DOM, so you can listen on
 * the <deck-stage> element or on document:
 *
 *   document.querySelector('deck-stage').addEventListener('slidechange', (e) => {
 *     e.detail.index         // new 0-based index
 *     e.detail.previousIndex // previous index, or -1 on init
 *     e.detail.total         // total slide count
 *     e.detail.slide         // the new active slide element
 *     e.detail.previousSlide // the prior slide element, or null on init
 *     e.detail.reason        // 'init' | 'keyboard' | 'click' | 'tap' | 'api'
 *   });
 *
 * Persistence: none at the deck level. The host app keeps the current slide
 * in its own URL (?slide=) and re-delivers it via location.hash on load, so a
 * bare load with no hash always starts at slide 1.
 *
 * Usage:
 *   <style>deck-stage:not(:defined){visibility:hidden}</style>
 *   <deck-stage width="1920" height="1080">
 *     <section data-label="Title">...</section>
 *     <section data-label="Agenda">...</section>
 *   </deck-stage>
 *   <script src="deck-stage.js"></script>
 *
 * The :not(:defined) rule prevents a flash of the first slide at its
 * authored styles before this script runs and attaches the shadow root.
 *
 * Slides are the direct element children of <deck-stage>. Each slide is
 * automatically tagged with:
 *   - data-screen-label="NN Label"   (1-indexed, for comment flow)
 *   - data-om-validate="no_overflowing_text,no_overlapping_text,slide_sized_text"
 *
 * Speaker notes stay in sync because the component posts {slideIndexChanged: N}
 * to the parent — just include the #speaker-notes script tag if asked for notes.
 *
 * Authoring guidance:
 *   - Write slide bodies as static HTML inside <deck-stage>, with sizing via
 *     CSS custom properties in a <style> block rather than JS constants.
 *     Static slide markup is what lets the user click a heading in edit mode
 *     and retype it directly; a slide rendered through <script type="text/babel">,
 *     React, or a loop over a JS array has to round-trip every tweak through a
 *     chat message instead. Reach for script-generated slides only when the
 *     content genuinely needs interactive behaviour static HTML can't express.
 *   - Do NOT set position/inset/width/height on the slide <section> elements —
 *     the component absolutely positions every slotted child for you.
 *   - Entrance animations: make the visible end-state the base style and
 *     animate *from* hidden, so print and reduced-motion show content.
 *     Gate the animation on [data-deck-active] and the motion query, e.g.
 *     `@media (prefers-reduced-motion:no-preference){ [data-deck-active] .x{animation:fade-in .5s both} }`.
 *     Avoid infinite decorative loops on slide content.
 */
/* END USAGE */

(() => {
  const DESIGN_W_DEFAULT = 1920;
  const DESIGN_H_DEFAULT = 1080;
  const OVERLAY_HIDE_MS = 1800;
  const VALIDATE_ATTR = 'no_overflowing_text,no_overlapping_text,slide_sized_text';
  const FINE_POINTER_MQ = matchMedia('(hover: hover) and (pointer: fine)');
  const NARROW_MQ = matchMedia('(max-width: 640px)');
  // Slide-authored controls that should keep a tap instead of it navigating.
  const INTERACTIVE_SEL = 'a[href], button, input, select, textarea, summary, label, video[controls], audio[controls], [role="button"], [onclick], [tabindex]:not([tabindex^="-"]), [contenteditable]:not([contenteditable="false" i])';
  const pad2 = n => String(n).padStart(2, '0');

  // Label precedence: data-label → data-screen-label (number stripped) → first heading → "Slide".
  const getSlideLabel = el => {
    const explicit = el.getAttribute('data-label');
    if (explicit) return explicit;
    const existing = el.getAttribute('data-screen-label');
    if (existing) return existing.replace(/^\s*\d+\s*/, '').trim() || existing;
    const h = el.querySelector('h1, h2, h3, [data-title]');
    const t = h && (h.textContent || '').trim().slice(0, 40);
    if (t) return t;
    return 'Slide';
  };
  const stylesheet = `
    :host {
      position: fixed;
      inset: 0;
      display: block;
      background: #000;
      color: #fff;
      font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", Helvetica, Arial, sans-serif;
      overflow: hidden;
      -webkit-tap-highlight-color: transparent;
    }
    /* connectedCallback holds this until document.fonts.ready (capped 2s) so
     * the first visible paint has the deck's real typography + final rail
     * layout. opacity (not visibility) so the active slide can't un-hide
     * itself via the ::slotted([data-deck-active]) visibility:visible rule.
     * Only the stage/rail hide — the black :host background stays, so the
     * iframe doesn't flash the page's default white. */
    :host([data-fonts-pending]) .stage,
    :host([data-fonts-pending]) .rail { opacity: 0; pointer-events: none; }

    .stage {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .canvas {
      position: relative;
      transform-origin: center center;
      flex-shrink: 0;
      background: #fff;
      will-change: transform;
    }

    /* Slides live in light DOM (via <slot>) so authored CSS still applies.
       We absolutely position each slotted child to stack them. */
    ::slotted(*) {
      position: absolute !important;
      inset: 0 !important;
      width: 100% !important;
      height: 100% !important;
      box-sizing: border-box !important;
      overflow: hidden;
      opacity: 0;
      pointer-events: none;
      visibility: hidden;
    }
    ::slotted([data-deck-active]) {
      opacity: 1;
      pointer-events: auto;
      visibility: visible;
    }

    .overlay {
      position: fixed;
      left: 50%;
      bottom: 22px;
      transform: translate(-50%, 6px) scale(0.92);
      filter: blur(6px);
      display: flex;
      align-items: center;
      gap: 4px;
      padding: 4px;
      background: #000;
      color: #fff;
      border-radius: 999px;
      font-size: 12px;
      font-feature-settings: "tnum" 1;
      letter-spacing: 0.01em;
      opacity: 0;
      pointer-events: none;
      transition: opacity 260ms ease, transform 260ms cubic-bezier(.2,.8,.2,1), filter 260ms ease;
      transform-origin: center bottom;
      z-index: 2147483000;
      user-select: none;
    }
    .overlay[data-visible] {
      opacity: 1;
      pointer-events: auto;
      transform: translate(-50%, 0) scale(1);
      filter: blur(0);
    }

    .btn {
      appearance: none;
      -webkit-appearance: none;
      background: transparent;
      border: 0;
      margin: 0;
      padding: 0;
      color: inherit;
      font: inherit;
      cursor: default;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      height: 28px;
      min-width: 28px;
      border-radius: 999px;
      color: rgba(255,255,255,0.72);
      transition: background 140ms ease, color 140ms ease;
      -webkit-tap-highlight-color: transparent;
    }
    .btn:hover { background: rgba(255,255,255,0.12); color: #fff; }
    .btn:active { background: rgba(255,255,255,0.18); }
    .btn:focus { outline: none; }
    .btn:focus-visible { outline: none; }
    .btn::-moz-focus-inner { border: 0; }
    .btn svg { width: 14px; height: 14px; display: block; }
    .btn.reset {
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.02em;
      padding: 0 10px 0 12px;
      gap: 6px;
      color: rgba(255,255,255,0.72);
    }
    .btn.reset .kbd {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 16px;
      height: 16px;
      padding: 0 4px;
      font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
      font-size: 10px;
      line-height: 1;
      color: rgba(255,255,255,0.88);
      background: rgba(255,255,255,0.12);
      border-radius: 4px;
    }

    .count {
      font-variant-numeric: tabular-nums;
      color: #fff;
      font-weight: 500;
      padding: 0 8px;
      min-width: 42px;
      text-align: center;
      font-size: 12px;
    }
    .count .sep { color: rgba(255,255,255,0.45); margin: 0 3px; font-weight: 400; }
    .count .total { color: rgba(255,255,255,0.55); }

    .divider {
      width: 1px;
      height: 14px;
      background: rgba(255,255,255,0.18);
      margin: 0 2px;
    }

    /* ── Thumbnail rail ──────────────────────────────────────────────────
       Fixed column on the left; each thumbnail is a static deep-clone of
       the light-DOM slide scaled into a 16:9 (or design-aspect) frame. The
       stage re-fits around it (see _fit); hidden during present / noscale
       / print so capture geometry and fullscreen output are unchanged. */
    .rail {
      position: fixed;
      left: 0;
      top: 0;
      bottom: 0;
      width: var(--deck-rail-w, 188px);
      background: #141414;
      border-right: 1px solid rgba(255,255,255,0.08);
      overflow-y: auto;
      overflow-x: hidden;
      padding: 12px 10px;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      gap: 12px;
      z-index: 2147482500;
      scrollbar-width: thin;
      scrollbar-color: rgba(255,255,255,0.18) transparent;
    }
    .rail::-webkit-scrollbar { width: 8px; }
    .rail::-webkit-scrollbar-track { background: transparent; margin: 2px; }
    .rail::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,0.18);
      border-radius: 4px;
      border: 2px solid transparent;
      background-clip: content-box;
    }
    .rail::-webkit-scrollbar-thumb:hover {
      background: rgba(255,255,255,0.28);
      border: 2px solid transparent;
      background-clip: content-box;
    }
    :host([no-rail]) .rail,
    :host([noscale]) .rail { display: none; }
    .rail[data-presenting] { display: none; }
    @media (max-width: 640px) {
      .rail, .rail-resize { display: none; }
    }
    /* User-driven show/hide (the TweaksPanel toggle) slides instead of
       popping. Transitions are gated on :host([data-rail-anim]) — set only
       for the 200ms around the toggle — so window-resize and rail-width
       drag (which also call _fit) don't lag behind the cursor. */
    .rail[data-user-hidden] { transform: translateX(-100%); }
    :host([data-rail-anim]) .rail { transition: transform 200ms cubic-bezier(.3,.7,.4,1); }
    :host([data-rail-anim]) .stage { transition: left 200ms cubic-bezier(.3,.7,.4,1); }
    :host([data-rail-anim]) .canvas { transition: transform 200ms cubic-bezier(.3,.7,.4,1); }
    /* transition shorthand replaces rather than merges — repeat the base
       .overlay opacity/transform/filter transitions so visibility changes
       during the 200ms toggle window still fade instead of popping. */
    :host([data-rail-anim]) .overlay {
      transition: margin-left 200ms cubic-bezier(.3,.7,.4,1),
                  opacity 260ms ease,
                  transform 260ms cubic-bezier(.2,.8,.2,1),
                  filter 260ms ease;
    }

    .thumb {
      position: relative;
      display: flex;
      align-items: flex-start;
      gap: 8px;
      cursor: pointer;
      user-select: none;
    }
    .thumb .num {
      width: 16px;
      flex-shrink: 0;
      font-size: 11px;
      font-weight: 500;
      text-align: right;
      color: rgba(255,255,255,0.55);
      padding-top: 2px;
      font-variant-numeric: tabular-nums;
    }
    .thumb .frame {
      position: relative;
      flex: 1;
      min-width: 0;
      aspect-ratio: var(--deck-aspect);
      background: #fff;
      border-radius: 4px;
      outline: 2px solid transparent;
      outline-offset: 0;
      overflow: hidden;
      transition: outline-color 120ms ease;
    }
    .thumb:hover .frame { outline-color: rgba(255,255,255,0.25); }
    .thumb { outline: none; }
    .thumb:focus-visible .frame { outline-color: rgba(255,255,255,0.5); }
    .thumb[data-current] .num { color: #fff; }
    .thumb[data-current] .frame { outline-color: #D97757; }
    .thumb[data-dragging] { opacity: 0.35; }
    .thumb::before {
      content: '';
      position: absolute;
      left: 24px;
      right: 0;
      height: 3px;
      border-radius: 2px;
      background: #D97757;
      opacity: 0;
      pointer-events: none;
    }
    .thumb[data-drop="before"]::before { top: -8px; opacity: 1; }
    .thumb[data-drop="after"]::before { bottom: -8px; opacity: 1; }
    .thumb[data-skip] .frame { opacity: 0.35; }
    .thumb[data-skip] .frame::after {
      content: 'Skipped';
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0,0,0,0.45);
      color: #fff;
      font-size: 10px;
      font-weight: 500;
      letter-spacing: 0.04em;
    }

    .ctxmenu {
      position: fixed;
      min-width: 150px;
      padding: 4px;
      background: #242424;
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 7px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.45);
      z-index: 2147483100;
      display: none;
      font-size: 12px;
    }
    .ctxmenu[data-open] { display: block; }
    .ctxmenu button {
      display: block;
      width: 100%;
      appearance: none;
      border: 0;
      background: transparent;
      color: #e8e8e8;
      font: inherit;
      text-align: left;
      padding: 6px 10px;
      border-radius: 4px;
      cursor: pointer;
    }
    .ctxmenu button:hover:not(:disabled) { background: rgba(255,255,255,0.08); }
    .ctxmenu button:disabled { opacity: 0.35; cursor: default; }
    .ctxmenu hr {
      border: 0;
      border-top: 1px solid rgba(255,255,255,0.1);
      margin: 4px 2px;
    }

    .rail-resize {
      position: fixed;
      left: calc(var(--deck-rail-w, 188px) - 3px);
      top: 0;
      bottom: 0;
      width: 6px;
      cursor: col-resize;
      z-index: 2147482600;
      touch-action: none;
    }
    .rail-resize:hover,
    .rail-resize[data-dragging] { background: rgba(255,255,255,0.12); }
    :host([no-rail]) .rail-resize,
    :host([noscale]) .rail-resize,
    .rail[data-presenting] + .rail-resize,
    .rail[data-user-hidden] + .rail-resize { display: none; }

    /* Delete-confirm popup — matches the SPA's ConfirmDialog layout
       (title + message body, depressed footer with Cancel / Delete). */
    .confirm-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.45);
      z-index: 2147483200;
      display: none;
      align-items: center;
      justify-content: center;
    }
    .confirm-backdrop[data-open] { display: flex; }
    .confirm {
      width: 320px;
      max-width: calc(100vw - 32px);
      background: #2a2a2a;
      color: #e8e8e8;
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 12px;
      box-shadow: 0 12px 32px rgba(0,0,0,0.5);
      overflow: hidden;
      font-family: inherit;
      animation: deck-confirm-in 0.18s ease;
    }
    @keyframes deck-confirm-in {
      from { opacity: 0; transform: scale(0.96); }
      to { opacity: 1; transform: scale(1); }
    }
    .confirm .body { padding: 20px 20px 16px; }
    .confirm .title { font-size: 14px; font-weight: 600; margin-bottom: 4px; }
    .confirm .msg { font-size: 13px; line-height: 1.5; color: rgba(255,255,255,0.65); }
    .confirm .footer {
      padding: 14px 20px;
      background: #1f1f1f;
      border-top: 1px solid rgba(255,255,255,0.08);
      display: flex;
      justify-content: flex-end;
      gap: 8px;
    }
    .confirm button {
      appearance: none;
      font: inherit;
      font-size: 13px;
      font-weight: 500;
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
    }
    .confirm .cancel {
      background: transparent;
      border: 0;
      color: rgba(255,255,255,0.8);
    }
    .confirm .cancel:hover { background: rgba(255,255,255,0.08); }
    .confirm .danger {
      background: #c96442;
      border: 1px solid rgba(0,0,0,0.15);
      color: #fff;
      box-shadow: 0 1px 3px rgba(166,50,68,0.3), 0 2px 6px rgba(166,50,68,0.18);
    }
    .confirm .danger:hover { background: #b5563a; }

    /* ── Print: one page per slide, no chrome ────────────────────────────
       The screen layout stacks every slide at inset:0 inside a scaled
       canvas; for print we want them in document flow at the authored
       design size so the browser paginates one slide per sheet. The
       @page size is set from the width/height attributes via the inline
       <style id="deck-stage-print-page"> that connectedCallback injects
       into <head> (the @page at-rule has no effect inside shadow DOM). */
    @media print {
      :host {
        position: static;
        inset: auto;
        background: none;
        overflow: visible;
        color: inherit;
      }
      .stage { position: static; display: block; }
      .canvas {
        transform: none !important;
        width: auto !important;
        height: auto !important;
        background: none;
        will-change: auto;
      }
      ::slotted(*) {
        position: relative !important;
        inset: auto !important;
        width: var(--deck-design-w) !important;
        height: var(--deck-design-h) !important;
        box-sizing: border-box !important;
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto;
        break-after: page;
        page-break-after: always;
        break-inside: avoid;
        overflow: hidden;
      }
      /* :last-child alone isn't enough once data-deck-skip hides the
         trailing slide(s) — the last *visible* slide still carries
         break-after:page and prints a blank sheet. _markLastVisible()
         maintains data-deck-last-visible on the last non-skipped slide. */
      ::slotted(*:last-child),
      ::slotted([data-deck-last-visible]) {
        break-after: auto;
        page-break-after: auto;
      }
      ::slotted([data-deck-skip]) { display: none !important; }
      .overlay, .rail, .rail-resize, .ctxmenu, .confirm-backdrop { display: none !important; }
    }
  `;
  class DeckStage extends HTMLElement {
    static get observedAttributes() {
      return ['width', 'height', 'noscale', 'no-rail'];
    }
    constructor() {
      super();
      this._root = this.attachShadow({
        mode: 'open'
      });
      this._index = 0;
      this._slides = [];
      this._notes = [];
      this._hideTimer = null;
      this._mouseIdleTimer = null;
      this._menuIndex = -1;
      this._onKey = this._onKey.bind(this);
      this._onResize = this._onResize.bind(this);
      this._onSlotChange = this._onSlotChange.bind(this);
      this._onMouseMove = this._onMouseMove.bind(this);
      this._onTap = this._onTap.bind(this);
      this._onMessage = this._onMessage.bind(this);
      // Capture-phase close so a click anywhere dismisses the menu, but
      // ignore clicks that land inside the menu itself — otherwise the
      // capture handler runs before the menu's own (bubble) handler and
      // clears _menuIndex out from under it.
      this._onDocClick = e => {
        if (this._menu && e.composedPath && e.composedPath().includes(this._menu)) return;
        this._closeMenu();
      };
    }
    get designWidth() {
      return parseInt(this.getAttribute('width'), 10) || DESIGN_W_DEFAULT;
    }
    get designHeight() {
      return parseInt(this.getAttribute('height'), 10) || DESIGN_H_DEFAULT;
    }
    connectedCallback() {
      // Presenter-view popup loads deckUrl?_snthumb=...#N for its prev/cur/
      // next thumbnails — the rail has no business rendering inside those
      // (wrong scale, and it offsets the stage so the thumb shows a gutter).
      if (/[?&]_snthumb=/.test(location.search)) this.setAttribute('no-rail', '');
      this._render();
      this._loadNotes();
      this._syncPrintPageRule();
      window.addEventListener('keydown', this._onKey);
      window.addEventListener('resize', this._onResize);
      window.addEventListener('mousemove', this._onMouseMove, {
        passive: true
      });
      window.addEventListener('message', this._onMessage);
      window.addEventListener('click', this._onDocClick, true);
      this.addEventListener('click', this._onTap);
      // Print lays every slide out as its own page, so [data-deck-active]-
      // gated entrance styles need the attribute on every slide (not just
      // the current one) or their content prints at the hidden base style.
      // The transient freeze style lands BEFORE the attributes so any
      // attribute-keyed transition fires at 0s (changing transition-
      // duration after a transition has started doesn't affect it).
      this._onBeforePrint = () => {
        if (this._freezeStyle) this._freezeStyle.remove();
        this._freezeStyle = document.createElement('style');
        this._freezeStyle.textContent = '*,*::before,*::after{transition-duration:0s !important}';
        document.head.appendChild(this._freezeStyle);
        this._slides.forEach(s => s.setAttribute('data-deck-active', ''));
      };
      this._onAfterPrint = () => {
        this._applyIndex({
          showOverlay: false,
          broadcast: false
        });
        if (this._freezeStyle) {
          this._freezeStyle.remove();
          this._freezeStyle = null;
        }
      };
      window.addEventListener('beforeprint', this._onBeforePrint);
      window.addEventListener('afterprint', this._onAfterPrint);
      // Initial collection + layout happens via slotchange, which fires on mount.
      this._enableRail();
      // Hold the stage hidden until webfonts are ready so the first visible
      // paint has the deck's real typography — the :not(:defined) guard in
      // the page HTML only covers custom-element upgrade, not font load.
      // Capped so a 404'd font URL can't blank the deck indefinitely.
      this.setAttribute('data-fonts-pending', '');
      const reveal = () => this.removeAttribute('data-fonts-pending');
      // rAF first: fonts.ready is a pre-resolved promise until layout has
      // resolved the slotted text's font-family and pushed a FontFace into
      // 'loading'. Reading it here in connectedCallback (parse-time) would
      // settle the race in a microtask before any font fetch starts.
      requestAnimationFrame(() => {
        Promise.race([document.fonts ? document.fonts.ready : Promise.resolve(), new Promise(r => setTimeout(r, 2000))]).then(reveal, reveal);
      });
    }
    _enableRail() {
      // Idempotent — older host builds still post __omelette_rail_enabled.
      // no-rail guard keeps the observers/stylesheet walk off the cheap path
      // for presenter-popup thumbnail iframes (up to 9 per view).
      if (this._railEnabled || this.hasAttribute('no-rail')) return;
      this._railEnabled = true;
      // Per-viewer preference — restored alongside rail width. Default on;
      // only a stored '0' (from the TweaksPanel toggle) hides it.
      this._railVisible = true;
      try {
        if (localStorage.getItem('deck-stage.railVisible') === '0') this._railVisible = false;
      } catch (e) {}
      // Live thumbnail updates: watch the light-DOM slides for content
      // edits and re-clone just the affected thumb(s), debounced. Ignore
      // the data-deck-* / data-screen-label / data-om-validate attributes
      // this component itself writes so nav and skip don't trigger
      // spurious refreshes.
      const OWN_ATTRS = /^data-(deck-|screen-label$|om-validate$)/;
      this._liveDirty = new Set();
      this._liveObserver = new MutationObserver(records => {
        for (const r of records) {
          if (r.type === 'attributes' && OWN_ATTRS.test(r.attributeName || '')) continue;
          let n = r.target;
          while (n && n.parentElement !== this) n = n.parentElement;
          if (n && this._slideSet && this._slideSet.has(n)) this._liveDirty.add(n);
        }
        if (this._liveDirty.size && !this._liveTimer) {
          this._liveTimer = setTimeout(() => {
            this._liveTimer = null;
            this._liveDirty.forEach(s => this._refreshThumb(s));
            this._liveDirty.clear();
          }, 200);
        }
      });
      this._liveObserver.observe(this, {
        subtree: true,
        childList: true,
        characterData: true,
        attributes: true
      });
      // Lazy thumbnail materialization — clone the slide only when its
      // frame scrolls into (or near) the rail viewport. rootMargin gives
      // ~4 thumbs of pre-load so fast scrolling doesn't flash blanks.
      this._railObserver = new IntersectionObserver(entries => {
        entries.forEach(e => {
          if (e.isIntersecting && e.target.__deckThumb) {
            this._materialize(e.target.__deckThumb);
          }
        });
      }, {
        root: this._rail,
        rootMargin: '400px 0px'
      });
      // Tweaks typically change CSS vars / attrs OUTSIDE <deck-stage>
      // (on <html>, <body>, a wrapper div, or a <style> tag), which
      // _liveObserver can't see. Re-snapshot author CSS (constructable
      // sheet is shared by reference, so one replaceSync updates every
      // thumb shadow root) and re-sync each thumb host's attrs + custom
      // properties. In-slide DOM mutations are _liveObserver's job.
      // Debounced so slider drags don't thrash.
      this._onTweakChange = () => {
        clearTimeout(this._tweakTimer);
        this._tweakTimer = setTimeout(() => {
          this._snapshotAuthorCss();
          // One getComputedStyle for the whole batch — each
          // getPropertyValue read below reuses the same computed style
          // as long as nothing invalidates layout between thumbs.
          const cs = getComputedStyle(this);
          (this._thumbs || []).forEach(t => {
            if (t.host) this._syncThumbHostAttrs(t.host, cs);
          });
        }, 120);
      };
      window.addEventListener('tweakchange', this._onTweakChange);
      this._snapshotAuthorCss();
      // Build the rail now that it's enabled — slotchange already fired,
      // so _renderRail's early-return skipped the initial build.
      this._syncRailHidden();
      this._renderRail();
      this._fit();
    }

    /** Snapshot document stylesheets into a constructable sheet that each
     *  thumbnail's nested shadow root adopts — so author CSS styles the
     *  cloned slide content without touching this component's chrome.
     *  Cross-origin sheets throw on .cssRules — skip them. Re-callable:
     *  the existing constructable sheet is reused via replaceSync so every
     *  already-adopted shadow root picks up the fresh CSS without re-adopt. */
    _snapshotAuthorCss() {
      // :root in an adopted sheet inside a shadow root matches nothing
      // (only the document root qualifies), so author rules like
      // `:root[data-voice="modern"] .serif` never reach the clones.
      // Rewrite :root → :host and mirror <html>'s data-*/class/lang onto
      // each thumb host (see _syncThumbHostAttrs) so the same selectors
      // match inside the thumbnail's shadow tree.
      const authorCss = Array.from(document.styleSheets).map(sh => {
        try {
          return Array.from(sh.cssRules).map(r => r.cssText).join('\n');
        } catch (e) {
          return '';
        }
      }).join('\n')
      // The shadow host is featureless outside the functional :host(...)
      // form, so any compound on :root — [attr], .class, #id, :pseudo —
      // must become :host(<compound>) not :host<compound>. Same for the
      // html type selector (Tailwind class-strategy dark mode emits
      // html.dark; Pico uses html[data-theme]), which has nothing to
      // match inside the thumb's shadow tree.
      .replace(/:root((?:\[[^\]]*\]|[.#][-\w]+|:[-\w]+(?:\([^)]*\))?)+)/g, ':host($1)').replace(/:root\b/g, ':host').replace(/(^|[\s,>~+(}])html((?:\[[^\]]*\]|[.#][-\w]+|:[-\w]+(?:\([^)]*\))?)+)(?![-\w])/g, '$1:host($2)').replace(/(^|[\s,>~+(}])html(?![-\w])/g, '$1:host');
      // Every custom property the author references. _syncThumbHostAttrs
      // mirrors each one's *computed* value at <deck-stage> onto the
      // thumb host so the live value wins over the :host default above
      // regardless of which ancestor the tweak wrote to (<html>, <body>,
      // a wrapper div, or the deck-stage element itself all inherit
      // down to getComputedStyle(this)).
      this._authorVars = new Set(authorCss.match(/--[\w-]+/g) || []);
      try {
        if (!this._adoptedSheet) this._adoptedSheet = new CSSStyleSheet();
        this._adoptedSheet.replaceSync(authorCss);
      } catch (e) {
        this._adoptedSheet = null;
        this._authorCss = authorCss;
      }
    }
    _syncThumbHostAttrs(host, cs) {
      const de = document.documentElement;
      // setAttribute overwrites but can't delete — an attr removed from
      // <html> (toggleAttribute off, classList emptied) would linger on
      // the host and :host([data-*]) / :host(.foo) rules would keep
      // matching. Remove stale mirrored attrs first; iterate backward
      // because removeAttribute mutates the live NamedNodeMap.
      for (let i = host.attributes.length - 1; i >= 0; i--) {
        const n = host.attributes[i].name;
        if ((n.startsWith('data-') || n === 'class' || n === 'lang') && !de.hasAttribute(n)) {
          host.removeAttribute(n);
        }
      }
      for (const a of de.attributes) {
        if (a.name.startsWith('data-') || a.name === 'class' || a.name === 'lang') {
          host.setAttribute(a.name, a.value);
        }
      }
      // The :root→:host rewrite in _snapshotAuthorCss pins each custom
      // property to its stylesheet default on the thumb host, shadowing
      // the live value that would otherwise inherit. Tweaks can write the
      // live value on any ancestor — <html>, <body>, a wrapper div, the
      // deck-stage element — so read it as the *computed* value at
      // <deck-stage> (which sees the whole inheritance chain) rather than
      // trying to guess which element the author wrote to. Inline on the
      // host beats the :host{} rule. remove-stale covers vars dropped
      // from the stylesheet between snapshots.
      const vars = this._authorVars || new Set();
      for (let i = host.style.length - 1; i >= 0; i--) {
        const p = host.style[i];
        if (p.startsWith('--') && !vars.has(p)) host.style.removeProperty(p);
      }
      const live = cs || getComputedStyle(this);
      vars.forEach(p => {
        const v = live.getPropertyValue(p);
        if (v) host.style.setProperty(p, v.trim());else host.style.removeProperty(p);
      });
    }
    disconnectedCallback() {
      window.removeEventListener('keydown', this._onKey);
      window.removeEventListener('resize', this._onResize);
      window.removeEventListener('mousemove', this._onMouseMove);
      window.removeEventListener('message', this._onMessage);
      window.removeEventListener('click', this._onDocClick, true);
      window.removeEventListener('beforeprint', this._onBeforePrint);
      window.removeEventListener('afterprint', this._onAfterPrint);
      if (this._freezeStyle) {
        this._freezeStyle.remove();
        this._freezeStyle = null;
      }
      this.removeEventListener('click', this._onTap);
      if (this._hideTimer) clearTimeout(this._hideTimer);
      if (this._mouseIdleTimer) clearTimeout(this._mouseIdleTimer);
      if (this._liveTimer) clearTimeout(this._liveTimer);
      if (this._tweakTimer) clearTimeout(this._tweakTimer);
      if (this._railAnimTimer) clearTimeout(this._railAnimTimer);
      if (this._scaleRaf) cancelAnimationFrame(this._scaleRaf);
      if (this._liveObserver) this._liveObserver.disconnect();
      if (this._railObserver) this._railObserver.disconnect();
      if (this._onTweakChange) window.removeEventListener('tweakchange', this._onTweakChange);
    }
    attributeChangedCallback() {
      if (this._canvas) {
        this._canvas.style.width = this.designWidth + 'px';
        this._canvas.style.height = this.designHeight + 'px';
        this._canvas.style.setProperty('--deck-design-w', this.designWidth + 'px');
        this._canvas.style.setProperty('--deck-design-h', this.designHeight + 'px');
        if (this._rail) {
          this._rail.style.setProperty('--deck-aspect', this.designWidth + '/' + this.designHeight);
        }
        this._fit();
        this._scaleThumbs();
        this._syncPrintPageRule();
      }
    }
    _render() {
      const style = document.createElement('style');
      style.textContent = stylesheet;
      const stage = document.createElement('div');
      stage.className = 'stage';
      const canvas = document.createElement('div');
      canvas.className = 'canvas';
      canvas.style.width = this.designWidth + 'px';
      canvas.style.height = this.designHeight + 'px';
      canvas.style.setProperty('--deck-design-w', this.designWidth + 'px');
      canvas.style.setProperty('--deck-design-h', this.designHeight + 'px');
      const slot = document.createElement('slot');
      slot.addEventListener('slotchange', this._onSlotChange);
      canvas.appendChild(slot);
      stage.appendChild(canvas);

      // Overlay: compact, solid black, with clickable controls.
      const overlay = document.createElement('div');
      overlay.className = 'overlay export-hidden';
      overlay.setAttribute('role', 'toolbar');
      overlay.setAttribute('aria-label', 'Deck controls');
      overlay.setAttribute('data-omelette-chrome', '');
      overlay.innerHTML = `
        <button class="btn prev" type="button" aria-label="Previous slide" title="Previous (←)">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 3L5 8l5 5"/></svg>
        </button>
        <span class="count" aria-live="polite"><span class="current">1</span><span class="sep">/</span><span class="total">1</span></span>
        <button class="btn next" type="button" aria-label="Next slide" title="Next (→)">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3l5 5-5 5"/></svg>
        </button>
        <span class="divider"></span>
        <button class="btn reset" type="button" aria-label="Reset to first slide" title="Reset (R)">Reset<span class="kbd">R</span></button>
      `;
      overlay.querySelector('.prev').addEventListener('click', () => this._advance(-1, 'click'));
      overlay.querySelector('.next').addEventListener('click', () => this._advance(1, 'click'));
      overlay.querySelector('.reset').addEventListener('click', () => this._go(0, 'click'));

      // Thumbnail rail + context menu. Thumbnails are populated in
      // _renderRail() after _collectSlides().
      const rail = document.createElement('div');
      rail.className = 'rail export-hidden';
      rail.setAttribute('data-omelette-chrome', '');
      rail.style.setProperty('--deck-aspect', this.designWidth + '/' + this.designHeight);
      // Edge auto-scroll while dragging a thumb near the rail's top/bottom
      // so off-screen drop targets are reachable. Native dragover fires
      // continuously while the pointer is stationary, so a per-event nudge
      // (ramped by edge proximity) is enough — no rAF loop needed.
      rail.addEventListener('dragover', e => {
        if (this._dragFrom == null) return;
        const r = rail.getBoundingClientRect();
        const EDGE = 40;
        const dt = e.clientY - r.top;
        const db = r.bottom - e.clientY;
        if (dt < EDGE) rail.scrollTop -= Math.ceil((EDGE - dt) / 3);else if (db < EDGE) rail.scrollTop += Math.ceil((EDGE - db) / 3);
      });
      const menu = document.createElement('div');
      menu.className = 'ctxmenu export-hidden';
      menu.setAttribute('data-omelette-chrome', '');
      menu.innerHTML = `
        <button type="button" data-act="skip">Skip slide</button>
        <button type="button" data-act="up">Move up</button>
        <button type="button" data-act="down">Move down</button>
        <button type="button" data-act="duplicate">Duplicate slide</button>
        <hr>
        <button type="button" data-act="delete">Delete slide</button>
      `;
      menu.addEventListener('click', e => {
        const act = e.target && e.target.getAttribute && e.target.getAttribute('data-act');
        if (!act) return;
        const i = this._menuIndex;
        this._closeMenu();
        if (act === 'skip') this._toggleSkip(i);else if (act === 'up') this._moveSlide(i, i - 1);else if (act === 'down') this._moveSlide(i, i + 1);else if (act === 'duplicate') this._duplicateSlide(i);else if (act === 'delete') this._openConfirm(i);
      });
      menu.addEventListener('contextmenu', e => e.preventDefault());

      // Rail resize handle — drag to set --deck-rail-w, persisted to
      // localStorage so the width survives reloads.
      const resize = document.createElement('div');
      resize.className = 'rail-resize export-hidden';
      resize.setAttribute('data-omelette-chrome', '');
      resize.addEventListener('pointerdown', e => {
        e.preventDefault();
        resize.setPointerCapture(e.pointerId);
        resize.setAttribute('data-dragging', '');
        const move = ev => this._setRailWidth(ev.clientX);
        const up = () => {
          resize.removeEventListener('pointermove', move);
          resize.removeEventListener('pointerup', up);
          resize.removeEventListener('pointercancel', up);
          resize.removeAttribute('data-dragging');
          try {
            localStorage.setItem('deck-stage.railWidth', String(this._railPx));
          } catch (err) {}
        };
        resize.addEventListener('pointermove', move);
        resize.addEventListener('pointerup', up);
        resize.addEventListener('pointercancel', up);
      });

      // Delete-confirm dialog — mirrors the SPA's ConfirmDialog layout.
      const confirm = document.createElement('div');
      confirm.className = 'confirm-backdrop export-hidden';
      confirm.setAttribute('data-omelette-chrome', '');
      confirm.innerHTML = `
        <div class="confirm" role="dialog" aria-modal="true">
          <div class="body">
            <div class="title">Delete slide?</div>
            <div class="msg">This slide will be removed from the deck.</div>
          </div>
          <div class="footer">
            <button type="button" class="cancel">Cancel</button>
            <button type="button" class="danger">Delete</button>
          </div>
        </div>
      `;
      confirm.addEventListener('click', e => {
        if (e.target === confirm) this._closeConfirm();
      });
      confirm.querySelector('.cancel').addEventListener('click', () => this._closeConfirm());
      confirm.querySelector('.danger').addEventListener('click', () => {
        const i = this._confirmIndex;
        this._closeConfirm();
        this._deleteSlide(i);
      });
      this._root.append(style, rail, resize, stage, overlay, menu, confirm);
      this._canvas = canvas;
      this._stage = stage;
      this._slot = slot;
      this._overlay = overlay;
      this._rail = rail;
      this._resize = resize;
      this._menu = menu;
      this._confirm = confirm;
      this._countEl = overlay.querySelector('.current');
      this._totalEl = overlay.querySelector('.total');

      // Restore persisted rail width.
      let rw = 188;
      try {
        const s = localStorage.getItem('deck-stage.railWidth');
        if (s) rw = parseInt(s, 10) || rw;
      } catch (err) {}
      this._setRailWidth(rw);
      this._syncRailHidden();
    }
    _setRailWidth(px) {
      const w = Math.max(120, Math.min(360, Math.round(px)));
      this._railPx = w;
      this.style.setProperty('--deck-rail-w', w + 'px');
      this._fit();
      // _scaleThumbs forces a sync layout (frame.offsetWidth) then writes
      // N transforms. During a resize drag this runs per-pointermove;
      // coalesce to one per frame.
      if (!this._scaleRaf) {
        this._scaleRaf = requestAnimationFrame(() => {
          this._scaleRaf = null;
          this._scaleThumbs();
        });
      }
    }

    /** @page must live in the document stylesheet — it's a no-op inside
     *  shadow DOM. Inject/update a single <head> style tag so the print
     *  sheet matches the design size and Save-as-PDF yields one slide per
     *  page with no margins. */
    _syncPrintPageRule() {
      const id = 'deck-stage-print-page';
      let tag = document.getElementById(id);
      if (!tag) {
        tag = document.createElement('style');
        tag.id = id;
        document.head.appendChild(tag);
      }
      tag.textContent = '@page { size: ' + this.designWidth + 'px ' + this.designHeight + 'px; margin: 0; } ' + '@media print { html, body { margin: 0 !important; padding: 0 !important; background: none !important; overflow: visible !important; height: auto !important; } ' + '* { -webkit-print-color-adjust: exact; print-color-adjust: exact; } ' +
      // Jump authored animations/transitions to their end state so print
      // never captures mid-entrance — pairs with the beforeprint handler
      // in connectedCallback that sets data-deck-active on every slide.
      '*, *::before, *::after { animation-delay: -99s !important; animation-duration: .001s !important; ' + 'animation-iteration-count: 1 !important; animation-fill-mode: both !important; ' + 'animation-play-state: running !important; transition-duration: 0s !important; } }';
    }
    _onSlotChange() {
      // Rail mutations (delete/move/duplicate) already reconcile synchronously and
      // emit slidechange with reason 'api'; skip the async slotchange that
      // would otherwise re-broadcast with reason 'init'.
      if (this._squelchSlotChange) {
        this._squelchSlotChange = false;
        return;
      }
      this._collectSlides();
      this._restoreIndex();
      this._applyIndex({
        showOverlay: false,
        broadcast: true,
        reason: 'init'
      });
      this._fit();
    }
    _collectSlides() {
      const assigned = this._slot.assignedElements({
        flatten: true
      });
      this._slides = assigned.filter(el => {
        // Skip template/style/script nodes even if someone slots them.
        const tag = el.tagName;
        return tag !== 'TEMPLATE' && tag !== 'SCRIPT' && tag !== 'STYLE';
      });
      this._slideSet = new Set(this._slides);
      this._slides.forEach((slide, i) => {
        const n = i + 1;
        slide.setAttribute('data-screen-label', `${pad2(n)} ${getSlideLabel(slide)}`);

        // Validation attribute for comment flow / auto-checks.
        if (!slide.hasAttribute('data-om-validate')) {
          slide.setAttribute('data-om-validate', VALIDATE_ATTR);
        }
        slide.setAttribute('data-deck-slide', String(i));
      });
      if (this._totalEl) this._totalEl.textContent = String(this._slides.length || 1);
      if (this._index >= this._slides.length) this._index = Math.max(0, this._slides.length - 1);
      this._markLastVisible();
      this._renderRail();
    }

    /** Tag the last non-skipped slide so print CSS can drop its
     *  break-after (see the @media print comment above — :last-child
     *  alone matches a hidden skipped slide). */
    _markLastVisible() {
      let last = null;
      this._slides.forEach(s => {
        s.removeAttribute('data-deck-last-visible');
        if (!s.hasAttribute('data-deck-skip')) last = s;
      });
      if (last) last.setAttribute('data-deck-last-visible', '');
    }
    _loadNotes() {
      const tag = document.getElementById('speaker-notes');
      if (!tag) {
        this._notes = [];
        return;
      }
      try {
        const parsed = JSON.parse(tag.textContent || '[]');
        if (Array.isArray(parsed)) this._notes = parsed;
      } catch (e) {
        console.warn('[deck-stage] Failed to parse #speaker-notes JSON:', e);
        this._notes = [];
      }
    }
    _restoreIndex() {
      // The host's ?slide= param is delivered as a #<int> hash (1-indexed) on
      // the iframe src. No hash → slide 1; the deck itself keeps no position
      // state across loads.
      const h = (location.hash || '').match(/^#(\d+)$/);
      if (h) {
        const n = parseInt(h[1], 10) - 1;
        if (n >= 0 && n < this._slides.length) this._index = n;
      }
    }
    _applyIndex({
      showOverlay = true,
      broadcast = true,
      reason = 'init'
    } = {}) {
      if (!this._slides.length) return;
      const prev = this._prevIndex == null ? -1 : this._prevIndex;
      const curr = this._index;
      // Keep the iframe's own hash in sync so an in-iframe location.reload()
      // (reload banner path in viewer-handle.ts) lands on the current slide,
      // not the stale deep-link hash from initial load.
      try {
        history.replaceState(null, '', '#' + (curr + 1));
      } catch (e) {}
      this._slides.forEach((s, i) => {
        if (i === curr) s.setAttribute('data-deck-active', '');else s.removeAttribute('data-deck-active');
      });
      if (this._countEl) this._countEl.textContent = String(curr + 1);
      // Follow-scroll on every navigation (init deep-link, keyboard, click,
      // tap, external goTo) — the only time we *don't* want the rail to
      // track current is after a rail-internal mutation, where _renderRail
      // has already restored the user's scroll position and yanking back to
      // current would undo it.
      this._syncRail(reason !== 'mutation');
      if (broadcast) {
        // (1) Legacy: host-window postMessage for speaker-notes renderers.
        try {
          window.postMessage({
            slideIndexChanged: curr,
            deckTotal: this._slides.length,
            deckSkipped: this._skippedIndices()
          }, '*');
        } catch (e) {}

        // (2) In-page CustomEvent on the <deck-stage> element itself.
        //     Bubbles and composes out of shadow DOM so slide code can listen:
        //       document.querySelector('deck-stage').addEventListener('slidechange', e => {
        //         e.detail.index, e.detail.previousIndex, e.detail.total, e.detail.slide, e.detail.reason
        //       });
        const detail = {
          index: curr,
          previousIndex: prev,
          total: this._slides.length,
          slide: this._slides[curr] || null,
          previousSlide: prev >= 0 ? this._slides[prev] || null : null,
          reason: reason // 'init' | 'keyboard' | 'click' | 'tap' | 'api'
        };
        this.dispatchEvent(new CustomEvent('slidechange', {
          detail,
          bubbles: true,
          composed: true
        }));
      }
      this._prevIndex = curr;
      if (showOverlay) this._flashOverlay();
    }
    _flashOverlay() {
      // Host posts __omelette_presenting while in fullscreen/tab presentation
      // mode — suppress the nav footer entirely (both hover and slide-change
      // flash) so the audience sees clean slides.
      if (!this._overlay || this._presenting) return;
      this._overlay.setAttribute('data-visible', '');
      if (this._hideTimer) clearTimeout(this._hideTimer);
      this._hideTimer = setTimeout(() => {
        this._overlay.removeAttribute('data-visible');
      }, OVERLAY_HIDE_MS);
    }
    _railWidth() {
      // State-based, no offsetWidth: the first _fit() can run before the
      // rail has had layout on some load paths, and a 0 there paints the
      // slide full-width for one frame before the post-slotchange _fit()
      // corrects it.
      if (!this._railEnabled || !this._railVisible || this.hasAttribute('no-rail') || this.hasAttribute('noscale') || this._presenting || this._previewMode || NARROW_MQ.matches) return 0;
      return this._railPx || 0;
    }
    _fit() {
      if (!this._canvas) return;
      const stage = this._canvas.parentElement;
      // PPTX export sets noscale so the DOM capture sees authored-size
      // geometry — the scaled canvas is in shadow DOM, so the exporter's
      // resetTransformSelector can't reach .canvas.style.transform directly.
      if (this.hasAttribute('noscale')) {
        this._canvas.style.transform = 'none';
        if (stage) stage.style.left = '0';
        if (this._overlay) this._overlay.style.marginLeft = '0';
        return;
      }
      const rw = this._railWidth();
      if (stage) stage.style.left = rw + 'px';
      // Overlay is centred on the viewport via left:50% + translate(-50%);
      // marginLeft shifts the centre by rw/2 so it lands in the middle of
      // the [rw, innerWidth] stage region.
      if (this._overlay) this._overlay.style.marginLeft = rw / 2 + 'px';
      const vw = window.innerWidth - rw;
      const vh = window.innerHeight;
      const s = Math.min(vw / this.designWidth, vh / this.designHeight);
      this._canvas.style.transform = `scale(${s})`;
    }
    _onResize() {
      this._fit();
      // Crossing the narrow-viewport breakpoint reveals the rail — rerun the
      // thumbnail scale the same way _setRailWidth does.
      if (!this._scaleRaf) {
        this._scaleRaf = requestAnimationFrame(() => {
          this._scaleRaf = null;
          this._scaleThumbs();
        });
      }
    }
    _onMouseMove() {
      // Keep overlay visible while mouse moves; hide after idle.
      this._flashOverlay();
    }
    _onMessage(e) {
      const d = e.data;
      if (d && typeof d.__omelette_presenting === 'boolean') {
        this._presenting = d.__omelette_presenting;
        if (this._presenting && this._overlay) {
          this._overlay.removeAttribute('data-visible');
          if (this._hideTimer) clearTimeout(this._hideTimer);
        }
        this._syncRailHidden();
        this._closeMenu();
        this._closeConfirm();
        this._fit();
        this._scaleThumbs();
      }
      // Host's Preview segment (ViewerMode='none'): the rail's drag-reorder /
      // right-click skip-delete affordances are editing chrome, so hide it
      // while the user is just looking at the deck. Same hard-hide path as
      // presenting; independent of the user's _railVisible preference so
      // returning to Edit restores whatever they had.
      if (d && typeof d.__omelette_preview_mode === 'boolean') {
        if (d.__omelette_preview_mode === this._previewMode) return;
        this._previewMode = d.__omelette_preview_mode;
        this._syncRailHidden();
        this._closeMenu();
        this._closeConfirm();
        this._fit();
        this._scaleThumbs();
      }
      // Per-viewer show/hide, driven by the TweaksPanel's auto-injected
      // "Thumbnail rail" toggle (or any author script). Independent of
      // whether the Tweaks panel itself is open — closing the panel
      // doesn't change rail visibility. Persists alongside rail width.
      if (d && d.type === '__deck_rail_visible' && typeof d.on === 'boolean') {
        if (d.on === this._railVisible) return;
        this._railVisible = d.on;
        try {
          localStorage.setItem('deck-stage.railVisible', d.on ? '1' : '0');
        } catch (e) {}
        // Arm the transition, commit it, then flip state — otherwise the
        // browser coalesces both writes and nothing animates on show.
        this.setAttribute('data-rail-anim', '');
        void (this._rail && this._rail.offsetHeight);
        this._syncRailHidden();
        this._fit();
        this._scaleThumbs();
        clearTimeout(this._railAnimTimer);
        this._railAnimTimer = setTimeout(() => this.removeAttribute('data-rail-anim'), 220);
      }
      if (d && d.type === '__omelette_rail_enabled') this._enableRail();
    }
    _syncRailHidden() {
      if (!this._rail) return;
      // data-presenting is the hard hide (display:none) for flag-off,
      // presentation mode, and the host's Preview segment — instant, no
      // transition. data-user-hidden is the soft hide (translateX(-100%))
      // for the viewer's rail toggle, so show/hide slides under
      // :host([data-rail-anim]).
      const hard = !this._railEnabled || this._presenting || this._previewMode;
      if (hard) this._rail.setAttribute('data-presenting', '');else this._rail.removeAttribute('data-presenting');
      if (!this._railVisible) this._rail.setAttribute('data-user-hidden', '');else this._rail.removeAttribute('data-user-hidden');
      // translateX hide leaves thumbs (tabIndex=0) in the tab order —
      // inert keeps them unfocusable while the rail is off-screen.
      this._rail.inert = hard || !this._railVisible;
    }
    _onTap(e) {
      // Touch-only — keyboard + the overlay toolbar cover nav on desktop.
      if (FINE_POINTER_MQ.matches) return;
      // Only taps that land on the stage (slide content or letterbox); the
      // overlay / rail / menus are siblings with their own click handlers.
      const path = e.composedPath();
      if (!this._stage || !path.includes(this._stage)) return;
      // Let interactive slide content keep the tap. composedPath (not
      // e.target.closest) so we see through open shadow roots — a <button>
      // inside a slide-authored custom element retargets e.target to the
      // host but still appears in the composed path.
      if (e.defaultPrevented) return;
      for (const n of path) {
        if (n === this._stage) break;
        if (n.matches && n.matches(INTERACTIVE_SEL)) return;
      }
      e.preventDefault();
      const rw = this._railWidth();
      const mid = rw + (window.innerWidth - rw) / 2;
      this._advance(e.clientX < mid ? -1 : 1, 'tap');
    }
    _onKey(e) {
      // Ignore when the user is typing.
      const t = e.target;
      if (t && (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName))) return;
      // Confirm dialog swallows nav keys while open; Escape cancels. Enter
      // is left to the focused button's native activation so Tab→Cancel
      // →Enter activates Cancel, not the window-level confirm path.
      if (this._confirm && this._confirm.hasAttribute('data-open')) {
        if (e.key === 'Escape') {
          this._closeConfirm();
          e.preventDefault();
        }
        return;
      }
      if (e.key === 'Escape' && this._menu && this._menu.hasAttribute('data-open')) {
        this._closeMenu();
        e.preventDefault();
        return;
      }
      if (e.metaKey || e.ctrlKey || e.altKey) return;
      const key = e.key;
      let handled = true;
      if (key === 'ArrowRight' || key === 'PageDown' || key === ' ' || key === 'Spacebar') {
        this._advance(1, 'keyboard');
      } else if (key === 'ArrowLeft' || key === 'PageUp') {
        this._advance(-1, 'keyboard');
      } else if (key === 'Home') {
        this._go(0, 'keyboard');
      } else if (key === 'End') {
        this._go(this._slides.length - 1, 'keyboard');
      } else if (key === 'r' || key === 'R') {
        this._go(0, 'keyboard');
      } else if (/^[0-9]$/.test(key)) {
        // 1..9 jump to that slide; 0 jumps to 10.
        const n = key === '0' ? 9 : parseInt(key, 10) - 1;
        if (n < this._slides.length) this._go(n, 'keyboard');
      } else {
        handled = false;
      }
      if (handled) {
        e.preventDefault();
        this._flashOverlay();
      }
    }
    _go(i, reason = 'api') {
      if (!this._slides.length) return;
      const clamped = Math.max(0, Math.min(this._slides.length - 1, i));
      if (clamped === this._index) {
        this._flashOverlay();
        return;
      }
      this._index = clamped;
      this._applyIndex({
        showOverlay: true,
        broadcast: true,
        reason
      });
    }

    /** Step forward/back skipping any slide marked data-deck-skip. Falls
     *  back to _go's clamp-at-ends behaviour (flash overlay) when there's
     *  nothing further in that direction. */
    _advance(dir, reason) {
      if (!this._slides.length) return;
      let i = this._index + dir;
      while (i >= 0 && i < this._slides.length && this._slides[i].hasAttribute('data-deck-skip')) {
        i += dir;
      }
      if (i < 0 || i >= this._slides.length) {
        this._flashOverlay();
        return;
      }
      this._go(i, reason);
    }

    // ── Thumbnail rail ────────────────────────────────────────────────────
    //
    // Thumbs are keyed by slide element and reused across _renderRail()
    // calls, so a reorder/delete is an O(changed) DOM shuffle instead of an
    // O(N) teardown-and-re-clone. Each thumb starts as a lightweight shell
    // (num + empty frame); the clone is materialized lazily by an
    // IntersectionObserver when the frame scrolls into (or near) view, so
    // only visible-ish slides pay the clone + image-decode cost.

    _renderRail() {
      if (!this._rail || !this._railEnabled) {
        this._thumbs = [];
        return;
      }
      // FLIP: record each *materialized* thumb's top before the reconcile.
      // Off-screen (non-materialized) thumbs don't need the animation and
      // skipping their getBoundingClientRect saves a forced layout per
      // off-screen thumb on large decks.
      const prevTops = new Map();
      (this._thumbs || []).forEach(({
        thumb,
        slide,
        host
      }) => {
        if (host) prevTops.set(slide, thumb.getBoundingClientRect().top);
      });
      const st = this._rail.scrollTop;

      // Reconcile: reuse thumbs that already exist for a slide, create
      // shells for new slides, drop thumbs for removed slides.
      const bySlide = new Map();
      (this._thumbs || []).forEach(t => bySlide.set(t.slide, t));
      const next = [];
      this._slides.forEach(slide => {
        let t = bySlide.get(slide);
        if (t) bySlide.delete(slide);else t = this._makeThumb(slide);
        next.push(t);
      });
      // Orphans — slides removed since last render.
      bySlide.forEach(t => {
        if (this._railObserver) this._railObserver.unobserve(t.frame);
        t.thumb.remove();
      });
      // Put thumbs into document order to match _slides. insertBefore on
      // an already-correctly-placed node is a no-op, so this is cheap
      // when nothing moved.
      next.forEach((t, i) => {
        const want = t.thumb;
        const at = this._rail.children[i];
        if (at !== want) this._rail.insertBefore(want, at || null);
        t.i = i;
        t.num.textContent = String(i + 1);
        if (t.slide.hasAttribute('data-deck-skip')) t.thumb.setAttribute('data-skip', '');else t.thumb.removeAttribute('data-skip');
      });
      this._thumbs = next;
      this._rail.scrollTop = st;
      if (prevTops.size) {
        const moved = [];
        this._thumbs.forEach(({
          thumb,
          slide
        }) => {
          const old = prevTops.get(slide);
          if (old == null) return;
          const dy = old - thumb.getBoundingClientRect().top;
          if (Math.abs(dy) < 1) return;
          thumb.style.transition = 'none';
          thumb.style.transform = `translateY(${dy}px)`;
          moved.push(thumb);
        });
        if (moved.length) {
          // Commit the inverted positions before flipping the transition
          // on — otherwise the browser coalesces both style writes and
          // nothing animates.
          void this._rail.offsetHeight;
          moved.forEach(t => {
            t.style.transition = 'transform 180ms cubic-bezier(.2,.7,.3,1)';
            t.style.transform = '';
          });
          setTimeout(() => moved.forEach(t => {
            t.style.transition = '';
          }), 220);
        }
      }
      requestAnimationFrame(() => this._scaleThumbs());
      this._syncRail(false);
    }

    /** Create a lightweight thumb shell for one slide. The clone is
     *  materialized later by the IntersectionObserver. Event handlers
     *  look up the thumb's *current* index (via _thumbs.indexOf) so the
     *  same element can be reused across reorders. */
    _makeThumb(slide) {
      const thumb = document.createElement('div');
      thumb.className = 'thumb';
      thumb.tabIndex = 0;
      const num = document.createElement('div');
      num.className = 'num';
      const frame = document.createElement('div');
      frame.className = 'frame';
      thumb.append(num, frame);
      const entry = {
        thumb,
        num,
        frame,
        slide,
        clone: null,
        host: null,
        i: -1
      };
      // entry.i is refreshed on every _renderRail reconcile pass, so
      // handlers read the thumb's current position without an O(N) scan.
      const idx = () => entry.i;
      thumb.addEventListener('click', () => this._go(idx(), 'click'));
      // ↑/↓ step through the rail when a thumb has focus. _go clamps at the
      // ends and _applyIndex→_syncRail scrolls the new current thumb into
      // view; we move focus to it (preventScroll — _syncRail already
      // scrolled) so a held key walks the whole list. stopPropagation keeps
      // this out of the window-level _onKey nav handler.
      thumb.addEventListener('keydown', e => {
        if (e.key !== 'ArrowUp' && e.key !== 'ArrowDown') return;
        if (e.metaKey || e.ctrlKey || e.altKey) return;
        e.preventDefault();
        e.stopPropagation();
        this._go(idx() + (e.key === 'ArrowDown' ? 1 : -1), 'keyboard');
        const cur = this._thumbs && this._thumbs[this._index];
        if (cur) cur.thumb.focus({
          preventScroll: true
        });
      });
      thumb.addEventListener('contextmenu', e => {
        e.preventDefault();
        this._openMenu(idx(), e.clientX, e.clientY);
      });
      thumb.draggable = true;
      thumb.addEventListener('dragstart', e => {
        this._dragFrom = idx();
        thumb.setAttribute('data-dragging', '');
        e.dataTransfer.effectAllowed = 'move';
        try {
          e.dataTransfer.setData('text/plain', String(this._dragFrom));
        } catch (err) {}
      });
      thumb.addEventListener('dragend', () => {
        thumb.removeAttribute('data-dragging');
        this._clearDrop();
        this._dragFrom = null;
      });
      thumb.addEventListener('dragover', e => {
        if (this._dragFrom == null) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const r = thumb.getBoundingClientRect();
        this._setDrop(idx(), e.clientY < r.top + r.height / 2 ? 'before' : 'after');
      });
      thumb.addEventListener('drop', e => {
        if (this._dragFrom == null) return;
        e.preventDefault();
        const i = idx();
        const r = thumb.getBoundingClientRect();
        let to = e.clientY >= r.top + r.height / 2 ? i + 1 : i;
        if (this._dragFrom < to) to--;
        const from = this._dragFrom;
        this._clearDrop();
        this._dragFrom = null;
        if (to !== from) this._moveSlide(from, to);
      });
      if (this._railObserver) this._railObserver.observe(frame);
      frame.__deckThumb = entry;
      return entry;
    }

    /** Lazily build the clone for a thumb that has scrolled into view. */
    _materialize(entry) {
      if (entry.host) return;
      const dw = this.designWidth,
        dh = this.designHeight;
      let clone = entry.slide.cloneNode(true);
      clone.removeAttribute('id');
      clone.removeAttribute('data-deck-active');
      clone.querySelectorAll('[id]').forEach(el => el.removeAttribute('id'));
      // Neuter heavy media; replace <video> with its poster so the box
      // keeps a visual. <iframe>/<audio> become empty placeholders.
      clone.querySelectorAll('iframe, audio, object, embed').forEach(el => {
        el.removeAttribute('src');
        el.removeAttribute('srcdoc');
        el.removeAttribute('data');
        el.innerHTML = '';
      });
      clone.querySelectorAll('video').forEach(el => {
        if (!el.poster) {
          el.removeAttribute('src');
          el.innerHTML = '';
          return;
        }
        const img = document.createElement('img');
        img.src = el.poster;
        img.alt = '';
        img.style.cssText = el.style.cssText + ';object-fit:cover;width:100%;height:100%;';
        img.className = el.className;
        el.replaceWith(img);
      });
      // Images: defer decode and let the browser pick the smallest
      // srcset candidate for the ~140px thumb. Same-URL clones reuse the
      // slide's decoded bitmap (URL-keyed cache), so the remaining cost
      // is paint/composite — lazy+async keeps that off the main thread.
      clone.querySelectorAll('img').forEach(el => {
        el.loading = 'lazy';
        el.decoding = 'async';
        if (el.srcset) el.sizes = (this._railPx || 188) + 'px';
      });
      // Custom elements inside the slide would have their
      // connectedCallback fire when the clone is appended. Replace them
      // with inert boxes so a component-heavy deck doesn't run N copies
      // of each component's mount logic in the rail. Children are
      // preserved so layout-wrapper elements (<my-column><h2>…</h2>)
      // still show their authored content; the querySelectorAll NodeList
      // is static, so nested custom elements in the moved subtree are
      // still visited on later iterations.
      const neuter = el => {
        const box = document.createElement('div');
        box.style.cssText = (el.getAttribute('style') || '') + ';background:rgba(0,0,0,0.06);border:1px dashed rgba(0,0,0,0.15);';
        box.className = el.className;
        // Preserve theming/i18n hooks so [data-*] / :lang() / [dir]
        // descendant selectors still match the neutered root.
        for (const a of el.attributes) {
          const n = a.name;
          if (n.startsWith('data-') || n.startsWith('aria-') || n === 'lang' || n === 'dir' || n === 'role' || n === 'title') {
            box.setAttribute(n, a.value);
          }
        }
        while (el.firstChild) box.appendChild(el.firstChild);
        return box;
      };
      // querySelectorAll('*') returns descendants only — a custom-element
      // slide root (<my-slide>…</my-slide>) would slip through and upgrade
      // on append. Swap the root first.
      if (clone.tagName.includes('-')) clone = neuter(clone);
      clone.querySelectorAll('*').forEach(el => {
        if (el.tagName.includes('-')) el.replaceWith(neuter(el));
      });
      clone.style.cssText += ';position:absolute;top:0;left:0;transform-origin:0 0;' + 'pointer-events:none;width:' + dw + 'px;height:' + dh + 'px;' + 'box-sizing:border-box;overflow:hidden;visibility:visible;opacity:1;';
      const host = document.createElement('div');
      host.style.cssText = 'position:absolute;inset:0;';
      this._syncThumbHostAttrs(host);
      const sr = host.attachShadow({
        mode: 'open'
      });
      if (this._adoptedSheet) sr.adoptedStyleSheets = [this._adoptedSheet];else {
        const st = document.createElement('style');
        st.textContent = this._authorCss || '';
        sr.appendChild(st);
      }
      sr.appendChild(clone);
      entry.frame.appendChild(host);
      entry.host = host;
      entry.clone = clone;
      if (this._thumbScale) clone.style.transform = 'scale(' + this._thumbScale + ')';
      // Once materialized the IO callback is a no-op early-return —
      // unobserve so scroll doesn't keep firing it.
      if (this._railObserver) this._railObserver.unobserve(entry.frame);
    }

    /** Re-clone a single thumb (live-update path). No-op if the thumb
     *  hasn't been materialized yet — it'll pick up current content when
     *  it scrolls into view. */
    _refreshThumb(slide) {
      const entry = (this._thumbs || []).find(t => t.slide === slide);
      if (!entry || !entry.host) return;
      entry.host.remove();
      entry.host = entry.clone = null;
      this._materialize(entry);
    }
    _scaleThumbs() {
      if (!this._thumbs || !this._thumbs.length) return;
      // Every frame is the same width; if it reads 0 the rail is
      // display:none (noscale / no-rail / presenting / print) — leave the
      // clones as-is and re-run when the rail is revealed.
      const fw = this._thumbs[0].frame.offsetWidth;
      if (!fw) return;
      this._thumbScale = fw / this.designWidth;
      this._thumbs.forEach(({
        clone
      }) => {
        if (clone) clone.style.transform = 'scale(' + this._thumbScale + ')';
      });
    }
    _setDrop(i, where) {
      // dragover fires at pointer-event rate; touch only the previous
      // and new target rather than sweeping all N thumbs.
      const t = this._thumbs && this._thumbs[i];
      if (this._dropOn && this._dropOn !== t) {
        this._dropOn.thumb.removeAttribute('data-drop');
      }
      if (t) t.thumb.setAttribute('data-drop', where);
      this._dropOn = t || null;
    }
    _clearDrop() {
      if (this._dropOn) this._dropOn.thumb.removeAttribute('data-drop');
      this._dropOn = null;
    }
    _syncRail(follow) {
      if (!this._thumbs) return;
      this._thumbs.forEach(({
        thumb
      }, i) => {
        if (i === this._index) {
          thumb.setAttribute('data-current', '');
          if (follow && typeof thumb.scrollIntoView === 'function') {
            thumb.scrollIntoView({
              block: 'nearest'
            });
          }
        } else {
          thumb.removeAttribute('data-current');
        }
      });
    }
    _openMenu(i, x, y) {
      if (!this._menu) return;
      this._menuIndex = i;
      const slide = this._slides[i];
      const skip = slide && slide.hasAttribute('data-deck-skip');
      this._menu.querySelector('[data-act="skip"]').textContent = skip ? 'Unskip slide' : 'Skip slide';
      this._menu.querySelector('[data-act="up"]').disabled = i <= 0;
      this._menu.querySelector('[data-act="down"]').disabled = i >= this._slides.length - 1;
      this._menu.querySelector('[data-act="delete"]').disabled = this._slides.length <= 1;
      // Place, then clamp to viewport after it's measurable.
      this._menu.style.left = x + 'px';
      this._menu.style.top = y + 'px';
      this._menu.setAttribute('data-open', '');
      const r = this._menu.getBoundingClientRect();
      const nx = Math.min(x, window.innerWidth - r.width - 4);
      const ny = Math.min(y, window.innerHeight - r.height - 4);
      this._menu.style.left = Math.max(4, nx) + 'px';
      this._menu.style.top = Math.max(4, ny) + 'px';
    }
    _closeMenu() {
      if (this._menu) this._menu.removeAttribute('data-open');
      this._menuIndex = -1;
    }
    _openConfirm(i) {
      if (!this._confirm) return;
      this._confirmIndex = i;
      this._confirm.querySelector('.title').textContent = 'Delete slide ' + (i + 1) + '?';
      this._confirm.setAttribute('data-open', '');
      const btn = this._confirm.querySelector('.danger');
      if (btn && btn.focus) btn.focus();
    }
    _closeConfirm() {
      if (this._confirm) this._confirm.removeAttribute('data-open');
      this._confirmIndex = -1;
    }
    _emitDeckChange(detail) {
      this.dispatchEvent(new CustomEvent('deckchange', {
        detail,
        bubbles: true,
        composed: true
      }));
    }
    _deleteSlide(i) {
      const slide = this._slides[i];
      if (!slide || this._slides.length <= 1) return;
      const wasCurrent = i === this._index;
      if (i < this._index || wasCurrent && i === this._slides.length - 1) this._index--;
      this._squelchSlotChange = true;
      slide.remove();
      this._emitDeckChange({
        action: 'delete',
        from: i,
        slide
      });
      this._collectSlides();
      this._applyIndex({
        showOverlay: true,
        broadcast: true,
        reason: 'mutation'
      });
    }
    _duplicateSlide(i) {
      const slide = this._slides[i];
      if (!slide) return;
      const copy = slide.cloneNode(true);
      // Strip ids so the document stays valid (no duplicate-id collisions
      // with the original). Same treatment _materialize gives rail clones.
      copy.removeAttribute('id');
      copy.querySelectorAll('[id]').forEach(el => el.removeAttribute('id'));
      // Insert after the original and make the copy active so it's the one
      // on screen. _collectSlides re-derives data-screen-label / data-deck-*
      // attrs, so the cloned values are overwritten.
      this._index = i + 1;
      this._squelchSlotChange = true;
      this.insertBefore(copy, slide.nextSibling);
      this._emitDeckChange({
        action: 'duplicate',
        from: i,
        to: i + 1,
        slide: copy
      });
      this._collectSlides();
      this._applyIndex({
        showOverlay: true,
        broadcast: true,
        reason: 'mutation'
      });
    }
    _toggleSkip(i) {
      const slide = this._slides[i];
      if (!slide) return;
      const on = !slide.hasAttribute('data-deck-skip');
      if (on) slide.setAttribute('data-deck-skip', '');else slide.removeAttribute('data-deck-skip');
      if (this._thumbs && this._thumbs[i]) {
        if (on) this._thumbs[i].thumb.setAttribute('data-skip', '');else this._thumbs[i].thumb.removeAttribute('data-skip');
      }
      this._markLastVisible();
      this._emitDeckChange({
        action: on ? 'skip' : 'unskip',
        from: i,
        slide
      });
      // Re-broadcast so the presenter popup's prev/next thumbnails re-pick
      // the nearest non-skipped slide without waiting for a nav event.
      try {
        window.postMessage({
          slideIndexChanged: this._index,
          deckTotal: this._slides.length,
          deckSkipped: this._skippedIndices()
        }, '*');
      } catch (e) {}
    }
    _skippedIndices() {
      const out = [];
      for (let i = 0; i < this._slides.length; i++) {
        if (this._slides[i].hasAttribute('data-deck-skip')) out.push(i);
      }
      return out;
    }
    _moveSlide(i, j) {
      if (j < 0 || j >= this._slides.length || j === i) return;
      const slide = this._slides[i];
      const ref = j < i ? this._slides[j] : this._slides[j].nextSibling;
      // Track the active slide across the reorder so the same content
      // stays on screen.
      const cur = this._index;
      if (cur === i) this._index = j;else if (i < cur && j >= cur) this._index = cur - 1;else if (i > cur && j <= cur) this._index = cur + 1;
      this._squelchSlotChange = true;
      this.insertBefore(slide, ref);
      this._emitDeckChange({
        action: 'move',
        from: i,
        to: j,
        slide
      });
      this._collectSlides();
      this._applyIndex({
        showOverlay: false,
        broadcast: true,
        reason: 'mutation'
      });
    }

    // Public API ------------------------------------------------------------

    /** Current slide index (0-based). */
    get index() {
      return this._index;
    }
    /** Total slide count. */
    get length() {
      return this._slides.length;
    }
    /** Programmatically navigate. */
    goTo(i) {
      this._go(i, 'api');
    }
    next() {
      this._advance(1, 'api');
    }
    prev() {
      this._advance(-1, 'api');
    }
    reset() {
      this._go(0, 'api');
    }
  }
  if (!customElements.get('deck-stage')) {
    customElements.define('deck-stage', DeckStage);
  }
})();
})(); } catch (e) { __ds_ns.__errors.push({ path: "slides/deck-stage.js", error: String((e && e.message) || e) }); }

// ui_kits/marketing/MarketingSite.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
/* Lucide helper — converts <i data-lucide> after every commit. */
function useLucide() {
  React.useLayoutEffect(() => {
    if (window.lucide) window.lucide.createIcons();
  });
}
const I = (n, p = {}) => /*#__PURE__*/React.createElement("i", _extends({
  "data-lucide": n
}, p));

/* Carbon photo placeholder — stands in for engine/vehicle photography. */
function Media({
  label,
  h = 'auto',
  icon = 'image',
  ratio
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "mk-media grid-surface",
    style: {
      height: h,
      aspectRatio: ratio
    }
  }, /*#__PURE__*/React.createElement("span", {
    className: "mk-media__ico"
  }, I(icon)), /*#__PURE__*/React.createElement("span", {
    className: "mk-media__lbl"
  }, label));
}
function Header({
  theme,
  onToggleTheme
}) {
  return /*#__PURE__*/React.createElement("header", {
    className: "mk-header"
  }, /*#__PURE__*/React.createElement("div", {
    className: "mk-wrap mk-header__in"
  }, /*#__PURE__*/React.createElement(__ds_scope.Logo, {
    size: "md",
    assetBase: "../../",
    solidMark: theme === 'dark',
    href: "#"
  }), /*#__PURE__*/React.createElement("nav", {
    className: "mk-nav"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Tuning-Dateien"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Fahrzeuge"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Tools"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\xDCber uns")), /*#__PURE__*/React.createElement("div", {
    className: "mk-header__act"
  }, /*#__PURE__*/React.createElement("button", {
    className: "mk-icobtn",
    "aria-label": "Theme wechseln",
    onClick: onToggleTheme
  }, I(theme === 'dark' ? 'sun' : 'moon')), /*#__PURE__*/React.createElement("button", {
    className: "mk-icobtn",
    "aria-label": "Warenkorb"
  }, I('shopping-cart')), /*#__PURE__*/React.createElement(__ds_scope.Button, {
    size: "sm",
    variant: "secondary",
    iconLeft: I('user')
  }, "Login"))));
}
function Hero() {
  const [marke, setMarke] = React.useState('');
  return /*#__PURE__*/React.createElement("section", {
    className: "mk-hero grid-surface"
  }, /*#__PURE__*/React.createElement("div", {
    className: "mk-wrap mk-hero__in"
  }, /*#__PURE__*/React.createElement("div", {
    className: "mk-hero__copy"
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "Seit 2010 \xB7 \xFCber 8.000 Fahrzeuge"), /*#__PURE__*/React.createElement("h1", {
    className: "mk-hero__h1"
  }, "Mehr Leistung.", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("span", {
    className: "mk-accent"
  }, "Aus deiner Datei.")), /*#__PURE__*/React.createElement("p", {
    className: "mk-hero__sub"
  }, "Optimierte ECU-Kennfelder \u2014 auf dem Pr\xFCfstand abgestimmt, EU-konform und in der Regel innerhalb von 30\xA0Minuten geliefert."), /*#__PURE__*/React.createElement("div", {
    className: "mk-finder"
  }, /*#__PURE__*/React.createElement("span", {
    className: "mk-finder__t"
  }, I('search'), " Fahrzeug finden"), /*#__PURE__*/React.createElement("div", {
    className: "mk-finder__row"
  }, /*#__PURE__*/React.createElement(__ds_scope.Select, {
    placeholder: "Marke",
    value: marke,
    onChange: e => setMarke(e.target.value),
    options: ['Audi', 'BMW', 'Mercedes-Benz', 'VW', 'Porsche']
  }), /*#__PURE__*/React.createElement(__ds_scope.Select, {
    placeholder: "Modell",
    options: ['A4', 'A6', 'Q5'],
    disabled: !marke
  }), /*#__PURE__*/React.createElement(__ds_scope.Select, {
    placeholder: "Motor",
    options: ['2.0 TDI', '3.0 TDI'],
    disabled: !marke
  }), /*#__PURE__*/React.createElement(__ds_scope.Button, {
    iconRight: I('arrow-right')
  }, "Suchen")))), /*#__PURE__*/React.createElement(Media, {
    label: "HERO // 2.0 TDI auf dem Pr\xFCfstand",
    icon: "gauge",
    h: "100%"
  })));
}
function StatStrip() {
  return /*#__PURE__*/React.createElement("section", {
    className: "mk-stats mk-wrap"
  }, /*#__PURE__*/React.createElement(__ds_scope.StatTile, {
    label: "\xD8 Mehrleistung",
    value: "+34",
    unit: "PS",
    gain: true
  }), /*#__PURE__*/React.createElement(__ds_scope.StatTile, {
    label: "\xD8 Drehmoment",
    value: "+71",
    unit: "Nm"
  }), /*#__PURE__*/React.createElement(__ds_scope.StatTile, {
    label: "Lieferung",
    value: "30",
    unit: "Min"
  }), /*#__PURE__*/React.createElement(__ds_scope.StatTile, {
    label: "Fahrzeuge",
    value: "8.000",
    unit: "+"
  }));
}
const STAGES = [{
  n: '1',
  title: 'Stage 1',
  desc: 'Reine Software-Optimierung. Serie auf Optimum, ohne Hardware.',
  ps: '+38',
  nm: '+72',
  price: 'ab 199 €',
  feat: true
}, {
  n: '2',
  title: 'Stage 2',
  desc: 'Software plus Hardware (Downpipe, Ladeluftkühler). Maximaler Zuwachs.',
  ps: '+62',
  nm: '+110',
  price: 'ab 349 €'
}, {
  n: 'E',
  title: 'Eco-Tuning',
  desc: 'Auf Verbrauch optimiert — mehr Drehmoment im Teillastbereich.',
  ps: '+18',
  nm: '+55',
  price: 'ab 179 €'
}];
function Stages() {
  return /*#__PURE__*/React.createElement("section", {
    className: "mk-wrap mk-stages"
  }, /*#__PURE__*/React.createElement("div", {
    className: "mk-sec-head"
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "Tuning-Stufen"), /*#__PURE__*/React.createElement("h2", null, "W\xE4hle deine Stufe")), /*#__PURE__*/React.createElement("div", {
    className: "mk-stage-grid"
  }, STAGES.map(s => /*#__PURE__*/React.createElement(__ds_scope.Card, {
    key: s.n,
    featured: s.feat,
    badge: /*#__PURE__*/React.createElement(__ds_scope.Badge, {
      solid: s.feat,
      tone: s.feat ? undefined : 'neutral'
    }, `Stage ${s.n}`),
    title: s.title,
    footer: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("span", {
      className: "mk-price"
    }, s.price), /*#__PURE__*/React.createElement(__ds_scope.Button, {
      size: "sm",
      variant: s.feat ? 'primary' : 'secondary',
      iconRight: I('arrow-right')
    }, "W\xE4hlen"))
  }, /*#__PURE__*/React.createElement("p", {
    className: "mk-stage-desc"
  }, s.desc), /*#__PURE__*/React.createElement("div", {
    className: "mk-stage-figs"
  }, /*#__PURE__*/React.createElement(__ds_scope.StatTile, {
    label: "Leistung",
    value: s.ps,
    unit: "PS",
    gain: true
  }), /*#__PURE__*/React.createElement(__ds_scope.StatTile, {
    label: "Drehmoment",
    value: s.nm,
    unit: "Nm"
  }))))));
}
const TRUST = [{
  i: 'shield-check',
  t: 'TÜV-konform',
  d: 'Abgestimmt auf Serienzuverlässigkeit, auf Wunsch mit Gutachten.'
}, {
  i: 'gauge',
  t: 'Prüfstand-getestet',
  d: 'Jedes Kennfeld auf dem Leistungsprüfstand validiert.'
}, {
  i: 'timer',
  t: '30-Min-Lieferung',
  d: 'Datei hochladen, optimierte Datei zurückerhalten — meist sofort.'
}, {
  i: 'headset',
  t: 'Support vom Profi',
  d: 'Direkter Draht zu erfahrenen Tunern, kein Callcenter.'
}];
function Trust() {
  return /*#__PURE__*/React.createElement("section", {
    className: "mk-trust grid-surface"
  }, /*#__PURE__*/React.createElement("div", {
    className: "mk-wrap"
  }, /*#__PURE__*/React.createElement("div", {
    className: "mk-sec-head"
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "Warum Chiptuningfile"), /*#__PURE__*/React.createElement("h2", {
    className: "mk-on-dark"
  }, "Engineering, kein Marketing")), /*#__PURE__*/React.createElement("div", {
    className: "mk-trust-grid"
  }, TRUST.map(x => /*#__PURE__*/React.createElement("div", {
    className: "mk-trust-item",
    key: x.t
  }, /*#__PURE__*/React.createElement("span", {
    className: "mk-trust-ico"
  }, I(x.i)), /*#__PURE__*/React.createElement("h4", null, x.t), /*#__PURE__*/React.createElement("p", null, x.d))))));
}
function CTA() {
  return /*#__PURE__*/React.createElement("section", {
    className: "mk-wrap mk-cta"
  }, /*#__PURE__*/React.createElement("div", {
    className: "mk-cta__box"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "Bereit?"), /*#__PURE__*/React.createElement("h2", null, "Lade deine Datei hoch"), /*#__PURE__*/React.createElement("p", null, "Originaldatei auslesen, hochladen, optimierte Version zur\xFCckerhalten.")), /*#__PURE__*/React.createElement("div", {
    className: "mk-cta__act"
  }, /*#__PURE__*/React.createElement(__ds_scope.Button, {
    size: "lg",
    iconLeft: I('upload')
  }, "Datei hochladen"), /*#__PURE__*/React.createElement(__ds_scope.Button, {
    size: "lg",
    variant: "secondary",
    iconRight: I('arrow-right')
  }, "Fahrzeug w\xE4hlen"))));
}
function Footer({
  theme
}) {
  return /*#__PURE__*/React.createElement("footer", {
    className: "mk-footer"
  }, /*#__PURE__*/React.createElement("div", {
    className: "mk-wrap mk-footer__in"
  }, /*#__PURE__*/React.createElement("div", {
    className: "mk-footer__brand"
  }, /*#__PURE__*/React.createElement(__ds_scope.Logo, {
    size: "sm",
    assetBase: "../../",
    solidMark: theme === 'dark'
  }), /*#__PURE__*/React.createElement("p", null, "Optimierte ECU-Tuning-Dateien f\xFCr PKW, LKW, Motorrad & mehr.")), /*#__PURE__*/React.createElement("div", {
    className: "mk-footer__cols"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h5", null, "Produkte"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Tuning-Dateien"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Stage 1 / 2"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Eco-Tuning"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Tools")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h5", null, "Service"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Fahrzeugsuche"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "H\xE4ndlerbereich"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Support"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "FAQ")), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h5", null, "Unternehmen"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "\xDCber uns"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Kontakt"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Impressum"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Datenschutz")))), /*#__PURE__*/React.createElement("div", {
    className: "mk-wrap mk-footer__bar"
  }, /*#__PURE__*/React.createElement("span", null, "\xA9 2026 Chiptuningfile.de"), /*#__PURE__*/React.createElement("span", {
    className: "mk-mono"
  }, "ECU PERFORMANCE FILES")));
}
function MarketingSite({
  initialTheme = 'dark'
} = {}) {
  const [theme, setTheme] = React.useState(initialTheme);
  useLucide();
  return /*#__PURE__*/React.createElement("div", {
    className: "mk-root",
    "data-theme": theme
  }, /*#__PURE__*/React.createElement(Header, {
    theme: theme,
    onToggleTheme: () => setTheme(theme === 'dark' ? 'light' : 'dark')
  }), /*#__PURE__*/React.createElement(Hero, null), /*#__PURE__*/React.createElement(StatStrip, null), /*#__PURE__*/React.createElement(Stages, null), /*#__PURE__*/React.createElement(Trust, null), /*#__PURE__*/React.createElement(CTA, null), /*#__PURE__*/React.createElement(Footer, {
    theme: theme
  }));
}
Object.assign(__ds_scope, { useLucide, MarketingSite });
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/marketing/MarketingSite.jsx", error: String((e && e.message) || e) }); }

// ui_kits/catalog/CatalogApp.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const I = (n, p = {}) => /*#__PURE__*/React.createElement("i", _extends({
  "data-lucide": n
}, p));
const RESULTS = [{
  id: 'a',
  motor: '2.0 TDI',
  code: 'EA288',
  ps: 150,
  tps: 188,
  nm: 340,
  tnm: 410,
  fuel: 'Diesel',
  years: '2015–2020'
}, {
  id: 'b',
  motor: '2.0 TDI',
  code: 'EA288 Evo',
  ps: 190,
  tps: 228,
  nm: 400,
  tnm: 470,
  fuel: 'Diesel',
  years: '2020–'
}, {
  id: 'c',
  motor: '1.4 TSI',
  code: 'EA211',
  ps: 150,
  tps: 180,
  nm: 250,
  tnm: 310,
  fuel: 'Benzin',
  years: '2017–'
}, {
  id: 'd',
  motor: '3.0 TDI',
  code: 'EA897',
  ps: 272,
  tps: 334,
  nm: 600,
  tnm: 720,
  fuel: 'Diesel',
  years: '2016–'
}];
function TopBar({
  theme,
  onToggle,
  q,
  setQ
}) {
  return /*#__PURE__*/React.createElement("header", {
    className: "ct-top"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ct-top__in"
  }, /*#__PURE__*/React.createElement(__ds_scope.Logo, {
    size: "sm",
    assetBase: "../../",
    solidMark: theme === 'dark',
    href: "#"
  }), /*#__PURE__*/React.createElement("div", {
    className: "ct-search"
  }, /*#__PURE__*/React.createElement(__ds_scope.Input, {
    placeholder: "Fahrzeug, Motor oder ECU suchen\u2026",
    value: q,
    onChange: e => setQ(e.target.value),
    prefix: I('search')
  })), /*#__PURE__*/React.createElement("div", {
    className: "ct-top__act"
  }, /*#__PURE__*/React.createElement(__ds_scope.IconButton, {
    label: "Theme",
    variant: "ghost",
    onClick: onToggle
  }, I(theme === 'dark' ? 'sun' : 'moon')), /*#__PURE__*/React.createElement(__ds_scope.IconButton, {
    label: "Konto",
    variant: "ghost"
  }, I('user')), /*#__PURE__*/React.createElement(__ds_scope.Button, {
    size: "sm",
    iconLeft: I('upload')
  }, "Datei hochladen"))));
}
function Filters({
  marke,
  setMarke
}) {
  return /*#__PURE__*/React.createElement("aside", {
    className: "ct-filters"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ct-panel"
  }, /*#__PURE__*/React.createElement("h3", {
    className: "ct-panel__h"
  }, "Fahrzeug w\xE4hlen"), /*#__PURE__*/React.createElement("div", {
    className: "ct-fields"
  }, /*#__PURE__*/React.createElement(__ds_scope.Select, {
    label: "Marke",
    placeholder: "Marke",
    value: marke,
    onChange: e => setMarke(e.target.value),
    options: ['Audi', 'BMW', 'Mercedes-Benz', 'VW', 'Seat', 'Škoda']
  }), /*#__PURE__*/React.createElement(__ds_scope.Select, {
    label: "Modell",
    placeholder: "Modell",
    options: ['A4', 'A5', 'A6', 'Q5'],
    disabled: !marke
  }), /*#__PURE__*/React.createElement(__ds_scope.Select, {
    label: "Baujahr",
    placeholder: "Baujahr",
    options: ['2024', '2022', '2020', '2018', '2016'],
    disabled: !marke
  }), /*#__PURE__*/React.createElement(__ds_scope.Select, {
    label: "Motor",
    placeholder: "Motorisierung",
    options: ['2.0 TDI', '2.0 TFSI', '3.0 TDI'],
    disabled: !marke
  }))), /*#__PURE__*/React.createElement("div", {
    className: "ct-panel"
  }, /*#__PURE__*/React.createElement("h3", {
    className: "ct-panel__h"
  }, "Filter"), /*#__PURE__*/React.createElement("div", {
    className: "ct-facets"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ct-facet-lbl"
  }, "Kraftstoff"), /*#__PURE__*/React.createElement("div", {
    className: "ct-tags"
  }, /*#__PURE__*/React.createElement(__ds_scope.Tag, {
    active: true,
    onRemove: () => {}
  }, "Diesel"), /*#__PURE__*/React.createElement(__ds_scope.Tag, null, "Benzin"), /*#__PURE__*/React.createElement(__ds_scope.Tag, null, "Hybrid")), /*#__PURE__*/React.createElement("span", {
    className: "ct-facet-lbl"
  }, "Ma\xDFnahme"), /*#__PURE__*/React.createElement("div", {
    className: "ct-tags"
  }, /*#__PURE__*/React.createElement(__ds_scope.Tag, null, "Stage 1"), /*#__PURE__*/React.createElement(__ds_scope.Tag, null, "Stage 2"), /*#__PURE__*/React.createElement(__ds_scope.Tag, null, "EGR/DPF"), /*#__PURE__*/React.createElement(__ds_scope.Tag, null, "AdBlue")))));
}
function ResultRow({
  r,
  onOpen
}) {
  return /*#__PURE__*/React.createElement("button", {
    className: "ct-row",
    onClick: () => onOpen(r)
  }, /*#__PURE__*/React.createElement("div", {
    className: "ct-row__veh"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ct-row__motor"
  }, r.motor), /*#__PURE__*/React.createElement("span", {
    className: "ct-row__code"
  }, r.code, " \xB7 ", r.years)), /*#__PURE__*/React.createElement(__ds_scope.Badge, {
    tone: r.fuel === 'Diesel' ? 'info' : 'warning'
  }, r.fuel), /*#__PURE__*/React.createElement("div", {
    className: "ct-row__fig"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ct-row__k"
  }, "Serie"), /*#__PURE__*/React.createElement("b", null, r.ps, " PS")), /*#__PURE__*/React.createElement("div", {
    className: "ct-row__fig ct-row__fig--gain"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ct-row__k"
  }, "Stage 1"), /*#__PURE__*/React.createElement("b", null, r.tps, " PS")), /*#__PURE__*/React.createElement("div", {
    className: "ct-row__delta"
  }, I('trending-up'), " +", r.tps - r.ps), /*#__PURE__*/React.createElement("span", {
    className: "ct-row__go"
  }, I('chevron-right')));
}
function Browse({
  marke,
  setMarke,
  onOpen,
  count
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "ct-grid"
  }, /*#__PURE__*/React.createElement(Filters, {
    marke: marke,
    setMarke: setMarke
  }), /*#__PURE__*/React.createElement("main", {
    className: "ct-results"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ct-results__head"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, count, " Motorisierungen"), /*#__PURE__*/React.createElement("h2", null, "Audi A4 (B9)")), /*#__PURE__*/React.createElement(__ds_scope.Select, {
    placeholder: "Sortierung",
    options: ['Mehrleistung', 'Hubraum', 'Baujahr']
  })), /*#__PURE__*/React.createElement("div", {
    className: "ct-rows"
  }, RESULTS.map(r => /*#__PURE__*/React.createElement(ResultRow, {
    key: r.id,
    r: r,
    onOpen: onOpen
  })))));
}
function Detail({
  r,
  onBack
}) {
  const [stage, setStage] = React.useState('s1');
  const tps = stage === 's1' ? r.tps : r.tps + 26;
  const tnm = stage === 's1' ? r.tnm : r.tnm + 40;
  return /*#__PURE__*/React.createElement("div", {
    className: "ct-detail"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ct-detail__bar"
  }, /*#__PURE__*/React.createElement(__ds_scope.Button, {
    variant: "ghost",
    size: "sm",
    iconLeft: I('arrow-left'),
    onClick: onBack
  }, "Zur\xFCck")), /*#__PURE__*/React.createElement("div", {
    className: "ct-detail__head"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "Audi A4 (B9) \xB7 ", r.fuel), /*#__PURE__*/React.createElement("h1", null, r.motor, " ", /*#__PURE__*/React.createElement("span", {
    className: "ct-muted"
  }, r.code))), /*#__PURE__*/React.createElement(__ds_scope.Badge, {
    solid: true
  }, "Verf\xFCgbar")), /*#__PURE__*/React.createElement("div", {
    className: "ct-detail__grid"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ct-dyno grid-surface"
  }, /*#__PURE__*/React.createElement("div", {
    className: "ct-dyno__top"
  }, /*#__PURE__*/React.createElement("span", {
    className: "ct-mono"
  }, "LEISTUNGSDIAGRAMM"), /*#__PURE__*/React.createElement("span", {
    className: "ct-mono ct-muted"
  }, "PS / Nm \xFCber Drehzahl")), /*#__PURE__*/React.createElement("div", {
    className: "ct-dyno__plot"
  }, /*#__PURE__*/React.createElement("svg", {
    viewBox: "0 0 400 200",
    preserveAspectRatio: "none",
    className: "ct-dyno__svg"
  }, /*#__PURE__*/React.createElement("path", {
    d: "M0,185 C120,170 240,70 400,40",
    fill: "none",
    stroke: "var(--carbon-500)",
    "stroke-width": "2",
    "stroke-dasharray": "5 4"
  }), /*#__PURE__*/React.createElement("path", {
    d: "M0,180 C120,150 240,40 400,18",
    fill: "none",
    stroke: "var(--accent)",
    "stroke-width": "3"
  })), /*#__PURE__*/React.createElement("div", {
    className: "ct-dyno__legend"
  }, /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("i", {
    className: "dot dot--serie"
  }), "Serie"), /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("i", {
    className: "dot dot--tuned"
  }), "Stage")))), /*#__PURE__*/React.createElement("div", {
    className: "ct-detail__side"
  }, /*#__PURE__*/React.createElement(__ds_scope.Tabs, {
    value: stage,
    onChange: setStage,
    tabs: [{
      id: 's1',
      label: 'Stage 1'
    }, {
      id: 's2',
      label: 'Stage 2'
    }]
  }), /*#__PURE__*/React.createElement("div", {
    className: "ct-figs"
  }, /*#__PURE__*/React.createElement(__ds_scope.StatTile, {
    label: "Leistung Serie",
    value: r.ps,
    unit: "PS",
    dark: true
  }), /*#__PURE__*/React.createElement(__ds_scope.StatTile, {
    label: "Leistung Stage",
    value: tps,
    unit: "PS",
    gain: true,
    dark: true
  }), /*#__PURE__*/React.createElement(__ds_scope.StatTile, {
    label: "Drehmoment Serie",
    value: r.nm,
    unit: "Nm",
    dark: true
  }), /*#__PURE__*/React.createElement(__ds_scope.StatTile, {
    label: "Drehmoment Stage",
    value: tnm,
    unit: "Nm",
    gain: true,
    dark: true
  })), /*#__PURE__*/React.createElement("div", {
    className: "ct-order"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", {
    className: "ct-mono ct-muted"
  }, "Mehrleistung"), /*#__PURE__*/React.createElement("div", {
    className: "ct-order__big"
  }, "+", tps - r.ps, " PS \xB7 +", tnm - r.nm, " Nm")), /*#__PURE__*/React.createElement(__ds_scope.Button, {
    block: true,
    size: "lg",
    iconRight: I('arrow-right')
  }, "F\xFCr 199 \u20AC konfigurieren"), /*#__PURE__*/React.createElement("p", {
    className: "ct-order__note"
  }, I('shield-check'), " Auf dem Pr\xFCfstand abgestimmt \xB7 EU-konform \xB7 30-Min-Lieferung")))));
}
function CatalogApp({
  initialTheme = 'dark'
} = {}) {
  const [theme, setTheme] = React.useState(initialTheme);
  const [marke, setMarke] = React.useState('Audi');
  const [q, setQ] = React.useState('');
  const [sel, setSel] = React.useState(null);
  __ds_scope.useLucide();
  return /*#__PURE__*/React.createElement("div", {
    className: "ct-root",
    "data-theme": theme
  }, /*#__PURE__*/React.createElement(TopBar, {
    theme: theme,
    onToggle: () => setTheme(theme === 'dark' ? 'light' : 'dark'),
    q: q,
    setQ: setQ
  }), /*#__PURE__*/React.createElement("div", {
    className: "ct-wrap"
  }, /*#__PURE__*/React.createElement(__ds_scope.Breadcrumb, {
    items: [{
      label: 'Start',
      href: '#'
    }, {
      label: 'PKW',
      href: '#'
    }, {
      label: 'Audi',
      href: '#'
    }, sel ? `A4 ${sel.motor}` : 'A4 (B9)']
  }), sel ? /*#__PURE__*/React.createElement(Detail, {
    r: sel,
    onBack: () => setSel(null)
  }) : /*#__PURE__*/React.createElement(Browse, {
    marke: marke,
    setMarke: setMarke,
    onOpen: setSel,
    count: RESULTS.length
  })));
}
Object.assign(__ds_scope, { CatalogApp });
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/catalog/CatalogApp.jsx", error: String((e && e.message) || e) }); }

// ui_kits/portal/PortalApp.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const I = (n, p = {}) => /*#__PURE__*/React.createElement("i", _extends({
  "data-lucide": n
}, p));

/* ---- Sidebar ---- */
const NAV = [{
  type: 'item',
  icon: 'layout-dashboard',
  label: 'Dashboard'
}, {
  type: 'group',
  icon: 'car-front',
  label: 'Tuning File',
  open: true,
  children: [{
    icon: 'file-plus-2',
    label: 'Neuer Auftrag'
  }, {
    icon: 'history',
    label: 'Alle Aufträge'
  }, {
    icon: 'list',
    label: 'Preisliste'
  }, {
    icon: 'bar-chart-3',
    label: 'Statistiken'
  }]
}, {
  type: 'item',
  icon: 'sliders-horizontal',
  label: 'Konfigurator'
}, {
  type: 'item',
  icon: 'triangle-alert',
  label: 'DTC Codes'
}, {
  type: 'group',
  icon: 'circle-dollar-sign',
  label: 'Credits',
  open: true,
  children: [{
    icon: 'shopping-cart',
    label: 'Kaufen'
  }, {
    icon: 'receipt',
    label: 'Rechnungen'
  }]
}, {
  type: 'item',
  icon: 'ticket',
  label: 'Tickets'
}, {
  type: 'item',
  icon: 'mail',
  label: 'E-Mail Templates'
}, {
  type: 'item',
  icon: 'user',
  label: 'Profil'
}];
function PortalSidebar({
  theme,
  active = 'Neuer Auftrag'
}) {
  return /*#__PURE__*/React.createElement("aside", {
    className: "pt-side"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pt-side__logo"
  }, /*#__PURE__*/React.createElement(__ds_scope.Logo, {
    size: "sm",
    assetBase: "../../",
    tagline: "",
    solidMark: theme === 'dark',
    href: "#"
  })), /*#__PURE__*/React.createElement("nav", {
    className: "pt-nav"
  }, NAV.map((n, i) => n.type === 'item' ? /*#__PURE__*/React.createElement("a", {
    key: i,
    className: `pt-navitem${n.label === active ? ' is-active' : ''}`,
    href: "#"
  }, I(n.icon), /*#__PURE__*/React.createElement("span", null, n.label)) : /*#__PURE__*/React.createElement("div", {
    key: i,
    className: "pt-navgroup"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pt-navgroup__h"
  }, I(n.icon), /*#__PURE__*/React.createElement("span", null, n.label), I('chevron-up', {
    className: 'pt-navgroup__chev'
  })), /*#__PURE__*/React.createElement("div", {
    className: "pt-navgroup__kids"
  }, n.children.map((c, j) => /*#__PURE__*/React.createElement("a", {
    key: j,
    className: `pt-navitem pt-navitem--sub${c.label === active ? ' is-active' : ''}`,
    href: "#"
  }, I(c.icon), /*#__PURE__*/React.createElement("span", null, c.label))))))), /*#__PURE__*/React.createElement("a", {
    className: "pt-navitem pt-side__out",
    href: "#"
  }, I('log-out'), /*#__PURE__*/React.createElement("span", null, "Abmelden")));
}

/* ---- Top bar ---- */
function PortalTopBar({
  theme,
  onToggle
}) {
  return /*#__PURE__*/React.createElement("header", {
    className: "pt-top"
  }, /*#__PURE__*/React.createElement("label", {
    className: "pt-search"
  }, I('search'), /*#__PURE__*/React.createElement("input", {
    placeholder: "Globale Suche, Aktionen und Dateien\u2026"
  }), /*#__PURE__*/React.createElement("kbd", null, "CMD K")), /*#__PURE__*/React.createElement("div", {
    className: "pt-top__act"
  }, /*#__PURE__*/React.createElement(__ds_scope.Button, {
    size: "sm",
    iconLeft: I('file-text')
  }, "Auftrag"), /*#__PURE__*/React.createElement("button", {
    className: "pt-credits"
  }, I('circle-dollar-sign'), /*#__PURE__*/React.createElement("span", null, "Credits kaufen ", /*#__PURE__*/React.createElement("b", null, "(72.140)"))), /*#__PURE__*/React.createElement(__ds_scope.IconButton, {
    label: "Theme",
    variant: "ghost",
    onClick: onToggle
  }, I(theme === 'dark' ? 'sun' : 'moon')), /*#__PURE__*/React.createElement("button", {
    className: "pt-bell",
    "aria-label": "Benachrichtigungen"
  }, I('bell'), /*#__PURE__*/React.createElement("span", {
    className: "pt-bell__n"
  }, "8")), /*#__PURE__*/React.createElement("button", {
    className: "pt-lang"
  }, I('globe'), /*#__PURE__*/React.createElement("span", null, "DE")), /*#__PURE__*/React.createElement("span", {
    className: "pt-avatar"
  }, "AS")));
}

/* ---- Stepper ---- */
const STEPS = [{
  icon: 'upload',
  label: 'File & Lesegerät'
}, {
  icon: 'car-front',
  label: 'Fahrzeug + Tuning'
}, {
  icon: 'circle-check-big',
  label: 'Übersicht'
}];
function Stepper({
  current
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "pt-stepper"
  }, STEPS.map((s, i) => /*#__PURE__*/React.createElement(React.Fragment, {
    key: i
  }, /*#__PURE__*/React.createElement("div", {
    className: `pt-step${i === current ? ' is-active' : ''}${i < current ? ' is-done' : ''}`
  }, /*#__PURE__*/React.createElement("span", {
    className: "pt-step__ico"
  }, I(s.icon)), /*#__PURE__*/React.createElement("span", {
    className: "pt-step__lbl"
  }, s.label)), i < STEPS.length - 1 && /*#__PURE__*/React.createElement("span", {
    className: `pt-step__line${i < current ? ' is-done' : ''}`
  }))));
}

/* ---- Segmented option group ---- */
function SegGroup({
  icon,
  label,
  options,
  value,
  onChange
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: "pt-seg"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pt-seg__lbl"
  }, I(icon), /*#__PURE__*/React.createElement("span", null, label)), /*#__PURE__*/React.createElement("div", {
    className: "pt-seg__opts",
    style: {
      gridTemplateColumns: `repeat(${options.length}, 1fr)`
    }
  }, options.map(o => /*#__PURE__*/React.createElement("button", {
    key: o.value,
    className: `pt-opt${value === o.value ? ' is-on' : ''}`,
    onClick: () => onChange(o.value)
  }, o.icon && I(o.icon), /*#__PURE__*/React.createElement("span", null, o.label), o.price && /*#__PURE__*/React.createElement("em", {
    className: "pt-opt__price"
  }, o.price)))));
}
function PortalApp({
  initialTheme = 'light'
} = {}) {
  const [theme, setTheme] = React.useState(initialTheme);
  const [cfg, setCfg] = React.useState({
    method: 'obd',
    read: 'virtuell',
    ms: 'slave',
    prio: 'normal',
    winols: 'match'
  });
  const set = k => v => setCfg(c => ({
    ...c,
    [k]: v
  }));
  __ds_scope.useLucide();
  return /*#__PURE__*/React.createElement("div", {
    className: "pt-root",
    "data-theme": theme
  }, /*#__PURE__*/React.createElement(PortalSidebar, {
    theme: theme,
    active: "Neuer Auftrag"
  }), /*#__PURE__*/React.createElement("div", {
    className: "pt-main"
  }, /*#__PURE__*/React.createElement(PortalTopBar, {
    theme: theme,
    onToggle: () => setTheme(theme === 'dark' ? 'light' : 'dark')
  }), /*#__PURE__*/React.createElement("div", {
    className: "pt-scroll"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pt-card pt-stepcard"
  }, /*#__PURE__*/React.createElement(Stepper, {
    current: 0
  })), /*#__PURE__*/React.createElement("div", {
    className: "pt-head"
  }, /*#__PURE__*/React.createElement("h1", null, "File & Leseger\xE4t"), /*#__PURE__*/React.createElement("span", {
    className: "pt-step-tag"
  }, "Step 01")), /*#__PURE__*/React.createElement("div", {
    className: "pt-grid"
  }, /*#__PURE__*/React.createElement("section", {
    className: "pt-card pt-options"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pt-field"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pt-seg__lbl"
  }, I('wrench'), /*#__PURE__*/React.createElement("span", null, "Tuning Tool / Leseger\xE4t")), /*#__PURE__*/React.createElement(__ds_scope.Select, {
    options: [{
      value: 'autotuner',
      label: 'Autotuner'
    }, {
      value: 'flash',
      label: 'Flash-Interface V3'
    }],
    placeholder: "Tool"
  })), /*#__PURE__*/React.createElement(SegGroup, {
    icon: "scan-line",
    label: "Lesemethode",
    value: cfg.method,
    onChange: set('method'),
    options: [{
      value: 'obd',
      label: 'OBD'
    }, {
      value: 'bench',
      label: 'Bench'
    }, {
      value: 'boot',
      label: 'Boot'
    }]
  }), /*#__PURE__*/React.createElement(SegGroup, {
    icon: "book-open",
    label: "Leseart",
    value: cfg.read,
    onChange: set('read'),
    options: [{
      value: 'virtuell',
      label: 'Virtuell',
      icon: 'cloud-download'
    }, {
      value: 'full',
      label: 'Full Read',
      icon: 'hard-drive'
    }]
  }), /*#__PURE__*/React.createElement(SegGroup, {
    icon: "git-branch",
    label: "Master / Slave",
    value: cfg.ms,
    onChange: set('ms'),
    options: [{
      value: 'slave',
      label: 'Slave',
      icon: 'link'
    }, {
      value: 'master',
      label: 'Master',
      icon: 'crown'
    }]
  }), /*#__PURE__*/React.createElement(SegGroup, {
    icon: "timer",
    label: "Priorit\xE4t",
    value: cfg.prio,
    onChange: set('prio'),
    options: [{
      value: 'normal',
      label: 'Normal'
    }, {
      value: 'express',
      label: 'Express',
      price: '+49€'
    }, {
      value: 'sofort',
      label: 'Sofort',
      price: '+99€'
    }]
  }), /*#__PURE__*/React.createElement(SegGroup, {
    icon: "zap",
    label: "WinOLS Test-Modus",
    value: cfg.winols,
    onChange: set('winols'),
    options: [{
      value: 'match',
      label: 'Match positiv (Auto-Fertig)'
    }, {
      value: 'nomatch',
      label: 'Kein Match (manuell/offen)'
    }]
  })), /*#__PURE__*/React.createElement("section", {
    className: "pt-card pt-drop"
  }, /*#__PURE__*/React.createElement(__ds_scope.FileUpload, {
    title: "Datei hierher ziehen",
    hint: ".bin .ori .mod .ecu .hex .s19 .bkp .slave",
    accept: ".bin,.ori,.mod,.ecu,.hex,.s19,.bkp,.slave"
  }))), /*#__PURE__*/React.createElement("section", {
    className: "pt-card pt-comment"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pt-seg__lbl"
  }, I('message-square'), /*#__PURE__*/React.createElement("span", null, "Kommentar (optional)")), /*#__PURE__*/React.createElement("textarea", {
    rows: 4,
    placeholder: "Zus\xE4tzliche Hinweise, W\xFCnsche oder Informationen\u2026"
  })), /*#__PURE__*/React.createElement("div", {
    className: "pt-foot"
  }, /*#__PURE__*/React.createElement(__ds_scope.Button, {
    size: "lg",
    iconRight: I('arrow-right')
  }, "Weiter zu Schritt 2")))));
}
Object.assign(__ds_scope, { PortalSidebar, PortalTopBar, PortalApp });
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/portal/PortalApp.jsx", error: String((e && e.message) || e) }); }

// ui_kits/portal/CreditsApp.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const I = (n, p = {}) => /*#__PURE__*/React.createElement("i", _extends({
  "data-lucide": n
}, p));
const eur = n => n.toLocaleString('de-DE', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2
}) + ' €';
const PACKS = [{
  credits: 10,
  net: 12.0,
  gross: 14.28,
  per: null,
  note: '10 Credits'
}, {
  credits: 10,
  net: 60.0,
  gross: 71.40,
  per: '60€',
  note: '10 OLSx-Credits'
}, {
  credits: 50,
  net: 58.0,
  gross: 69.02,
  per: null,
  note: '50 Credits'
}, {
  credits: 100,
  net: 115.0,
  gross: 136.85,
  per: '115€',
  note: '100 Credits'
}, {
  credits: 500,
  net: 550.0,
  gross: 654.50,
  per: '110€',
  note: '500 Credits'
}, {
  credits: 1000,
  net: 1050.0,
  gross: 1249.50,
  per: '105€',
  note: '1000 Credits'
}, {
  credits: 2000,
  net: 2000.0,
  gross: 2380.00,
  per: '100€',
  note: '2000 Credits'
}, {
  credits: 3000,
  net: 2850.0,
  gross: 3391.50,
  per: '95€',
  note: '3000 Credits'
}, {
  credits: 4000,
  net: 3600.0,
  gross: 4284.00,
  per: '90€',
  note: '4000 Credits'
}, {
  credits: 5000,
  net: 4250.0,
  gross: 5057.50,
  per: '85€',
  note: '5000 Credits'
}];
const BUCHUNGEN = [{
  datum: '22.05.2026, 03:19',
  typ: 'Verbrauch',
  details: 'Auftrag A-000144 | Audi A6 C7 · 2011 3.0 TDI EU6 · 218 PS | Stage 1 | 150 Credits',
  zahlung: '—',
  credits: -150,
  saldo: 72140
}, {
  datum: '22.05.2026, 03:18',
  typ: 'Verbrauch',
  details: 'Auftrag A-000143 | Audi A6 2011 · C7 3.0 TDI EU6 (FWD) 218 PS · Diesel | Stage 1 | 200 Credits',
  zahlung: '—',
  credits: -200,
  saldo: 72290
}, {
  datum: '23.04.2026, 10:09',
  typ: 'Verbrauch',
  details: 'Auftrag A-000142 | Audi A6 2011 · C7 3.0 TDI EU6 (FWD) 218 PS · Diesel | Stage 1 | 150 Credits',
  zahlung: '—',
  credits: -150,
  saldo: 72490
}, {
  datum: '21.04.2026, 07:17',
  typ: 'Aufladung',
  details: 'Zahlungseingang bestätigt (700 Credits) · Ursprung Buchung #97',
  zahlung: 'Vorkasse',
  credits: 700,
  saldo: 72640
}];
function Pack({
  p,
  selected,
  onSelect
}) {
  return /*#__PURE__*/React.createElement("div", {
    className: `cr-pack${selected ? ' is-sel' : ''}`,
    onClick: onSelect
  }, /*#__PURE__*/React.createElement("div", {
    className: "cr-pack__num"
  }, p.credits.toLocaleString('de-DE')), /*#__PURE__*/React.createElement("div", {
    className: "cr-pack__unit"
  }, "Credits"), /*#__PURE__*/React.createElement("div", {
    className: "cr-pack__price"
  }, eur(p.net)), /*#__PURE__*/React.createElement("div", {
    className: "cr-pack__gross"
  }, eur(p.gross), " inkl. MwSt."), /*#__PURE__*/React.createElement("div", {
    className: "cr-pack__rule"
  }), /*#__PURE__*/React.createElement("div", {
    className: "cr-pack__eig"
  }, "Eigenschaften"), /*#__PURE__*/React.createElement("div", {
    className: "cr-pack__feat"
  }, I('check'), /*#__PURE__*/React.createElement("span", null, p.note)), p.per ? /*#__PURE__*/React.createElement("span", {
    className: "cr-pack__per"
  }, I('tag'), p.per, " / File") : /*#__PURE__*/React.createElement("span", {
    className: "cr-pack__per cr-pack__per--muted"
  }, I('tag'), "Staffelpreis"), /*#__PURE__*/React.createElement(__ds_scope.Button, {
    block: true,
    size: "sm",
    variant: selected ? 'primary' : 'secondary',
    onClick: e => {
      e.stopPropagation();
      onSelect();
    }
  }, "Jetzt kaufen"));
}
function CreditsApp({
  initialTheme = 'light'
} = {}) {
  const [theme, setTheme] = React.useState(initialTheme);
  const [sel, setSel] = React.useState(null);
  __ds_scope.useLucide();
  const cols = [{
    key: 'datum',
    header: 'Datum',
    render: r => /*#__PURE__*/React.createElement("span", {
      className: "cr-mono"
    }, r.datum)
  }, {
    key: 'typ',
    header: 'Typ',
    render: r => /*#__PURE__*/React.createElement(__ds_scope.Badge, {
      tone: r.credits < 0 ? 'neutral' : 'success'
    }, r.typ)
  }, {
    key: 'details',
    header: 'Details'
  }, {
    key: 'zahlung',
    header: 'Zahlung',
    render: r => /*#__PURE__*/React.createElement("span", {
      className: "cr-muted"
    }, r.zahlung)
  }, {
    key: 'credits',
    header: 'Credits',
    numeric: true,
    render: r => /*#__PURE__*/React.createElement("b", {
      className: r.credits < 0 ? 'cr-neg' : 'cr-pos'
    }, r.credits > 0 ? '+' : '', r.credits)
  }, {
    key: 'saldo',
    header: 'Saldo',
    numeric: true,
    render: r => /*#__PURE__*/React.createElement("span", {
      className: "cr-saldo"
    }, r.saldo.toLocaleString('de-DE'))
  }];
  return /*#__PURE__*/React.createElement("div", {
    className: "pt-root",
    "data-theme": theme
  }, /*#__PURE__*/React.createElement(__ds_scope.PortalSidebar, {
    theme: theme,
    active: "Kaufen"
  }), /*#__PURE__*/React.createElement("div", {
    className: "pt-main"
  }, /*#__PURE__*/React.createElement(__ds_scope.PortalTopBar, {
    theme: theme,
    onToggle: () => setTheme(theme === 'dark' ? 'light' : 'dark')
  }), /*#__PURE__*/React.createElement("div", {
    className: "pt-scroll cr-scroll"
  }, /*#__PURE__*/React.createElement("div", {
    className: "cr-head"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h1", null, I('shopping-cart'), " Credits kaufen"), /*#__PURE__*/React.createElement("p", null, "Tuning Credits erwerben und sofort nutzen.")), /*#__PURE__*/React.createElement("div", {
    className: "cr-balance"
  }, /*#__PURE__*/React.createElement("span", {
    className: "cr-balance__ico"
  }, I('circle-dollar-sign')), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", {
    className: "cr-balance__lbl"
  }, "Dein Guthaben"), /*#__PURE__*/React.createElement("div", {
    className: "cr-balance__val"
  }, "72.140 ", /*#__PURE__*/React.createElement("em", null, "Credits"))))), /*#__PURE__*/React.createElement("div", {
    className: "cr-grid"
  }, PACKS.map((p, i) => /*#__PURE__*/React.createElement(Pack, {
    key: i,
    p: p,
    selected: sel === i,
    onSelect: () => setSel(i)
  }))), /*#__PURE__*/React.createElement("div", {
    className: "cr-row2"
  }, /*#__PURE__*/React.createElement("div", {
    className: "pt-card cr-select"
  }, I('shopping-cart'), /*#__PURE__*/React.createElement("span", null, sel == null ? 'Bitte ein Credit-Paket auswählen' : `${PACKS[sel].credits.toLocaleString('de-DE')} Credits — ${eur(PACKS[sel].net)}`)), /*#__PURE__*/React.createElement("div", {
    className: "pt-card cr-vorkasse"
  }, /*#__PURE__*/React.createElement("div", {
    className: "cr-vorkasse__txt"
  }, /*#__PURE__*/React.createElement("span", {
    className: "cr-eig"
  }, I('building-2'), " Rechnung auf Vorkasse"), /*#__PURE__*/React.createElement("p", null, "Individuelle Rechnung mit gew\xFCnschtem Betrag anfordern.")), /*#__PURE__*/React.createElement("div", {
    className: "cr-vorkasse__act"
  }, /*#__PURE__*/React.createElement("input", {
    placeholder: "z.B. 200"
  }), /*#__PURE__*/React.createElement(__ds_scope.Button, {
    variant: "secondary",
    iconLeft: I('file-text')
  }, "Rechnung anfordern")))), /*#__PURE__*/React.createElement("section", {
    className: "pt-card cr-buchungen"
  }, /*#__PURE__*/React.createElement("div", {
    className: "cr-buchungen__head"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h2", null, "Buchungen"), /*#__PURE__*/React.createElement("p", null, "Alle Credit-Buchungen deines Kontos.")), /*#__PURE__*/React.createElement(__ds_scope.Badge, {
    tone: "neutral"
  }, "78")), /*#__PURE__*/React.createElement(__ds_scope.Table, {
    columns: cols,
    rows: BUCHUNGEN
  })))));
}
Object.assign(__ds_scope, { CreditsApp });
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/portal/CreditsApp.jsx", error: String((e && e.message) || e) }); }

// ui_kits/shop/ShopApp.jsx
try { (() => {
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const I = (n, p = {}) => /*#__PURE__*/React.createElement("i", _extends({
  "data-lucide": n
}, p));
const PRODUCTS = [{
  id: 'autotuner',
  name: 'Autotuner Master',
  cat: 'Lese-/Schreibtool',
  price: 2490,
  tag: 'Bestseller',
  icon: 'cpu',
  desc: 'OBD-, Bench- und Boot-Modus in einem Gerät. Inkl. 12 Monate Updates.'
}, {
  id: 'kess',
  name: 'Flash-Interface V3',
  cat: 'OBD-Tool',
  price: 890,
  icon: 'usb',
  desc: 'Schnelles OBD-Flashen für gängige Steuergeräte.'
}, {
  id: 'bench',
  name: 'Bench-Kabelsatz Pro',
  cat: 'Zubehör',
  price: 320,
  icon: 'cable',
  desc: '40-teiliges Set für Bench- und Boot-Anschluss.'
}, {
  id: 'pds',
  name: 'Prüfstand-Mietzeit',
  cat: 'Service',
  price: 180,
  icon: 'gauge',
  desc: '1 Stunde Leistungsprüfstand inkl. Bediener.'
}, {
  id: 'credits',
  name: '10er Datei-Credits',
  cat: 'Guthaben',
  price: 450,
  tag: 'Spar-Set',
  icon: 'coins',
  desc: '10 Tuning-Dateien aus unserem Katalog, jederzeit abrufbar.'
}, {
  id: 'license',
  name: 'Händler-Lizenz',
  cat: 'Software',
  price: 990,
  icon: 'shield-check',
  desc: 'Jahreslizenz für den Händlerbereich inkl. API-Zugang.'
}];
const eur = n => n.toLocaleString('de-DE') + ' €';
function TopBar({
  theme,
  onToggle,
  cartCount,
  onOpenCart
}) {
  return /*#__PURE__*/React.createElement("header", {
    className: "sh-top"
  }, /*#__PURE__*/React.createElement("div", {
    className: "sh-top__in"
  }, /*#__PURE__*/React.createElement(__ds_scope.Logo, {
    size: "sm",
    assetBase: "../../",
    solidMark: theme === 'dark',
    href: "#"
  }), /*#__PURE__*/React.createElement("nav", {
    className: "sh-nav"
  }, /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Tools"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Zubeh\xF6r"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Credits"), /*#__PURE__*/React.createElement("a", {
    href: "#"
  }, "Service")), /*#__PURE__*/React.createElement("div", {
    className: "sh-top__act"
  }, /*#__PURE__*/React.createElement(__ds_scope.IconButton, {
    label: "Theme",
    variant: "ghost",
    onClick: onToggle
  }, I(theme === 'dark' ? 'sun' : 'moon')), /*#__PURE__*/React.createElement("button", {
    className: "sh-cart",
    onClick: onOpenCart,
    "aria-label": "Warenkorb"
  }, I('shopping-cart'), cartCount > 0 && /*#__PURE__*/React.createElement("span", {
    className: "sh-cart__n"
  }, cartCount)))));
}
function ProductCard({
  p,
  onOpen,
  onAdd
}) {
  return /*#__PURE__*/React.createElement(__ds_scope.Card, {
    interactive: true,
    featured: !!p.tag,
    media: /*#__PURE__*/React.createElement("div", {
      className: "sh-media grid-surface"
    }, I(p.icon)),
    badge: p.tag ? /*#__PURE__*/React.createElement(__ds_scope.Badge, {
      solid: true
    }, p.tag) : /*#__PURE__*/React.createElement(__ds_scope.Badge, {
      tone: "neutral"
    }, p.cat),
    title: p.name,
    footer: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("span", {
      className: "sh-price"
    }, eur(p.price)), /*#__PURE__*/React.createElement(__ds_scope.Button, {
      size: "sm",
      onClick: e => {
        e.stopPropagation();
        onAdd(p);
      },
      iconLeft: I('plus')
    }, "Add"))
  }, /*#__PURE__*/React.createElement("p", {
    className: "sh-desc",
    onClick: () => onOpen(p)
  }, p.desc));
}
function Grid({
  onOpen,
  onAdd
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
    className: "sh-hero grid-surface"
  }, /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, "Tools & Hardware"), /*#__PURE__*/React.createElement("h1", null, "Werkstatt-Equipment"), /*#__PURE__*/React.createElement("p", null, "Lese- und Schreibtools, Kabels\xE4tze und Datei-Credits \u2014 f\xFCr Profis, die selbst flashen."))), /*#__PURE__*/React.createElement("div", {
    className: "sh-grid"
  }, PRODUCTS.map(p => /*#__PURE__*/React.createElement(ProductCard, {
    key: p.id,
    p: p,
    onOpen: onOpen,
    onAdd: onAdd
  }))));
}
function Detail({
  p,
  onBack,
  onAdd
}) {
  const [variant, setVariant] = React.useState('master');
  return /*#__PURE__*/React.createElement("div", {
    className: "sh-detail"
  }, /*#__PURE__*/React.createElement("div", {
    className: "sh-detail__bar"
  }, /*#__PURE__*/React.createElement(__ds_scope.Button, {
    variant: "ghost",
    size: "sm",
    iconLeft: I('arrow-left'),
    onClick: onBack
  }, "Zum Shop")), /*#__PURE__*/React.createElement("div", {
    className: "sh-detail__grid"
  }, /*#__PURE__*/React.createElement("div", {
    className: "sh-media sh-media--lg grid-surface"
  }, I(p.icon)), /*#__PURE__*/React.createElement("div", {
    className: "sh-detail__info"
  }, /*#__PURE__*/React.createElement("span", {
    className: "eyebrow"
  }, p.cat), /*#__PURE__*/React.createElement("h1", null, p.name), /*#__PURE__*/React.createElement("p", {
    className: "sh-detail__desc"
  }, p.desc), /*#__PURE__*/React.createElement("div", {
    className: "sh-variants"
  }, /*#__PURE__*/React.createElement("span", {
    className: "sh-vlabel"
  }, "Ausf\xFChrung"), /*#__PURE__*/React.createElement(__ds_scope.Radio, {
    name: "v",
    value: "master",
    label: "Master \u2014 alle Protokolle",
    checked: variant === 'master',
    onChange: () => setVariant('master')
  }), /*#__PURE__*/React.createElement(__ds_scope.Radio, {
    name: "v",
    value: "slave",
    label: "Slave \u2014 gebunden an Master",
    checked: variant === 'slave',
    onChange: () => setVariant('slave')
  })), /*#__PURE__*/React.createElement("div", {
    className: "sh-buy"
  }, /*#__PURE__*/React.createElement("span", {
    className: "sh-price sh-price--lg"
  }, eur(p.price)), /*#__PURE__*/React.createElement(__ds_scope.Button, {
    size: "lg",
    iconLeft: I('shopping-cart'),
    onClick: () => onAdd(p)
  }, "In den Warenkorb")), /*#__PURE__*/React.createElement("ul", {
    className: "sh-feats"
  }, /*#__PURE__*/React.createElement("li", null, I('check'), " 24 Monate Garantie"), /*#__PURE__*/React.createElement("li", null, I('check'), " Versand in 24 h"), /*#__PURE__*/React.createElement("li", null, I('check'), " Support & Schulung inklusive")))));
}
function CartDrawer({
  open,
  items,
  onClose,
  onRemove
}) {
  const total = items.reduce((s, x) => s + x.price * x.qty, 0);
  return /*#__PURE__*/React.createElement("div", {
    className: `sh-drawer${open ? ' is-open' : ''}`,
    "aria-hidden": !open
  }, /*#__PURE__*/React.createElement("div", {
    className: "sh-drawer__scrim",
    onClick: onClose
  }), /*#__PURE__*/React.createElement("aside", {
    className: "sh-drawer__panel"
  }, /*#__PURE__*/React.createElement("div", {
    className: "sh-drawer__head"
  }, /*#__PURE__*/React.createElement("h3", null, "Warenkorb"), /*#__PURE__*/React.createElement(__ds_scope.IconButton, {
    label: "Schlie\xDFen",
    variant: "ghost",
    onClick: onClose
  }, I('x'))), /*#__PURE__*/React.createElement("div", {
    className: "sh-drawer__body"
  }, items.length === 0 && /*#__PURE__*/React.createElement("p", {
    className: "sh-empty"
  }, I('shopping-cart'), /*#__PURE__*/React.createElement("br", null), "Noch nichts im Warenkorb."), items.map(x => /*#__PURE__*/React.createElement("div", {
    className: "sh-line",
    key: x.id
  }, /*#__PURE__*/React.createElement("span", {
    className: "sh-line__ico grid-surface"
  }, I(x.icon)), /*#__PURE__*/React.createElement("div", {
    className: "sh-line__info"
  }, /*#__PURE__*/React.createElement("b", null, x.name), /*#__PURE__*/React.createElement("span", null, x.qty, " \xD7 ", eur(x.price))), /*#__PURE__*/React.createElement("button", {
    className: "sh-line__x",
    onClick: () => onRemove(x.id),
    "aria-label": "Entfernen"
  }, I('trash-2'))))), /*#__PURE__*/React.createElement("div", {
    className: "sh-drawer__foot"
  }, /*#__PURE__*/React.createElement("div", {
    className: "sh-drawer__total"
  }, /*#__PURE__*/React.createElement("span", null, "Zwischensumme"), /*#__PURE__*/React.createElement("b", null, eur(total))), /*#__PURE__*/React.createElement(__ds_scope.Button, {
    block: true,
    size: "lg",
    disabled: items.length === 0,
    iconRight: I('arrow-right')
  }, "Zur Kasse"))));
}
function ShopApp() {
  const [theme, setTheme] = React.useState('light');
  const [sel, setSel] = React.useState(null);
  const [cart, setCart] = React.useState([]);
  const [open, setOpen] = React.useState(false);
  __ds_scope.useLucide();
  const add = p => {
    setCart(c => {
      const e = c.find(x => x.id === p.id);
      return e ? c.map(x => x.id === p.id ? {
        ...x,
        qty: x.qty + 1
      } : x) : [...c, {
        ...p,
        qty: 1
      }];
    });
    setOpen(true);
  };
  const remove = id => setCart(c => c.filter(x => x.id !== id));
  const count = cart.reduce((s, x) => s + x.qty, 0);
  return /*#__PURE__*/React.createElement("div", {
    className: "sh-root",
    "data-theme": theme
  }, /*#__PURE__*/React.createElement(TopBar, {
    theme: theme,
    onToggle: () => setTheme(theme === 'dark' ? 'light' : 'dark'),
    cartCount: count,
    onOpenCart: () => setOpen(true)
  }), /*#__PURE__*/React.createElement("div", {
    className: "sh-wrap"
  }, /*#__PURE__*/React.createElement(__ds_scope.Breadcrumb, {
    items: [{
      label: 'Start',
      href: '#'
    }, {
      label: 'Shop',
      href: '#'
    }, sel ? sel.name : 'Tools']
  }), sel ? /*#__PURE__*/React.createElement(Detail, {
    p: sel,
    onBack: () => setSel(null),
    onAdd: add
  }) : /*#__PURE__*/React.createElement(Grid, {
    onOpen: setSel,
    onAdd: add
  })), /*#__PURE__*/React.createElement(CartDrawer, {
    open: open,
    items: cart,
    onClose: () => setOpen(false),
    onRemove: remove
  }));
}
Object.assign(__ds_scope, { ShopApp });
})(); } catch (e) { __ds_ns.__errors.push({ path: "ui_kits/shop/ShopApp.jsx", error: String((e && e.message) || e) }); }

__ds_ns.Badge = __ds_scope.Badge;

__ds_ns.Button = __ds_scope.Button;

__ds_ns.IconButton = __ds_scope.IconButton;

__ds_ns.Logo = __ds_scope.Logo;

__ds_ns.Tag = __ds_scope.Tag;

__ds_ns.Card = __ds_scope.Card;

__ds_ns.FileUpload = __ds_scope.FileUpload;

__ds_ns.ProgressBar = __ds_scope.ProgressBar;

__ds_ns.StatTile = __ds_scope.StatTile;

__ds_ns.Table = __ds_scope.Table;

__ds_ns.Tabs = __ds_scope.Tabs;

__ds_ns.Modal = __ds_scope.Modal;

__ds_ns.Toast = __ds_scope.Toast;

__ds_ns.Tooltip = __ds_scope.Tooltip;

__ds_ns.Checkbox = __ds_scope.Checkbox;

__ds_ns.Input = __ds_scope.Input;

__ds_ns.Radio = __ds_scope.Radio;

__ds_ns.Select = __ds_scope.Select;

__ds_ns.Switch = __ds_scope.Switch;

__ds_ns.Breadcrumb = __ds_scope.Breadcrumb;

__ds_ns.CatalogApp = __ds_scope.CatalogApp;

__ds_ns.MarketingSite = __ds_scope.MarketingSite;

__ds_ns.CreditsApp = __ds_scope.CreditsApp;

__ds_ns.PortalSidebar = __ds_scope.PortalSidebar;

__ds_ns.PortalTopBar = __ds_scope.PortalTopBar;

__ds_ns.PortalApp = __ds_scope.PortalApp;

__ds_ns.ShopApp = __ds_scope.ShopApp;

})();
