# Website Widget Kunden-Anleitung

Stand: 11.05.2026

Diese Anleitung beschreibt, wie ein Kunde im Portal sein Website Widget anlegt, bearbeitet und in die eigene Website einbindet.

## Zugang

1. Login-Seite oeffnen: `https://verwaltung.tuningfux.de/kunden-widget/login`.
2. Mit den Kundenzugangsdaten einloggen.
3. Nach dem Login oeffnet sich die Widget-Verwaltung unter `https://verwaltung.tuningfux.de/kunden-widget`.

Die Seite ist nur fuer eingeloggte Kunden verfuegbar. Jeder Kunde sieht und bearbeitet ausschliesslich sein eigenes Widget. Dieser Login ist ein eigener Zugang nur fuer die Website-Widget-Verwaltung und ist nicht der Admin-Login.

## Widget erstmalig anlegen

Wenn noch kein Widget vorhanden ist, zeigt die Seite den Hinweis, dass Vorschau und Einbettungs-Code erst nach dem Speichern erzeugt werden.

1. Gewuenschte Einstellungen ausfuellen.
2. `Widget aktiviert` eingeschaltet lassen, wenn das Widget sofort nutzbar sein soll.
3. Auf `Speichern` klicken.

Beim ersten Speichern werden automatisch erstellt:

- oeffentlicher Widget-Key
- interner API-Token
- JavaScript-Snippet
- iFrame-Snippet
- Vorschau-Link

Der API-Token wird dem Kunden nicht angezeigt und bleibt serverseitig im CRM.

## Allgemeine Einstellungen

- `Widget aktiviert`: schaltet das Widget oeffentlich ein oder aus.
- `Titel`: Haupttitel im Widget und auf der Anfrage-Seite.
- `Untertitel`: optionaler Beschreibungstext.
- `Button-Text`: Text des Anfrage-Buttons.
- `Erfolgs-Titel`: Ueberschrift nach erfolgreichem Absenden einer Anfrage.
- `Erfolgs-Nachricht`: optionaler Text nach erfolgreichem Absenden.
- `Layout`: visuelle Variante des Fahrzeugdatenbank-Widgets.
- `iFrame-Hoehe`: Hoehe des eingebetteten Widgets.

## Branding und Farben

Der Kunde kann die wichtigsten Farben selbst anpassen:

- Primaerfarbe
- Hintergrundfarbe
- Textfarbe
- Selectbox-Hintergrundfarbe
- Selectbox offen/Hover Hintergrundfarbe

Optional kann eigenes CSS hinterlegt werden. Fuer eigene Styles sollte der Selektor `.vdb-page` verwendet werden.

## Anfrageformular

Im Bereich `Anfrageformular` wird festgelegt, welche Felder Endkunden im Formular sehen.

Moegliche Felder:

- Telefon
- E-Mail
- FIN/VIN
- Kennzeichen
- Kilometerstand

Telefon und E-Mail koennen zusaetzlich als Pflichtfelder markiert werden. Deaktivierte Felder werden im Formular nicht angezeigt und auch nicht validiert.

## Datenschutz und Sicherheit

- `Datenschutz-Checkbox erforderlich`: Endkunden muessen die Datenschutz-Checkbox bestaetigen.
- `Datenschutz-URL`: Link zur Datenschutzerklaerung.
- `Datenschutz-Text`: Text neben der Checkbox.
- `Weiterleitung nach Absenden`: optionale URL, auf die nach erfolgreichem Absenden weitergeleitet wird.
- `Erlaubte Domains`: legt fest, auf welchen Domains das Widget eingebunden werden darf.
- `Tracking-Events senden`: erlaubt Tracking an die Fahrzeugdatenbank-API.

### Erlaubte Domains

Pro Zeile wird eine Domain eingetragen.

Beispiele:

```text
example.com
www.example.com
*.example.com
```

Bleibt das Feld leer, ist das Widget auf allen Domains erlaubt.

## Widget einbinden

Nach dem Speichern erscheinen zwei Varianten.

Empfohlen ist das JavaScript-Snippet:

```html
<div id="tuningfux-widget"></div>
<script src="https://verwaltung.tuningfux.de/widget/<embedKey>/embed.js"></script>
```

Alternativ kann ein iFrame genutzt werden:

```html
<iframe src="https://verwaltung.tuningfux.de/widget/<embedKey>" width="100%" height="850" frameborder="0" style="border:none;"></iframe>
```

Der Kunde kopiert das gewuenschte Snippet und fuegt es an der passenden Stelle in seiner Website ein.

## Vorschau pruefen

Nach dem Speichern kann der Kunde:

1. `Vorschau` oben rechts oeffnen.
2. Die Live-Vorschau auf der Widget-Seite pruefen.
3. Farben, Texte und Formularfelder anpassen.
4. Wieder speichern.

## Fehlerbehebung

### Widget wird nicht angezeigt

Pruefen:

- Ist `Widget aktiviert` eingeschaltet?
- Wurde das Widget mindestens einmal gespeichert?
- Ist das Snippet vollstaendig eingebunden?
- Passt die aktuelle Website-Domain zu `Erlaubte Domains`?

### Vorschau funktioniert, Einbindung aber nicht

Wahrscheinlich ist die Kundendomain nicht freigegeben. In diesem Fall die Domain in `Erlaubte Domains` eintragen oder das Feld leer lassen.

### Anfrage kommt nicht an

Pruefen:

- Ist beim Kunden im CRM eine E-Mail-Adresse hinterlegt?
- Sind Pflichtfelder im Formular korrekt ausgefuellt?
- Ist die Datenschutz-Checkbox aktiviert, falls sie erforderlich ist?

## Interner Hinweis fuer Support

Die Kundenroute lautet:

```text
https://verwaltung.tuningfux.de/kunden-widget
```

Der Login fuer Kunden lautet:

```text
https://verwaltung.tuningfux.de/kunden-widget/login
```

Die oeffentliche Widget-URL lautet:

```text
/widget/{embedKey}
```

Die JavaScript-Einbindung nutzt:

```text
/widget/{embedKey}/embed.js
```

Die Kundenseite verwendet die bestehende Entity `WebsiteWidgetConfig`. Neue Widgets werden automatisch dem eingeloggten Kunden zugeordnet.
