# Portal UI kit — "Neuer Auftrag"

Recreation of the dealer-portal order wizard (Step 1: File & Lesegerät), rebuilt in the Chiptuningfile system (carbon + red, Orbitron headlines, DS components). Based on a user-supplied screenshot of the live portal.

- **`index.html`** — dark theme · **`index-light.html`** — light theme. Both mount `PortalApp`; the theme toggle in the top bar switches live.
- **`credits.html`** / **`credits-light.html`** — the "Credits kaufen" screen (`CreditsApp`): package grid, balance card, prepay-invoice panel, and the Buchungen ledger table.
- **`PortalApp.jsx`** — exports `PortalApp({ initialTheme })`, plus the shared shell pieces `PortalSidebar` / `PortalTopBar` (reused by the credits screen). Internally `Stepper`, `SegGroup`, options/dropzone/comment. Composes `Logo`, `Button`, `IconButton`, `Select`, `FileUpload`.
- **`CreditsApp.jsx`** — exports `CreditsApp({ initialTheme })`; composes the shared shell + `Button`, `Badge`, `Table`.
- **`portal.css`** / **`credits.css`** — layout only.

Differences from the source screenshot: the original uses a blue accent and a green heading rule; this version maps both onto the brand's **ignition red** accent per the system. Sidebar/topbar structure, stepper, option groups, dropzone and comment field mirror the original.
