# Chiptuningfile Design System

A performance-engineering design language for **Chiptuningfile.de** — a German chip-tuning file service. This system covers brand foundations (color, type, spacing, motion), reusable UI components, and full-screen UI kits for the marketing site, tuning-file catalog / vehicle selector, and tools shop.

> **Theme note:** the brief asked for *hell und dunkel* — the entire system ships a **light** and a **dark** theme. Toggle with `data-theme="light"` (default) or `data-theme="dark"` on any ancestor element (usually `<html>`).

---

## Company context

**Chiptuningfile.de** is a Germany-based B2B/B2C provider of optimized ECU (engine control unit) tuning files and tuning hardware. Core offerings inferred from the public site:

- **Tuning files** — remapped ECU data for more power, torque, or efficiency across passenger cars, vans, trucks/commercial vehicles, motorcycles, buses, agricultural & marine engines.
- **Vehicle selector / file catalog** — browse by make → model → engine → ECU to find the matching tuning solution (Stage 1, EGR/DPF/AdBlue handling, eco-tuning, etc.).
- **Tuning tools shop** — sells reading/writing hardware (e.g. **Autotuner**) and accessories.
- **Dealer portal** — registration, login, a credit/file-request system, file upload, and a cart, aimed at workshops and resellers.
- Bilingual **DE / EN**; the company emphasizes years of experience, fast turnaround, and safe, dyno-tested results.

### Sources
- Primary reference: <https://chiptuningfile.de/index.php/de/home> (live site; Joomla-based, `index.php/de/...` routing).
- No codebase, Figma file, or brand-asset package was provided. Site **text** was read programmatically; exact colors, fonts, and logo files could **not** be extracted (cross-origin asset access is blocked from the build tools).

### Direction chosen (from intake)
- **Fresh take** — a strong new automotive-performance aesthetic (not a 1:1 copy of the current site).
- Typography: **technical / industrial, condensed, mechanical**.
- Audience: tuning **enthusiasts (B2C)** lead, dealers secondary.
- Copy language: **German primary**.
- Surfaces to build: marketing site, tuning-file catalog / vehicle selector, tools shop.
- Comprehensive component set + a few sample slide layouts.

---

## CONTENT FUNDAMENTALS

How the brand writes.

- **Language:** German first. Technical automotive vocabulary is used confidently and untranslated (*Kennfeld, Steuergerät, Leistungssteigerung, Drehmoment, Serienzustand, Eintragung*). English mirrors exist for international dealers.
- **Address:** speaks directly to the customer — informal-professional. Prefer **"du / dein Fahrzeug"** for the enthusiast B2C voice; switch to **"Sie"** only in formal dealer/legal contexts. Avoid corporate first-person ("wir bei …"); lead with the customer's car and result.
- **Tone:** precise, confident, performance-driven. Engineering credibility over hype. Numbers do the talking — power/torque deltas, stages, turnaround times. Never overpromise; pair claims with "geprüft / TÜV / auf dem Prüfstand".
- **Casing:** German nouns capitalized per grammar. **Display headlines are set UPPERCASE** for a mechanical, stamped feel — keep them short (1–4 words). Sentence case for body and UI labels.
- **Numbers & units:** metric, with a thin space before units — `+38 PS`, `+72 Nm`, `1.598 cm³`, `2,0 TDI`. Use German decimal comma and thousands point. Power deltas always carry an explicit **+**.
- **Eyebrows / kickers:** short uppercase mono labels above headlines — e.g. `STAGE 1`, `ECU REMAP`, `SEIT 2010`. Tracked out (`--ls-label`).
- **Buttons / CTAs:** imperative verbs — *Fahrzeug wählen, Datei hochladen, Jetzt anfragen, In den Warenkorb*. One primary action per view.
- **Emoji:** **none.** Not part of the brand voice.
- **Voice examples**
  - Hero: `MEHR LEISTUNG. AUS DEINER DATEI.` / sub: `Optimierte Kennfelder für über 8.000 Fahrzeuge — auf dem Prüfstand abgestimmt.`
  - Product: `Stage 1 — Serie auf Optimum. +38 PS, +72 Nm. Innerhalb von 30 Minuten geliefert.`
  - Empty state: `Noch kein Fahrzeug gewählt. Wähle Marke, Modell und Motor, um passende Dateien zu sehen.`
  - Error/critical: `Upload fehlgeschlagen — Datei beschädigt. Bitte erneut auslesen.`

---

## VISUAL FOUNDATIONS

The look: **engineered, high-contrast, telemetry-grade.** Think dyno readouts, ECU dashboards, and machined surfaces — not glossy car-dealer chrome.

- **Color vibe.** A near-neutral **carbon** ramp (cool graphite, faintly blue) carries 90% of the UI. A single hot **ignition red** (`--power-500 #E11226`) is the only brand accent — reserved for primary actions, live data, and key highlights. Semantic colors are literal instrument readings: **dyno green** = gain/success, **redline red** = critical/limit, **telemetry cyan** = info/data, **amber** = warning. Color is used sparingly against carbon; if everything glows, nothing does.
- **Light vs dark.** Light ("hell") is a clean workshop-paper off-white (`--carbon-25`) with carbon ink. Dark ("dunkel") is near-black carbon (`--carbon-950`) — the default mood for data-dense views (catalog, dyno graphs). Both are first-class; every component is built against semantic tokens so it flips cleanly.
- **Type.** **Orbitron** (geometric, futuristic) for UPPERCASE display headlines and big numbers; **Archivo** (neutral engineering grotesk) for body & UI; **IBM Plex Mono** for specs, ECU readouts, eyebrows, and any figure that should feel "measured". Tight tracking on display (`-0.02em`), generously tracked uppercase mono labels (`0.12em`).
- **Backgrounds.** Mostly flat carbon surfaces. Signature texture is a **faint hairline grid** (`.grid-surface`, 32px) evoking graph/dyno paper — used on heroes and section breaks, never behind body text. Occasional full-bleed automotive/engine photography, treated **cool and high-contrast with slight grain**, often with a carbon protection gradient for text legibility. No purple/blue hero gradients, no soft pastel blobs.
- **Borders.** Crisp **1px hairlines** (`--border`) define structure everywhere — this is a line-driven, instrument-panel system. A 2px accent border or a top "ignition" rule marks the active/important element. Dividers over fills.
- **Corner radii.** Deliberately **tight and mechanical**: 4–6px on controls and cards, 10–14px max on large containers, `0` is valid for technical tables/readouts. Pills only for tags/status chips. Avoid big friendly 16px+ rounding.
- **Shadows.** Low, tight, neutral — structure comes from borders, not float. `--shadow-sm/md` for raised cards and menus; heavier in dark. One **signature effect**: the `--glow-power` ignition glow (1px ring + soft orange cast) on the primary CTA and on "live"/active data — used very sparingly.
- **Spacing & layout.** 8px grid. Dense but breathable; data views (tables, catalog rows) tighten to 4/8/12, marketing views open up to 48/64/96. Max content width `--container 1240px`. Strong left-alignment and a visible columnar grid; ragged-right display text.
- **Motion.** Decisive and mechanical, never bouncy-cute. `--ease-out` (quick settle) for most enter/exit; `--ease-snap` (tiny overshoot) only on toggles/switches. Durations 120–360ms. Fades + short transl(8–16px) for entrances. Respect `prefers-reduced-motion`.
- **Hover states.** Buttons darken toward `--accent-hover` (light) / lighten (dark); ghost/secondary fill with `--surface-2`; links underline. Cards lift one shadow step and the hairline border strengthens to `--border-strong`.
- **Press states.** Subtle `scale(0.98)` + move to `--accent-press`; no large bounce. Tactile, like a physical switch.
- **Transparency & blur.** Used only for overlay scrims (`rgba` carbon at ~60%) and the sticky header's slight backdrop blur on scroll. Not a decorative glassmorphism system.
- **Imagery.** Cool-tone, high-contrast engine/vehicle/hardware photography with fine grain; avoid warm stock-y lifestyle shots. Dyno graphs and data viz use the accent + telemetry palette on carbon.
- **Cards.** Flat `--surface` fill, 1px `--border`, `--radius-lg`, `--shadow-sm`. Optional 2px accent top rule for featured cards. Hover: `--shadow-md` + `--border-strong`. No left-color-accent-bar trope.

---

---

## ICONOGRAPHY

- **System:** [**Lucide**](https://lucide.dev) — loaded from CDN (`https://unpkg.com/lucide@latest`). Chosen for its consistent **1.5–2px stroke, geometric, no-fill** style, which matches the engineered/instrument aesthetic. Use it everywhere; do not mix icon families.
  - In components/kits: `<i data-lucide="gauge"></i>` then call `lucide.createIcons()`, or inline the SVG. Default stroke width **1.75**, `currentColor`, sized in even steps (16 / 20 / 24).
- **No emoji.** Never used in UI or marketing copy.
- **No decorative unicode** as icons (no ►, ★, etc.). Status uses semantic-colored Lucide glyphs (`check-circle`, `alert-triangle`, `x-circle`).
- **Common glyphs for this brand:** `gauge` / `gauge-circle` (power), `zap` (boost/ignition), `car` / `truck` / `bike` (vehicle classes), `cpu` / `microchip` (ECU), `upload` / `file-up` (file submission), `download`, `search`, `sliders-horizontal` (tuning stages), `shield-check` (TÜV/geprüft), `trending-up` (dyno gain), `wrench`, `chevron-right`.
- **Logo / mark:** `assets/logo-mark.svg` (dark-on-light) and `assets/logo-mark-solid.svg` (red) — a **geometric double-chevron "boost" mark**. ⚠️ **Placeholder**: generated for this fresh-take direction because no official logo was supplied. Replace with the real brand asset when available; the `Logo` component reads these files.
- **Substitution flag:** Lucide is a substitute icon system (the original site's icon set could not be extracted). If the brand standardizes on a specific set, swap the CDN link and update this section.

---

## INDEX / MANIFEST

Root files:
- `styles.css` — global entry point (consumers link this). `@import`s the token + base files only.
- `tokens/` — `colors.css` (light+dark), `typography.css`, `spacing.css`, `effects.css`, `fonts.css`, `base.css`.
- `readme.md` — this guide.
- `SKILL.md` — Agent-Skills-compatible entry for Claude Code.
- `assets/` — logo marks (+ kit imagery as added).
- `guidelines/` — foundation specimen cards (Design System tab).
- `components/` — reusable React primitives (see below).
- `ui_kits/` — full-screen product recreations.
- `slides/` — branded sample slide layouts.

**Components** (`window.ChiptuningfileDesignSystem_3beb42.*` — 20 primitives):
- `components/core/` — **Button**, **IconButton**, **Badge**, **Tag**, **Logo**
- `components/forms/` — **Input**, **Select**, **Checkbox**, **Radio**, **Switch**
- `components/data/` — **Card**, **StatTile**, **Tabs**, **Table**, **ProgressBar**, **FileUpload**
- `components/feedback/` — **Modal**, **Toast**, **Tooltip**
- `components/navigation/` — **Breadcrumb**

Each directory has a `<Name>.jsx` + `<Name>.d.ts` + `<Name>.prompt.md` and one `@dsCard` HTML. Consume via `const { Button } = window.ChiptuningfileDesignSystem_3beb42` after loading `_ds_bundle.js`, or `import` directly in another bundled `.jsx`.

**UI kits** (each `{index.html, *.jsx, *.css, README.md}`):
- `ui_kits/marketing/` — homepage: hero + vehicle finder, stages, trust band, CTA, footer (`MarketingSite`). *Starting point.*
- `ui_kits/catalog/` — vehicle selector → results → file detail with dyno + stage toggle (`CatalogApp`). *Starting point.*
- `ui_kits/shop/` — tools shop: product grid, detail, add-to-cart drawer (`ShopApp`). *Starting point.*

All three are interactive and ship a working light/dark toggle.

**Slides:** `slides/` — `TitleSlide`, `StatSlide`, `ComparisonSlide`, `SectionSlide` (1280×720).

**Fonts:** Saira Condensed · Archivo · IBM Plex Mono — currently loaded from **Google Fonts CDN** (`tokens/fonts.css`). ⚠️ For production/offline, self-host the files and replace the `@import` with `@font-face` rules.
