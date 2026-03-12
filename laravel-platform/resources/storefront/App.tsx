import React, { useEffect, lazy, Suspense } from 'react';
import { Routes, Route, useLocation } from 'react-router-dom';
import { StoreProvider } from './contexts/StoreContext';
import { ThemeProvider } from './contexts/ThemeContext';
import { LanguageProvider } from './contexts/LanguageContext';
import { CartProvider } from './contexts/CartContext';
import { CustomerAuthProvider } from './contexts/CustomerAuthContext';
import { Layout } from './components/layout/Layout';
import ErrorBoundary from './components/ErrorBoundary';
import { Toaster } from 'sonner';

// Pages - Lazy loaded for performance
const HomePage = lazy(() => import('./pages/HomePage'));
const ProductsPage = lazy(() => import('./pages/ProductsPage'));
const ProductDetailPage = lazy(() => import('./pages/ProductDetailPage'));
const CartPage = lazy(() => import('./pages/CartPage'));
const CheckoutPage = lazy(() => import('./pages/CheckoutPage'));
const CategoryPage = lazy(() => import('./pages/CategoryPage'));
const SearchPage = lazy(() => import('./pages/SearchPage'));
const CustomPage = lazy(() => import('./pages/CustomPage'));
const OrderTrackingPage = lazy(() => import('./pages/OrderTrackingPage'));
const WishlistPage = lazy(() => import('./pages/WishlistPage'));
const NotFoundPage = lazy(() => import('./pages/NotFoundPage'));
const LoginPage = lazy(() => import('./pages/LoginPage'));
const RegisterPage = lazy(() => import('./pages/RegisterPage'));
const ProfilePage = lazy(() => import('./pages/ProfilePage'));

// Loading fallback
const PageLoader: React.FC = () => (
  <div className="min-h-screen flex items-center justify-center">
    <div className="flex flex-col items-center gap-3">
      <div className="animate-spin h-8 w-8 border-4 rounded-full" style={{ borderColor: 'var(--color-muted)', borderTopColor: 'var(--color-primary)' }} />
      <span className="text-sm" style={{ color: 'var(--color-muted-foreground)' }}>جاري التحميل...</span>
    </div>
  </div>
);

// Scroll to top on route change
const ScrollToTop: React.FC = () => {
  const { pathname } = useLocation();
  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);
  return null;
};

const App: React.FC = () => {
  return (
    <StoreProvider>
      <ErrorBoundary>
        <ThemeProvider>
          <LanguageProvider>
            <CustomerAuthProvider>
              <CartProvider>
                <ScrollToTop />
                <Suspense fallback={<PageLoader />}>
                  <Routes>
                    <Route element={<Layout />}>
                      <Route path="/" element={<HomePage />} />
                      <Route path="/products" element={<ProductsPage />} />
                      <Route path="/products/:productId" element={<ProductDetailPage />} />
                      <Route path="/product/:productId" element={<ProductDetailPage />} />
                      <Route path="/category/:categoryId" element={<CategoryPage />} />
                      <Route path="/categories/:categoryId" element={<CategoryPage />} />
                      <Route path="/search" element={<SearchPage />} />
                      <Route path="/cart" element={<CartPage />} />
                      <Route path="/checkout" element={<CheckoutPage />} />
                      <Route path="/track" element={<OrderTrackingPage />} />
                      <Route path="/track/:orderId" element={<OrderTrackingPage />} />
                      <Route path="/wishlist" element={<WishlistPage />} />
                      <Route path="/page/:slug" element={<CustomPage />} />
                      <Route path="/pages/:slug" element={<CustomPage />} />
                      {/* Customer Auth */}
                      <Route path="/login" element={<LoginPage />} />
                      <Route path="/register" element={<RegisterPage />} />
                      <Route path="/profile" element={<ProfilePage />} />
                      <Route path="*" element={<NotFoundPage />} />
                    </Route>
                  </Routes>
                </Suspense>
                <Toaster
                  position="top-center"
                  richColors
                  toastOptions={{
                    style: {
                      fontFamily: 'var(--font-body)',
                    },
                  }}
                />
              </CartProvider>
            </CustomerAuthProvider>
          </LanguageProvider>
        </ThemeProvider>
      </ErrorBoundary>
    </StoreProvider>
  );
};

export default App;
