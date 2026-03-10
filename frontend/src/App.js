import React, { useEffect } from 'react';
import { BrowserRouter, Routes, Route, useLocation } from 'react-router-dom';
import { LanguageProvider } from '@/contexts/LanguageContext';
import { AuthProvider } from '@/contexts/AuthContext';
import { CartProvider } from '@/contexts/CartContext';
import { StoreSettingsProvider } from '@/contexts/StoreSettingsContext';
import { Layout } from '@/components/layout/Layout';
import { Toaster } from 'sonner';
import { initFBPixel, trackPageView } from '@/lib/fbPixel';

// Pages
import HomePage from '@/pages/HomePage';
import ProductsPage from '@/pages/ProductsPage';
import ProductDetailPage from '@/pages/ProductDetailPage';
import DealsPage from '@/pages/DealsPage';
import CategoriesPage from '@/pages/CategoriesPage';
import CartPage from '@/pages/CartPage';
import CheckoutPage from '@/pages/CheckoutPage';
import LoginPage from '@/pages/LoginPage';
import RegisterPage from '@/pages/RegisterPage';
import ForgotPasswordPage from '@/pages/ForgotPasswordPage';
import AuthCallback from '@/pages/AuthCallback';
import ProfilePage from '@/pages/ProfilePage';
import AdminRouter from '@/pages/admin/AdminRouter';
import ProductLandingPage from '@/pages/ProductLandingPage';
import RecoverCheckoutPage from '@/pages/RecoverCheckoutPage';

import '@/App.css';

// Scroll to top + Facebook Pixel PageView on route change
const ScrollToTop = () => {
    const { pathname } = useLocation();

    useEffect(() => {
        window.scrollTo(0, 0);
        trackPageView();
    }, [pathname]);

    return null;
};

// Router wrapper to handle auth callback
const AppRouter = () => {
    const location = useLocation();

    // Handle Google OAuth callback
    useEffect(() => {
        const urlParams = new URLSearchParams(location.search);
        const googleAuth = urlParams.get('google_auth');
        const userData = urlParams.get('user');

        if (googleAuth === 'success' && userData) {
            try {
                // Decode user data from base64
                const decodedUser = atob(userData);
                const user = JSON.parse(decodedUser);

                // Save to localStorage (same key as normal login)
                localStorage.setItem('agroyousfi_user', JSON.stringify(user));

                // Clean URL (remove query parameters)
                window.history.replaceState({}, document.title, window.location.pathname);

                // Reload to update UI
                window.location.reload();
            } catch (e) {
                console.error('Failed to process Google OAuth user data:', e);
            }
        }
    }, [location.search]);

    // Check URL fragment for session_id (Google Auth callback)
    if (location.hash?.includes('session_id=')) {
        return <AuthCallback />;
    }

    return (
        <Routes>
            {/* Landing page for ads (no Layout wrapper) */}
            <Route path="/p/:productId" element={<ProductLandingPage />} />

            {/* Recovery link for abandoned checkouts */}
            <Route path="/recover/:checkoutId" element={<Layout><RecoverCheckoutPage /></Layout>} />

            {/* Admin routes */}
            <Route path="/admin/*" element={<AdminRouter />} />

            {/* Product detail - must be before products list */}
            <Route path="/products/:productId" element={
                <Layout>
                    <ProductDetailPage />
                </Layout>
            } />

            {/* Products list */}
            <Route path="/products" element={
                <Layout>
                    <ProductsPage />
                </Layout>
            } />

            {/* Deals page */}
            <Route path="/deals" element={
                <Layout>
                    <DealsPage />
                </Layout>
            } />

            {/* Other public routes */}
            <Route path="/" element={<Layout><HomePage /></Layout>} />
            <Route path="/categories" element={<Layout><CategoriesPage /></Layout>} />
            <Route path="/cart" element={<Layout><CartPage /></Layout>} />
            <Route path="/checkout" element={<Layout><CheckoutPage /></Layout>} />
            <Route path="/login" element={<Layout><LoginPage /></Layout>} />
            <Route path="/register" element={<Layout><RegisterPage /></Layout>} />
            <Route path="/forgot-password" element={<Layout><ForgotPasswordPage /></Layout>} />
            <Route path="/auth/callback" element={<Layout><AuthCallback /></Layout>} />
            <Route path="/profile" element={<Layout><ProfilePage /></Layout>} />
            <Route path="/orders" element={<Layout><ProfilePage /></Layout>} />
        </Routes>
    );
};

function App() {
    useEffect(() => {
        initFBPixel();
    }, []);

    return (
        <>
            <ScrollToTop />
            <LanguageProvider>
                <AuthProvider>
                    <CartProvider>
                        <StoreSettingsProvider>
                            <AppRouter />
                            <Toaster position="top-center" richColors />
                        </StoreSettingsProvider>
                    </CartProvider>
                </AuthProvider>
            </LanguageProvider>
        </>
    );
}

export default App;
