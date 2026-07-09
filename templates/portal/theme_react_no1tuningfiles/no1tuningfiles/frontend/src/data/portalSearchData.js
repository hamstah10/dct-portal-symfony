import { getInvoicesData } from '../lib/invoicesBootstrap';
import { getOrdersData } from '../lib/ordersBootstrap';
import { portalRoutes } from '../lib/portalRoutes';

const labels = {
  de: {
    groups: {
      action: 'Aktionen',
      navigation: 'Navigation',
      order: 'Auftraege',
      parked_order: 'Geparkte Auftraege',
      customer: 'Kunden',
      vehicle: 'Fahrzeuge',
      invoice: 'Rechnungen',
      credit_transaction: 'Credits / Zahlungen',
    },
    actions: {
      newOrder: 'Neuen Auftrag starten',
      buyCredits: 'Credits kaufen',
      continueParked: 'Geparkten Auftrag fortsetzen',
      openLatestInvoice: 'Neueste Rechnung oeffnen',
      openProfile: 'Profil oeffnen',
    },
    navigation: {
      dashboard: 'Dashboard',
      newOrder: 'Neuer Auftrag',
      orders: 'Auftraege',
      parkedOrders: 'Geparkte Auftraege',
      credits: 'Credits',
      invoices: 'Rechnungen',
      profile: 'Profil',
      customers: 'Kundenprofil',
      vehicles: 'Fahrzeuge',
      tickets: 'Tickets',
      dtcCodes: 'DTC Codes',
      statistics: 'Statistiken',
    },
    badges: {
      action: 'Schnellaktion',
      parked: 'Geparkt',
      paid: 'Bezahlt',
      pending: 'Ausstehend',
      cancelled: 'Storniert',
      completed: 'Abgeschlossen',
      processing: 'In Bearbeitung',
      booked: 'Gebucht',
    },
  },
  en: {
    groups: {
      action: 'Actions',
      navigation: 'Navigation',
      order: 'Orders',
      parked_order: 'Parked Orders',
      customer: 'Customers',
      vehicle: 'Vehicles',
      invoice: 'Invoices',
      credit_transaction: 'Credits / Payments',
    },
    actions: {
      newOrder: 'Start new order',
      buyCredits: 'Buy credits',
      continueParked: 'Resume parked order',
      openLatestInvoice: 'Open latest invoice',
      openProfile: 'Open profile',
    },
    navigation: {
      dashboard: 'Dashboard',
      newOrder: 'New order',
      orders: 'Orders',
      parkedOrders: 'Parked orders',
      credits: 'Credits',
      invoices: 'Invoices',
      profile: 'Profile',
      customers: 'Customer profile',
      vehicles: 'Vehicles',
      tickets: 'Tickets',
      dtcCodes: 'DTC Codes',
      statistics: 'Statistics',
    },
    badges: {
      action: 'Quick action',
      parked: 'Parked',
      paid: 'Paid',
      pending: 'Pending',
      cancelled: 'Cancelled',
      completed: 'Completed',
      processing: 'Processing',
      booked: 'Booked',
    },
  },
};

const orderStatusLabels = {
  completed: { de: 'Abgeschlossen', en: 'Completed' },
  processing: { de: 'In Bearbeitung', en: 'Processing' },
  cancelled: { de: 'Storniert', en: 'Cancelled' },
  parked: { de: 'Geparkt', en: 'Parked' },
  pending: { de: 'Wartend', en: 'Pending' },
};

const invoiceStatusLabels = {
  paid: { de: 'Bezahlt', en: 'Paid' },
  pending: { de: 'Ausstehend', en: 'Pending' },
  cancelled: { de: 'Storniert', en: 'Cancelled' },
};

const safeString = (value, fallback = '') => {
  return typeof value === 'string' ? value : fallback;
};

const translateStatus = (map, status, language) => map[status]?.[language] || map[status]?.en || status;

const createSearchUrl = (path, query) => `${path}?search=${encodeURIComponent(query)}`;

const getLabels = (language) => labels[language] || labels.en;

const readBootstrap = () => {
  if (typeof window === 'undefined') {
    return {};
  }

  return window.portalReactBootstrap && typeof window.portalReactBootstrap === 'object'
    ? window.portalReactBootstrap
    : {};
};

const readProfile = () => {
  const profile = readBootstrap().profile;
  return profile && typeof profile === 'object' ? profile : {};
};

const readDashboard = () => {
  const dashboard = readBootstrap().dashboard;
  return dashboard && typeof dashboard === 'object' ? dashboard : {};
};

const readParkedOrders = () => {
  const bootstrap = readBootstrap();
  const parkedOrders = bootstrap?.orders?.parkedOrders;

  if (Array.isArray(parkedOrders) && parkedOrders.length > 0) {
    return parkedOrders;
  }

  return Array.isArray(bootstrap?.dashboard?.parkedOrders) ? bootstrap.dashboard.parkedOrders : [];
};

const buildCustomerResults = (language) => {
  const profile = readProfile();
  const dashboard = readDashboard();
  const invoices = getInvoicesData();
  const customerMap = new Map();

  const pushCustomer = (entry) => {
    const id = safeString(entry.id, `${entry.name || entry.email || 'customer'}`);
    if (customerMap.has(id)) {
      return;
    }

    customerMap.set(id, {
      id,
      type: 'customer',
      title: entry.name || entry.company || entry.email || (language === 'de' ? 'Kundenprofil' : 'Customer profile'),
      subtitle: [entry.company, entry.email, entry.city].filter(Boolean).join(' · '),
      badge: entry.badge || null,
      url: portalRoutes.profile,
      keywords: [entry.name, entry.company, entry.email, entry.phone, entry.city, entry.id].filter(Boolean),
    });
  };

  pushCustomer({
    id: 'profile-customer',
    name: [profile.vorname, profile.nachname].filter(Boolean).join(' ').trim() || dashboard.customerName || '',
    company: profile.firmenname || '',
    email: profile.email || '',
    phone: profile.telefon || '',
    city: profile.stadt || '',
    badge: language === 'de' ? 'Profil' : 'Profile',
  });

  invoices.forEach((invoice, index) => {
    const customer = invoice.customer && typeof invoice.customer === 'object' ? invoice.customer : null;
    if (!customer) {
      return;
    }

    pushCustomer({
      id: customer.id || `invoice-customer-${index + 1}`,
      name: customer.name || '',
      company: customer.company || '',
      email: customer.email || '',
      phone: customer.phone || '',
      city: customer.city || '',
      badge: invoice.id,
    });
  });

  return Array.from(customerMap.values()).filter((item) => item.title !== '');
};

const buildVehicleResults = (orders, parkedOrders, language) => {
  const orderVehicles = orders.map((order) => ({
    id: `vehicle-order-${order.id}`,
    type: 'vehicle',
    title: [order.vehicle.manufacturer, order.vehicle.series, order.vehicle.model].filter(Boolean).join(' '),
    subtitle: [order.vehicle.engine, order.vehicle.ecu, order.id].filter(Boolean).join(' · '),
    badge: order.vehicle.year ? String(order.vehicle.year) : null,
    url: `/orders/${encodeURIComponent(order.id)}`,
    keywords: [
      order.vehicle.manufacturer,
      order.vehicle.series,
      order.vehicle.model,
      order.vehicle.engine,
      order.vehicle.ecu,
      order.vehicle.power,
      order.id,
      ...(order.tuning.options || []),
    ].filter(Boolean),
  }));

  const parkedVehicles = parkedOrders.map((order, index) => ({
    id: `vehicle-parked-${order.id || index + 1}`,
    type: 'vehicle',
    title: safeString(order.vehicleLabel, language === 'de' ? 'Geparktes Fahrzeug' : 'Parked vehicle'),
    subtitle: [order.stageLabel, order.orderNumber].filter(Boolean).join(' · '),
    badge: language === 'de' ? 'Geparkt' : 'Parked',
    url: `/auftrag?parkedOrderId=${encodeURIComponent(order.id)}`,
    keywords: [order.vehicleLabel, order.stageLabel, order.orderNumber].filter(Boolean),
  }));

  return [...orderVehicles, ...parkedVehicles].filter((item) => item.title !== '');
};

export const getSearchLabels = (language) => getLabels(language);

export const buildGlobalSearchIndex = (language = 'de') => {
  const copy = getLabels(language);
  const orders = getOrdersData();
  const invoices = getInvoicesData();
  const parkedOrders = readParkedOrders();
  const latestInvoice = invoices[0] || null;

  const navigation = [
    {
      id: 'nav-dashboard',
      type: 'navigation',
      title: copy.navigation.dashboard,
      subtitle: language === 'de' ? 'Startseite und Portal-Ueberblick' : 'Start page and portal overview',
      badge: null,
      url: portalRoutes.dashboard,
      keywords: ['home', 'overview', 'portal'],
    },
    {
      id: 'nav-new-order',
      type: 'navigation',
      title: copy.navigation.newOrder,
      subtitle: language === 'de' ? 'Upload und Auftragserstellung' : 'Upload and order creation',
      badge: null,
      url: portalRoutes.order,
      keywords: ['upload', 'wizard', 'file'],
    },
    {
      id: 'nav-orders',
      type: 'navigation',
      title: copy.navigation.orders,
      subtitle: language === 'de' ? 'Alle Portalauftraege' : 'All portal orders',
      badge: null,
      url: portalRoutes.orders,
      keywords: ['history', 'status', 'files'],
    },
    {
      id: 'nav-parked-orders',
      type: 'navigation',
      title: copy.navigation.parkedOrders,
      subtitle: language === 'de' ? 'Gespeicherte Auftraege fortsetzen' : 'Resume saved orders',
      badge: copy.badges.parked,
      url: parkedOrders[0] ? `/auftrag?parkedOrderId=${encodeURIComponent(parkedOrders[0].id)}` : portalRoutes.order,
      keywords: ['draft', 'resume', 'saved'],
    },
    {
      id: 'nav-credits',
      type: 'navigation',
      title: copy.navigation.credits,
      subtitle: language === 'de' ? 'Creditkauf und Guthaben' : 'Credit purchase and balance',
      badge: null,
      url: portalRoutes.credits,
      keywords: ['buy', 'balance', 'payment'],
    },
    {
      id: 'nav-invoices',
      type: 'navigation',
      title: copy.navigation.invoices,
      subtitle: language === 'de' ? 'Rechnungen und Zahlungsbelege' : 'Invoices and payment records',
      badge: null,
      url: portalRoutes.invoices,
      keywords: ['billing', 'pdf', 'payment'],
    },
    {
      id: 'nav-profile',
      type: 'navigation',
      title: copy.navigation.profile,
      subtitle: language === 'de' ? 'Konto- und Firmendaten' : 'Account and company details',
      badge: null,
      url: portalRoutes.profile,
      keywords: ['account', 'settings', 'email'],
    },
    {
      id: 'nav-vehicles',
      type: 'navigation',
      title: copy.navigation.vehicles,
      subtitle: language === 'de' ? 'Fahrzeugdatenbank und Suche' : 'Vehicle database and lookup',
      badge: null,
      url: portalRoutes.vehicles,
      keywords: ['ecu', 'car', 'model'],
    },
    {
      id: 'nav-tickets',
      type: 'navigation',
      title: copy.navigation.tickets,
      subtitle: language === 'de' ? 'Supportanfragen und Rueckfragen' : 'Support requests and follow-ups',
      badge: null,
      url: portalRoutes.tickets,
      keywords: ['support', 'help', 'messages'],
    },
    {
      id: 'nav-dtc-codes',
      type: 'navigation',
      title: copy.navigation.dtcCodes,
      subtitle: language === 'de' ? 'DTC-Code-Verzeichnis aus dem Superadmin' : 'DTC code directory from superadmin',
      badge: null,
      url: portalRoutes.dtcCodes,
      keywords: ['dtc', 'fault code', 'error code', 'diagnostic'],
    },
    {
      id: 'nav-statistics',
      type: 'navigation',
      title: copy.navigation.statistics,
      subtitle: language === 'de' ? 'Trends und Kennzahlen' : 'Trends and metrics',
      badge: null,
      url: portalRoutes.statistics,
      keywords: ['analytics', 'reporting'],
    },
  ];

  const actions = [
    {
      id: 'action-new-order',
      type: 'action',
      title: copy.actions.newOrder,
      subtitle: language === 'de' ? 'Direkt in den Auftragsfluss wechseln' : 'Jump straight into the order flow',
      badge: copy.badges.action,
      url: portalRoutes.order,
      keywords: ['upload', 'new', 'wizard'],
    },
    {
      id: 'action-buy-credits',
      type: 'action',
      title: copy.actions.buyCredits,
      subtitle: language === 'de' ? 'Creditpakete und Vorkasse' : 'Credit packages and prepayment',
      badge: copy.badges.action,
      url: portalRoutes.credits,
      keywords: ['paypal', 'package', 'balance'],
    },
    {
      id: 'action-open-profile',
      type: 'action',
      title: copy.actions.openProfile,
      subtitle: language === 'de' ? 'Firmendaten und API-Schluessel bearbeiten' : 'Edit company details and API key',
      badge: copy.badges.action,
      url: portalRoutes.profile,
      keywords: ['profile', 'company', 'settings'],
    },
    {
      id: 'action-open-latest-invoice',
      type: 'action',
      title: copy.actions.openLatestInvoice,
      subtitle: latestInvoice ? latestInvoice.id : (language === 'de' ? 'Keine Rechnung vorhanden' : 'No invoice available'),
      badge: latestInvoice ? translateStatus(invoiceStatusLabels, latestInvoice.status, language) : copy.badges.action,
      url: latestInvoice ? `/invoices/${encodeURIComponent(latestInvoice.id)}` : portalRoutes.invoices,
      keywords: latestInvoice ? [latestInvoice.id, latestInvoice.paymentMethod] : ['invoice'],
    },
    {
      id: 'action-continue-parked',
      type: 'action',
      title: copy.actions.continueParked,
      subtitle: parkedOrders[0]
        ? [parkedOrders[0].orderNumber, parkedOrders[0].vehicleLabel].filter(Boolean).join(' · ')
        : (language === 'de' ? 'Kein geparkter Auftrag' : 'No parked order'),
      badge: copy.badges.parked,
      url: parkedOrders[0] ? `/auftrag?parkedOrderId=${encodeURIComponent(parkedOrders[0].id)}` : portalRoutes.order,
      keywords: parkedOrders[0] ? [parkedOrders[0].orderNumber, parkedOrders[0].vehicleLabel, parkedOrders[0].stageLabel] : ['parked'],
    },
  ];

  const orderResults = orders.map((order) => ({
    id: order.id,
    type: 'order',
    title: order.id,
    subtitle: [order.vehicle.manufacturer, order.vehicle.series, order.vehicle.model, order.tuning.type].filter(Boolean).join(' · '),
    badge: translateStatus(orderStatusLabels, order.status, language),
    url: `/orders/${encodeURIComponent(order.id)}`,
    keywords: [
      order.id,
      order.vehicle.manufacturer,
      order.vehicle.series,
      order.vehicle.model,
      order.vehicle.engine,
      order.vehicle.ecu,
      order.vehicle.power,
      order.tuning.type,
      ...(order.tuning.options || []),
      order.files.original,
      order.files.modified,
      order.notes,
      order.status,
    ].filter(Boolean),
  }));

  const parkedOrderResults = parkedOrders.map((order, index) => ({
    id: `parked-${order.id || index + 1}`,
    type: 'parked_order',
    title: [order.orderNumber, order.vehicleLabel].filter(Boolean).join(' · '),
    subtitle: [order.stageLabel, order.updatedAt].filter(Boolean).join(' · '),
    badge: copy.badges.parked,
    url: `/auftrag?parkedOrderId=${encodeURIComponent(order.id)}`,
    keywords: [order.orderNumber, order.vehicleLabel, order.stageLabel, order.updatedAt].filter(Boolean),
  }));

  const invoiceResults = invoices.map((invoice) => ({
    id: invoice.id,
    type: 'invoice',
    title: invoice.id,
    subtitle: [
      invoice.customer?.name || invoice.customer?.company || '',
      `${invoice.total.toFixed(2)} EUR`,
      invoice.paymentMethod,
    ].filter(Boolean).join(' · '),
    badge: translateStatus(invoiceStatusLabels, invoice.status, language),
    url: `/invoices/${encodeURIComponent(invoice.id)}`,
    keywords: [
      invoice.id,
      invoice.paymentMethod,
      String(invoice.credits),
      invoice.customer?.name,
      invoice.customer?.company,
      invoice.customer?.email,
      invoice.total.toFixed(2),
      invoice.date,
    ].filter(Boolean),
  }));

  const creditResults = invoices.map((invoice) => ({
    id: `credit-${invoice.id}`,
    type: 'credit_transaction',
    title: `${invoice.credits} Credits · ${invoice.id}`,
    subtitle: [
      invoice.paymentMethod,
      `${invoice.total.toFixed(2)} EUR`,
      invoice.customer?.name || invoice.customer?.company || '',
    ].filter(Boolean).join(' · '),
    badge: invoice.status === 'paid' ? copy.badges.booked : translateStatus(invoiceStatusLabels, invoice.status, language),
    url: `/invoices/${encodeURIComponent(invoice.id)}`,
    keywords: [
      invoice.id,
      invoice.paymentMethod,
      String(invoice.credits),
      invoice.customer?.name,
      invoice.customer?.company,
      invoice.customer?.email,
      invoice.total.toFixed(2),
      invoice.status,
    ].filter(Boolean),
  }));

  return [
    ...actions,
    ...navigation,
    ...orderResults,
    ...parkedOrderResults,
    ...buildCustomerResults(language),
    ...buildVehicleResults(orders, parkedOrders, language),
    ...invoiceResults,
    ...creditResults,
  ];
};
