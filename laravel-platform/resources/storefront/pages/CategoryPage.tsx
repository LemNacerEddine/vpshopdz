import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { api } from '../lib/api';
import { getCategoryName } from '../lib/utils';
import { ProductCard } from '../components/products/ProductCard';

const CategoryPage: React.FC = () => {
  const { categoryId } = useParams<{ categoryId: string }>();
  const { apiBase } = useStore();
  const { colors, layout } = useTheme();
  const { t, language, isRTL } = useLanguage();

  const [category, setCategory] = useState<any>(null);
  const [products, setProducts] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const [catRes, prodRes] = await Promise.all([
          api.get(`${apiBase}/categories/${categoryId}`).catch(() => ({ data: null })),
          api.get(`${apiBase}/categories/${categoryId}/products`, { params: { limit: 40 } })
            .catch(() => api.get(`${apiBase}/products`, { params: { category_id: categoryId, limit: 40 } })),
        ]);
        setCategory(catRes.data?.data || catRes.data);
        setProducts(prodRes.data?.data || prodRes.data || []);
      } catch (error) {
        console.error('Error fetching category:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [categoryId, apiBase]);

  const gridCols = layout.gridColumns || 4;

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin h-8 w-8 border-4 rounded-full" style={{ borderColor: colors.muted, borderTopColor: colors.primary }} />
      </div>
    );
  }

  return (
    <div className="py-6" style={{ backgroundColor: colors.background }}>
      <div className="container mx-auto px-4">
        {/* Breadcrumb */}
        <nav className="flex items-center gap-2 text-sm mb-6" style={{ color: colors.mutedForeground }}>
          <Link to="/" className="hover:underline">{t('nav.home')}</Link>
          <span>/</span>
          <Link to="/products" className="hover:underline">{t('nav.products')}</Link>
          <span>/</span>
          <span style={{ color: colors.foreground }}>{category ? getCategoryName(category, language) : ''}</span>
        </nav>

        <h1 className="text-2xl font-bold mb-6" style={{ color: colors.foreground }}>
          {category ? getCategoryName(category, language) : t('nav.products')}
        </h1>

        {products.length === 0 ? (
          <div className="text-center py-20">
            <p className="text-lg" style={{ color: colors.mutedForeground }}>{t('products.noProducts')}</p>
          </div>
        ) : (
          <div className={`grid grid-cols-2 md:grid-cols-3 lg:grid-cols-${gridCols} gap-4`}>
            {products.map((product: any) => (
              <ProductCard key={product.id || product.product_id} product={product} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default CategoryPage;
