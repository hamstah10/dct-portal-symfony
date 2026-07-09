/**
 * Async Fetch Controller
 *
 * Lädt Inhalte von einem Symfony-Endpoint und rendert sie in ein Target-Element.
 * Unterstützt Debouncing, Loading-States und Fehlerbehandlung.
 *
 * Verwendung (automatisch beim Connect laden):
 *   <div
 *     data-controller="async-fetch"
 *     data-async-fetch-url-value="{{ path('api_endpoint') }}"
 *     data-async-fetch-auto-load-value="true"
 *   >
 *     <div data-async-fetch-target="output">Lade...</div>
 *   </div>
 *
 * Verwendung (manuell via Button):
 *   <div
 *     data-controller="async-fetch"
 *     data-async-fetch-url-value="{{ path('api_endpoint') }}"
 *   >
 *     <button type="button" data-action="async-fetch#load">Laden</button>
 *     <div data-async-fetch-target="output"></div>
 *     <div data-async-fetch-target="loading" class="d-none">
 *       <div class="spinner-border spinner-border-sm"></div>
 *     </div>
 *   </div>
 *
 * Verwendung (Suche mit Debounce):
 *   <div
 *     data-controller="async-fetch"
 *     data-async-fetch-url-value="{{ path('api_suche') }}"
 *     data-async-fetch-debounce-value="400"
 *     data-async-fetch-param-name-value="q"
 *   >
 *     <input
 *       type="text"
 *       data-async-fetch-target="input"
 *       data-action="input->async-fetch#search"
 *       placeholder="Suchen..."
 *     >
 *     <div data-async-fetch-target="output"></div>
 *   </div>
 */
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['output', 'loading', 'input', 'error'];

    static values = {
        // Pflicht: URL des Symfony-Endpoints
        url: String,
        // Automatisch laden wenn Controller verbunden wird
        autoLoad: { type: Boolean, default: false },
        // Debounce-Verzögerung in ms (für Suche)
        debounce: { type: Number, default: 300 },
        // Query-Parameter-Name für Sucheingaben
        paramName: { type: String, default: 'q' },
        // Minimale Zeichenanzahl für Suche
        minLength: { type: Number, default: 0 },
        // Fehlermeldung
        errorMessage: { type: String, default: 'Fehler beim Laden.' },
        // Leere-Meldung
        emptyMessage: { type: String, default: '' },
    };

    #debounceTimer = null;
    #abortController = null;
    #lastQuery = null;

    connect() {
        if (this.autoLoadValue && this.urlValue) {
            this.load();
        }
    }

    disconnect() {
        this.#cancelDebounce();
        this.#abortController?.abort();
    }

    // Direktes Laden (kein Debounce)
    async load(event) {
        if (event) {
            event.preventDefault();
        }

        await this.#fetch(this.urlValue);
    }

    // Suche mit Debounce (für input-Events)
    search() {
        this.#cancelDebounce();

        const query = this.hasInputTarget ? this.inputTarget.value.trim() : '';

        if (query.length < this.minLengthValue) {
            if (this.hasOutputTarget) {
                this.outputTarget.innerHTML = '';
            }
            return;
        }

        if (query === this.#lastQuery) {
            return;
        }

        this.#debounceTimer = setTimeout(() => {
            const url = new URL(this.urlValue, window.location.origin);
            url.searchParams.set(this.paramNameValue, query);
            this.#fetch(url.toString());
        }, this.debounceValue);
    }

    // Lädt mit zusätzlichen Query-Parametern
    // Verwendung: data-action="change->async-fetch#loadWithParams"
    //             data-async-fetch-params-value='{"kategorie": "tuning"}'
    loadWithParams(event) {
        const params = this.#parseParams(event.currentTarget.dataset.asyncFetchParams);
        const url = new URL(this.urlValue, window.location.origin);

        Object.entries(params).forEach(([key, value]) => {
            url.searchParams.set(key, value);
        });

        this.#fetch(url.toString());
    }

    async #fetch(url) {
        // Vorherige Anfrage abbrechen
        this.#abortController?.abort();
        this.#abortController = new AbortController();

        this.#setLoading(true);
        this.#clearError();

        try {
            const response = await fetch(url, {
                signal: this.#abortController.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('Content-Type') || '';

            if (contentType.includes('application/json')) {
                const data = await response.json();
                this.#renderJson(data);
            } else {
                const html = await response.text();
                this.#renderHtml(html);
            }

            // Custom Event für andere Controller
            this.dispatch('loaded', { detail: { url } });

        } catch (error) {
            if (error.name === 'AbortError') {
                return; // Absichtlich abgebrochen
            }

            this.#showError(this.errorMessageValue);
            this.dispatch('error', { detail: { error } });
        } finally {
            this.#setLoading(false);
        }
    }

    #renderHtml(html) {
        if (!this.hasOutputTarget) {
            return;
        }

        if (!html.trim() && this.emptyMessageValue) {
            this.outputTarget.innerHTML = `<p class="text-muted">${this.#escape(this.emptyMessageValue)}</p>`;
            return;
        }

        this.outputTarget.innerHTML = html;
    }

    #renderJson(data) {
        if (!this.hasOutputTarget) {
            return;
        }

        // Erwartet { html: '...' } oder { items: [...] }
        if (data.html !== undefined) {
            this.#renderHtml(data.html);
            return;
        }

        // Fallback: JSON als formatierter Text
        this.outputTarget.innerHTML = `<pre>${this.#escape(JSON.stringify(data, null, 2))}</pre>`;
    }

    #setLoading(active) {
        this.loadingTargets.forEach((el) => {
            el.classList.toggle('d-none', !active);
        });
        this.element.classList.toggle('is-loading', active);

        // Inputs während des Ladens deaktivieren
        this.inputTargets.forEach((input) => {
            input.disabled = active;
        });
    }

    #showError(message) {
        if (this.hasErrorTarget) {
            this.errorTarget.textContent = message;
            this.errorTarget.classList.remove('d-none');
        } else if (this.hasOutputTarget) {
            this.outputTarget.innerHTML = `<div class="alert alert-danger">${this.#escape(message)}</div>`;
        }
    }

    #clearError() {
        if (this.hasErrorTarget) {
            this.errorTarget.classList.add('d-none');
            this.errorTarget.textContent = '';
        }
    }

    #cancelDebounce() {
        if (this.#debounceTimer) {
            clearTimeout(this.#debounceTimer);
            this.#debounceTimer = null;
        }
    }

    #parseParams(raw) {
        if (!raw) {
            return {};
        }
        try {
            return JSON.parse(raw);
        } catch (_e) {
            return {};
        }
    }

    #escape(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '<')
            .replace(/>/g, '>')
            .replace(/"/g, '"');
    }
}
