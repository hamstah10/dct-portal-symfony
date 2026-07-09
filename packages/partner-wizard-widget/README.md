# Partner Wizard Widget

Eigenständiges Web-Component-Widget für eigene Websites und Portale. Interessenten beantworten einen Fragebogen zu Fahrzeugarten, Leistungen und vorhandenem Tool und bekommen live eine passende Tool- und Partner-Empfehlung. Abgesendete Anfragen landen als Lead im EasyAdmin unter **Tools → Partner Wizard → Anfragen**.

## Einbau

```html
<link rel="stylesheet" href="https://portal.dct-files.de/widgets/partner-wizard/style.css?v=910">

<tfd-partner-wizard
  client-id="no"
  api-base-url="https://portal.dct-files.de"
  primary-color="#E31E24"
  theme="light">
</tfd-partner-wizard>

<script src="https://portal.dct-files.de/widgets/partner-wizard/tfd-partner-wizard.umd.js?v=910"></script>
```

`client-id` steuert, welche Konfiguration aus EasyAdmin geladen wird:

```txt
/api/tfd-partner-wizard/config/{clientId}
```

Die No1-Website nutzt aktuell `client-id="no"` und ist unter `/partner-wizard` eingebunden. Der Bereich **Händler werden** verlinkt auf diese eigene Partner-Wizard-Seite.

## EasyAdmin

Im EasyAdmin unter **Tools → Partner Wizard → Einstellungen** wird pro Client gepflegt:

- aktive Fahrzeugarten
- aktive Leistungen
- **Tools im Wizard** für die Frage “Hast du bereits ein eigenes Tool?”
- Tool-Empfehlungen, Pakete, Kombinationen und Leads

Die Auswahl **Tools im Wizard** nutzt die bestehenden Tool-Hersteller aus der Fahrzeugdatenbank. Diese Liste steuert nur die auswählbaren Karten bei der Frage nach dem vorhandenen Tool; sie ist getrennt von den Tool-Empfehlungen im Ergebnisbereich.

## Build

```bash
cd packages/partner-wizard-widget
NODENV_VERSION=20 npm run build
```

## Deploy

```bash
rm -rf /var/www/vhosts/dct-files.de/portal.dct-files.de/public/widgets/partner-wizard/*
cp -R dist/* /var/www/vhosts/dct-files.de/portal.dct-files.de/public/widgets/partner-wizard/
```

Nach jedem Deploy die Cache-Version im einbindenden Frontend erhöhen, z. B. `v=914`.
