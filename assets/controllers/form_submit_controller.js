/**
 * Form Submit Controller
 *
 * Sendet Formulare via AJAX mit Loading-State und Feedback.
 * Unterstützt JSON- und HTML-Antworten von Symfony.
 *
 * Verwendung (einfach):
 *   <form
 *     action="{{ path('kontakt_senden') }}"
 *     method="POST"
 *     data-controller="form-submit"
 *   >
 *     <input type="text" name="name">
 *     <button type="submit" data-form-submit-target="submit">Senden</button>
 *     <div data-form-submit-target="feedback" class="d-none"></div>
 *   </form>
 *
 * Verwendung (mit Redirect nach Erfolg):
 *   <form
 *     data-controller="form-submit"
 *     data-form-submit-redirect-value="{{ path('erfolg_seite') }}"
 *   >
 *
 * Verwendung (Formular nach Erfolg zurücksetzen):
 *   <form
 *     data-controller="form-submit"
 *     data-form-submit-reset-on-success-value="true"
 *   >
 *
 * Symfony-Controller muss JSON zurückgeben:
 *   return $this->json(['success' => true, 'message' => 'Gespeichert!']);
 *   // oder bei Fehler:
 *   return $this->json(['success' => false, 'message' => 'Fehler!'], 422);
 */
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['submit', 'feedback', 'fieldError'];

    static values = {
        // Nach Erfolg zu dieser URL weiterleiten
        redirect: String,
        // Formular nach Erfolg zurücksetzen
        resetOnSuccess: { type: Boolean, default: false },
        // Erfolgsmeldung (wenn keine vom Server kommt)
        successMessage: { type: String, default: 'Erfolgreich gespeichert.' },
        // Fehlermeldung (wenn keine vom Server kommt)
        errorMessage: { type: String, default: 'Ein Fehler ist aufgetreten.' },
        // Loading-Text auf dem Submit-Button
        loadingText: { type: String, default: 'Speichern...' },
    };

    connect() {
        // Nur wenn das Element selbst ein Formular ist
        if (this.element.tagName === 'FORM') {
            this.element.addEventListener('submit', this.#handleSubmit);
        }
    }

    disconnect() {
        this.element.removeEventListener('submit', this.#handleSubmit);
    }

    // Kann auch manuell aufgerufen werden:
    // data-action="click->form-submit#submit"
    submit(event) {
        if (event) {
            event.preventDefault();
        }
        this.#doSubmit();
    }

    #handleSubmit = (event) => {
        event.preventDefault();
        this.#doSubmit();
    };

    async #doSubmit() {
        const form = this.element.tagName === 'FORM'
            ? this.element
            : this.element.querySelector('form');

        if (!form) {
            console.error('form-submit: Kein <form>-Element gefunden.');
            return;
        }

        // Vorherige Fehler zurücksetzen
        this.#clearFeedback();
        this.#clearFieldErrors();

        // Loading-State aktivieren
        const submitBtn = this.hasSubmitTarget ? this.submitTarget : form.querySelector('[type="submit"]');
        this.#setLoading(submitBtn, true);

        try {
            const response = await fetch(form.action || window.location.href, {
                method: (form.method || 'POST').toUpperCase(),
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json, text/html',
                },
            });

            const contentType = response.headers.get('Content-Type') || '';

            if (contentType.includes('application/json')) {
                const data = await response.json();
                this.#handleJsonResponse(response.ok, data, form);
            } else {
                // HTML-Antwort (z.B. Formular mit Validierungsfehlern)
                const html = await response.text();
                this.#handleHtmlResponse(response.ok, html);
            }

        } catch (_error) {
            this.#showFeedback(false, this.errorMessageValue);
        } finally {
            this.#setLoading(submitBtn, false);
        }
    }

    #handleJsonResponse(success, data, form) {
        if (success && data.success !== false) {
            const message = data.message || this.successMessageValue;
            this.#showFeedback(true, message);

            // Feldspezifische Fehler anzeigen (falls vorhanden)
            if (data.errors && typeof data.errors === 'object') {
                this.#showFieldErrors(data.errors);
            }

            if (this.resetOnSuccessValue) {
                form.reset();
            }

            if (this.redirectValue) {
                setTimeout(() => {
                    window.location.href = this.redirectValue;
                }, 1200);
            }

            // Custom Event für andere Controller
            this.dispatch('success', { detail: { data } });

        } else {
            const message = data.message || this.errorMessageValue;
            this.#showFeedback(false, message);

            // Feldspezifische Fehler anzeigen
            if (data.errors && typeof data.errors === 'object') {
                this.#showFieldErrors(data.errors);
            }

            this.dispatch('error', { detail: { data } });
        }
    }

    #handleHtmlResponse(success, html) {
        if (success) {
            this.#showFeedback(true, this.successMessageValue);
        } else {
            // Bei HTML-Fehlerantwort: Formular ersetzen (z.B. mit Validierungsfehlern)
            if (this.hasFeedbackTarget) {
                this.feedbackTarget.innerHTML = html;
                this.feedbackTarget.classList.remove('d-none');
            }
        }
    }

    #showFeedback(success, message) {
        if (!this.hasFeedbackTarget) {
            return;
        }

        const el = this.feedbackTarget;
        el.className = `alert ${success ? 'alert-success' : 'alert-danger'} mt-2`;
        el.textContent = message;
        el.classList.remove('d-none');

        // Automatisch ausblenden nach 5 Sekunden bei Erfolg
        if (success) {
            setTimeout(() => {
                el.classList.add('d-none');
            }, 5000);
        }
    }

    #clearFeedback() {
        if (this.hasFeedbackTarget) {
            this.feedbackTarget.classList.add('d-none');
            this.feedbackTarget.textContent = '';
        }
    }

    // Feldspezifische Fehler: { "name": "Pflichtfeld", "email": "Ungültige E-Mail" }
    #showFieldErrors(errors) {
        Object.entries(errors).forEach(([fieldName, message]) => {
            const field = this.element.querySelector(`[name="${fieldName}"]`);
            if (!field) {
                return;
            }

            field.classList.add('is-invalid');

            // Vorhandenes Feedback-Element suchen oder erstellen
            let feedback = field.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                field.after(feedback);
            }

            feedback.textContent = message;
        });
    }

    #clearFieldErrors() {
        this.element.querySelectorAll('.is-invalid').forEach((field) => {
            field.classList.remove('is-invalid');
        });
        this.element.querySelectorAll('.invalid-feedback').forEach((el) => {
            el.textContent = '';
        });
    }

    #setLoading(button, active) {
        this.element.classList.toggle('is-loading', active);

        if (!button) {
            return;
        }

        button.disabled = active;

        if (active) {
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                ${this.#escape(this.loadingTextValue)}
            `;
        } else if (button.dataset.originalHtml) {
            button.innerHTML = button.dataset.originalHtml;
            delete button.dataset.originalHtml;
        }
    }

    #escape(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '<')
            .replace(/>/g, '>');
    }
}
