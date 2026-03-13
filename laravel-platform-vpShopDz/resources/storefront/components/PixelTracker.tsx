/**
 * Pixel Tracking Utilities
 * Handles Facebook, Google, TikTok, and Snapchat pixel events
 */

declare global {
  interface Window {
    fbq?: (...args: any[]) => void;
    gtag?: (...args: any[]) => void;
    ttq?: any;
    snaptr?: (...args: any[]) => void;
  }
}

// Track page view
export const trackPageView = () => {
  if (window.fbq) window.fbq('track', 'PageView');
  if (window.gtag) window.gtag('event', 'page_view');
  if (window.ttq) window.ttq.page();
  if (window.snaptr) window.snaptr('track', 'PAGE_VIEW');
};

// Track product view
export const trackViewContent = (product: {
  id: string | number;
  name: string;
  price: number;
  category?: string;
}) => {
  if (window.fbq) {
    window.fbq('track', 'ViewContent', {
      content_ids: [product.id],
      content_name: product.name,
      content_type: 'product',
      value: product.price,
      currency: 'DZD',
    });
  }
  if (window.gtag) {
    window.gtag('event', 'view_item', {
      items: [{
        item_id: product.id,
        item_name: product.name,
        price: product.price,
        item_category: product.category,
      }],
    });
  }
  if (window.ttq) {
    window.ttq.track('ViewContent', {
      content_id: String(product.id),
      content_name: product.name,
      value: product.price,
      currency: 'DZD',
    });
  }
};

// Track add to cart
export const trackAddToCart = (product: {
  id: string | number;
  name: string;
  price: number;
  quantity: number;
}) => {
  if (window.fbq) {
    window.fbq('track', 'AddToCart', {
      content_ids: [product.id],
      content_name: product.name,
      content_type: 'product',
      value: product.price * product.quantity,
      currency: 'DZD',
      num_items: product.quantity,
    });
  }
  if (window.gtag) {
    window.gtag('event', 'add_to_cart', {
      items: [{
        item_id: product.id,
        item_name: product.name,
        price: product.price,
        quantity: product.quantity,
      }],
    });
  }
  if (window.ttq) {
    window.ttq.track('AddToCart', {
      content_id: String(product.id),
      content_name: product.name,
      value: product.price * product.quantity,
      currency: 'DZD',
      quantity: product.quantity,
    });
  }
};

// Track initiate checkout
export const trackInitiateCheckout = (data: {
  value: number;
  numItems: number;
  items: Array<{ id: string | number; name: string; price: number; quantity: number }>;
}) => {
  if (window.fbq) {
    window.fbq('track', 'InitiateCheckout', {
      content_ids: data.items.map(i => i.id),
      value: data.value,
      currency: 'DZD',
      num_items: data.numItems,
    });
  }
  if (window.gtag) {
    window.gtag('event', 'begin_checkout', {
      value: data.value,
      items: data.items.map(i => ({
        item_id: i.id,
        item_name: i.name,
        price: i.price,
        quantity: i.quantity,
      })),
    });
  }
  if (window.ttq) {
    window.ttq.track('InitiateCheckout', {
      value: data.value,
      currency: 'DZD',
      quantity: data.numItems,
    });
  }
};

// Track purchase
export const trackPurchase = (data: {
  orderId: string;
  value: number;
  items: Array<{ id: string | number; name: string; price: number; quantity: number }>;
}) => {
  if (window.fbq) {
    window.fbq('track', 'Purchase', {
      content_ids: data.items.map(i => i.id),
      content_type: 'product',
      value: data.value,
      currency: 'DZD',
      order_id: data.orderId,
      num_items: data.items.reduce((sum, i) => sum + i.quantity, 0),
    });
  }
  if (window.gtag) {
    window.gtag('event', 'purchase', {
      transaction_id: data.orderId,
      value: data.value,
      currency: 'DZD',
      items: data.items.map(i => ({
        item_id: i.id,
        item_name: i.name,
        price: i.price,
        quantity: i.quantity,
      })),
    });
  }
  if (window.ttq) {
    window.ttq.track('CompletePayment', {
      value: data.value,
      currency: 'DZD',
      order_id: data.orderId,
      quantity: data.items.reduce((sum, i) => sum + i.quantity, 0),
    });
  }
  if (window.snaptr) {
    window.snaptr('track', 'PURCHASE', {
      price: data.value,
      currency: 'DZD',
      transaction_id: data.orderId,
    });
  }
};

// Track search
export const trackSearch = (query: string) => {
  if (window.fbq) {
    window.fbq('track', 'Search', { search_string: query });
  }
  if (window.gtag) {
    window.gtag('event', 'search', { search_term: query });
  }
};
