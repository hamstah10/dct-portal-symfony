import { Controller } from '@hotwired/stimulus';

/**
 * Applies a watermark overlay onto <img> elements using Canvas.
 *
 * Usage:
 *   <div data-controller="watermark"
 *        data-watermark-area-value="vehicle_stage"
 *        data-watermark-api-url-value="/api/watermark/config/vehicle_stage">
 *     <img data-watermark-target="image" src="..." />
 *   </div>
 *
 * Or wrap multiple images:
 *   <img data-controller="watermark"
 *        data-watermark-area-value="vehicle_stage"
 *        data-watermark-api-url-value="/api/watermark/config/vehicle_stage"
 *        data-watermark-target="image"
 *        src="...">
 */
export default class extends Controller {
    static targets = ['image'];
    static values  = { apiUrl: String, area: String };

    #config = null;
    #ready  = false;

    async connect() {
        if (!this.hasApiUrlValue || !this.apiUrlValue) return;
        try {
            const resp = await fetch(this.apiUrlValue, {
                headers: { Accept: 'application/json' },
            });
            const data = await resp.json();
            if (!data.enabled || !data.preset) return;
            this.#config = data.preset;
            this.#ready  = true;
            this.imageTargets.forEach(img => this.#applyToImage(img));
        } catch {
            // watermark silently fails
        }
    }

    imageTargetConnected(img) {
        if (!this.#ready) return;
        this.#applyToImage(img);
    }

    #applyToImage(img) {
        if (img.dataset.watermarkApplied === '1') return;
        img.dataset.watermarkApplied = '1';

        const apply = () => this.#render(img);
        if (img.complete && img.naturalWidth > 0) {
            apply();
        } else {
            img.addEventListener('load', apply, { once: true });
        }
    }

    #render(img) {
        const cfg = this.#config;
        if (!cfg) return;

        const canvas  = document.createElement('canvas');
        canvas.width  = img.naturalWidth;
        canvas.height = img.naturalHeight;
        const ctx     = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);

        const opacity = parseFloat(cfg.opacity ?? 0.5);

        const doRender = () => {
            if (cfg.imagePath) {
                this.#drawImageWatermark(ctx, canvas, cfg, opacity);
            } else if (cfg.text) {
                this.#drawTextWatermark(ctx, canvas, cfg, opacity);
                this.#replaceImg(img, canvas);
            }
        };

        if (cfg.imagePath) {
            const wm    = new Image();
            wm.crossOrigin = 'anonymous';
            wm.onload  = () => {
                this.#drawImageWatermark(ctx, canvas, cfg, opacity, wm);
                if (cfg.text) this.#drawTextWatermark(ctx, canvas, cfg, opacity);
                this.#replaceImg(img, canvas);
            };
            wm.onerror = () => {
                if (cfg.text) this.#drawTextWatermark(ctx, canvas, cfg, opacity);
                this.#replaceImg(img, canvas);
            };
            wm.src = cfg.imagePath.startsWith('/') || cfg.imagePath.startsWith('http')
                ? cfg.imagePath
                : '/' + cfg.imagePath;
        } else {
            doRender();
            this.#replaceImg(img, canvas);
        }
    }

    #drawImageWatermark(ctx, canvas, cfg, opacity, wmImg) {
        if (!wmImg) return;
        const ratio = wmImg.naturalHeight / wmImg.naturalWidth;
        const margin = this.#marginPx(cfg, canvas.width, canvas.height);
        const maxW  = cfg.fillWidth
            ? canvas.width - margin.x * 2
            : (cfg.imageWidth ?? Math.round(canvas.width * 0.25));
        const w     = Math.min(maxW, canvas.width);
        const h     = Math.round(w * ratio);
        const [x, y] = this.#position(cfg.position, canvas.width, canvas.height, w, h, margin);
        ctx.globalAlpha = opacity;
        ctx.drawImage(wmImg, x, y, w, h);
        ctx.globalAlpha = 1;
    }

    #drawTextWatermark(ctx, canvas, cfg, opacity) {
        const fontSize = cfg.fontSize ?? 18;
        const color    = cfg.textColor ?? '#ffffff';
        const text     = cfg.text ?? '';
        ctx.font        = `bold ${fontSize}px sans-serif`;
        ctx.fillStyle   = color;
        ctx.globalAlpha = opacity;
        const metrics   = ctx.measureText(text);
        const w         = metrics.width;
        const h         = fontSize;
        const margin    = this.#marginPx(cfg, canvas.width, canvas.height);
        const [x, y]    = this.#position(cfg.position, canvas.width, canvas.height, w, h, margin);
        ctx.fillText(text, x, y + h);
        ctx.globalAlpha = 1;
    }

    /**
     * Margin as pixels, derived from cfg.marginPercent (% of width/height), falling back to a fixed 12px.
     */
    #marginPx(cfg, cw, ch) {
        const pct = parseFloat(cfg.marginPercent);
        if (Number.isNaN(pct)) {
            return { x: 12, y: 12 };
        }
        return { x: Math.round(cw * pct / 100), y: Math.round(ch * pct / 100) };
    }

    #position(pos, cw, ch, w, h, margin = { x: 12, y: 12 }) {
        const { x: padX, y: padY } = margin;
        const map = {
            'top-left':      [padX,              padY],
            'top-center':    [(cw - w) / 2,      padY],
            'top-right':     [cw - w - padX,      padY],
            'middle-left':   [padX,              (ch - h) / 2],
            'middle-center': [(cw - w) / 2,       (ch - h) / 2],
            'middle-right':  [cw - w - padX,      (ch - h) / 2],
            'bottom-left':   [padX,              ch - h - padY],
            'bottom-center': [(cw - w) / 2,       ch - h - padY],
            'bottom-right':  [cw - w - padX,      ch - h - padY],
            'diagonal':      [cw - w - padX,      ch - h - padY],
        };
        return map[pos] ?? map['bottom-right'];
    }

    #replaceImg(img, canvas) {
        canvas.toBlob(blob => {
            if (!blob) return;
            const url  = URL.createObjectURL(blob);
            const next = new Image();
            next.src   = url;
            next.alt   = img.alt;
            next.className = img.className;
            next.style.cssText = img.style.cssText;
            next.dataset.watermarkApplied = '1';
            img.replaceWith(next);
        }, 'image/jpeg', 0.92);
    }
}
