import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const CartContext = createContext();

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;
const LOCAL_CART_KEY = 'agroyousfi_cart';
const BROWSER_ID_KEY = 'agroyousfi_browser_id';

export const useCart = () => {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
};

// Generate unique browser ID for guest users
const getBrowserId = () => {
  let browserId = localStorage.getItem(BROWSER_ID_KEY);
  if (!browserId) {
    browserId = 'browser_' + Math.random().toString(36).substr(2, 9) + Date.now();
    localStorage.setItem(BROWSER_ID_KEY, browserId);
  }
  return browserId;
};

export const CartProvider = ({ children }) => {
  const [cart, setCart] = useState({ items: [] });
  const [loading, setLoading] = useState(false);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [browserId] = useState(getBrowserId());

  useEffect(() => {
    initializeCart();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const initializeCart = async () => {
    try {
      setLoading(true);
      // Try to fetch cart from server
      const response = await axios.get(`${API}/cart?browser_id=${browserId}`, {
        withCredentials: true
      });
      setCart(response.data);

      // Check if user is authenticated
      try {
        await axios.get(`${API}/auth/me`, { withCredentials: true });
        setIsAuthenticated(true);
      } catch {
        setIsAuthenticated(false);
      }
    } catch (error) {
      // If server fails, load from localStorage as fallback
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
      } else {
        setCart({ items: [] });
      }
    } catch (error) {
      console.error('Error loading local cart:', error);
      setCart({ items: [] });
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

      // Save cleaned cart back to localStorage
      saveLocalCart(validItems);
    } catch (error) {
      console.error('Error fetching products for cart:', error);
      setCart({ items: [] });
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
    try {
      setLoading(true);

      // Try server first (works for both authenticated and guest users with browser_id)
      try {
        const response = await axios.post(
          `${API}/cart/add`,
          {
            product_id: productId,
            quantity,
            browser_id: browserId
          },
          { withCredentials: true }
        );

        // Refresh cart from server
        await initializeCart();
        return true;
      } catch (serverError) {
        console.log('Server cart failed, using local cart:', serverError.message);

        // Fallback to local cart
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
          const response = await axios.get(`${API}/products/${productId}`);
          newItems = [
            ...cart.items,
            {
              product_id: productId,
              quantity,
              product: response.data
            }
          ];
        }

        setCart({ items: newItems });
        saveLocalCart(newItems);
        return true;
      }
    } catch (error) {
      console.error('Error adding to cart:', error);
      return false;
    } finally {
      setLoading(false);
    }
  };

  const updateQuantity = async (productId, quantity) => {
    try {
      setLoading(true);

      // Try server first
      try {
        await axios.put(
          `${API}/cart/update`,
          {
            product_id: productId,
            quantity,
            browser_id: browserId
          },
          { withCredentials: true }
        );
        await initializeCart();
      } catch (serverError) {
        // Fallback to local cart
        const newItems = quantity > 0
          ? cart.items.map(item =>
            item.product_id === productId
              ? { ...item, quantity }
              : item
          )
          : cart.items.filter(item => item.product_id !== productId);

        setCart({ items: newItems });
        saveLocalCart(newItems);
      }
    } catch (error) {
      console.error('Error updating cart:', error);
    } finally {
      setLoading(false);
    }
  };

  const removeFromCart = async (productId) => {
    try {
      setLoading(true);

      // Try server first
      try {
        await axios.delete(
          `${API}/cart/remove/${productId}?browser_id=${browserId}`,
          { withCredentials: true }
        );
        await initializeCart();
      } catch (serverError) {
        // Fallback to local cart
        const newItems = cart.items.filter(item => item.product_id !== productId);
        setCart({ items: newItems });
        saveLocalCart(newItems);
      }
    } catch (error) {
      console.error('Error removing from cart:', error);
    } finally {
      setLoading(false);
    }
  };

  const clearCart = async () => {
    try {
      setLoading(true);

      // Try server first
      try {
        await axios.delete(
          `${API}/cart/clear`,
          {
            data: { browser_id: browserId },
            withCredentials: true
          }
        );
      } catch (serverError) {
        // Ignore server errors for clear
      }

      // Always clear local cart
      setCart({ items: [] });
      localStorage.removeItem(LOCAL_CART_KEY);
    } catch (error) {
      console.error('Error clearing cart:', error);
    } finally {
      setLoading(false);
    }
  };

  // Only clear local state (for logout) to preserve server cart
  const resetCart = () => {
    setCart({ items: [] });
    localStorage.removeItem(LOCAL_CART_KEY);
  };

  // Sync local cart to server when user logs in
  const syncCartToServer = async () => {
    try {
      setLoading(true);

      // Sync local cart items to server
      const localCart = localStorage.getItem(LOCAL_CART_KEY);
      if (localCart) {
        const parsed = JSON.parse(localCart);
        for (const item of parsed.items || []) {
          try {
            await axios.post(
              `${API}/cart/add`,
              {
                product_id: item.product_id,
                quantity: item.quantity,
                browser_id: browserId
              },
              { withCredentials: true }
            );
          } catch (error) {
            console.error('Error syncing item:', error);
          }
        }
        localStorage.removeItem(LOCAL_CART_KEY);
      }

      // Refresh cart from server
      await initializeCart();
      setIsAuthenticated(true);
    } catch (error) {
      console.error('Error syncing cart:', error);
    } finally {
      setLoading(false);
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
    resetCart,
    fetchCart: initializeCart,
    syncCartToServer,
    cartTotal,
    cartCount,
    isAuthenticated,
    browserId
  };

  return (
    <CartContext.Provider value={value}>
      {children}
    </CartContext.Provider>
  );
};


/*
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
    try {
      // Always try server first for authenticated users
      try {
        await axios.post(
          `${API}/cart/add`,
          { product_id: productId, quantity },
          { withCredentials: true }
        );
        await initializeCart();
        return true;
      } catch (serverError) {
        // If server fails (401), use local cart
        if (serverError.response?.status === 401) {
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
            const response = await axios.get(`${API}/products/${productId}`);
            newItems = [
              ...cart.items,
              {
                product_id: productId,
                quantity,
                product: response.data
              }
            ];
          }

          setCart({ items: newItems });
          saveLocalCart(newItems);
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
*/