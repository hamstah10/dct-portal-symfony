# No1 Tuningfiles — Design System (design.md)

Build-Briefing für **Claude Code**. Alles, um No1-Tuningfiles-Oberflächen konsistent zu bauen.
Quelle der Wahrheit für Tokens: `styles.css` (CSS Custom Properties). Diese Datei erklärt **wie** man sie einsetzt.

Ästhetik: **engineered, motorsport, high-contrast** — schwere kursiv-condensed Headlines (Logo-Geist), Carbon-Schwarz + ein einziges Ignition-Rot, hell-first, scharfe Hairlines statt weicher Tiefe.

---

## 0. Setup

```html
<link rel="stylesheet" href="styles.css">  <!-- Tokens + Fonts (Google Fonts @import) -->
<script src="https://unpkg.com/lucide@latest"></script>  <!-- Icons -->
<script>lucide.createIcons();</script>       <!-- nach jedem DOM-Update aufrufen -->
```

- Kein Build-Step, kein Framework nötig — statisches HTML + CSS-Variablen.
- Fonts laden per CDN. Für Produktion/Offline `@import` in `styles.css` durch self-hosted `@font-face` ersetzen.
- Markenfarben sind aus dem Logo gesampelt: **Rot `#E31E24`**, **Carbon `#2B2A29`**.

---

## 1. Prinzipien (Pflicht)

1. **Ein Akzent.** Nur Ignition-Rot (`--accent`) für primäre Aktionen, Live-/Status-Werte, aktive Zustände, Key-Zahlen. Sparsam — wenn alles rot ist, sticht nichts heraus.
2. **Carbon trägt 90 %.** Flächen, Text, Rahmen kommen aus der Carbon-Neutral-Rampe. Struktur entsteht aus **1px-Hairlines** (`--border`), nicht aus Schatten.
3. **Kursiv-condensed = Marke.** Headlines, Buttons, große Zahlen, Stage-Labels immer in `--font-display` **italic + UPPERCASE**. Das ist die DNA des Logos.
4. **Mechanisch & scharf.** Enge Radien (4–10px), flache Schatten. Pills nur für Tags/Status. `0`-Radius für technische Tabellen ok.
5. **8px-Raster.** Datenviews dicht (8/12/16), Marketing offen (48/64/96).
6. **Technik in Mono.** ECU-Codes, Specs, Credits, Labels, Eyebrows in `--font-mono`, uppercase, Letterspacing `0.08–0.12em`.
7. **Kein Emoji, kein Dekor.** Status = farbige Lucide-Glyphe + Text. Keine erfundenen Stats/Icons.

---

## 2. Tokens — immer Variablen, nie Hex hardcoden

### Farbe (semantisch)
| Zweck | Token |
|---|---|
| Seitenhintergrund | `--bg` (#FFF) · App-Shell-Grau `--surface-2` |
| Karte / Fläche | `--surface`, `--surface-2`, `--surface-3` |
| Hairline / stark | `--border` (#E6E6E6), `--border-strong` |
| Text / muted / subtil | `--text` (#2B2A29), `--text-muted`, `--text-subtle` |
| **Akzent** | `--accent` (#E31E24), `--accent-hover`, `--accent-press` |
| Akzent dezent | `--accent-soft` (#FDECEC), `--accent-text` |
| Text auf Akzent | `--text-on-accent` (#FFF) |
| Status | `--success` Dyno-Grün · `--danger` Redline (= Rot) · `--info` Telemetry-Blau · `--warning` Amber (jeweils `*-soft` als BG) |
| Dunkle Insel | `--carbon-800/900` (Sidebar, invertierte Tags, Logo-BG) |

Rampen für Sonderfälle: `--red-50…800`, `--carbon-25…950`.

### Typografie
- **Display** `--font-display` = *Saira Condensed* → `font-style:italic; font-weight:700/800; text-transform:uppercase; letter-spacing:var(--ls-tight)`. Headlines, Buttons, Zahlen, Stage-Labels.
- **Body/UI** `--font-sans` = *Archivo*. Fließtext, Inputs, Tabellenzellen.
- **Mono** `--font-mono` = *IBM Plex Mono*. Specs, Eyebrows, Labels, Credits, Codes.
- Scale: `--text-2xs (11) … --text-5xl (clamp 48–88)`. Body = `--text-md (16)`.
- Weights: `--fw-medium/semibold/bold/black` = 500/600/700/800.

### Spacing / Effekte
- Spacing: `--space-1…10` (4 → 128px). Container `--container: 1240px`.
- Radien: `--radius-xs 2 / sm 4 / md 6 / lg 10 / xl 14 / pill`.
- Schatten: `--shadow-xs…lg` (flach). Signatur `--glow-power` (roter 1px-Ring + Cast) — **nur** primäre CTA / Live-Daten.
- Motion: `--ease-out` (enter/exit), `--ease-snap` (toggles), `--dur-fast 120 / base 200 / slow 360`. `prefers-reduced-motion` respektieren.

---

## 3. Komponenten-Rezepte

Alle als statisches HTML + Klassen umgesetzt (siehe `Design System.html`, `portal/*.html` für vollständige Markup-Vorlagen). Muster:

- **Button** — `font-display` italic uppercase, `--radius-sm`, `padding:11px 20px`.
  - Primär: `bg:--accent; color:#fff`; hover `--accent-hover` + `--glow-power`.
  - Dark: `bg:--carbon-800`. Outline: `border:--border-strong`, hover Rand `--accent`. Ghost: transparent.
- **Badge** (Status, Pill): `font-mono` uppercase 11px, `*-soft` BG + farbiger Text, optional Punkt/Glyphe.
- **Tag** (Tuning-Feature): klein, `--radius-sm`; aktiv/technisch = `--carbon-800` BG, weißer Text.
- **Input / Select / Textarea**: `border:--border-strong`, `--radius-sm`; Focus = Rand `--accent` + `box-shadow:0 0 0 3px var(--accent-soft)`.
- **Switch**: 42×24 Pill, „on" = `--accent`, Knopf gleitet mit `--ease-snap`. **Checkbox**: 20px, „on" = `--accent` + weißer Haken.
- **Stat-Tile**: Karte mit linker 3px-Akzentkante; Wert in `font-display italic black`, Delta in `--success`.
- **Dyno-/Produkt-Card**: Kopf (Fahrzeug + Stage-Badge), 3-Spalten-Readout (Leistung/Drehmoment/Preis), Hairline-getrennt; Zugewinn grün, Preis rot.
- **Tabelle**: Mono-Header (uppercase, subtle), Zeilen-Hairlines, Hover `--surface-2`; Fahrzeug-Zelle = Icon-Chip + Name (display) + Spec (mono).
- **Progress**: 8px Pill-Track `--surface-3`, Füllung `--accent`.
- **Dropzone**: gestrichelter `--border-strong`, hover Rand `--accent` + `--accent-soft` BG; roter Icon-Chip.
- **Alert**: `*-soft` BG, farbiger Rand + runde Icon-Badge (success/warn).

---

## 4. App-Shell (Portal)

Layout: `grid-template-columns: 248px 1fr`.
- **Sidebar** (`.pt-side`): zwei Varianten — Carbon-900 (dunkel, Default) **oder** weiß mit Hairline-Rand. Logo oben (im Dunkelmodus `filter:brightness(0) invert(1)`), Nav-Gruppen ein-/ausklappbar, aktives Item = `--accent`-Fläche, Sub-Items mit linkem Hairline-Strich.
- **Topbar** (`.pt-top`): sticky, blur, globale Suche mit `CMD K`-kbd, Credits-Pill, Bell mit roter Zähl-Blase, Avatar (display italic). Auf Unterseiten **immer ein Zurück-Button** links.
- **Content**: zentriert, `max-width ~1180px` (Tabellen-Seiten breiter).
- **Stepper**: nummerierte Schritte, aktiv = roter Icon-Chip + `--glow-power`, erledigt = grün, Verbindungslinien.

---

## 5. Copywriting

- **Deutsch zuerst**; Auto-/Tuning-Vokabular unübersetzt: *Kennfeld, Steuergerät, Stage, DPF/AGR/AdBlue Off, Lesegerät, Slave/Master*.
- **Ansprache „du / dein Fahrzeug"** (Enthusiast-B2C); „Sie" nur Dealer/formal.
- **Ton:** präzise, leistungsgetrieben, Prüfstand-Glaubwürdigkeit statt Hype.
- **Headlines UPPERCASE & kurz** (1–4 Wörter). Body Satzschreibung.
- **Zahlen metrisch**, deutsches Format, Zugewinn mit **+**: `+72 PS`, `+110 Nm`, `2.0 TDI`. Credits ohne Komma-Tausenderpunkt nur wo nötig.
- **CTAs imperativ**, eine Primäraktion pro View: *Jetzt tunen, Fahrzeug wählen, Datei hochladen, In den Warenkorb, Weiter zu Schritt 2*.

---

## 6. Icons & Logo

- **Lucide** (`<i data-lucide="name">`), Größen 15–22px, `currentColor`. Nach DOM-Update `lucide.createIcons()`.
- Marken-Glyphen: `car-front`, `gauge`/`zap`, `cpu`/`file-cog`, `upload-cloud`, `sliders-horizontal`, `circle-dollar-sign`, `history`, `check-circle-2`, `x-circle`.
- **Logo** `assets/No1-logo.png` (PNG mit Alpha). Auf Dunkel invertieren. **Niemals** strecken, neigen, umfärben. Clearspace ≥ Höhe des „o", Min-Breite ~120px digital.

---

## 7. Do / Don't

- ✅ Rot nur für Aktion/Status/Highlight · ✅ Hairlines für Struktur · ✅ display-italic-uppercase für Headlines & Zahlen · ✅ mono für alles Technische.
- ❌ Mehrfarbige Akzente · ❌ große weiche Schatten/Glassmorphism · ❌ runde „Bubble"-Cards mit dicken Radien · ❌ Emoji · ❌ erfundene Stats/Icons als Deko · ❌ Inter/Roboto (nutze die definierten Familien).

---

## Caveats
- Fonts via Google-Fonts-CDN → für Produktion self-hosten.
- Kein kompiliertes Komponenten-Bundle: die Komponenten leben als HTML-Klassenmuster in den Beispiel-Seiten. Bei Bedarf in React/Vue extrahieren — Tokens (`styles.css`) bleiben die Basis.
