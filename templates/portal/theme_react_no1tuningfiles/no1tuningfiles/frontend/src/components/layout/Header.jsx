import { useEffect, useMemo, useRef, useState } from 'react';
import { useLanguage } from '../../context/LanguageContext';
import { useTheme } from '../../context/ThemeContext';
import { Bell, MagnifyingGlass, GlobeSimple, FilePlus, CurrencyCircleDollar, Sun, Moon, User, Ticket, SignOut, Palette } from '@phosphor-icons/react';
import { Button } from '../ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '../ui/dropdown-menu';
import { Badge } from '../ui/badge';
import { portalRoutes } from '../../lib/portalRoutes';
import { getBootstrapCredits, PORTAL_CREDITS_UPDATED_EVENT } from '../../lib/sessionBootstrap';
import { GlobalSearch } from '../search/GlobalSearch';

const NOTIFICATION_STATE_STORAGE_KEY = 'tfd.header.notification_state.v1';
const NOTIFICATION_ITEMS_STORAGE_KEY = 'tfd.header.notification_items.v1';
const NOTIFICATION_READ_STORAGE_KEY = 'tfd.header.notification_read_at.v1';
const MAX_NOTIFICATION_ITEMS = 20;
const COLOR_PRESETS = ['#e42525', '#2563eb', '#16a34a', '#f97316', '#7c3aed', '#14b8a6', '#e11d48', '#b45309'];

const readJsonStorage = (key, fallback) => {
  if (typeof window === 'undefined') {
    return fallback;
  }

  try {
    const raw = window.localStorage.getItem(key);
    if (!raw) {
      return fallback;
    }

    const parsed = JSON.parse(raw);
    return parsed && typeof parsed === 'object' ? parsed : fallback;
  } catch (_error) {
    return fallback;
  }
};

const writeJsonStorage = (key, value) => {
  if (typeof window === 'undefined') {
    return;
  }

  try {
    window.localStorage.setItem(key, JSON.stringify(value));
  } catch (_error) {
    // ignore storage errors
  }
};

const normalizeOrderStatusLabel = (status, language) => {
  const safeStatus = String(status || '').toLowerCase();
  if (safeStatus === 'completed') {
    return language === 'de' ? 'Abgeschlossen' : 'Completed';
  }
  if (safeStatus === 'processing') {
    return language === 'de' ? 'In Bearbeitung' : 'Processing';
  }
  if (safeStatus === 'parked') {
    return language === 'de' ? 'Geparkt' : 'Parked';
  }
  if (safeStatus === 'cancelled') {
    return language === 'de' ? 'Abgelehnt' : 'Cancelled';
  }
  return language === 'de' ? 'Eingegangen' : 'Received';
};

const normalizeVehicleLabel = (order, language) => {
  const vehicle = order?.vehicle && typeof order.vehicle === 'object' ? order.vehicle : {};
  const parts = [
    String(vehicle.manufacturer || '').trim(),
    String(vehicle.series || '').trim(),
    String(vehicle.model || '').trim(),
  ].filter((item) => item !== '');

  const engine = String(vehicle.engine || '').trim();
  if (parts.length === 0 && engine !== '') {
    return engine;
  }

  const base = parts.join(' ');
  if (base === '') {
    return language === 'de' ? 'Fahrzeug' : 'Vehicle';
  }

  if (engine !== '' && !base.toLowerCase().includes(engine.toLowerCase())) {
    return `${base} · ${engine}`;
  }

  return base;
};

const extractLatestSupportMessageTimestamp = (ticket) => {
  if (!ticket || typeof ticket !== 'object' || !Array.isArray(ticket.messages)) {
    return null;
  }

  let latestTimestamp = null;
  let latestValue = 0;

  for (const message of ticket.messages) {
    if (!message || message.sender !== 'support') {
      continue;
    }

    const timestamp = String(message.timestamp || '').trim();
    if (timestamp === '') {
      continue;
    }

    const value = new Date(timestamp).getTime();
    if (!Number.isFinite(value)) {
      continue;
    }

    if (latestTimestamp === null || value > latestValue) {
      latestTimestamp = timestamp;
      latestValue = value;
    }
  }

  return latestTimestamp;
};

const resolveHeaderUser = (profilePayload = null) => {
  if (typeof window === 'undefined') {
    return { name: 'Portal Kunde', initials: 'PK' };
  }

  const bootstrap = window?.portalReactBootstrap ?? {};
  const dashboardName = String(bootstrap?.dashboard?.customerName || '').trim();
  const bootstrapProfile = bootstrap?.profile && typeof bootstrap.profile === 'object' ? bootstrap.profile : {};
  const profile = profilePayload && typeof profilePayload === 'object' ? profilePayload : bootstrapProfile;
  const firstName = String(profile.firstName || profile.vorname || '').trim();
  const lastName = String(profile.lastName || profile.nachname || '').trim();
  const profileName = `${firstName} ${lastName}`.trim();
  const fallbackName = String(profile.companyName || profile.firmenname || profile.email || '').trim();
  const name = profileName || fallbackName || dashboardName || 'Portal Kunde';

  const parts = name.split(/\s+/).filter(Boolean);
  const initials = (parts.length >= 2
    ? `${parts[0][0] || ''}${parts[1][0] || ''}`
    : `${parts[0]?.[0] || 'P'}${parts[0]?.[1] || 'K'}`
  ).toUpperCase();

  return { name, initials };
};

export const Header = () => {
  const { language, setLanguage, t } = useLanguage();
  const { theme, toggleTheme, primaryColor, setPrimaryColor, resetPrimaryColor, defaultPrimaryColor } = useTheme();
  const [credits, setCredits] = useState(() => getBootstrapCredits());
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [designMenuOpen, setDesignMenuOpen] = useState(false);
  const [notifications, setNotifications] = useState(() => {
    const stored = readJsonStorage(NOTIFICATION_ITEMS_STORAGE_KEY, { items: [] });
    const items = Array.isArray(stored.items) ? stored.items : [];
    return items.filter((item) => item && typeof item === 'object');
  });
  const [notificationsOpen, setNotificationsOpen] = useState(false);
  const [languageMenuOpen, setLanguageMenuOpen] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const [lastReadAt, setLastReadAt] = useState(() => {
    if (typeof window === 'undefined') {
      return null;
    }

    const value = window.localStorage.getItem(NOTIFICATION_READ_STORAGE_KEY);
    return typeof value === 'string' && value.trim() !== '' ? value : null;
  });
  const notificationStateRef = useRef(
    readJsonStorage(NOTIFICATION_STATE_STORAGE_KEY, {
      orderSignatures: {},
      ticketReplySignatures: {},
    })
  );
  const [user, setUser] = useState(() => resolveHeaderUser());

  const orderText = language === 'de' ? 'Auftrag' : 'Order';
  const creditsText = language === 'de' ? 'Credits kaufen' : 'Buy Credits';
  const creditsWithValue = `${creditsText} (${credits.toLocaleString(language === 'de' ? 'de-DE' : 'en-US')})`;
  const shortcutText = typeof navigator !== 'undefined' && navigator.platform.toUpperCase().includes('MAC') ? 'Cmd K' : 'Ctrl K';
  const designText = language === 'de' ? 'Designfarbe' : 'Theme color';
  const resetColorText = language === 'de' ? 'Standardfarbe' : 'Reset color';
  const unreadNotifications = useMemo(() => {
    if (!Array.isArray(notifications) || notifications.length === 0) {
      return [];
    }

    if (!lastReadAt) {
      return notifications;
    }

    const readAtTimestamp = new Date(lastReadAt).getTime();
    return notifications.filter((notification) => {
      const createdAt = String(notification?.createdAt || '');
      const createdAtTimestamp = createdAt !== '' ? new Date(createdAt).getTime() : 0;
      return Number.isFinite(createdAtTimestamp) && createdAtTimestamp > readAtTimestamp;
    });
  }, [lastReadAt, notifications]);

  useEffect(() => {
    const handleCreditsUpdated = (event) => {
      const nextCredits = event?.detail?.credits;
      if (typeof nextCredits === 'number' && Number.isFinite(nextCredits)) {
        setCredits(nextCredits);
        return;
      }

      setCredits(getBootstrapCredits());
    };

    window.addEventListener(PORTAL_CREDITS_UPDATED_EVENT, handleCreditsUpdated);

    return () => {
      window.removeEventListener(PORTAL_CREDITS_UPDATED_EVENT, handleCreditsUpdated);
    };
  }, []);

  useEffect(() => {
    let cancelled = false;

    const loadProfile = async () => {
      try {
        const response = await fetch('/profil/api', {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
        });
        if (!response.ok) {
          return;
        }

        const payload = await response.json();
        if (!cancelled) {
          setUser(resolveHeaderUser(payload));
        }
      } catch (_error) {
        // ignore profile loading errors
      }
    };

    loadProfile();

    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    let cancelled = false;

    const pollNotifications = async () => {
      try {
        const response = await fetch('/header/notifications', {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
        });
        if (!response.ok) {
          return;
        }

        const payload = await response.json();
        const orders = Array.isArray(payload?.orders) ? payload.orders : [];
        const tickets = Array.isArray(payload?.tickets) ? payload.tickets : [];
        const nowIso = new Date().toISOString();

        const previousState = notificationStateRef.current ?? {
          orderSignatures: {},
          ticketReplySignatures: {},
        };

        const nextOrderSignatures = {};
        const nextTicketReplySignatures = {};
        const incomingNotifications = [];

        for (const order of orders) {
          const id = String(order?.id || '').trim();
          if (id === '') {
            continue;
          }

          const status = String(order?.status || '').trim();
          const updatedAt = String(order?.updatedAt || order?.createdAt || '').trim();
          const signature = `${status}|${updatedAt}`;
          const previousSignature = String(previousState.orderSignatures?.[id] || '');
          nextOrderSignatures[id] = signature;

          if (previousSignature !== '' && previousSignature !== signature) {
            incomingNotifications.push({
              id: `order:${id}:${signature}`,
              type: 'order',
              href: `/orders/${encodeURIComponent(id)}`,
              title: language === 'de' ? 'Auftragsstatus geändert' : 'Order status changed',
              text: `${id} · ${normalizeVehicleLabel(order, language)} · ${normalizeOrderStatusLabel(status, language)}`,
              createdAt: nowIso,
            });
          }
        }

        for (const ticket of tickets) {
          const id = String(ticket?.id || '').trim();
          if (id === '') {
            continue;
          }

          const latestSupportTimestamp = extractLatestSupportMessageTimestamp(ticket);
          const signature = latestSupportTimestamp || '';
          const previousSignature = String(previousState.ticketReplySignatures?.[id] || '');
          nextTicketReplySignatures[id] = signature;

          if (signature !== '' && previousSignature !== signature) {
            incomingNotifications.push({
              id: `ticket:${id}:${signature}`,
              type: 'ticket',
              href: `/tickets/${encodeURIComponent(id)}`,
              title: language === 'de' ? 'Neue Ticket-Antwort' : 'New ticket reply',
              text: `${id} · ${String(ticket?.subject || '').trim() || (language === 'de' ? 'Support-Ticket' : 'Support ticket')}`,
              createdAt: nowIso,
            });
          }
        }

        notificationStateRef.current = {
          orderSignatures: nextOrderSignatures,
          ticketReplySignatures: nextTicketReplySignatures,
        };
        writeJsonStorage(NOTIFICATION_STATE_STORAGE_KEY, notificationStateRef.current);

        if (!cancelled && incomingNotifications.length > 0) {
          setNotifications((previous) => {
            const combined = [...incomingNotifications, ...(Array.isArray(previous) ? previous : [])];
            const deduplicated = [];
            const seen = new Set();

            for (const entry of combined) {
              const key = String(entry?.id || '');
              if (key === '' || seen.has(key)) {
                continue;
              }

              seen.add(key);
              deduplicated.push(entry);
              if (deduplicated.length >= MAX_NOTIFICATION_ITEMS) {
                break;
              }
            }

            writeJsonStorage(NOTIFICATION_ITEMS_STORAGE_KEY, { items: deduplicated });
            return deduplicated;
          });
        }
      } catch (_error) {
        // ignore polling errors
      }
    };

    pollNotifications();
    const intervalId = window.setInterval(pollNotifications, 30000);

    return () => {
      cancelled = true;
      window.clearInterval(intervalId);
    };
  }, [language]);

  useEffect(() => {
    if (!notificationsOpen) {
      return;
    }

    const timestamp = new Date().toISOString();
    setLastReadAt(timestamp);
    if (typeof window !== 'undefined') {
      window.localStorage.setItem(NOTIFICATION_READ_STORAGE_KEY, timestamp);
    }
  }, [notificationsOpen]);

  return (
    <header
      className="h-16 bg-card/90 backdrop-blur border-b border-border flex items-center justify-between px-6 sticky top-0 z-40"
      data-testid="header"
    >
      {/* Left Section - Back + Search */}
      <div className="flex items-center gap-3">
        <button
          type="button"
          onClick={() => setIsSearchOpen(true)}
          className="group flex h-10 w-[22rem] items-center gap-3 rounded-[6px] border border-border bg-background px-3 text-left transition-colors hover:border-primary/50"
          data-testid="search-input"
        >
          <MagnifyingGlass className="h-4 w-4 text-muted-foreground transition-colors group-hover:text-primary" />
          <span className="flex-1 truncate text-sm text-muted-foreground">
            {language === 'de' ? 'Suche…' : 'Search…'}
          </span>
          <span className="rounded-[4px] border border-border bg-card px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-muted-foreground">
            {shortcutText}
          </span>
        </button>
      </div>

      {/* Right Section */}
      <div className="flex items-center gap-3">
        {/* New Order Button */}
        <Button
          className="bg-primary hover:bg-[hsl(var(--primary-hover))] text-primary-foreground font-semibold"
          onClick={() => {
            window.location.href = portalRoutes.order;
          }}
          data-testid="header-new-order-btn"
        >
          <FilePlus weight="bold" className="w-4 h-4 mr-2" />
          {orderText}
        </Button>

        {/* Buy Credits Button */}
        <Button
          variant="outline"
          className="h-10 rounded-[999px] border-border bg-background px-4 hover:bg-secondary font-semibold"
          onClick={() => {
            window.location.href = portalRoutes.credits;
          }}
          data-testid="header-buy-credits-btn"
        >
          <CurrencyCircleDollar weight="bold" className="w-4 h-4 mr-2" />
          {creditsWithValue}
        </Button>

        {/* Theme Toggle */}
        <DropdownMenu open={designMenuOpen} onOpenChange={setDesignMenuOpen} modal={false}>
          <DropdownMenuTrigger asChild>
            <Button
              variant="ghost"
              size="icon"
              className="text-muted-foreground hover:text-foreground hover:bg-secondary"
              data-testid="design-color-toggle"
            >
              <Palette weight="regular" className="w-5 h-5" style={{ color: primaryColor }} />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="bg-card border-border w-64 p-3">
            <div className="space-y-3">
              <div className="flex items-center justify-between gap-3">
                <div>
                  <p className="text-sm font-semibold text-foreground">{designText}</p>
                  <p className="text-xs text-muted-foreground">{primaryColor.toUpperCase()}</p>
                </div>
                <label
                  className="h-10 w-10 rounded-sm border border-border bg-secondary overflow-hidden"
                  aria-label={designText}
                >
                  <input
                    type="color"
                    value={primaryColor}
                    onChange={(event) => setPrimaryColor(event.target.value)}
                    className="h-12 w-12 -translate-x-1 -translate-y-1 cursor-pointer border-0 bg-transparent"
                    data-testid="design-color-input"
                  />
                </label>
              </div>
              <div className="flex flex-wrap gap-2">
                {COLOR_PRESETS.map((color) => (
                  <button
                    key={color}
                    type="button"
                    className="h-7 w-7 rounded-sm border transition-transform hover:scale-105"
                    style={{
                      backgroundColor: color,
                      borderColor: color.toLowerCase() === primaryColor.toLowerCase() ? '#ffffff' : 'rgba(255,255,255,0.12)',
                    }}
                    onClick={() => setPrimaryColor(color)}
                    aria-label={`${designText} ${color}`}
                    data-testid={`design-color-preset-${color.replace('#', '').toLowerCase()}`}
                  />
                ))}
              </div>
              <Button
                type="button"
                variant="outline"
                className="w-full border-border hover:bg-secondary"
                onClick={resetPrimaryColor}
                disabled={primaryColor.toLowerCase() === defaultPrimaryColor.toLowerCase()}
                data-testid="design-color-reset"
              >
                {resetColorText}
              </Button>
            </div>
          </DropdownMenuContent>
        </DropdownMenu>

        <Button
          variant="ghost"
          size="icon"
          className="text-muted-foreground hover:text-foreground hover:bg-secondary"
          onClick={toggleTheme}
          data-testid="theme-toggle"
        >
          {theme === 'dark' ? (
            <Sun weight="regular" className="w-5 h-5" />
          ) : (
            <Moon weight="regular" className="w-5 h-5" />
          )}
        </Button>

        {/* Notifications */}
        <DropdownMenu open={notificationsOpen} onOpenChange={setNotificationsOpen} modal={false}>
          <DropdownMenuTrigger asChild>
            <Button
              variant="ghost"
              size="icon"
              className="relative h-10 w-10 rounded-[6px] text-muted-foreground hover:text-foreground hover:bg-secondary"
              data-testid="notifications-btn"
            >
              <Bell weight="regular" className="w-5 h-5" />
              {unreadNotifications.length > 0 && (
                <Badge 
                  className="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 flex items-center justify-center bg-primary text-primary-foreground text-[10px] font-bold border-0"
                >
                  {unreadNotifications.length > 99 ? '99+' : unreadNotifications.length}
                </Badge>
              )}
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="bg-card border-border w-[360px] max-h-[420px] overflow-y-auto">
            {notifications.length === 0 ? (
              <DropdownMenuItem disabled className="text-muted-foreground">
                {language === 'de' ? 'Keine Benachrichtigungen' : 'No notifications'}
              </DropdownMenuItem>
            ) : (
              notifications.map((notification) => (
                <DropdownMenuItem
                  key={notification.id}
                  className="py-3 cursor-pointer"
                  onClick={() => {
                    window.location.href = String(notification.href || '/');
                  }}
                >
                  <div className="min-w-0">
                    <p className="text-sm font-semibold text-foreground truncate">{notification.title}</p>
                    <p className="text-xs text-muted-foreground truncate">{notification.text}</p>
                  </div>
                </DropdownMenuItem>
              ))
            )}
          </DropdownMenuContent>
        </DropdownMenu>

        {/* Language Switcher */}
        <DropdownMenu open={languageMenuOpen} onOpenChange={setLanguageMenuOpen} modal={false}>
          <DropdownMenuTrigger asChild>
            <Button
              variant="ghost"
              size="sm"
              className="gap-2 text-muted-foreground hover:text-foreground hover:bg-secondary"
              data-testid="language-switcher"
            >
              <GlobeSimple weight="regular" className="w-5 h-5" />
              <span className="uppercase text-xs font-semibold">{language}</span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="bg-card border-border">
            <DropdownMenuItem 
              onClick={() => {
                setLanguage('de');
                setLanguageMenuOpen(false);
              }}
              className={language === 'de' ? 'bg-primary/20 text-primary' : ''}
              data-testid="lang-de"
            >
              {t('german')}
            </DropdownMenuItem>
            <DropdownMenuItem 
              onClick={() => {
                setLanguage('en');
                setLanguageMenuOpen(false);
              }}
              className={language === 'en' ? 'bg-primary/20 text-primary' : ''}
              data-testid="lang-en"
            >
              {t('english')}
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        {/* User Menu */}
        <DropdownMenu open={userMenuOpen} onOpenChange={setUserMenuOpen} modal={false}>
          <DropdownMenuTrigger asChild>
            <Button
              variant="ghost"
              className="h-10 px-2 gap-2 rounded-[6px] text-muted-foreground hover:text-foreground hover:bg-secondary"
              data-testid="user-avatar"
            >
              <div className="w-8 h-8 rounded-[6px] bg-secondary border border-border flex items-center justify-center">
                <span className="text-sm font-semibold italic uppercase text-foreground">{user.initials}</span>
              </div>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="bg-card border-border w-56">
            <DropdownMenuLabel className="truncate">{user.name}</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem onClick={() => { setUserMenuOpen(false); window.location.href = portalRoutes.profile; }}>
              <User weight="regular" />
              {language === 'de' ? 'Profil' : 'Profile'}
            </DropdownMenuItem>
            <DropdownMenuItem onClick={() => { setUserMenuOpen(false); window.location.href = portalRoutes.tickets; }}>
              <Ticket weight="regular" />
              {language === 'de' ? 'Tickets' : 'Tickets'}
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem onClick={() => { setUserMenuOpen(false); window.location.href = portalRoutes.logout; }}>
              <SignOut weight="regular" />
              {language === 'de' ? 'Abmelden' : 'Logout'}
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
      <GlobalSearch language={language} open={isSearchOpen} onOpenChange={setIsSearchOpen} />
    </header>
  );
};
