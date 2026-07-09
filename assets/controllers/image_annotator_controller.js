import { Controller } from '@hotwired/stimulus';

/**
 * Canvas-based image annotation editor.
 * Supports: Rectangle (draw by drag) and Text (click to place).
 * Annotations stored as JSON, original image unchanged.
 */
export default class extends Controller {
    static targets = [
        'canvas', 'toolRect', 'toolText', 'colorPicker', 'fontSize', 'fontSizeGroup',
        'strokeWidth', 'hint', 'textInputRow', 'textInput',
        'saveBtn', 'successMsg', 'errorMsg',
    ];
    static values = {
        imageUrl:  String,
        assetId:   Number,
        backUrl:   String,
        apiGet:    String,
        apiSave:   String,
    };

    #tool       = 'rect';
    #color      = '#ff0000';
    #fontSize   = 20;
    #strokeWidth = 2;
    #annotations = [];
    #drawing    = false;
    #startX     = 0;
    #startY     = 0;
    #pendingTextPos = null;
    #img        = null;
    #scale      = 1;

    async connect() {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = async () => {
            this.#img = img;
            const canvas = this.canvasTarget;
            canvas.width  = img.naturalWidth;
            canvas.height = img.naturalHeight;
            canvas.style.maxWidth = '100%';

            const containerW = canvas.parentElement.clientWidth || canvas.width;
            this.#scale = Math.min(1, containerW / canvas.width);

            try {
                const resp = await fetch(this.apiGetValue);
                const data = await resp.json();
                this.#annotations = Array.isArray(data.annotations) ? data.annotations : [];
            } catch {
                this.#annotations = [];
            }

            this.#redraw();
        };
        img.src = this.imageUrlValue;
    }

    selectRect() {
        this.#tool = 'rect';
        this.toolRectTarget.classList.add('active');
        this.toolTextTarget.classList.remove('active');
        this.fontSizeGroupTarget.classList.add('d-none');
        this.hintTarget.textContent = 'Klicken und ziehen um ein Rechteck zu zeichnen.';
        this.#hidePendingText();
    }

    selectText() {
        this.#tool = 'text';
        this.toolTextTarget.classList.add('active');
        this.toolRectTarget.classList.remove('active');
        this.fontSizeGroupTarget.classList.remove('d-none');
        this.hintTarget.textContent = 'Auf das Bild klicken um Text zu platzieren.';
        this.#hidePendingText();
    }

    colorChanged() {
        this.#color = this.colorPickerTarget.value;
    }

    fontSizeChanged() {
        this.#fontSize = parseInt(this.fontSizeTarget.value, 10) || 20;
    }

    strokeWidthChanged() {
        this.#strokeWidth = parseInt(this.strokeWidthTarget.value, 10) || 2;
    }

    onMouseDown(e) {
        if (this.#tool !== 'rect') return;
        this.#drawing = true;
        const [x, y] = this.#canvasPos(e);
        this.#startX = x;
        this.#startY = y;
    }

    onMouseUp(e) {
        if (this.#tool === 'rect' && this.#drawing) {
            this.#drawing = false;
            const [x, y] = this.#canvasPos(e);
            const w = x - this.#startX;
            const h = y - this.#startY;
            if (Math.abs(w) > 3 && Math.abs(h) > 3) {
                this.#annotations.push({
                    type:        'rect',
                    x:           Math.min(this.#startX, x),
                    y:           Math.min(this.#startY, y),
                    w:           Math.abs(w),
                    h:           Math.abs(h),
                    color:       this.#color,
                    strokeWidth: this.#strokeWidth,
                });
                this.#redraw();
            }
            return;
        }

        if (this.#tool === 'text') {
            const [x, y] = this.#canvasPos(e);
            this.#pendingTextPos = { x, y };
            this.textInputRowTarget.classList.remove('d-none');
            this.textInputTarget.value = '';
            this.textInputTarget.focus();
        }
    }

    onMouseMove(e) {
        if (!this.#drawing || this.#tool !== 'rect') return;
        const [x, y] = this.#canvasPos(e);
        this.#redraw();
        const ctx = this.canvasTarget.getContext('2d');
        ctx.strokeStyle = this.#color;
        ctx.lineWidth   = this.#strokeWidth;
        ctx.strokeRect(this.#startX, this.#startY, x - this.#startX, y - this.#startY);
    }

    confirmText() {
        const text = this.textInputTarget.value.trim();
        if (!text || !this.#pendingTextPos) {
            this.#hidePendingText();
            return;
        }
        this.#annotations.push({
            type:     'text',
            x:        this.#pendingTextPos.x,
            y:        this.#pendingTextPos.y,
            text:     text,
            color:    this.#color,
            fontSize: this.#fontSize,
        });
        this.#hidePendingText();
        this.#redraw();
    }

    cancelText() {
        this.#hidePendingText();
    }

    undoLast() {
        this.#annotations.pop();
        this.#redraw();
    }

    clearAll() {
        if (!confirm('Alle Annotationen wirklich entfernen?')) return;
        this.#annotations = [];
        this.#redraw();
    }

    async save() {
        this.saveBtnTarget.disabled = true;
        this.successMsgTarget.classList.add('d-none');
        this.errorMsgTarget.classList.add('d-none');
        try {
            const resp = await fetch(this.apiSaveValue, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body:    JSON.stringify({ annotations: this.#annotations }),
            });
            if (!resp.ok) throw new Error(resp.status);
            this.successMsgTarget.classList.remove('d-none');
            setTimeout(() => this.successMsgTarget.classList.add('d-none'), 3000);
        } catch {
            this.errorMsgTarget.classList.remove('d-none');
        } finally {
            this.saveBtnTarget.disabled = false;
        }
    }

    #hidePendingText() {
        this.#pendingTextPos = null;
        this.textInputRowTarget.classList.add('d-none');
    }

    #canvasPos(e) {
        const rect  = this.canvasTarget.getBoundingClientRect();
        const scale = this.canvasTarget.width / rect.width;
        return [
            Math.round((e.clientX - rect.left) * scale),
            Math.round((e.clientY - rect.top)  * scale),
        ];
    }

    #redraw() {
        const canvas = this.canvasTarget;
        const ctx    = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (this.#img) ctx.drawImage(this.#img, 0, 0);

        for (const a of this.#annotations) {
            if (a.type === 'rect') {
                ctx.strokeStyle = a.color ?? '#ff0000';
                ctx.lineWidth   = a.strokeWidth ?? 2;
                ctx.strokeRect(a.x, a.y, a.w, a.h);
            } else if (a.type === 'text') {
                const size = a.fontSize ?? 20;
                ctx.font      = `bold ${size}px sans-serif`;
                ctx.fillStyle = a.color ?? '#ff0000';
                ctx.fillText(a.text ?? '', a.x, a.y);
            }
        }
    }
}
