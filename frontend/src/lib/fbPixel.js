const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

let pixelId = null;

export const initFBPixel = async () => {
  if (window.fbq) return; // Already initialized

  // Fetch pixel ID from backend settings
  try {
    const res = await fetch(`${API}/settings/public`);
    const data = await res.json();
    pixelId = data.fb_pixel_id;
  } catch {
    // Silently fail
  }

  if (!pixelId) return;

  // Facebook Pixel base code
  (function (f, b, e, v, n, t, s) {
    if (f.fbq) return;
    n = f.fbq = function () {
      n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
    };
    if (!f._fbq) f._fbq = n;
    n.push = n;
    n.loaded = !0;
    n.version = '2.0';
    n.queue = [];
    t = b.createElement(e);
    t.async = !0;
    t.src = v;
    s = b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t, s);
  })(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

  window.fbq('init', pixelId);
  window.fbq('track', 'PageView');
};

const fbq = (...args) => {
  if (!pixelId || !window.fbq) return;
  window.fbq(...args);
};

// Standard events
export const trackPageView = () => {
  fbq('track', 'PageView');
};

export const trackViewContent = ({ id, name, category, price, currency = 'DZD' }) => {
  fbq('track', 'ViewContent', {
    content_ids: [id],
    content_name: name,
    content_category: category,
    content_type: 'product',
    value: price,
    currency,
  });
};

export const trackAddToCart = ({ id, name, price, quantity = 1, currency = 'DZD' }) => {
  fbq('track', 'AddToCart', {
    content_ids: [id],
    content_name: name,
    content_type: 'product',
    value: price * quantity,
    currency,
    num_items: quantity,
  });
};

export const trackInitiateCheckout = ({ items, total, currency = 'DZD' }) => {
  fbq('track', 'InitiateCheckout', {
    content_ids: items.map(i => i.product_id),
    content_type: 'product',
    value: total,
    currency,
    num_items: items.reduce((sum, i) => sum + (i.quantity || 1), 0),
  });
};

export const trackPurchase = ({ orderId, total, items, currency = 'DZD' }) => {
  fbq('track', 'Purchase', {
    content_ids: items.map(i => i.product_id),
    content_type: 'product',
    value: total,
    currency,
    num_items: items.reduce((sum, i) => sum + (i.quantity || 1), 0),
    order_id: orderId,
  });
};
