# Catalog / vehicle-selector UI kit

Recreation of the tuning-file catalog and vehicle drill-down.

- **`index.html`** — mounts `CatalogApp`. Interactive: pick a Marke to unlock the dependent selects, click any result row to open the **file detail** (dyno graph + Stage 1/2 toggle that recomputes the figures), use the back button to return. Light/dark toggle in the top bar.
- **`CatalogApp.jsx`** — exports `CatalogApp`; internally `TopBar`, `Filters`, `ResultRow`, `Browse`, `Detail`. Composes `Logo`, `Input`, `Select`, `Tag`, `Tabs`, `StatTile`, `Badge`, `Breadcrumb`, `Button`, `IconButton`.
- **`catalog.css`** — layout only.

The dyno curve is a simple two-path SVG (serie vs. tuned) — illustrative, not real telemetry. Default theme **dark**.
