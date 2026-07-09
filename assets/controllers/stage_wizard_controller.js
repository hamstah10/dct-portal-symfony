import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'step1', 'step2', 'step3',
        'stepIndicator',
        'pool',
        'type', 'brand', 'series', 'model', 'engine',
        'existingStages',
        'name', 'description', 'price', 'hp', 'nm', 'originalPs', 'originalNm',
        'iconMediaAsset', 'iconPreview', 'active',
        'submitBtn', 'successMsg', 'errorMsg',
        'engineLabel',
    ];

    static values = {
        urlTypes:   String,
        urlBrands:  String,
        urlSeries:  String,
        urlModels:  String,
        urlEngines: String,
        urlStages:  String,
        urlSave:    String,
    };

    currentStep = 1;
    selectedPoolId = null;
    selectedEngineId = null;

    connect() {
        this.showStep(1);
    }

    showStep(step) {
        this.currentStep = step;
        [this.step1Target, this.step2Target, this.step3Target].forEach((el, i) => {
            el.classList.toggle('d-none', i + 1 !== step);
        });
        this.stepIndicatorTargets.forEach((el, i) => {
            el.classList.toggle('active', i + 1 === step);
            el.classList.toggle('completed', i + 1 < step);
        });
    }

    // Step 1: Pool gewählt → weiter zu Schritt 2
    async poolChanged() {
        this.selectedPoolId = this.poolTarget.value || null;
    }

    async goToStep2() {
        if (!this.selectedPoolId) {
            this.showError('Bitte einen Datenbestand wählen.');
            return;
        }
        this.hideError();
        await this.loadTypes();
        this.showStep(2);
    }

    // Fahrzeug-Selects
    async loadTypes() {
        const url = this.urlTypesValue.replace('__POOL__', this.selectedPoolId);
        const types = await this.fetchList(url);
        this.populateSelect(this.typeTarget, types, 'Fahrzeugtyp wählen');
        this.clearSelect(this.brandTarget, 'Marke wählen');
        this.clearSelect(this.seriesTarget, 'Baureihe wählen');
        this.clearSelect(this.modelTarget, 'Modell wählen');
        this.clearSelect(this.engineTarget, 'Motor wählen');
        this.selectedEngineId = null;
        this.existingStagesTarget.innerHTML = '';
    }

    async typeChanged() {
        const typeId = this.typeTarget.value;
        this.clearSelect(this.brandTarget, 'Marke wählen');
        this.clearSelect(this.seriesTarget, 'Baureihe wählen');
        this.clearSelect(this.modelTarget, 'Modell wählen');
        this.clearSelect(this.engineTarget, 'Motor wählen');
        this.existingStagesTarget.innerHTML = '';
        this.selectedEngineId = null;
        if (!typeId) return;

        const url = this.urlBrandsValue.replace('__POOL__', this.selectedPoolId).replace('__TYPE__', typeId);
        const items = await this.fetchList(url);
        this.populateSelect(this.brandTarget, items, 'Marke wählen');
    }

    async brandChanged() {
        const brandId = this.brandTarget.value;
        this.clearSelect(this.seriesTarget, 'Baureihe wählen');
        this.clearSelect(this.modelTarget, 'Modell wählen');
        this.clearSelect(this.engineTarget, 'Motor wählen');
        this.existingStagesTarget.innerHTML = '';
        this.selectedEngineId = null;
        if (!brandId) return;

        const url = this.urlSeriesValue.replace('__POOL__', this.selectedPoolId).replace('__BRAND__', brandId);
        const items = await this.fetchList(url);
        this.populateSelect(this.seriesTarget, items, 'Baureihe wählen');
    }

    async seriesChanged() {
        const seriesId = this.seriesTarget.value;
        this.clearSelect(this.modelTarget, 'Modell wählen');
        this.clearSelect(this.engineTarget, 'Motor wählen');
        this.existingStagesTarget.innerHTML = '';
        this.selectedEngineId = null;
        if (!seriesId) return;

        const url = this.urlModelsValue.replace('__POOL__', this.selectedPoolId).replace('__SERIES__', seriesId);
        const items = await this.fetchList(url);
        this.populateSelect(this.modelTarget, items, 'Modell wählen');
    }

    async modelChanged() {
        const modelId = this.modelTarget.value;
        this.clearSelect(this.engineTarget, 'Motor wählen');
        this.existingStagesTarget.innerHTML = '';
        this.selectedEngineId = null;
        if (!modelId) return;

        const url = this.urlEnginesValue.replace('__POOL__', this.selectedPoolId).replace('__MODEL__', modelId);
        const items = await this.fetchList(url);
        this.populateSelect(this.engineTarget, items, 'Motor wählen', (item) => {
            const parts = [item.horsepower ? item.horsepower + ' PS' : null, item.fuel, item.displacement].filter(Boolean);
            return item.name + (parts.length ? ' (' + parts.join(', ') + ')' : '');
        });
    }

    async engineChanged() {
        const engineId = this.engineTarget.value;
        this.selectedEngineId = engineId || null;
        this.existingStagesTarget.innerHTML = '';
        if (!engineId) return;

        const url = this.urlStagesValue.replace('__ENGINE__', engineId);
        const stages = await this.fetchList(url);
        this.renderExistingStages(stages);
    }

    renderExistingStages(stages) {
        if (!stages.length) {
            this.existingStagesTarget.innerHTML = '<p class="text-muted small mb-0">Noch keine Leistungsstufen für diesen Motor.</p>';
            return;
        }

        const rows = stages.map(s => `
            <tr>
                <td><strong>${this.esc(s.name)}</strong></td>
                <td>${s.hp ? s.hp + ' PS' : '–'}</td>
                <td>${s.nm ? s.nm + ' Nm' : '–'}</td>
                <td>${s.price ? parseFloat(s.price).toFixed(2) + ' €' : '–'}</td>
                <td><span class="badge ${s.active ? 'bg-success' : 'bg-secondary'}">${s.active ? 'Aktiv' : 'Inaktiv'}</span></td>
            </tr>
        `).join('');

        this.existingStagesTarget.innerHTML = `
            <div class="table-responsive mt-3">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th><th>PS</th><th>Nm</th><th>Preis</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    iconChanged() {
        const select = this.iconMediaAssetTarget;
        const selected = select.options[select.selectedIndex];
        const img = this.iconPreviewTarget;
        const src = selected?.dataset?.image || '';
        if (src) {
            img.src = src;
            img.classList.remove('d-none');
        } else {
            img.src = '';
            img.classList.add('d-none');
        }
    }

    goToStep3() {
        if (!this.selectedEngineId) {
            this.showError('Bitte einen Motor wählen.');
            return;
        }
        this.hideError();

        const engineOption = this.engineTarget.options[this.engineTarget.selectedIndex];
        this.engineLabelTarget.textContent = engineOption ? engineOption.textContent : '';

        this.showStep(3);
    }

    backToStep1() {
        this.showStep(1);
    }

    backToStep2() {
        this.showStep(2);
    }

    async save() {
        const name = this.nameTarget.value.trim();
        if (!name) {
            this.showError('Name ist erforderlich.');
            return;
        }

        this.submitBtnTarget.disabled = true;
        this.hideError();

        const payload = {
            engineId:         parseInt(this.selectedEngineId),
            poolId:           parseInt(this.selectedPoolId),
            name,
            description:      this.descriptionTarget.value,
            price:            this.priceTarget.value,
            hp:               this.hpTarget.value,
            nm:               this.nmTarget.value,
            originalPs:       this.originalPsTarget.value,
            originalNm:       this.originalNmTarget.value,
            iconMediaAssetId: this.iconMediaAssetTarget.value || null,
            active:           this.activeTarget.checked,
        };

        try {
            const resp = await fetch(this.urlSaveValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(payload),
            });

            const data = await resp.json();
            if (!resp.ok) {
                throw new Error(data.error || 'Fehler beim Speichern');
            }

            this.successMsgTarget.innerHTML = `
                Leistungsstufe <strong>${this.esc(name)}</strong> wurde erstellt.
                <a href="${data.editUrl}" class="alert-link ms-2">Jetzt bearbeiten →</a>
            `;
            this.successMsgTarget.classList.remove('d-none');
            this.element.querySelector('form')?.reset();
            this.showStep(1);
        } catch (err) {
            this.showError(err.message);
        } finally {
            this.submitBtnTarget.disabled = false;
        }
    }

    async fetchList(url) {
        try {
            const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } });
            if (!resp.ok) return [];
            const data = await resp.json();
            return Array.isArray(data) ? data : [];
        } catch {
            return [];
        }
    }

    populateSelect(select, items, placeholder, labelFn = null) {
        select.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder;
        select.appendChild(opt);
        items.forEach(item => {
            const o = document.createElement('option');
            o.value = String(item.id ?? '');
            o.textContent = labelFn ? labelFn(item) : String(item.name ?? '–');
            select.appendChild(o);
        });
        select.disabled = false;
    }

    clearSelect(select, label) {
        if (!select) return;
        select.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = label;
        select.appendChild(opt);
        select.disabled = true;
    }

    showError(msg) {
        this.errorMsgTarget.textContent = msg;
        this.errorMsgTarget.classList.remove('d-none');
    }

    hideError() {
        this.errorMsgTarget.classList.add('d-none');
    }

    esc(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
}
