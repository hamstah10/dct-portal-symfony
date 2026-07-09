# No1 Tuningfiles Portal Frontend

Das Kundenportal-Frontend fuer `portal/no1tuningfiles` wird mit CRACO/Create React App gebaut und nach `public/assets/portals/no1tuningfiles/react` geschrieben.

## Voraussetzungen

- Node.js 18+
- npm 9+
- keine Yarn-Installation erforderlich

## Wichtige Befehle

Im Verzeichnis `templates/portal/theme_react_no1tuningfiles/no1tuningfiles/frontend`:

### `npm install`

Installiert die Frontend-Abhaengigkeiten.

### `npm start`

Startet den Dev-Server.

### `npm run build`

Baut produktionsfaehige Assets fuer das Portal und schreibt sie nach:

`public/assets/portals/no1tuningfiles/react`

Das Script setzt `PUBLIC_URL=/assets/portals/no1tuningfiles/react` und kopiert anschliessend die Main-Assets auf die stabilen Namen `main.js` und `main.css`, die vom Twig-Host geladen werden.

### `npm test`

Startet die CRA-Testumgebung.

## Hinweis zum Build

Wenn ein Tool automatisch `yarn build` ausfuehrt, wird der Build auf diesem Host fehlschlagen, weil Yarn nicht installiert ist. Der unterstuetzte Produktionsbefehl ist:

```bash
npm run build
```

Weitere CRA-/CRACO-Details ergeben sich aus `craco.config.js` und den React-Quellen unter `src/`.
