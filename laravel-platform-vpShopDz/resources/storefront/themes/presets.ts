export interface ThemePreset {
  name: string;
  nameAr: string;
  slug: string;
  description: string;
  descriptionAr: string;
  category: string;
  isPremium: boolean;
  preview?: string;
  colors: Record<string, string>;
  fonts: Record<string, string>;
  layout: Record<string, any>;
}

export const themePresets: Record<string, ThemePreset> = {
  // ────────────────────────────────────────────
  // 1. Dawn - الثيم الافتراضي العصري
  // ────────────────────────────────────────────
  dawn: {
    name: 'Dawn',
    nameAr: 'فجر',
    slug: 'dawn',
    description: 'Clean and modern theme for any store',
    descriptionAr: 'ثيم نظيف وعصري لأي متجر',
    category: 'general',
    isPremium: false,
    colors: {
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
    },
    fonts: {
      heading: "'Tajawal', sans-serif",
      body: "'Tajawal', sans-serif",
    },
    layout: {
      headerStyle: 'default',
      footerStyle: 'default',
      productCardStyle: 'default',
      heroStyle: 'slider',
      gridColumns: 4,
      containerWidth: '1280px',
      showBreadcrumb: true,
      showWhyUs: true,
    },
  },

  // ────────────────────────────────────────────
  // 2. Boutique - ثيم أنيق للأزياء
  // ────────────────────────────────────────────
  boutique: {
    name: 'Boutique',
    nameAr: 'بوتيك',
    slug: 'boutique',
    description: 'Elegant theme for fashion and clothing stores',
    descriptionAr: 'ثيم أنيق لمتاجر الأزياء والملابس',
    category: 'fashion',
    isPremium: false,
    colors: {
      primary: '#be185d',
      secondary: '#9333ea',
      accent: '#ec4899',
      background: '#fdf2f8',
      foreground: '#1e1b2e',
      card: '#ffffff',
      cardForeground: '#1e1b2e',
      muted: '#fce7f3',
      mutedForeground: '#6b7280',
      border: '#f9a8d4',
      headerBg: '#ffffff',
      headerText: '#1e1b2e',
      footerBg: '#1e1b2e',
      footerText: '#fdf2f8',
      buttonRadius: '9999px',
      cardRadius: '1rem',
      inputRadius: '9999px',
    },
    fonts: {
      heading: "'Cairo', sans-serif",
      body: "'Cairo', sans-serif",
    },
    layout: {
      headerStyle: 'centered',
      footerStyle: 'rich',
      productCardStyle: 'overlay',
      heroStyle: 'split',
      gridColumns: 3,
      containerWidth: '1200px',
      showBreadcrumb: true,
      showWhyUs: true,
    },
  },

  // ────────────────────────────────────────────
  // 3. TechStore - ثيم داكن للإلكترونيات
  // ────────────────────────────────────────────
  techstore: {
    name: 'TechStore',
    nameAr: 'تك ستور',
    slug: 'techstore',
    description: 'Dark modern theme for electronics and tech',
    descriptionAr: 'ثيم داكن وعصري للإلكترونيات والتقنية',
    category: 'electronics',
    isPremium: false,
    colors: {
      primary: '#3b82f6',
      secondary: '#6366f1',
      accent: '#22d3ee',
      background: '#0f172a',
      foreground: '#f1f5f9',
      card: '#1e293b',
      cardForeground: '#f1f5f9',
      muted: '#1e293b',
      mutedForeground: '#94a3b8',
      border: '#334155',
      headerBg: '#0f172a',
      headerText: '#f1f5f9',
      footerBg: '#020617',
      footerText: '#94a3b8',
      buttonRadius: '0.375rem',
      cardRadius: '0.5rem',
      inputRadius: '0.375rem',
    },
    fonts: {
      heading: "'Tajawal', sans-serif",
      body: "'Tajawal', sans-serif",
    },
    layout: {
      headerStyle: 'default',
      footerStyle: 'minimal',
      productCardStyle: 'default',
      heroStyle: 'banner',
      gridColumns: 4,
      containerWidth: '1280px',
      showBreadcrumb: true,
      showWhyUs: false,
    },
  },

  // ────────────────────────────────────────────
  // 4. FreshMarket - ثيم للمنتجات الغذائية
  // ────────────────────────────────────────────
  freshmarket: {
    name: 'FreshMarket',
    nameAr: 'سوق طازج',
    slug: 'freshmarket',
    description: 'Warm theme for food and agricultural products',
    descriptionAr: 'ثيم دافئ للمنتجات الغذائية والزراعية',
    category: 'food',
    isPremium: false,
    colors: {
      primary: '#16a34a',
      secondary: '#ca8a04',
      accent: '#ea580c',
      background: '#fefce8',
      foreground: '#1a2e05',
      card: '#ffffff',
      cardForeground: '#1a2e05',
      muted: '#ecfccb',
      mutedForeground: '#4d7c0f',
      border: '#bef264',
      headerBg: '#ffffff',
      headerText: '#1a2e05',
      footerBg: '#1a2e05',
      footerText: '#ecfccb',
      buttonRadius: '9999px',
      cardRadius: '1rem',
      inputRadius: '0.5rem',
    },
    fonts: {
      heading: "'Tajawal', sans-serif",
      body: "'Tajawal', sans-serif",
    },
    layout: {
      headerStyle: 'default',
      footerStyle: 'default',
      productCardStyle: 'default',
      heroStyle: 'slider',
      gridColumns: 4,
      containerWidth: '1280px',
      showBreadcrumb: true,
      showWhyUs: true,
    },
  },

  // ────────────────────────────────────────────
  // 5. Starter - ثيم بسيط ومينيمال
  // ────────────────────────────────────────────
  starter: {
    name: 'Starter',
    nameAr: 'بداية',
    slug: 'starter',
    description: 'Simple and minimal theme for landing pages',
    descriptionAr: 'ثيم بسيط ومينيمال لصفحات الهبوط',
    category: 'general',
    isPremium: false,
    colors: {
      primary: '#18181b',
      secondary: '#71717a',
      accent: '#f97316',
      background: '#ffffff',
      foreground: '#18181b',
      card: '#fafafa',
      cardForeground: '#18181b',
      muted: '#f4f4f5',
      mutedForeground: '#71717a',
      border: '#e4e4e7',
      headerBg: '#ffffff',
      headerText: '#18181b',
      footerBg: '#18181b',
      footerText: '#d4d4d8',
      buttonRadius: '0.375rem',
      cardRadius: '0.5rem',
      inputRadius: '0.375rem',
    },
    fonts: {
      heading: "'Tajawal', sans-serif",
      body: "'Tajawal', sans-serif",
    },
    layout: {
      headerStyle: 'minimal',
      footerStyle: 'minimal',
      productCardStyle: 'minimal',
      heroStyle: 'banner',
      gridColumns: 3,
      containerWidth: '1024px',
      showBreadcrumb: false,
      showWhyUs: false,
    },
  },

  // ────────────────────────────────────────────
  // 6. ProServices - ثيم احترافي للخدمات
  // ────────────────────────────────────────────
  proservices: {
    name: 'ProServices',
    nameAr: 'خدمات برو',
    slug: 'proservices',
    description: 'Professional theme for service-based businesses',
    descriptionAr: 'ثيم احترافي للأعمال القائمة على الخدمات',
    category: 'services',
    isPremium: false,
    colors: {
      primary: '#0d9488',
      secondary: '#0891b2',
      accent: '#f59e0b',
      background: '#f0fdfa',
      foreground: '#134e4a',
      card: '#ffffff',
      cardForeground: '#134e4a',
      muted: '#ccfbf1',
      mutedForeground: '#5eead4',
      border: '#99f6e4',
      headerBg: '#ffffff',
      headerText: '#134e4a',
      footerBg: '#134e4a',
      footerText: '#ccfbf1',
      buttonRadius: '0.5rem',
      cardRadius: '0.75rem',
      inputRadius: '0.5rem',
    },
    fonts: {
      heading: "'Cairo', sans-serif",
      body: "'Tajawal', sans-serif",
    },
    layout: {
      headerStyle: 'default',
      footerStyle: 'rich',
      productCardStyle: 'default',
      heroStyle: 'split',
      gridColumns: 3,
      containerWidth: '1200px',
      showBreadcrumb: true,
      showWhyUs: true,
    },
  },

  // ────────────────────────────────────────────
  // 7. Luxe - ثيم فاخر ذهبي (Premium)
  // ────────────────────────────────────────────
  luxe: {
    name: 'Luxe',
    nameAr: 'فاخر',
    slug: 'luxe',
    description: 'Luxury gold theme for premium brands',
    descriptionAr: 'ثيم فاخر ذهبي للعلامات التجارية الراقية',
    category: 'luxury',
    isPremium: true,
    colors: {
      primary: '#b45309',
      secondary: '#92400e',
      accent: '#d97706',
      background: '#fffbeb',
      foreground: '#1c1917',
      card: '#ffffff',
      cardForeground: '#1c1917',
      muted: '#fef3c7',
      mutedForeground: '#78716c',
      border: '#fcd34d',
      headerBg: '#1c1917',
      headerText: '#fcd34d',
      footerBg: '#1c1917',
      footerText: '#fef3c7',
      buttonRadius: '0rem',
      cardRadius: '0rem',
      inputRadius: '0rem',
    },
    fonts: {
      heading: "'Cairo', sans-serif",
      body: "'Cairo', sans-serif",
    },
    layout: {
      headerStyle: 'centered',
      footerStyle: 'rich',
      productCardStyle: 'overlay',
      heroStyle: 'slider',
      gridColumns: 3,
      containerWidth: '1200px',
      showBreadcrumb: true,
      showWhyUs: true,
    },
  },

  // ────────────────────────────────────────────
  // 8. Flavor - ثيم للمطاعم والكافيهات (Premium)
  // ────────────────────────────────────────────
  flavor: {
    name: 'Flavor',
    nameAr: 'نكهة',
    slug: 'flavor',
    description: 'Warm theme for restaurants and cafes',
    descriptionAr: 'ثيم دافئ للمطاعم والكافيهات',
    category: 'food',
    isPremium: true,
    colors: {
      primary: '#dc2626',
      secondary: '#ea580c',
      accent: '#fbbf24',
      background: '#fef2f2',
      foreground: '#1c1917',
      card: '#ffffff',
      cardForeground: '#1c1917',
      muted: '#fee2e2',
      mutedForeground: '#78716c',
      border: '#fecaca',
      headerBg: '#7f1d1d',
      headerText: '#fef2f2',
      footerBg: '#7f1d1d',
      footerText: '#fecaca',
      buttonRadius: '0.75rem',
      cardRadius: '1rem',
      inputRadius: '0.75rem',
    },
    fonts: {
      heading: "'Cairo', sans-serif",
      body: "'Tajawal', sans-serif",
    },
    layout: {
      headerStyle: 'default',
      footerStyle: 'default',
      productCardStyle: 'default',
      heroStyle: 'slider',
      gridColumns: 3,
      containerWidth: '1200px',
      showBreadcrumb: false,
      showWhyUs: true,
    },
  },

  // ────────────────────────────────────────────
  // 9. Neon - ثيم داكن نيون عصري (Premium)
  // ────────────────────────────────────────────
  neon: {
    name: 'Neon',
    nameAr: 'نيون',
    slug: 'neon',
    description: 'Dark neon theme for gaming and youth brands',
    descriptionAr: 'ثيم داكن نيون للألعاب والعلامات الشبابية',
    category: 'gaming',
    isPremium: true,
    colors: {
      primary: '#a855f7',
      secondary: '#ec4899',
      accent: '#06b6d4',
      background: '#09090b',
      foreground: '#fafafa',
      card: '#18181b',
      cardForeground: '#fafafa',
      muted: '#27272a',
      mutedForeground: '#a1a1aa',
      border: '#3f3f46',
      headerBg: '#09090b',
      headerText: '#fafafa',
      footerBg: '#09090b',
      footerText: '#a1a1aa',
      buttonRadius: '0.5rem',
      cardRadius: '0.75rem',
      inputRadius: '0.5rem',
    },
    fonts: {
      heading: "'Tajawal', sans-serif",
      body: "'Tajawal', sans-serif",
    },
    layout: {
      headerStyle: 'default',
      footerStyle: 'minimal',
      productCardStyle: 'overlay',
      heroStyle: 'banner',
      gridColumns: 4,
      containerWidth: '1280px',
      showBreadcrumb: true,
      showWhyUs: false,
    },
  },

  // ────────────────────────────────────────────
  // 10. Sahara - ثيم صحراوي جزائري (Premium)
  // ────────────────────────────────────────────
  sahara: {
    name: 'Sahara',
    nameAr: 'صحراء',
    slug: 'sahara',
    description: 'Algerian-inspired warm desert theme',
    descriptionAr: 'ثيم صحراوي دافئ مستوحى من الجزائر',
    category: 'general',
    isPremium: true,
    colors: {
      primary: '#c2410c',
      secondary: '#a16207',
      accent: '#15803d',
      background: '#fffbeb',
      foreground: '#422006',
      card: '#ffffff',
      cardForeground: '#422006',
      muted: '#fef3c7',
      mutedForeground: '#92400e',
      border: '#fde68a',
      headerBg: '#ffffff',
      headerText: '#422006',
      footerBg: '#422006',
      footerText: '#fef3c7',
      buttonRadius: '0.5rem',
      cardRadius: '0.75rem',
      inputRadius: '0.5rem',
    },
    fonts: {
      heading: "'Cairo', sans-serif",
      body: "'Tajawal', sans-serif",
    },
    layout: {
      headerStyle: 'default',
      footerStyle: 'rich',
      productCardStyle: 'default',
      heroStyle: 'split',
      gridColumns: 4,
      containerWidth: '1280px',
      showBreadcrumb: true,
      showWhyUs: true,
    },
  },
};

// Helper: Get theme by slug
export const getThemePreset = (slug: string): ThemePreset | null => {
  return themePresets[slug] || null;
};

// Helper: Get all themes
export const getAllThemes = (): ThemePreset[] => {
  return Object.values(themePresets);
};

// Helper: Get free themes only
export const getFreeThemes = (): ThemePreset[] => {
  return Object.values(themePresets).filter(t => !t.isPremium);
};

// Helper: Get premium themes only
export const getPremiumThemes = (): ThemePreset[] => {
  return Object.values(themePresets).filter(t => t.isPremium);
};

// Helper: Get themes by category
export const getThemesByCategory = (category: string): ThemePreset[] => {
  return Object.values(themePresets).filter(t => t.category === category);
};

// Theme categories
export const themeCategories = [
  { slug: 'general', name: 'General', nameAr: 'عام' },
  { slug: 'fashion', name: 'Fashion', nameAr: 'أزياء' },
  { slug: 'electronics', name: 'Electronics', nameAr: 'إلكترونيات' },
  { slug: 'food', name: 'Food & Drink', nameAr: 'طعام وشراب' },
  { slug: 'services', name: 'Services', nameAr: 'خدمات' },
  { slug: 'luxury', name: 'Luxury', nameAr: 'فاخر' },
  { slug: 'gaming', name: 'Gaming', nameAr: 'ألعاب' },
];
