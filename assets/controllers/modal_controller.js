/**
 * Modal Controller
 *
 * Öffnet Bootstrap-Modals, optional mit Lazy-Loading des Inhalts via AJAX.
 *
 * Verwendung (statischer Inhalt):
 *   <div data-controller="modal">
 *     <button type="button" data-action="modal#open">Öffnen</button>
 *     <div class="modal fade" data-modal-target="dialog" tabindex="-1">
 *       <div class="modal-dialog">
 *         <div class="modal-content">
 *           <div class="modal-body">Inhalt</div>
 *         </div>
 *       </div>
 *     </div>
 *   </div>
 *
 * Verwendung (AJAX-Inhalt):
 *   <div
 *     data-controller="modal"
 *     data-modal-url-value="{{ path('mein_endpoint') }}"
 *   >
 *     <button type="button" data-action="modal#open">Öffnen</button>
 *     <div class="modal fade" data-modal-target="dialog" tabindex="-1">
 *       <div class="modal-dialog">
 *         <div class="modal-content">
 *           <div class="modal-body" data-modal-target="body">
 *             <div class="text-center py-4">
 *               <div class="spinner-border"></div>
 *             </div>
 *           </div>
 *         </div>
 *       </div>
 *     </div>
 *   </div>
 */
import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ['dialog', 'body'];
    static values = {
        url: String,
        // Wenn true: Inhalt bei jedem Öffnen neu laden
        reload: { type: Boolean, default: false },
    };

    #modal = null;
    #loaded = false;

    connect() {
        if (this.hasDialogTarget) {
            this.#modal = Modal.getOrCreateInstance(this.dialogTarget);

            // Event: Modal wurde geschlossen
            this.dialogTarget.addEventListener('hidden.bs.modal', () => {
                if (this.reloadValue) {
                    this.#loaded = false;
                }
            });
        }
    }

    disconnect() {
        this.#modal?.dispose();
        this.#modal = null;
        this.#loaded = false;
    }

    async open(event) {
        if (event) {
            event.preventDefault();
        }

        // URL aus dem auslösenden Element überschreibt den Value
        const trigger = event?.currentTarget;
        const url = trigger?.dataset?.modalUrl || this.urlValue;

        if (url && (!this.#loaded || this.reloadValue)) {
            await this.#loadContent(url);
        }

        this.#modal?.show();
    }

    close() {
        this.#modal?.hide();
    }

    // Kann als data-action auf einem Link verwendet werden:
    // data-action="modal#openFromLink" data-modal-url="/pfad/zum/inhalt"
    async openFromLink(event) {
        event.preventDefault();
        const url = event.currentTarget.getAttribute('href')
            || event.currentTarget.dataset.modalUrl
            || this.urlValue;

        if (url) {
            await this.#loadContent(url);
        }

        this.#modal?.show();
    }

    async #loadContent(url) {
        if (!this.hasBodyTarget) {
            return;
        }

        this.bodyTarget.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Lade...</span>
                </div>
            </div>
        `;

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            this.bodyTarget.innerHTML = await response.text();
            this.#loaded = true;
        } catch (_error) {
            this.bodyTarget.innerHTML = `
                <div class="alert alert-danger">
                    Inhalt konnte nicht geladen werden.
                </div>
            `;
        }
    }
}
