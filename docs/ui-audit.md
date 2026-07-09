# UI Audit

## Scope

- EasyAdmin bleibt die technische Basis.
- Der Redesign-Fokus liegt auf Dashboard, globalem Layout, Tabellen, Detailseiten, Sidebar und Header.

## Ist-Zustand

- Das Projekt besitzt bereits mehrere EasyAdmin-Overrides fuer `index`, `detail`, `edit` und einzelne Spezialseiten.
- Die Asset-Einbindung ist korrekt zentral im Dashboard-Controller ueber `configureAssets()` gelagert.
- Vor dem Redesign war die visuelle Sprache uneinheitlich:
  - gemischte Light- und Dark-Layer,
  - viele punktuelle Inline-Styles,
  - unterschiedliche Card-, Table- und Header-Muster.

## Hauptprobleme

- Dashboard hatte bereits Daten, aber keine starke SaaS-Hierarchie.
- KPI-Karten waren in Inhalt und Gewichtung uneinheitlich.
- Sidebar und Header wirkten funktional, aber nicht systematisch gestaltet.
- Detailseiten waren nur teilweise gruppiert; mehrere CRUDs fielen noch auf Standard-Feldlisten zurueck.
- Tabellen hatten zu wenig Tiefe und zu wenig visuelle Trennung.

## Technische Leitplanken

- Keine Vendor-Aenderungen.
- Keine Aenderung der CRUD-Logik.
- Keine React-SPA.
- Theme und Layout muessen mit bestehenden EasyAdmin-Block-Overrides und Embedded-Layouts kompatibel bleiben.

## Umsetzungsstrategie

1. Globales Designsystem zentral in `assets/styles/admin-theme.css`.
2. Dashboard als Hero + KPI Grid + Main Grid neu strukturieren.
3. Wiederverwendbare Twig-Bausteine fuer Status, Header und Meta.
4. Priority-CRUDs ueber `FormField::addFieldset()` und Detail-Templates staerken.
5. Inline-Styling aus zentralen Screens in CSS/JS verschieben.
