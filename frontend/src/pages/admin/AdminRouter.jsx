import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import AdminLayout from '@/components/admin/AdminLayout';
import DashboardHome from '@/pages/admin/DashboardHome';
import ProductsPage from '@/pages/admin/ProductsPage';
import ProductForm from '@/pages/admin/ProductForm';
import OrdersPage from '@/pages/admin/OrdersPage';
import CustomersPage from '@/pages/admin/CustomersPage';
import CategoriesPage from '@/pages/admin/CategoriesPage';
import SettingsPage from '@/pages/admin/SettingsPage';
import ShippingCompaniesPage from '@/pages/admin/ShippingCompaniesPage';
import ShippingRatesPage from '@/pages/admin/ShippingRatesPage';
import ShippingRulesPage from '@/pages/admin/ShippingRulesPage';
import AbandonedCheckoutsPage from '@/pages/admin/AbandonedCheckoutsPage';
import FacebookAdsPage from '@/pages/admin/FacebookAdsPage';
import { useAuth } from '@/contexts/AuthContext';

const AdminRouter = () => {
  const { isAdmin, loading, user } = useAuth();

  // Show loading only if we don't have a cached user
  if (loading && !user) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary" />
      </div>
    );
  }

  // Check if user is admin (from cached or fresh data)
  if (!isAdmin && !loading) {
    return <Navigate to="/login" replace />;
  }

  // If still loading but we have a cached admin user, show the dashboard
  if (loading && user?.role === 'admin') {
    // Continue to show dashboard while verifying
  }

  return (
    <AdminLayout>
      <Routes>
        <Route index element={<DashboardHome />} />
        <Route path="products" element={<ProductsPage />} />
        <Route path="products/new" element={<ProductForm />} />
        <Route path="products/:productId/edit" element={<ProductForm />} />
        <Route path="categories" element={<CategoriesPage />} />
        <Route path="orders" element={<OrdersPage />} />
        <Route path="orders/:orderId" element={<OrdersPage />} />
        <Route path="abandoned-checkouts" element={<AbandonedCheckoutsPage />} />
        <Route path="customers" element={<CustomersPage />} />
        <Route path="analytics" element={<DashboardHome />} />
        <Route path="finance/*" element={<DashboardHome />} />
        <Route path="shipping" element={<ShippingCompaniesPage />} />
        <Route path="shipping/:companyId/rates" element={<ShippingRatesPage />} />
        <Route path="shipping/rules" element={<ShippingRulesPage />} />
        <Route path="facebook-ads" element={<FacebookAdsPage />} />
        <Route path="settings/*" element={<SettingsPage />} />
        <Route path="*" element={<Navigate to="/admin" replace />} />
      </Routes>
    </AdminLayout>
  );
};

export default AdminRouter;
