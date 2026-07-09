import { useEffect, useMemo, useRef, useState } from 'react';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import {
  CurrencyCircleDollar, ShoppingCart, Check, Lightning, Star,
  Fire, Tag, ArrowRight, CreditCard, Receipt, Coins,
  Invoice, PaperPlaneTilt, CheckCircle, Bank, X as XIcon,
} from '@phosphor-icons/react';
import { cn } from '../lib/utils';
import { getBootstrapCredits } from '../lib/sessionBootstrap';

const fallbackCreditPackages = [
  { id: 1, credits: 10, price: 12, priceInkl: 14.28, features: ['10 Tuning Credits'], badge: null, oldPrice: null, perFile: null },
  { id: 2, credits: 50, price: 58, priceInkl: 69.02, features: ['50 Tuning Credits'], badge: null, oldPrice: null, perFile: null },
  { id: 3, credits: 100, price: 115, priceInkl: 136.85, features: ['100 Tuning Credits', 'BESTSELLER %'], badge: 'bestseller', oldPrice: null, perFile: null },
  { id: 4, credits: 200, price: 225, priceInkl: 267.75, features: ['200 Tuning Credits'], badge: null, oldPrice: null, perFile: null },
  { id: 5, credits: 500, price: 550, priceInkl: 654.50, features: ['500 Tuning Credits'], badge: null, oldPrice: null, perFile: null },
  { id: 6, credits: 1000, price: 950, priceInkl: 1249.50, features: ['1000 Tuning Credits', 'AM BELIEBTESTEN !!'], badge: 'popular', oldPrice: 1050, perFile: null },
  { id: 7, credits: 2000, price: 2000, priceInkl: 2380, features: ['2000 Tuning Credits', '100\u20AC / File'], badge: null, oldPrice: null, perFile: 100 },
  { id: 8, credits: 3000, price: 2850, priceInkl: 3391.50, features: ['3000 Tuning Credits', '95\u20AC / File'], badge: null, oldPrice: null, perFile: 95 },
  { id: 9, credits: 4000, price: 3600, priceInkl: 4284, features: ['4000 Tuning Credits', '90\u20AC / File'], badge: null, oldPrice: null, perFile: 90 },
  { id: 10, credits: 5000, price: 4250, priceInkl: 5057.50, features: ['5000 Tuning Credits', '85\u20AC / File'], badge: 'top', oldPrice: null, perFile: 85 },
];

const fmt = (n) => n.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const readCreditsBootstrap = () => {
  if (typeof window === 'undefined') {
    return { packages: [], transactions: [], paypal: {}, prepayment: {}, stripe: {} };
  }

  const bootstrap = window?.portalReactBootstrap?.creditsPage;
  const packages = Array.isArray(bootstrap?.packages) ? bootstrap.packages : [];
  const transactions = Array.isArray(bootstrap?.transactions) ? bootstrap.transactions : [];
  const paypal = bootstrap?.paypal && typeof bootstrap.paypal === 'object' ? bootstrap.paypal : {};
  const prepayment = bootstrap?.prepayment && typeof bootstrap.prepayment === 'object' ? bootstrap.prepayment : {};
  const stripe = bootstrap?.stripe && typeof bootstrap.stripe === 'object' ? bootstrap.stripe : {};

  return { packages, transactions, paypal, prepayment, stripe };
};

const hasCreditsBootstrapData = (bootstrap) => {
  return Array.isArray(bootstrap?.packages) && Array.isArray(bootstrap?.transactions);
};

const normalizePackages = (items) => {
  if (!Array.isArray(items)) {
    return [];
  }

  return items
    .map((pkg) => {
      const id = Number(pkg?.id || 0);
      const credits = Number(pkg?.credits || 0);
      const price = Number(pkg?.priceNet || 0);
      const priceInkl = Number(pkg?.priceGross || 0);
      if (!Number.isFinite(id) || !Number.isFinite(credits) || credits <= 0) {
        return null;
      }

      return {
        id,
        credits,
        price: Number.isFinite(price) ? price : 0,
        priceInkl: Number.isFinite(priceInkl) ? priceInkl : 0,
        features: [String(pkg?.title || `${credits} Tuning Credits`)],
        badge: null,
        oldPrice: null,
        perFile: Number.isFinite(Number(pkg?.pricePerFile)) ? Number(pkg?.pricePerFile) : null,
      };
    })
    .filter(Boolean)
    .sort((a, b) => a.credits - b.credits);
};

const normalizeTransactions = (items) => {
  if (!Array.isArray(items)) {
    return [];
  }

  return items.map((item) => ({
    id: Number(item?.id || 0),
    amount: Number(item?.amount || 0),
    transactionType: String(item?.transactionType || ''),
    description: String(item?.description || ''),
    paymentMethod: String(item?.paymentMethod || ''),
    priceNet: item?.priceNet === null || item?.priceNet === undefined ? null : Number(item.priceNet),
    priceGross: item?.priceGross === null || item?.priceGross === undefined ? null : Number(item.priceGross),
    balanceAfter: Number(item?.balanceAfter || 0),
    createdAt: String(item?.createdAt || ''),
  }));
};

const t_data = {
  de: {
    title: 'Credits kaufen',
    subtitle: 'Tuning Credits erwerben und sofort nutzen',
    yourBalance: 'Dein Guthaben',
    credits: 'Credits',
    features: 'Eigenschaften',
    buyNow: 'Jetzt kaufen',
    inclVat: 'inkl. MwSt.',
    selectedPackage: 'Ausgewähltes Paket',
    selectPackage: 'Paket auswählen',
    selectHint: 'Bitte ein Credit-Paket auswählen',
    price: 'Preis',
    vat: 'inkl. MwSt.',
    payWith: 'Bezahlen mit',
    perFile: 'pro File',
    bestseller: 'BESTSELLER',
    popular: 'AM BELIEBTESTEN',
    topDeal: 'TOP DEAL',
    infoTitle: 'Deine Vorteile',
    info1: 'Credits verfallen nie',
    info2: 'Sichere PayPal-Zahlung',
    info3: 'Sofortige Gutschrift',
    info4: 'Mengenrabatte bei gro\u00DFen Paketen',
    info5: 'Rechnungen als PDF',
    // Vorkasse
    vorkasseTitle: 'Rechnung auf Vorkasse',
    vorkasseDesc: 'Individuelle Rechnung mit gew\u00FCnschtem Betrag anfordern',
    vorkasseCredits: 'Gew\u00FCnschte Credits',
    vorkasseCalc: 'Berechneter Preis',
    vorkasseNetto: 'Netto',
    vorkasseBrutto: 'Brutto (inkl. 19% MwSt.)',
    vorkasseBtn: 'Rechnung anfordern',
    vorkasseNote: 'Die Rechnung wird per E-Mail zugesendet. Credits werden nach Zahlungseingang gutgeschrieben.',
    vorkasseMin: 'Mindestens 10 Credits',
    vorkasseSent: 'Anfrage gesendet!',
    vorkasseSentMsg: 'Deine Vorkasse-Rechnung wird erstellt und per E-Mail zugesendet.',
    vorkasseClose: 'Schlie\u00DFen',
    vorkasseNew: 'Neue Anfrage',
    transactionsTitle: 'Buchungen',
    transactionsSubtitle: 'Alle Credit-Buchungen deines Kontos',
    noTransactions: 'Noch keine Buchungen vorhanden.',
    bookingDate: 'Datum',
    bookingType: 'Typ',
    bookingAmount: 'Credits',
    bookingBalance: 'Saldo',
    bookingPayment: 'Zahlung',
    bookingDetails: 'Details',
    typeCustomerTopup: 'Aufladung',
    typeAdminTopup: 'Admin-Aufladung',
    typeManualAdjustment: 'Manuelle Korrektur',
    typeUsage: 'Verbrauch',
    typeReversal: 'Storno',
    typeUnknown: 'Buchung',
    paymentUnknown: '-',
  },
  en: {
    title: 'Buy Credits',
    subtitle: 'Purchase tuning credits and use them instantly',
    yourBalance: 'Your Balance',
    credits: 'Credits',
    features: 'Features',
    buyNow: 'Buy Now',
    inclVat: 'incl. VAT',
    selectedPackage: 'Selected Package',
    selectPackage: 'Select Package',
    selectHint: 'Please select a credit package',
    price: 'Price',
    vat: 'incl. VAT',
    payWith: 'Pay with',
    perFile: 'per File',
    bestseller: 'BESTSELLER',
    popular: 'MOST POPULAR',
    topDeal: 'TOP DEAL',
    infoTitle: 'Your Benefits',
    info1: 'Credits never expire',
    info2: 'Secure PayPal payment',
    info3: 'Instant delivery',
    info4: 'Bulk discounts on large packages',
    info5: 'PDF invoices',
    // Vorkasse
    vorkasseTitle: 'Prepayment Invoice',
    vorkasseDesc: 'Request a custom invoice with your desired amount',
    vorkasseCredits: 'Desired Credits',
    vorkasseCalc: 'Calculated Price',
    vorkasseNetto: 'Net',
    vorkasseBrutto: 'Gross (incl. 19% VAT)',
    vorkasseBtn: 'Request Invoice',
    vorkasseNote: 'The invoice will be sent via email. Credits are added after payment is received.',
    vorkasseMin: 'Minimum 10 credits',
    vorkasseSent: 'Request sent!',
    vorkasseSentMsg: 'Your prepayment invoice is being created and will be sent via email.',
    vorkasseClose: 'Close',
    vorkasseNew: 'New Request',
    transactionsTitle: 'Transactions',
    transactionsSubtitle: 'All credit bookings for your account',
    noTransactions: 'No transactions yet.',
    bookingDate: 'Date',
    bookingType: 'Type',
    bookingAmount: 'Credits',
    bookingBalance: 'Balance',
    bookingPayment: 'Payment',
    bookingDetails: 'Details',
    typeCustomerTopup: 'Top-up',
    typeAdminTopup: 'Admin top-up',
    typeManualAdjustment: 'Manual adjustment',
    typeUsage: 'Usage',
    typeReversal: 'Reversal',
    typeUnknown: 'Booking',
    paymentUnknown: '-',
  },
};

export default function Credits() {
  const { language } = useLanguage();
  const t = (k) => t_data[language]?.[k] || k;
  const [selectedId, setSelectedId] = useState(null);
  const [vorkasseCredits, setVorkasseCredits] = useState('');
  const [vorkasseSent, setVorkasseSent] = useState(false);
  const [transactionsPage, setTransactionsPage] = useState(1);
  const [isPaypalOpen, setIsPaypalOpen] = useState(false);
  const [paypalBusy, setPaypalBusy] = useState(false);
  const [purchaseError, setPurchaseError] = useState('');
  const [purchaseSuccess, setPurchaseSuccess] = useState('');
  const [prepaymentBusy, setPrepaymentBusy] = useState(false);
  const [prepaymentError, setPrepaymentError] = useState('');
  const [prepaymentSuccess, setPrepaymentSuccess] = useState('');
  const transactionsPerPage = 25;
  const [currentBalance, setCurrentBalance] = useState(() => getBootstrapCredits());
  const [paypalSdkReady, setPaypalSdkReady] = useState(false);
  const paypalButtonsRef = useRef(null);
  const [creditsBootstrap, setCreditsBootstrap] = useState(() => readCreditsBootstrap());
  const { packages: rawPackages, transactions: rawTransactions, paypal: rawPaypal, prepayment: rawPrepayment, stripe: rawStripe } = creditsBootstrap;
  const creditPackages = useMemo(() => {
    const normalized = normalizePackages(rawPackages);
    return normalized.length > 0 ? normalized : fallbackCreditPackages;
  }, [rawPackages]);
  const [transactions, setTransactions] = useState(() => normalizeTransactions(rawTransactions));
  const paypalConfig = useMemo(() => ({
    configured: Boolean(rawPaypal?.configured),
    clientId: String(rawPaypal?.clientId || ''),
    csrfToken: String(rawPaypal?.csrfToken || ''),
  }), [rawPaypal]);
  const prepaymentConfig = useMemo(() => ({
    csrfToken: String(rawPrepayment?.csrfToken || ''),
  }), [rawPrepayment]);
  const stripeConfig = useMemo(() => ({
    configured: Boolean(rawStripe?.configured),
    checkoutUrlTemplate: String(rawStripe?.checkoutUrlTemplate || ''),
  }), [rawStripe]);
  const selected = creditPackages.find(p => p.id === selectedId);
  const stripeCheckoutUrl = useMemo(() => {
    if (!stripeConfig.configured || !stripeConfig.checkoutUrlTemplate || !selected) {
      return null;
    }
    return stripeConfig.checkoutUrlTemplate.replace('__PACKAGE_ID__', String(selected.id));
  }, [stripeConfig, selected]);
  const totalTransactionPages = Math.max(1, Math.ceil(transactions.length / transactionsPerPage));
  const safeTransactionsPage = Math.min(transactionsPage, totalTransactionPages);
  const paginatedTransactions = useMemo(() => {
    const start = (safeTransactionsPage - 1) * transactionsPerPage;
    return transactions.slice(start, start + transactionsPerPage);
  }, [safeTransactionsPage, transactions]);

  useEffect(() => {
    setTransactions(normalizeTransactions(rawTransactions));
  }, [rawTransactions]);

  useEffect(() => {
    if (hasCreditsBootstrapData(creditsBootstrap)) {
      return undefined;
    }

    const controller = new AbortController();

    const loadCreditsBootstrap = async () => {
      try {
        const response = await fetch('/credits/bootstrap', {
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
          },
          signal: controller.signal,
        });

        if (!response.ok) {
          return;
        }

        const payload = await response.json();
        setCreditsBootstrap({
          packages: Array.isArray(payload?.packages) ? payload.packages : [],
          transactions: Array.isArray(payload?.transactions) ? payload.transactions : [],
          paypal: payload?.paypal && typeof payload.paypal === 'object' ? payload.paypal : {},
          prepayment: payload?.prepayment && typeof payload.prepayment === 'object' ? payload.prepayment : {},
          stripe: payload?.stripe && typeof payload.stripe === 'object' ? payload.stripe : {},
        });
      } catch (_error) {
        // keep current fallback state
      }
    };

    loadCreditsBootstrap();

    return () => {
      controller.abort();
    };
  }, [creditsBootstrap]);

  // Calculate Vorkasse price based on package tiers
  const calcVorkassePrice = (credits) => {
    if (!credits || credits < 10) return { netto: 0, brutto: 0 };
    // Find best matching tier for price-per-credit
    const sorted = [...creditPackages].sort((a, b) => b.credits - a.credits);
    const tier = sorted.find(p => credits >= p.credits) || creditPackages[0];
    const pricePerCredit = tier.price / tier.credits;
    const netto = Math.round(credits * pricePerCredit * 100) / 100;
    const brutto = Math.round(netto * 1.19 * 100) / 100;
    return { netto, brutto };
  };

  const vorkasseNum = parseInt(vorkasseCredits) || 0;
  const vorkassePrice = calcVorkassePrice(vorkasseNum);

  const getBadge = (pkg) => {
    if (pkg.badge === 'bestseller') return { label: t('bestseller'), cls: 'bg-primary text-white border-primary' };
    if (pkg.badge === 'popular') return { label: t('popular'), cls: 'bg-orange-500 text-white border-orange-500' };
    if (pkg.badge === 'top') return { label: t('topDeal'), cls: 'bg-green-500 text-white border-green-500' };
    return null;
  };

  const getTransactionTypeLabel = (value) => {
    const type = String(value || '').toLowerCase();
    if (type === 'customer_topup') return t('typeCustomerTopup');
    if (type === 'admin_topup') return t('typeAdminTopup');
    if (type === 'manual_adjustment') return t('typeManualAdjustment');
    if (type === 'usage') return t('typeUsage');
    if (type === 'reversal') return t('typeReversal');
    return t('typeUnknown');
  };

  const formatBookingDate = (value) => {
    const parsed = new Date(String(value || ''));
    if (Number.isNaN(parsed.getTime())) {
      return '-';
    }

    return new Intl.DateTimeFormat(language === 'de' ? 'de-DE' : 'en-US', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(parsed);
  };

  const postPaypalJson = async (url, payload) => {
    const response = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const json = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(String(json?.message || 'PayPal-Anfrage fehlgeschlagen.'));
    }

    return json;
  };

  useEffect(() => {
    if (!isPaypalOpen || !selected || !paypalConfig.configured || !paypalConfig.csrfToken) {
      return undefined;
    }

    let canceled = false;
    const container = paypalButtonsRef.current;

    const renderButtons = async () => {
      try {
        setPurchaseError('');
        const ensurePaypalSdk = async () => {
          if (typeof window === 'undefined') {
            throw new Error('PayPal ist im Browser nicht verfuegbar.');
          }

          if (!paypalConfig.clientId) {
            throw new Error('PayPal Client-ID fehlt.');
          }

          if (window.paypal?.Buttons) {
            setPaypalSdkReady(true);
            return;
          }

          const existing = document.getElementById('paypal-sdk-js');
          if (existing) {
            await new Promise((resolve, reject) => {
              existing.addEventListener('load', resolve, { once: true });
              existing.addEventListener('error', reject, { once: true });
            });
            setPaypalSdkReady(Boolean(window.paypal?.Buttons));
            return;
          }

          await new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.id = 'paypal-sdk-js';
            script.src = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(paypalConfig.clientId)}&currency=EUR&intent=capture`;
            script.async = true;
            script.onload = resolve;
            script.onerror = reject;
            document.body.appendChild(script);
          });
          setPaypalSdkReady(Boolean(window.paypal?.Buttons));
        };

        await ensurePaypalSdk();
        if (canceled || !window.paypal?.Buttons || !container) {
          return;
        }

        container.innerHTML = '';
        const buttons = window.paypal.Buttons({
          style: {
            layout: 'vertical',
            shape: 'rect',
            label: 'paypal',
          },
          createOrder: async () => {
            setPaypalBusy(true);
            setPurchaseError('');
            try {
              const json = await postPaypalJson('/credits/paypal/order', {
                packageId: selected.id,
                _token: paypalConfig.csrfToken,
              });

              const orderId = String(json?.id || '').trim();
              if (!orderId) {
                throw new Error('PayPal Order konnte nicht erstellt werden.');
              }

              return orderId;
            } catch (error) {
              setPurchaseError(error instanceof Error ? error.message : 'PayPal Order konnte nicht erstellt werden.');
              throw error;
            } finally {
              setPaypalBusy(false);
            }
          },
          onApprove: async (data) => {
            setPaypalBusy(true);
            setPurchaseError('');
            try {
              const json = await postPaypalJson('/credits/paypal/capture', {
                orderId: String(data?.orderID || ''),
                _token: paypalConfig.csrfToken,
              });

              if (typeof json?.credits === 'number' && Number.isFinite(json.credits)) {
                setCurrentBalance(json.credits);
              }
              if (json?.transaction && typeof json.transaction === 'object') {
                setTransactions((current) => [normalizeTransactions([json.transaction])[0], ...current]);
              }

              setPurchaseSuccess(String(json?.message || `${selected.credits} Credits wurden aufgeladen.`));
              setIsPaypalOpen(false);
            } catch (error) {
              setPurchaseError(error instanceof Error ? error.message : 'PayPal-Zahlung konnte nicht verbucht werden.');
            } finally {
              setPaypalBusy(false);
            }
          },
          onCancel: () => {
            setPaypalBusy(false);
          },
          onError: (error) => {
            setPaypalBusy(false);
            setPurchaseError(error instanceof Error ? error.message : 'PayPal konnte nicht gestartet werden.');
          },
        });

        if (!buttons.isEligible()) {
          setPurchaseError('PayPal ist fuer dieses Konto aktuell nicht verfuegbar.');
          return;
        }

        await buttons.render(container);
      } catch (error) {
        if (canceled) {
          return;
        }
        setPurchaseError(error instanceof Error ? error.message : 'PayPal konnte nicht geladen werden.');
        setPaypalBusy(false);
      }
    };

    renderButtons();

    return () => {
      canceled = true;
      if (container) {
        container.innerHTML = '';
      }
    };
  }, [isPaypalOpen, paypalConfig.clientId, paypalConfig.configured, paypalConfig.csrfToken, selected]);

  const handleStartPaypalCheckout = () => {
    setPurchaseSuccess('');
    setPurchaseError('');

    if (!selected) {
      setPurchaseError(t('selectHint'));
      return;
    }

    if (!stripeCheckoutUrl) {
      setPurchaseError('Der Checkout ist momentan nicht erreichbar.');
      return;
    }

    window.location.href = stripeCheckoutUrl;
  };

  const handleRequestPrepaymentInvoice = async () => {
    setPrepaymentError('');
    setPrepaymentSuccess('');

    if (vorkasseNum < 10) {
      setPrepaymentError(t('vorkasseMin'));
      return;
    }

    if (!prepaymentConfig.csrfToken) {
      setPrepaymentError('CSRF-Token fehlt. Bitte Seite neu laden.');
      return;
    }

    setPrepaymentBusy(true);
    try {
      const payload = await postPaypalJson('/credits/prepayment/invoice', {
        credits: vorkasseNum,
        _token: prepaymentConfig.csrfToken,
      });

      const message = String(payload?.message || t('vorkasseSentMsg'));
      setPrepaymentSuccess(message);
      setVorkasseSent(true);
    } catch (error) {
      setPrepaymentError(error instanceof Error ? error.message : 'Rechnung konnte nicht erstellt werden.');
    } finally {
      setPrepaymentBusy(false);
    }
  };

  return (
    <DashboardLayout>
      <div className="space-y-6 max-w-[1400px]">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="font-heading text-2xl font-bold text-foreground tracking-tight flex items-center gap-3">
              <ShoppingCart weight="fill" className="w-7 h-7 text-primary" />
              {t('title')}
            </h1>
            <p className="text-sm text-muted-foreground mt-1">{t('subtitle')}</p>
          </div>
          <div className="flex items-center gap-3 bg-card border border-border rounded-sm px-5 py-3" data-testid="balance-display">
            <CurrencyCircleDollar weight="fill" className="w-8 h-8 text-primary" />
            <div>
              <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{t('yourBalance')}</p>
              <p className="text-xl font-bold text-foreground font-heading">{currentBalance.toLocaleString('de-DE')} <span className="text-sm font-normal text-muted-foreground">{t('credits')}</span></p>
            </div>
          </div>
        </div>

        {/* Packages grid - full width */}
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4" data-testid="packages-grid">
          {creditPackages.map((pkg) => {
            const isActive = selectedId === pkg.id;
            const badge = getBadge(pkg);
            return (
              <button
                key={pkg.id}
                type="button"
                onClick={() => setSelectedId(pkg.id)}
                data-testid={`package-${pkg.id}`}
                className={cn(
                  "relative text-left rounded-sm border-2 transition-all p-5 group",
                  isActive
                    ? "bg-primary/8 border-primary ring-1 ring-primary/30"
                    : badge
                      ? "bg-card border-border hover:border-primary/50"
                      : "bg-card border-border hover:border-muted-foreground/40"
                )}
              >
                {badge && (
                  <div className={cn("absolute -top-3 right-4 px-3 py-0.5 rounded-sm text-[10px] font-bold tracking-wider", badge.cls)}>
                    {badge.label}
                  </div>
                )}
                {isActive && (
                  <div className="absolute top-3 right-3 w-6 h-6 rounded-full bg-primary flex items-center justify-center">
                    <Check weight="bold" className="w-3.5 h-3.5 text-white" />
                  </div>
                )}
                {/* Credits amount - hero */}
                <p className="text-3xl font-bold text-primary font-heading">{pkg.credits.toLocaleString('de-DE')}</p>
                <p className="text-xs text-muted-foreground mb-3">{t('credits')}</p>
                {/* Price */}
                <div className="flex items-baseline gap-2 mb-1">
                  <span className="text-sm font-semibold text-foreground">{fmt(pkg.price)} &euro;</span>
                  {pkg.oldPrice && (
                    <span className="text-xs text-muted-foreground line-through">{fmt(pkg.oldPrice)} &euro;</span>
                  )}
                </div>
                <p className="text-[11px] text-muted-foreground mb-4">{fmt(pkg.priceInkl)} &euro; {t('inclVat')}</p>
                <div className="h-px bg-border mb-3" />
                <p className="text-[10px] text-muted-foreground uppercase tracking-wider mb-2">{t('features')}</p>
                <div className="flex flex-wrap gap-x-4 gap-y-1">
                  {pkg.features.map((f, i) => (
                    <span key={i} className="text-sm text-foreground flex items-center gap-1.5">
                      <span className="w-1 h-1 rounded-full bg-primary flex-shrink-0" />
                      {f}
                    </span>
                  ))}
                </div>
                {pkg.perFile && (
                  <div className="mt-3 flex items-center gap-1.5 text-xs text-green-400 font-semibold">
                    <Tag weight="fill" className="w-3.5 h-3.5" />
                    {pkg.perFile}&euro; / File
                  </div>
                )}
                <button
                  type="button"
                  className={cn(
                    "mt-4 w-full py-2 rounded-sm text-xs font-bold uppercase tracking-wider transition-all",
                    isActive
                      ? "btn-gradient text-white"
                      : "bg-primary/15 text-primary border border-primary/30 group-hover:bg-primary/25"
                  )}
                >
                  {t('buyNow')}
                </button>
              </button>
            );
          })}
        </div>

        {/* Bottom row: Purchase summary + Vorkasse */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Selected package / Buy */}
          <Card className={cn("border-2 transition-all", selected ? "bg-card border-primary/50" : "bg-card border-border")} data-testid="purchase-sidebar">
            <CardContent className="p-5">
              {selected ? (
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <div className="flex items-center gap-4">
                    <Coins weight="fill" className="w-8 h-8 text-primary" />
                    <div>
                      <p className="text-3xl font-bold text-foreground font-heading">{selected.credits.toLocaleString('de-DE')}</p>
                      <p className="text-xs text-muted-foreground">{t('credits')}</p>
                    </div>
                  </div>
                  <div className="flex-1 flex items-center gap-4 text-sm">
                    <div className="space-y-1">
                      <div className="flex justify-between gap-4">
                        <span className="text-muted-foreground">{t('price')} (netto)</span>
                        <span className="font-bold text-foreground">{fmt(selected.price)} &euro;</span>
                      </div>
                      <div className="flex justify-between gap-4">
                        <span className="text-muted-foreground">{t('vat')} (19%)</span>
                        <span className="font-bold text-foreground">{fmt(selected.priceInkl)} &euro;</span>
                      </div>
                    </div>
                  </div>
                  <div className="flex flex-wrap gap-2 justify-end">
                    <Button
                      asChild
                      className="btn-gradient text-white font-bold py-3 px-8 h-auto text-sm flex-shrink-0"
                      data-testid="purchase-btn"
                    >
                      <a href={stripeCheckoutUrl || '#'} className="flex items-center gap-2">
                        <CreditCard weight="fill" className="w-5 h-5" />
                        {t('buyNow')} &mdash; {fmt(selected.priceInkl)} &euro;
                      </a>
                    </Button>
                  </div>
                </div>
              ) : (
                <div className="flex items-center gap-4 py-2">
                  <ShoppingCart weight="light" className="w-8 h-8 text-muted-foreground/40" />
                  <p className="text-sm text-muted-foreground">{t('selectHint')}</p>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Vorkasse */}
          <Card className="bg-card border-border border-2" data-testid="vorkasse-card">
            <CardContent className="p-5">
              {vorkasseSent ? (
                <div className="flex items-center gap-4">
                  <CheckCircle weight="fill" className="w-10 h-10 text-green-500 flex-shrink-0" />
                  <div className="flex-1">
                    <p className="text-sm font-bold text-green-400">{t('vorkasseSent')}</p>
                    <p className="text-xs text-muted-foreground mt-0.5">{t('vorkasseSentMsg')}</p>
                  </div>
                  <Button
                    variant="outline"
                    className="border-border font-semibold text-sm flex-shrink-0"
                    data-testid="vorkasse-new-btn"
                    onClick={() => { setVorkasseSent(false); setVorkasseCredits(''); }}
                  >
                    {t('vorkasseNew')}
                  </Button>
                </div>
              ) : (
                <div className="flex flex-col gap-3">
                  <div>
                    <label className="flex items-center gap-2 text-xs font-semibold text-muted-foreground tracking-widest uppercase">
                      <Bank weight="bold" className="w-3.5 h-3.5" />
                      {t('vorkasseTitle')}
                    </label>
                    <p className="text-[11px] text-muted-foreground mt-0.5">{t('vorkasseDesc')}</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <div className="relative flex-1 min-w-0">
                      <input
                        type="number"
                        min="10"
                        step="10"
                        value={vorkasseCredits}
                        onChange={(e) => setVorkasseCredits(e.target.value)}
                        placeholder="z.B. 500"
                        data-testid="vorkasse-input"
                        style={{appearance: 'textfield', MozAppearance: 'textfield'}}
                        className="w-full bg-secondary/50 border border-border rounded-sm px-4 py-2.5 text-sm text-foreground font-semibold placeholder:text-muted-foreground/50 focus:outline-none focus:border-primary/50 focus:ring-1 focus:ring-primary/20"
                      />
                      <span className="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground font-semibold pointer-events-none">Credits</span>
                    </div>
                    {vorkasseNum >= 10 && (
                      <span className="text-sm font-bold text-primary whitespace-nowrap flex-shrink-0">{fmt(vorkassePrice.brutto)} &euro;</span>
                    )}
                    <Button
                      className="bg-secondary/80 hover:bg-secondary border border-border text-foreground font-bold py-2.5 h-auto text-sm flex-shrink-0"
                      disabled={vorkasseNum < 10 || prepaymentBusy}
                      data-testid="vorkasse-submit-btn"
                      onClick={handleRequestPrepaymentInvoice}
                    >
                      <Receipt weight="fill" className="w-4 h-4 mr-2" />
                      {prepaymentBusy ? 'Sende...' : t('vorkasseBtn')}
                    </Button>
                  </div>
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {(prepaymentError || prepaymentSuccess) && (
          <Card className={cn('border', prepaymentError ? 'border-red-500/40 bg-red-500/10' : 'border-green-500/40 bg-green-500/10')}>
            <CardContent className="p-4">
              <p className={cn('text-sm', prepaymentError ? 'text-red-300' : 'text-green-300')}>
                {prepaymentError || prepaymentSuccess}
              </p>
            </CardContent>
          </Card>
        )}

        {(purchaseError || purchaseSuccess) && (
          <Card className={cn('border', purchaseError ? 'border-red-500/40 bg-red-500/10' : 'border-green-500/40 bg-green-500/10')}>
            <CardContent className="p-4">
              <p className={cn('text-sm', purchaseError ? 'text-red-300' : 'text-green-300')}>
                {purchaseError || purchaseSuccess}
              </p>
            </CardContent>
          </Card>
        )}

        <Card className="bg-card border-border" data-testid="transactions-card">
          <CardContent className="p-5">
            <div className="flex items-center justify-between mb-4">
              <div>
                <p className="text-lg font-heading font-bold text-foreground">{t('transactionsTitle')}</p>
                <p className="text-xs text-muted-foreground">{t('transactionsSubtitle')}</p>
              </div>
              <Badge variant="outline" className="border-border text-muted-foreground">
                {transactions.length}
              </Badge>
            </div>

            {transactions.length === 0 ? (
              <p className="text-sm text-muted-foreground">{t('noTransactions')}</p>
            ) : (
              <div className="space-y-2">
                {paginatedTransactions.map((transaction) => {
                  const isPositive = transaction.amount >= 0;
                  return (
                    <div
                      key={transaction.id || `${transaction.createdAt}-${transaction.amount}`}
                      className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[170px_140px_minmax(0,1fr)_120px_110px_120px] gap-2 rounded-sm border border-border bg-secondary/30 px-3 py-2.5"
                    >
                      <div className="min-w-0">
                        <p className="text-[10px] uppercase tracking-wider text-muted-foreground">{t('bookingDate')}</p>
                        <p className="text-xs text-foreground">{formatBookingDate(transaction.createdAt)}</p>
                      </div>
                      <div className="min-w-0">
                        <p className="text-[10px] uppercase tracking-wider text-muted-foreground">{t('bookingType')}</p>
                        <p className="text-xs text-foreground">{getTransactionTypeLabel(transaction.transactionType)}</p>
                      </div>
                      <div className="min-w-0 sm:col-span-2 xl:col-span-1">
                        <p className="text-[10px] uppercase tracking-wider text-muted-foreground">{t('bookingDetails')}</p>
                        <p className="text-xs text-foreground whitespace-normal break-all leading-relaxed">{transaction.description || '-'}</p>
                      </div>
                      <div className="min-w-0">
                        <p className="text-[10px] uppercase tracking-wider text-muted-foreground">{t('bookingPayment')}</p>
                        <p className="text-xs text-foreground">{transaction.paymentMethod || t('paymentUnknown')}</p>
                      </div>
                      <div className="min-w-0">
                        <p className="text-[10px] uppercase tracking-wider text-muted-foreground">{t('bookingAmount')}</p>
                        <p className={cn('text-xs font-bold', isPositive ? 'text-green-400' : 'text-red-400')}>
                          {isPositive ? '+' : ''}{transaction.amount}
                        </p>
                      </div>
                      <div className="min-w-0">
                        <p className="text-[10px] uppercase tracking-wider text-muted-foreground">{t('bookingBalance')}</p>
                        <p className="text-xs font-semibold text-primary">{transaction.balanceAfter}</p>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}

            {transactions.length > 0 && (
              <div className="mt-4 flex items-center justify-between border-t border-border pt-3">
                <span className="text-xs text-muted-foreground">
                  Seite {safeTransactionsPage} / {totalTransactionPages}
                </span>
                <div className="flex items-center gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    className="h-8 px-3 border-border text-xs"
                    disabled={safeTransactionsPage <= 1}
                    onClick={() => setTransactionsPage((page) => Math.max(1, page - 1))}
                  >
                    Zurück
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    className="h-8 px-3 border-border text-xs"
                    disabled={safeTransactionsPage >= totalTransactionPages}
                    onClick={() => setTransactionsPage((page) => Math.min(totalTransactionPages, page + 1))}
                  >
                    Weiter
                  </Button>
                </div>
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      {isPaypalOpen && (
        <div className="fixed inset-0 z-[120] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
          <Card className="w-full max-w-md bg-card border-border">
            <CardContent className="p-5 space-y-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-lg font-heading font-bold text-foreground">{t('buyNow')}</p>
                  <p className="text-xs text-muted-foreground">
                    {selected ? `${selected.credits} Credits - ${fmt(selected.priceInkl)} EUR` : ''}
                  </p>
                </div>
                <Button
                  type="button"
                  variant="outline"
                  className="h-8 w-8 p-0 border-border"
                  onClick={() => setIsPaypalOpen(false)}
                >
                  <XIcon weight="bold" className="w-4 h-4" />
                </Button>
              </div>

              <div ref={paypalButtonsRef} className={cn('min-h-[56px]', !paypalSdkReady && 'animate-pulse bg-secondary/40 rounded-sm')} />

              {paypalBusy && (
                <p className="text-xs text-muted-foreground">PayPal wird verarbeitet...</p>
              )}
              {purchaseError && (
                <p className="text-xs text-red-300">{purchaseError}</p>
              )}
            </CardContent>
          </Card>
        </div>
      )}
    </DashboardLayout>
  );
}
