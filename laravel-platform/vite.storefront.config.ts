import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  root: path.resolve(__dirname, 'resources/storefront'),
  base: '/storefront/',
  build: {
    outDir: path.resolve(__dirname, 'public/storefront'),
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: path.resolve(__dirname, 'resources/storefront/index.html'),
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/storefront'),
    },
  },
  server: {
    port: 5174,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
});
