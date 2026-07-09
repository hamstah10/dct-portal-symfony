import React from 'react';
import { Logo } from '../../components/core/Logo.jsx';
import { Button } from '../../components/core/Button.jsx';
import { Badge } from '../../components/core/Badge.jsx';
import { Tag } from '../../components/core/Tag.jsx';
import { IconButton } from '../../components/core/IconButton.jsx';
import { Select } from '../../components/forms/Select.jsx';
import { Input } from '../../components/forms/Input.jsx';
import { StatTile } from '../../components/data/StatTile.jsx';
import { Tabs } from '../../components/data/Tabs.jsx';
import { Breadcrumb } from '../../components/navigation/Breadcrumb.jsx';
import { useLucide } from '../marketing/MarketingSite.jsx';

const I = (n, p = {}) => <i data-lucide={n} {...p} />;

const RESULTS = [
  { id: 'a', motor: '2.0 TDI', code: 'EA288', ps: 150, tps: 188, nm: 340, tnm: 410, fuel: 'Diesel', years: '2015–2020' },
  { id: 'b', motor: '2.0 TDI', code: 'EA288 Evo', ps: 190, tps: 228, nm: 400, tnm: 470, fuel: 'Diesel', years: '2020–' },
  { id: 'c', motor: '1.4 TSI', code: 'EA211', ps: 150, tps: 180, nm: 250, tnm: 310, fuel: 'Benzin', years: '2017–' },
  { id: 'd', motor: '3.0 TDI', code: 'EA897', ps: 272, tps: 334, nm: 600, tnm: 720, fuel: 'Diesel', years: '2016–' },
];

function TopBar({ theme, onToggle, q, setQ }) {
  return (
    <header className="ct-top">
      <div className="ct-top__in">
        <Logo size="sm" assetBase="../../" solidMark={theme === 'dark'} href="#" />
        <div className="ct-search">
          <Input placeholder="Fahrzeug, Motor oder ECU suchen…" value={q}
                 onChange={(e) => setQ(e.target.value)} prefix={I('search')} />
        </div>
        <div className="ct-top__act">
          <IconButton label="Theme" variant="ghost" onClick={onToggle}>{I(theme === 'dark' ? 'sun' : 'moon')}</IconButton>
          <IconButton label="Konto" variant="ghost">{I('user')}</IconButton>
          <Button size="sm" iconLeft={I('upload')}>Datei hochladen</Button>
        </div>
      </div>
    </header>
  );
}

function Filters({ marke, setMarke }) {
  return (
    <aside className="ct-filters">
      <div className="ct-panel">
        <h3 className="ct-panel__h">Fahrzeug wählen</h3>
        <div className="ct-fields">
          <Select label="Marke" placeholder="Marke" value={marke} onChange={(e) => setMarke(e.target.value)}
                  options={['Audi', 'BMW', 'Mercedes-Benz', 'VW', 'Seat', 'Škoda']} />
          <Select label="Modell" placeholder="Modell" options={['A4', 'A5', 'A6', 'Q5']} disabled={!marke} />
          <Select label="Baujahr" placeholder="Baujahr" options={['2024', '2022', '2020', '2018', '2016']} disabled={!marke} />
          <Select label="Motor" placeholder="Motorisierung" options={['2.0 TDI', '2.0 TFSI', '3.0 TDI']} disabled={!marke} />
        </div>
      </div>
      <div className="ct-panel">
        <h3 className="ct-panel__h">Filter</h3>
        <div className="ct-facets">
          <span className="ct-facet-lbl">Kraftstoff</span>
          <div className="ct-tags"><Tag active onRemove={() => {}}>Diesel</Tag><Tag>Benzin</Tag><Tag>Hybrid</Tag></div>
          <span className="ct-facet-lbl">Maßnahme</span>
          <div className="ct-tags"><Tag>Stage 1</Tag><Tag>Stage 2</Tag><Tag>EGR/DPF</Tag><Tag>AdBlue</Tag></div>
        </div>
      </div>
    </aside>
  );
}

function ResultRow({ r, onOpen }) {
  return (
    <button className="ct-row" onClick={() => onOpen(r)}>
      <div className="ct-row__veh">
        <span className="ct-row__motor">{r.motor}</span>
        <span className="ct-row__code">{r.code} · {r.years}</span>
      </div>
      <Badge tone={r.fuel === 'Diesel' ? 'info' : 'warning'}>{r.fuel}</Badge>
      <div className="ct-row__fig"><span className="ct-row__k">Serie</span><b>{r.ps} PS</b></div>
      <div className="ct-row__fig ct-row__fig--gain"><span className="ct-row__k">Stage 1</span><b>{r.tps} PS</b></div>
      <div className="ct-row__delta">{I('trending-up')} +{r.tps - r.ps}</div>
      <span className="ct-row__go">{I('chevron-right')}</span>
    </button>
  );
}

function Browse({ marke, setMarke, onOpen, count }) {
  return (
    <div className="ct-grid">
      <Filters marke={marke} setMarke={setMarke} />
      <main className="ct-results">
        <div className="ct-results__head">
          <div>
            <span className="eyebrow">{count} Motorisierungen</span>
            <h2>Audi A4 (B9)</h2>
          </div>
          <Select placeholder="Sortierung" options={['Mehrleistung', 'Hubraum', 'Baujahr']} />
        </div>
        <div className="ct-rows">
          {RESULTS.map((r) => <ResultRow key={r.id} r={r} onOpen={onOpen} />)}
        </div>
      </main>
    </div>
  );
}

function Detail({ r, onBack }) {
  const [stage, setStage] = React.useState('s1');
  const tps = stage === 's1' ? r.tps : r.tps + 26;
  const tnm = stage === 's1' ? r.tnm : r.tnm + 40;
  return (
    <div className="ct-detail">
      <div className="ct-detail__bar">
        <Button variant="ghost" size="sm" iconLeft={I('arrow-left')} onClick={onBack}>Zurück</Button>
      </div>
      <div className="ct-detail__head">
        <div>
          <span className="eyebrow">Audi A4 (B9) · {r.fuel}</span>
          <h1>{r.motor} <span className="ct-muted">{r.code}</span></h1>
        </div>
        <Badge solid>Verfügbar</Badge>
      </div>
      <div className="ct-detail__grid">
        <div className="ct-dyno grid-surface">
          <div className="ct-dyno__top"><span className="ct-mono">LEISTUNGSDIAGRAMM</span><span className="ct-mono ct-muted">PS / Nm über Drehzahl</span></div>
          <div className="ct-dyno__plot">
            <svg viewBox="0 0 400 200" preserveAspectRatio="none" className="ct-dyno__svg">
              <path d="M0,185 C120,170 240,70 400,40" fill="none" stroke="var(--carbon-500)" stroke-width="2" stroke-dasharray="5 4"/>
              <path d="M0,180 C120,150 240,40 400,18" fill="none" stroke="var(--accent)" stroke-width="3"/>
            </svg>
            <div className="ct-dyno__legend"><span><i className="dot dot--serie"></i>Serie</span><span><i className="dot dot--tuned"></i>Stage</span></div>
          </div>
        </div>
        <div className="ct-detail__side">
          <Tabs value={stage} onChange={setStage} tabs={[{ id: 's1', label: 'Stage 1' }, { id: 's2', label: 'Stage 2' }]} />
          <div className="ct-figs">
            <StatTile label="Leistung Serie" value={r.ps} unit="PS" dark />
            <StatTile label="Leistung Stage" value={tps} unit="PS" gain dark />
            <StatTile label="Drehmoment Serie" value={r.nm} unit="Nm" dark />
            <StatTile label="Drehmoment Stage" value={tnm} unit="Nm" gain dark />
          </div>
          <div className="ct-order">
            <div><span className="ct-mono ct-muted">Mehrleistung</span><div className="ct-order__big">+{tps - r.ps} PS · +{tnm - r.nm} Nm</div></div>
            <Button block size="lg" iconRight={I('arrow-right')}>Für 199 € konfigurieren</Button>
            <p className="ct-order__note">{I('shield-check')} Auf dem Prüfstand abgestimmt · EU-konform · 30-Min-Lieferung</p>
          </div>
        </div>
      </div>
    </div>
  );
}

export function CatalogApp({ initialTheme = 'dark' } = {}) {
  const [theme, setTheme] = React.useState(initialTheme);
  const [marke, setMarke] = React.useState('Audi');
  const [q, setQ] = React.useState('');
  const [sel, setSel] = React.useState(null);
  useLucide();
  return (
    <div className="ct-root" data-theme={theme}>
      <TopBar theme={theme} onToggle={() => setTheme(theme === 'dark' ? 'light' : 'dark')} q={q} setQ={setQ} />
      <div className="ct-wrap">
        <Breadcrumb items={[{ label: 'Start', href: '#' }, { label: 'PKW', href: '#' }, { label: 'Audi', href: '#' }, sel ? `A4 ${sel.motor}` : 'A4 (B9)']} />
        {sel
          ? <Detail r={sel} onBack={() => setSel(null)} />
          : <Browse marke={marke} setMarke={setMarke} onOpen={setSel} count={RESULTS.length} />}
      </div>
    </div>
  );
}
