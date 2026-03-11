import React, { createContext, useContext, useState, useEffect, useCallback, useMemo } from 'react';
import { useStore } from './StoreContext';
import { api } from '../lib/api';

interface CartItem {
  product_id: string;
  quantity: number;
  variant_id?: string;
  product?: any;
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

  // Load cart on mount
  useEffect(() => {
    loadCart();
  }, []);

  const loadCart = async () => {
    try {
      setLoading(true);
      const response = await api.get(`${apiBase}/cart`, { params: { browser_id: browserId } });
      if (response.data?.items) {
        setItems(response.data.items);
      }
    } catch {
      // Fallback to localStorage
      const saved = localStorage.getItem(CART_KEY);
      if (saved) {
        try {
          setItems(JSON.parse(saved));
        } catch {
          setItems([]);
        }
      }
    } finally {
      setLoading(false);
    }
  };

  const saveLocal = (newItems: CartItem[]) => {
    localStorage.setItem(CART_KEY, JSON.stringify(newItems));
  };

  const addToCart = useCallback(async (productId: string, quantity = 1, variantId?: string): Promise<boolean> => {
    try {
      setLoading(true);
      const response = await api.post(`${apiBase}/cart/add`, {
        product_id: productId,
        quantity,
        variant_id: variantId,
        browser_id: browserId,
      });
      if (response.data?.items) {
        setItems(response.data.items);
        saveLocal(response.data.items);
      } else {
        await loadCart();
      }
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
        newItems = [...items, { product_id: productId, quantity, variant_id: variantId }];
      }
      setItems(newItems);
      saveLocal(newItems);
      return true;
    } finally {
      setLoading(false);
    }
  }, [items, apiBase, browserId]);

  const updateQuantity = useCallback(async (productId: string, quantity: number) => {
    try {
      setLoading(true);
      await api.put(`${apiBase}/cart/update`, {
        product_id: productId,
        quantity,
        browser_id: browserId,
      });
      await loadCart();
    } catch {
      const newItems = quantity > 0
        ? items.map(i => i.product_id === productId ? { ...i, quantity } : i)
        : items.filter(i => i.product_id !== productId);
      setItems(newItems);
      saveLocal(newItems);
    } finally {
      setLoading(false);
    }
  }, [items, apiBase, browserId]);

  const removeFromCart = useCallback(async (productId: string) => {
    try {
      setLoading(true);
      await api.delete(`${apiBase}/cart/remove/${productId}`, {
        params: { browser_id: browserId },
      });
      await loadCart();
    } catch {
      const newItems = items.filter(i => i.product_id !== productId);
      setItems(newItems);
      saveLocal(newItems);
    } finally {
      setLoading(false);
    }
  }, [items, apiBase, browserId]);

  const clearCart = useCallback(async () => {
    try {
      setLoading(true);
      await api.delete(`${apiBase}/cart/clear`, {
        data: { browser_id: browserId },
      });
    } catch { /* ignore */ }
    setItems([]);
    localStorage.removeItem(CART_KEY);
    setLoading(false);
  }, [apiBase, browserId]);

  const cartCount = useMemo(() => items.reduce((sum, i) => sum + i.quantity, 0), [items]);
  const cartTotal = useMemo(() =>
    items.reduce((sum, i) => sum + (i.product?.price || 0) * i.quantity, 0),
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
    browserId,
  };

  return (
    <CartContext.Provider value={value}>
      {children}
    </CartContext.Provider>
  );
};
