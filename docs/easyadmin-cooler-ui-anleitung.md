# EasyAdmin Styling-Anleitung – moderner Look für Admin + bessere Detailansichten

## Ziel

Diese Anleitung beschreibt, wie der bestehende EasyAdmin-Bereich moderner, hochwertiger und strukturierter wirkt, **ohne** EasyAdmin technisch zu ersetzen.

Ziel ist:

- dunkles, modernes Admin-Theme
- hochwertigere Tabellen, Buttons, Karten und Formulare
- deutlich bessere Detailansichten von Datensätzen
- bessere visuelle Hierarchie
- weiterhin volle EasyAdmin-Funktionalität

WICHTIG:

- EasyAdmin bleibt bestehen
- Symfony bleibt führend
- keine Bearbeitung von `vendor/`
- keine wilden Overrides ohne Struktur
- keine Business-Logik ins Frontend verlagern

---

## Grundidee

Der größte Unterschied zwischen einem „Standard-EasyAdmin“ und einem hochwertigen Admin liegt meistens **nicht** an React, sondern an diesen vier Punkten:

1. globales Theme
2. bessere Feld-Darstellung
3. schönere Detailseiten
4. saubere Template-Overrides

Die beste Strategie ist deshalb:

- EasyAdmin technisch stabil lassen
- globales Admin-Theme einführen
- `index`, `detail`, `edit`, `new` optisch verbessern
- wichtige Detailansichten als strukturierte Datensatzseiten gestalten

---

## Empfohlene Projektstruktur

Lege die Styling- und Template-Dateien sauber an.

```text
assets/
  styles/
    admin-theme.css
  admin/
    admin-theme.js

templates/
  admin/
    field/
      status_badge.html.twig
      detail_header.html.twig
      money_card.html.twig
      meta_list.html.twig
    crud/
      detail_two_column.html.twig
    components/
      detail_kpi_row.html.twig
```

Falls im Projekt bereits eine andere Asset-Struktur existiert, diese übernehmen – aber logisch und konsistent bleiben.

---

## Schritt 1 – Globales Theme laden

### DashboardController anpassen

Im `DashboardController` die Assets global einbinden.

```php
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;

public function configureDashboard(): Dashboard
{
    return Dashboard::new()
        ->setTitle('Superadmin');
}

public function configureAssets(): Assets
{
    return Assets::new()
        ->addCssFile('build/admin-theme.css')
        ->addJsFile('build/admin-theme.js');
}
```

Damit wird das neue Theme in allen EasyAdmin-Seiten geladen.

---

## Schritt 2 – Dunkles EasyAdmin Theme bauen

### Datei

`assets/styles/admin-theme.css`

### Beispiel-CSS

```css
:root {
    --tf-bg: #111931;
    --tf-surface: #182341;
    --tf-surface-2: #223055;
    --tf-border: rgba(255,255,255,0.08);
    --tf-text: #eef3ff;
    --tf-text-soft: #b8c4e0;
    --tf-accent: #bbcf4e;
    --tf-accent-dark: #9daf3e;
    --tf-danger: #e25d6a;
    --tf-warning: #d8ad3d;
    --tf-success: #58b777;
    --tf-radius: 18px;
    --tf-shadow: 0 12px 34px rgba(0,0,0,0.28);
}

body.ea {
    background: var(--tf-bg);
    color: var(--tf-text);
}

.wrapper,
.content-wrapper,
.ea-content,
.main-content,
.content,
.ea-detail,
.ea-index,
.ea-edit,
.ea-new {
    background: transparent;
}

.sidebar {
    background: linear-gradient(180deg, #0f1730 0%, #162246 100%);
    border-right: 1px solid var(--tf-border);
}

.sidebar .menu .menu-item a {
    color: var(--tf-text-soft);
    border-radius: 12px;
    margin: 4px 10px;
    transition: all .18s ease;
}

.sidebar .menu .menu-item.active a,
.sidebar .menu .menu-item a:hover {
    background: rgba(187, 207, 78, 0.12);
    color: var(--tf-text);
}

.navbar,
.content-top,
.content-header {
    background: rgba(24, 35, 65, 0.82);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--tf-border);
}

.content-panel,
.form-panel,
.card,
.ea-detail .field-group,
.content-panel-body {
    background: var(--tf-surface);
    border: 1px solid var(--tf-border);
    border-radius: var(--tf-radius);
    box-shadow: var(--tf-shadow);
}

.table {
    color: var(--tf-text);
}

.table thead th {
    color: var(--tf-text-soft);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom-color: var(--tf-border);
}

.table td,
.table th {
    border-color: var(--tf-border);
    vertical-align: middle;
}

.table tbody tr:hover {
    background: rgba(255,255,255,0.02);
}

.btn-primary {
    background: var(--tf-accent);
    border-color: var(--tf-accent);
    color: #111931;
    font-weight: 700;
}

.btn-primary:hover,
.btn-primary:focus {
    background: var(--tf-accent-dark);
    border-color: var(--tf-accent-dark);
    color: #111931;
}

.btn-secondary,
.btn-light {
    background: var(--tf-surface-2);
    border-color: var(--tf-border);
    color: var(--tf-text);
}

.form-control,
.form-select {
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--tf-border);
    color: var(--tf-text);
    border-radius: 12px;
}

.form-control:focus,
.form-select:focus {
    border-color: rgba(187, 207, 78, 0.5);
    box-shadow: 0 0 0 0.2rem rgba(187, 207, 78, 0.15);
    background: rgba(255,255,255,0.06);
    color: var(--tf-text);
}

.badge {
    border-radius: 999px;
    padding: .45rem .7rem;
    font-weight: 700;
}

.pagination .page-link {
    background: var(--tf-surface);
    color: var(--tf-text);
    border-color: var(--tf-border);
}

.pagination .page-item.active .page-link {
    background: var(--tf-accent);
    border-color: var(--tf-accent);
    color: #111931;
}

.field-text .form-widget,
.field-textarea .form-widget,
.field-association .form-widget {
    color: var(--tf-text);
}
```

---

## Schritt 3 – Assets bauen

Je nach Setup zum Beispiel mit Encore oder dem vorhandenen Frontend-Build.

### Beispiel mit Encore

```js
// assets/admin/admin-theme.js
import '../styles/admin-theme.css';
```

Danach Build ausführen, z. B.:

```bash
npm run dev
```

oder für Produktion:

```bash
npm run build
```

---

## Schritt 4 – CRUD-Felder pro Seite besser definieren

Die gleiche Entity sollte auf `index`, `detail`, `edit` und `new` **nicht** immer gleich aussehen.

### Ziel

- `index`: kompakt, schnell lesbar
- `detail`: hochwertig, gruppiert, ausführlich
- `edit/new`: aufgeräumt und logisch aufgebaut

### Beispiel `configureFields()`

```php
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

public function configureFields(string $pageName): iterable
{
    if (Crud::PAGE_INDEX === $pageName) {
        yield IdField::new('id');
        yield TextField::new('name', 'Name');
        yield ChoiceField::new('status')
            ->setChoices([
                'Aktiv' => 'active',
                'Wartend' => 'pending',
                'Gesperrt' => 'blocked',
            ])
            ->renderAsBadges([
                'active' => 'success',
                'pending' => 'warning',
                'blocked' => 'danger',
            ]);

        yield IntegerField::new('credits', 'Credits');
        yield DateTimeField::new('createdAt', 'Erstellt');
        return;
    }

    if (Crud::PAGE_DETAIL === $pageName) {
        yield TextField::new('detailHeader', false)
            ->setVirtual(true)
            ->setTemplatePath('admin/field/detail_header.html.twig');

        yield FormField::addFieldset('Stammdaten');
        yield IdField::new('id');
        yield TextField::new('name', 'Name');
        yield TextField::new('email', 'E-Mail');

        yield FormField::addFieldset('Status & Konto');
        yield ChoiceField::new('status')
            ->setChoices([
                'Aktiv' => 'active',
                'Wartend' => 'pending',
                'Gesperrt' => 'blocked',
            ])
            ->renderAsBadges([
                'active' => 'success',
                'pending' => 'warning',
                'blocked' => 'danger',
            ]);

        yield IntegerField::new('credits', 'Credits');
        yield MoneyField::new('balance', 'Kontostand')->setCurrency('EUR');

        yield FormField::addFieldset('Beziehungen');
        yield AssociationField::new('company', 'Firma');
        yield AssociationField::new('orders', 'Bestellungen');

        yield FormField::addFieldset('Interne Informationen');
        yield TextareaField::new('notes', 'Notizen');
        yield DateTimeField::new('createdAt', 'Erstellt');
        yield DateTimeField::new('updatedAt', 'Aktualisiert');
        return;
    }

    yield FormField::addFieldset('Allgemein');
    yield TextField::new('name', 'Name');
    yield TextField::new('email', 'E-Mail');
    yield ChoiceField::new('status')
        ->setChoices([
            'Aktiv' => 'active',
            'Wartend' => 'pending',
            'Gesperrt' => 'blocked',
        ]);

    yield FormField::addFieldset('Weitere Daten');
    yield IntegerField::new('credits', 'Credits');
    yield TextareaField::new('notes', 'Notizen');
}
```

---

## Schritt 5 – Detailansichten wie echte Datensatzseiten gestalten

Die Detailansicht sollte nicht wie eine rohe Debug-Ausgabe wirken.

### Gute Struktur für Detailseiten

Oben:

- Titel / Name des Datensatzes
- ID
- Status
- wichtige Meta-Informationen
- Hauptaktion wie „Bearbeiten“

Darunter in Blöcken:

- Stammdaten
- Status / Finanzen / Konto
- Beziehungen
- Dateien / Medien
- technische Daten
- interne Notizen
- Historie / Timestamps

### Prinzip

- Informationen gruppieren
- wichtige Infos zuerst
- sekundäre Infos nach unten
- Status visuell hervorheben
- Beziehungen als saubere Liste oder Chips darstellen
- Zahlen formatiert anzeigen

---

## Schritt 6 – eigenes Header-Template für Detailansichten

### Datei

`templates/admin/field/detail_header.html.twig`

```twig
{% set entity = ea.entity.instance %}

<div class="tf-detail-hero">
    <div>
        <div class="tf-detail-eyebrow">Datensatz #{{ attribute(entity, 'id') ?? '-' }}</div>
        <h1 class="tf-detail-title">
            {{ attribute(entity, 'name') ?? attribute(entity, 'title') ?? 'Eintrag' }}
        </h1>
        <div class="tf-detail-meta">
            Erstellt: {{ attribute(entity, 'createdAt') ? attribute(entity, 'createdAt')|date('d.m.Y H:i') : '-' }}
        </div>
    </div>

    <div class="tf-detail-actions">
        <a class="btn btn-primary" href="{{ ea_url().setAction('edit').generateUrl() }}">Bearbeiten</a>
    </div>
</div>
```

### Ergänzung im CSS

```css
.tf-detail-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    padding: 22px 26px;
    margin-bottom: 22px;
    background: linear-gradient(135deg, rgba(187,207,78,.12), rgba(255,255,255,.03));
    border: 1px solid var(--tf-border);
    border-radius: 20px;
    box-shadow: var(--tf-shadow);
}

.tf-detail-eyebrow {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--tf-text-soft);
    margin-bottom: 8px;
}

.tf-detail-title {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: var(--tf-text);
}

.tf-detail-meta {
    margin-top: 8px;
    color: var(--tf-text-soft);
}

.tf-detail-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
```

---

## Schritt 7 – Status schöner darstellen

### Datei

`templates/admin/field/status_badge.html.twig`

```twig
{% set value = field.value %}

{% set map = {
    'active': {'label': 'Aktiv', 'class': 'badge bg-success-subtle text-success-emphasis'},
    'pending': {'label': 'Wartend', 'class': 'badge bg-warning-subtle text-warning-emphasis'},
    'blocked': {'label': 'Gesperrt', 'class': 'badge bg-danger-subtle text-danger-emphasis'}
} %}

{% set item = map[value] ?? {'label': value ?: '-', 'class': 'badge bg-secondary'} %}

<span class="{{ item.class }}">{{ item.label }}</span>
```

### Verwendung im CRUD

```php
yield TextField::new('status', 'Status')
    ->onlyOnDetail()
    ->setTemplatePath('admin/field/status_badge.html.twig');
```

---

## Schritt 8 – Detailseiten mit Spalten strukturieren

Für umfangreiche Datensätze sind Spalten nützlich.

### Idee

- linke Spalte: Hauptinformationen
- rechte Spalte: Status, Konto, Meta, Aktionen

### Beispiel

```php
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

if (Crud::PAGE_DETAIL === $pageName) {
    yield TextField::new('detailHeader', false)
        ->setVirtual(true)
        ->setTemplatePath('admin/field/detail_header.html.twig');

    yield FormField::addColumn(8);
    yield FormField::addFieldset('Stammdaten');
    yield TextField::new('name');
    yield TextField::new('email');
    yield TextareaField::new('notes');

    yield FormField::addColumn(4);
    yield FormField::addFieldset('Status & Meta');
    yield ChoiceField::new('status')->renderAsBadges();
    yield IntegerField::new('credits');
    yield DateTimeField::new('createdAt');
    yield DateTimeField::new('updatedAt');

    return;
}
```

Nicht jede EasyAdmin-Version oder jedes Layout verhält sich identisch – bei Bedarf mit eigenen Templates nachschärfen.

---

## Schritt 9 – Index-Seiten aufwerten

Auch die Listenansichten sollten hochwertiger wirken.

### Verbessern bei `index`

- Status als Badge
- Datumswerte formatieren
- Zahlen sauber formatieren
- wichtige Felder zuerst
- unwichtige Felder ausblenden
- Actions klar und schlank halten

### Beispiel

```php
if (Crud::PAGE_INDEX === $pageName) {
    yield IdField::new('id');
    yield TextField::new('name');
    yield TextField::new('email')->hideOnIndex(false);
    yield ChoiceField::new('status')->renderAsBadges();
    yield IntegerField::new('credits');
    yield DateTimeField::new('createdAt')->setFormat('short', 'short');
    return;
}
```

### Optische Ziele für Tabellen

- mehr Luft in den Zeilen
- bessere Hover-Zustände
- Buttons nicht zu groß
- Status nicht als roher Text
- relationale Werte lesbar statt technisch

---

## Schritt 10 – Actions schöner machen

EasyAdmin-Actions lassen sich pro Seite verbessern.

### Beispiel

```php
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

public function configureActions(Actions $actions): Actions
{
    $openPortal = Action::new('openPortal', 'Im Portal öffnen')
        ->linkToRoute('portal_customer_show', function ($entity) {
            return ['id' => $entity->getId()];
        })
        ->addCssClass('btn btn-primary');

    return $actions
        ->add(Crud::PAGE_DETAIL, $openPortal)
        ->update(Crud::PAGE_INDEX, Action::DETAIL, fn (Action $action) => $action->setIcon('fa fa-eye'))
        ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action->setIcon('fa fa-pen'))
        ->update(Crud::PAGE_DETAIL, Action::EDIT, fn (Action $action) => $action->setCssClass('btn btn-primary'));
}
```

### Gute Actions auf Detailseiten

- Bearbeiten
- im Portal öffnen
- Datei ansehen
- Kunde öffnen
- Credits buchen
- API prüfen
- Export / PDF

---

## Schritt 11 – gute Felder für schöne Admins

Einige Feldtypen geben direkt mehr Qualität.

Besonders nützlich:

- `ChoiceField` für Status und Typen
- `AssociationField` für Beziehungen
- `MoneyField` für Beträge
- `DateTimeField` für Meta-Daten
- `TextareaField` für längere Inhalte
- `CollectionField` für Listen / Sammlungen
- `CodeEditorField` für JSON, Logs, Configs
- `AvatarField` für Profilbilder oder Logos

Wichtig ist nicht nur das Feld selbst, sondern **wo** und **wie** es angezeigt wird.

---

## Schritt 12 – gute Detailansicht-Muster für deine Entities

### Kunde / User

Oben:

- Name
- E-Mail
- Status
- Credits
- Firma

Blöcke:

- Stammdaten
- Konto / Credits / Umsatz
- Beziehungen (Bestellungen, Dateien)
- interne Notizen
- Meta (erstellt, letzter Login, geändert)

### Bestellung / Auftrag

Oben:

- Auftragsnummer
- Kunde
- Status
- Preis
- Datum

Blöcke:

- Fahrzeugdaten
- Auftragsdaten
- Datei-Uploads
- Rechnungsdaten
- interne Bearbeitung

### Datei / Upload

Oben:

- Dateiname
- Kunde
- Upload-Datum
- Status

Blöcke:

- Dateiinfos
- Fahrzeugbezug
- Bearbeitungsstatus
- zugehörige Bestellung
- Download / Aktionen

---

## Schritt 13 – was du lieber nicht tun solltest

### Nicht tun

- `vendor/` direkt anfassen
- alles mit brutalem CSS `!important` erschlagen
- gleiche Felder blind für alle Seiten verwenden
- komplette CRUDs unnötig neu bauen
- Business-Logik in Twig schieben
- Detailseiten wie Rohdatenlisten belassen
- zu viele Farben / Effekte mischen
- halbe React-Lösungen ohne klare Architektur starten

### Stattdessen

- globales Theme
- klare CRUD-Konfiguration
- pro Seite unterschiedliche Darstellung
- kleine gezielte Twig-Overrides
- ein einheitliches Designsystem

---

## Schritt 14 – empfohlene Reihenfolge für die Umsetzung

### Phase 1

- globales CSS anlegen
- Assets im Dashboard einbinden
- Sidebar, Header, Buttons, Tabellen, Formulare modernisieren

### Phase 2

- wichtigste 2–3 CRUDs auswählen
- `index` kompakter und sauberer machen
- `detail` in Fieldsets und Blöcke aufteilen

### Phase 3

- `detail_header.html.twig` bauen
- `status_badge.html.twig` bauen
- evtl. `money_card.html.twig` oder `meta_list.html.twig` ergänzen

### Phase 4

- Actions verbessern
- sekundäre Templates verfeinern
- alles konsolidieren und Altlasten entfernen

---

## Schritt 15 – Ergebnisbild

Wenn die Anleitung sauber umgesetzt wird, sollte der Admin danach so wirken:

- dunkles modernes SaaS-Layout
- klare Sidebar
- hochwertiger Header
- Karten statt nackter Blöcke
- lesbare Tabellen
- moderne Formulare
- Detailseiten mit echter Struktur
- deutlich professioneller Gesamteindruck
- weiterhin volle EasyAdmin-Kompatibilität

---

## Kurzfazit

Für einen deutlich besseren EasyAdmin brauchst du **nicht sofort React**.

Der größte Hebel ist:

- sauberes Theme
- bessere Feld-Konfiguration
- schöne Detailseiten
- gezielte Twig-Templates

Gerade die **Detailansichten von Datensätzen** gewinnen am meisten, wenn du:

- einen Hero-Bereich oben baust
- Informationen in Blöcke gruppierst
- Status visuell hervorhebst
- Zahlen und Beziehungen schöner darstellst
- Meta-Daten klar und ruhig platzierst

So bleibt EasyAdmin wartbar, sieht aber deutlich hochwertiger aus.
