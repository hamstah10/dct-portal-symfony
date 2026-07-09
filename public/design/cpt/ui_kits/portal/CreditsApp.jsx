import React from 'react';
import { Button } from '../../components/core/Button.jsx';
import { Badge } from '../../components/core/Badge.jsx';
import { Table } from '../../components/data/Table.jsx';
import { useLucide } from '../marketing/MarketingSite.jsx';
import { PortalSidebar, PortalTopBar } from './PortalApp.jsx';

const I = (n, p = {}) => <i data-lucide={n} {...p} />;
const eur = (n) => n.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';

const PACKS = [
  { credits: 10, net: 12.0, gross: 14.28, per: null, note: '10 Credits' },
  { credits: 10, net: 60.0, gross: 71.40, per: '60€', note: '10 OLSx-Credits' },
  { credits: 50, net: 58.0, gross: 69.02, per: null, note: '50 Credits' },
  { credits: 100, net: 115.0, gross: 136.85, per: '115€', note: '100 Credits' },
  { credits: 500, net: 550.0, gross: 654.50, per: '110€', note: '500 Credits' },
  { credits: 1000, net: 1050.0, gross: 1249.50, per: '105€', note: '1000 Credits' },
  { credits: 2000, net: 2000.0, gross: 2380.00, per: '100€', note: '2000 Credits' },
  { credits: 3000, net: 2850.0, gross: 3391.50, per: '95€', note: '3000 Credits' },
  { credits: 4000, net: 3600.0, gross: 4284.00, per: '90€', note: '4000 Credits' },
  { credits: 5000, net: 4250.0, gross: 5057.50, per: '85€', note: '5000 Credits' },
];

const BUCHUNGEN = [
  { datum: '22.05.2026, 03:19', typ: 'Verbrauch', details: 'Auftrag A-000144 | Audi A6 C7 · 2011 3.0 TDI EU6 · 218 PS | Stage 1 | 150 Credits', zahlung: '—', credits: -150, saldo: 72140 },
  { datum: '22.05.2026, 03:18', typ: 'Verbrauch', details: 'Auftrag A-000143 | Audi A6 2011 · C7 3.0 TDI EU6 (FWD) 218 PS · Diesel | Stage 1 | 200 Credits', zahlung: '—', credits: -200, saldo: 72290 },
  { datum: '23.04.2026, 10:09', typ: 'Verbrauch', details: 'Auftrag A-000142 | Audi A6 2011 · C7 3.0 TDI EU6 (FWD) 218 PS · Diesel | Stage 1 | 150 Credits', zahlung: '—', credits: -150, saldo: 72490 },
  { datum: '21.04.2026, 07:17', typ: 'Aufladung', details: 'Zahlungseingang bestätigt (700 Credits) · Ursprung Buchung #97', zahlung: 'Vorkasse', credits: 700, saldo: 72640 },
];

function Pack({ p, selected, onSelect }) {
  return (
    <div className={`cr-pack${selected ? ' is-sel' : ''}`} onClick={onSelect}>
      <div className="cr-pack__num">{p.credits.toLocaleString('de-DE')}</div>
      <div className="cr-pack__unit">Credits</div>
      <div className="cr-pack__price">{eur(p.net)}</div>
      <div className="cr-pack__gross">{eur(p.gross)} inkl. MwSt.</div>
      <div className="cr-pack__rule" />
      <div className="cr-pack__eig">Eigenschaften</div>
      <div className="cr-pack__feat">{I('check')}<span>{p.note}</span></div>
      {p.per
        ? <span className="cr-pack__per">{I('tag')}{p.per} / File</span>
        : <span className="cr-pack__per cr-pack__per--muted">{I('tag')}Staffelpreis</span>}
      <Button block size="sm" variant={selected ? 'primary' : 'secondary'} onClick={(e) => { e.stopPropagation(); onSelect(); }}>Jetzt kaufen</Button>
    </div>
  );
}

export function CreditsApp({ initialTheme = 'light' } = {}) {
  const [theme, setTheme] = React.useState(initialTheme);
  const [sel, setSel] = React.useState(null);
  useLucide();

  const cols = [
    { key: 'datum', header: 'Datum', render: (r) => <span className="cr-mono">{r.datum}</span> },
    { key: 'typ', header: 'Typ', render: (r) => <Badge tone={r.credits < 0 ? 'neutral' : 'success'}>{r.typ}</Badge> },
    { key: 'details', header: 'Details' },
    { key: 'zahlung', header: 'Zahlung', render: (r) => <span className="cr-muted">{r.zahlung}</span> },
    { key: 'credits', header: 'Credits', numeric: true, render: (r) => <b className={r.credits < 0 ? 'cr-neg' : 'cr-pos'}>{r.credits > 0 ? '+' : ''}{r.credits}</b> },
    { key: 'saldo', header: 'Saldo', numeric: true, render: (r) => <span className="cr-saldo">{r.saldo.toLocaleString('de-DE')}</span> },
  ];

  return (
    <div className="pt-root" data-theme={theme}>
      <PortalSidebar theme={theme} active="Kaufen" />
      <div className="pt-main">
        <PortalTopBar theme={theme} onToggle={() => setTheme(theme === 'dark' ? 'light' : 'dark')} />
        <div className="pt-scroll cr-scroll">

          <div className="cr-head">
            <div>
              <h1>{I('shopping-cart')} Credits kaufen</h1>
              <p>Tuning Credits erwerben und sofort nutzen.</p>
            </div>
            <div className="cr-balance">
              <span className="cr-balance__ico">{I('circle-dollar-sign')}</span>
              <div>
                <span className="cr-balance__lbl">Dein Guthaben</span>
                <div className="cr-balance__val">72.140 <em>Credits</em></div>
              </div>
            </div>
          </div>

          <div className="cr-grid">
            {PACKS.map((p, i) => <Pack key={i} p={p} selected={sel === i} onSelect={() => setSel(i)} />)}
          </div>

          <div className="cr-row2">
            <div className="pt-card cr-select">
              {I('shopping-cart')}
              <span>{sel == null ? 'Bitte ein Credit-Paket auswählen' : `${PACKS[sel].credits.toLocaleString('de-DE')} Credits — ${eur(PACKS[sel].net)}`}</span>
            </div>
            <div className="pt-card cr-vorkasse">
              <div className="cr-vorkasse__txt">
                <span className="cr-eig">{I('building-2')} Rechnung auf Vorkasse</span>
                <p>Individuelle Rechnung mit gewünschtem Betrag anfordern.</p>
              </div>
              <div className="cr-vorkasse__act">
                <input placeholder="z.B. 200" />
                <Button variant="secondary" iconLeft={I('file-text')}>Rechnung anfordern</Button>
              </div>
            </div>
          </div>

          <section className="pt-card cr-buchungen">
            <div className="cr-buchungen__head">
              <div>
                <h2>Buchungen</h2>
                <p>Alle Credit-Buchungen deines Kontos.</p>
              </div>
              <Badge tone="neutral">78</Badge>
            </div>
            <Table columns={cols} rows={BUCHUNGEN} />
          </section>

        </div>
      </div>
    </div>
  );
}
