import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function truncate(str: string, length: number): string {
  if (str.length <= length) return str;
  return str.slice(0, length) + '...';
}

export function slugify(str: string): string {
  return str
    .toLowerCase()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export function getImageUrl(path: string | null, fallback = '/images/placeholder.png'): string {
  if (!path) return fallback;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
}

export function getProductName(product: any, language: string): string {
  return product[`name_${language}`] || product.name_ar || product.name || '';
}

export function getProductDescription(product: any, language: string): string {
  return product[`description_${language}`] || product.description_ar || product.description || '';
}

export function getCategoryName(category: any, language: string): string {
  return category[`name_${language}`] || category.name_ar || category.name || '';
}

export function calculateDiscount(price: number, oldPrice: number | null): number {
  if (!oldPrice || oldPrice <= price) return 0;
  return Math.round(((oldPrice - price) / oldPrice) * 100);
}

export function debounce<T extends (...args: any[]) => any>(
  func: T,
  wait: number
): (...args: Parameters<T>) => void {
  let timeout: ReturnType<typeof setTimeout>;
  return (...args: Parameters<T>) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
}
