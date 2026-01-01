import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import AdminLayout from '@/components/admin/AdminLayout';
import DashboardHome from '@/pages/admin/DashboardHome';
import ProductsPage from '@/pages/admin/ProductsPage';
import ProductForm from '@/pages/admin/ProductForm';
import OrdersPage from '@/pages/admin/OrdersPage';
import CustomersPage from '@/pages/admin/CustomersPage';
import SettingsPage from '@/pages/admin/SettingsPage';
import { useAuth } from '@/contexts/AuthContext';

const AdminRouter = () => {
  const { isAdmin, loading } = useAuth();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary" />
      </div>
    );
  }

  if (!isAdmin) {
    return <Navigate to="/login" replace />;
  }

  return (
    <AdminLayout>
      <Routes>
        <Route path="/" element={<DashboardHome />} />
        <Route path="/products" element={<ProductsPage />} />
        <Route path="/products/new" element={<ProductForm />} />
        <Route path="/products/:productId/edit" element={<ProductForm />} />
        <Route path="/categories" element={<ProductsPage />} />
        <Route path="/orders" element={<OrdersPage />} />
        <Route path="/orders/:orderId" element={<OrdersPage />} />
        <Route path="/customers" element={<CustomersPage />} />
        <Route path="/analytics" element={<DashboardHome />} />
        <Route path="/finance/*" element={<DashboardHome />} />
        <Route path="/settings/*" element={<SettingsPage />} />
        <Route path="*" element={<Navigate to="/admin" replace />} />
      </Routes>
    </AdminLayout>
  );
};

export default AdminRouter;
