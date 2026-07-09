import { useState } from 'react';
import { useLanguage } from '../context/LanguageContext';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import {
  EnvelopeSimple, Eye, Code, Copy, Check, CheckCircle,
  Package, CreditCard, Headset, UserCircle, FileArrowDown, Receipt,
} from '@phosphor-icons/react';
import { cn } from '../lib/utils';
import { getPortalDesignBootstrap } from '../lib/portalDesignBootstrap';

const buildBaseStyles = (portalDesign) => `
body { margin:0; padding:0; background:${portalDesign.contentBackgroundColor}; font-family:${portalDesign.bodyFont}; }
.wrapper { max-width:600px; margin:0 auto; background:${portalDesign.secondaryColor}; }
.header { background:linear-gradient(135deg,${portalDesign.secondaryColor} 0%,${portalDesign.menuBackgroundColor} 100%); padding:32px 40px; border-bottom:3px solid ${portalDesign.primaryColor}; }
.header img { height:32px; }
.logo-text { font-size:22px; font-weight:800; color:#fff; letter-spacing:-0.5px; font-family:${portalDesign.headingFont}; }
.logo-text span { color:${portalDesign.primaryColor}; }
.body { padding:40px; }
.h1 { font-size:24px; font-weight:700; color:#ffffff; margin:0 0 8px 0; font-family:${portalDesign.headingFont}; }
.subtitle { font-size:14px; color:#888; margin:0 0 28px 0; }
.card { background:#222; border:1px solid #333; border-radius:4px; padding:20px; margin-bottom:16px; }
.card-label { font-size:10px; color:#888; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:4px; }
.card-value { font-size:15px; color:#fff; font-weight:600; }
.card-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #2a2a2a; }
.card-row:last-child { border-bottom:none; }
.card-row-label { font-size:13px; color:#888; }
.card-row-value { font-size:13px; color:#fff; font-weight:600; }
.btn { display:inline-block; background:${portalDesign.primaryColor}; color:#fff; text-decoration:none; padding:14px 32px; border-radius:4px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
.btn-outline { display:inline-block; border:2px solid ${portalDesign.primaryColor}; color:${portalDesign.primaryColor}; text-decoration:none; padding:12px 28px; border-radius:4px; font-size:13px; font-weight:700; }
.highlight { color:${portalDesign.primaryColor}; font-weight:700; }
.badge { display:inline-block; background:${portalDesign.primaryColor}26; color:${portalDesign.primaryColor}; border:1px solid ${portalDesign.primaryColor}4d; padding:4px 12px; border-radius:4px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
.badge-green { background:rgba(34,197,94,0.1); color:#22c55e; border-color:rgba(34,197,94,0.3); }
.badge-blue { background:rgba(59,130,246,0.1); color:#3b82f6; border-color:rgba(59,130,246,0.3); }
.divider { height:1px; background:#333; margin:24px 0; }
.footer { background:${portalDesign.menuBackgroundColor}; padding:28px 40px; text-align:center; border-top:1px solid #222; }
.footer p { font-size:11px; color:#555; margin:4px 0; }
.footer a { color:${portalDesign.primaryColor}; text-decoration:none; }
.text-sm { font-size:13px; color:#aaa; line-height:1.7; }
.text-center { text-align:center; }
.mt-16 { margin-top:16px; }
.mt-24 { margin-top:24px; }
.mb-8 { margin-bottom:8px; }
`;

const buildFooterHtml = (portalDesign) => `
<div class="footer">
  <p style="font-size:13px;color:#888;font-weight:600;">${portalDesign.portalName}</p>
  <p>Professionelle Tuning-Files f&uuml;r alle Fahrzeuge</p>
  <p style="margin-top:12px;"><a href="#">Portal</a> &middot; <a href="#">Support</a> &middot; <a href="#">Impressum</a></p>
  <p style="margin-top:8px;">&copy; 2026 ${portalDesign.portalName} &mdash; Alle Rechte vorbehalten</p>
</div>`;

const buildHeaderHtml = (portalDesign) => `
<div class="header">
  <div class="logo-text">${portalDesign.portalName}</div>
</div>`;

const buildTemplates = (portalDesign) => {
  const baseStyles = buildBaseStyles(portalDesign);
  const headerHtml = buildHeaderHtml(portalDesign);
  const footerHtml = buildFooterHtml(portalDesign);

  return [
  {
    id: 'order_confirm',
    name: { de: 'Auftragsbestätigung', en: 'Order Confirmation' },
    desc: { de: 'Neuer Auftrag empfangen', en: 'New order received' },
    Icon: Package,
    color: 'text-primary',
    html: `<!DOCTYPE html><html><head><meta charset="utf-8"><style>${baseStyles}</style></head><body>
<div class="wrapper">
  ${headerHtml}
  <div class="body">
    <h1 class="h1">Auftrag empfangen!</h1>
    <p class="subtitle">Dein Auftrag wurde erfolgreich erstellt und wird verarbeitet.</p>

    <div class="card">
      <div class="card-row"><span class="card-row-label">Auftragsnummer</span><span class="card-row-value highlight">CT-20260324-48291</span></div>
      <div class="card-row"><span class="card-row-label">Status</span><span class="badge">In Bearbeitung</span></div>
      <div class="card-row"><span class="card-row-label">Erstellt am</span><span class="card-row-value">24.03.2026, 16:45</span></div>
    </div>

    <div class="card">
      <p class="card-label">Fahrzeug</p>
      <p class="card-value" style="margin-bottom:12px;">BMW 330d G20 - 2019</p>
      <div class="card-row"><span class="card-row-label">Motor</span><span class="card-row-value">330d - 286 PS</span></div>
      <div class="card-row"><span class="card-row-label">Steuerger&auml;t</span><span class="card-row-value" style="font-family:monospace;">Bosch EDC17C50</span></div>
      <div class="card-row"><span class="card-row-label">Tuning</span><span class="card-row-value"><span class="badge">Stage 1</span></span></div>
      <div class="card-row"><span class="card-row-label">Optionen</span><span class="card-row-value">DPF-Off, EGR-Off</span></div>
    </div>

    <div class="card">
      <div class="card-row"><span class="card-row-label">Leseger&auml;t</span><span class="card-row-value">Autotuner - Tool</span></div>
      <div class="card-row"><span class="card-row-label">Methode</span><span class="card-row-value">OBD / Full Read / Master</span></div>
      <div class="card-row"><span class="card-row-label">Priorit&auml;t</span><span class="card-row-value">Normal</span></div>
      <div class="card-row"><span class="card-row-label">Credits</span><span class="card-row-value highlight">100 Credits</span></div>
    </div>

    <div class="text-center mt-24">
      <a href="#" class="btn">Auftrag im Portal ansehen</a>
    </div>

    <p class="text-sm mt-24">Wir pr&uuml;fen dein File automatisch mit WinOLS. Du erh&auml;ltst eine Benachrichtigung, sobald das Tuning-File bereit ist.</p>
  </div>
  ${footerHtml}
</div></body></html>`,
  },
  {
    id: 'file_ready',
    name: { de: 'File fertig', en: 'File Ready' },
    desc: { de: 'Tuning-File bereit zum Download', en: 'Tuning file ready for download' },
    Icon: FileArrowDown,
    color: 'text-green-500',
    html: `<!DOCTYPE html><html><head><meta charset="utf-8"><style>${baseStyles}</style></head><body>
<div class="wrapper">
  ${headerHtml}
  <div class="body">
    <div class="text-center" style="margin-bottom:28px;">
      <div style="width:64px;height:64px;border-radius:50%;background:rgba(34,197,94,0.1);border:2px solid rgba(34,197,94,0.3);display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
        <span style="font-size:28px;color:#22c55e;">&#10003;</span>
      </div>
      <h1 class="h1">Dein Tuning-File ist bereit!</h1>
      <p class="subtitle">Das modifizierte File steht zum Download bereit.</p>
    </div>

    <div class="card">
      <div class="card-row"><span class="card-row-label">Auftrag</span><span class="card-row-value highlight">CT-20260324-48291</span></div>
      <div class="card-row"><span class="card-row-label">Fahrzeug</span><span class="card-row-value">BMW 330d G20 - 286 PS</span></div>
      <div class="card-row"><span class="card-row-label">Tuning</span><span class="card-row-value"><span class="badge">Stage 1</span></span></div>
      <div class="card-row"><span class="card-row-label">Status</span><span class="badge-green badge">Fertig</span></div>
    </div>

    <div class="card" style="text-align:center;background:rgba(34,197,94,0.05);border-color:rgba(34,197,94,0.2);">
      <p class="card-label">Modifiziertes File</p>
      <p class="card-value" style="font-family:monospace;font-size:13px;margin:8px 0;">BMW_330d_G20_Stage1_mod.bin</p>
      <p style="font-size:12px;color:#888;margin-bottom:16px;">Erstellt: 24.03.2026, 17:02 &middot; Methode: Automatisch (WinOLS)</p>
      <a href="#" class="btn" style="background:#16a34a;">&#8595; Jetzt herunterladen</a>
    </div>

    <p class="text-sm mt-24">Das File wurde automatisch von WinOLS erstellt. Bei Fragen oder Problemen kontaktiere bitte unseren Support.</p>
  </div>
  ${footerHtml}
</div></body></html>`,
  },
  {
    id: 'support_handling',
    name: { de: 'Support-Übernahme', en: 'Support Handling' },
    desc: { de: 'Auftrag wird manuell bearbeitet', en: 'Order being handled manually' },
    Icon: Headset,
    color: 'text-blue-400',
    html: `<!DOCTYPE html><html><head><meta charset="utf-8"><style>${baseStyles}</style></head><body>
<div class="wrapper">
  ${headerHtml}
  <div class="body">
    <h1 class="h1">Dein Auftrag wird vom Support bearbeitet</h1>
    <p class="subtitle">WinOLS konnte kein passendes File finden. Unser Team &uuml;bernimmt.</p>

    <div class="card">
      <div class="card-row"><span class="card-row-label">Auftrag</span><span class="card-row-value highlight">CT-20260324-48291</span></div>
      <div class="card-row"><span class="card-row-label">Fahrzeug</span><span class="card-row-value">Audi A4 B9 - 2.0 TDI 150 PS</span></div>
      <div class="card-row"><span class="card-row-label">Tuning</span><span class="card-row-value"><span class="badge">Stage 2</span></span></div>
      <div class="card-row"><span class="card-row-label">Status</span><span class="badge-blue badge">Support bearbeitet</span></div>
    </div>

    <div class="card" style="background:rgba(59,130,246,0.05);border-color:rgba(59,130,246,0.2);">
      <p style="font-size:14px;color:#3b82f6;font-weight:700;margin:0 0 8px 0;">&#9432; Was bedeutet das?</p>
      <p class="text-sm" style="margin:0;">Diese Konfiguration muss manuell von unserem erfahrenen Team erstellt werden. Dein Auftrag hat hohe Priorit&auml;t und wird so schnell wie m&ouml;glich bearbeitet.</p>
    </div>

    <div class="card">
      <div class="card-row"><span class="card-row-label">Gesch&auml;tzte Bearbeitungszeit</span><span class="card-row-value" style="color:#f59e0b;">30 - 60 Minuten</span></div>
      <div class="card-row"><span class="card-row-label">Priorit&auml;t</span><span class="card-row-value">Normal</span></div>
    </div>

    <p class="text-sm">Du erh&auml;ltst eine weitere E-Mail, sobald das Tuning-File bereit zum Download ist.</p>

    <div class="text-center mt-24">
      <a href="#" class="btn">Auftrag im Portal verfolgen</a>
    </div>
  </div>
  ${footerHtml}
</div></body></html>`,
  },
  {
    id: 'credits_purchased',
    name: { de: 'Credits gekauft', en: 'Credits Purchased' },
    desc: { de: 'Zahlungsbestätigung', en: 'Payment confirmation' },
    Icon: CreditCard,
    color: 'text-green-400',
    html: `<!DOCTYPE html><html><head><meta charset="utf-8"><style>${baseStyles}</style></head><body>
<div class="wrapper">
  ${headerHtml}
  <div class="body">
    <div class="text-center" style="margin-bottom:28px;">
      <h1 class="h1">Zahlung erfolgreich!</h1>
      <p class="subtitle">Deine Credits wurden deinem Konto gutgeschrieben.</p>
    </div>

    <div class="card" style="text-align:center;">
      <p class="card-label">Gutgeschrieben</p>
      <p style="font-size:42px;font-weight:800;color:${portalDesign.primaryColor};margin:8px 0;font-family:${portalDesign.headingFont};">1.000</p>
      <p style="font-size:14px;color:#888;margin:0;">Credits</p>
    </div>

    <div class="card">
      <div class="card-row"><span class="card-row-label">Zahlungsmethode</span><span class="card-row-value">PayPal</span></div>
      <div class="card-row"><span class="card-row-label">Betrag (netto)</span><span class="card-row-value">950,00 &euro;</span></div>
      <div class="card-row"><span class="card-row-label">inkl. MwSt. (19%)</span><span class="card-row-value">1.249,50 &euro;</span></div>
      <div class="card-row"><span class="card-row-label">Transaktions-ID</span><span class="card-row-value" style="font-family:monospace;font-size:12px;">PAY-7X92KL4M</span></div>
      <div class="card-row"><span class="card-row-label">Datum</span><span class="card-row-value">24.03.2026, 14:22</span></div>
    </div>

    <div class="card" style="background:rgba(139,38,53,0.05);border-color:rgba(139,38,53,0.2);">
      <div class="card-row"><span class="card-row-label">Neuer Kontostand</span><span class="card-row-value" style="font-size:18px;color:${portalDesign.primaryColor};font-weight:800;">2.250 Credits</span></div>
    </div>

    <div class="text-center mt-24">
      <a href="#" class="btn">Rechnung herunterladen</a>
      <div style="margin-top:12px;"><a href="#" class="btn-outline">Zum Portal</a></div>
    </div>
  </div>
  ${footerHtml}
</div></body></html>`,
  },
  {
    id: 'invoice',
    name: { de: 'Rechnung', en: 'Invoice' },
    desc: { de: 'Rechnungsbeleg', en: 'Invoice receipt' },
    Icon: Receipt,
    color: 'text-yellow-400',
    html: `<!DOCTYPE html><html><head><meta charset="utf-8"><style>${baseStyles}</style></head><body>
<div class="wrapper">
  ${headerHtml}
  <div class="body">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;">
      <div>
        <h1 class="h1" style="font-size:20px;">Rechnung</h1>
        <p class="subtitle" style="margin-bottom:0;">Rechnungsnummer: <span class="highlight">RE-2026-00142</span></p>
      </div>
      <div style="text-align:right;">
        <p style="font-size:12px;color:#888;margin:0;">Datum: 24.03.2026</p>
        <p style="font-size:12px;color:#888;margin:4px 0 0 0;">F&auml;llig: Sofort</p>
      </div>
    </div>

    <div class="card">
      <p class="card-label">Rechnungsempf&auml;nger</p>
      <p class="card-value">Max Mustermann</p>
      <p style="font-size:13px;color:#888;margin:4px 0 0 0;">Muster GmbH<br>Musterstra&szlig;e 123<br>12345 Musterstadt<br>Deutschland</p>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
      <div style="display:flex;justify-content:space-between;padding:12px 20px;background:#252525;border-bottom:1px solid #333;">
        <span style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;font-weight:700;">Beschreibung</span>
        <span style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;font-weight:700;">Betrag</span>
      </div>
      <div class="card-row" style="padding:12px 20px;"><span class="card-row-label">1.000 Tuning Credits</span><span class="card-row-value">950,00 &euro;</span></div>
        <div style="padding:12px 20px;border-top:1px solid #333;">
        <div class="card-row"><span class="card-row-label">Zwischensumme</span><span class="card-row-value">950,00 &euro;</span></div>
        <div class="card-row"><span class="card-row-label">MwSt. (19%)</span><span class="card-row-value">180,50 &euro;</span></div>
        <div class="card-row" style="border-bottom:none;padding-top:12px;border-top:2px solid ${portalDesign.primaryColor};margin-top:8px;">
          <span style="font-size:14px;color:#fff;font-weight:700;">Gesamtbetrag</span>
          <span style="font-size:18px;color:${portalDesign.primaryColor};font-weight:800;">1.130,50 &euro;</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-row"><span class="card-row-label">Zahlungsmethode</span><span class="card-row-value">PayPal</span></div>
      <div class="card-row"><span class="card-row-label">Status</span><span class="badge-green badge">Bezahlt</span></div>
    </div>

    <div class="text-center mt-24">
      <a href="#" class="btn">PDF herunterladen</a>
    </div>

    <p class="text-sm mt-24" style="font-size:11px;">Diese Rechnung wurde maschinell erstellt und ist ohne Unterschrift g&uuml;ltig. USt-IdNr: DE123456789</p>
  </div>
  ${footerHtml}
</div></body></html>`,
  },
  {
    id: 'welcome',
    name: { de: 'Willkommen', en: 'Welcome' },
    desc: { de: 'Neuer Account erstellt', en: 'New account created' },
    Icon: UserCircle,
    color: 'text-primary',
    html: `<!DOCTYPE html><html><head><meta charset="utf-8"><style>${baseStyles}</style></head><body>
<div class="wrapper">
  ${headerHtml}
  <div class="body">
    <div class="text-center" style="margin-bottom:28px;">
      <h1 class="h1">Willkommen bei ${portalDesign.portalName}!</h1>
      <p class="subtitle">Dein Account wurde erfolgreich erstellt.</p>
    </div>

    <div class="card">
      <div class="card-row"><span class="card-row-label">Name</span><span class="card-row-value">Max Mustermann</span></div>
      <div class="card-row"><span class="card-row-label">E-Mail</span><span class="card-row-value">max@muster.de</span></div>
      <div class="card-row"><span class="card-row-label">Kundennummer</span><span class="card-row-value highlight">KD-20260324-001</span></div>
    </div>

    <div class="card" style="background:${portalDesign.primaryColor}0d;border-color:${portalDesign.primaryColor}33;">
      <p style="font-size:14px;color:#fff;font-weight:700;margin:0 0 12px 0;">Deine n&auml;chsten Schritte:</p>
      <div style="margin-bottom:8px;">
        <span style="display:inline-block;width:20px;height:20px;background:${portalDesign.primaryColor};color:#fff;border-radius:50%;text-align:center;line-height:20px;font-size:11px;font-weight:700;margin-right:8px;">1</span>
        <span class="text-sm">Credits kaufen und sofort loslegen</span>
      </div>
      <div style="margin-bottom:8px;">
        <span style="display:inline-block;width:20px;height:20px;background:${portalDesign.primaryColor};color:#fff;border-radius:50%;text-align:center;line-height:20px;font-size:11px;font-weight:700;margin-right:8px;">2</span>
        <span class="text-sm">Tuning-File hochladen und Fahrzeug konfigurieren</span>
      </div>
      <div>
        <span style="display:inline-block;width:20px;height:20px;background:${portalDesign.primaryColor};color:#fff;border-radius:50%;text-align:center;line-height:20px;font-size:11px;font-weight:700;margin-right:8px;">3</span>
        <span class="text-sm">Optimiertes File herunterladen</span>
      </div>
    </div>

    <div class="card">
      <p class="card-label">Startguthaben</p>
      <p style="font-size:28px;font-weight:800;color:${portalDesign.primaryColor};margin:4px 0;">10 Credits</p>
      <p style="font-size:12px;color:#888;margin:0;">Zum Kennenlernen &mdash; geschenkt!</p>
    </div>

    <div class="text-center mt-24">
      <a href="#" class="btn">Zum Portal</a>
    </div>

    <p class="text-sm mt-24">Bei Fragen steht dir unser Support jederzeit zur Verf&uuml;gung. Wir freuen uns auf die Zusammenarbeit!</p>
  </div>
  ${footerHtml}
</div></body></html>`,
  },
  ];
};

const t_page = {
  de: {
    title: 'E-Mail Templates',
    subtitle: 'Vorschau und HTML-Code der E-Mail-Vorlagen',
    preview: 'Vorschau',
    code: 'HTML Code',
    copy: 'Kopieren',
    copied: 'Kopiert!',
  },
  en: {
    title: 'Email Templates',
    subtitle: 'Preview and HTML code of email templates',
    preview: 'Preview',
    code: 'HTML Code',
    copy: 'Copy',
    copied: 'Copied!',
  },
};

export default function EmailTemplates() {
  const { language } = useLanguage();
  const portalDesign = getPortalDesignBootstrap();
  const templates = buildTemplates(portalDesign);
  const t = (k) => t_page[language]?.[k] || k;
  const [activeId, setActiveId] = useState(templates[0].id);
  const [viewMode, setViewMode] = useState('preview');
  const [copied, setCopied] = useState(false);

  const active = templates.find(tp => tp.id === activeId);

  const handleCopy = () => {
    if (!active) return;
    navigator.clipboard.writeText(active.html);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <DashboardLayout>
      <div className="space-y-6 max-w-[1400px]">
        {/* Header */}
        <div>
          <h1 className="font-heading text-2xl font-bold text-foreground tracking-tight flex items-center gap-3">
            <EnvelopeSimple weight="fill" className="w-7 h-7 text-primary" />
            {t('title')}
          </h1>
          <p className="text-sm text-muted-foreground mt-1">{t('subtitle')}</p>
        </div>

        {/* Layout: Template list + Preview */}
        <div className="grid grid-cols-1 xl:grid-cols-[260px_1fr] gap-6">
          {/* LEFT: Template List */}
          <div className="space-y-2" data-testid="template-list">
            {templates.map(tp => {
              const Icon = tp.Icon;
              const isActive = activeId === tp.id;
              return (
                <button
                  key={tp.id}
                  type="button"
                  onClick={() => { setActiveId(tp.id); setViewMode('preview'); setCopied(false); }}
                  data-testid={`tmpl-${tp.id}`}
                  className={cn(
                    "w-full flex items-center gap-3 p-3 rounded-sm border-2 transition-all text-left",
                    isActive
                      ? "bg-primary/8 border-primary/40"
                      : "bg-card border-border hover:border-muted-foreground/40"
                  )}
                >
                  <Icon weight={isActive ? "fill" : "regular"} className={cn("w-5 h-5 flex-shrink-0", isActive ? tp.color : "text-muted-foreground")} />
                  <div className="min-w-0">
                    <p className={cn("text-sm font-semibold truncate", isActive ? "text-foreground" : "text-muted-foreground")}>{tp.name[language]}</p>
                    <p className="text-[10px] text-muted-foreground truncate">{tp.desc[language]}</p>
                  </div>
                </button>
              );
            })}
          </div>

          {/* RIGHT: Preview / Code */}
          <div className="space-y-4">
            {/* Toolbar */}
            <div className="flex items-center justify-between">
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => setViewMode('preview')}
                  data-testid="mode-preview"
                  className={cn(
                    "flex items-center gap-2 px-4 py-2 rounded-sm text-sm font-semibold border transition-all",
                    viewMode === 'preview' ? "bg-primary/10 border-primary/40 text-foreground" : "bg-secondary/50 border-border text-muted-foreground hover:text-foreground"
                  )}
                >
                  <Eye weight="bold" className="w-4 h-4" /> {t('preview')}
                </button>
                <button
                  type="button"
                  onClick={() => setViewMode('code')}
                  data-testid="mode-code"
                  className={cn(
                    "flex items-center gap-2 px-4 py-2 rounded-sm text-sm font-semibold border transition-all",
                    viewMode === 'code' ? "bg-primary/10 border-primary/40 text-foreground" : "bg-secondary/50 border-border text-muted-foreground hover:text-foreground"
                  )}
                >
                  <Code weight="bold" className="w-4 h-4" /> {t('code')}
                </button>
              </div>
              {viewMode === 'code' && (
                <Button
                  variant="outline"
                  className="border-border font-semibold text-sm"
                  onClick={handleCopy}
                  data-testid="copy-btn"
                >
                  {copied ? <CheckCircle weight="fill" className="w-4 h-4 mr-2 text-green-500" /> : <Copy weight="bold" className="w-4 h-4 mr-2" />}
                  {copied ? t('copied') : t('copy')}
                </Button>
              )}
            </div>

            {/* Content */}
            {active && viewMode === 'preview' && (
              <Card className="bg-[#111] border-border overflow-hidden" data-testid="template-preview">
                <CardContent className="p-0">
                  <iframe
                    srcDoc={active.html}
                    title={active.name[language]}
                    className="w-full border-0"
                    style={{ height: '700px', background: '#111' }}
                    sandbox="allow-same-origin"
                  />
                </CardContent>
              </Card>
            )}

            {active && viewMode === 'code' && (
              <Card className="bg-card border-border" data-testid="template-code">
                <CardContent className="p-0">
                  <pre className="p-5 text-xs text-muted-foreground font-mono overflow-auto max-h-[700px] leading-relaxed whitespace-pre-wrap break-all">
                    {active.html}
                  </pre>
                </CardContent>
              </Card>
            )}
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}
