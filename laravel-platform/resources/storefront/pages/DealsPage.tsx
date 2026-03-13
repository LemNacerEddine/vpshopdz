import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useCart } from '../contexts/CartContext';
import { api } from '../lib/api';
import { Loader2, Flame, ShoppingCart, Tag } from 'lucide-react';
import { toast } from 'sonner';

const DealsPage: React.FC = () => {
  const { apiBase } = useStore();
  const { colors } = useTheme();
  const { addItem } = useCart();

  const [products, setProducts] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [addingId, setAddingId] = useState<string | null>(null);

  useEffect(() => {
    const fetchDeals = async () => {
      setLoading(true);
      try {
        const res = await api.get(`${apiBase}/products`, { params: { deals: 1, per_page: 48 } });
        const items = res.data?.data || res.data?.items || res.data || [];
        setProducts(Array.isArray(items) ? items : []);
      } catch {
        setProducts([]);
      }
      setLoading(false);
    };
    fetchDeals();
  }, [apiBase]);

  const handleAddToCart = async (product: any) => {
    setAddingId(product.id);
    try {
      await addItem({ id: product.id, name: product.name || product.name_ar, price: product.sale_price || product.price, image: product.images?.[0], quantity: 1 });
      toast.success('تمت الإضافة إلى السلة');
    } catch {
      toast.error('حدث خطأ');
    }
    setAddingId(null);
  };

  const discountPct = (product: any) => {
    if (!product.sale_price || !product.price) return 0;
    return Math.round(((product.price - product.sale_price) / product.price) * 100);
  };

  return (
    <div>
      {/* Hero Banner */}
      <div className="py-12 px-4 text-center" style={{ background: `linear-gradient(135deg, #ef4444, #f97316)` }}>
        <div className="flex items-center justify-center gap-3 mb-3">
          <Flame className="h-8 w-8 text-yellow-300 animate-pulse" />
          <h1 className="text-3xl font-black text-white">تخفيضات حصرية</h1>
          <Flame className="h-8 w-8 text-yellow-300 animate-pulse" />
        </div>
        <p className="text-white/80 text-lg">أفضل الأسعار على أجود المنتجات</p>
        <div className="mt-4 inline-flex items-center gap-2 bg-white/20 text-white px-4 py-2 rounded-full text-sm font-medium">
          <Tag className="h-4 w-4" />
          <span>{products.length} منتج بتخفيضات حصرية</span>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        {loading ? (
          <div className="flex items-center justify-center py-24">
            <Loader2 className="h-10 w-10 animate-spin" style={{ color: colors.primary }} />
          </div>
        ) : products.length === 0 ? (
          <div className="text-center py-24">
            <Flame className="h-16 w-16 mx-auto mb-4 opacity-20" style={{ color: colors.foreground }} />
            <p className="text-lg font-medium" style={{ color: colors.mutedForeground }}>لا توجد عروض متاحة حالياً</p>
            <Link to="/products" className="mt-4 inline-block px-6 py-2 rounded-xl text-white text-sm" style={{ backgroundColor: colors.primary }}>
              تصفح المنتجات
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            {products.map((product) => {
              const pct = discountPct(product);
              const price = product.sale_price || product.price;
              const name = product.name_ar || product.name || '';
              const image = product.images?.[0];
              return (
                <div
                  key={product.id}
                  className="rounded-2xl overflow-hidden border transition-transform hover:-translate-y-1 hover:shadow-lg flex flex-col"
                  style={{ backgroundColor: colors.card, borderColor: colors.border }}
                >
                  <Link to={`/products/${product.id}`} className="block relative">
                    <div className="aspect-square overflow-hidden bg-gray-100">
                      {image ? (
                        <img src={image} alt={name} className="w-full h-full object-cover" />
                      ) : (
                        <div className="w-full h-full flex items-center justify-center" style={{ backgroundColor: colors.muted }}>
                          <ShoppingCart className="h-10 w-10 opacity-20" style={{ color: colors.foreground }} />
                        </div>
                      )}
                    </div>
                    {pct > 0 && (
                      <div className="absolute top-2 start-2 flex items-center gap-1 bg-red-500 text-white text-xs font-black px-2 py-1 rounded-full">
                        <Flame className="h-3 w-3" />
                        -{pct}%
                      </div>
                    )}
                  </Link>
                  <div className="p-3 flex flex-col flex-1">
                    <Link to={`/products/${product.id}`}>
                      <p className="text-sm font-semibold line-clamp-2 mb-2" style={{ color: colors.foreground }}>{name}</p>
                    </Link>
                    <div className="mt-auto">
                      <div className="flex items-center gap-2 mb-2">
                        <span className="font-black text-base" style={{ color: '#ef4444' }}>{parseFloat(price).toLocaleString()} د.ج</span>
                        {product.sale_price && product.price && (
                          <span className="text-xs line-through" style={{ color: colors.mutedForeground }}>{parseFloat(product.price).toLocaleString()}</span>
                        )}
                      </div>
                      <button
                        onClick={() => handleAddToCart(product)}
                        disabled={addingId === product.id}
                        className="w-full py-2 rounded-xl text-white text-xs font-medium flex items-center justify-center gap-1 transition-opacity hover:opacity-90 disabled:opacity-50"
                        style={{ backgroundColor: colors.primary }}
                      >
                        {addingId === product.id ? <Loader2 className="h-3 w-3 animate-spin" /> : <><ShoppingCart className="h-3 w-3" /> أضف للسلة</>}
                      </button>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
};

export default DealsPage;
