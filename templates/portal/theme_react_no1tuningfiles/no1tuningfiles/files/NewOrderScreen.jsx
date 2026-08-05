// "Neuer Auftrag" wizard step 1 — File & Lesegerät. Composes DS Select/Dropzone/Textarea/Button.
function NewOrderScreen() {
  const { Select, Textarea, Button } = window.No1TuningfilesDesignSystem_d7bdd5;
  const [seg, setSeg] = React.useState({ method: 'OBD', art: 'Virtuell', ms: 'Slave', prio: 'Normal', test: 'Match positiv (Auto-Fertig)' });
  const Opt = ({ group, value, icon, price, children }) => (
    <button
      onClick={() => setSeg((s) => ({ ...s, [group]: value }))}
      style={{
        display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, padding: '11px 12px',
        border: '1px solid ' + (seg[group] === value ? 'var(--accent)' : 'var(--border-strong)'),
        borderRadius: 'var(--radius-sm)', background: seg[group] === value ? 'var(--accent-soft)' : 'var(--surface)',
        color: seg[group] === value ? 'var(--accent-text)' : 'var(--text-muted)', fontFamily: 'var(--font-sans)',
        fontSize: 13.5, fontWeight: 500, cursor: 'pointer', textAlign: 'center', transition: 'all var(--dur-fast)',
      }}
    >
      {icon && <i data-lucide={icon} style={{ width: 15, height: 15 }}></i>}
      <span>{children}</span>
      {price && <em style={{ fontFamily: 'var(--font-mono)', fontSize: 11, fontStyle: 'normal', color: 'var(--accent-text)' }}>{price}</em>}
    </button>
  );
  const FieldLabel = ({ icon, children }) => (
    <div style={{ display: 'flex', alignItems: 'center', gap: 9, fontFamily: 'var(--font-mono)', fontSize: 11, letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--text-muted)', marginBottom: 12 }}>
      <i data-lucide={icon} style={{ width: 15, height: 15, color: 'var(--accent)' }}></i><span>{children}</span>
    </div>
  );
  const Field = ({ children }) => <div style={{ padding: '18px 0', borderBottom: '1px solid var(--border)' }}>{children}</div>;

  return (
    <div style={{ padding: 26, display: 'flex', flexDirection: 'column', gap: 20, maxWidth: 1180, width: '100%', margin: '0 auto' }}>
      <Stepper steps={[{ icon: 'upload', label: 'File & Lesegerät' }, { icon: 'car-front', label: 'Fahrzeug + Tuning' }, { icon: 'circle-check-big', label: 'Übersicht' }]} activeIndex={0} />

      <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
        <h1 style={{ fontFamily: 'var(--font-display)', fontStyle: 'italic', fontWeight: 800, textTransform: 'uppercase', letterSpacing: 'var(--ls-tight)', fontSize: 30, margin: 0, lineHeight: 1 }}>File &amp; Lesegerät</h1>
        <span style={{ fontFamily: 'var(--font-mono)', fontSize: 11, letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--accent-text)', background: 'var(--accent-soft)', padding: '5px 10px', borderRadius: 'var(--radius-pill)' }}>Step 01</span>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1.55fr 1fr', gap: 20, alignItems: 'start' }}>
        <section style={{ background: 'var(--surface)', border: '1px solid var(--border)', borderRadius: 'var(--radius-lg)', boxShadow: 'var(--shadow-xs)', padding: '8px 22px 22px', display: 'flex', flexDirection: 'column' }}>
          <Field><FieldLabel icon="wrench">Tuning Tool / Lesegerät</FieldLabel><Select options={['Autotuner', 'Flash-Interface V3', 'KESS3', 'CMD Flash']} /></Field>
          <Field>
            <FieldLabel icon="scan-line">Lesemethode</FieldLabel>
            <div style={{ display: 'grid', gap: 8, gridTemplateColumns: 'repeat(3,1fr)' }}>
              <Opt group="method" value="OBD">OBD</Opt><Opt group="method" value="Bench">Bench</Opt><Opt group="method" value="Boot">Boot</Opt>
            </div>
          </Field>
          <Field>
            <FieldLabel icon="book-open">Leseart</FieldLabel>
            <div style={{ display: 'grid', gap: 8, gridTemplateColumns: 'repeat(2,1fr)' }}>
              <Opt group="art" value="Virtuell" icon="cloud-download">Virtuell</Opt><Opt group="art" value="Full Read" icon="hard-drive">Full Read</Opt>
            </div>
          </Field>
          <Field>
            <FieldLabel icon="git-branch">Master / Slave</FieldLabel>
            <div style={{ display: 'grid', gap: 8, gridTemplateColumns: 'repeat(2,1fr)' }}>
              <Opt group="ms" value="Slave" icon="link">Slave</Opt><Opt group="ms" value="Master" icon="crown">Master</Opt>
            </div>
          </Field>
          <Field>
            <FieldLabel icon="timer">Priorität</FieldLabel>
            <div style={{ display: 'grid', gap: 8, gridTemplateColumns: 'repeat(3,1fr)' }}>
              <Opt group="prio" value="Normal">Normal</Opt><Opt group="prio" value="Express" price="+49€">Express</Opt><Opt group="prio" value="Sofort" price="+99€">Sofort</Opt>
            </div>
          </Field>
          <Field>
            <FieldLabel icon="zap">WinOLS Test-Modus</FieldLabel>
            <div style={{ display: 'grid', gap: 8, gridTemplateColumns: 'repeat(2,1fr)' }}>
              <Opt group="test" value="Match positiv (Auto-Fertig)">Match positiv (Auto-Fertig)</Opt>
              <Opt group="test" value="Kein Match (manuell/offen)">Kein Match (manuell/offen)</Opt>
            </div>
          </Field>
        </section>

        <section style={{ background: 'var(--surface)', border: '1px solid var(--border)', borderRadius: 'var(--radius-lg)', boxShadow: 'var(--shadow-xs)', padding: 22, display: 'flex', flexDirection: 'column', gap: 16 }}>
          {window.No1TuningfilesDesignSystem_d7bdd5.Dropzone({ title: 'Datei hierher ziehen', subtitle: '.bin .ori .mod .ecu .hex .s19 .bkp .slave' })}
          <div style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 14px', border: '1px solid var(--border)', borderRadius: 'var(--radius-md)', background: 'var(--surface)' }}>
            <div style={{ width: 34, height: 34, borderRadius: 'var(--radius-sm)', background: 'var(--surface-3)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--text-muted)', flex: '0 0 auto' }}>
              <i data-lucide="file-cog" style={{ width: 16, height: 16 }}></i>
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontFamily: 'var(--font-mono)', fontSize: 12.5, color: 'var(--text)', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>winols_dump_edc17c64.bin</div>
              <div style={{ fontFamily: 'var(--font-mono)', fontSize: 10.5, color: 'var(--text-subtle)', marginTop: 3 }}>2.04 MB · hochgeladen</div>
            </div>
            <span style={{ fontFamily: 'var(--font-mono)', fontSize: 10, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--success)', background: 'var(--success-soft)', padding: '4px 9px', borderRadius: 'var(--radius-pill)' }}>OK</span>
          </div>
        </section>
      </div>

      <section style={{ background: 'var(--surface)', border: '1px solid var(--border)', borderRadius: 'var(--radius-lg)', boxShadow: 'var(--shadow-xs)', padding: '20px 22px' }}>
        <Textarea label="Kommentar (optional)" placeholder="Zusätzliche Hinweise, Wünsche oder Informationen…" rows={4} />
      </section>

      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 20, flexWrap: 'wrap' }}>
        <div style={{ display: 'flex', alignItems: 'baseline', gap: 10 }}>
          <span style={{ fontFamily: 'var(--font-mono)', fontSize: 11, letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--text-muted)' }}>Geschätzte Kosten</span>
          <span style={{ fontFamily: 'var(--font-display)', fontStyle: 'italic', fontWeight: 800, fontSize: 30, lineHeight: 1 }}><span style={{ color: 'var(--accent)' }}>0</span> Credits</span>
        </div>
        <Button variant="primary" style={{ fontSize: 16, padding: '14px 26px' }}>Weiter zu Schritt 2 <i data-lucide="arrow-right" style={{ width: 18, height: 18 }}></i></Button>
      </div>
    </div>
  );
}
window.NewOrderScreen = NewOrderScreen;
