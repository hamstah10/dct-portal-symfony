/**
 * Shop Product Media Controller
 *
 * Scraped Bilder von einer eingegebenen Produkt-URL und haengt sie
 * an das aktuelle Produkt an. Rendert neue Thumbnails ohne Page-Reload.
 *
 * Verwendung:
 *   <div
 *     data-controller="shop-product-media"
 *     data-shop-product-media-scrape-url-value="{{ path('admin_shop_product_scrape_images', {uid: product.uid}) }}"
 *     data-shop-product-media-csrf-token-value="{{ csrf_token('shop_product_scrape_images_' ~ product.uid) }}"
 *   >
 *     <div data-shop-product-media-target="gallery"></div>
 *     <input data-shop-product-media-target="urlInput">
 *     <button data-action="shop-product-media#scrape" data-shop-product-media-target="scrapeButton">Bilder scrapen</button>
 *     <div data-shop-product-media-target="error" class="d-none"></div>
 *   </div>
 */
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['gallery', 'urlInput', 'scrapeButton', 'error'];
    static values = { scrapeUrl: String, csrfToken: String };

    async scrape() {
        const url = this.urlInputTarget.value.trim();
        if (!url) {
            this.#showError('Bitte eine URL eingeben.');
            return;
        }

        this.#clearError();
        this.#setLoading(true);

        try {
            const response = await fetch(this.scrapeUrlValue, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new URLSearchParams({ url, _token: this.csrfTokenValue }),
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                this.#showError(data.message || 'Scraping fehlgeschlagen.');
                return;
            }

            this.#appendThumbnails(data.created);
            this.urlInputTarget.value = '';
        } catch (_error) {
            this.#showError('Netzwerkfehler beim Scrapen.');
        } finally {
            this.#setLoading(false);
        }
    }

    #appendThumbnails(created) {
        const emptyEl = this.galleryTarget.querySelector('.shop-product-media-empty');
        if (emptyEl) {
            emptyEl.remove();
        }

        for (const asset of created) {
            const div = document.createElement('div');
            div.className = 'shop-product-media-thumb';
            const img = document.createElement('img');
            img.src = asset.url;
            img.alt = asset.name || '';
            div.appendChild(img);
            this.galleryTarget.appendChild(div);
        }
    }

    #setLoading(active) {
        this.scrapeButtonTarget.disabled = active;
        this.scrapeButtonTarget.textContent = active ? 'Scrape läuft…' : 'Bilder scrapen';
    }

    #showError(message) {
        this.errorTarget.textContent = message;
        this.errorTarget.classList.remove('d-none');
    }

    #clearError() {
        this.errorTarget.classList.add('d-none');
        this.errorTarget.textContent = '';
    }
}
