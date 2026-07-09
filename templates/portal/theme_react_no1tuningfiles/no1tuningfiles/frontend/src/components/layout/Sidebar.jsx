import { useState } from 'react';
import { useLocation } from 'react-router-dom';
import { useLanguage } from '../../context/LanguageContext';
import {
  House,
  FileArrowUp,
  ClockCounterClockwise,
  User,
  SignOut,
  Receipt,
  Ticket,
  CaretDown,
  CaretUp,
  CarProfile,
  Sliders,
  CurrencyCircleDollar,
  ListBullets,
  ShoppingCart,
  EnvelopeSimple,
  ChartBar,
  Warning,
} from '@phosphor-icons/react';
import { cn } from '../../lib/utils';
import { portalRoutes } from '../../lib/portalRoutes';
import { getPortalDesignBootstrap } from '../../lib/portalDesignBootstrap';

export const Sidebar = () => {
  const { language } = useLanguage();
  const location = useLocation();
  const design = getPortalDesignBootstrap();

  const [tuningOpen, setTuningOpen] = useState(true);
  const [creditsOpen, setCreditsOpen] = useState(true);

  const labels = {
    de: {
      dashboard: 'Dashboard',
      tuningFile: 'Tuning File',
      newOrder: 'Neuer Auftrag',
      allOrders: 'Alle Aufträge',
      priceList: 'Preisliste',
      configurator: 'Konfigurator',
      credits: 'Credits',
      buy: 'Kaufen',
      invoices: 'Rechnungen',
      tickets: 'Tickets',
      profile: 'Profil',
      emailTemplates: 'E-Mail Templates',
      statistics: 'Statistiken',
      dtcCodes: 'DTC Codes',
      logout: 'Abmelden',
    },
    en: {
      dashboard: 'Dashboard',
      tuningFile: 'Tuning File',
      newOrder: 'New Order',
      allOrders: 'All Orders',
      priceList: 'Price List',
      configurator: 'Configurator',
      credits: 'Credits',
      buy: 'Buy',
      invoices: 'Invoices',
      tickets: 'Tickets',
      profile: 'Profile',
      emailTemplates: 'Email Templates',
      statistics: 'Statistics',
      dtcCodes: 'DTC Codes',
      logout: 'Logout',
    },
  };

  const t = (key) => labels[language]?.[key] || labels.en[key];

  const isPathActive = (path) => location.pathname === path;
  const isGroupActive = (paths) => paths.some((path) => location.pathname === path || location.pathname.startsWith(`${path}/`));

  const NavItem = ({ path, icon: Icon, label, indent = false }) => {
    const isActive = isPathActive(path);

    return (
      <a
        href={path}
        data-testid={`nav-${path.replace('/', '') || 'dashboard'}`}
        className={cn(
          'flex items-center gap-3 px-4 py-2.5 rounded-[4px] text-sm transition-colors duration-200',
          indent && 'ml-4 border-l pl-3',
          indent && 'border-white/10',
          isActive
            ? 'bg-primary text-primary-foreground font-semibold'
            : 'text-[#A5A5A8] hover:text-white hover:bg-white/5 font-medium'
        )}
      >
        <Icon weight={isActive ? 'fill' : 'regular'} className="w-5 h-5" />
        {label}
      </a>
    );
  };

  const MenuGroup = ({ icon: Icon, label, isOpen, onToggle, children, activePaths }) => {
    const hasActiveChild = isGroupActive(activePaths);

    return (
      <div>
        <button
          onClick={onToggle}
          className={cn(
            'flex items-center justify-between w-full px-4 py-2.5 rounded-[4px] text-sm transition-colors duration-200',
            hasActiveChild ? 'text-white font-semibold' : 'text-[#A5A5A8] hover:text-white hover:bg-white/5 font-medium'
          )}
        >
          <span className="flex items-center gap-3">
            <Icon weight={hasActiveChild ? 'fill' : 'regular'} className="w-5 h-5" />
            {label}
          </span>
          {isOpen ? <CaretUp weight="bold" className="w-4 h-4" /> : <CaretDown weight="bold" className="w-4 h-4" />}
        </button>
        {isOpen && <div className="mt-1 space-y-1">{children}</div>}
      </div>
    );
  };

  const Separator = () => <div className="my-3 mx-4 h-px bg-white/10" />;

  return (
    <aside
      className="fixed left-0 top-0 h-screen w-64 border-r flex flex-col z-50"
      data-testid="sidebar"
      style={{ backgroundColor: '#151618', borderColor: 'rgba(255,255,255,0.08)' }}
    >
      <div className="h-16 flex items-center justify-between px-6 border-b" style={{ borderColor: 'rgba(255,255,255,0.08)' }}>
        {design.logoUrl
          ? <img src={design.logoUrl} alt={design.portalName} className="h-8 max-w-[160px] object-contain" style={{ filter: 'brightness(0) invert(1)' }} />
          : <span className="text-white font-bold text-sm">{design.portalName}</span>
        }
      </div>

      <nav className="flex-1 py-4 px-3 overflow-y-auto">
        <NavItem path={portalRoutes.dashboard} icon={House} label={t('dashboard')} />
        <Separator />

        <MenuGroup
          icon={CarProfile}
          label={t('tuningFile')}
          isOpen={tuningOpen}
          onToggle={() => setTuningOpen(!tuningOpen)}
          activePaths={[portalRoutes.order, portalRoutes.orders, portalRoutes.priceList, portalRoutes.statistics]}
        >
          <NavItem path={portalRoutes.order} icon={FileArrowUp} label={t('newOrder')} indent />
          <NavItem path={portalRoutes.orders} icon={ClockCounterClockwise} label={t('allOrders')} indent />
          <NavItem path={portalRoutes.priceList} icon={ListBullets} label={t('priceList')} indent />
          <NavItem path={portalRoutes.statistics} icon={ChartBar} label={t('statistics')} indent />
        </MenuGroup>

        <Separator />

        <NavItem path={portalRoutes.configurator} icon={Sliders} label={t('configurator')} />
        <NavItem path={portalRoutes.dtcCodes} icon={Warning} label={t('dtcCodes')} />

        <Separator />

        <MenuGroup
          icon={CurrencyCircleDollar}
          label={t('credits')}
          isOpen={creditsOpen}
          onToggle={() => setCreditsOpen(!creditsOpen)}
          activePaths={[portalRoutes.credits, portalRoutes.invoices]}
        >
          <NavItem path={portalRoutes.credits} icon={ShoppingCart} label={t('buy')} indent />
          <NavItem path={portalRoutes.invoices} icon={Receipt} label={t('invoices')} indent />
        </MenuGroup>

        <Separator />

        <NavItem path={portalRoutes.tickets} icon={Ticket} label={t('tickets')} />
        <NavItem path={portalRoutes.emailTemplates} icon={EnvelopeSimple} label={t('emailTemplates')} />
        <NavItem path={portalRoutes.profile} icon={User} label={t('profile')} />
      </nav>

      <div className="p-3 border-t" style={{ borderColor: 'rgba(255,255,255,0.08)' }}>
        <button
          onClick={() => {
            window.location.href = portalRoutes.logout;
          }}
          data-testid="logout-btn"
          className="flex items-center gap-3 px-4 py-2.5 w-full rounded-[4px] font-medium text-sm text-[#A5A5A8] hover:text-white hover:bg-white/5 transition-colors duration-200"
        >
          <SignOut weight="regular" className="w-5 h-5" />
          {t('logout')}
        </button>
      </div>
    </aside>
  );
};
