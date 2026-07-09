import './stimulus_bootstrap.js';
import '@hotwired/turbo';
import 'bootstrap';
import { Toast } from 'bootstrap';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'datatables.net-rowgroup-bs5';
import 'datatables.net-rowgroup-bs5/css/rowGroup.bootstrap5.min.css';
import './styles/admin.css';
import './styles/admin-theme.css';
import './js/admin-theme.js';

// EasyAdmin renders DateTimeField values as "DD.MM.YYYY HH:mm" (German locale
// default). DataTables' built-in type auto-detection doesn't recognize this
// format and falls back to plain string sorting, which sorts by the day
// number first (e.g. "01.07" ends up before "29.06"). Register a dedicated
// type so date/time columns sort chronologically instead of alphabetically.
const GERMAN_DATETIME_PATTERN = /^(\d{2})\.(\d{2})\.(\d{4})(?:\s+(\d{2}):(\d{2}))?$/;

function parseGermanDatetime(value) {
    const text = String(value ?? '').trim();
    const match = GERMAN_DATETIME_PATTERN.exec(text);
    if (!match) {
        return null;
    }

    const [, day, month, year, hours, minutes] = match;

    return Date.UTC(
        Number(year),
        Number(month) - 1,
        Number(day),
        hours ? Number(hours) : 0,
        minutes ? Number(minutes) : 0
    );
}

DataTable.ext.type.detect.unshift((value) => (
    null !== parseGermanDatetime(value) ? 'de-datetime' : null
));

DataTable.ext.type.order['de-datetime-pre'] = (value) => parseGermanDatetime(value) ?? Number.NEGATIVE_INFINITY;

const toastListenerByEventSource = new WeakMap();
const select2AnimationTimers = new WeakMap();
const SELECT2_CLOSE_ANIMATION_MS = 160;

function ensureLightboxStyles() {
    if (document.getElementById('ea-lightbox-style')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'ea-lightbox-style';
    style.textContent = `
        .ea-lightbox-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.82);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            padding: 20px;
        }
        .ea-lightbox-frame {
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: min(92vw, 1600px);
            max-height: 88vh;
            width: min(92vw, 1600px);
            height: 88vh;
        }
        .ea-lightbox-page {
            width: 100%;
            height: 100%;
            background: #ffffff;
            box-shadow: 0 12px 60px rgba(0, 0, 0, 0.45);
            overflow: hidden;
        }
        .ea-lightbox-page iframe {
            width: 100%;
            height: 100%;
            border: 0;
            background: #ffffff;
            display: block;
        }
        .ea-lightbox-overlay img {
            max-width: min(92vw, 1600px);
            max-height: 88vh;
            border-radius: 10px;
            box-shadow: 0 12px 60px rgba(0, 0, 0, 0.45);
            background: #fff;
        }
        .ea-lightbox-model {
            width: 100%;
            height: 100%;
            border-radius: 10px;
            box-shadow: 0 12px 60px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            background: #111827;
        }
        .ea-lightbox-model model-viewer {
            width: 100%;
            height: 100%;
            background: #111827;
        }
        .ea-lightbox-fallback {
            color: #e5e7eb;
            font-size: 14px;
            text-align: center;
        }
        .ea-lightbox-fallback a {
            color: #bd1e21;
            text-decoration: underline;
        }
        .ea-lightbox-close {
            position: fixed;
            top: 14px;
            right: 16px;
            border: 0;
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
            width: 38px;
            height: 38px;
            border-radius: 999px;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
        }
    `;
    document.head.appendChild(style);
}

let modelViewerScriptPromise = null;

function resolveLightboxType(url, preferredType = '') {
    const normalizedType = String(preferredType || '').trim().toLowerCase();
    if (normalizedType === 'glb' || normalizedType === 'image' || normalizedType === 'page') {
        return normalizedType;
    }

    const normalizedUrl = String(url || '').split('#')[0].split('?')[0].toLowerCase();
    if (normalizedUrl.endsWith('.glb')) {
        return 'glb';
    }

    return 'image';
}

function ensureModelViewerScript() {
    if (window.customElements && window.customElements.get('model-viewer')) {
        return Promise.resolve();
    }

    if (modelViewerScriptPromise) {
        return modelViewerScriptPromise;
    }

    modelViewerScriptPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.type = 'module';
        script.src = 'https://unpkg.com/@google/model-viewer@4.1.0/dist/model-viewer.min.js';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Model-Viewer konnte nicht geladen werden.'));
        document.head.appendChild(script);
    });

    return modelViewerScriptPromise;
}

async function openLightbox(url, altText = '', preferredType = '') {
    if (!url) {
        return;
    }

    ensureLightboxStyles();
    const lightboxType = resolveLightboxType(url, preferredType);
    const escapedUrl = escapeHtml(url);
    const escapedAlt = escapeHtml(altText || '');

    const overlay = document.createElement('div');
    overlay.className = 'ea-lightbox-overlay';
    overlay.innerHTML = `
        <button type="button" class="ea-lightbox-close" aria-label="Schliessen">&times;</button>
        <div class="ea-lightbox-frame"></div>
    `;
    const frame = overlay.querySelector('.ea-lightbox-frame');
    if (!(frame instanceof HTMLElement)) {
        return;
    }

    if (lightboxType === 'page') {
        frame.innerHTML = `
            <div class="ea-lightbox-page">
                <iframe src="${escapedUrl}" title="${escapedAlt || 'Frontend Vorschau'}" loading="lazy"></iframe>
            </div>
        `;
    } else if (lightboxType === 'glb') {
        try {
            await ensureModelViewerScript();
            frame.innerHTML = `
                <div class="ea-lightbox-model">
                    <model-viewer
                        src="${escapedUrl}"
                        camera-controls
                        camera-orbit="32.76deg 74.6deg 65%"
                        field-of-view="30deg"
                        shadow-intensity="1"
                        exposure="1"
                        interaction-prompt="auto"
                    ></model-viewer>
                </div>
            `;
        } catch (_error) {
            frame.innerHTML = `
                <div class="ea-lightbox-fallback">
                    GLB-Vorschau konnte nicht geladen werden.<br>
                    <a href="${escapedUrl}" target="_blank" rel="noopener">Datei in neuem Tab oeffnen</a>
                </div>
            `;
        }
    } else {
        frame.innerHTML = `<img src="${escapedUrl}" alt="${escapedAlt}">`;
    }

    const close = () => {
        document.removeEventListener('keydown', onEsc);
        overlay.remove();
    };

    const onEsc = (event) => {
        if (event.key === 'Escape') {
            close();
        }
    };

    overlay.addEventListener('click', (event) => {
        if (event.target === overlay || event.target.classList.contains('ea-lightbox-close')) {
            close();
        }
    });

    document.addEventListener('keydown', onEsc);
    document.body.appendChild(overlay);
}

function getEventElement(event) {
    return event.target instanceof Element ? event.target : null;
}

function setCopyFeedback(copyTrigger, message, isError = false) {
    const wrapper = copyTrigger.closest('span');
    if (!(wrapper instanceof HTMLElement)) {
        return;
    }

    const feedback = wrapper.querySelector('[data-copy-feedback]');
    if (!(feedback instanceof HTMLElement)) {
        return;
    }

    const previousTimerRaw = feedback.dataset.copyTimer || '';
    const previousTimer = Number.parseInt(previousTimerRaw, 10);
    if (Number.isFinite(previousTimer) && previousTimer > 0) {
        window.clearTimeout(previousTimer);
    }

    feedback.textContent = message;
    feedback.classList.remove('d-none', 'text-success', 'text-danger');
    feedback.classList.add(isError ? 'text-danger' : 'text-success');

    const timeoutId = window.setTimeout(() => {
        feedback.classList.add('d-none');
        delete feedback.dataset.copyTimer;
    }, 2200);

    feedback.dataset.copyTimer = String(timeoutId);
}

document.addEventListener('mousedown', (event) => {
    const eventElement = getEventElement(event);
    if (!eventElement) {
        return;
    }

    const copyTrigger = eventElement.closest('[data-copy-clipboard]');
    if (!copyTrigger) {
        return;
    }

    event.stopPropagation();
});

document.addEventListener('click', (event) => {
    const eventElement = getEventElement(event);
    if (!eventElement) {
        return;
    }

    const copyTrigger = eventElement.closest('[data-copy-clipboard]');
    if (copyTrigger) {
        event.preventDefault();
        event.stopPropagation();
        if (typeof event.stopImmediatePropagation === 'function') {
            event.stopImmediatePropagation();
        }

        const valueToCopy = String(copyTrigger.getAttribute('data-copy-clipboard') || '');
        if (valueToCopy.trim() === '') {
            showBootstrapToast({
                title: 'Kopieren fehlgeschlagen',
                body: 'Kein Inhalt zum Kopieren vorhanden.',
            });
            return;
        }

        void (async () => {
            try {
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    await navigator.clipboard.writeText(valueToCopy);
                } else {
                    const textarea = document.createElement('textarea');
                    textarea.value = valueToCopy;
                    textarea.setAttribute('readonly', 'readonly');
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();

                    const copied = document.execCommand('copy');
                    textarea.remove();
                    if (!copied) {
                        throw new Error('Clipboard fallback failed');
                    }
                }

                showBootstrapToast({
                    title: 'Token kopiert',
                    body: 'Der Token wurde in die Zwischenablage kopiert.',
                });
                setCopyFeedback(copyTrigger, 'Kopiert');
            } catch (_error) {
                showBootstrapToast({
                    title: 'Kopieren fehlgeschlagen',
                    body: 'Zwischenablage ist im Browser nicht verfuegbar.',
                });
                setCopyFeedback(copyTrigger, 'Fehlgeschlagen', true);
            }
        })();

        return;
    }

    const notifyTestButton = eventElement.closest('[data-notify-test="1"]');
    if (notifyTestButton) {
        event.preventDefault();
        void (async () => {
            if (!('Notification' in window)) {
                window.alert('Dieser Browser unterstuetzt keine Desktop-Benachrichtigungen.');
                return;
            }

            let permission = window.Notification.permission;
            if (permission !== 'granted') {
                try {
                    permission = await window.Notification.requestPermission();
                } catch (_error) {
                    permission = window.Notification.permission;
                }
            }

            if (permission !== 'granted') {
                window.alert('Benachrichtigungen sind im Browser blockiert. Bitte in den Website-Einstellungen erlauben.');
                return;
            }

            try {
                // eslint-disable-next-line no-new
                new window.Notification('TFD CRM', {
                    body: 'Test: Benachrichtigungen sind aktiv.',
                    tag: 'tfd-notify-test',
                });
            } catch (_error) {
                window.alert('Benachrichtigung konnte nicht angezeigt werden.');
            }
        })();
        return;
    }

    const lightboxTrigger = eventElement.closest('[data-lightbox-url]');
    if (lightboxTrigger) {
        event.preventDefault();
        void openLightbox(
            lightboxTrigger.getAttribute('data-lightbox-url') || '',
            lightboxTrigger.getAttribute('data-lightbox-caption') || '',
            lightboxTrigger.getAttribute('data-lightbox-type') || ''
        );
        return;
    }

    const legacyTrigger = eventElement.closest('a[data-lightbox="preview"]');
    if (!legacyTrigger) {
        return;
    }

    event.preventDefault();
    const legacyType = legacyTrigger.getAttribute('data-lightbox-type') || 'page';
    void openLightbox(
        legacyTrigger.getAttribute('href') || '',
        legacyTrigger.getAttribute('data-lightbox-caption') || '',
        legacyType
    );
}, true);

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

function formatToastTime(date = new Date()) {
    try {
        return date.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
    } catch (_error) {
        return '';
    }
}

function showBootstrapToast({ title, body = '', tag = '' }) {
    const normalizedTitle = String(title || '').trim();
    if (!normalizedTitle) {
        return;
    }

    const container = getToastContainer();
    if (!container) {
        return;
    }

    const normalizedTag = String(tag || '').trim();
    if (normalizedTag) {
        for (const existing of container.querySelectorAll('.toast')) {
            if (existing instanceof HTMLElement && existing.dataset.tfdToastTag === normalizedTag) {
                return;
            }
        }
    }

    const toast = document.createElement('div');
    toast.className = 'toast tfd-toast';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    if (normalizedTag) {
        toast.dataset.tfdToastTag = normalizedTag;
    }

    const timeLabel = formatToastTime();
    const bodyText = String(body || '').trim();

    toast.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">${escapeHtml(normalizedTitle)}</strong>
            ${timeLabel ? `<small class="text-body-secondary">${escapeHtml(timeLabel)}</small>` : ''}
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Schliessen"></button>
        </div>
        ${bodyText ? `<div class="toast-body">${escapeHtml(bodyText)}</div>` : ''}
    `;

    container.appendChild(toast);

    // Limit stacking to avoid an ever-growing list.
    const maxToasts = 5;
    const currentToasts = container.querySelectorAll('.toast');
    if (currentToasts.length > maxToasts) {
        for (let i = 0; i < currentToasts.length - maxToasts; i += 1) {
            currentToasts[i]?.remove();
        }
    }

    const toastInstance =
        typeof Toast?.getOrCreateInstance === 'function'
            ? Toast.getOrCreateInstance(toast, { autohide: true, delay: 8000 })
            : null;

    toast.addEventListener(
        'hidden.bs.toast',
        () => {
            toast.remove();
        },
        { once: true }
    );

    if (toastInstance) {
        toastInstance.show();
        return;
    }

    // Fallback if bootstrap Toast JS isn't available.
    toast.classList.add('show');
    window.setTimeout(() => toast.remove(), 8000);
}

function bindToastNotificationsToEventSources(eventSources) {
    if (!Array.isArray(eventSources)) {
        return;
    }

    for (const eventSource of eventSources) {
        if (!(eventSource instanceof EventSource)) {
            continue;
        }

        if (toastListenerByEventSource.has(eventSource)) {
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
                // Ignore malformed payloads.
            }
        };

        eventSource.addEventListener('message', listener);
        toastListenerByEventSource.set(eventSource, listener);
    }
}

function renderStageIconOption(optionState) {
    const label = escapeHtml(optionState.text || '');

    if (!optionState.id) {
        return `<span class="ea-stage-icon-option ea-stage-icon-option--empty">${label}</span>`;
    }

    const imagePath = optionState.element?.dataset?.image || '';
    if (!imagePath) {
        return `
            <span class="ea-stage-icon-option ea-stage-icon-option--no-image">
                <span class="ea-stage-icon-option__label">${label}</span>
            </span>
        `;
    }

    return `
        <span class="ea-stage-icon-option">
            <img src="${escapeHtml(imagePath)}" alt="" class="ea-stage-icon-option__thumb">
            <span class="ea-stage-icon-option__label">${label}</span>
        </span>
    `;
}

function bindSelect2OpenCloseAnimation($select) {
    if (!$select || typeof $select.on !== 'function') {
        return;
    }

    const namespace = '.eaSelect2Anim';
    const animatedCloseKey = 'eaSelect2AnimatedCloseActive';

    $select.off(`select2:open${namespace}`);
    $select.off(`select2:closing${namespace}`);
    $select.off(`select2:close${namespace}`);

    $select.on(`select2:open${namespace}`, () => {
        const $openContainer = window.jQuery('.select2-container--open').last();
        $openContainer.removeClass('ea-select2-closing');
    });

    $select.on(`select2:closing${namespace}`, (event) => {
        if ($select.data(animatedCloseKey) === true) {
            return;
        }

        const $openContainer = window.jQuery('.select2-container--open').last();
        if ($openContainer.length === 0) {
            return;
        }

        event.preventDefault();

        $select.data(animatedCloseKey, true);
        $openContainer.addClass('ea-select2-closing');

        const previousTimer = select2AnimationTimers.get($select[0]);
        if (previousTimer) {
            window.clearTimeout(previousTimer);
        }

        const timerId = window.setTimeout(() => {
            select2AnimationTimers.delete($select[0]);
            try {
                $select.select2('close');
            } finally {
                $select.removeData(animatedCloseKey);
                window.jQuery('.select2-container.ea-select2-closing').removeClass('ea-select2-closing');
            }
        }, SELECT2_CLOSE_ANIMATION_MS);

        select2AnimationTimers.set($select[0], timerId);
    });

    $select.on(`select2:close${namespace}`, () => {
        const previousTimer = select2AnimationTimers.get($select[0]);
        if (previousTimer) {
            window.clearTimeout(previousTimer);
            select2AnimationTimers.delete($select[0]);
        }

        $select.removeData(animatedCloseKey);
        window.jQuery('.select2-container.ea-select2-closing').removeClass('ea-select2-closing');
    });
}

function initStageIconSelect2() {
    if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
        return;
    }

    const $ = window.jQuery;
    const $selects = $('select[data-stage-icon-media-select="1"]');
    if ($selects.length === 0) {
        return;
    }

    $selects.each(function applySelect2() {
        const $select = $(this);
        if ($select.data('select2')) {
            $select.select2('destroy');
        }

        const placeholder = String($select.data('placeholder') || 'Icon auswaehlen');

        $select.select2({
            width: '100%',
            allowClear: !$select.prop('required'),
            placeholder,
            templateResult: renderStageIconOption,
            templateSelection: renderStageIconOption,
            escapeMarkup: (markup) => markup,
        });

        bindSelect2OpenCloseAnimation($select);
    });
}

function initEasyAdminListDataTables() {
    if (!document.body.classList.contains('ea-index')) {
        return;
    }

    const tables = document.querySelectorAll('table.datagrid');
    for (const table of tables) {
        if (!(table instanceof HTMLTableElement)) {
            continue;
        }

        if (table.dataset.eaDatatableInitialized === '1') {
            continue;
        }

        const headers = Array.from(table.querySelectorAll('thead th'));
        if (headers.length === 0) {
            continue;
        }

        table.querySelectorAll('thead th a[href]').forEach((link) => {
            if (link.dataset.eaDatatableSortLinkBound === '1') {
                return;
            }

            const plainLabel = String(link.textContent || '').replace(/\s+/g, ' ').trim();
            link.textContent = plainLabel;

            link.dataset.eaDatatableSortLinkBound = '1';
            link.addEventListener('click', (event) => event.preventDefault());
            link.setAttribute('href', '#');
        });

        const nonSortableTargets = [];
        headers.forEach((header, index) => {
            const hasBatchCheckbox = header.querySelector('input.form-batch-checkbox-all') !== null;
            const isActionsColumn = header.classList.contains('actions-as-dropdown-table-head');
            const isEmptyHeader = String(header.textContent || '').trim() === '';

            if (hasBatchCheckbox || isActionsColumn) {
                nonSortableTargets.push(index);
                header.classList.add('ea-dt-no-order');
                return;
            }

            if (index === 0 && isEmptyHeader) {
                nonSortableTargets.push(index);
                header.classList.add('ea-dt-no-order');
            }
        });

        const hasBatchColumn = table.querySelector('thead input.form-batch-checkbox-all') !== null;
        if (hasBatchColumn && headers.length > 0) {
            nonSortableTargets.push(0);
            headers[0]?.classList.add('ea-dt-no-order');
        }

        const uniqueNonSortableTargets = [...new Set(nonSortableTargets)];

        const columnDefs = [];

        const options = {
            autoWidth: false,
            searching: true,
            ordering: true,
            order: [],
            paging: true,
            pagingType: 'simple_numbers',
            pageLength: 25,
            lengthChange: false,
            info: true,
            language: {
                info: '_START_ bis _END_ von _TOTAL_ Einträgen',
                infoEmpty: '0 bis 0 von 0 Einträgen',
                infoFiltered: '(gefiltert von _MAX_ Einträgen)',
                zeroRecords: 'Keine Einträge gefunden',
                emptyTable: 'Keine Daten verfügbar',
                search: 'Suche:',
                paginate: {
                    first: 'Erste',
                    previous: 'Zurück',
                    next: 'Weiter',
                    last: 'Letzte',
                },
            },
        };

        const isVehicleStageIndex = document.body.classList.contains('ea-index-VehicleStage');
        if (isVehicleStageIndex) {
            const groupColumnIndex = headers.findIndex((header) => header.dataset.column === 'vehicleEngine');
            if (groupColumnIndex >= 0) {
                options.orderFixed = {
                    pre: [[groupColumnIndex, 'asc']],
                };
                options.order = [[groupColumnIndex, 'asc']];
                options.rowGroup = {
                    dataSrc: groupColumnIndex,
                    className: 'ea-dt-rowgroup',
                    startRender: (rows, group) => `${group} (${rows.count()})`,
                };

                // Keep grouping source column in the data, but hide it in the UI.
                columnDefs.push({
                    targets: groupColumnIndex,
                    visible: false,
                    searchable: false,
                    orderable: false,
                });
            }
        }

        const isVehicleCoverageMatchIndex = document.body.classList.contains('ea-index-VehicleCoverageMatch');
        let vehicleCoverageMatchGroupState = null;
        if (isVehicleCoverageMatchIndex) {
            const brandIndex = headers.findIndex((header) => header.dataset.column === 'vehicleBrand');
            const modelIndex = headers.findIndex((header) => header.dataset.column === 'vehicleModel');

            if (brandIndex >= 0 && modelIndex >= 0) {
                const totalColumns = headers.length;
                const collapsedBrands = new Set();

                const buildGroupRow = (label, count, level, collapsed = false) => {
                    const safeLabel = escapeHtml(label || '');
                    const indent = Math.max(0, level) * 18;
                    const icon = level === 0
                        ? `<i class="fa ${collapsed ? 'fa-chevron-right' : 'fa-chevron-down'}" aria-hidden="true"></i>`
                        : `<span class="ea-dt-rowgroup__dot"></span>`;
                    const countBadge = level === 0 ? `<span class="badge text-bg-light ms-2">${count}</span>` : '';

                    const tr = document.createElement('tr');
                    tr.className = `ea-dt-rowgroup ea-dt-rowgroup--level-${level}`;
                    if (level === 0) {
                        tr.classList.add('ea-dt-rowgroup--collapsible');
                        tr.dataset.group = label;
                        tr.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                    }

                    const td = document.createElement('td');
                    td.colSpan = totalColumns;
                    td.innerHTML = `
                        <div class="ea-dt-rowgroup__inner" style="padding-left:${indent}px;">
                            <span class="ea-dt-rowgroup__toggle">${icon}</span>
                            <span class="ea-dt-rowgroup__label">${safeLabel}</span>
                            ${countBadge}
                        </div>
                    `;
                    tr.appendChild(td);
                    return tr;
                };

                const stripCell = (value) => {
                    if (value === null || value === undefined) {
                        return '';
                    }
                    const raw = String(value);
                    if (raw.indexOf('<') === -1) {
                        return raw.trim();
                    }
                    const tmp = document.createElement('div');
                    tmp.innerHTML = raw;
                    return String(tmp.textContent || '').trim();
                };
                const getCell = (rowNode, index) => {
                    if (!(rowNode instanceof HTMLElement)) {
                        return null;
                    }
                    const cells = rowNode.querySelectorAll('td');
                    const cell = cells[index];
                    return cell instanceof HTMLElement ? cell : null;
                };
                const getGroupValue = (rowNode, index, fallback) => {
                    const cell = getCell(rowNode, index);
                    if (!cell) {
                        return fallback;
                    }
                    const stored = (cell.dataset.eaGroupValue || '').trim();
                    if (stored) {
                        return stored;
                    }
                    const value = stripCell(cell.innerHTML);
                    if (value) {
                        cell.dataset.eaGroupValue = value;
                        return value;
                    }
                    return fallback;
                };
                const hideGroupCells = (rowNode) => {
                    const indices = [brandIndex, modelIndex];
                    for (const index of indices) {
                        const cell = getCell(rowNode, index);
                        if (!cell) {
                            continue;
                        }
                        if (!cell.dataset.eaGroupValue) {
                            const value = stripCell(cell.innerHTML);
                            if (value) {
                                cell.dataset.eaGroupValue = value;
                            }
                        }
                        cell.innerHTML = '';
                    }
                };

                options.orderFixed = {
                    pre: [
                        [brandIndex, 'asc'],
                        [modelIndex, 'asc'],
                    ],
                };
                options.order = [[brandIndex, 'asc'], [modelIndex, 'asc']];

                options.drawCallback = function drawVehicleCoverageMatchGroups() {
                    const api = this.api();
                    const tbody = api.table().body();
                    if (!(tbody instanceof HTMLElement)) {
                        return;
                    }

                    tbody.querySelectorAll('tr.ea-dt-rowgroup').forEach((row) => row.remove());

                    const rowNodes = Array.from(api.rows({ page: 'current' }).nodes());

                    const brandCounts = new Map();
                    for (const rowNode of rowNodes) {
                        const brandLabel = getGroupValue(rowNode, brandIndex, 'Ohne Marke');
                        brandCounts.set(brandLabel, (brandCounts.get(brandLabel) || 0) + 1);
                    }

                    let lastBrand = null;
                    let lastModel = null;

                    for (let i = 0; i < rowNodes.length; i += 1) {
                        const rowNode = rowNodes[i];
                        if (!(rowNode instanceof HTMLElement)) {
                            continue;
                        }

                        const brandLabel = getGroupValue(rowNode, brandIndex, 'Ohne Marke');
                        const modelLabel = getGroupValue(rowNode, modelIndex, 'Ohne Modell');

                        if (brandLabel !== lastBrand) {
                            lastBrand = brandLabel;
                            lastModel = null;
                            const groupRow = buildGroupRow(
                                brandLabel,
                                brandCounts.get(brandLabel) || 0,
                                0,
                                collapsedBrands.has(brandLabel)
                            );
                            tbody.insertBefore(groupRow, rowNode);
                        }

                        const isCollapsed = collapsedBrands.has(brandLabel);
                        if (isCollapsed) {
                            rowNode.style.display = 'none';
                            continue;
                        }
                        rowNode.style.display = '';

                        if (modelLabel !== lastModel) {
                            lastModel = modelLabel;
                            const modelRow = buildGroupRow(modelLabel, 0, 1, false);
                            tbody.insertBefore(modelRow, rowNode);
                        }

                        hideGroupCells(rowNode);
                    }
                };

                vehicleCoverageMatchGroupState = {
                    collapsedBrands,
                };
            }
        }

        const isVehicleEngineIndex = document.body.classList.contains('ea-index-VehicleEngine');
        let vehicleEngineGroupState = null;
        if (isVehicleEngineIndex) {
            const brandIndex = headers.findIndex((header) => header.dataset.column === 'brandGroup' || header.textContent?.trim() === 'Hersteller');
            const seriesIndex = headers.findIndex((header) => header.dataset.column === 'seriesGroup' || header.textContent?.trim() === 'Baureihe');
            const modelIndex = headers.findIndex((header) => header.dataset.column === 'modelGroup' || header.textContent?.trim() === 'Modell');
            const engineNameIndex = headers.findIndex((header) => header.dataset.column === 'name');

            if (brandIndex >= 0 && seriesIndex >= 0 && modelIndex >= 0) {
                const totalColumns = headers.length;
                const collapsedManufacturers = new Set();

                const buildGroupRow = (label, count, level, collapsed = false) => {
                    const safeLabel = escapeHtml(label || '');
                    const indent = Math.max(0, level) * 18;
                    const icon = level === 0
                        ? `<i class="fa ${collapsed ? 'fa-chevron-right' : 'fa-chevron-down'}" aria-hidden="true"></i>`
                        : `<span class="ea-dt-rowgroup__dot"></span>`;
                    const countBadge = level === 0 ? `<span class="badge text-bg-light ms-2">${count}</span>` : '';

                    const tr = document.createElement('tr');
                    tr.className = `ea-dt-rowgroup ea-dt-rowgroup--level-${level}`;
                    if (level === 0) {
                        tr.classList.add('ea-dt-rowgroup--collapsible');
                        tr.dataset.group = label;
                        tr.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                    }

                    const td = document.createElement('td');
                    td.colSpan = totalColumns;
                    td.innerHTML = `
                        <div class="ea-dt-rowgroup__inner" style="padding-left:${indent}px;">
                            <span class="ea-dt-rowgroup__toggle">${icon}</span>
                            <span class="ea-dt-rowgroup__label">${safeLabel}</span>
                            ${countBadge}
                        </div>
                    `;
                    tr.appendChild(td);
                    return tr;
                };

                const stripCell = (value) => {
                    if (value === null || value === undefined) {
                        return '';
                    }
                    const raw = String(value);
                    if (raw.indexOf('<') === -1) {
                        return raw.trim();
                    }
                    const tmp = document.createElement('div');
                    tmp.innerHTML = raw;
                    return String(tmp.textContent || '').trim();
                };
                const getCell = (rowNode, index) => {
                    if (!(rowNode instanceof HTMLElement)) {
                        return null;
                    }
                    const cells = rowNode.querySelectorAll('td');
                    const cell = cells[index];
                    return cell instanceof HTMLElement ? cell : null;
                };
                const getGroupValue = (rowNode, index, fallback) => {
                    const cell = getCell(rowNode, index);
                    if (!cell) {
                        return fallback;
                    }
                    const stored = (cell.dataset.eaGroupValue || '').trim();
                    if (stored) {
                        return stored;
                    }
                    const value = stripCell(cell.innerHTML);
                    if (value) {
                        cell.dataset.eaGroupValue = value;
                        return value;
                    }
                    return fallback;
                };
                const hideGroupCells = (rowNode) => {
                    const indices = [brandIndex, seriesIndex, modelIndex];
                    for (const index of indices) {
                        const cell = getCell(rowNode, index);
                        if (!cell) {
                            continue;
                        }
                        if (!cell.dataset.eaGroupValue) {
                            const value = stripCell(cell.innerHTML);
                            if (value) {
                                cell.dataset.eaGroupValue = value;
                            }
                        }
                        cell.innerHTML = '';
                    }
                };

                options.orderFixed = {
                    pre: [
                        [brandIndex, 'asc'],
                        [seriesIndex, 'asc'],
                        [modelIndex, 'asc'],
                    ],
                };
                if (engineNameIndex >= 0) {
                    options.orderFixed.pre.push([engineNameIndex, 'asc']);
                    options.order = [[engineNameIndex, 'asc']];
                }

                options.drawCallback = function drawVehicleEngineGroups() {
                    const api = this.api();
                    const tbody = api.table().body();
                    if (!(tbody instanceof HTMLElement)) {
                        return;
                    }

                    tbody.querySelectorAll('tr.ea-dt-rowgroup').forEach((row) => row.remove());

                    const rowNodes = Array.from(api.rows({ page: 'current' }).nodes());

                    const brandCounts = new Map();
                    for (const rowNode of rowNodes) {
                        const brandLabel = getGroupValue(rowNode, brandIndex, 'Unbekannter Hersteller');
                        brandCounts.set(brandLabel, (brandCounts.get(brandLabel) || 0) + 1);
                    }

                    let lastBrand = null;
                    let lastSeries = null;
                    let lastModel = null;

                    for (let i = 0; i < rowNodes.length; i += 1) {
                        const rowNode = rowNodes[i];
                        if (!(rowNode instanceof HTMLElement)) {
                            continue;
                        }

                        const brandLabel = getGroupValue(rowNode, brandIndex, 'Unbekannter Hersteller');
                        const seriesLabel = getGroupValue(rowNode, seriesIndex, 'Ohne Baureihe');
                        const modelLabel = getGroupValue(rowNode, modelIndex, 'Ohne Modell');

                        if (brandLabel !== lastBrand) {
                            lastBrand = brandLabel;
                            lastSeries = null;
                            lastModel = null;
                            const groupRow = buildGroupRow(
                                brandLabel,
                                brandCounts.get(brandLabel) || 0,
                                0,
                                collapsedManufacturers.has(brandLabel)
                            );
                            tbody.insertBefore(groupRow, rowNode);
                        }

                        const isCollapsed = collapsedManufacturers.has(brandLabel);
                        if (isCollapsed) {
                            rowNode.style.display = 'none';
                            continue;
                        }
                        rowNode.style.display = '';

                        if (seriesLabel !== lastSeries) {
                            lastSeries = seriesLabel;
                            lastModel = null;
                            const seriesRow = buildGroupRow(seriesLabel, 0, 1, false);
                            tbody.insertBefore(seriesRow, rowNode);
                        }

                        if (modelLabel !== lastModel) {
                            lastModel = modelLabel;
                            const modelRow = buildGroupRow(modelLabel, 0, 2, false);
                            tbody.insertBefore(modelRow, rowNode);
                        }

                        hideGroupCells(rowNode);
                    }
                };

                vehicleEngineGroupState = {
                    collapsedManufacturers,
                };
            }
        }

        if (uniqueNonSortableTargets.length > 0) {
            columnDefs.push({
                targets: uniqueNonSortableTargets,
                orderable: false,
                searchable: false,
            });
        }

        if (columnDefs.length > 0) {
            options.columnDefs = columnDefs;
        }

        try {
            if (typeof DataTable.isDataTable === 'function' && DataTable.isDataTable(table)) {
                table.dataset.eaDatatableInitialized = '1';
                continue;
            }

            // eslint-disable-next-line no-new
            const dataTable = new DataTable(table, options);
            table.dataset.eaDatatableInitialized = '1';

            const contentPanel = table.closest('.content-panel');
            if (contentPanel instanceof HTMLElement) {
                contentPanel.querySelectorAll('.content-panel-footer').forEach((footer) => {
                    if (footer instanceof HTMLElement) {
                        footer.classList.add('d-none');
                    }
                });
            }

            if (isVehicleEngineIndex && vehicleEngineGroupState && table.dataset.eaEngineGroupBound !== '1') {
                table.dataset.eaEngineGroupBound = '1';
                table.addEventListener('click', (event) => {
                    const target = event.target instanceof Element ? event.target : null;
                    if (!target) {
                        return;
                    }

                    const groupRow = target.closest('tr.ea-dt-rowgroup--level-0');
                    if (!(groupRow instanceof HTMLTableRowElement)) {
                        return;
                    }

                    const group = String(groupRow.dataset.group || '').trim();
                    if (!group) {
                        return;
                    }

                    if (vehicleEngineGroupState.collapsedManufacturers.has(group)) {
                        vehicleEngineGroupState.collapsedManufacturers.delete(group);
                    } else {
                        vehicleEngineGroupState.collapsedManufacturers.add(group);
                    }

                    dataTable.draw(false);
                });
            }

            if (isVehicleCoverageMatchIndex && vehicleCoverageMatchGroupState && table.dataset.eaCoverageMatchGroupBound !== '1') {
                table.dataset.eaCoverageMatchGroupBound = '1';
                table.addEventListener('click', (event) => {
                    const target = event.target instanceof Element ? event.target : null;
                    if (!target) {
                        return;
                    }

                    const groupRow = target.closest('tr.ea-dt-rowgroup--level-0');
                    if (!(groupRow instanceof HTMLTableRowElement)) {
                        return;
                    }

                    const group = String(groupRow.dataset.group || '').trim();
                    if (!group) {
                        return;
                    }

                    if (vehicleCoverageMatchGroupState.collapsedBrands.has(group)) {
                        vehicleCoverageMatchGroupState.collapsedBrands.delete(group);
                    } else {
                        vehicleCoverageMatchGroupState.collapsedBrands.add(group);
                    }

                    dataTable.draw(false);
                });
            }
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('DataTables init failed:', error);
        }
    }
}

function initMediaManagerTree() {
    const trees = document.querySelectorAll('[data-media-tree]');
    if (trees.length === 0) {
        return;
    }

    const storageKey = 'tfd_media_tree_state';
    const collapsedIds = new Set();

    const loadState = () => {
        if (!window.localStorage) {
            return;
        }

        try {
            const raw = window.localStorage.getItem(storageKey);
            if (!raw) {
                return;
            }

            const payload = JSON.parse(raw);
            const ids = Array.isArray(payload?.collapsedIds) ? payload.collapsedIds : [];
            ids.forEach((id) => {
                if (typeof id === 'string' && id.trim() !== '') {
                    collapsedIds.add(id);
                } else if (typeof id === 'number' && Number.isFinite(id)) {
                    collapsedIds.add(String(id));
                }
            });
        } catch (_error) {
            collapsedIds.clear();
        }
    };

    const saveState = () => {
        if (!window.localStorage) {
            return;
        }

        const payload = {
            collapsedIds: Array.from(collapsedIds),
        };

        try {
            window.localStorage.setItem(storageKey, JSON.stringify(payload));
        } catch (_error) {
            // Ignore storage failures.
        }
    };

    const findDirectChildrenList = (item) => {
        if (!(item instanceof HTMLElement)) {
            return null;
        }

        for (const child of item.children) {
            if (child instanceof HTMLElement && child.classList.contains('tfd-media-tree__children')) {
                return child;
            }
        }

        return null;
    };

    const getFolderId = (item) => {
        if (!(item instanceof HTMLElement)) {
            return '';
        }

        const raw = String(item.dataset.folderId || '').trim();
        return raw;
    };

    const updateIconState = (item, collapsed) => {
        if (!(item instanceof HTMLElement)) {
            return;
        }

        if (!item.classList.contains('has-children')) {
            return;
        }

        const icon = item.querySelector('[data-folder-icon]');
        if (!(icon instanceof HTMLElement)) {
            return;
        }

        if (collapsed) {
            icon.classList.remove('fa-folder-open');
            icon.classList.add('fa-folder');
        } else {
            icon.classList.add('fa-folder-open');
            icon.classList.remove('fa-folder');
        }
    };

    const setItemCollapsed = (item, collapsed, persistState) => {
        if (!(item instanceof HTMLElement)) {
            return;
        }

        const childrenList = findDirectChildrenList(item);
        if (!(childrenList instanceof HTMLElement)) {
            return;
        }

        childrenList.classList.toggle('is-collapsed', collapsed);

        const toggleButton = item.querySelector('[data-tree-toggle]');
        if (toggleButton instanceof HTMLButtonElement) {
            toggleButton.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }

        updateIconState(item, collapsed);

        const folderId = getFolderId(item);
        if (folderId === '') {
            return;
        }

        if (persistState) {
            if (collapsed) {
                collapsedIds.add(folderId);
            } else {
                collapsedIds.delete(folderId);
            }
            saveState();
        }
    };

    loadState();

    trees.forEach((tree) => {
        tree.querySelectorAll('.tfd-media-tree__item.has-children').forEach((item) => {
            if (!(item instanceof HTMLElement)) {
                return;
            }

            const childrenList = findDirectChildrenList(item);
            if (!(childrenList instanceof HTMLElement)) {
                return;
            }

            const containsSelected = item.dataset.containsSelected === '1';
            const folderId = getFolderId(item);
            if (folderId !== '' && collapsedIds.has(folderId) && !containsSelected) {
                setItemCollapsed(item, true, false);
            } else if (containsSelected) {
                setItemCollapsed(item, false, false);
            } else {
                const isCollapsed = childrenList.classList.contains('is-collapsed');
                updateIconState(item, isCollapsed);
                const toggleButton = item.querySelector('[data-tree-toggle]');
                if (toggleButton instanceof HTMLButtonElement) {
                    toggleButton.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                }
            }
        });

        const scope = tree.closest('[data-media-tree-scope]');
        if (scope instanceof HTMLElement && scope.dataset.treeActionsBound !== '1') {
            scope.dataset.treeActionsBound = '1';
            const expandAllButton = scope.querySelector('[data-tree-expand]');
            const collapseAllButton = scope.querySelector('[data-tree-collapse]');

            if (expandAllButton instanceof HTMLButtonElement) {
                expandAllButton.addEventListener('click', () => {
                    tree.querySelectorAll('.tfd-media-tree__item.has-children').forEach((item) => {
                        setItemCollapsed(item, false, true);
                    });
                });
            }

            if (collapseAllButton instanceof HTMLButtonElement) {
                collapseAllButton.addEventListener('click', () => {
                    tree.querySelectorAll('.tfd-media-tree__item.has-children').forEach((item) => {
                        const containsSelected = item instanceof HTMLElement
                            ? item.dataset.containsSelected === '1'
                            : false;
                        if (containsSelected) {
                            setItemCollapsed(item, false, true);
                            return;
                        }
                        setItemCollapsed(item, true, true);
                    });
                });
            }
        }

        tree.querySelectorAll('[data-tree-toggle]').forEach((toggleButton) => {
            if (!(toggleButton instanceof HTMLButtonElement)) {
                return;
            }

            if (toggleButton.dataset.treeToggleBound === '1') {
                return;
            }

            toggleButton.dataset.treeToggleBound = '1';
            const item = toggleButton.closest('.tfd-media-tree__item');
            const children = findDirectChildrenList(item);

            if (children instanceof HTMLElement) {
                const isExpanded = !children.classList.contains('is-collapsed');
                toggleButton.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                updateIconState(item, !isExpanded);
            }

            toggleButton.addEventListener('click', () => {
                const parentItem = toggleButton.closest('.tfd-media-tree__item');
                const childrenList = findDirectChildrenList(parentItem);
                if (!(childrenList instanceof HTMLElement)) {
                    return;
                }

                const collapsed = childrenList.classList.toggle('is-collapsed');
                toggleButton.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                updateIconState(parentItem, collapsed);

                const folderId = getFolderId(parentItem);
                if (folderId !== '') {
                    if (collapsed) {
                        collapsedIds.add(folderId);
                    } else {
                        collapsedIds.delete(folderId);
                    }
                    saveState();
                }
            });
        });
    });
}

function initActionGroupFallback() {
    if (document.documentElement.dataset.eaActionGroupFallback === '1') {
        return;
    }
    document.documentElement.dataset.eaActionGroupFallback = '1';

    const closeAll = () => {
        document.querySelectorAll('.action-group .dropdown-menu.show').forEach((menu) => {
            menu.classList.remove('show');
        });
        document.querySelectorAll('.action-group .dropdown-toggle[aria-expanded="true"]').forEach((toggle) => {
            toggle.setAttribute('aria-expanded', 'false');
        });
    };

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const toggle = target ? target.closest('.action-group .dropdown-toggle') : null;
        if (!toggle) {
            closeAll();
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const actionGroup = toggle.closest('.action-group');
        const menu = actionGroup ? actionGroup.querySelector('.dropdown-menu') : null;
        if (!(menu instanceof HTMLElement)) {
            return;
        }

        const isOpen = menu.classList.contains('show');
        closeAll();

        if (!isOpen) {
            try {
                if (window.bootstrap && typeof window.bootstrap.Dropdown?.getOrCreateInstance === 'function') {
                    const instance = window.bootstrap.Dropdown.getOrCreateInstance(toggle);
                    instance.toggle();
                    return;
                }
            } catch (error) {
                // fallback to manual toggle
            }

            menu.classList.add('show');
            toggle.setAttribute('aria-expanded', 'true');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });
}

let notifyPermissionGestureBound = false;

function initNotifyPermissionGesture() {
    if (notifyPermissionGestureBound) {
        return;
    }

    // UX Notify uses the native Notification API; most browsers require a user gesture
    // to request permission. We ask on the first click only when permission is still "default".
    if (!('Notification' in window)) {
        return;
    }

    if (window.Notification.permission !== 'default') {
        return;
    }

    notifyPermissionGestureBound = true;
    document.addEventListener(
        'click',
        () => {
            try {
                void window.Notification.requestPermission();
            } catch (_error) {
                // ignore
            }
        },
        { once: true, capture: true }
    );
}

const ckEditor5Instances = new Map();

function parseCkEditor5Config(textarea) {
    const rawConfig = textarea.getAttribute('data-ea-ckeditor5-config');
    if (!rawConfig) {
        return {};
    }

    try {
        const parsed = JSON.parse(rawConfig);
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (_error) {
        return {};
    }
}

function bindCkEditorFormSync(form) {
    if (!form || form.dataset.eaCkeditor5Bound === '1') {
        return;
    }

    form.dataset.eaCkeditor5Bound = '1';
    form.addEventListener('submit', () => {
        for (const [textarea, editor] of ckEditor5Instances.entries()) {
            if (!form.contains(textarea)) {
                continue;
            }

            try {
                textarea.value = editor.getData();
            } catch (_error) {
                // keep form submit robust even if one editor instance failed
            }
        }
    });
}

async function initCkEditor5() {
    if (!window.ClassicEditor || typeof window.ClassicEditor.create !== 'function') {
        return;
    }

    const textareas = document.querySelectorAll('textarea[data-ea-ckeditor5="1"]');
    for (const textarea of textareas) {
        if (textarea.dataset.eaCkeditor5Initialized === '1') {
            continue;
        }

        textarea.dataset.eaCkeditor5Initialized = '1';

        try {
            const editor = await window.ClassicEditor.create(textarea, parseCkEditor5Config(textarea));
            ckEditor5Instances.set(textarea, editor);
            bindCkEditorFormSync(textarea.closest('form'));
        } catch (error) {
            textarea.dataset.eaCkeditor5Initialized = '0';
            // eslint-disable-next-line no-console
            console.error('CKEditor5 init failed:', error);
        }
    }
}

function initSupportChat() {
    const root = document.querySelector('[data-support-chat="1"]');
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const messages = root.querySelector('[data-support-chat-messages]');
    if (messages instanceof HTMLElement && messages.dataset.tfdChatAutoScroll !== '1') {
        messages.dataset.tfdChatAutoScroll = '1';
        window.setTimeout(() => {
            messages.scrollTop = messages.scrollHeight;
        }, 0);
    }

    const filterInput = root.querySelector('[data-support-chat-firm-filter]');
    if (!(filterInput instanceof HTMLInputElement)) {
        return;
    }

    if (filterInput.dataset.tfdBound === '1') {
        return;
    }
    filterInput.dataset.tfdBound = '1';

    const items = Array.from(root.querySelectorAll('[data-support-chat-firm-item]')).filter(
        (el) => el instanceof HTMLElement
    );

    const applyFilter = () => {
        const term = filterInput.value.trim().toLowerCase();
        for (const item of items) {
            if (!(item instanceof HTMLElement)) {
                continue;
            }

            const name = String(item.getAttribute('data-support-chat-firm-name') || item.textContent || '')
                .trim()
                .toLowerCase();
            const isMatch = term === '' || name.includes(term);
            item.classList.toggle('d-none', !isMatch);
        }
    };

    filterInput.addEventListener('input', applyFilter);
}

function initSupportTickets() {
    const root = document.querySelector('[data-support-tickets="1"]');
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const messages = root.querySelector('[data-support-ticket-messages]');
    if (messages instanceof HTMLElement && messages.dataset.tfdTicketAutoScroll !== '1') {
        messages.dataset.tfdTicketAutoScroll = '1';
        window.setTimeout(() => {
            messages.scrollTop = messages.scrollHeight;
        }, 0);
    }

    const filterInput = root.querySelector('[data-support-ticket-filter]');
    if (filterInput instanceof HTMLInputElement && filterInput.dataset.tfdBound !== '1') {
        filterInput.dataset.tfdBound = '1';

        const items = Array.from(root.querySelectorAll('[data-support-ticket-item]')).filter((el) => el instanceof HTMLElement);

        const applyFilter = () => {
            const term = filterInput.value.trim().toLowerCase();
            for (const item of items) {
                if (!(item instanceof HTMLElement)) {
                    continue;
                }

                const name = String(item.getAttribute('data-support-ticket-name') || item.textContent || '')
                    .trim()
                    .toLowerCase();
                const isMatch = term === '' || name.includes(term);
                item.classList.toggle('d-none', !isMatch);
            }
        };

        filterInput.addEventListener('input', applyFilter);
    }

    const customersUrl = String(root.getAttribute('data-support-ticket-customers-url') || '').trim();
    const firmaSelect = root.querySelector('[data-support-ticket-new-firma]');
    const kundeSelect = root.querySelector('[data-support-ticket-new-kunde]');

    if (!(firmaSelect instanceof HTMLSelectElement) || !(kundeSelect instanceof HTMLSelectElement) || customersUrl === '') {
        return;
    }

    if (firmaSelect.dataset.tfdBound === '1') {
        return;
    }
    firmaSelect.dataset.tfdBound = '1';

    const loadCustomers = async () => {
        const firmaId = Number.parseInt(String(firmaSelect.value || '0'), 10) || 0;

        kundeSelect.innerHTML = '';
        const defaultOption = document.createElement('option');
        defaultOption.value = '0';
        defaultOption.textContent = 'Kein Kunde';
        kundeSelect.appendChild(defaultOption);

        if (firmaId <= 0) {
            return;
        }

        try {
            const url = new URL(customersUrl, window.location.origin);
            url.searchParams.set('firma', String(firmaId));
            const response = await fetch(url.toString(), {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const items = Array.isArray(payload?.items) ? payload.items : [];
            for (const item of items) {
                const id = Number.parseInt(String(item?.id || '0'), 10) || 0;
                const name = String(item?.name || '').trim();
                if (id <= 0 || name === '') {
                    continue;
                }

                const option = document.createElement('option');
                option.value = String(id);
                option.textContent = name;
                kundeSelect.appendChild(option);
            }
        } catch (_error) {
            // Ignore fetch errors.
        }
    };

    firmaSelect.addEventListener('change', () => {
        loadCustomers();
    });
}

function runAdminEnhancements() {
    initStageIconSelect2();
    initNotifyPermissionGesture();
    initCkEditor5();
    initEasyAdminListDataTables();
    initMediaManagerTree();
    initActionGroupFallback();
    initSupportChat();
    initSupportTickets();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runAdminEnhancements);
} else {
    runAdminEnhancements();
}

document.addEventListener('turbo:load', runAdminEnhancements);
document.addEventListener('ea.collection.item-added', runAdminEnhancements);

document.addEventListener('notify:connect', (event) => {
    // Helpful for debugging UX Notify in the browser console.
    // eslint-disable-next-line no-console
    console.debug('UX Notify connected', event?.detail || {});

    bindToastNotificationsToEventSources(event?.detail?.eventSources);
});
