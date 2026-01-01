import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Skeleton } from '@/components/ui/skeleton';
import { 
  Leaf, 
  Droplets, 
  Wrench, 
  Shield, 
  Droplet, 
  Home,
  ChevronRight,
  ChevronLeft
} from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const iconMap = {
  'Leaf': Leaf,
  'Droplets': Droplets,
  'Wrench': Wrench,
  'Shield': Shield,
  'Droplet': Droplet,
  'Home': Home
};

export const CategoriesPage = () => {
  const { t, language, isRTL } = useLanguage();
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  useEffect(() => {
    fetchCategories();
  }, []);

  const fetchCategories = async () => {
    try {
      const response = await axios.get(`${API}/categories`);
      setCategories(response.data);
    } catch (error) {
      console.error('Error fetching categories:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-background" data-testid="categories-page">
      {/* Header */}
      <div className="bg-primary/5 py-8">
        <div className="container mx-auto px-4">
          <h1 className="text-3xl font-bold text-foreground mb-2">
            {t('categories.title')}
          </h1>
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <Link to="/" className="hover:text-primary">{t('nav.home')}</Link>
            <ChevronIcon className="h-4 w-4" />
            <span className="text-foreground">{t('nav.categories')}</span>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        <p className="text-muted-foreground mb-8">{t('categories.subtitle')}</p>

        {loading ? (
          <div className="grid grid-cols-2 md:grid-cols-3 gap-6">
            {[...Array(6)].map((_, i) => (
              <Skeleton key={i} className="aspect-[4/3] rounded-3xl" />
            ))}
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {categories.map((category) => {
              const IconComponent = iconMap[category.icon] || Leaf;
              const name = category[`name_${language}`] || category.name_ar;
              const description = category[`description_${language}`] || category.description_ar;
              
              return (
                <Link 
                  key={category.category_id}
                  to={`/products?category=${category.category_id}`}
                  className="group"
                  data-testid={`category-card-${category.category_id}`}
                >
                  <div className="relative overflow-hidden rounded-3xl aspect-[4/3] bg-muted">
                    {category.image && (
                      <img
                        src={category.image}
                        alt={name}
                        className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                      />
                    )}
                    <div className="absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/50 to-transparent" />
                    <div className="absolute inset-0 p-6 flex flex-col justify-end text-white">
                      <div className="h-14 w-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center mb-4">
                        <IconComponent className="h-7 w-7" strokeWidth={1.5} />
                      </div>
                      <h3 className="text-xl font-bold mb-2">{name}</h3>
                      {description && (
                        <p className="text-sm text-white/80 line-clamp-2">{description}</p>
                      )}
                    </div>
                  </div>
                </Link>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
};

export default CategoriesPage;
