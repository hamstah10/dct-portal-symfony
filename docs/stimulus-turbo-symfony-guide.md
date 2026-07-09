# Stimulus + Turbo + Symfony - Vollstaendige Anleitung

> Basierend auf dem aktuellen Projektstand vom 20.03.2026 (Symfony 8, AssetMapper, Stimulus 3.2, Turbo aktiv, UX-Pakete)

---

## 1. Architektur-Überblick

```
Browser
  └── Twig-Template (HTML mit data-* Attributen)
        ├── Stimulus Controller (assets/controllers/*.js)
        │     └── kommuniziert mit Symfony via fetch() / Turbo
        ├── Turbo Drive  → SPA-Navigation ohne Reload
        ├── Turbo Frames → partielle Seitenaktualisierung
        ├── Turbo Streams → Server-Push nach Form-Submit
        └── Live Components → reaktive Server-Komponenten
```

**Kernprinzip:** HTML bleibt die "Single Source of Truth".
JavaScript *erweitert* das HTML, ersetzt es nicht.

---

## 2. Bestandsaufnahme dieses Projekts

| Was | Status | Datei |
|-----|--------|-------|
| Stimulus 3.2 | aktiv | `assets/stimulus_bootstrap.js` |
| `@symfony/stimulus-bundle` | aktiv | Auto-Discovery via `startStimulusApp()` |
| `@hotwired/turbo` | aktiv | `assets/app.js`, `assets/admin.js` |
| `symfony/ux-turbo` | installiert | `composer.json`, `assets/controllers.json` |
| UX Live Components | aktiv | `@symfony/ux-live-component` |
| UX Autocomplete | aktiv | Tom Select |
| UX ChartJS | aktiv | Chart.js |
| UX Notify | aktiv | SSE Toast-Notifications |
| Eigene Stimulus Controller | aktiv | `assets/controllers/` |

Aktuell vorhandene eigene Controller:

- `toggle_controller.js`
- `modal_controller.js`
- `async_fetch_controller.js`
- `form_submit_controller.js`

---

## 3. Stimulus Controller erstellen

### 3.1 Wie Auto-Discovery funktioniert

`startStimulusApp()` in `stimulus_bootstrap.js` scannt automatisch alle Dateien
in `assets/controllers/` die auf `_controller.js` enden.

```
assets/controllers/hello_controller.js        → data-controller="hello"
assets/controllers/modal_controller.js        → data-controller="modal"
assets/controllers/async_fetch_controller.js  → data-controller="async-fetch"
assets/controllers/form_submit_controller.js  → data-controller="form-submit"
```

**Namensregel:** Unterstriche `_` werden zu Bindestrichen `-` im HTML-Attribut.

### 3.2 Anatomie eines Stimulus Controllers

```js
// assets/controllers/beispiel_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    // 1. TARGETS: DOM-Elemente die der Controller kennt
    //    HTML: data-beispiel-target="output"
    static targets = ['output', 'input', 'button'];

    // 2. VALUES: Daten aus data-* Attributen (typisiert + reaktiv)
    //    HTML: data-beispiel-url-value="/api/daten"
    //    HTML: data-beispiel-delay-value="300"
    static values = {
        url:   String,
        delay: { type: Number, default: 300 },
        active: { type: Boolean, default: false },
    };

    // 3. CLASSES: CSS-Klassen aus data-* Attributen
    //    HTML: data-beispiel-loading-class="is-loading"
    static classes = ['loading', 'error'];

    // ── Lifecycle ──────────────────────────────────────────────

    connect() {
        // Controller wurde mit dem DOM-Element verbunden
        console.log('Verbunden:', this.element);
    }

    disconnect() {
        // Aufräumen: Timer, EventListener, AbortController etc.
    }

    // Wird automatisch aufgerufen wenn sich activeValue ändert
    activeValueChanged(newValue, oldValue) {
        this.element.classList.toggle('is-active', newValue);
    }

    // ── Actions (via data-action aufgerufen) ───────────────────

    handleClick(event) {
        event.preventDefault();
        this.outputTarget.textContent = this.inputTarget.value;
    }
}
```

### 3.3 Twig-Integration – alle Attribute im Überblick

```twig
<div
    data-controller="beispiel"

    data-beispiel-url-value="{{ path('api_endpoint') }}"
    data-beispiel-delay-value="500"
    data-beispiel-active-value="true"

    data-beispiel-loading-class="is-loading"
>
    {{-- Target-Elemente --}}
    <input data-beispiel-target="input" type="text" class="form-control">
    <span  data-beispiel-target="output"></span>

    {{-- Action: click → handleClick() --}}
    <button
        type="button"
        data-action="click->beispiel#handleClick"
        class="btn btn-primary"
    >
        Klick mich
    </button>

    {{-- Mehrere Actions auf einem Element --}}
    <input
        data-action="input->beispiel#handleInput keydown.enter->beispiel#handleSubmit"
        type="text"
    >

    {{-- Mehrere Controller auf einem Element --}}
    <div data-controller="toggle modal">
        ...
    </div>
</div>
```

### 3.4 Häufige Action-Deskriptoren

| Deskriptor | Bedeutung |
|-----------|-----------|
| `click->name#method` | Klick-Event |
| `input->name#method` | Input-Event (bei Texteingabe) |
| `change->name#method` | Change-Event (bei Select/Checkbox) |
| `submit->name#method` | Form-Submit |
| `keydown.enter->name#method` | Enter-Taste |
| `keydown.escape->name#method` | Escape-Taste |
| `mouseover->name#method` | Hover |
| `turbo:load@document->name#method` | Turbo-Navigation |

---

## 4. Vorhandene Beispiel-Controller

### 4.1 Toggle Controller (`toggle_controller.js`)

Schaltet Sichtbarkeit von Elementen um.

```twig
{# Einfaches Auf-/Zuklappen #}
<div data-controller="toggle">
    <button
        type="button"
        data-action="toggle#toggle"
        aria-expanded="false"
        class="btn btn-secondary"
    >
        <span data-toggle-target="openLabel">Mehr anzeigen</span>
        <span data-toggle-target="closeLabel" class="d-none">Weniger anzeigen</span>
    </button>

    <div data-toggle-target="content" class="d-none mt-3">
        Versteckter Inhalt hier...
    </div>
</div>

{# Standardmäßig offen #}
<div data-controller="toggle" data-toggle-open-value="true">
    <button type="button" data-action="toggle#toggle">Schließen</button>
    <div data-toggle-target="content">Sichtbarer Inhalt</div>
</div>

{# Mit eigener CSS-Klasse statt d-none #}
<div
    data-controller="toggle"
    data-toggle-hidden-class="visually-hidden"
>
    ...
</div>
```

### 4.2 Modal Controller (`modal_controller.js`)

Bootstrap-Modals mit optionalem AJAX-Inhalt.

```twig
{# Statischer Modal-Inhalt #}
<div data-controller="modal">
    <button type="button" data-action="modal#open" class="btn btn-primary">
        Details anzeigen
    </button>

    <div class="modal fade" data-modal-target="dialog" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Titel</h5>
                    <button type="button" class="btn-close" data-action="modal#close"></button>
                </div>
                <div class="modal-body">
                    Statischer Inhalt
                </div>
            </div>
        </div>
    </div>
</div>

{# AJAX-Inhalt (wird beim Öffnen geladen) #}
<div
    data-controller="modal"
    data-modal-url-value="{{ path('auftrag_detail_partial', {id: auftrag.id}) }}"
    data-modal-reload-value="false"
>
    <button type="button" data-action="modal#open" class="btn btn-sm btn-outline-primary">
        <i class="fa fa-eye"></i> Vorschau
    </button>

    <div class="modal fade" data-modal-target="dialog" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Auftrag #{{ auftrag.orderNumber }}</h5>
                    <button type="button" class="btn-close" data-action="modal#close"></button>
                </div>
                <div class="modal-body" data-modal-target="body">
                    {# Wird via AJAX befüllt #}
                </div>
            </div>
        </div>
    </div>
</div>
```

**Symfony-Controller für AJAX-Modal:**

```php
#[Route('/auftrag/{id}/partial', name: 'auftrag_detail_partial')]
public function partial(Auftrag $auftrag, Request $request): Response
{
    // Nur AJAX-Requests erlauben
    if (!$request->isXmlHttpRequest()) {
        return $this->redirectToRoute('auftrag_detail', ['id' => $auftrag->getId()]);
    }

    return $this->render('auftrag/_detail_partial.html.twig', [
        'auftrag' => $auftrag,
    ]);
}
```

### 4.3 Async Fetch Controller (`async_fetch_controller.js`)

Lädt Inhalte von Symfony-Endpoints.

```twig
{# Automatisch laden beim Seitenaufruf #}
<div
    data-controller="async-fetch"
    data-async-fetch-url-value="{{ path('api_statistiken') }}"
    data-async-fetch-auto-load-value="true"
>
    <div data-async-fetch-target="loading">
        <div class="spinner-border spinner-border-sm"></div> Lade...
    </div>
    <div data-async-fetch-target="output"></div>
</div>

{# Suche mit Debounce #}
<div
    data-controller="async-fetch"
    data-async-fetch-url-value="{{ path('api_fahrzeuge_suche') }}"
    data-async-fetch-debounce-value="400"
    data-async-fetch-param-name-value="q"
    data-async-fetch-min-length-value="2"
    data-async-fetch-empty-message-value="Keine Fahrzeuge gefunden."
>
    <input
        type="text"
        data-async-fetch-target="input"
        data-action="input->async-fetch#search"
        placeholder="Fahrzeug suchen..."
        class="form-control"
    >
    <div data-async-fetch-target="loading" class="d-none mt-2">
        <div class="spinner-border spinner-border-sm"></div>
    </div>
    <div data-async-fetch-target="output" class="mt-3"></div>
</div>

{# Manuell laden via Button #}
<div
    data-controller="async-fetch"
    data-async-fetch-url-value="{{ path('api_bericht_generieren') }}"
>
    <button
        type="button"
        data-action="async-fetch#load"
        class="btn btn-primary"
    >
        Bericht laden
    </button>
    <div data-async-fetch-target="output" class="mt-3"></div>
</div>
```

**Symfony-Controller für Async Fetch (HTML-Antwort):**

```php
#[Route('/api/fahrzeuge/suche', name: 'api_fahrzeuge_suche')]
public function suche(Request $request): Response
{
    $query = $request->query->get('q', '');
    $fahrzeuge = $this->fahrzeugRepository->suche($query);

    // HTML-Fragment zurückgeben
    return $this->render('partials/_fahrzeug_liste.html.twig', [
        'fahrzeuge' => $fahrzeuge,
    ]);
}
```

**Symfony-Controller für Async Fetch (JSON-Antwort):**

```php
#[Route('/api/statistiken', name: 'api_statistiken')]
public function statistiken(): JsonResponse
{
    return $this->json([
        'html' => $this->renderView('partials/_statistiken.html.twig', [
            'daten' => $this->statistikService->getDaten(),
        ]),
    ]);
}
```

### 4.4 Form Submit Controller (`form_submit_controller.js`)

AJAX-Formular-Submit mit Loading-State und Fehlerbehandlung.

```twig
<form
    action="{{ path('kontakt_senden') }}"
    method="POST"
    data-controller="form-submit"
    data-form-submit-success-message-value="Nachricht erfolgreich gesendet!"
    data-form-submit-reset-on-success-value="true"
>
    {{ csrf_token('kontakt') }}

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">E-Mail</label>
        <input type="email" name="email" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Nachricht</label>
        <textarea name="nachricht" class="form-control" rows="4"></textarea>
    </div>

    <div data-form-submit-target="feedback" class="d-none"></div>

    <button
        type="submit"
        data-form-submit-target="submit"
        class="btn btn-primary"
    >
        Senden
    </button>
</form>
```

**Symfony-Controller für Form Submit:**

```php
#[Route('/kontakt/senden', name: 'kontakt_senden', methods: ['POST'])]
public function senden(Request $request): JsonResponse
{
    // CSRF prüfen
    if (!$this->isCsrfTokenValid('kontakt', $request->request->get('_token'))) {
        return $this->json(['success' => false, 'message' => 'Ungültiges Token.'], 403);
    }

    $name     = $request->request->get('name', '');
    $email    = $request->request->get('email', '');
    $nachricht = $request->request->get('nachricht', '');

    // Validierung
    $errors = [];
    if (empty($name)) {
        $errors['name'] = 'Name ist ein Pflichtfeld.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Ungültige E-Mail-Adresse.';
    }

    if (!empty($errors)) {
        return $this->json([
            'success' => false,
            'message' => 'Bitte alle Felder korrekt ausfüllen.',
            'errors'  => $errors,
        ], 422);
    }

    // Verarbeitung...
    $this->mailerService->sendKontakt($name, $email, $nachricht);

    return $this->json([
        'success' => true,
        'message' => 'Nachricht erfolgreich gesendet!',
    ]);
}
```

---

## 5. Turbo im aktuellen Projekt

Turbo ist bereits eingebunden und muss fuer dieses Projekt nicht erst nachinstalliert werden.

### 5.1 Ist-Zustand

- `@hotwired/turbo` wird in `assets/app.js` importiert.
- `@hotwired/turbo` wird in `assets/admin.js` importiert.
- `symfony/ux-turbo` ist in `composer.json` vorhanden.
- In `assets/controllers.json` ist `@symfony/ux-turbo` aktiviert.
- `mercure-turbo-stream` ist aktuell deaktiviert.

### 5.2 Tatsächliche Einbindung

```js
// assets/app.js
import './stimulus_bootstrap.js';
import '@hotwired/turbo';
```

```js
// assets/admin.js
import './stimulus_bootstrap.js';
import '@hotwired/turbo';
```

### 5.3 Relevante Nutzung im Projekt

- Im Frontend wird `turbo:load` bereits fuer Matomo-Tracking verwendet.
- Im Admin wird Turbo ebenfalls geladen; Re-Initialisierungen nach Navigation sollten an `turbo:load` haengen.
- Es gibt aktuell keinen belegten Swup-Setup mehr in `composer.json`, `importmap.php` oder `assets/controllers.json`.

---

## 6. Turbo Drive (SPA-Navigation)

Turbo Drive fängt alle `<a>`-Klicks und `<form>`-Submits ab und ersetzt nur
`<body>` + `<title>` – ohne vollen Seitenreload.

### 6.1 Turbo für bestimmte Links deaktivieren

```twig
{# Normaler Browser-Reload erzwingen #}
<a href="/download/datei.pdf" data-turbo="false">Download</a>

{# Ganzen Bereich von Turbo ausschließen #}
<div data-turbo="false">
    <a href="/extern">Externer Link</a>
</div>
```

### 6.2 Turbo-Events in Stimulus nutzen

```js
// assets/controllers/page_tracker_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        document.addEventListener('turbo:load', this.#onLoad);
        document.addEventListener('turbo:before-visit', this.#onBeforeVisit);
    }

    disconnect() {
        document.removeEventListener('turbo:load', this.#onLoad);
        document.removeEventListener('turbo:before-visit', this.#onBeforeVisit);
    }

    #onLoad = () => {
        // Wird bei JEDER Turbo-Navigation aufgerufen
        // z.B. Analytics, Re-Initialisierungen
    };

    #onBeforeVisit = (event) => {
        // Vor der Navigation – kann abgebrochen werden
        // event.detail.url enthält die Ziel-URL
    };
}
```

---

## 7. Turbo Frames (Partielle Seitenaktualisierung)

Turbo Frames aktualisieren nur einen definierten Bereich der Seite –
**ohne JavaScript zu schreiben**.

### 7.1 Grundprinzip

```twig
{# templates/website/produkte/index.html.twig #}

<turbo-frame id="produkt-liste">
    {% for produkt in produkte %}
        <div class="card mb-2">
            <div class="card-body">
                <h5>{{ produkt.name }}</h5>
                {# Link innerhalb des Frames → aktualisiert nur den Frame #}
                <a href="{{ path('produkt_detail', {id: produkt.id}) }}">
                    Details
                </a>
            </div>
        </div>
    {% endfor %}

    {# Pagination innerhalb des Frames #}
    <nav>
        <a href="{{ path('produkte', {page: currentPage + 1}) }}">
            Nächste Seite →
        </a>
    </nav>
</turbo-frame>
```

```twig
{# templates/website/produkte/detail.html.twig #}
{# Symfony rendert die ganze Seite, Turbo nimmt nur den passenden Frame #}

<turbo-frame id="produkt-liste">
    <h2>{{ produkt.name }}</h2>
    <p>{{ produkt.beschreibung }}</p>
    <a href="{{ path('produkte') }}">← Zurück zur Liste</a>
</turbo-frame>
```

### 7.2 Lazy-Loading mit Turbo Frames

```twig
{# Frame wird erst geladen wenn er im Viewport erscheint #}
<turbo-frame
    id="statistiken-widget"
    src="{{ path('api_statistiken_widget') }}"
    loading="lazy"
>
    <div class="text-center py-3">
        <div class="spinner-border spinner-border-sm"></div>
        Lade Statistiken...
    </div>
</turbo-frame>
```

```php
// src/Controller/Api/StatistikController.php
#[Route('/api/statistiken/widget', name: 'api_statistiken_widget')]
public function widget(): Response
{
    return $this->render('partials/_statistiken_widget.html.twig', [
        'daten' => $this->statistikService->getWidget(),
    ]);
}
```

```twig
{# templates/partials/_statistiken_widget.html.twig #}
{# MUSS die gleiche turbo-frame id haben! #}
<turbo-frame id="statistiken-widget">
    <div class="row g-2">
        {% for item in daten %}
            <div class="col-6">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="h3">{{ item.wert }}</div>
                        <div class="small text-muted">{{ item.label }}</div>
                    </div>
                </div>
            </div>
        {% endfor %}
    </div>
</turbo-frame>
```

### 7.3 Frame aus einem anderen Kontext aktualisieren

```twig
{# Link der einen ANDEREN Frame aktualisiert #}
<a
    href="{{ path('sidebar_content') }}"
    data-turbo-frame="sidebar"
>
    Sidebar aktualisieren
</a>

<turbo-frame id="sidebar">
    Aktueller Sidebar-Inhalt
</turbo-frame>
```

---

## 8. Turbo Streams (Server-Push nach Form-Submit)

Turbo Streams ermöglichen es, nach einem Form-Submit **mehrere Bereiche**
der Seite gleichzeitig zu aktualisieren.

### 8.1 Symfony-Controller

```php
// src/Controller/AuftragController.php
use Symfony\UX\Turbo\TurboBundle;

#[Route('/auftrag/erstellen', name: 'auftrag_erstellen', methods: ['POST'])]
public function erstellen(Request $request): Response
{
    $auftrag = new Auftrag();
    $form = $this->createForm(AuftragType::class, $auftrag);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $this->em->persist($auftrag);
        $this->em->flush();

        // Turbo Stream Response
        if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            return $this->render('auftrag/_stream_erstellt.html.twig', [
                'auftrag' => $auftrag,
            ]);
        }

        // Fallback für normale Requests
        return $this->redirectToRoute('auftrag_liste');
    }

    return $this->render('auftrag/erstellen.html.twig', ['form' => $form]);
}
```

### 8.2 Stream-Template

```twig
{# templates/auftrag/_stream_erstellt.html.twig #}

{# Neuen Auftrag oben in die Liste einfügen #}
<turbo-stream action="prepend" target="auftrag-liste">
    <template>
        {{ include('auftrag/_auftrag_row.html.twig', {auftrag: auftrag}) }}
    </template>
</turbo-stream>

{# Zähler aktualisieren #}
<turbo-stream action="replace" target="auftrag-count">
    <template>
        <span id="auftrag-count" class="badge text-bg-primary">
            {{ auftragCount }}
        </span>
    </template>
</turbo-stream>

{# Formular zurücksetzen #}
<turbo-stream action="replace" target="auftrag-formular">
    <template>
        <div id="auftrag-formular">
            {{ include('auftrag/_form.html.twig') }}
        </div>
    </template>
</turbo-stream>
```

### 8.3 Verfügbare Stream-Actions

| Action | Beschreibung |
|--------|-------------|
| `append` | Am Ende des Targets einfügen |
| `prepend` | Am Anfang des Targets einfügen |
| `replace` | Target komplett ersetzen (inkl. Wrapper-Element) |
| `update` | Nur Inhalt des Targets ersetzen |
| `remove` | Target aus dem DOM entfernen |
| `before` | Vor dem Target einfügen |
| `after` | Nach dem Target einfügen |

---

## 9. Live Components (bereits installiert)

Live Components sind reaktive Server-Komponenten – wie React/Vue,
aber gerendert von Symfony/Twig.

### 9.1 PHP-Komponente erstellen

```php
// src/Twig/Components/FahrzeugSucheComponent.php
namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use App\Repository\VehicleEngineRepository;

#[AsLiveComponent]
class FahrzeugSucheComponent
{
    use DefaultActionTrait;

    // LiveProp: Wird bei Änderung automatisch neu gerendert
    #[LiveProp(writable: true)]
    public string $suchbegriff = '';

    #[LiveProp(writable: true)]
    public string $kraftstoff = 'alle';

    public function __construct(
        private readonly VehicleEngineRepository $engineRepository
    ) {}

    public function getErgebnisse(): array
    {
        if (strlen($this->suchbegriff) < 2) {
            return [];
        }

        return $this->engineRepository->suche(
            $this->suchbegriff,
            $this->kraftstoff !== 'alle' ? $this->kraftstoff : null
        );
    }
}
```

### 9.2 Twig-Template der Komponente

```twig
{# templates/components/FahrzeugSucheComponent.html.twig #}
<div {{ attributes }}>
    <div class="row g-2 mb-3">
        <div class="col-md-8">
            <input
                type="text"
                data-model="suchbegriff"
                placeholder="Fahrzeug suchen..."
                class="form-control"
            >
        </div>
        <div class="col-md-4">
            <select data-model="kraftstoff" class="form-select">
                <option value="alle">Alle Kraftstoffe</option>
                <option value="benzin">Benzin</option>
                <option value="diesel">Diesel</option>
                <option value="elektro">Elektro</option>
            </select>
        </div>
    </div>

    {% if suchbegriff|length >= 2 %}
        {% set ergebnisse = this.ergebnisse %}
        {% if ergebnisse is empty %}
            <p class="text-muted">Keine Ergebnisse für "{{ suchbegriff }}"</p>
        {% else %}
            <div class="list-group">
                {% for engine in ergebnisse %}
                    <a
                        href="{{ path('app_vehicle_database', {engineId: engine.id}) }}"
                        class="list-group-item list-group-item-action"
                    >
                        <strong>{{ engine.name }}</strong>
                        <span class="text-muted ms-2">{{ engine.horsepower }} PS</span>
                    </a>
                {% endfor %}
            </div>
        {% endif %}
    {% endif %}
</div>
```

### 9.3 Komponente einbinden

```twig
{# In jedem Twig-Template #}
{{ component('FahrzeugSucheComponent') }}

{# Mit initialen Props #}
{{ component('FahrzeugSucheComponent', {
    suchbegriff: 'BMW',
    kraftstoff: 'benzin'
}) }}
```

### 9.4 Live Actions (Button-Klicks die Server-Logik auslösen)

```php
// In der Komponente
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;

#[LiveAction]
public function zuFavoriten(#[LiveArg] int $engineId): void
{
    $this->favoritenService->hinzufuegen($engineId);
    // Komponente wird automatisch neu gerendert
}
```

```twig
{# Im Template #}
<button
    type="button"
    data-action="live#action"
    data-live-action-param="zuFavoriten"
    data-live-engine-id-param="{{ engine.id }}"
    class="btn btn-sm btn-outline-warning"
>
    ♥ Favorit
</button>
```

---

## 10. Mehrere Controller kombinieren

Stimulus-Controller können auf demselben Element kombiniert werden:

```twig
{# Toggle + Async Fetch kombiniert: Inhalt wird beim ersten Öffnen geladen #}
<div
    data-controller="toggle async-fetch"
    data-async-fetch-url-value="{{ path('api_details', {id: item.id}) }}"
    data-toggle-open-value="false"
>
    <button
        type="button"
        data-action="toggle#toggle async-fetch#load"
        class="btn btn-link"
    >
        Details laden
    </button>

    <div data-toggle-target="content" data-async-fetch-target="output" class="d-none mt-2">
        <div class="spinner-border spinner-border-sm"></div>
    </div>
</div>
```

---

## 11. Controller-Kommunikation via Events

Stimulus-Controller können miteinander kommunizieren ohne direkte Abhängigkeiten.

```js
// Controller A sendet ein Event
// assets/controllers/filter_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { kategorie: String };

    aendern(event) {
        this.kategorieValue = event.currentTarget.value;

        // Custom Event dispatchen – andere Controller können lauschen
        this.dispatch('changed', {
            detail: { kategorie: this.kategorieValue },
            bubbles: true,
        });
    }
}
```

```js
// Controller B empfängt das Event
// assets/controllers/liste_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['item'];

    // Wird aufgerufen wenn filter:changed Event empfangen wird
    // data-action="filter:changed->liste#filtern"
    filtern(event) {
        const { kategorie } = event.detail;

        this.itemTargets.forEach((item) => {
            const match = kategorie === 'alle' || item.dataset.kategorie === kategorie;
            item.classList.toggle('d-none', !match);
        });
    }
}
```

```twig
{# Twig-Template #}
<div
    data-controller="filter"
    data-action="filter:changed->liste#filtern"
>
    <select data-action="change->filter#aendern" class="form-select">
        <option value="alle">Alle</option>
        <option value="tuning">Tuning</option>
        <option value="zubehoer">Zubehör</option>
    </select>
</div>

<div data-controller="liste">
    <div data-liste-target="item" data-kategorie="tuning">Tuning-Produkt 1</div>
    <div data-liste-target="item" data-kategorie="zubehoer">Zubehör 1</div>
    <div data-liste-target="item" data-kategorie="tuning">Tuning-Produkt 2</div>
</div>
```

---

## 12. Best Practices für dieses Projekt

### 12.1 Wann was verwenden?

| Anforderung | Empfehlung |
|-------------|-----------|
| Einfaches Auf-/Zuklappen | `toggle_controller.js` |
| Modal mit AJAX-Inhalt | `modal_controller.js` |
| Daten nachladen (Suche, Listen) | `async_fetch_controller.js` |
| Formular ohne Seitenreload | `form_submit_controller.js` |
| Reaktive Suche/Filter (komplex) | Live Component |
| Partielle Seitenaktualisierung | Turbo Frame |
| Nach Form-Submit mehrere Bereiche updaten | Turbo Stream |
| SPA-Navigation | Turbo Drive |
| Echtzeit-Updates (WebSocket/SSE) | UX Notify (bereits aktiv) |

### 12.2 Inline-JavaScript vermeiden

**Schlecht** (wie aktuell in `vehicle_database.html.twig`):
```twig
{# ❌ Hunderte Zeilen Inline-JS im Template #}
<script>
(() => {
    const selectType = document.getElementById('typeSelect');
    // ... 1000 Zeilen JS
})();
</script>
```

**Besser** (mit Stimulus):
```twig
{# ✅ Logik im Controller, Template bleibt sauber #}
<div
    data-controller="vehicle-database"
    data-vehicle-database-api-base-value="{{ path('app_vehicle_database_types')|replace({'/types': ''}) }}"
    data-vehicle-database-template-variant-value="{{ frontendSettings.templateVariant|default('performance') }}"
>
    <select data-vehicle-database-target="typeSelect" data-action="change->vehicle-database#onTypeChange">
        <option value="">Typ wählen</option>
    </select>
    {# ... #}
</div>
```

### 12.3 Turbo + Entrypoints

Turbo ist in beiden Entrypoints aktiv:

- `assets/app.js`
- `assets/admin.js`

Im Frontend wird `document.addEventListener('turbo:load', trackMatomoPageView);` bereits verwendet. Dieselbe Regel gilt fuer weitere Re-Initialisierungen im Admin.

### 12.4 AssetMapper vs. npm

Dieses Projekt nutzt **AssetMapper** (kein Node.js/npm nötig für Basis-Setup).
Neue Pakete hinzufügen:

```bash
# JavaScript-Paket hinzufügen
php bin/console importmap:require paket-name

# Paket entfernen
php bin/console importmap:remove paket-name

# Alle Pakete aktualisieren
php bin/console importmap:update

# Übersicht aller Assets
php bin/console debug:asset-map
```

### 12.5 Stimulus Controller debuggen

```js
// In jedem Controller verfügbar:
connect() {
    // Element das den Controller hat
    console.log(this.element);

    // Alle Values
    console.log(this.urlValue, this.activeValue);

    // Alle Targets
    console.log(this.outputTargets);

    // Identifier des Controllers
    console.log(this.identifier); // z.B. "async-fetch"
}
```

Im Browser: Stimulus DevTools Extension für Chrome/Firefox installieren.

---

## 13. Schnellstart-Checkliste

### Neuen Stimulus Controller erstellen

```bash
# 1. Datei erstellen (Naming: kebab-case → snake_case im Dateinamen)
touch assets/controllers/mein_feature_controller.js

# 2. Basis-Template einfügen (siehe Abschnitt 3.2)

# 3. Im Twig-Template verwenden:
# data-controller="mein-feature"

# 4. Cache leeren (bei Problemen)
php bin/console cache:clear
php bin/console asset-map:compile  # nur für Produktion
```

### Turbo in einem neuen Projekt hinzufuegen

```bash
php bin/console importmap:require @hotwired/turbo
composer require symfony/ux-turbo
# In assets/app.js: import '@hotwired/turbo';
php bin/console cache:clear
```

### Live Component erstellen

```bash
# Maker Bundle verwenden
php bin/console make:live-component MeinComponent
# Erstellt: src/Twig/Components/MeinComponent.php
#           templates/components/MeinComponent.html.twig
```

---

## 14. Dateiübersicht dieses Projekts

```
assets/
├── app.js                          ← Frontend-Entrypoint (Stimulus, Turbo, Bootstrap, Matomo)
├── admin.js                        ← Admin-Entrypoint (Stimulus, Turbo, DataTables, Lightbox)
├── stimulus_bootstrap.js           ← Stimulus App starten + Auto-Discovery
├── controllers.json                ← UX-Package-Controller konfigurieren
├── controllers/
│   ├── .gitignore
│   ├── toggle_controller.js        ← Sichtbarkeit umschalten
│   ├── modal_controller.js         ← Bootstrap Modal + AJAX
│   ├── async_fetch_controller.js   ← Inhalte nachladen
│   └── form_submit_controller.js   ← AJAX-Formular-Submit
├── styles/
│   ├── app.css
│   ├── admin.css
│   └── admin-overrides.css
└── js/
    └── powercurve-chart.js

importmap.php                       ← Alle JS-Pakete (wie package.json)
```
