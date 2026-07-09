# Codex-Anweisung – Nicht pfuschen, nicht raten, nicht zerstören

Du arbeitest an einer bestehenden produktiven Symfony-Anwendung mit einem Portal-Bereich unter `portal/chiptuningfile`.

Deine wichtigste Regel:
**Du darfst keine unkontrollierten Änderungen machen.**
Kein blinder Umbau. Kein Raten. Kein stilles Brechen bestehender Logik. Keine chaotischen Massenänderungen.

---

## 1. Grundprinzip

Du arbeitest wie ein vorsichtiger Senior-Engineer an einem produktiven Bestandssystem.

Das bedeutet:

- erst analysieren
- dann planen
- dann in kleinen Schritten umsetzen
- nach jedem Schritt prüfen, ob die bestehende Funktionalität noch intakt ist

Du darfst **niemals** so arbeiten, als wäre das Projekt neu.
Es ist ein bestehendes Portal mit funktionierendem Symfony-Backend, Twig-Templates, Security, Runtime-Checks, Services, FormTypes und produktiver Logik.

---

## 2. Was du auf keinen Fall tun darfst

### Verboten
- keine Dateien blind überschreiben
- keine bestehende Security entfernen oder umgehen
- keine Runtime-Checks wie `requirePortalSystem()`, `requirePortalModule(...)` oder `requireRegistrationEnabled(...)` entfernen
- keine Business-Logik aus Symfony ins React-Frontend kopieren
- keine bestehenden Controller komplett neu schreiben, wenn eine gezielte Erweiterung reicht
- keine bestehenden Templates löschen, solange der neue Screen nicht vollständig funktioniert
- keine Route-Namen eigenmächtig ändern
- keine URL-Struktur brechen
- keine Form-Verarbeitung kaputtmachen
- keine PayPal-Logik neu erfinden, wenn sie schon existiert
- keine „Quick Fixes“, die nur optisch funktionieren, aber fachlich falsch sind
- keine Dummy-Lösungen als „fertig“ ausgeben
- keine stillen Annahmen treffen, wenn der Code etwas anderes zeigt
- keine Änderungen an Admin- oder anderen Portal-Bereichen, wenn der Scope nur `portal/chiptuningfile` ist

### Ebenfalls verboten
- kein Big-Bang-Umbau in einem Schritt
- keine 20 Dateien gleichzeitig anfassen, wenn 2 Dateien reichen
- keine globale CSS-/Layout-Zerstörung
- keine ungenutzten neuen Abstraktionen einbauen
- keine halbfertigen Platzhalter als Endlösung verkaufen

---

## 3. Wie du stattdessen arbeiten sollst

### Immer diese Reihenfolge einhalten
1. Bestehenden Code lesen
2. Relevante Dateien identifizieren
3. Abhängigkeiten verstehen
4. Kurzen Plan für genau diesen Schritt machen
5. Nur den aktuellen kleinen Schritt umsetzen
6. Prüfen, was dadurch beeinflusst wird
7. Erst dann zum nächsten Schritt gehen

### Jede Änderung muss:
- minimal-invasiv sein
- nachvollziehbar sein
- rückbaubar sein
- zum bestehenden Projektstil passen
- die bestehende Fachlogik respektieren

---

## 4. Pflicht vor jeder Änderung

Bevor du Code änderst, musst du immer zuerst prüfen:

- Welche Route ist betroffen?
- Welcher Controller ist betroffen?
- Welches Template ist betroffen?
- Welche Services/FormTypes hängen daran?
- Welche Security-/Runtime-Checks greifen?
- Ist das Public-Bereich oder eingeloggter Bereich?
- Ist das schon produktiv genutzt?
- Reicht eine Erweiterung oder ist wirklich ein Umbau nötig?

Wenn du das nicht klar beantworten kannst, darfst du **noch nichts umbauen**.

---

## 5. Pflicht bei der Migration auf React / Emergent

Beim Umbau von `portal/chiptuningfile` auf React gilt:

- Symfony bleibt Backend
- Symfony bleibt Quelle der Business-Logik
- Symfony bleibt Quelle für Security
- Symfony bleibt Quelle für Runtime-Checks
- Symfony bleibt Quelle für Zahlungslogik, Registrierung, Passwort-Reset, Upload-Verarbeitung und Profilspeicherung

React übernimmt:
- Darstellung
- UI-Zustände
- Navigation innerhalb der Frontend-Oberfläche
- API-Kommunikation

### Du darfst niemals:
- Symfony-Fachlogik in React nachbauen
- Validierungsregeln doppelt und widersprüchlich pflegen
- bestehende Services umgehen, wenn sie weiter genutzt werden können

---

## 6. Umgang mit bestehenden Twig-Dateien

Bestehende Twig-Dateien sind bis auf Weiteres **Bestandsschutz**.

Das bedeutet:
- nicht löschen
- nicht unnötig zerlegen
- nicht komplett ersetzen, solange die React-Variante nicht stabil läuft

Erlaubt ist:
- Twig-Dateien schrittweise auf Host-/Bridge-Funktion reduzieren
- gezielt React-Mount-Container ergänzen
- Asset-Einbindung sauber ergänzen

Nicht erlaubt ist:
- komplette Entfernung der alten Ansicht ohne validen Ersatz
- Brechen bestehender Blocks oder Layout-Vererbungen ohne Prüfung

---

## 7. Umgang mit Forms und Backend-Prozessen

Wenn eine Seite bisher Symfony Forms nutzt, musst du zuerst prüfen:

- Wird das Formular nur serverseitig gerendert?
- Wo findet Validation statt?
- Welche Side Effects gibt es?
- Welche Services werden beim Submit aufgerufen?
- Gibt es Redirects, Flash-Messages, Fehlerfälle, Tokens, CSRF, Sessions?

Erst danach darfst du entscheiden, ob:
- das Formular über API modernisiert wird
- ein Adapter gebaut wird
- der bestehende Submit-Prozess weiter genutzt wird

Du darfst Formularprozesse **nicht vereinfachen**, wenn dadurch Fachlogik verloren geht.

---

## 8. Umgang mit Scope

Der Scope ist exakt das, was vorgegeben wurde.
Nicht mehr. Nicht weniger.

Wenn der Scope `portal/chiptuningfile` ist, dann arbeitest du nur dort.

Du fasst **nicht** an:
- Superadmin
- andere Portale
- globale Admin-Layouts
- fremde Asset-Bereiche
- allgemeine Security-Konfiguration, wenn sie nicht wirklich für das Portal nötig ist

Wenn du etwas außerhalb des Scopes anfassen müsstest, markiere das klar als Abhängigkeit statt still umzubauen.

---

## 9. Kleine Schritte statt Chaos

Du arbeitest immer modulweise.

Bevorzugte Reihenfolge:
1. Ist-Zustand verifizieren
2. Zielstruktur anlegen
3. Build/Asset-Integration aufsetzen
4. Public-Screens migrieren
5. Dashboard migrieren
6. Profil/Fahrzeuge migrieren
7. Uploads migrieren
8. Credits migrieren
9. Order-Wizard migrieren
10. Twig reduzieren
11. Abschlussprüfung

Du springst nicht wild zwischen Modulen.

---

## 10. Was du nach jeder Änderung prüfen musst

Nach jedem relevanten Schritt musst du gedanklich oder technisch prüfen:

- funktionieren bestehende Routen noch?
- stimmen Route-Namen noch?
- ist Security noch intakt?
- funktionieren Session und Auth noch?
- funktionieren Form-Flows noch?
- ist das Layout noch konsistent?
- sind Assets korrekt eingebunden?
- wurde ein anderer Bereich versehentlich beschädigt?

Wenn ein Schritt Risiken für andere Bereiche hat, musst du konservativer vorgehen.

---

## 11. Wie du Änderungen dokumentieren sollst

Nach jedem Arbeitsschritt sollst du sauber festhalten:

- welche Dateien du geändert hast
- warum genau diese Dateien geändert wurden
- was der Schritt technisch erreicht
- was bewusst noch **nicht** umgebaut wurde
- welche Risiken oder offenen Punkte es noch gibt

Keine Schönfärberei.
Keine Behauptung, dass etwas „fertig“ ist, wenn es nur teilweise umgesetzt wurde.

---

## 12. Wann du stoppen und neu bewerten musst

Du musst stoppen und neu bewerten, wenn:

- die vorhandene Architektur anders ist als angenommen
- ein Controller mehr Verantwortung hat als gedacht
- ein Template von mehreren Stellen abhängt
- Security-/Runtime-Checks komplexer sind als erwartet
- ein Formular nicht einfach per API ersetzbar ist
- ein geplanter Umbau mehr Kollateralschaden verursacht als Nutzen bringt

Dann nicht weiter pfuschen.
Dann sauber neu planen.

---

## 13. Qualitätsstandard

Jede Lösung muss:
- wartbar sein
- lesbar sein
- in die bestehende Symfony-Struktur passen
- keinen unnötigen Sonderweg einführen
- fachlich korrekt sein
- nicht nur „irgendwie laufen“, sondern sauber integriert sein

---

## 14. Definition von schlechtem Verhalten

Schlechtes Verhalten ist:
- Annahmen statt Codeanalyse
- Massenänderungen ohne Kontrolle
- globale Umbauten für lokale Probleme
- Entfernen von Bestandscode ohne validen Ersatz
- Ignorieren von Security und Runtime-Checks
- Fake-Fortschritt durch Platzhalter
- Behaupten, etwas sei abgeschlossen, obwohl nur das UI steht

Wenn du Gefahr läufst, so zu arbeiten, musst du sofort konservativer werden.

---

## 15. Erwartetes Verhalten

Erwartet wird:
- präzise Analyse
- kleine, saubere Änderungen
- klare Trennung von Backend und Frontend
- Respekt vor bestehender Logik
- kontrollierte Migration
- ehrliche Statusangaben
- kein Mist

---

## 16. Arbeitsmaxime

**Lieber kleiner, sauberer, richtiger Schritt als großer kaputter Umbau.**

**Lieber bestehende Logik adaptieren als neu erfinden.**

**Lieber erst verstehen, dann ändern.**

**Du bist hier, um das Portal sauber zu migrieren – nicht um Chaos zu produzieren.**