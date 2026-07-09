import React from 'react';
import { Logo } from '../../components/core/Logo.jsx';
import { Button } from '../../components/core/Button.jsx';
import { Badge } from '../../components/core/Badge.jsx';
import { IconButton } from '../../components/core/IconButton.jsx';
import { Radio } from '../../components/forms/Radio.jsx';
import { Card } from '../../components/data/Card.jsx';
import { Breadcrumb } from '../../components/navigation/Breadcrumb.jsx';
import { useLucide } from '../marketing/MarketingSite.jsx';

const I = (n, p = {}) => <i data-lucide={n} {...p} />;

const PRODUCTS = [
  { id: 'autotuner', name: 'Autotuner Master', cat: 'Lese-/Schreibtool', price: 2490, tag: 'Bestseller', icon: 'cpu', desc: 'OBD-, Bench- und Boot-Modus in einem Gerät. Inkl. 12 Monate Updates.' },
  { id: 'kess', name: 'Flash-Interface V3', cat: 'OBD-Tool', price: 890, icon: 'usb', desc: 'Schnelles OBD-Flashen für gängige Steuergeräte.' },
  { id: 'bench', name: 'Bench-Kabelsatz Pro', cat: 'Zubehör', price: 320, icon: 'cable', desc: '40-teiliges Set für Bench- und Boot-Anschluss.' },
  { id: 'pds', name: 'Prüfstand-Mietzeit', cat: 'Service', price: 180, icon: 'gauge', desc: '1 Stunde Leistungsprüfstand inkl. Bediener.' },
  { id: 'credits', name: '10er Datei-Credits', cat: 'Guthaben', price: 450, tag: 'Spar-Set', icon: 'coins', desc: '10 Tuning-Dateien aus unserem Katalog, jederzeit abrufbar.' },
  { id: 'license', name: 'Händler-Lizenz', cat: 'Software', price: 990, icon: 'shield-check', desc: 'Jahreslizenz für den Händlerbereich inkl. API-Zugang.' },
];
const eur = (n) => n.toLocaleString('de-DE') + ' €';

function TopBar({ theme, onToggle, cartCount, onOpenCart }) {
  return (
    <header className="sh-top">
      <div className="sh-top__in">
        <Logo size="sm" assetBase="../../" solidMark={theme === 'dark'} href="#" />
        <nav className="sh-nav"><a href="#">Tools</a><a href="#">Zubehör</a><a href="#">Credits</a><a href="#">Service</a></nav>
        <div className="sh-top__act">
          <IconButton label="Theme" variant="ghost" onClick={onToggle}>{I(theme === 'dark' ? 'sun' : 'moon')}</IconButton>
          <button className="sh-cart" onClick={onOpenCart} aria-label="Warenkorb">
            {I('shopping-cart')}
            {cartCount > 0 && <span className="sh-cart__n">{cartCount}</span>}
          </button>
        </div>
      </div>
    </header>
  );
}

function ProductCard({ p, onOpen, onAdd }) {
  return (
    <Card interactive featured={!!p.tag}
          media={<div className="sh-media grid-surface">{I(p.icon)}</div>}
          badge={p.tag ? <Badge solid>{p.tag}</Badge> : <Badge tone="neutral">{p.cat}</Badge>}
          title={p.name}
          footer={<><span className="sh-price">{eur(p.price)}</span><Button size="sm" onClick={(e) => { e.stopPropagation(); onAdd(p); }} iconLeft={I('plus')}>Add</Button></>}>
      <p className="sh-desc" onClick={() => onOpen(p)}>{p.desc}</p>
    </Card>
  );
}

function Grid({ onOpen, onAdd }) {
  return (
    <>
      <div className="sh-hero grid-surface">
        <div>
          <span className="eyebrow">Tools &amp; Hardware</span>
          <h1>Werkstatt-Equipment</h1>
          <p>Lese- und Schreibtools, Kabelsätze und Datei-Credits — für Profis, die selbst flashen.</p>
        </div>
      </div>
      <div className="sh-grid">
        {PRODUCTS.map((p) => <ProductCard key={p.id} p={p} onOpen={onOpen} onAdd={onAdd} />)}
      </div>
    </>
  );
}

function Detail({ p, onBack, onAdd }) {
  const [variant, setVariant] = React.useState('master');
  return (
    <div className="sh-detail">
      <div className="sh-detail__bar"><Button variant="ghost" size="sm" iconLeft={I('arrow-left')} onClick={onBack}>Zum Shop</Button></div>
      <div className="sh-detail__grid">
        <div className="sh-media sh-media--lg grid-surface">{I(p.icon)}</div>
        <div className="sh-detail__info">
          <span className="eyebrow">{p.cat}</span>
          <h1>{p.name}</h1>
          <p className="sh-detail__desc">{p.desc}</p>
          <div className="sh-variants">
            <span className="sh-vlabel">Ausführung</span>
            <Radio name="v" value="master" label="Master — alle Protokolle" checked={variant === 'master'} onChange={() => setVariant('master')} />
            <Radio name="v" value="slave" label="Slave — gebunden an Master" checked={variant === 'slave'} onChange={() => setVariant('slave')} />
          </div>
          <div className="sh-buy">
            <span className="sh-price sh-price--lg">{eur(p.price)}</span>
            <Button size="lg" iconLeft={I('shopping-cart')} onClick={() => onAdd(p)}>In den Warenkorb</Button>
          </div>
          <ul className="sh-feats">
            <li>{I('check')} 24 Monate Garantie</li>
            <li>{I('check')} Versand in 24 h</li>
            <li>{I('check')} Support &amp; Schulung inklusive</li>
          </ul>
        </div>
      </div>
    </div>
  );
}

function CartDrawer({ open, items, onClose, onRemove }) {
  const total = items.reduce((s, x) => s + x.price * x.qty, 0);
  return (
    <div className={`sh-drawer${open ? ' is-open' : ''}`} aria-hidden={!open}>
      <div className="sh-drawer__scrim" onClick={onClose}></div>
      <aside className="sh-drawer__panel">
        <div className="sh-drawer__head"><h3>Warenkorb</h3><IconButton label="Schließen" variant="ghost" onClick={onClose}>{I('x')}</IconButton></div>
        <div className="sh-drawer__body">
          {items.length === 0 && <p className="sh-empty">{I('shopping-cart')}<br />Noch nichts im Warenkorb.</p>}
          {items.map((x) => (
            <div className="sh-line" key={x.id}>
              <span className="sh-line__ico grid-surface">{I(x.icon)}</span>
              <div className="sh-line__info"><b>{x.name}</b><span>{x.qty} × {eur(x.price)}</span></div>
              <button className="sh-line__x" onClick={() => onRemove(x.id)} aria-label="Entfernen">{I('trash-2')}</button>
            </div>
          ))}
        </div>
        <div className="sh-drawer__foot">
          <div className="sh-drawer__total"><span>Zwischensumme</span><b>{eur(total)}</b></div>
          <Button block size="lg" disabled={items.length === 0} iconRight={I('arrow-right')}>Zur Kasse</Button>
        </div>
      </aside>
    </div>
  );
}

export function ShopApp() {
  const [theme, setTheme] = React.useState('light');
  const [sel, setSel] = React.useState(null);
  const [cart, setCart] = React.useState([]);
  const [open, setOpen] = React.useState(false);
  useLucide();
  const add = (p) => {
    setCart((c) => {
      const e = c.find((x) => x.id === p.id);
      return e ? c.map((x) => x.id === p.id ? { ...x, qty: x.qty + 1 } : x) : [...c, { ...p, qty: 1 }];
    });
    setOpen(true);
  };
  const remove = (id) => setCart((c) => c.filter((x) => x.id !== id));
  const count = cart.reduce((s, x) => s + x.qty, 0);
  return (
    <div className="sh-root" data-theme={theme}>
      <TopBar theme={theme} onToggle={() => setTheme(theme === 'dark' ? 'light' : 'dark')} cartCount={count} onOpenCart={() => setOpen(true)} />
      <div className="sh-wrap">
        <Breadcrumb items={[{ label: 'Start', href: '#' }, { label: 'Shop', href: '#' }, sel ? sel.name : 'Tools']} />
        {sel ? <Detail p={sel} onBack={() => setSel(null)} onAdd={add} /> : <Grid onOpen={setSel} onAdd={add} />}
      </div>
      <CartDrawer open={open} items={cart} onClose={() => setOpen(false)} onRemove={remove} />
    </div>
  );
}
