import React from 'react';
import { BrowserRouter, Routes, Route, useLocation } from 'react-router-dom';
import { LanguageProvider } from '@/contexts/LanguageContext';
import { AuthProvider } from '@/contexts/AuthContext';
import { CartProvider } from '@/contexts/CartContext';
import { Layout } from '@/components/layout/Layout';
import { Toaster } from 'sonner';

// Pages
import HomePage from '@/pages/HomePage';
import ProductsPage from '@/pages/ProductsPage';
import ProductDetailPage from '@/pages/ProductDetailPage';
import CategoriesPage from '@/pages/CategoriesPage';
import CartPage from '@/pages/CartPage';
import CheckoutPage from '@/pages/CheckoutPage';
import LoginPage from '@/pages/LoginPage';
import AuthCallback from '@/pages/AuthCallback';
import ProfilePage from '@/pages/ProfilePage';
import AdminRouter from '@/pages/admin/AdminRouter';

import '@/App.css';

// Router wrapper to handle auth callback
const AppRouter = () => {
  const location = useLocation();
  
  // Check URL fragment for session_id (Google Auth callback)
  if (location.hash?.includes('session_id=')) {
    return <AuthCallback />;
  }

  // Admin routes - separate layout
  if (location.pathname.startsWith('/admin')) {
    return <AdminRouter />;
  }

  return (
    <Layout>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/products" element={<ProductsPage />} />
        <Route path="/products/:productId" element={<ProductDetailPage />} />
        <Route path="/categories" element={<CategoriesPage />} />
        <Route path="/cart" element={<CartPage />} />
        <Route path="/checkout" element={<CheckoutPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/auth/callback" element={<AuthCallback />} />
        <Route path="/profile" element={<ProfilePage />} />
        <Route path="/orders" element={<ProfilePage />} />
      </Routes>
    </Layout>
  );
};

function App() {
  return (
    <BrowserRouter>
      <LanguageProvider>
        <AuthProvider>
          <CartProvider>
            <AppRouter />
          </CartProvider>
        </AuthProvider>
      </LanguageProvider>
    </BrowserRouter>
  );
}

export default App;
