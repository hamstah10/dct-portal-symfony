const ensureHexColor = (value, fallback) => {
  const normalized = typeof value === 'string' ? value.trim() : '';
  return /^#[0-9a-fA-F]{6}$/.test(normalized) ? normalized : fallback;
};

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const hexToRgb = (hex) => {
  const normalized = ensureHexColor(hex, '#000000').slice(1);
  return {
    r: parseInt(normalized.slice(0, 2), 16),
    g: parseInt(normalized.slice(2, 4), 16),
    b: parseInt(normalized.slice(4, 6), 16),
  };
};

const getRelativeLuminance = ({ r, g, b }) => {
  const transformChannel = (channel) => {
    const normalized = channel / 255;
    return normalized <= 0.03928
      ? normalized / 12.92
      : ((normalized + 0.055) / 1.055) ** 2.4;
  };

  const red = transformChannel(r);
  const green = transformChannel(g);
  const blue = transformChannel(b);

  return 0.2126 * red + 0.7152 * green + 0.0722 * blue;
};

const rgbToHsl = ({ r, g, b }) => {
  const red = r / 255;
  const green = g / 255;
  const blue = b / 255;
  const max = Math.max(red, green, blue);
  const min = Math.min(red, green, blue);
  let h = 0;
  let s = 0;
  const l = (max + min) / 2;

  if (max !== min) {
    const d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

    switch (max) {
      case red:
        h = (green - blue) / d + (green < blue ? 6 : 0);
        break;
      case green:
        h = (blue - red) / d + 2;
        break;
      default:
        h = (red - green) / d + 4;
        break;
    }

    h /= 6;
  }

  return {
    h: Math.round(h * 360),
    s: Math.round(s * 100),
    l: Math.round(l * 100),
  };
};

const toHslToken = ({ h, s, l }) => `${h} ${s}% ${l}%`;

const shiftLightness = (hex, delta) => {
  const hsl = rgbToHsl(hexToRgb(hex));
  return toHslToken({ ...hsl, l: clamp(hsl.l + delta, 0, 100) });
};

const isLightColor = (hex, threshold = 0.6) => getRelativeLuminance(hexToRgb(hex)) >= threshold;

const getSurfaceStep = (hex, darkDelta, lightDelta, threshold = 0.6) => (
  isLightColor(hex, threshold) ? shiftLightness(hex, lightDelta) : shiftLightness(hex, darkDelta)
);

const getReadableForeground = (hex) => (
  isLightColor(hex) ? '222 47% 11%' : '0 0% 98%'
);

const getMutedForeground = (hex) => (
  isLightColor(hex) ? '215 16% 35%' : '215 20% 72%'
);

const getSemanticPalette = (mode) => {
  if (mode === 'light') {
    return {
      success: '145 63% 34%',
      successSoft: '145 52% 92%',
      successBorder: '145 38% 74%',
      successForeground: '145 63% 28%',
      info: '214 84% 44%',
      infoSoft: '214 100% 94%',
      infoBorder: '214 73% 78%',
      infoForeground: '214 84% 36%',
      warning: '33 88% 42%',
      warningSoft: '36 100% 93%',
      warningBorder: '36 82% 76%',
      warningForeground: '28 92% 32%',
      danger: '0 72% 46%',
      dangerSoft: '0 100% 95%',
      dangerBorder: '0 75% 82%',
      dangerForeground: '0 72% 38%',
    };
  }

  return {
    success: '145 63% 48%',
    successSoft: '145 55% 14%',
    successBorder: '145 44% 30%',
    successForeground: '145 72% 64%',
    info: '214 90% 60%',
    infoSoft: '214 70% 14%',
    infoBorder: '214 60% 30%',
    infoForeground: '214 92% 70%',
    warning: '40 96% 58%',
    warningSoft: '40 70% 14%',
    warningBorder: '40 58% 28%',
    warningForeground: '40 96% 68%',
    danger: '0 78% 60%',
    dangerSoft: '0 65% 14%',
    dangerBorder: '0 55% 28%',
    dangerForeground: '0 86% 72%',
  };
};

export const hexToHslToken = (hex) => toHslToken(rgbToHsl(hexToRgb(hex)));

export const getPortalDesignStorageScope = () => {
  const bootstrap = window?.portalReactBootstrap?.portalDesign ?? {};
  const primaryColor = ensureHexColor(bootstrap.primaryColor, '#b91c1c');
  const secondaryColor = ensureHexColor(bootstrap.secondaryColor, '#111111');
  const menuBackgroundColor = ensureHexColor(bootstrap.menuBackgroundColor, '#111111');
  const contentBackgroundColor = ensureHexColor(bootstrap.contentBackgroundColor, '#050505');

  return [
    primaryColor,
    secondaryColor,
    menuBackgroundColor,
    contentBackgroundColor,
  ].join(':');
};

export const getPortalPrimaryColorStorageKey = () => (
  `portal-primary-color-override:${getPortalDesignStorageScope()}`
);

export const normalizePortalPrimaryColor = (value, fallback = '#b91c1c') => (
  ensureHexColor(value, fallback)
);

export const readPortalPrimaryColorOverride = () => {
  if (typeof window === 'undefined') {
    return null;
  }

  try {
    const value = window.localStorage.getItem(getPortalPrimaryColorStorageKey());
    const normalized = ensureHexColor(value, '');
    return normalized === '' ? null : normalized;
  } catch (_error) {
    return null;
  }
};

export const writePortalPrimaryColorOverride = (value) => {
  if (typeof window === 'undefined') {
    return;
  }

  const key = getPortalPrimaryColorStorageKey();
  const normalized = ensureHexColor(value, '');

  try {
    if (normalized === '') {
      window.localStorage.removeItem(key);
      return;
    }

    window.localStorage.setItem(key, normalized);
  } catch (_error) {
    // ignore storage errors
  }
};

export const getPortalDesignBootstrap = (overrides = {}) => {
  const bootstrap = window?.portalReactBootstrap?.portalDesign ?? {};
  const shouldRespectStoredOverride = overrides.respectStoredOverride !== false;
  const primaryOverride = ensureHexColor(
    overrides.primaryColor ?? (shouldRespectStoredOverride ? readPortalPrimaryColorOverride() : null),
    ''
  );

  return {
    primaryColor: primaryOverride || ensureHexColor(bootstrap.primaryColor, '#b91c1c'),
    secondaryColor: ensureHexColor(bootstrap.secondaryColor, '#111111'),
    menuBackgroundColor: ensureHexColor(bootstrap.menuBackgroundColor, '#111111'),
    contentBackgroundColor: ensureHexColor(bootstrap.contentBackgroundColor, '#050505'),
    headingFont: typeof bootstrap.headingFont === 'string' && bootstrap.headingFont.trim() !== '' ? bootstrap.headingFont.trim() : "'Averta', 'IBM Plex Sans', sans-serif",
    bodyFont: typeof bootstrap.bodyFont === 'string' && bootstrap.bodyFont.trim() !== '' ? bootstrap.bodyFont.trim() : "'Averta', 'IBM Plex Sans', sans-serif",
    portalName: typeof bootstrap.portalName === 'string' && bootstrap.portalName.trim() !== '' ? bootstrap.portalName.trim() : 'Chiptuningfile.de',
    portalKey: typeof bootstrap.portalKey === 'string' ? bootstrap.portalKey.trim() : '',
    logoUrl: typeof bootstrap.logoUrl === 'string' && bootstrap.logoUrl.trim() !== '' ? bootstrap.logoUrl.trim() : null,
  };
};

export const getPortalThemeTokens = (overrides = {}) => {
  const design = getPortalDesignBootstrap(overrides);
  const mode = overrides.mode === 'light' || overrides.mode === 'dark'
    ? overrides.mode
    : (isLightColor(design.contentBackgroundColor) ? 'light' : 'dark');
  const primaryForeground = getReadableForeground(design.primaryColor);
  const semantic = getSemanticPalette(mode);
  const isLightMode = mode === 'light';
  const background = isLightMode ? '210 20% 98%' : hexToHslToken(design.contentBackgroundColor);
  const foreground = isLightMode ? '222 47% 11%' : getReadableForeground(design.contentBackgroundColor);
  const card = isLightMode ? '0 0% 100%' : hexToHslToken(design.secondaryColor);
  const cardForeground = isLightMode ? '222 47% 11%' : getReadableForeground(design.secondaryColor);
  const popover = card;
  const popoverForeground = cardForeground;
  const secondary = isLightMode ? '210 16% 94%' : getSurfaceStep(design.contentBackgroundColor, 8, -4);
  const secondaryForeground = foreground;
  const muted = isLightMode ? '210 16% 94%' : getSurfaceStep(design.contentBackgroundColor, 10, -6);
  const mutedForeground = isLightMode ? '215 16% 35%' : getMutedForeground(design.contentBackgroundColor);
  const border = isLightMode ? '214 22% 88%' : getSurfaceStep(design.contentBackgroundColor, 14, -12);
  const input = border;
  const sidebarBg = isLightMode ? '0 0% 100%' : hexToHslToken(design.menuBackgroundColor);
  const sidebarBorder = isLightMode ? '214 22% 88%' : shiftLightness(design.menuBackgroundColor, 10);

  return {
    mode,
    primary: hexToHslToken(design.primaryColor),
    accent: hexToHslToken(design.primaryColor),
    accentForeground: primaryForeground,
    ring: hexToHslToken(design.primaryColor),
    gradientStart: getSurfaceStep(design.primaryColor, 4, 8, 0.52),
    gradientEnd: getSurfaceStep(design.primaryColor, -8, -6, 0.52),
    background,
    foreground,
    card,
    cardForeground,
    popover,
    popoverForeground,
    primaryForeground,
    secondary,
    secondaryForeground,
    muted,
    mutedForeground,
    border,
    input,
    sidebarBg,
    sidebarBorder,
    success: semantic.success,
    successSoft: semantic.successSoft,
    successBorder: semantic.successBorder,
    successForeground: semantic.successForeground,
    info: semantic.info,
    infoSoft: semantic.infoSoft,
    infoBorder: semantic.infoBorder,
    infoForeground: semantic.infoForeground,
    warning: semantic.warning,
    warningSoft: semantic.warningSoft,
    warningBorder: semantic.warningBorder,
    warningForeground: semantic.warningForeground,
    danger: semantic.danger,
    dangerSoft: semantic.dangerSoft,
    dangerBorder: semantic.dangerBorder,
    dangerForeground: semantic.dangerForeground,
    headingFont: design.headingFont,
    bodyFont: design.bodyFont,
  };
};
