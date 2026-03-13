import React, { useState, useEffect, useCallback } from "react";
import { Link } from "react-router-dom";
import { useStore } from "../contexts/StoreContext";
import { useTheme } from "../contexts/ThemeContext";
import { useLanguage } from "../contexts/LanguageContext";
import { useCart } from "../contexts/CartContext";
import { api } from "../lib/api";
import { getProductName } from "../lib/utils";
import { ProductCard } from "../components/products/ProductCard";
import { toast } from "sonner";
import { Heart, ShoppingBag, Trash2, ShoppingCart, Loader2 } from "lucide-react";

const WishlistPage: React.FC = () => {
  const { apiBase } = useStore();
  const { colors } = useTheme();
  const { t, isRTL, language } = useLanguage();
  const { addToCart } = useCart();
  const [wishlist, setWishlist] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [removing, setRemoving] = useState<string | null>(null);

  const loadWishlist = useCallback(async () => {
    try {
      setLoading(true);
      const res = await api.get(`${apiBase}/wishlist`);
      setWishlist(res.data?.data || res.data || []);
    } catch {
      try {
        const saved = localStorage.getItem("wishlist");
        if (saved) setWishlist(JSON.parse(saved));
      } catch {}
    } finally {
      setLoading(false);
    }
  }, [apiBase]);

  useEffect(() => { loadWishlist(); }, [loadWishlist]);

  const removeFromWishlist = async (productId: string) => {
    try {
      setRemoving(productId);
      await api.delete(`${apiBase}/wishlist/items/${productId}`);
      setWishlist(prev => prev.filter(p => (p.id || p.product_id) !== productId));
      toast.success(isRTL ? "تم الحذف من قائمة الأمنيات" : "Removed from wishlist");
    } catch {
      setWishlist(prev => prev.filter(p => (p.id || p.product_id) !== productId));
    } finally {
      setRemoving(null);
    }
  };

  const handleAddToCart = async (product: any) => {
    const id = product.id || product.product_id;
    const success = await addToCart(id, 1);
    if (success) {
      toast.success(t("products.addToCart"), { description: getProductName(product, language) });
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center" style={{ backgroundColor: colors.background }}>
        <Loader2 className="h-8 w-8 animate-spin" style={{ color: colors.primary }} />
      </div>
    );
  }

  return (
    <div className="py-6 min-h-screen" style={{ backgroundColor: colors.background }}>
      <div className="container mx-auto px-4">
        <h1 className="text-2xl font-bold mb-6" style={{ color: colors.foreground }}>
          {isRTL ? "قائمة الأمنيات" : "Wishlist"}
          {wishlist.length > 0 && (
            <span className="text-base font-normal mx-2" style={{ color: colors.mutedForeground }}>
              ({wishlist.length})
            </span>
          )}
        </h1>
        {wishlist.length === 0 ? (
          <div className="text-center py-20">
            <Heart className="h-16 w-16 mx-auto mb-4 opacity-20" style={{ color: colors.foreground }} />
            <p className="text-lg font-medium mb-2" style={{ color: colors.foreground }}>
              {isRTL ? "قائمة الأمنيات فارغة" : "Your wishlist is empty"}
            </p>
            <p className="text-sm mb-6" style={{ color: colors.mutedForeground }}>
              {isRTL ? "أضف منتجات إلى قائمة الأمنيات لحفظها لاحقاً" : "Add products to your wishlist to save them for later"}
            </p>
            <Link
              to="/products"
              className="inline-flex items-center gap-2 px-6 py-3 text-white font-medium"
              style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
            >
              <ShoppingBag className="h-4 w-4" />
              {isRTL ? "تصفح المنتجات" : "Browse Products"}
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {wishlist.map((product: any) => {
              const id = product.id || product.product_id;
              return (
                <div key={id} className="relative group">
                  <ProductCard product={product} />
                  <div className="absolute top-2 right-2 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                      onClick={() => removeFromWishlist(id)}
                      disabled={removing === id}
                      className="p-2 rounded-full shadow-md transition-colors"
                      style={{ backgroundColor: colors.card, color: "#ef4444" }}
                    >
                      {removing === id ? <Loader2 className="h-4 w-4 animate-spin" /> : <Trash2 className="h-4 w-4" />}
                    </button>
                    <button
                      onClick={() => handleAddToCart(product)}
                      className="p-2 rounded-full shadow-md transition-colors text-white"
                      style={{ backgroundColor: colors.primary }}
                    >
                      <ShoppingCart className="h-4 w-4" />
                    </button>
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

export default WishlistPage;
