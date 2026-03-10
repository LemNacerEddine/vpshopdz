/**
 * HTML-based Invoice Generator
 *
 * Uses the browser's native text rendering for proper Arabic letter shaping.
 * Opens in a new window for print/save as PDF.
 */

/**
 * Generate and print an HTML invoice
 * @param {Object} order - The order data
 * @param {Object} storeInfo - Store settings
 * @param {string} language - Current language ('ar', 'fr', 'en')
 * @param {Object} translations - Translation strings
 */
export function generateHtmlInvoice(order, storeInfo, language, translations) {
  const isRtl = language === 'ar';
  const dir = isRtl ? 'rtl' : 'ltr';
  const text = translations;
  const sName = storeInfo.store_name || 'AgroYousfi';
  const sPhone = storeInfo.store_phone || '';
  const sEmail = storeInfo.store_email || '';
  const sAddress = storeInfo.store_address || '';
  const sRC = storeInfo.store_rc || '';
  const sNIF = storeInfo.store_nif || '';
  const sNIS = storeInfo.store_nis || '';
  const sWebsite = storeInfo.store_website || '';

  const priceStr = (price) => `${(parseFloat(price) || 0).toLocaleString()} DZD`;

  const items = order.items || [];
  const discountAmount = parseFloat(order.discount_amount) || 0;
  const discountPercentage = parseFloat(order.discount_percentage) || 0;
  const shippingCost = parseFloat(order.shipping_cost) || 0;
  const subtotalVal = order.subtotal || (order.total + discountAmount - shippingCost);

  const orderDate = order.created_at
    ? new Date(order.created_at).toLocaleString(isRtl ? 'ar-DZ' : 'fr-DZ', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit'
      })
    : '-';

  const statusLabels = {
    pending: text.pending || 'Pending',
    confirmed: text.confirmed || 'Confirmed',
    processing: text.processing || 'Processing',
    shipped: text.shipped || 'Shipped',
    delivered: text.delivered || 'Delivered',
    cancelled: text.cancelled || 'Cancelled',
  };

  const statusColors = {
    pending: '#b45309',
    confirmed: '#2563eb',
    processing: '#ea580c',
    shipped: '#7c3aed',
    delivered: '#16a34a',
    cancelled: '#dc2626',
  };

  // Build business info line
  const businessParts = [];
  if (sPhone) businessParts.push(sPhone);
  if (sEmail) businessParts.push(sEmail);
  if (sAddress) businessParts.push(sAddress);

  const taxParts = [];
  if (sRC) taxParts.push(`RC: ${sRC}`);
  if (sNIF) taxParts.push(`NIF: ${sNIF}`);
  if (sNIS) taxParts.push(`NIS: ${sNIS}`);

  // Build items rows
  const itemsHtml = items.map(item => {
    const name = item.product_name || item.name || 'Product';
    const qty = item.quantity || 1;
    const price = item.price || 0;
    const total = price * qty;
    return `<tr>
      <td>${name}</td>
      <td>${qty}</td>
      <td>${priceStr(price)}</td>
      <td>${priceStr(total)}</td>
    </tr>`;
  }).join('');

  const noItemsLabel = isRtl ? 'لا توجد منتجات' : 'No items';
  const itemsContent = items.length > 0
    ? itemsHtml
    : `<tr><td colspan="4" style="text-align:center;">${noItemsLabel}</td></tr>`;

  const customerName = order.customer_name || '-';
  const customerPhone = order.customer_phone || order.phone || '-';
  const wilaya = order.wilaya || '-';
  const address = order.shipping_address || order.address || '-';

  const logoHtml = storeInfo.store_logo
    ? `<img src="${storeInfo.store_logo}" alt="Logo" style="height:60px;margin-bottom:8px;" /><br/>`
    : '';

  const html = `<!DOCTYPE html>
<html lang="${language}" dir="${dir}">
<head>
  <meta charset="UTF-8" />
  <title>${text.invoice || 'Invoice'} #${order.order_id || ''}</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;600;700&family=Noto+Sans:wght@400;600;700&display=swap');

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Noto Sans Arabic', 'Noto Sans', Arial, sans-serif;
      font-size: 14px;
      color: #1a1a1a;
      direction: ${dir};
      padding: 20px 40px;
      max-width: 800px;
      margin: 0 auto;
    }

    .header {
      text-align: center;
      padding-bottom: 15px;
      border-bottom: 2px solid #22543d;
      margin-bottom: 20px;
    }

    .store-name {
      font-size: 24px;
      font-weight: 700;
      color: #22543d;
      margin-bottom: 4px;
    }

    .store-info {
      font-size: 11px;
      color: #666;
      margin-bottom: 2px;
    }

    .invoice-title {
      text-align: center;
      margin-bottom: 5px;
    }

    .invoice-title h2 {
      font-size: 16px;
      color: #555;
      font-weight: 600;
    }

    .invoice-date {
      text-align: center;
      color: #888;
      font-size: 13px;
      margin-bottom: 20px;
    }

    .section-title {
      font-size: 15px;
      font-weight: 700;
      color: #1a1a1a;
      margin-bottom: 10px;
    }

    .customer-info {
      margin-bottom: 25px;
    }

    .customer-info p {
      font-size: 13px;
      color: #444;
      margin-bottom: 5px;
    }

    .customer-info .label {
      font-weight: 600;
      color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    thead th {
      background-color: #22543d;
      color: #fff;
      padding: 10px 12px;
      font-weight: 600;
      font-size: 13px;
      text-align: ${isRtl ? 'right' : 'left'};
    }

    tbody td {
      padding: 9px 12px;
      font-size: 13px;
      border-bottom: 1px solid #e5e5e5;
    }

    tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .totals-table {
      margin-top: 0;
    }

    .totals-table td {
      padding: 8px 12px;
      font-size: 13px;
    }

    .totals-table .label-cell {
      font-weight: 700;
      color: #333;
    }

    .totals-table .total-row {
      font-size: 16px;
      font-weight: 700;
      border-top: 2px solid #22543d;
    }

    .totals-table .discount-row td {
      color: #dc2626;
    }

    .footer-info {
      margin-top: 25px;
      padding-top: 15px;
      border-top: 1px solid #ddd;
    }

    .footer-info p {
      font-size: 12px;
      color: #666;
      margin-bottom: 4px;
    }

    .status-badge {
      display: inline-block;
      padding: 3px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      color: #fff;
    }

    .page-footer {
      text-align: center;
      margin-top: 40px;
      padding-top: 15px;
      border-top: 1px solid #eee;
      font-size: 11px;
      color: #999;
    }

    @media print {
      body { padding: 10px 20px; }
      .no-print { display: none !important; }
      @page { margin: 15mm; }
    }
  </style>
</head>
<body>
  <div class="header">
    ${logoHtml}
    <div class="store-name">${sName}</div>
    ${businessParts.length > 0 ? `<div class="store-info">${businessParts.join(' | ')}</div>` : ''}
    ${taxParts.length > 0 ? `<div class="store-info">${taxParts.join(' | ')}</div>` : ''}
  </div>

  <div class="invoice-title">
    <h2>${text.invoice || 'Invoice'} #${order.order_id || ''}</h2>
  </div>
  <div class="invoice-date">${orderDate}</div>

  <div class="customer-info">
    <div class="section-title">${isRtl ? 'معلومات العميل' : (language === 'fr' ? 'Informations client' : 'Customer Information')}</div>
    <p><span class="label">${isRtl ? 'الاسم' : (language === 'fr' ? 'Nom' : 'Name')}:</span> ${customerName}</p>
    <p><span class="label">${isRtl ? 'الهاتف' : (language === 'fr' ? 'Téléphone' : 'Phone')}:</span> ${customerPhone}</p>
    <p><span class="label">${isRtl ? 'الولاية' : 'Wilaya'}:</span> ${wilaya}</p>
    <p><span class="label">${isRtl ? 'العنوان' : (language === 'fr' ? 'Adresse' : 'Address')}:</span> ${address}</p>
  </div>

  <table>
    <thead>
      <tr>
        <th>${isRtl ? 'المنتج' : (language === 'fr' ? 'Produit' : 'Product')}</th>
        <th>${isRtl ? 'الكمية' : (language === 'fr' ? 'Qté' : 'Qty')}</th>
        <th>${isRtl ? 'السعر' : (language === 'fr' ? 'Prix' : 'Price')}</th>
        <th>${isRtl ? 'المجموع' : (language === 'fr' ? 'Sous-total' : 'Subtotal')}</th>
      </tr>
    </thead>
    <tbody>
      ${itemsContent}
    </tbody>
  </table>

  <table class="totals-table">
    <tbody>
      <tr>
        <td class="label-cell">${isRtl ? 'المجموع الفرعي' : (language === 'fr' ? 'Sous-total' : 'Subtotal')}</td>
        <td style="text-align:${isRtl ? 'left' : 'right'};font-weight:600;">${priceStr(subtotalVal)}</td>
      </tr>
      ${discountAmount > 0 ? `<tr class="discount-row">
        <td class="label-cell">${isRtl ? 'الخصم' : (language === 'fr' ? 'Remise' : 'Discount')}${discountPercentage > 0 ? ` (${discountPercentage}%)` : ''}</td>
        <td style="text-align:${isRtl ? 'left' : 'right'};font-weight:600;">-${priceStr(discountAmount)}</td>
      </tr>` : ''}
      <tr>
        <td class="label-cell">${isRtl ? 'التوصيل' : (language === 'fr' ? 'Livraison' : 'Shipping')}</td>
        <td style="text-align:${isRtl ? 'left' : 'right'};font-weight:600;">${shippingCost > 0 ? priceStr(shippingCost) : (isRtl ? 'مجاني' : (language === 'fr' ? 'Gratuit' : 'Free'))}</td>
      </tr>
      <tr class="total-row">
        <td class="label-cell">${isRtl ? 'المجموع الكلي' : 'Total'}</td>
        <td style="text-align:${isRtl ? 'left' : 'right'};font-weight:700;font-size:16px;">${priceStr(order.total)}</td>
      </tr>
    </tbody>
  </table>

  <div class="footer-info">
    <p><span class="label">${isRtl ? 'الدفع' : (language === 'fr' ? 'Paiement' : 'Payment')}:</span> ${isRtl ? 'الدفع عند الاستلام' : (language === 'fr' ? 'Paiement à la livraison' : 'Cash on Delivery')}</p>
    <p><span class="label">${isRtl ? 'الحالة' : (language === 'fr' ? 'Statut' : 'Status')}:</span>
      <span class="status-badge" style="background-color:${statusColors[order.status] || '#666'}">
        ${statusLabels[order.status] || order.status}
      </span>
    </p>
  </div>

  <div class="page-footer">
    ${sName}${sWebsite ? ' - ' + sWebsite : ''}
  </div>

  <script>
    window.onload = function() {
      window.print();
    };
  </script>
</body>
</html>`;

  const printWindow = window.open('', '_blank');
  if (printWindow) {
    printWindow.document.write(html);
    printWindow.document.close();
  }
}
