import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const CartContext = createContext();

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;
const LOCAL_CART_KEY = 'agroyousfi_cart';

export const useCart = () => {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
};

export const CartProvider = ({ children }) => {
  const [cart, setCart] = useState({ items: [] });
  const [loading, setLoading] = useState(false);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  useEffect(() => {
    initializeCart();
  }, []);

  const initializeCart = async () => {
    try {
      setLoading(true);
      // Try to fetch cart from server (for logged in users)
      const response = await axios.get(`${API}/cart`, { withCredentials: true });
      setCart(response.data);
      setIsAuthenticated(true);
    } catch (error) {
      // If not authenticated, load from localStorage
      setIsAuthenticated(false);
      loadLocalCart();
    } finally {
      setLoading(false);
    }
  };

  const loadLocalCart = () => {
    try {
      const savedCart = localStorage.getItem(LOCAL_CART_KEY);
      if (savedCart) {
        const parsedCart = JSON.parse(savedCart);
        // Fetch product details for local cart items
        fetchProductsForLocalCart(parsedCart.items || []);
      }
    } catch (error) {
      console.error('Error loading local cart:', error);
    }
  };

  const fetchProductsForLocalCart = async (items) => {
    if (items.length === 0) {
      setCart({ items: [] });
      return;
    }

    try {
      const itemsWithProducts = await Promise.all(
        items.map(async (item) => {
          try {
            const response = await axios.get(`${API}/products/${item.product_id}`);
            return {
              ...item,
              product: response.data
            };
          } catch (error) {
            // Product might not exist anymore
            return null;
          }
        })
      );

      const validItems = itemsWithProducts.filter(item => item !== null);
      setCart({ items: validItems });
    } catch (error) {
      console.error('Error fetching products for cart:', error);
    }
  };

  const saveLocalCart = (items) => {
    const cartData = items.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity
    }));
    localStorage.setItem(LOCAL_CART_KEY, JSON.stringify({ items: cartData }));
  };

  const addToCart = async (productId, quantity = 1) => {
    console.log('addToCart called:', productId, quantity);
    try {
      // Always try server first for authenticated users
      try {
        console.log('Trying server cart...');
        await axios.post(
          `${API}/cart/add`,
          { product_id: productId, quantity },
          { withCredentials: true }
        );
        console.log('Server cart success');
        await initializeCart();
        return true;
      } catch (serverError) {
        console.log('Server error:', serverError.response?.status, serverError.message);
        // If server fails (401), use local cart
        if (serverError.response?.status === 401) {
          console.log('Using local cart...');
          // Local cart for guests
          const existingIndex = cart.items.findIndex(item => item.product_id === productId);
          let newItems;

          if (existingIndex >= 0) {
            newItems = cart.items.map((item, index) =>
              index === existingIndex
                ? { ...item, quantity: item.quantity + quantity }
                : item
            );
          } else {
            // Fetch product details
            console.log('Fetching product details...');
            const response = await axios.get(`${API}/products/${productId}`);
            console.log('Product fetched:', response.data.name_ar);
            newItems = [
              ...cart.items,
              {
                product_id: productId,
                quantity,
                product: response.data
              }
            ];
          }

          console.log('Setting cart with items:', newItems.length);
          setCart({ items: newItems });
          saveLocalCart(newItems);
          console.log('Local cart saved');
          return true;
        }
        throw serverError;
      }
    } catch (error) {
      console.error('Error adding to cart:', error);
      return false;
    }
  };

  const updateQuantity = async (productId, quantity) => {
    try {
      if (isAuthenticated) {
        await axios.put(
          `${API}/cart/update`,
          { product_id: productId, quantity },
          { withCredentials: true }
        );
        await initializeCart();
      } else {
        const newItems = cart.items.map(item =>
          item.product_id === productId
            ? { ...item, quantity }
            : item
        );
        setCart({ items: newItems });
        saveLocalCart(newItems);
      }
    } catch (error) {
      console.error('Error updating cart:', error);
    }
  };

  const removeFromCart = async (productId) => {
    try {
      if (isAuthenticated) {
        await axios.delete(`${API}/cart/remove/${productId}`, { withCredentials: true });
        await initializeCart();
      } else {
        const newItems = cart.items.filter(item => item.product_id !== productId);
        setCart({ items: newItems });
        saveLocalCart(newItems);
      }
    } catch (error) {
      console.error('Error removing from cart:', error);
    }
  };

  const clearCart = async () => {
    try {
      if (isAuthenticated) {
        await axios.delete(`${API}/cart/clear`, { withCredentials: true });
      }
      setCart({ items: [] });
      localStorage.removeItem(LOCAL_CART_KEY);
    } catch (error) {
      console.error('Error clearing cart:', error);
    }
  };

  // Sync local cart to server when user logs in
  const syncCartToServer = async () => {
    try {
      const localCart = localStorage.getItem(LOCAL_CART_KEY);
      if (localCart) {
        const parsed = JSON.parse(localCart);
        for (const item of parsed.items || []) {
          await axios.post(
            `${API}/cart/add`,
            { product_id: item.product_id, quantity: item.quantity },
            { withCredentials: true }
          );
        }
        localStorage.removeItem(LOCAL_CART_KEY);
      }
      await initializeCart();
    } catch (error) {
      console.error('Error syncing cart:', error);
    }
  };

  const cartTotal = cart.items.reduce((total, item) => {
    return total + (item.product?.price || 0) * item.quantity;
  }, 0);

  const cartCount = cart.items.reduce((count, item) => count + item.quantity, 0);

  const value = {
    cart,
    loading,
    addToCart,
    updateQuantity,
    removeFromCart,
    clearCart,
    fetchCart: initializeCart,
    syncCartToServer,
    cartTotal,
    cartCount,
    isAuthenticated
  };

  return (
    <CartContext.Provider value={value}>
      {children}
    </CartContext.Provider>
  );
};
