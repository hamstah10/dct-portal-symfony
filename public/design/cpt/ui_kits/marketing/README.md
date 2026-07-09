# Marketing UI kit

High-fidelity recreation of the Chiptuningfile public marketing homepage in the fresh automotive-performance direction.

- **`index.html`** — interactive page. Mounts `MarketingSite` from the compiled bundle. Includes a working **light/dark toggle** (sun/moon in the header), a vehicle-finder whose Modell/Motor selects unlock once a Marke is chosen, and hover states throughout.
- **`MarketingSite.jsx`** — exports `MarketingSite`; internally: `Header`, `Hero` (with finder), `StatStrip`, `Stages`, `Trust` (dark band), `CTA`, `Footer`. Composes DS primitives (`Logo`, `Button`, `Badge`, `Select`, `StatTile`, `Card`). Also exports the `useLucide()` helper used across kits.
- **`marketing.css`** — layout only; all color/type/spacing comes from DS tokens.

**Imagery:** uses the carbon `Media` placeholder (grid texture + Lucide watermark + mono caption) wherever real engine/vehicle photography belongs. Drop in real photos by replacing `<Media>` with `<img>`.

Default theme is **dark**; toggle to light in the header.
