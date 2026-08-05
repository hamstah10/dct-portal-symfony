// "Alle Aufträge" — KPI tiles + searchable orders table. Composes DS Badge/Tag.
const ORDERS = [
  { id: 'A-000144', date: '22.05.2026, 03:19', veh: 'Audi C7 — 2011 A6', spec: '3.0 TDI EU6 · 218 PS · Bosch EDC17CP54', stage: 'Stage 1', tags: ['Swirl Flaps-Off'], status: 'ok', credits: 150 },
  { id: 'A-000143', date: '22.05.2026, 03:18', veh: 'Audi 2011 — C7 A6', spec: '3.0 TDI EU6 (FWD) 218 PS · Diesel · Bosch EDC17CP14', stage: 'Stage 1', tags: ['CVN Patch', 'Drallklappen Off'], status: 'ok', credits: 200 },
  { id: 'A-000142', date: '23.04.2026, 10:09', veh: 'Audi 2011 — C7 A6', spec: '3.0 TDI EU6 (FWD) 218 PS · Diesel · EDC17CP44', stage: 'Stage 1', tags: ['AdBlue (SCR) Off'], status: 'ok', credits: 150 },
  { id: 'A-000129', date: '15.04.2026, 23:37', veh: 'Audi 2011 — C7 A6', spec: '3.0 TDI EU6 (FWD) 218 PS · Diesel · Bosch EDC17CP14', stage: 'Stage 1', tags: ['DPF Off'], status: 'ok', credits: 100 },
  { id: 'A-000128', date: '15.04.2026, 20:43', veh: 'Audi C7 — 2011 A6', spec: '3.0 TDI EU6 · 218 PS · Bosch EDC17CP54', stage: 'Stage 1', tags: ['DPF Off', 'V-Max Off'], status: 'ok', credits: 100 },
  { id: 'A-000126', date: '06.04.2026, 02:50', veh: 'Audi 2013 — C7 RS6', spec: '4.0 TFSI 560 PS · Benzin · MED17.1.1', stage: 'Stage 1', tags: ['DPF Off', 'Auspuffklappen'], status: 'ok', credits: 180 },
  { id: 'A-000124', date: '28.03.2026, 08:14', veh: 'Audi C7 — 2011 A6', spec: '3.0 TDI EU6 (FWD) · Bosch EDC17CP14', stage: 'Stage 1', tags: ['AGR Off'], status: 'no', credits: 100 },
  { id: 'A-000122', date: '28.03.2026, 07:15', veh: 'Audi C7 — 2011 A6', spec: '3.0 TDI EU6 (FWD) · Bosch EDC17CP14', stage: 'Stage 1', tags: ['DPF Off', 'Kat Off'], status: 'ok', credits: 100 },
];

function OrdersScreen() {
  const { Badge, Select } = window.No1TuningfilesDesignSystem_d7bdd5;
  const statusBadge = (s) => s === 'ok' ? <Badge color="green">Abgeschlossen</Badge> : <Badge color="red">Storniert</Badge>;

  const KPI = ({ label, value, icon, tone }) => (
    <div style={{ position: 'relative', background: 'var(--surface)', border: '1px solid var(--border)', borderRadius: 'var(--radius-lg)', padding: '18px 18px 16px', boxShadow: 'var(--shadow-xs)', overflow: 'hidden' }}>
      <span style={{ position: 'absolute', top: 0, left: 0, width: 3, height: '100%', background: 'var(--accent)' }} />
      <div style={{ fontFamily: 'var(--font-mono)', fontSize: 11, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--text-muted)', paddingRight: 40 }}>{label}</div>
      <div style={{ fontFamily: 'var(--font-display)', fontStyle: 'italic', fontWeight: 800, fontSize: 38, lineHeight: 1, marginTop: 12, letterSpacing: 'var(--ls-tight)' }}>{value}</div>
      <span style={{ position: 'absolute', top: 16, right: 16, width: 34, height: 34, borderRadius: 'var(--radius-sm)', display: 'flex', alignItems: 'center', justifyContent: 'center', background: tone + '-soft' === 'kg-soft' ? 'var(--surface-3)' : `var(--${tone}-soft)`, color: tone === 'kg' ? 'var(--text-muted)' : `var(--${tone})` }}>
        <i data-lucide={icon} style={{ width: 17, height: 17 }}></i>
      </span>
    </div>
  );

  return (
    <div style={{ padding: 26, display: 'flex', flexDirection: 'column', gap: 20, maxWidth: 1500, width: '100%', margin: '0 auto' }}>
      <h1 style={{ fontFamily: 'var(--font-display)', fontStyle: 'italic', fontWeight: 800, textTransform: 'uppercase', letterSpacing: 'var(--ls-tight)', fontSize: 30, margin: 0 }}>Aufträge</h1>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(5,1fr)', gap: 16 }}>
        <KPI label="Gesamt Aufträge" value="35" icon="history" tone="kg" />
        <KPI label="Abgeschlossen" value="33" icon="check" tone="success" />
        <KPI label="In Bearbeitung" value="0" icon="loader" tone="info" />
        <KPI label="Warten auf Prüfung" value="0" icon="clock" tone="warning" />
        <KPI label="Geparkt" value="0" icon="pause" tone="kg" />
      </div>

      <section style={{ background: 'var(--surface)', border: '1px solid var(--border)', borderRadius: 'var(--radius-lg)', boxShadow: 'var(--shadow-xs)' }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 16, flexWrap: 'wrap', padding: '18px 22px', borderBottom: '1px solid var(--border)' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, fontFamily: 'var(--font-display)', fontStyle: 'italic', fontWeight: 800, textTransform: 'uppercase', letterSpacing: 'var(--ls-tight)', fontSize: 20 }}>
            <i data-lucide="history" style={{ width: 19, height: 19, color: 'var(--accent)' }}></i><span>Alle Aufträge</span>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
            <label style={{ display: 'flex', alignItems: 'center', gap: 9, background: 'var(--surface-2)', border: '1px solid var(--border)', borderRadius: 'var(--radius-sm)', padding: '8px 12px', color: 'var(--text-muted)', minWidth: 240 }}>
              <i data-lucide="search" style={{ width: 15, height: 15, flex: '0 0 auto' }}></i>
              <input placeholder="Auftrag oder Fahrzeug suchen…" style={{ flex: 1, border: 0, background: 'transparent', fontFamily: 'var(--font-sans)', fontSize: 13, color: 'var(--text)', outline: 'none' }} />
            </label>
            <Select options={['Alle Status', 'Abgeschlossen', 'In Bearbeitung', 'Storniert']} style={{ minWidth: 150 }} />
          </div>
        </div>
        <div style={{ overflowX: 'auto' }}>
          <table style={{ width: '100%', borderCollapse: 'collapse', minWidth: 1080 }}>
            <thead>
              <tr>
                {['Auftrag #', 'Datum', 'Fahrzeug', 'Tuning', 'Status', 'Credits', 'Aktionen'].map((h, i) => (
                  <th key={h} style={{ fontFamily: 'var(--font-mono)', fontSize: 10.5, letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--text-subtle)', textAlign: i >= 5 ? 'right' : 'left', padding: '14px 18px', borderBottom: '1px solid var(--border-strong)', whiteSpace: 'nowrap' }}>{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {ORDERS.map((o, i) => (
                <tr key={o.id}>
                  <td style={{ padding: '16px 18px', borderBottom: i < ORDERS.length - 1 ? '1px solid var(--border)' : 'none' }}>
                    <span style={{ fontFamily: 'var(--font-mono)', fontSize: 13, color: 'var(--accent-text)', fontWeight: 500, whiteSpace: 'nowrap' }}>{o.id}</span>
                  </td>
                  <td style={{ padding: '16px 18px', borderBottom: i < ORDERS.length - 1 ? '1px solid var(--border)' : 'none', fontFamily: 'var(--font-mono)', fontSize: 12.5, color: 'var(--text-muted)', whiteSpace: 'nowrap' }}>{o.date}</td>
                  <td style={{ padding: '16px 18px', borderBottom: i < ORDERS.length - 1 ? '1px solid var(--border)' : 'none' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 11 }}>
                      <span style={{ width: 36, height: 36, borderRadius: 'var(--radius-sm)', background: 'var(--accent-soft)', color: 'var(--accent)', display: 'flex', alignItems: 'center', justifyContent: 'center', flex: '0 0 auto' }}>
                        <i data-lucide="car-front" style={{ width: 18, height: 18 }}></i>
                      </span>
                      <div>
                        <div style={{ fontFamily: 'var(--font-display)', fontStyle: 'italic', fontWeight: 700, textTransform: 'uppercase', fontSize: 15, lineHeight: 1.1 }}>{o.veh}</div>
                        <div style={{ fontFamily: 'var(--font-mono)', fontSize: 11, color: 'var(--text-subtle)', marginTop: 4 }}>{o.spec}</div>
                      </div>
                    </div>
                  </td>
                  <td style={{ padding: '16px 18px', borderBottom: i < ORDERS.length - 1 ? '1px solid var(--border)' : 'none' }}>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 7 }}>
                      <span style={{ fontSize: 13, fontWeight: 600 }}>{o.stage}</span>
                      <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                        {o.tags.map((t) => <span key={t} style={{ fontFamily: 'var(--font-mono)', fontSize: 10, letterSpacing: '0.05em', textTransform: 'uppercase', padding: '4px 8px', borderRadius: 'var(--radius-sm)', background: 'var(--carbon-800)', color: '#fff' }}>{t}</span>)}
                      </div>
                    </div>
                  </td>
                  <td style={{ padding: '16px 18px', borderBottom: i < ORDERS.length - 1 ? '1px solid var(--border)' : 'none' }}>{statusBadge(o.status)}</td>
                  <td style={{ padding: '16px 18px', borderBottom: i < ORDERS.length - 1 ? '1px solid var(--border)' : 'none', textAlign: 'right' }}>
                    <div style={{ lineHeight: 1.1 }}>
                      <b style={{ fontFamily: 'var(--font-display)', fontStyle: 'italic', fontWeight: 800, fontSize: 20 }}>{o.credits}</b>
                      <span style={{ display: 'block', fontFamily: 'var(--font-mono)', fontSize: 10, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--text-subtle)', marginTop: 3 }}>Credits</span>
                    </div>
                  </td>
                  <td style={{ padding: '16px 18px', borderBottom: i < ORDERS.length - 1 ? '1px solid var(--border)' : 'none', textAlign: 'right' }}>
                    <div style={{ display: 'inline-flex', gap: 6, justifyContent: 'flex-end' }}>
                      {o.status === 'ok' && <button style={{ width: 32, height: 32, borderRadius: 'var(--radius-sm)', border: '1px solid var(--border)', background: 'var(--surface)', color: 'var(--text-muted)', cursor: 'pointer', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}><i data-lucide="download" style={{ width: 15, height: 15 }}></i></button>}
                      <button style={{ width: 32, height: 32, borderRadius: 'var(--radius-sm)', border: '1px solid var(--border)', background: 'var(--surface)', color: 'var(--text-muted)', cursor: 'pointer', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}><i data-lucide="arrow-right" style={{ width: 15, height: 15 }}></i></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 16, flexWrap: 'wrap', padding: '16px 22px', borderTop: '1px solid var(--border)', fontFamily: 'var(--font-mono)', fontSize: 12, color: 'var(--text-muted)', letterSpacing: '0.03em' }}>
          <span>1–8 von 35 Aufträgen</span>
          <div style={{ display: 'flex', gap: 6 }}>
            {[1, 2, 3, 4].map((n) => (
              <button key={n} style={{ minWidth: 32, height: 32, padding: '0 8px', borderRadius: 'var(--radius-sm)', border: '1px solid ' + (n === 1 ? 'var(--accent)' : 'var(--border)'), background: n === 1 ? 'var(--accent)' : 'var(--surface)', color: n === 1 ? '#fff' : 'var(--text-muted)', fontFamily: 'var(--font-mono)', fontSize: 12, cursor: 'pointer' }}>{n}</button>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
window.OrdersScreen = OrdersScreen;
