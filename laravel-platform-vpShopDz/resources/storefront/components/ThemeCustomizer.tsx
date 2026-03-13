import React, { useState } from 'react';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { getAllThemes, themeCategories, ThemePreset } from '../themes/presets';
import { Palette, X, Check, Lock, Eye, ChevronDown } from 'lucide-react';

interface ThemeCustomizerProps {
  isOpen: boolean;
  onClose: () => void;
  onSelectTheme?: (theme: ThemePreset) => void;
  allowPremium?: boolean;
}

export const ThemeCustomizer: React.FC<ThemeCustomizerProps> = ({
  isOpen,
  onClose,
  onSelectTheme,
  allowPremium = false,
}) => {
  const { colors, currentTheme, setTheme } = useTheme();
  const { language, isRTL } = useLanguage();
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [previewTheme, setPreviewTheme] = useState<string | null>(null);

  const allThemes = getAllThemes();
  const filteredThemes = selectedCategory === 'all'
    ? allThemes
    : allThemes.filter(t => t.category === selectedCategory);

  const handleSelectTheme = (theme: ThemePreset) => {
    if (theme.isPremium && !allowPremium) return;
    setTheme(theme.slug);
    onSelectTheme?.(theme);
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex">
      {/* Backdrop */}
      <div className="absolute inset-0 bg-black/50" onClick={onClose} />

      {/* Panel */}
      <div
        className={`relative ${isRTL ? 'mr-auto' : 'ml-auto'} w-full max-w-md h-full overflow-y-auto shadow-2xl`}
        style={{ backgroundColor: colors.background }}
      >
        {/* Header */}
        <div className="sticky top-0 z-10 flex items-center justify-between p-4 border-b" style={{ backgroundColor: colors.background, borderColor: colors.border }}>
          <div className="flex items-center gap-2">
            <Palette className="h-5 w-5" style={{ color: colors.primary }} />
            <h2 className="font-bold text-lg" style={{ color: colors.foreground }}>
              {isRTL ? 'اختر الثيم' : 'Choose Theme'}
            </h2>
          </div>
          <button onClick={onClose} className="h-8 w-8 rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors">
            <X className="h-5 w-5" style={{ color: colors.foreground }} />
          </button>
        </div>

        {/* Category Filter */}
        <div className="p-4 border-b" style={{ borderColor: colors.border }}>
          <div className="flex gap-2 overflow-x-auto pb-2">
            <button
              onClick={() => setSelectedCategory('all')}
              className="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-colors"
              style={{
                backgroundColor: selectedCategory === 'all' ? colors.primary : colors.muted,
                color: selectedCategory === 'all' ? '#fff' : colors.foreground,
              }}
            >
              {isRTL ? 'الكل' : 'All'}
            </button>
            {themeCategories.map((cat) => (
              <button
                key={cat.slug}
                onClick={() => setSelectedCategory(cat.slug)}
                className="shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-colors"
                style={{
                  backgroundColor: selectedCategory === cat.slug ? colors.primary : colors.muted,
                  color: selectedCategory === cat.slug ? '#fff' : colors.foreground,
                }}
              >
                {isRTL ? cat.nameAr : cat.name}
              </button>
            ))}
          </div>
        </div>

        {/* Themes Grid */}
        <div className="p-4 space-y-4">
          {filteredThemes.map((theme) => {
            const isActive = currentTheme === theme.slug;
            const isLocked = theme.isPremium && !allowPremium;

            return (
              <button
                key={theme.slug}
                onClick={() => !isLocked && handleSelectTheme(theme)}
                className={`w-full text-start rounded-xl border-2 overflow-hidden transition-all ${isLocked ? 'opacity-70' : 'hover:shadow-lg'}`}
                style={{
                  borderColor: isActive ? colors.primary : colors.border,
                }}
              >
                {/* Theme Preview */}
                <div className="relative h-32 overflow-hidden" style={{ backgroundColor: theme.colors.background }}>
                  {/* Mini preview */}
                  <div className="absolute inset-0 p-3">
                    {/* Header preview */}
                    <div className="h-6 rounded-sm mb-2 flex items-center px-2 gap-1" style={{ backgroundColor: theme.colors.headerBg }}>
                      <div className="h-3 w-3 rounded-full" style={{ backgroundColor: theme.colors.primary }} />
                      <div className="h-2 w-12 rounded-full" style={{ backgroundColor: theme.colors.headerText, opacity: 0.5 }} />
                    </div>
                    {/* Content preview */}
                    <div className="grid grid-cols-3 gap-1.5">
                      {[1, 2, 3].map((i) => (
                        <div key={i} className="rounded-sm overflow-hidden" style={{ backgroundColor: theme.colors.card, border: `1px solid ${theme.colors.border}` }}>
                          <div className="h-8" style={{ backgroundColor: theme.colors.muted }} />
                          <div className="p-1">
                            <div className="h-1.5 w-full rounded-full mb-1" style={{ backgroundColor: theme.colors.cardForeground, opacity: 0.3 }} />
                            <div className="h-1.5 w-8 rounded-full" style={{ backgroundColor: theme.colors.primary }} />
                          </div>
                        </div>
                      ))}
                    </div>
                    {/* Footer preview */}
                    <div className="absolute bottom-3 left-3 right-3 h-4 rounded-sm" style={{ backgroundColor: theme.colors.footerBg }} />
                  </div>

                  {/* Badges */}
                  <div className="absolute top-2 right-2 flex gap-1">
                    {isActive && (
                      <span className="flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-bold text-white" style={{ backgroundColor: colors.primary }}>
                        <Check className="h-2.5 w-2.5" />
                        {isRTL ? 'مفعّل' : 'Active'}
                      </span>
                    )}
                    {theme.isPremium && (
                      <span className="flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-bold text-white bg-amber-500">
                        {isLocked ? <Lock className="h-2.5 w-2.5" /> : <Check className="h-2.5 w-2.5" />}
                        Premium
                      </span>
                    )}
                  </div>
                </div>

                {/* Theme Info */}
                <div className="p-3" style={{ backgroundColor: theme.colors.card }}>
                  <div className="flex items-center justify-between mb-1">
                    <h3 className="font-bold text-sm" style={{ color: theme.colors.cardForeground }}>
                      {isRTL ? theme.nameAr : theme.name}
                    </h3>
                    {/* Color dots */}
                    <div className="flex gap-1">
                      {[theme.colors.primary, theme.colors.secondary, theme.colors.accent].map((c, i) => (
                        <div key={i} className="h-4 w-4 rounded-full border border-white shadow-sm" style={{ backgroundColor: c }} />
                      ))}
                    </div>
                  </div>
                  <p className="text-xs" style={{ color: theme.colors.mutedForeground || '#888' }}>
                    {isRTL ? theme.descriptionAr : theme.description}
                  </p>
                </div>
              </button>
            );
          })}
        </div>
      </div>
    </div>
  );
};

export default ThemeCustomizer;
