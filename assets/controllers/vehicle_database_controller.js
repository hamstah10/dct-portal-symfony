import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'type',
        'brand',
        'series',
        'model',
        'engine',
        'status',
        'details',
    ];

    connect() {
        this.loadTypes();
    }

    async typeChanged() {
        const typeId = this.typeTarget.value;
        this.clearSelect(this.brandTarget, 'Marke wählen');
        this.clearSelect(this.seriesTarget, 'Baureihe wählen');
        this.clearSelect(this.modelTarget, 'Modell wählen');
        this.clearSelect(this.engineTarget, 'Motor wählen');
        this.renderDetails(null);

        if (!typeId) {
            this.setStatus('Bitte zuerst einen Fahrzeugtyp wählen.');
            return;
        }

        this.setStatus('Lade Marken …');
        try {
            const brands = await this.fetchList(`/vehicle-api/brands/${typeId}`);
            this.populateSelect(this.brandTarget, brands, 'Marke wählen');
            this.brandTarget.disabled = false;
            this.setStatus('Marke wählen …');
        } catch (error) {
            this.handleError(error);
        }
    }

    async brandChanged() {
        const brandId = this.brandTarget.value;
        this.clearSelect(this.seriesTarget, 'Baureihe wählen');
        this.clearSelect(this.modelTarget, 'Modell wählen');
        this.clearSelect(this.engineTarget, 'Motor wählen');
        this.renderDetails(null);

        if (!brandId) {
            this.setStatus('Bitte eine Marke auswählen.');
            return;
        }

        this.setStatus('Lade Baureihen …');
        try {
            const series = await this.fetchList(`/vehicle-api/series/${brandId}`);
            this.populateSelect(this.seriesTarget, series, 'Baureihe wählen');
            this.seriesTarget.disabled = false;
            this.setStatus('Baureihe wählen …');
        } catch (error) {
            this.handleError(error);
        }
    }

    async seriesChanged() {
        const seriesId = this.seriesTarget.value;
        this.clearSelect(this.modelTarget, 'Modell wählen');
        this.clearSelect(this.engineTarget, 'Motor wählen');
        this.renderDetails(null);

        if (!seriesId) {
            this.setStatus('Bitte eine Baureihe auswählen.');
            return;
        }

        this.setStatus('Lade Modelle …');
        try {
            const models = await this.fetchList(`/vehicle-api/models/${seriesId}`);
            this.populateSelect(this.modelTarget, models, 'Modell wählen');
            this.modelTarget.disabled = false;
            this.setStatus('Modell wählen …');
        } catch (error) {
            this.handleError(error);
        }
    }

    async modelChanged() {
        const modelId = this.modelTarget.value;
        this.clearSelect(this.engineTarget, 'Motor wählen');
        this.renderDetails(null);

        if (!modelId) {
            this.setStatus('Bitte ein Modell auswählen.');
            return;
        }

        this.setStatus('Lade Motoren …');
        try {
            const engines = await this.fetchList(`/vehicle-api/engines/${modelId}`);
            this.populateSelect(this.engineTarget, engines, 'Motor wählen');
            this.engineTarget.disabled = false;
            this.setStatus('Motor wählen …');
        } catch (error) {
            this.handleError(error);
        }
    }

    async engineChanged() {
        const engineId = this.engineTarget.value;
        this.renderDetails(null);

        if (!engineId) {
            this.setStatus('Bitte einen Motor auswählen.');
            return;
        }

        this.setStatus('Lade Motor-Details …');
        try {
            const payload = await this.fetchJson(`/vehicle-api/engine-details/${engineId}`);
            this.renderDetails(payload.data || null);
            this.setStatus('Motor-Details laden.');
        } catch (error) {
            this.handleError(error);
        }
    }

    async loadTypes() {
        this.setStatus('Lade Fahrzeugtypen …');
        try {
            const types = await this.fetchList('/vehicle-api/types');
            this.populateSelect(this.typeTarget, types, 'Fahrzeugtyp wählen');
            this.typeTarget.disabled = false;
            this.setStatus('Fahrzeugtyp wählen …');
        } catch (error) {
            this.handleError(error);
        }
    }

    async fetchList(endpoint) {
        const payload = await this.fetchJson(endpoint);
        return Array.isArray(payload.data) ? payload.data : [];
    }

    async fetchJson(endpoint) {
        const url = new URL(endpoint, window.location.origin);
        const response = await fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    populateSelect(select, items, fallbackText) {
        select.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = fallbackText;
        select.appendChild(placeholder);

        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = String(item.id ?? '');
            option.textContent = String(item.name ?? item.title ?? '–');
            select.appendChild(option);
        });
        select.disabled = false;
    }

    clearSelect(select, label) {
        if (!select) {
            return;
        }

        select.innerHTML = '';
        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = label;
        select.appendChild(empty);
        select.disabled = true;
    }

    renderDetails(data) {
        if (!this.hasDetailsTarget) {
            return;
        }

        if (!data) {
            this.detailsTarget.innerHTML = '<p class="text-muted mb-0">Wähle einen Motor, um Stages, ECU-Optionen und Getriebe anzuzeigen.</p>';
            return;
        }

        const engine = data.engine ?? {};
        const stageList = Array.isArray(data.stages) ? data.stages : [];
        const ecuList = Array.isArray(data.ecus) ? data.ecus : [];
        const gearboxList = Array.isArray(data.gearboxes) ? data.gearboxes : [];

        const stageHtml = stageList.length
            ? `<ul class="list-unstyled mb-3">${stageList
                .map(
                    (stage) => `<li class="mb-1">
                        <strong>${stage.name}</strong>
                        ${stage.hp ? `· ${stage.hp} PS` : ''}
                        ${stage.price ? `· ${stage.price} €` : ''}
                    </li>`
                )
                .join('')}</ul>`
            : '<p class="text-muted mb-3">Keine Stages verfügbar.</p>';

        const ecuHtml = ecuList.length
            ? `<div class="mb-3"><strong>ECU-Optionen</strong><ul class="list-unstyled mb-0">${ecuList
                .map((ecu) => `<li>${ecu.name}${ecu.price ? ` · ${ecu.price} €` : ''}</li>`)
                .join('')}</ul></div>`
            : '<p class="text-muted mb-3">Keine ECU-Optionen.</p>';

        const gearboxHtml = gearboxList.length
            ? `<div class="mb-3"><strong>Getriebe</strong><ul class="list-unstyled mb-0">${gearboxList
                .map((gearbox) => `<li>${gearbox.title}${gearbox.type ? ` · ${gearbox.type}` : ''}</li>`)
                .join('')}</ul></div>`
            : '<p class="text-muted mb-3">Keine Getriebe-Infos.</p>';

        this.detailsTarget.innerHTML = `
            <p class="mb-1"><strong>${engine.name || 'Motor'}</strong> · ${engine.horsepower || '–'} PS · ${engine.fuel || '–'}</p>
            ${stageHtml}
            ${ecuHtml}
            ${gearboxHtml}
        `;
    }

    setStatus(message) {
        if (this.hasStatusTarget) {
            this.statusTarget.textContent = message;
        }
    }

    handleError(error) {
        console.error('Vehicle database error', error);
        this.setStatus('Die Fahrzeugdatenbank ist momentan nicht erreichbar.');
    }
}
