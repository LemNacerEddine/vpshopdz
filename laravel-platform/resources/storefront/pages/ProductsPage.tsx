import React, { useState, useEffect, useCallback } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { api } from '../lib/api';
import { getCategoryName } from '../lib/utils';
import { ProductCard } from '../components/products/ProductCard';
import {
  SlidersHorizontal, X, ChevronDown, Search, Grid3X3, LayoutList
} from 'lucide-react';

const ProductsPage: React.FC = () => {
  const { apiBase } = useStore();
  const { colors, layout } = useTheme();
  const { t, language, isRTL } = useLanguage();
  const [searchParams, setSearchParams] = useSearchParams();

  const [products, setProducts] = useState<any[]>([]);
  const [categories, setCategories] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [totalPages, setTotalPages] = useState(1);
  const [showFilters, setShowFilters] = useState(false);
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');

  // Filter state
  const [selectedCategory, setSelectedCategory] = useState(searchParams.get('category') || '');
  const [sortBy, setSortBy] = useState(searchParams.get('sort') || 'newest');
  const [priceMin, setPriceMin] = useState(searchParams.get('price_min') || '');
  const [priceMax, setPriceMax] = useState(searchParams.get('price_max') || '');
  const [page, setPage] = useState(parseInt(searchParams.get('page') || '1'));
  const [searchQuery, setSearchQuery] = useState(searchParams.get('search') || searchParams.get('q') || '');

  const fetchProducts = useCallback(async () => {
    try {
      setLoading(true);
      const params: any = { page, limit: 20, sort: sortBy };
      if (selectedCategory) params.category_id = selectedCategory;
      if (priceMin) params.price_min = priceMin;
      if (priceMax) params.price_max = priceMax;
      if (searchQuery) params.search = searchQuery;
      if (searchParams.get('featured')) params.featured = 1;
      if (searchParams.get('deals')) params.deals = 1;

      const res = await api.get(`${apiBase}/products`, { params });
      setProducts(res.data?.data || res.data || []);
      setTotalPages(res.data?.last_page || res.data?.meta?.last_page || 1);
    } catch (error) {
      console.error('Error fetching products:', error);
    } finally {
      setLoading(false);
    }
  }, [apiBase, page, sortBy, selectedCategory, priceMin, priceMax, searchQuery, searchParams]);

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const res = await api.get(`${apiBase}/categories`);
        setCategories(res.data?.data || res.data || []);
      } catch { /* ignore */ }
    };
    fetchCategories();
  }, [apiBase]);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  const applyFilter = (key: string, value: string) => {
    const params = new URLSearchParams(searchParams);
    if (value) params.set(key, value);
    else params.delete(key);
    params.set('page', '1');
    setSearchParams(params);
    setPage(1);
  };

  const clearFilters = () => {
    setSelectedCategory('');
    setSortBy('newest');
    setPriceMin('');
    setPriceMax('');
    setSearchQuery('');
    setSearchParams({});
    setPage(1);
  };

  const hasActiveFilters = selectedCategory || priceMin || priceMax || searchQuery;

  const sortOptions = [
    { value: 'newest', label: t('products.sortNewest') },
    { value: 'price_asc', label: t('products.sortPriceLow') },
    { value: 'price_desc', label: t('products.sortPriceHigh') },
    { value: 'popular', label: t('products.sortPopular') },
  ];

  const gridCols = layout.gridColumns || 4;

  return (
    <div className="py-6" style={{ backgroundColor: colors.background }}>
      <div className="container mx-auto px-4">
        {/* Page Header */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
          <div>
            <h1 className="text-2xl font-bold" style={{ color: colors.foreground }}>
              {searchQuery ? `${t('products.showingResults')}: "${searchQuery}"` : t('products.all')}
            </h1>
            {products.length > 0 && (
              <p className="text-sm mt-1" style={{ color: colors.mutedForeground }}>
                {products.length} {t('cart.items')}
              </p>
            )}
          </div>

          <div className="flex items-center gap-3">
            {/* View Mode Toggle */}
            <div className="hidden md:flex items-center rounded-lg border" style={{ borderColor: colors.border }}>
              <button
                onClick={() => setViewMode('grid')}
                className="h-9 w-9 flex items-center justify-center rounded-l-lg transition-colors"
                style={{ backgroundColor: viewMode === 'grid' ? colors.primary : 'transparent', color: viewMode === 'grid' ? '#fff' : colors.foreground }}
              >
                <Grid3X3 className="h-4 w-4" />
              </button>
              <button
                onClick={() => setViewMode('list')}
                className="h-9 w-9 flex items-center justify-center rounded-r-lg transition-colors"
                style={{ backgroundColor: viewMode === 'list' ? colors.primary : 'transparent', color: viewMode === 'list' ? '#fff' : colors.foreground }}
              >
                <LayoutList className="h-4 w-4" />
              </button>
            </div>

            {/* Sort */}
            <select
              value={sortBy}
              onChange={(e) => { setSortBy(e.target.value); applyFilter('sort', e.target.value); }}
              className="h-9 px-3 rounded-lg border text-sm focus:outline-none"
              style={{ backgroundColor: colors.card, borderColor: colors.border, color: colors.foreground }}
            >
              {sortOptions.map((opt) => (
                <option key={opt.value} value={opt.value}>{opt.label}</option>
              ))}
            </select>

            {/* Filter Toggle (Mobile) */}
            <button
              onClick={() => setShowFilters(!showFilters)}
              className="md:hidden h-9 px-3 rounded-lg border flex items-center gap-2 text-sm"
              style={{ borderColor: colors.border, color: colors.foreground }}
            >
              <SlidersHorizontal className="h-4 w-4" />
              {t('products.filters')}
            </button>
          </div>
        </div>

        <div className="flex gap-6">
          {/* Sidebar Filters */}
          <aside
            className={`${showFilters ? 'fixed inset-0 z-50 bg-black/50 md:static md:bg-transparent' : 'hidden'} md:block w-64 shrink-0`}
            onClick={() => setShowFilters(false)}
          >
            <div
              className={`${showFilters ? `fixed ${isRTL ? 'right-0' : 'left-0'} top-0 h-full w-80 overflow-y-auto z-50` : ''} md:static p-4 rounded-xl border`}
              style={{ backgroundColor: colors.card, borderColor: colors.border }}
              onClick={(e) => e.stopPropagation()}
            >
              {/* Mobile close */}
              {showFilters && (
                <div className="flex items-center justify-between mb-4 md:hidden">
                  <h3 className="font-bold" style={{ color: colors.foreground }}>{t('products.filters')}</h3>
                  <button onClick={() => setShowFilters(false)}>
                    <X className="h-5 w-5" style={{ color: colors.foreground }} />
                  </button>
                </div>
              )}

              {/* Search */}
              <div className="mb-6">
                <div className="relative">
                  <Search className={`absolute ${isRTL ? 'right-3' : 'left-3'} top-1/2 -translate-y-1/2 h-4 w-4`} style={{ color: colors.mutedForeground }} />
                  <input
                    type="text"
                    placeholder={t('nav.searchPlaceholder')}
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    onKeyDown={(e) => { if (e.key === 'Enter') applyFilter('search', searchQuery); }}
                    className={`${isRTL ? 'pr-10' : 'pl-10'} h-9 w-full rounded-lg border text-sm focus:outline-none`}
                    style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                  />
                </div>
              </div>

              {/* Categories */}
              <div className="mb-6">
                <h4 className="font-semibold text-sm mb-3" style={{ color: colors.foreground }}>{t('products.category')}</h4>
                <div className="space-y-1">
                  <button
                    onClick={() => { setSelectedCategory(''); applyFilter('category', ''); }}
                    className={`w-full text-start px-3 py-2 rounded-lg text-sm transition-colors`}
                    style={{
                      backgroundColor: !selectedCategory ? `${colors.primary}15` : 'transparent',
                      color: !selectedCategory ? colors.primary : colors.foreground,
                    }}
                  >
                    {t('common.all')}
                  </button>
                  {categories.map((cat: any) => (
                    <button
                      key={cat.id}
                      onClick={() => { setSelectedCategory(String(cat.id)); applyFilter('category', String(cat.id)); }}
                      className="w-full text-start px-3 py-2 rounded-lg text-sm transition-colors"
                      style={{
                        backgroundColor: selectedCategory === String(cat.id) ? `${colors.primary}15` : 'transparent',
                        color: selectedCategory === String(cat.id) ? colors.primary : colors.foreground,
                      }}
                    >
                      {getCategoryName(cat, language)}
                    </button>
                  ))}
                </div>
              </div>

              {/* Price Range */}
              <div className="mb-6">
                <h4 className="font-semibold text-sm mb-3" style={{ color: colors.foreground }}>{t('products.priceRange')}</h4>
                <div className="flex gap-2">
                  <input
                    type="number"
                    placeholder="Min"
                    value={priceMin}
                    onChange={(e) => setPriceMin(e.target.value)}
                    onBlur={() => applyFilter('price_min', priceMin)}
                    className="h-9 w-full rounded-lg border text-sm px-3 focus:outline-none"
                    style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                  />
                  <input
                    type="number"
                    placeholder="Max"
                    value={priceMax}
                    onChange={(e) => setPriceMax(e.target.value)}
                    onBlur={() => applyFilter('price_max', priceMax)}
                    className="h-9 w-full rounded-lg border text-sm px-3 focus:outline-none"
                    style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                  />
                </div>
              </div>

              {/* Clear Filters */}
              {hasActiveFilters && (
                <button
                  onClick={clearFilters}
                  className="w-full py-2 rounded-lg text-sm font-medium border transition-colors"
                  style={{ borderColor: colors.border, color: colors.foreground }}
                >
                  {isRTL ? 'مسح الفلاتر' : 'Clear Filters'}
                </button>
              )}
            </div>
          </aside>

          {/* Products Grid */}
          <div className="flex-1">
            {loading ? (
              <div className="flex items-center justify-center py-20">
                <div className="animate-spin h-8 w-8 border-4 rounded-full" style={{ borderColor: colors.muted, borderTopColor: colors.primary }} />
              </div>
            ) : products.length === 0 ? (
              <div className="text-center py-20">
                <p className="text-lg font-medium mb-2" style={{ color: colors.foreground }}>{t('products.noProducts')}</p>
                <p className="text-sm" style={{ color: colors.mutedForeground }}>
                  {isRTL ? 'جرب تغيير الفلاتر أو البحث بكلمات مختلفة' : 'Try changing filters or search with different keywords'}
                </p>
              </div>
            ) : (
              <>
                <div className={viewMode === 'list' ? 'space-y-3' : `grid grid-cols-2 md:grid-cols-3 lg:grid-cols-${Math.max(gridCols - 1, 2)} gap-4`}>
                  {products.map((product: any) => (
                    <ProductCard
                      key={product.id || product.product_id}
                      product={product}
                      style={viewMode === 'list' ? 'horizontal' : undefined}
                    />
                  ))}
                </div>

                {/* Pagination */}
                {totalPages > 1 && (
                  <div className="flex items-center justify-center gap-2 mt-8">
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map((p) => (
                      <button
                        key={p}
                        onClick={() => { setPage(p); applyFilter('page', String(p)); window.scrollTo(0, 0); }}
                        className="h-9 min-w-[36px] rounded-lg text-sm font-medium transition-colors"
                        style={{
                          backgroundColor: page === p ? colors.primary : 'transparent',
                          color: page === p ? '#fff' : colors.foreground,
                          border: page === p ? 'none' : `1px solid ${colors.border}`,
                        }}
                      >
                        {p}
                      </button>
                    ))}
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductsPage;
