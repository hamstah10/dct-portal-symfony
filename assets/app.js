import './stimulus_bootstrap.js';
import '@hotwired/turbo';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import { Toast } from 'bootstrap';
import './styles/app.css';

const frontendToastListenerByEventSource = new WeakMap();

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function getToastContainer() {
    const existing = document.getElementById('tfd-toast-container');
    if (existing instanceof HTMLElement) {
        return existing;
    }

    const container = document.createElement('div');
    container.id = 'tfd-toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-atomic', 'true');
    document.body.appendChild(container);

    return container;
}

function showBootstrapToast({ title, body = '', tag = '' }) {
    const normalizedTitle = String(title || '').trim();
    if (!normalizedTitle) {
        return;
    }

    const container = getToastContainer();
    const normalizedTag = String(tag || '').trim();

    if (normalizedTag) {
        for (const existing of container.querySelectorAll('.toast')) {
            if (existing instanceof HTMLElement && existing.dataset.tfdToastTag === normalizedTag) {
                return;
            }
        }
    }

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    if (normalizedTag) {
        toast.dataset.tfdToastTag = normalizedTag;
    }

    const bodyText = String(body || '').trim();
    toast.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">${escapeHtml(normalizedTitle)}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Schliessen"></button>
        </div>
        ${bodyText ? `<div class="toast-body">${escapeHtml(bodyText)}</div>` : ''}
    `;

    container.appendChild(toast);

    toast.addEventListener(
        'hidden.bs.toast',
        () => {
            toast.remove();
        },
        { once: true }
    );

    Toast.getOrCreateInstance(toast, { autohide: true, delay: 7000 }).show();
}

function bindToastNotificationsToEventSources(eventSources) {
    if (!Array.isArray(eventSources)) {
        return;
    }

    for (const eventSource of eventSources) {
        if (!(eventSource instanceof EventSource) || frontendToastListenerByEventSource.has(eventSource)) {
            continue;
        }

        const listener = (event) => {
            try {
                const payload = JSON.parse(event.data || '{}');
                const summary = String(payload?.summary || '').trim();
                if (!summary) {
                    return;
                }

                showBootstrapToast({
                    title: summary,
                    body: payload?.content?.body || '',
                    tag: payload?.content?.tag || '',
                });
            } catch (_error) {
            }
        };

        eventSource.addEventListener('message', listener);
        frontendToastListenerByEventSource.set(eventSource, listener);
    }
}

document.addEventListener('notify:connect', (event) => {
    bindToastNotificationsToEventSources(event?.detail?.eventSources);
});

function trackMatomoPageView() {
    const matomoQueue = window._paq = window._paq || [];
    matomoQueue.push(['setCustomUrl', window.location.href]);
    matomoQueue.push(['setDocumentTitle', document.title]);
    matomoQueue.push(['trackPageView']);
}

const matomoQueue = window._paq = window._paq || [];
matomoQueue.push(['enableLinkTracking']);

(() => {
    const baseUrl = '//matomo.tuningfux.de/';
    matomoQueue.push(['setTrackerUrl', `${baseUrl}matomo.php`]);
    matomoQueue.push(['setSiteId', '2']);

    const documentRef = document;
    const script = documentRef.createElement('script');
    const firstScript = documentRef.getElementsByTagName('script')[0];
    script.async = true;
    script.src = `${baseUrl}matomo.js`;
    firstScript.parentNode.insertBefore(script, firstScript);
})();

trackMatomoPageView();
document.addEventListener('turbo:load', trackMatomoPageView);
