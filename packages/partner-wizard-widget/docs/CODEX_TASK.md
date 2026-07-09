# Codex Task — Partner Wizard Widget

Ziel: Das bestehende React/Vite Web Component Widget im Ordner `packages/partner-wizard-widget` bauen und auf dem Portal bereitstellen. Das Widget ist allgemein für eigene Websites und Portale gedacht; No1 ist nur eine konkrete Einbindung.

## Anforderungen

- Client-ID kommt aus der einbindenden Website, z. B. `no` für no1tuningfiles.com.
- Widget lädt Admin-Konfiguration von `https://portal.dct-files.de/api/tfd-partner-wizard/config/{clientId}`.
- Widget nutzt `/recommend` für Pakete/Tool-Empfehlung.
- Widget nutzt `/leads` für Absenden.
- Design bleibt No1-kompatibel, darf aber für weitere eigene Websites über Client-Konfiguration und CSS-Variablen genutzt werden.
- Keine feste Widget-Höhe setzen; der Inhalt soll auf der Website natürlich mitwachsen.
- CSS-Anpassung am Ende von `src/styles.css` nicht entfernen:
  - `.tfdWizard { margin:0; max-width:100%; }`
  - `.no1-partner-wizard-section`
  - `.no1-partner-wizard-container`
  - `tfd-partner-wizard`

## Commands

```bash
cd packages/partner-wizard-widget
rm -rf node_modules package-lock.json dist
npm cache verify
npm install
npm run build
```

Deploy:

```bash
rm -rf /var/www/vhosts/dct-files.de/portal.dct-files.de/public/widgets/partner-wizard/*
cp -R dist/* /var/www/vhosts/dct-files.de/portal.dct-files.de/public/widgets/partner-wizard/
```
