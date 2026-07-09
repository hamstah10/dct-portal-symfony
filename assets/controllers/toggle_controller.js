/**
 * Toggle Controller
 *
 * Schaltet Sichtbarkeit von Elementen um.
 *
 * Verwendung:
 *   <div data-controller="toggle">
 *     <button type="button" data-action="toggle#toggle">Anzeigen/Verstecken</button>
 *     <div data-toggle-target="content" class="d-none">Inhalt</div>
 *   </div>
 *
 * Mit CSS-Klasse:
 *   <div data-controller="toggle" data-toggle-hidden-class="visually-hidden">
 *
 * Standardmäßig offen:
 *   <div data-controller="toggle" data-toggle-open-value="true">
 */
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['content', 'openLabel', 'closeLabel'];
    static classes = ['hidden'];
    static values = {
        open: { type: Boolean, default: false },
    };

    connect() {
        // Initialen Zustand anwenden ohne Animation
        this.#applyState(false);
    }

    openValueChanged() {
        this.#applyState(true);
    }

    toggle() {
        this.openValue = !this.openValue;
    }

    open() {
        this.openValue = true;
    }

    close() {
        this.openValue = false;
    }

    // Schließt wenn außerhalb geklickt wird
    closeOnOutsideClick(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }

    #applyState(animate) {
        const hiddenClass = this.hasHiddenClass ? this.hiddenClass : 'd-none';

        this.contentTargets.forEach((target) => {
            target.classList.toggle(hiddenClass, !this.openValue);
        });

        // Labels tauschen (z.B. "Mehr anzeigen" / "Weniger anzeigen")
        this.openLabelTargets.forEach((el) => {
            el.classList.toggle('d-none', this.openValue);
        });
        this.closeLabelTargets.forEach((el) => {
            el.classList.toggle('d-none', !this.openValue);
        });

        // ARIA-Attribute aktualisieren
        const trigger = this.element.querySelector('[data-action*="toggle#toggle"]');
        if (trigger) {
            trigger.setAttribute('aria-expanded', String(this.openValue));
        }
    }
}
