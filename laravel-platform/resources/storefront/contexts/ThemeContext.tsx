import React, { createContext, useContext, useEffect, useMemo, useState, useCallback } from 'react';
import { useStore } from './StoreContext';
import { themePresets, ThemePreset } from '../themes/presets';

interface ThemeColors {
  primary: string;
  secondary: string;
  accent: string;
  background: string;
  foreground: string;
  card: string;
  cardForeground: string;
  muted: string;
  mutedForeground: string;
  border: string;
  headerBg: string;
  headerText: string;
  footerBg: string;
  footerText: string;
  buttonRadius: string;
  cardRadius: string;
  inputRadius?: string;
}

interface ThemeFonts {
  heading: string;
  body: string;
}

interface ThemeLayout {
  headerStyle: 'default' | 'centered' | 'minimal' | 'mega';
  footerStyle: 'default' | 'minimal' | 'rich';
  productCardStyle: 'default' | 'minimal' | 'overlay' | 'horizontal';
  heroStyle: 'slider' | 'banner' | 'split' | 'video' | 'product-split';
  gridColumns: number;
  containerWidth?: string;
  showBreadcrumb?: boolean;
  showWhyUs?: boolean;
}

interface ThemeContextType {
  colors: ThemeColors;
  fonts: ThemeFonts;
  layout: ThemeLayout;
  themeSlug: string;
  currentTheme: string;
  preset: ThemePreset | null;
  isDark: boolean;
  setTheme: (slug: string) => void;
  setCustomColors: (colors: Partial<ThemeColors>) => void;
}

const defaultColors: ThemeColors = {
  primary: '#2563eb',
  secondary: '#64748b',
  accent: '#f59e0b',
  background: '#ffffff',
  foreground: '#1e293b',
  card: '#ffffff',
  cardForeground: '#1e293b',
  muted: '#f1f5f9',
  mutedForeground: '#64748b',
  border: '#e2e8f0',
  headerBg: '#ffffff',
  headerText: '#1e293b',
  footerBg: '#1e293b',
  footerText: '#f8fafc',
  buttonRadius: '0.5rem',
  cardRadius: '0.75rem',
  inputRadius: '0.5rem',
};

const defaultFonts: ThemeFonts = {
  heading: "'Tajawal', sans-serif",
  body: "'Tajawal', sans-serif",
};

const defaultLayout: ThemeLayout = {
  headerStyle: 'default',
  footerStyle: 'default',
  productCardStyle: 'default',
  heroStyle: 'product-split',
  gridColumns: 4,
  containerWidth: '1280px',
  showBreadcrumb: true,
  showWhyUs: true,
};

const ThemeContext = createContext<ThemeContextType>({
  colors: defaultColors,
  fonts: defaultFonts,
  layout: defaultLayout,
  themeSlug: 'dawn',
  currentTheme: 'dawn',
  preset: null,
  isDark: false,
  setTheme: () => { },
  setCustomColors: () => { },
});

export const useTheme = (): ThemeContextType => {
  return useContext(ThemeContext);
};

// Convert hex to HSL for Tailwind CSS variables
function hexToHSL(hex: string): string {
  hex = hex.replace('#', '');
  if (hex.length !== 6) return '0 0% 0%';
  const r = parseInt(hex.substring(0, 2), 16) / 255;
  const g = parseInt(hex.substring(2, 4), 16) / 255;
  const b = parseInt(hex.substring(4, 6), 16) / 255;

  const max = Math.max(r, g, b);
  const min = Math.min(r, g, b);
  let h = 0, s = 0;
  const l = (max + min) / 2;

  if (max !== min) {
    const d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    switch (max) {
      case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
      case g: h = ((b - r) / d + 2) / 6; break;
      case b: h = ((r - g) / d + 4) / 6; break;
    }
  }

  return `${Math.round(h * 360)} ${Math.round(s * 100)}% ${Math.round(l * 100)}%`;
}

// Determine if a color is dark
function isColorDark(hex: string): boolean {
  hex = hex.replace('#', '');
  if (hex.length !== 6) return false;
  const r = parseInt(hex.substring(0, 2), 16);
  const g = parseInt(hex.substring(2, 4), 16);
  const b = parseInt(hex.substring(4, 6), 16);
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
  return luminance < 0.5;
}

export const ThemeProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { store } = useStore();

  const storeThemeSlug = store.theme?.slug || 'dawn';
  const [activeTheme, setActiveTheme] = useState(storeThemeSlug);
  const [customColors, setCustomColorsState] = useState<Partial<ThemeColors>>({});

  // Sync with store theme changes
  useEffect(() => {
    setActiveTheme(storeThemeSlug);
  }, [storeThemeSlug]);

  const setTheme = useCallback((slug: string) => {
    setActiveTheme(slug);
    setCustomColorsState({});
  }, []);

  const setCustomColors = useCallback((newColors: Partial<ThemeColors>) => {
    setCustomColorsState(prev => ({ ...prev, ...newColors }));
  }, []);

  // Merge preset defaults with store customizations
  const preset = useMemo(() => {
    return themePresets[activeTheme] || themePresets['dawn'] || null;
  }, [activeTheme]);

  const colors = useMemo((): ThemeColors => {
    const storeColors = store.theme?.colors || {};
    const presetColors = preset?.colors || {};
    return {
      ...defaultColors,
      ...presetColors,
      ...storeColors,
      ...customColors,
    };
  }, [store.theme?.colors, preset, customColors]);

  const fonts = useMemo((): ThemeFonts => {
    const storeFonts = store.theme?.fonts || {};
    const presetFonts = preset?.fonts || {};
    return {
      ...defaultFonts,
      ...presetFonts,
      ...storeFonts,
    };
  }, [store.theme?.fonts, preset]);

  const layout = useMemo((): ThemeLayout => {
    const storeLayout = store.theme?.layout || {};
    const presetLayout = preset?.layout || {};
    return {
      ...defaultLayout,
      ...presetLayout,
      ...storeLayout,
    };
  }, [store.theme?.layout, preset]);

  const isDark = useMemo(() => isColorDark(colors.background), [colors.background]);

  // Apply CSS variables to document
  useEffect(() => {
    const root = document.documentElement;

    // Colors as CSS custom properties
    Object.entries(colors).forEach(([key, value]) => {
      const cssKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
      root.style.setProperty(`--color-${cssKey}`, value);

      // HSL version for Tailwind (only for actual colors, not radius)
      if (typeof value === 'string' && value.startsWith('#')) {
        const hslKey = cssKey.replace('color-', '');
        root.style.setProperty(`--${hslKey}`, hexToHSL(value));
      }
    });

    // Fonts
    root.style.setProperty('--font-heading', fonts.heading);
    root.style.setProperty('--font-body', fonts.body);
    document.body.style.fontFamily = fonts.body;

    // Background color
    document.body.style.backgroundColor = colors.background;
    document.body.style.color = colors.foreground;

    // Meta theme-color for mobile browsers
    let metaTheme = document.querySelector('meta[name="theme-color"]');
    if (!metaTheme) {
      metaTheme = document.createElement('meta');
      metaTheme.setAttribute('name', 'theme-color');
      document.head.appendChild(metaTheme);
    }
    metaTheme.setAttribute('content', colors.headerBg);
  }, [colors, fonts]);

  const value: ThemeContextType = {
    colors,
    fonts,
    layout,
    themeSlug: activeTheme,
    currentTheme: activeTheme,
    preset,
    isDark,
    setTheme,
    setCustomColors,
  };

  return (
    <ThemeContext.Provider value={value}>
      {children}
    </ThemeContext.Provider>
  );
};
