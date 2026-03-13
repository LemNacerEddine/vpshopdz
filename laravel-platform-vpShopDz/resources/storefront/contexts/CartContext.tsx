import React, { createContext, useContext, useState, useEffect, useCallback, useMemo } from 'react';
import { useStore } from './StoreContext';
import { api } from '../lib/api';

export interface CartItem {
  product_id: string;
  variant_id?: string | null;
  name?: string;
  name_ar?: string;
  price?: number;
  original_price?: number;
  discount_percent?: number;
  image?: string | null;
  quantity: number;
  total?: number;
  slug?: string;
  stock?: number;
  product?: {
    id: string;
    name: string;
    name_ar?: string;
    price: number;
    final_price?: number;
    discount_percent?: number;
    images?: { url: string; is_primary: boolean }[];
    slug?: string;
    stock_quantity?: number;
    track_inventory?: boolean;
  };
}

interface CartContextType {
  items: CartItem[];
  loading: boolean;
  cartCount: number;
  cartTotal: number;
  addToCart: (productId: string, quantity?: number, variantId?: string) => Promise<boolean>;
  updateQuantity: (productId: string, quantity: number) => Promise<void>;
  removeFromCart: (productId: string) => Promise<void>;
  clearCart: () => Promise<void>;
  syncCart: () => Promise<void>;
  browserId: string;
}

const CartContext = createContext<CartContextType | null>(null);

export const useCart = (): CartContextType => {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
};

const getBrowserId = (storeSlug: string): string => {
  const key = `vpshopdz_browser_${storeSlug}`;
  let id = localStorage.getItem(key);
  if (!id) {
    id = 'b_' + Math.random().toString(36).substr(2, 9) + Date.now();
    localStorage.setItem(key, id);
  }
  return id;
};

export const CartProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { store, apiBase } = useStore();
  const [items, setItems] = useState<CartItem[]>([]);
  const [loading, setLoading] = useState(false);
  const browserId = useMemo(() => getBrowserId(store.slug), [store.slug]);
  const CART_KEY = `vpshopdz_cart_${store.slug}`;

  const saveLocal = (newItems: CartItem[]) => {
    try { localStorage.setItem(CART_KEY, JSON.stringify(newItems)); } catch { /* storage full */ }
  };

  const loadFromLocal = (): CartItem[] => {
    try {
      const saved = localStorage.getItem(CART_KEY);
      return saved ? JSON.parse(saved) : [];
    } catch { return []; }
  };

  const updateFromResponse = (data: any) => {
    const newItems: CartItem[] = data?.items || data?.data?.items || [];
    setItems(newItems);
    saveLocal(newItems);
  };

  // Load cart on mount
  useEffect(() => {
    const loadCart = async () => {
      try {
        setLoading(true);
        const response = await api.get(`${apiBase}/cart`);
        const cartData = response.data?.data || response.data;
        if (cartData?.items !== undefined) {
          updateFromResponse(cartData);
        } else {
          const local = loadFromLocal();
          if (local.length > 0) {
            // Sync local cart to server
            const syncRes = await api.post(`${apiBase}/cart/sync`, {
              items: local.map(i => ({ product_id: i.product_id, quantity: i.quantity, variant_id: i.variant_id })),
            }).catch(() => null);
            if (syncRes) {
              updateFromResponse(syncRes.data?.data || syncRes.data);
            } else {
              setItems(local);
            }
          }
        }
      } catch {
        setItems(loadFromLocal());
      } finally {
        setLoading(false);
      }
    };
    loadCart();
  }, [apiBase]);

  const syncCart = useCallback(async () => {
    try {
      const response = await api.get(`${apiBase}/cart`);
      updateFromResponse(response.data?.data || response.data);
    } catch { /* ignore */ }
  }, [apiBase]);

  const addToCart = useCallback(async (productId: string, quantity = 1, variantId?: string): Promise<boolean> => {
    try {
      setLoading(true);
      // POST /api/v1/store/{store}/cart/items
      const response = await api.post(`${apiBase}/cart/items`, {
        product_id: productId,
        quantity,
        variant_id: variantId || null,
      });
      updateFromResponse(response.data?.data || response.data);
      return true;
    } catch {
      // Local fallback
      const existing = items.findIndex(i => i.product_id === productId);
      let newItems: CartItem[];
      if (existing >= 0) {
        newItems = items.map((item, idx) =>
          idx === existing ? { ...item, quantity: item.quantity + quantity } : item
        );
      } else {
        newItems = [...items, { product_id: productId, quantity, variant_id: variantId || null }];
      }
      setItems(newItems);
      saveLocal(newItems);
      return true;
    } finally {
      setLoading(false);
    }
  }, [items, apiBase]);

  const updateQuantity = useCallback(async (productId: string, quantity: number) => {
    try {
      setLoading(true);
      if (quantity <= 0) {
        // DELETE /api/v1/store/{store}/cart/items/{itemId}
        const response = await api.delete(`${apiBase}/cart/items/${productId}`);
        updateFromResponse(response.data?.data || response.data);
      } else {
        // PUT /api/v1/store/{store}/cart/items/{itemId}
        const response = await api.put(`${apiBase}/cart/items/${productId}`, { quantity });
        updateFromResponse(response.data?.data || response.data);
      }
    } catch {
      const newItems = quantity > 0
        ? items.map(i => i.product_id === productId ? { ...i, quantity } : i)
        : items.filter(i => i.product_id !== productId);
      setItems(newItems);
      saveLocal(newItems);
    } finally {
      setLoading(false);
    }
  }, [items, apiBase]);

  const removeFromCart = useCallback(async (productId: string) => {
    try {
      setLoading(true);
      // DELETE /api/v1/store/{store}/cart/items/{itemId}
      const response = await api.delete(`${apiBase}/cart/items/${productId}`);
      updateFromResponse(response.data?.data || response.data);
    } catch {
      const newItems = items.filter(i => i.product_id !== productId);
      setItems(newItems);
      saveLocal(newItems);
    } finally {
      setLoading(false);
    }
  }, [items, apiBase]);

  const clearCart = useCallback(async () => {
    try {
      setLoading(true);
      // DELETE /api/v1/store/{store}/cart
      await api.delete(`${apiBase}/cart`);
    } catch { /* ignore */ }
    setItems([]);
    localStorage.removeItem(CART_KEY);
    setLoading(false);
  }, [apiBase, CART_KEY]);

  const cartCount = useMemo(
    () => items.reduce((sum, i) => sum + (i.quantity || 0), 0),
    [items]
  );

  const cartTotal = useMemo(
    () => items.reduce((sum, i) => {
      const price = i.price ?? i.product?.final_price ?? i.product?.price ?? 0;
      return sum + price * (i.quantity || 0);
    }, 0),
    [items]
  );

  const value: CartContextType = {
    items,
    loading,
    cartCount,
    cartTotal,
    addToCart,
    updateQuantity,
    removeFromCart,
    clearCart,
    syncCart,
    browserId,
  };

  return (
    <CartContext.Provider value={value}>
      {children}
    </CartContext.Provider>
  );
};
