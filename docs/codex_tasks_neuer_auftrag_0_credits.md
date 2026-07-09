# Codex Tasks – Neuer Auftrag Flow mit 0 Credits + Test-Switch im Profil

## Ziel
Wenn ein User beim Erstellen eines neuen Auftrags **0 Credits** hat, soll er **nicht sofort blockiert** werden.

Stattdessen soll er nach **Schritt 2** normal zu **Schritt 3** kommen und dort:

- den Hinweis sehen, dass Credits fehlen
- die **genau benötigte Credit-Anzahl** für diesen Auftrag angezeigt bekommen
- Credits direkt kaufen können
- nach erfolgreicher PayPal-Zahlung automatisch mit dem Auftrag fortfahren
- erst **nach erfolgreichem Kauf** den Auftrag final platzieren
- für den Creditkauf eine **Rechnung** erhalten

---

## 1. Auftrag-Flow erweitern: 0-Credit-Fall nicht mehr vor Schritt 3 blockieren
**Task:**  
Passe die Logik im `Neuer Auftrag`-Flow an, damit User mit `0 Credits` nicht mehr vorzeitig blockiert werden.

**Akzeptanzkriterien:**
- User mit ausreichend Credits: bestehender Flow bleibt unverändert
- User mit 0 Credits: kommt nach Schritt 2 trotzdem auf Schritt 3
- Auftrag wird in diesem Fall **noch nicht final abgesendet**
- Stattdessen wird in Schritt 3 ein zusätzlicher Bereich `Credits kaufen` angezeigt

---

## 2. Benötigte Credits für den Auftrag berechnen
**Task:**  
Implementiere eine serverseitige Berechnung der für den aktuellen Auftrag benötigten Credits.

**Akzeptanzkriterien:**
- Die benötigte Credit-Anzahl wird aus den Auftragsdaten korrekt berechnet
- Die Berechnung erfolgt serverseitig und ist nicht nur Frontend-basiert
- Schritt 3 zeigt exakt:
  - vorhandene Credits
  - benötigte Credits
  - fehlende Credits
- Bei `0 Credits` soll direkt die volle benötigte Menge vorgeschlagen werden

---

## 3. UI in Schritt 3 erweitern: Zusatzanzeige „Credits kaufen“
**Task:**  
Ergänze Schritt 3 um eine Zusatzbox für fehlende Credits.

**Inhalt der Box:**
- Hinweistext: „Für diesen Auftrag sind X Credits erforderlich.“
- Anzeige:
  - Aktuelle Credits: `0`
  - Benötigt: `X`
  - Zu kaufen: `X`
- CTA-Button: **„Credits jetzt kaufen“**

**Akzeptanzkriterien:**
- Zusatzbox wird nur angezeigt, wenn Credits < benötigte Credits
- Bei genügend Credits wird die Box nicht angezeigt
- Layout ist konsistent mit bestehendem Credit-Kauf-Flow

---

## 4. Kaufmenge auf exakten Bedarf begrenzen
**Task:**  
Im Spezialfall aus dem Auftrags-Flow soll der User **genau die Anzahl** an Credits kaufen, die für diesen Auftrag benötigt wird.

**Akzeptanzkriterien:**
- Kaufmenge wird automatisch vorbelegt
- Kaufmenge entspricht exakt `fehlende Credits`
- User kann in diesem Flow die Menge nicht frei verändern
- Es wird klar kommuniziert, dass die Menge speziell für diesen Auftrag berechnet wurde

---

## 5. PayPal-Checkout an bestehenden Credit-Kauf-Flow anbinden
**Task:**  
Nutze für den Kauf aus Schritt 3 denselben PayPal-Checkout-Mechanismus wie im bestehenden Credit-Kauf-Flow.

**Akzeptanzkriterien:**
- Keine doppelte Zahlungslogik
- Bestehende PayPal-Integration wird wiederverwendet
- Der Checkout bekommt Metadaten mit:
  - User-ID
  - Auftrag-ID oder temporäre Auftrag-Referenz
  - benötigte Credit-Menge
  - Kontext: `order_flow_credit_topup`
- Nach Abbruch oder Fehler kehrt der User sauber zu Schritt 3 zurück

---

## 6. Auftrag vor Kauf in Pending-Zustand speichern
**Task:**  
Speichere die bisherigen Auftragsdaten vor dem PayPal-Redirect in einem temporären/pending Zustand.

**Akzeptanzkriterien:**
- Auftragsdaten aus Schritt 1 und 2 gehen nicht verloren
- Es gibt einen Status wie z. B.:
  - `draft`
  - `awaiting_credit_payment`
- Nach Rückkehr von PayPal kann der Flow exakt wieder aufgenommen werden
- Kein finaler Auftrag wird angelegt, solange die Zahlung nicht erfolgreich ist

---

## 7. Nach erfolgreicher Zahlung Credits buchen
**Task:**  
Nach erfolgreichem PayPal-Payment sollen die gekauften Credits dem User-Konto gutgeschrieben werden.

**Akzeptanzkriterien:**
- Credit-Buchung erfolgt nur bei bestätigter Zahlung
- Buchung ist idempotent
- Doppelte Webhooks / doppelte Rückkehr erzeugen keine Doppelgutschrift
- Credit-Transaktion wird protokolliert

---

## 8. Auftrag nach erfolgreicher Zahlung automatisch final platzieren
**Task:**  
Wenn die Zahlung erfolgreich war und die Credits gutgeschrieben wurden, soll der offene Auftrag automatisch final erstellt/platziert werden.

**Akzeptanzkriterien:**
- System prüft nach Gutschrift erneut, ob genug Credits vorhanden sind
- Benötigte Credits werden für den Auftrag direkt verbraucht
- Auftrag wird automatisch final abgesendet
- User landet auf Erfolgsseite / Auftragsdetailseite
- Erfolgsmeldung macht klar:
  - Credits gekauft
  - Auftrag erfolgreich platziert

---

## 9. Rechnung für den Creditkauf erzeugen
**Task:**  
Erstelle nach erfolgreichem Credit-Kauf automatisch eine Rechnung für die gekauften Credits.

**Akzeptanzkriterien:**
- Rechnung wird nur bei erfolgreicher Zahlung erzeugt
- Rechnung enthält:
  - Rechnungsnummer
  - Kundendaten
  - gekaufte Credit-Anzahl
  - Einzelpreis / Gesamtpreis
  - Zahlungsart PayPal
  - Datum
- Rechnung wird im bestehenden Rechnungsbereich abgelegt, falls vorhanden
- Rechnung kann per Mail versendet oder heruntergeladen werden

---

## 10. Trennung von Credit-Rechnung und Auftragsprozess sauber halten
**Task:**  
Sorge dafür, dass die Rechnung sich auf den **Creditkauf** bezieht, nicht versehentlich auf den Auftrag selbst.

**Akzeptanzkriterien:**
- Rechnungstyp eindeutig als `Credit-Kauf`
- Verknüpfung zur Auftragserstellung nur intern/metadatenbasiert
- Buchhalterisch sauber getrennt von möglicher Auftragsrechnung

---

## 11. Fehler- und Sonderfälle absichern
**Task:**  
Baue sauberes Handling für Edge Cases ein.

**Abzusichernde Fälle:**
- PayPal-Zahlung abgebrochen
- PayPal-Zahlung fehlgeschlagen
- User verlässt den Flow und kommt später zurück
- Credits wurden zwischenzeitlich manuell geändert
- Preis/Creditbedarf hat sich zwischen Schritt 3 und Zahlung verändert
- Webhook kommt doppelt
- Auftrag wurde bereits finalisiert

**Akzeptanzkriterien:**
- Keine Doppelaufträge
- Keine Doppelabrechnung
- Keine Doppelgutschrift
- User bekommt klare Fehlermeldungen
- System bleibt konsistent

---

## 12. Backend-Validierung final absichern
**Task:**  
Auch nach UI-Anpassung muss backendseitig verhindert werden, dass ein Auftrag ohne ausreichende Credits final erstellt wird.

**Akzeptanzkriterien:**
- Finale Auftragserstellung prüft Credits serverseitig
- Ohne bestätigte Zahlung kein finaler Auftrag
- Frontend allein kann den Prozess nicht umgehen

---

## 13. Audit-Log / Event-Tracking ergänzen
**Task:**  
Erfasse relevante Events für Nachvollziehbarkeit und Support.

**Zu loggende Events:**
- Auftrag begonnen
- Auftrag wartet auf Credit-Kauf
- PayPal-Checkout gestartet
- Zahlung erfolgreich
- Credits gutgeschrieben
- Rechnung erstellt
- Auftrag final platziert
- Zahlung abgebrochen / fehlgeschlagen

**Akzeptanzkriterien:**
- Support kann Flow nachvollziehen
- Events enthalten User-ID und Referenz auf Auftrag/Payment

---

## 14. Tests implementieren
**Task:**  
Erstelle automatisierte Tests für den neuen Flow.

**Mindestens testen:**
- User mit genügend Credits → normaler Flow
- User mit 0 Credits → Schritt 3 mit Zusatzanzeige
- exakte Creditmenge wird korrekt vorgeschlagen
- erfolgreicher PayPal-Flow → Credits + Rechnung + Auftrag
- abgebrochene Zahlung → kein Auftrag, keine Credits
- doppelte Callback/Webhook-Ausführung → keine Doppelbuchung
- Backend verhindert Auftrag ohne Credits

---

## 15. Test-Switch im Profil einbauen
**Task:**  
Baue im Profil einen **Test-Switch** ein, mit dem für Testzwecke simuliert werden kann, dass der User **0 Credits** hat.

**Ziel:**  
Der neue Auftrag-Flow mit Credit-Kauf soll einfach testbar sein, ohne echte Credits am Konto verändern zu müssen.

**Empfehlung:**  
Den Switch nur in Dev/Staging anzeigen oder zusätzlich per Feature-Flag absichern.

**Vorschlag UI:**
- Bereich: `Profil > Testmodus`
- Switch-Label: **„0 Credits simulieren“**

**Verhalten:**
- Wenn aktiv, behandelt das System den User im Auftrag-Flow so, als hätte er `0 Credits`
- Die echten Credits im Konto bleiben unverändert
- Die Simulation wirkt nur für den aktuellen Testkontext oder solange der Switch aktiv ist

**Akzeptanzkriterien:**
- Switch ist nicht für normale Live-User sichtbar, falls Production
- Aktivierter Switch überschreibt die Credit-Anzeige nur für den Testfall oder den Flow
- Echte Credit-Buchungen werden nicht manipuliert
- Im UI ist klar erkennbar, dass es sich um einen Testmodus handelt
- Testmodus kann jederzeit wieder deaktiviert werden

**Technische Hinweise:**
- Umsetzung z. B. per:
  - Feature Flag
  - Dev-only Profilsetting
  - Session-Flag
  - User-Meta-Feld `simulate_zero_credits`
- Bei aktiver Simulation sollen serverseitige Credit-Checks im Auftrag-Flow ebenfalls den simulierten Wert verwenden, aber nur in Test-/Dev-Kontext

---

## Technische Notizen für Codex

### Empfohlene Zustände
- `draft`
- `awaiting_credit_payment`
- `credit_payment_success`
- `submitted`
- `payment_failed`
- `payment_canceled`

### Wichtige Regeln
- Creditbedarf immer serverseitig berechnen
- Zahlungsbuchung idempotent machen
- Auftragsfinalisierung erst nach bestätigter Zahlung
- Bestehenden Credit-Kauf-Flow wiederverwenden, nicht neu bauen
- Test-Switch darf echte Credit-Daten nicht überschreiben

---

## Kompakte Übergabe an Codex
**Implementiere im „Neuer Auftrag“-Flow folgenden Sonderfall:**  
Wenn ein User 0 Credits hat, soll er nach Schritt 2 trotzdem Schritt 3 sehen. In Schritt 3 erscheint zusätzlich ein Bereich „Credits kaufen“ mit der exakt für den Auftrag benötigten Credit-Menge und einem PayPal-Button. Der bestehende Credit-Kauf-Flow soll wiederverwendet werden. Erst nach erfolgreicher PayPal-Zahlung werden die Credits gutgeschrieben, eine Rechnung für den Creditkauf erstellt und der Auftrag automatisch final platziert. Der Auftrag muss bis dahin in einem Pending-/Draft-Status gespeichert werden. Der gesamte Flow muss backendseitig validiert, idempotent und gegen Doppelbuchungen/Doppelaufträge abgesichert sein.

**Zusätzlich zum Testen:**  
Baue im Profil einen Test-Switch `0 Credits simulieren` ein. Wenn dieser aktiv ist, soll der User im Auftrag-Flow so behandelt werden, als hätte er 0 Credits, ohne dass echte Credits am Konto verändert werden. Der Switch sollte nur in Dev/Staging oder hinter einem Feature-Flag sichtbar sein.
