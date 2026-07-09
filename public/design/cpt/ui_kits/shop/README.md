# Tools shop UI kit

Recreation of the tuning-tools / hardware shop (Autotuner, interfaces, credits).

- **`index.html`** — mounts `ShopApp`. Interactive: **add-to-cart** (header badge counts up, cart drawer slides in), product detail with variant radios, remove items, running subtotal. Light/dark toggle in the bar (defaults **light** here, to contrast the catalog's dark default).
- **`ShopApp.jsx`** — exports `ShopApp`; internally `TopBar`, `ProductCard`, `Grid`, `Detail`, `CartDrawer`. Composes `Logo`, `Card`, `Badge`, `Radio`, `Breadcrumb`, `Button`, `IconButton`.
- **`shop.css`** — layout only; prices formatted with `toLocaleString('de-DE')`.

Product imagery uses Lucide glyph + carbon grid placeholders — swap for real product photos via the `media` prop on `Card`.
