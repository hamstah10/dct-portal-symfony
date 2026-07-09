# Superadmin Portal Frontend

Hier liegt das eigenstaendige React-Frontend fuer das Portal `superadmin_portal`.

Das echte Projekt liegt bereits hier:

- `frontend/fux-kern-verbindung`

Build-Ziel:

- `public/assets/portals/superadmin-portal/react/`

Erwartete Dateien fuer das Twig-Host-Template:

- `public/assets/portals/superadmin-portal/react/static/css/main.css`
- `public/assets/portals/superadmin-portal/react/static/js/main.js`

Bootstrap-Daten stehen im Browser unter:

- `window.portalReactBootstrap`

Build:

```bash
cd templates/portal/theme_react_superadmin_portal/superadmin_portal/frontend/fux-kern-verbindung
npm install
npm run build:portal
```

Die Vite-Konfiguration ist bereits auf das Symfony-Portal verdrahtet:

- Base-URL: `/_portal-react/superadmin-portal/`
- Output: `public/assets/portals/superadmin-portal/react/`
