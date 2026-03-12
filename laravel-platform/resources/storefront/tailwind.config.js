import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    resolve(__dirname, './**/*.{ts,tsx,html,js}'),
    resolve(__dirname, './index.html'),
    resolve(__dirname, './main.tsx'),
    resolve(__dirname, './App.tsx'),
    resolve(__dirname, './pages/**/*.{ts,tsx}'),
    resolve(__dirname, './components/**/*.{ts,tsx}'),
    resolve(__dirname, './contexts/**/*.{ts,tsx}'),
    resolve(__dirname, './themes/**/*.{ts,tsx}'),
    resolve(__dirname, './i18n/**/*.{ts,tsx}'),
    resolve(__dirname, './lib/**/*.{ts,tsx}'),
  ],
  theme: {
    extend: {
      colors: {
        primary: 'var(--color-primary)',
        secondary: 'var(--color-secondary)',
        accent: 'var(--color-accent)',
        background: 'var(--color-background)',
        foreground: 'var(--color-foreground)',
        card: 'var(--color-card)',
        muted: 'var(--color-muted)',
        border: 'var(--color-border)',
        'header-bg': 'var(--color-header-bg)',
        'header-text': 'var(--color-header-text)',
        'footer-bg': 'var(--color-footer-bg)',
        'footer-text': 'var(--color-footer-text)',
      },
      fontFamily: {
        heading: ['var(--font-heading)', 'Tajawal', 'Cairo', 'sans-serif'],
        body: ['var(--font-body)', 'Tajawal', 'Cairo', 'sans-serif'],
        sans: ['Tajawal', 'Cairo', 'sans-serif'],
      },
      borderRadius: {
        button: 'var(--button-radius)',
        card: 'var(--card-radius)',
        input: 'var(--input-radius)',
      },
    },
  },
  plugins: [],
};
