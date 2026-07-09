const ensureString = (value, fallback = '') => {
  return typeof value === 'string' ? value : fallback;
};

const ensureNumber = (value, fallback = 0) => {
  return Number.isFinite(value) ? value : fallback;
};

const normalizeInvoice = (invoice, index) => {
  const id = ensureString(invoice?.id, `INV-${index + 1}`);
  const statusValue = ensureString(invoice?.status, 'pending').toLowerCase();
  const status = ['paid', 'pending', 'cancelled'].includes(statusValue) ? statusValue : 'pending';

  return {
    id,
    date: ensureString(invoice?.date, null),
    credits: ensureNumber(invoice?.credits, 0),
    amount: ensureNumber(invoice?.amount, 0),
    tax: ensureNumber(invoice?.tax, 0),
    total: ensureNumber(invoice?.total, 0),
    status,
    paymentMethod: ensureString(invoice?.paymentMethod, ''),
    downloadUrl: ensureString(invoice?.downloadUrl, ''),
    issuer: invoice?.issuer && typeof invoice.issuer === 'object' ? invoice.issuer : null,
    customer: invoice?.customer && typeof invoice.customer === 'object' ? invoice.customer : null,
  };
};

export const getInvoicesData = () => {
  if (typeof window === 'undefined') {
    return [];
  }

  const items = window?.portalReactBootstrap?.invoices?.items;
  if (!Array.isArray(items)) {
    return [];
  }

  return items.map((invoice, index) => normalizeInvoice(invoice, index));
};
