import React from 'react';
import { Logo } from '../../components/core/Logo.jsx';
import { Button } from '../../components/core/Button.jsx';
import { Badge } from '../../components/core/Badge.jsx';
import { Select } from '../../components/forms/Select.jsx';
import { StatTile } from '../../components/data/StatTile.jsx';
import { Card } from '../../components/data/Card.jsx';

/* Lucide helper — converts <i data-lucide> after every commit. */
export function useLucide() {
  React.useLayoutEffect(() => { if (window.lucide) window.lucide.createIcons(); });
}
const I = (n, p = {}) => <i data-lucide={n} {...p} />;

/* Carbon photo placeholder — stands in for engine/vehicle photography. */
function Media({ label, h = 'auto', icon = 'image', ratio }) {
  return (
    <div className="mk-media grid-surface" style={{ height: h, aspectRatio: ratio }}>
      <span className="mk-media__ico">{I(icon)}</span>
      <span className="mk-media__lbl">{label}</span>
    </div>
  );
}

function Header({ theme, onToggleTheme }) {
  return (
    <header className="mk-header">
      <div className="mk-wrap mk-header__in">
        <Logo size="md" assetBase="../../" solidMark={theme === 'dark'} href="#" />
        <nav className="mk-nav">
          <a href="#">Tuning-Dateien</a>
          <a href="#">Fahrzeuge</a>
          <a href="#">Tools</a>
          <a href="#">Über uns</a>
        </nav>
        <div className="mk-header__act">
          <button className="mk-icobtn" aria-label="Theme wechseln" onClick={onToggleTheme}>
            {I(theme === 'dark' ? 'sun' : 'moon')}
          </button>
          <button className="mk-icobtn" aria-label="Warenkorb">{I('shopping-cart')}</button>
          <Button size="sm" variant="secondary" iconLeft={I('user')}>Login</Button>
        </div>
      </div>
    </header>
  );
}

function Hero() {
  const [marke, setMarke] = React.useState('');
  return (
    <section className="mk-hero grid-surface">
      <div className="mk-wrap mk-hero__in">
        <div className="mk-hero__copy">
          <span className="eyebrow">Seit 2010 · über 8.000 Fahrzeuge</span>
          <h1 className="mk-hero__h1">Mehr Leistung.<br /><span className="mk-accent">Aus deiner Datei.</span></h1>
          <p className="mk-hero__sub">Optimierte ECU-Kennfelder — auf dem Prüfstand abgestimmt, EU-konform und in der Regel innerhalb von 30&nbsp;Minuten geliefert.</p>
          <div className="mk-finder">
            <span className="mk-finder__t">{I('search')} Fahrzeug finden</span>
            <div className="mk-finder__row">
              <Select placeholder="Marke" value={marke} onChange={(e) => setMarke(e.target.value)}
                      options={['Audi', 'BMW', 'Mercedes-Benz', 'VW', 'Porsche']} />
              <Select placeholder="Modell" options={['A4', 'A6', 'Q5']} disabled={!marke} />
              <Select placeholder="Motor" options={['2.0 TDI', '3.0 TDI']} disabled={!marke} />
              <Button iconRight={I('arrow-right')}>Suchen</Button>
            </div>
          </div>
        </div>
        <Media label="HERO // 2.0 TDI auf dem Prüfstand" icon="gauge" h="100%" />
      </div>
    </section>
  );
}

function StatStrip() {
  return (
    <section className="mk-stats mk-wrap">
      <StatTile label="Ø Mehrleistung" value="+34" unit="PS" gain />
      <StatTile label="Ø Drehmoment" value="+71" unit="Nm" />
      <StatTile label="Lieferung" value="30" unit="Min" />
      <StatTile label="Fahrzeuge" value="8.000" unit="+" />
    </section>
  );
}

const STAGES = [
  { n: '1', title: 'Stage 1', desc: 'Reine Software-Optimierung. Serie auf Optimum, ohne Hardware.', ps: '+38', nm: '+72', price: 'ab 199 €', feat: true },
  { n: '2', title: 'Stage 2', desc: 'Software plus Hardware (Downpipe, Ladeluftkühler). Maximaler Zuwachs.', ps: '+62', nm: '+110', price: 'ab 349 €' },
  { n: 'E', title: 'Eco-Tuning', desc: 'Auf Verbrauch optimiert — mehr Drehmoment im Teillastbereich.', ps: '+18', nm: '+55', price: 'ab 179 €' },
];

function Stages() {
  return (
    <section className="mk-wrap mk-stages">
      <div className="mk-sec-head">
        <span className="eyebrow">Tuning-Stufen</span>
        <h2>Wähle deine Stufe</h2>
      </div>
      <div className="mk-stage-grid">
        {STAGES.map((s) => (
          <Card key={s.n} featured={s.feat}
                badge={<Badge solid={s.feat} tone={s.feat ? undefined : 'neutral'}>{`Stage ${s.n}`}</Badge>}
                title={s.title}
                footer={<><span className="mk-price">{s.price}</span><Button size="sm" variant={s.feat ? 'primary' : 'secondary'} iconRight={I('arrow-right')}>Wählen</Button></>}>
            <p className="mk-stage-desc">{s.desc}</p>
            <div className="mk-stage-figs">
              <StatTile label="Leistung" value={s.ps} unit="PS" gain />
              <StatTile label="Drehmoment" value={s.nm} unit="Nm" />
            </div>
          </Card>
        ))}
      </div>
    </section>
  );
}

const TRUST = [
  { i: 'shield-check', t: 'TÜV-konform', d: 'Abgestimmt auf Serienzuverlässigkeit, auf Wunsch mit Gutachten.' },
  { i: 'gauge', t: 'Prüfstand-getestet', d: 'Jedes Kennfeld auf dem Leistungsprüfstand validiert.' },
  { i: 'timer', t: '30-Min-Lieferung', d: 'Datei hochladen, optimierte Datei zurückerhalten — meist sofort.' },
  { i: 'headset', t: 'Support vom Profi', d: 'Direkter Draht zu erfahrenen Tunern, kein Callcenter.' },
];

function Trust() {
  return (
    <section className="mk-trust grid-surface">
      <div className="mk-wrap">
        <div className="mk-sec-head"><span className="eyebrow">Warum Chiptuningfile</span><h2 className="mk-on-dark">Engineering, kein Marketing</h2></div>
        <div className="mk-trust-grid">
          {TRUST.map((x) => (
            <div className="mk-trust-item" key={x.t}>
              <span className="mk-trust-ico">{I(x.i)}</span>
              <h4>{x.t}</h4>
              <p>{x.d}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

function CTA() {
  return (
    <section className="mk-wrap mk-cta">
      <div className="mk-cta__box">
        <div>
          <span className="eyebrow">Bereit?</span>
          <h2>Lade deine Datei hoch</h2>
          <p>Originaldatei auslesen, hochladen, optimierte Version zurückerhalten.</p>
        </div>
        <div className="mk-cta__act">
          <Button size="lg" iconLeft={I('upload')}>Datei hochladen</Button>
          <Button size="lg" variant="secondary" iconRight={I('arrow-right')}>Fahrzeug wählen</Button>
        </div>
      </div>
    </section>
  );
}

function Footer({ theme }) {
  return (
    <footer className="mk-footer">
      <div className="mk-wrap mk-footer__in">
        <div className="mk-footer__brand">
          <Logo size="sm" assetBase="../../" solidMark={theme === 'dark'} />
          <p>Optimierte ECU-Tuning-Dateien für PKW, LKW, Motorrad &amp; mehr.</p>
        </div>
        <div className="mk-footer__cols">
          <div><h5>Produkte</h5><a href="#">Tuning-Dateien</a><a href="#">Stage 1 / 2</a><a href="#">Eco-Tuning</a><a href="#">Tools</a></div>
          <div><h5>Service</h5><a href="#">Fahrzeugsuche</a><a href="#">Händlerbereich</a><a href="#">Support</a><a href="#">FAQ</a></div>
          <div><h5>Unternehmen</h5><a href="#">Über uns</a><a href="#">Kontakt</a><a href="#">Impressum</a><a href="#">Datenschutz</a></div>
        </div>
      </div>
      <div className="mk-wrap mk-footer__bar"><span>© 2026 Chiptuningfile.de</span><span className="mk-mono">ECU PERFORMANCE FILES</span></div>
    </footer>
  );
}

export function MarketingSite({ initialTheme = 'dark' } = {}) {
  const [theme, setTheme] = React.useState(initialTheme);
  useLucide();
  return (
    <div className="mk-root" data-theme={theme}>
      <Header theme={theme} onToggleTheme={() => setTheme(theme === 'dark' ? 'light' : 'dark')} />
      <Hero />
      <StatStrip />
      <Stages />
      <Trust />
      <CTA />
      <Footer theme={theme} />
    </div>
  );
}
