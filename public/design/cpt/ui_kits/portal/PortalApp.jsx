import React from 'react';
import { Logo } from '../../components/core/Logo.jsx';
import { Button } from '../../components/core/Button.jsx';
import { Badge } from '../../components/core/Badge.jsx';
import { IconButton } from '../../components/core/IconButton.jsx';
import { Select } from '../../components/forms/Select.jsx';
import { FileUpload } from '../../components/data/FileUpload.jsx';
import { useLucide } from '../marketing/MarketingSite.jsx';

const I = (n, p = {}) => <i data-lucide={n} {...p} />;

/* ---- Sidebar ---- */
const NAV = [
  { type: 'item', icon: 'layout-dashboard', label: 'Dashboard' },
  { type: 'group', icon: 'car-front', label: 'Tuning File', open: true, children: [
    { icon: 'file-plus-2', label: 'Neuer Auftrag' },
    { icon: 'history', label: 'Alle Aufträge' },
    { icon: 'list', label: 'Preisliste' },
    { icon: 'bar-chart-3', label: 'Statistiken' },
  ] },
  { type: 'item', icon: 'sliders-horizontal', label: 'Konfigurator' },
  { type: 'item', icon: 'triangle-alert', label: 'DTC Codes' },
  { type: 'group', icon: 'circle-dollar-sign', label: 'Credits', open: true, children: [
    { icon: 'shopping-cart', label: 'Kaufen' },
    { icon: 'receipt', label: 'Rechnungen' },
  ] },
  { type: 'item', icon: 'ticket', label: 'Tickets' },
  { type: 'item', icon: 'mail', label: 'E-Mail Templates' },
  { type: 'item', icon: 'user', label: 'Profil' },
];

export function PortalSidebar({ theme, active = 'Neuer Auftrag' }) {
  return (
    <aside className="pt-side">
      <div className="pt-side__logo"><Logo size="sm" assetBase="../../" tagline="" solidMark={theme === 'dark'} href="#" /></div>
      <nav className="pt-nav">
        {NAV.map((n, i) => n.type === 'item' ? (
          <a key={i} className={`pt-navitem${n.label === active ? ' is-active' : ''}`} href="#">{I(n.icon)}<span>{n.label}</span></a>
        ) : (
          <div key={i} className="pt-navgroup">
            <div className="pt-navgroup__h">{I(n.icon)}<span>{n.label}</span>{I('chevron-up', { className: 'pt-navgroup__chev' })}</div>
            <div className="pt-navgroup__kids">
              {n.children.map((c, j) => (
                <a key={j} className={`pt-navitem pt-navitem--sub${c.label === active ? ' is-active' : ''}`} href="#">{I(c.icon)}<span>{c.label}</span></a>
              ))}
            </div>
          </div>
        ))}
      </nav>
      <a className="pt-navitem pt-side__out" href="#">{I('log-out')}<span>Abmelden</span></a>
    </aside>
  );
}

/* ---- Top bar ---- */
export function PortalTopBar({ theme, onToggle }) {
  return (
    <header className="pt-top">
      <label className="pt-search">
        {I('search')}
        <input placeholder="Globale Suche, Aktionen und Dateien…" />
        <kbd>CMD K</kbd>
      </label>
      <div className="pt-top__act">
        <Button size="sm" iconLeft={I('file-text')}>Auftrag</Button>
        <button className="pt-credits">{I('circle-dollar-sign')}<span>Credits kaufen <b>(72.140)</b></span></button>
        <IconButton label="Theme" variant="ghost" onClick={onToggle}>{I(theme === 'dark' ? 'sun' : 'moon')}</IconButton>
        <button className="pt-bell" aria-label="Benachrichtigungen">{I('bell')}<span className="pt-bell__n">8</span></button>
        <button className="pt-lang">{I('globe')}<span>DE</span></button>
        <span className="pt-avatar">AS</span>
      </div>
    </header>
  );
}

/* ---- Stepper ---- */
const STEPS = [
  { icon: 'upload', label: 'File & Lesegerät' },
  { icon: 'car-front', label: 'Fahrzeug + Tuning' },
  { icon: 'circle-check-big', label: 'Übersicht' },
];
function Stepper({ current }) {
  return (
    <div className="pt-stepper">
      {STEPS.map((s, i) => (
        <React.Fragment key={i}>
          <div className={`pt-step${i === current ? ' is-active' : ''}${i < current ? ' is-done' : ''}`}>
            <span className="pt-step__ico">{I(s.icon)}</span>
            <span className="pt-step__lbl">{s.label}</span>
          </div>
          {i < STEPS.length - 1 && <span className={`pt-step__line${i < current ? ' is-done' : ''}`} />}
        </React.Fragment>
      ))}
    </div>
  );
}

/* ---- Segmented option group ---- */
function SegGroup({ icon, label, options, value, onChange }) {
  return (
    <div className="pt-seg">
      <div className="pt-seg__lbl">{I(icon)}<span>{label}</span></div>
      <div className="pt-seg__opts" style={{ gridTemplateColumns: `repeat(${options.length}, 1fr)` }}>
        {options.map((o) => (
          <button key={o.value} className={`pt-opt${value === o.value ? ' is-on' : ''}`} onClick={() => onChange(o.value)}>
            {o.icon && I(o.icon)}<span>{o.label}</span>
            {o.price && <em className="pt-opt__price">{o.price}</em>}
          </button>
        ))}
      </div>
    </div>
  );
}

export function PortalApp({ initialTheme = 'light' } = {}) {
  const [theme, setTheme] = React.useState(initialTheme);
  const [cfg, setCfg] = React.useState({
    method: 'obd', read: 'virtuell', ms: 'slave', prio: 'normal', winols: 'match',
  });
  const set = (k) => (v) => setCfg((c) => ({ ...c, [k]: v }));
  useLucide();
  return (
    <div className="pt-root" data-theme={theme}>
      <PortalSidebar theme={theme} active="Neuer Auftrag" />
      <div className="pt-main">
        <PortalTopBar theme={theme} onToggle={() => setTheme(theme === 'dark' ? 'light' : 'dark')} />
        <div className="pt-scroll">
          <div className="pt-card pt-stepcard"><Stepper current={0} /></div>

          <div className="pt-head">
            <h1>File &amp; Lesegerät</h1>
            <span className="pt-step-tag">Step 01</span>
          </div>

          <div className="pt-grid">
            <section className="pt-card pt-options">
              <div className="pt-field">
                <div className="pt-seg__lbl">{I('wrench')}<span>Tuning Tool / Lesegerät</span></div>
                <Select options={[{ value: 'autotuner', label: 'Autotuner' }, { value: 'flash', label: 'Flash-Interface V3' }]} placeholder="Tool" />
              </div>
              <SegGroup icon="scan-line" label="Lesemethode" value={cfg.method} onChange={set('method')}
                options={[{ value: 'obd', label: 'OBD' }, { value: 'bench', label: 'Bench' }, { value: 'boot', label: 'Boot' }]} />
              <SegGroup icon="book-open" label="Leseart" value={cfg.read} onChange={set('read')}
                options={[{ value: 'virtuell', label: 'Virtuell', icon: 'cloud-download' }, { value: 'full', label: 'Full Read', icon: 'hard-drive' }]} />
              <SegGroup icon="git-branch" label="Master / Slave" value={cfg.ms} onChange={set('ms')}
                options={[{ value: 'slave', label: 'Slave', icon: 'link' }, { value: 'master', label: 'Master', icon: 'crown' }]} />
              <SegGroup icon="timer" label="Priorität" value={cfg.prio} onChange={set('prio')}
                options={[{ value: 'normal', label: 'Normal' }, { value: 'express', label: 'Express', price: '+49€' }, { value: 'sofort', label: 'Sofort', price: '+99€' }]} />
              <SegGroup icon="zap" label="WinOLS Test-Modus" value={cfg.winols} onChange={set('winols')}
                options={[{ value: 'match', label: 'Match positiv (Auto-Fertig)' }, { value: 'nomatch', label: 'Kein Match (manuell/offen)' }]} />
            </section>

            <section className="pt-card pt-drop">
              <FileUpload title="Datei hierher ziehen" hint=".bin .ori .mod .ecu .hex .s19 .bkp .slave" accept=".bin,.ori,.mod,.ecu,.hex,.s19,.bkp,.slave" />
            </section>
          </div>

          <section className="pt-card pt-comment">
            <div className="pt-seg__lbl">{I('message-square')}<span>Kommentar (optional)</span></div>
            <textarea rows={4} placeholder="Zusätzliche Hinweise, Wünsche oder Informationen…" />
          </section>

          <div className="pt-foot">
            <Button size="lg" iconRight={I('arrow-right')}>Weiter zu Schritt 2</Button>
          </div>
        </div>
      </div>
    </div>
  );
}
