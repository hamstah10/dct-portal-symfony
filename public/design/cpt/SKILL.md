---
name: chiptuningfile-design
description: Use this skill to generate well-branded interfaces and assets for Chiptuningfile (chiptuningfile.de — German ECU chip-tuning files & tools), either for production or throwaway prototypes/mocks/etc. Contains essential design guidelines, colors, type, fonts, assets, and UI kit components for prototyping.
user-invocable: true
---

Read the `readme.md` file within this skill, and explore the other available files.
If creating visual artifacts (slides, mocks, throwaway prototypes, etc), copy assets out and create static HTML files for the user to view. If working on production code, you can copy assets and read the rules here to become an expert in designing with this brand.
If the user invokes this skill without any other guidance, ask them what they want to build or design, ask some questions, and act as an expert designer who outputs HTML artifacts _or_ production code, depending on the need.

## Quick map
- `styles.css` — link this one file; it `@import`s every token + base stylesheet (fonts, light **and** dark themes). Set `data-theme="light"` (default) or `data-theme="dark"` on any ancestor.
- `tokens/` — colors, typography, spacing, effects, fonts. Use the CSS custom properties; never hard-code hex.
- `components/` — React primitives (Button, Input, Select, Card, StatTile, Table, Modal, Toast, Breadcrumb, …). Each has a `.d.ts` (props) + `.prompt.md` (usage).
- `ui_kits/` — full-screen recreations: `marketing/`, `catalog/`, `shop/`. Open each `index.html`.
- `slides/` — branded 1280×720 slide layouts.
- `assets/` — logo marks (⚠️ placeholder — replace with official logo).
- `guidelines/` — foundation specimen cards.

## Non-negotiables (the short version)
- **Carbon + one ignition red** (`--accent #E11226`). Color is rare; structure comes from 1px hairline borders, not float.
- **Type:** Orbitron (UPPERCASE display) · Archivo (body/UI) · IBM Plex Mono (specs, eyebrows, figures).
- **Tight radii (4–14px), low neutral shadows, decisive motion.** No purple gradients, no emoji, no soft rounded-pastel cards.
- **Copy:** German first, enthusiast "du", metric units with explicit "+", numbers do the talking.
