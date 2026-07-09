import React from 'react';

const STYLE_ID = 'cdf-fileupload-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-drop{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:var(--space-3);
    text-align:center;padding:var(--space-7) var(--space-5);
    border:var(--bw-thick) dashed var(--border-strong);border-radius:var(--radius-lg);
    background:var(--surface-2);cursor:pointer;font-family:var(--font-sans);color:var(--text);
    transition:border-color var(--dur-fast) var(--ease-out),background var(--dur-fast) var(--ease-out);}
  .cdf-drop:hover,.cdf-drop--over{border-color:var(--accent);background:var(--accent-soft);}
  .cdf-drop__icon{width:44px;height:44px;border-radius:var(--radius-md);background:var(--surface);
    border:var(--bw-hair) solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--accent);}
  .cdf-drop__icon svg,.cdf-drop__icon i{width:22px;height:22px;}
  .cdf-drop__t{font-weight:var(--fw-semibold);}
  .cdf-drop__t em{font-style:normal;color:var(--accent-text);}
  .cdf-drop__sub{font-family:var(--font-mono);font-size:var(--text-2xs);letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);}
  .cdf-drop input{display:none;}
  `;
  document.head.appendChild(el);
}

export function FileUpload({
  title = 'Datei hier ablegen',
  hint = 'BIN · ORI · MOD — max. 30 MB',
  accept,
  multiple = false,
  onFiles = () => {},
  ...rest
}) {
  const [over, setOver] = React.useState(false);
  const inputRef = React.useRef(null);
  const pick = () => inputRef.current && inputRef.current.click();
  const drop = (e) => {
    e.preventDefault(); setOver(false);
    onFiles(Array.from(e.dataTransfer.files || []));
  };
  return (
    <div
      className={`cdf-drop${over ? ' cdf-drop--over' : ''}`}
      role="button" tabIndex={0}
      onClick={pick}
      onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && pick()}
      onDragOver={(e) => { e.preventDefault(); setOver(true); }}
      onDragLeave={() => setOver(false)}
      onDrop={drop}
      {...rest}
    >
      <span className="cdf-drop__icon"><i data-lucide="file-up" /></span>
      <span className="cdf-drop__t"><em>Auswählen</em> oder {title}</span>
      <span className="cdf-drop__sub">{hint}</span>
      <input ref={inputRef} type="file" accept={accept} multiple={multiple}
             onChange={(e) => onFiles(Array.from(e.target.files || []))} />
    </div>
  );
}
