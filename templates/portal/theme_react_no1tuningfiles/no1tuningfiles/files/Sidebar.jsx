// Portal sidebar — dark (default) or light variant, collapsible nav groups.
function Sidebar({ variant = 'dark', active, onNavigate }) {
  const dark = variant === 'dark';
  const [collapsed, setCollapsed] = React.useState({});
  const toggle = (k) => setCollapsed((c) => ({ ...c, [k]: !c[k] }));

  const itemStyle = (isActive) => ({
    display: 'flex', alignItems: 'center', gap: 11, padding: '9px 12px',
    borderRadius: 'var(--radius-sm)', fontSize: 13.5, cursor: 'pointer',
    transition: 'background var(--dur-fast), color var(--dur-fast)',
    color: isActive ? '#fff' : (dark ? 'var(--carbon-300)' : 'var(--text-muted)'),
    background: isActive ? 'var(--accent)' : 'transparent',
    outline: 'none',
  });
  const subItemStyle = (isActive) => ({ ...itemStyle(isActive), paddingLeft: 14, fontSize: 13 });
  const groupHeadStyle = {
    display: 'flex', alignItems: 'center', gap: 11, padding: '9px 12px',
    fontFamily: 'var(--font-mono)', fontSize: 11, letterSpacing: '0.1em',
    textTransform: 'uppercase', color: dark ? 'var(--carbon-400)' : 'var(--text-subtle)',
    cursor: 'pointer', transition: 'color var(--dur-fast)', outline: 'none',
  };

  const NavItem = ({ id, icon, label, sub }) => (
    <button
      style={sub ? subItemStyle(active === id) : itemStyle(active === id)}
      onClick={() => onNavigate(id)}
      aria-current={active === id ? 'page' : undefined}
    >
      <i data-lucide={icon} style={{ width: 17, height: 17, flex: '0 0 auto', color: active === id ? '#fff' : (dark ? 'var(--carbon-400)' : 'var(--text-subtle)') }}></i>
      <span>{label}</span>
    </button>
  );

  const GroupToggle = ({ id, icon, label }) => (
    <button
      style={groupHeadStyle}
      onClick={() => toggle(id)}
      aria-expanded={!collapsed[id]}
    >
      <i data-lucide={icon} style={{ width: 16, height: 16 }}></i>
      <span>{label}</span>
      <i data-lucide="chevron-up" style={{ marginLeft: 'auto', width: 14, height: 14, transform: collapsed[id] ? 'rotate(180deg)' : 'none', transition: 'transform var(--dur-base)' }}></i>
    </button>
  );

  return (
    <aside style={{
      background: dark ? 'var(--carbon-900)' : 'var(--surface)',
      color: dark ? 'var(--carbon-300)' : 'var(--text)',
      display: 'flex', flexDirection: 'column', position: 'sticky', top: 0, height: '100vh',
      borderRight: dark ? '1px solid rgba(255,255,255,0.06)' : '1px solid var(--border)',
    }}>
      <div style={{ padding: '18px 20px 16px', borderBottom: dark ? '1px solid rgba(255,255,255,0.06)' : '1px solid var(--border)' }}>
        <img src="../../assets/No1-logo.png" alt="No1 Tuningfiles" style={{ height: 26, display: 'block', filter: dark ? 'brightness(0) invert(1)' : 'none' }} />
      </div>
      <nav style={{ flex: 1, overflowY: 'auto', padding: '12px 12px 8px', display: 'flex', flexDirection: 'column', gap: 2 }}>
        <NavItem id="dashboard" icon="layout-dashboard" label="Dashboard" />
        <div style={{ margin: '4px 0' }}>
          <GroupToggle id="file" icon="car-front" label="Tuning File" />
          {!collapsed.file && (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 2, paddingLeft: 14, marginLeft: 7, borderLeft: dark ? '1px solid rgba(255,255,255,0.06)' : '1px solid var(--border)' }}>
              <NavItem id="new-order" icon="file-plus-2" label="Neuer Auftrag" sub />
              <NavItem id="orders" icon="history" label="Alle Aufträge" sub />
              <NavItem id="pricelist" icon="list" label="Preisliste" sub />
              <NavItem id="stats" icon="bar-chart-3" label="Statistiken" sub />
            </div>
          )}
        </div>
        <NavItem id="configurator" icon="sliders-horizontal" label="Konfigurator" />
        <NavItem id="dtc" icon="triangle-alert" label="DTC Codes" />
        <div style={{ margin: '4px 0' }}>
          <GroupToggle id="credits" icon="circle-dollar-sign" label="Credits" />
          {!collapsed.credits && (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 2, paddingLeft: 14, marginLeft: 7, borderLeft: dark ? '1px solid rgba(255,255,255,0.06)' : '1px solid var(--border)' }}>
              <NavItem id="buy-credits" icon="shopping-cart" label="Kaufen" sub />
              <NavItem id="invoices" icon="receipt" label="Rechnungen" sub />
            </div>
          )}
        </div>
        <NavItem id="tickets" icon="ticket" label="Tickets" />
        <NavItem id="profile" icon="user" label="Profil" />
      </nav>
      <div style={{ margin: '8px 12px 16px' }}>
        <NavItem id="logout" icon="log-out" label="Abmelden" />
      </div>
    </aside>
  );
}
window.Sidebar = Sidebar;
