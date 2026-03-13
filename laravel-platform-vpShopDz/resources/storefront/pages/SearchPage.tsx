import React, { useState, useEffect } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { api } from '../lib/api';
import { ProductCard } from '../components/products/ProductCard';
import { Search, Loader2, SlidersHorizontal, X } from 'lucide-react';

const SearchPage: React.FC = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const { apiBase } = useStore();
  const { colors } = useTheme();
  const { t, isRTL } = useLanguage();

  const query = searchParams.get('q') || '';
  const [searchInput, setSearchInput] = useState(query);
  const [products, setProducts] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [sortBy, setSortBy] = useState('newest');
  const [priceRange, setPriceRange] = useState<[number, number]>([0, 100000]);
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    if (query) {
      fetchProducts(query);
    }
  }, [query, sortBy]);

  const fetchProducts = async (q: string) => {
    try {
      setLoading(true);
      const res = await api.get(`${apiBase}/products`, {
        params: {
          search: q,
          sort: sortBy,
          min_price: priceRange[0] > 0 ? priceRange[0] : undefined,
          max_price: priceRange[1] < 100000 ? priceRange[1] : undefined,
          limit: 40,
        },
      });
      setProducts(res.data?.data || res.data || []);
    } catch {
      setProducts([]);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchInput.trim()) {
      setSearchParams({ q: searchInput.trim() });
    }
  };

  return (
    <div className="py-6 min-h-screen" style={{ backgroundColor: colors.background }}>
      <div className="container mx-auto px-4">
        {/* Search Bar */}
        <form onSubmit={handleSearch} className="max-w-2xl mx-auto mb-8">
          <div className="relative">
            <Search className={`absolute ${isRTL ? 'right-4' : 'left-4'} top-1/2 -translate-y-1/2 h-5 w-5`} style={{ color: colors.mutedForeground }} />
            <input
              type="text"
              value={searchInput}
              onChange={(e) => setSearchInput(e.target.value)}
              placeholder={t('nav.search') + '...'}
              className={`w-full h-14 ${isRTL ? 'pr-12 pl-4' : 'pl-12 pr-4'} rounded-xl border-2 text-base focus:outline-none transition-colors`}
              style={{
                backgroundColor: colors.card,
                borderColor: colors.border,
                color: colors.foreground,
              }}
              onFocus={(e) => (e.target.style.borderColor = colors.primary)}
              onBlur={(e) => (e.target.style.borderColor = colors.border)}
              autoFocus
            />
            {searchInput && (
              <button
                type="button"
                onClick={() => { setSearchInput(''); }}
                className={`absolute ${isRTL ? 'left-14' : 'right-14'} top-1/2 -translate-y-1/2`}
              >
                <X className="h-4 w-4" style={{ color: colors.mutedForeground }} />
              </button>
            )}
            <button
              type="submit"
              className={`absolute ${isRTL ? 'left-2' : 'right-2'} top-1/2 -translate-y-1/2 h-10 px-4 rounded-lg text-white text-sm font-medium`}
              style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
            >
              {t('nav.search')}
            </button>
          </div>
        </form>

        {query && (
          <>
            {/* Results header */}
            <div className="flex items-center justify-between mb-6">
              <div>
                <h1 className="text-xl font-bold" style={{ color: colors.foreground }}>
                  {isRTL ? `نتائج البحث عن "${query}"` : `Search results for "${query}"`}
                </h1>
                <p className="text-sm mt-1" style={{ color: colors.mutedForeground }}>
                  {loading ? '...' : `${products.length} ${isRTL ? 'نتيجة' : 'results'}`}
                </p>
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={() => setShowFilters(!showFilters)}
                  className="flex items-center gap-1 px-3 py-2 rounded-lg border text-sm"
                  style={{ borderColor: colors.border, color: colors.foreground }}
                >
                  <SlidersHorizontal className="h-4 w-4" />
                  {isRTL ? 'فلترة' : 'Filter'}
                </button>

                <select
                  value={sortBy}
                  onChange={(e) => setSortBy(e.target.value)}
                  className="px-3 py-2 rounded-lg border text-sm"
                  style={{ backgroundColor: colors.card, borderColor: colors.border, color: colors.foreground }}
                >
                  <option value="newest">{isRTL ? 'الأحدث' : 'Newest'}</option>
                  <option value="price_asc">{isRTL ? 'السعر: الأقل' : 'Price: Low to High'}</option>
                  <option value="price_desc">{isRTL ? 'السعر: الأعلى' : 'Price: High to Low'}</option>
                  <option value="popular">{isRTL ? 'الأكثر مبيعاً' : 'Most Popular'}</option>
                </select>
              </div>
            </div>

            {/* Loading */}
            {loading ? (
              <div className="flex items-center justify-center py-20">
                <Loader2 className="h-8 w-8 animate-spin" style={{ color: colors.primary }} />
              </div>
            ) : products.length === 0 ? (
              <div className="text-center py-20">
                <Search className="h-16 w-16 mx-auto mb-4 opacity-20" style={{ color: colors.foreground }} />
                <p className="text-lg font-medium" style={{ color: colors.foreground }}>
                  {isRTL ? 'لا توجد نتائج' : 'No results found'}
                </p>
                <p className="text-sm mt-2" style={{ color: colors.mutedForeground }}>
                  {isRTL ? 'جرّب كلمات بحث مختلفة' : 'Try different search terms'}
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                {products.map((product: any) => (
                  <ProductCard key={product.id || product.product_id} product={product} />
                ))}
              </div>
            )}
          </>
        )}

        {!query && (
          <div className="text-center py-20">
            <Search className="h-16 w-16 mx-auto mb-4 opacity-20" style={{ color: colors.foreground }} />
            <p className="text-lg font-medium" style={{ color: colors.foreground }}>
              {isRTL ? 'ابحث عن المنتجات' : 'Search for products'}
            </p>
          </div>
        )}
      </div>
    </div>
  );
};

export default SearchPage;
