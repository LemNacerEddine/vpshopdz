import React, { useState, useEffect } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { ProductCard } from '@/components/products/ProductCard';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Search, SlidersHorizontal, X, Leaf } from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const ProductsPage = () => {
  const { t, language, isRTL, formatPrice } = useLanguage();
  const [searchParams, setSearchParams] = useSearchParams();
  
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState(searchParams.get('search') || '');
  const [selectedCategory, setSelectedCategory] = useState(searchParams.get('category') || '');
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    fetchCategories();
  }, []);

  useEffect(() => {
    fetchProducts();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchParams]);

  const fetchCategories = async () => {
    try {
      const response = await axios.get(`${API}/categories`);
      setCategories(response.data);
    } catch (error) {
      console.error('Error fetching categories:', error);
    }
  };

  const fetchProducts = async () => {
    try {
      setLoading(true);
      const params = new URLSearchParams();
      
      const search = searchParams.get('search');
      const category = searchParams.get('category');
      
      if (search) params.append('search', search);
      if (category) params.append('category_id', category);
      
      const response = await axios.get(`${API}/products?${params.toString()}`);
      setProducts(response.data);
    } catch (error) {
      console.error('Error fetching products:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    const params = new URLSearchParams(searchParams);
    if (searchQuery) {
      params.set('search', searchQuery);
    } else {
      params.delete('search');
    }
    setSearchParams(params);
  };

  const handleCategoryChange = (categoryId) => {
    setSelectedCategory(categoryId);
    const params = new URLSearchParams(searchParams);
    if (categoryId && categoryId !== 'all') {
      params.set('category', categoryId);
    } else {
      params.delete('category');
    }
    setSearchParams(params);
  };

  const clearFilters = () => {
    setSearchQuery('');
    setSelectedCategory('');
    setSearchParams({});
  };

  const hasFilters = searchParams.get('search') || searchParams.get('category');

  const activeCategory = categories.find(c => c.category_id === selectedCategory);

  return (
    <div className="min-h-screen bg-background" data-testid="products-page">
      {/* Header */}
      <div className="bg-primary/5 py-8">
        <div className="container mx-auto px-4">
          <h1 className="text-3xl font-bold text-foreground mb-2">
            {activeCategory 
              ? activeCategory[`name_${language}`] || activeCategory.name_ar
              : t('products.title')
            }
          </h1>
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <Link to="/" className="hover:text-primary">{t('nav.home')}</Link>
            <span>/</span>
            <span>{t('nav.products')}</span>
            {activeCategory && (
              <>
                <span>/</span>
                <span>{activeCategory[`name_${language}`] || activeCategory.name_ar}</span>
              </>
            )}
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        {/* Filters Bar */}
        <div className="flex flex-col sm:flex-row gap-4 mb-8">
          {/* Search */}
          <form onSubmit={handleSearch} className="flex-1">
            <div className="relative">
              <Search className={`absolute ${isRTL ? 'right-3' : 'left-3'} top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground`} />
              <Input
                type="search"
                placeholder={t('nav.search')}
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className={`${isRTL ? 'pr-10' : 'pl-10'} rounded-full`}
                data-testid="products-search"
              />
            </div>
          </form>

          {/* Category Filter */}
          <Select value={selectedCategory || 'all'} onValueChange={handleCategoryChange}>
            <SelectTrigger className="w-full sm:w-48 rounded-full" data-testid="category-filter">
              <SelectValue placeholder={t('products.all')} />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{t('products.all')}</SelectItem>
              {categories.map((category) => (
                <SelectItem key={category.category_id} value={category.category_id}>
                  {category[`name_${language}`] || category.name_ar}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          {/* Clear Filters */}
          {hasFilters && (
            <Button variant="outline" onClick={clearFilters} className="rounded-full" data-testid="clear-filters">
              <X className="h-4 w-4 me-1" />
              {language === 'ar' ? 'مسح' : 'Clear'}
            </Button>
          )}
        </div>

        {/* Active Filters */}
        {hasFilters && (
          <div className="flex flex-wrap gap-2 mb-6">
            {searchParams.get('search') && (
              <Badge variant="secondary" className="px-3 py-1">
                {t('common.search')}: {searchParams.get('search')}
                <button 
                  onClick={() => {
                    setSearchQuery('');
                    const params = new URLSearchParams(searchParams);
                    params.delete('search');
                    setSearchParams(params);
                  }}
                  className="ms-2"
                >
                  <X className="h-3 w-3" />
                </button>
              </Badge>
            )}
            {activeCategory && (
              <Badge variant="secondary" className="px-3 py-1">
                {activeCategory[`name_${language}`] || activeCategory.name_ar}
                <button 
                  onClick={() => handleCategoryChange('all')}
                  className="ms-2"
                >
                  <X className="h-3 w-3" />
                </button>
              </Badge>
            )}
          </div>
        )}

        {/* Products Grid */}
        {loading ? (
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            {[...Array(18)].map((_, i) => (
              <Skeleton key={i} className="aspect-[4/3] rounded-lg" />
            ))}
          </div>
        ) : products.length === 0 ? (
          <div className="text-center py-16">
            <Leaf className="h-16 w-16 text-muted-foreground/50 mx-auto mb-4" />
            <h3 className="text-xl font-semibold text-muted-foreground mb-2">
              {t('products.noProducts')}
            </h3>
            <p className="text-muted-foreground mb-6">
              {language === 'ar' ? 'جرب البحث بكلمات أخرى' : language === 'fr' ? 'Essayez de rechercher avec d\'autres mots' : 'Try searching with different words'}
            </p>
            <Button onClick={clearFilters} variant="outline" className="rounded-full">
              {language === 'ar' ? 'عرض جميع المنتجات' : language === 'fr' ? 'Voir tous les produits' : 'View all products'}
            </Button>
          </div>
        ) : (
          <>
            <p className="text-sm text-muted-foreground mb-3">
              {products.length} {language === 'ar' ? 'منتج' : language === 'fr' ? 'produits' : 'products'}
            </p>
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
              {products.map((product) => (
                <ProductCard key={product.product_id} product={product} />
              ))}
            </div>
          </>
        )}
      </div>
    </div>
  );
};

export default ProductsPage;
