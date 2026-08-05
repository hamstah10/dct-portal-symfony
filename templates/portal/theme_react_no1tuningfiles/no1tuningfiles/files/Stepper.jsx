// Numbered stepper — active step gets accent chip + glow, done steps turn green.
function Stepper({ steps, activeIndex }) {
  return (
    <div style={{ background: 'var(--surface)', border: '1px solid var(--border)', borderRadius: 'var(--radius-lg)', boxShadow: 'var(--shadow-xs)', padding: '18px 22px' }}>
      <div style={{ display: 'flex', alignItems: 'center' }}>
        {steps.map((s, i) => (
          <React.Fragment key={s.label}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 11 }}>
              <span style={{
                width: 38, height: 38, borderRadius: 'var(--radius-md)', display: 'flex', alignItems: 'center', justifyContent: 'center',
                border: '1px solid ' + (i === activeIndex ? 'var(--accent)' : i < activeIndex ? 'var(--success)' : 'var(--border-strong)'),
                color: (i === activeIndex || i < activeIndex) ? '#fff' : 'var(--text-subtle)',
                background: i === activeIndex ? 'var(--accent)' : i < activeIndex ? 'var(--success)' : 'var(--surface)',
                boxShadow: i === activeIndex ? 'var(--glow-power)' : 'none', flex: '0 0 auto',
              }}>
                <i data-lucide={s.icon} style={{ width: 18, height: 18 }}></i>
              </span>
              <span style={{
                fontFamily: 'var(--font-display)', fontStyle: 'italic', fontWeight: 700, textTransform: 'uppercase',
                fontSize: 15, letterSpacing: '0.01em', color: (i === activeIndex || i < activeIndex) ? 'var(--text)' : 'var(--text-subtle)',
              }}>
                {s.label}
              </span>
            </div>
            {i < steps.length - 1 && <span style={{ flex: 1, height: 2, background: i < activeIndex ? 'var(--success)' : 'var(--border)', margin: '0 18px', borderRadius: 2 }} />}
          </React.Fragment>
        ))}
      </div>
    </div>
  );
}
window.Stepper = Stepper;
