import { createContext, useContext, useState, useEffect } from 'react';
import {
  getPortalDesignBootstrap,
  getPortalThemeTokens,
  normalizePortalPrimaryColor,
  writePortalPrimaryColorOverride,
} from '../lib/portalDesignBootstrap';

const ThemeContext = createContext();
const THEME_STORAGE_KEY = 'portal-theme-preference';

export const ThemeProvider = ({ children }) => {
  const portalDesign = getPortalDesignBootstrap({ respectStoredOverride: false });
  const [theme, setTheme] = useState(() => {
    const saved = localStorage.getItem(THEME_STORAGE_KEY);
    if (saved === 'light' || saved === 'dark') {
      return saved;
    }

    return getPortalThemeTokens().mode;
  });
  const [primaryColor, setPrimaryColorState] = useState(() => portalDesign.primaryColor);

  useEffect(() => {
    localStorage.setItem(THEME_STORAGE_KEY, theme);

    const root = document.documentElement;
    root.classList.remove('light', 'dark');
    root.classList.add(theme);

    const portalTheme = getPortalThemeTokens({ primaryColor, mode: theme });
    root.style.colorScheme = theme;
    root.style.setProperty('--primary', portalTheme.primary);
    root.style.setProperty('--accent', portalTheme.accent);
    root.style.setProperty('--accent-foreground', portalTheme.accentForeground);
    root.style.setProperty('--ring', portalTheme.ring);
    root.style.setProperty('--gradient-start', portalTheme.gradientStart);
    root.style.setProperty('--gradient-end', portalTheme.gradientEnd);
    root.style.setProperty('--background', portalTheme.background);
    root.style.setProperty('--foreground', portalTheme.foreground);
    root.style.setProperty('--card', portalTheme.card);
    root.style.setProperty('--card-foreground', portalTheme.cardForeground);
    root.style.setProperty('--popover', portalTheme.popover);
    root.style.setProperty('--popover-foreground', portalTheme.popoverForeground);
    root.style.setProperty('--primary-foreground', portalTheme.primaryForeground);
    root.style.setProperty('--secondary', portalTheme.secondary);
    root.style.setProperty('--secondary-foreground', portalTheme.secondaryForeground);
    root.style.setProperty('--muted', portalTheme.muted);
    root.style.setProperty('--muted-foreground', portalTheme.mutedForeground);
    root.style.setProperty('--border', portalTheme.border);
    root.style.setProperty('--input', portalTheme.input);
    root.style.setProperty('--sidebar-bg', portalTheme.sidebarBg);
    root.style.setProperty('--sidebar-border', portalTheme.sidebarBorder);
    root.style.setProperty('--success', portalTheme.success);
    root.style.setProperty('--success-soft', portalTheme.successSoft);
    root.style.setProperty('--success-border', portalTheme.successBorder);
    root.style.setProperty('--success-foreground', portalTheme.successForeground);
    root.style.setProperty('--info', portalTheme.info);
    root.style.setProperty('--info-soft', portalTheme.infoSoft);
    root.style.setProperty('--info-border', portalTheme.infoBorder);
    root.style.setProperty('--info-foreground', portalTheme.infoForeground);
    root.style.setProperty('--warning', portalTheme.warning);
    root.style.setProperty('--warning-soft', portalTheme.warningSoft);
    root.style.setProperty('--warning-border', portalTheme.warningBorder);
    root.style.setProperty('--warning-foreground', portalTheme.warningForeground);
    root.style.setProperty('--danger', portalTheme.danger);
    root.style.setProperty('--danger-soft', portalTheme.dangerSoft);
    root.style.setProperty('--danger-border', portalTheme.dangerBorder);
    root.style.setProperty('--danger-foreground', portalTheme.dangerForeground);
    root.style.setProperty('--font-heading-family', portalTheme.headingFont);
    root.style.setProperty('--font-body-family', portalTheme.bodyFont);
  }, [primaryColor, theme]);

  const setPrimaryColor = (value) => {
    const normalized = normalizePortalPrimaryColor(value, portalDesign.primaryColor);
    writePortalPrimaryColorOverride(normalized);
    setPrimaryColorState(normalized);
  };

  const resetPrimaryColor = () => {
    writePortalPrimaryColorOverride(null);
    setPrimaryColorState(portalDesign.primaryColor);
  };

  const toggleTheme = () => {
    setTheme(prev => prev === 'dark' ? 'light' : 'dark');
  };

  return (
    <ThemeContext.Provider value={{ theme, setTheme, toggleTheme, primaryColor, setPrimaryColor, resetPrimaryColor, defaultPrimaryColor: portalDesign.primaryColor }}>
      {children}
    </ThemeContext.Provider>
  );
};

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};
