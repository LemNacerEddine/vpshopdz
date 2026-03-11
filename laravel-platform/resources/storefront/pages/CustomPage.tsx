import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { api } from '../lib/api';

const CustomPage: React.FC = () => {
  const { slug } = useParams<{ slug: string }>();
  const { apiBase } = useStore();
  const { colors } = useTheme();
  const { t, language } = useLanguage();

  const [page, setPage] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchPage = async () => {
      try {
        setLoading(true);
        const res = await api.get(`${apiBase}/pages/${slug}`);
        setPage(res.data?.data || res.data);
      } catch {
        setPage(null);
      } finally {
        setLoading(false);
      }
    };
    fetchPage();
  }, [slug, apiBase]);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin h-8 w-8 border-4 rounded-full" style={{ borderColor: colors.muted, borderTopColor: colors.primary }} />
      </div>
    );
  }

  if (!page) {
    return (
      <div className="py-20 text-center" style={{ backgroundColor: colors.background }}>
        <p className="text-lg" style={{ color: colors.mutedForeground }}>
          {language === 'ar' ? 'الصفحة غير موجودة' : 'Page not found'}
        </p>
      </div>
    );
  }

  const title = page[`title_${language}`] || page.title_ar || page.title;
  const content = page[`content_${language}`] || page.content_ar || page.content;

  return (
    <div className="py-8" style={{ backgroundColor: colors.background }}>
      <div className="container mx-auto px-4 max-w-3xl">
        <nav className="flex items-center gap-2 text-sm mb-6" style={{ color: colors.mutedForeground }}>
          <Link to="/" className="hover:underline">{t('nav.home')}</Link>
          <span>/</span>
          <span style={{ color: colors.foreground }}>{title}</span>
        </nav>

        <h1 className="text-3xl font-bold mb-8" style={{ color: colors.foreground }}>{title}</h1>

        <div
          className="prose max-w-none text-sm leading-relaxed"
          style={{ color: colors.foreground }}
          dangerouslySetInnerHTML={{ __html: content }}
        />
      </div>
    </div>
  );
};

export default CustomPage;
