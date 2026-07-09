# Installation v9

## 1. Widget bauen

```bash
cd packages/partner-wizard-widget
rm -rf node_modules package-lock.json dist
npm cache verify
npm install
npm run build
```

## 2. Build auf Portal kopieren

```bash
rm -rf /var/www/vhosts/dct-files.de/portal.dct-files.de/public/widgets/partner-wizard/*
cp -R dist/* /var/www/vhosts/dct-files.de/portal.dct-files.de/public/widgets/partner-wizard/
```

## 3. No1 Embed

```html
<link rel="stylesheet" href="https://portal.dct-files.de/widgets/partner-wizard/style.css?v=900">

<section class="no1-partner-wizard-section">
  <div class="no1-partner-wizard-container">
    <tfd-partner-wizard
      client-id="no"
      api-base-url="https://portal.dct-files.de"
      primary-color="#E31E24"
      theme="light">
    </tfd-partner-wizard>
  </div>
</section>

<script src="https://portal.dct-files.de/widgets/partner-wizard/tfd-partner-wizard.umd.js?v=900"></script>
```

## 4. Cache umgehen

Bei jeder Änderung `v=900` auf `v=901`, `v=802` usw. erhöhen.
