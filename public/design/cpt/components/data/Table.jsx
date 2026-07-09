import React from 'react';

const STYLE_ID = 'cdf-table-styles';
if (typeof document !== 'undefined' && !document.getElementById(STYLE_ID)) {
  const el = document.createElement('style');
  el.id = STYLE_ID;
  el.textContent = `
  .cdf-table-wrap{border:var(--bw-hair) solid var(--border);border-radius:var(--radius-md);overflow:auto;background:var(--surface);}
  .cdf-table{width:100%;border-collapse:collapse;font-family:var(--font-sans);font-size:var(--text-sm);}
  .cdf-table thead th{font-family:var(--font-mono);font-size:var(--text-2xs);letter-spacing:.08em;
    text-transform:uppercase;color:var(--text-muted);text-align:left;font-weight:var(--fw-medium);
    padding:var(--space-3) var(--space-4);border-bottom:var(--bw-hair) solid var(--border);
    background:var(--surface-2);white-space:nowrap;}
  .cdf-table tbody td{padding:var(--space-3) var(--space-4);border-bottom:var(--bw-hair) solid var(--border);color:var(--text);vertical-align:middle;}
  .cdf-table tbody tr:last-child td{border-bottom:0;}
  .cdf-table tbody tr{transition:background var(--dur-fast) var(--ease-out);}
  .cdf-table--hover tbody tr:hover{background:var(--surface-2);}
  .cdf-table .num{font-family:var(--font-mono);font-variant-numeric:tabular-nums;text-align:right;}
  .cdf-table th.num{text-align:right;}
  `;
  document.head.appendChild(el);
}

export function Table({ columns = [], rows = [], hover = true, ...rest }) {
  return (
    <div className="cdf-table-wrap">
      <table className={`cdf-table${hover ? ' cdf-table--hover' : ''}`} {...rest}>
        <thead>
          <tr>
            {columns.map((c) => (
              <th key={c.key} className={c.numeric ? 'num' : ''} style={c.width ? { width: c.width } : undefined}>{c.header}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((r, i) => (
            <tr key={r.id ?? i}>
              {columns.map((c) => (
                <td key={c.key} className={c.numeric ? 'num' : ''}>{typeof c.render === 'function' ? c.render(r) : r[c.key]}</td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
