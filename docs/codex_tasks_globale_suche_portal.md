# Codex Tasks – Globale Suchfunktion im Portal (React)

## Ziel
Die bestehende Dummy-Suche im Header soll zu einer **modernen globalen Suche** ausgebaut werden.

Die Suche soll nicht nur Text filtern, sondern als **globale Portal-Suche / Command Palette** funktionieren und folgende Bereiche abdecken:

- Navigation
- Quick Actions
- Aufträge
- geparkte Aufträge
- Kunden
- Fahrzeuge
- Rechnungen
- Credit-/Zahlungsbezüge

Die Suche soll in React sauber, schnell und erweiterbar umgesetzt werden.

---

## Empfehlung für die Umsetzung
Für das UI bietet sich eine **Command-Palette-artige Suche** an.  
Die shadcn/ui-Command-Komponente basiert auf **cmdk** und ist explizit für **Search und Quick Actions** gedacht. citeturn595607search0

Wenn ihr bereits shadcn/ui nutzt oder nutzen wollt, ist das ein sehr passender Weg.  
Die shadcn-CLI unterstützt React-Setups und kann Komponenten in bestehende Projekte integrieren. citeturn595607search1turn595607search4

---

## 1. Header-Dummy durch echte globale Suche ersetzen
**Task:**  
Ersetze das bestehende Dummy-Suchfeld im Header durch einen echten Einstiegspunkt für die globale Suche.

**Akzeptanzkriterien:**
- Die Suche ist im Header sichtbar
- Klick auf das Suchfeld oder den Trigger öffnet die globale Suche
- Die Suche ist von jeder relevanten Portal-Seite erreichbar
- Der bisherige Dummy-Zustand wird vollständig ersetzt

---

## 2. Such-UX als Modal / Command Palette aufbauen
**Task:**  
Baue die Suche als zentrales Overlay bzw. Modal mit Command-Palette-Verhalten.

**Empfehlung:**
- großes zentriertes Overlay
- Suchfeld oben
- gruppierte Ergebnisse darunter
- Tastatur-Navigation

**Akzeptanzkriterien:**
- Overlay öffnet sich über dem aktuellen Inhalt
- ESC schließt die Suche
- Pfeiltasten navigieren durch Treffer
- Enter öffnet den markierten Treffer oder führt die Aktion aus
- Kein Page-Reload nötig

---

## 3. Tastenkürzel integrieren
**Task:**  
Baue ein globales Shortcut für die Suche ein.

**Empfehlung:**
- `Ctrl + K` auf Windows/Linux
- `Cmd + K` auf macOS

**Akzeptanzkriterien:**
- Shortcut öffnet die Suche zuverlässig
- Shortcut funktioniert portalweit
- Bei geöffnetem Modal fokussiert der Cursor direkt das Suchfeld
- Shortcut kollidiert nicht mit kritischen bestehenden Shortcuts

---

## 4. Ergebnisquellen definieren und vereinheitlichen
**Task:**  
Führe ein gemeinsames Datenmodell für alle Suchtreffer ein, damit unterschiedliche Datentypen gemeinsam dargestellt werden können.

**Zu unterstützende Typen:**
- `navigation`
- `action`
- `order`
- `parked_order`
- `customer`
- `vehicle`
- `invoice`
- `credit_transaction`

**Akzeptanzkriterien:**
- Alle Treffer werden auf ein gemeinsames Shape normalisiert
- Unterschiedliche Entitäten lassen sich gemeinsam rendern
- Jede Entity kann Titel, Untertitel, Status und Zielaktion mitbringen
- Erweiterung um neue Typen ist später einfach möglich

**Vorschlag Datenstruktur:**
```ts
type SearchResult = {
  id: string
  type: "navigation" | "action" | "order" | "parked_order" | "customer" | "vehicle" | "invoice" | "credit_transaction"
  title: string
  subtitle?: string
  badge?: string
  url?: string
  keywords?: string[]
}
```

---

## 5. Navigationsziele durchsuchbar machen
**Task:**  
Mache zentrale Portal-Seiten direkt über die Suche erreichbar.

**Beispiele:**
- Dashboard
- Neuer Auftrag
- Aufträge
- geparkte Aufträge
- Credits
- Rechnungen
- Profil
- Kunden
- Fahrzeuge

**Akzeptanzkriterien:**
- User kann mit Suchbegriffen direkt zu Portal-Seiten springen
- Navigationsziele erscheinen in einer eigenen Gruppe
- Treffer lassen sich über Enter direkt öffnen

---

## 6. Quick Actions in die Suche integrieren
**Task:**  
Baue direkte Aktionen in die globale Suche ein.

**Beispiele:**
- Neuer Auftrag
- Credits kaufen
- Geparkten Auftrag weiterführen
- Rechnung öffnen
- Profil öffnen

**Akzeptanzkriterien:**
- Aktionen erscheinen als eigene Treffergruppe
- Aktionen können direkt ausgeführt werden
- Quick Actions sind visuell von normalen Suchresultaten unterscheidbar

---

## 7. Aufträge und geparkte Aufträge integrieren
**Task:**  
Binde die wichtigsten Auftragsdaten in die Suche ein.

**Suchbar nach:**
- Auftragsnummer
- Kunde
- Fahrzeug
- Kennzeichen
- Status
- Projekt-/Dateiname

**Akzeptanzkriterien:**
- Normale Aufträge und geparkte Aufträge werden gefunden
- Geparkte Aufträge sind klar gekennzeichnet
- Treffer enthalten genug Kontext für die Unterscheidung
- Aktion je Treffer:
  - Auftrag öffnen
  - Geparkten Auftrag weiterführen

---

## 8. Kunden- und Fahrzeugsuche ergänzen
**Task:**  
Erweitere die globale Suche um Kunden und Fahrzeuge.

**Akzeptanzkriterien:**
- Suche findet Kunden nach Name, Firma, E-Mail oder ID
- Suche findet Fahrzeuge nach Modell, Kennzeichen oder relevanten Stammdaten
- Treffer zeigen kompakte Zusatzinfos zur besseren Einordnung
- Klick führt zur passenden Detail- oder Listenansicht

---

## 9. Rechnungen und Credit-Bezüge durchsuchbar machen
**Task:**  
Ergänze Rechnungen sowie Credit-/Zahlungsbezüge als Suchquellen.

**Suchbar nach:**
- Rechnungsnummer
- Kunde
- Betrag
- Zahlungsart
- Credit-Kauf-Bezug

**Akzeptanzkriterien:**
- Rechnungen können direkt gefunden und geöffnet werden
- Credit-Käufe / relevante Zahlungen lassen sich auffinden
- Treffer enthalten Status-/Kontextinformationen

---

## 10. Gruppierte Ergebnisdarstellung bauen
**Task:**  
Zeige Suchtreffer in logisch getrennten Gruppen an.

**Empfohlene Gruppen:**
- Aktionen
- Navigation
- Aufträge
- Geparkte Aufträge
- Kunden
- Fahrzeuge
- Rechnungen
- Credits / Zahlungen

**Akzeptanzkriterien:**
- Ergebnisgruppen sind klar voneinander getrennt
- Leere Gruppen werden nicht angezeigt
- Gruppentitel bleiben konsistent
- Trefferlisten bleiben auch bei vielen Ergebnissen übersichtlich

---

## 11. Status-Badges und Kontextinfos anzeigen
**Task:**  
Reichere Treffer um Status-Badges und Metainformationen an.

**Beispiele:**
- `Geparkt`
- `In Bearbeitung`
- `Bezahlt`
- `Wartet auf Credits`

**Akzeptanzkriterien:**
- User erkennt wichtige Zustände direkt in der Ergebnisliste
- Auftrags- und Rechnungsstatus sind sofort sichtbar
- Zusatzinfos helfen bei der Auswahl ähnlicher Treffer

---

## 12. Suchlogik MVP: lokal + serverseitig vorbereiten
**Task:**  
Setze die Suche so auf, dass ein MVP schnell funktioniert, aber später auf echte API-Suche erweitert werden kann.

**Empfehlung für Phase 1:**
- Navigation + Quick Actions lokal
- zentrale Entitäten initial über API oder vorhandene Stores laden
- Suchservice abstrahieren

**Akzeptanzkriterien:**
- Suchlogik ist nicht hart in die UI eingebrannt
- Es gibt eine zentrale Suchschicht / Search Service
- Spätere Umstellung auf echten Backend-Search-Endpunkt ist vorbereitet

---

## 13. Fuzzy Search / unscharfe Suche einbauen
**Task:**  
Die Suche soll nicht nur exakte Treffer finden, sondern auch toleranter auf Eingaben reagieren.

**Empfehlung:**
- Fuzzy-Matching für Namen, Auftragsnummern, Fahrzeuge, Rechnungen
- Keywords / Alias-Felder unterstützen

**Akzeptanzkriterien:**
- Teilbegriffe liefern sinnvolle Treffer
- Tippfehler oder verkürzte Eingaben verschlechtern die UX nicht massiv
- Relevante Treffer erscheinen priorisiert

---

## 14. Debounce und Performance optimieren
**Task:**  
Optimiere die Suchanfragen für eine schnelle und flüssige UX.

**Akzeptanzkriterien:**
- Eingaben werden nicht unnötig oft verarbeitet
- Sinnvoller Debounce bei serverseitiger Suche
- Suche bleibt auch bei größeren Datenmengen reaktionsfähig
- UI friert während des Tippens nicht ein

---

## 15. Empty State und Default State gestalten
**Task:**  
Definiere gute Zustände für leere Suche und keine Treffer.

**Wenn das Feld leer ist, zeigen:**
- letzte Aufträge
- zuletzt geparkte Aufträge
- häufige Aktionen
- wichtige Seiten

**Wenn nichts gefunden wird, zeigen:**
- klare „Keine Treffer“-Meldung
- alternative Aktionen / Vorschläge

**Akzeptanzkriterien:**
- Leerzustand ist nützlich und nicht tot
- No-Results-State wirkt bewusst gestaltet
- User hat immer eine sinnvolle nächste Aktion

---

## 16. Ergebnispriorisierung definieren
**Task:**  
Bestimme eine sinnvolle Reihenfolge für Treffer.

**Beispielhafte Priorität:**
1. exakte Treffer
2. aktive / geparkte Aufträge
3. Kunden / Fahrzeuge
4. Rechnungen
5. Navigation / allgemeine Aktionen

**Akzeptanzkriterien:**
- Wichtige operative Ergebnisse stehen weiter oben
- Trefferreihenfolge ist nachvollziehbar
- Exakte Matches werden bevorzugt

---

## 17. Routing und Direktaktionen sauber umsetzen
**Task:**  
Jeder Treffer muss eine klare Folgeaktion haben.

**Akzeptanzkriterien:**
- Navigationstreffer öffnen Seiten
- Aufträge öffnen Detail oder Flow
- Geparkte Aufträge öffnen direkt im Fortsetzen-Flow
- Aktionen führen direkt aus, statt nur auf Listen zu springen
- Suchmodal schließt sich nach erfolgreicher Aktion sinnvoll

---

## 18. React-Komponentenstruktur aufbauen
**Task:**  
Implementiere die Suchfunktion modular und wiederverwendbar.

**Empfohlene Komponenten:**
- `GlobalSearchTrigger`
- `GlobalSearchModal`
- `GlobalSearchInput`
- `GlobalSearchResults`
- `GlobalSearchGroup`
- `GlobalSearchItem`
- `useGlobalSearch`
- `searchService`

**Akzeptanzkriterien:**
- UI und Suchlogik sind getrennt
- Komponenten sind testbar
- Darstellung und Datenquellen lassen sich unabhängig erweitern

---

## 19. State-Management definieren
**Task:**  
Lege fest, wie die Suche global gesteuert wird.

**Empfehlung:**
- Zustand oder Context für:
  - offen/geschlossen
  - query
  - loading
  - selected index
  - results
  - recent items

**Akzeptanzkriterien:**
- Suchzustand ist zentral steuerbar
- Modal lässt sich von Header und Shortcut aus öffnen
- Race Conditions beim Suchen werden sauber behandelt

---

## 20. Berechtigungen und Sichtbarkeit beachten
**Task:**  
Die Suche darf nur Treffer anzeigen, die der aktuelle User sehen darf.

**Akzeptanzkriterien:**
- Keine fremden oder unberechtigten Daten in den Suchergebnissen
- Rollen-/Rechtesystem wird respektiert
- Backend-Suche validiert Berechtigungen serverseitig

---

## 21. Dashboard- und Flow-Kontext optional berücksichtigen
**Task:**  
Optional kann die Suche kontextabhängig sinnvollere Vorschläge machen.

**Beispiele:**
- im Auftragsbereich zuerst Aufträge und geparkte Aufträge priorisieren
- im Rechnungsbereich Rechnungen priorisieren

**Akzeptanzkriterien:**
- Sucherfahrung fühlt sich kontextsensitiv an
- Priorisierung bleibt trotzdem verständlich

---

## 22. Logging / Analytics ergänzen
**Task:**  
Tracke die Nutzung der Suchfunktion.

**Zu loggende Events:**
- Suche geöffnet
- Query eingegeben
- Treffer geöffnet
- Quick Action ausgeführt
- No results

**Akzeptanzkriterien:**
- Nutzung der Suche ist messbar
- Support / Produkt kann erkennen, wonach oft gesucht wird
- Datenschutz und interne Richtlinien werden eingehalten

---

## 23. Tests implementieren
**Task:**  
Erstelle automatisierte Tests für die globale Suche.

**Mindestens testen:**
- Öffnen per Klick
- Öffnen per Shortcut
- ESC schließt die Suche
- Pfeiltasten-Navigation
- Enter öffnet Treffer
- Gruppierung der Ergebnisse
- Aufträge werden gefunden
- geparkte Aufträge werden gefunden
- Quick Actions funktionieren
- leere Suche zeigt sinnvolle Vorschläge
- Berechtigungen werden respektiert

---

## 24. Optional: Technologievorschlag für UI
**Task:**  
Wenn bereits shadcn/ui im Projekt ist oder eingeführt werden soll, nutze die Command-Komponente als Basis.  
Die shadcn/ui Command-Komponente nutzt **cmdk** und ist für **Command Menus für Search und Quick Actions** vorgesehen. citeturn595607search0

**Akzeptanzkriterien:**
- Keine unnötige Eigenentwicklung für Grundverhalten
- UI bleibt modern und gut wartbar
- Styling passt zum bestehenden Portal

---

## Technische Notizen für Codex

### Empfohlene Architektur
- React-Komponenten für UI
- zentraler Search Service
- einheitliches `SearchResult`-Modell
- zunächst Mischbetrieb aus statischen Quellen + API
- später ausbaubar auf echten `/api/search`-Endpunkt

### MVP-Scope
- Header Trigger
- Modal / Command Palette
- `Ctrl/Cmd + K`
- Navigation
- Quick Actions
- Aufträge
- geparkte Aufträge
- Kunden
- Rechnungen
- gruppierte Treffer
- Status-Badges

### Spätere Ausbaustufen
- bessere Ranking-Logik
- Recent Searches
- gespeicherte Suchen
- kontextsensitive Priorisierung
- vollständige serverseitige globale Suche

---

## Kompakte Übergabe an Codex
**Implementiere im Portal Chiptuningfile eine moderne globale Suchfunktion im Header als Command Palette.**  
Die aktuelle Dummy-Suche im Header soll durch eine echte React-Suche ersetzt werden, die sich per Klick und per `Ctrl/Cmd + K` öffnen lässt. Die Suche soll gruppierte Ergebnisse für Navigation, Quick Actions, Aufträge, geparkte Aufträge, Kunden, Fahrzeuge, Rechnungen und Credit-/Zahlungsbezüge anzeigen. Treffer sollen Tastatur-navigierbar sein, Status-Badges und Kontextinfos zeigen und direkt geöffnet oder ausgeführt werden können. Die Architektur soll modular, performant und erweiterbar sein, mit sauberem Search Service, einheitlichem Ergebnisformat, Berechtigungsprüfung und Tests. Falls shadcn/ui genutzt wird, kann die Command-Komponente als Basis verwendet werden, da sie auf cmdk aufsetzt und explizit für Search und Quick Actions gedacht ist. citeturn595607search0turn595607search1turn595607search4
