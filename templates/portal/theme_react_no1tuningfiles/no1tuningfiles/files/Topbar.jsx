// Portal top bar — back button, global search, credits pill, bell, avatar.
function Topbar({ onBack, creditsAmount = '72.140', notificationCount = 8, userInitials = 'AS' }) {
  const [searchFocus, setSearchFocus] = React.useState(false);

  return (
    <header style={{
      position: 'sticky', top: 0, zIndex: 30, display: 'flex', alignItems: 'center', gap: 16,
      height: 64, padding: '0 26px', background: 'color-mix(in oklch, var(--surface) 88%, transparent)',
      backdropFilter: 'blur(10px)', borderBottom: '1px solid var(--border)',
    }}>
      <button
        onClick={onBack}
        style={{
          display: 'inline-flex', alignItems: 'center', gap: 8, fontFamily: 'var(--font-mono)', fontSize: 11,
          letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--text-muted)',
          border: '1px solid var(--border-strong)', borderRadius: 'var(--radius-sm)',
          padding: '9px 12px', whiteSpace: 'nowrap', cursor: 'pointer', background: 'transparent',
          transition: 'all var(--dur-fast)',
        }}
        onMouseEnter={(e) => { e.target.style.color = 'var(--text)'; e.target.style.borderColor = 'var(--text-muted)'; }}
        onMouseLeave={(e) => { e.target.style.color = 'var(--text-muted)'; e.target.style.borderColor = 'var(--border-strong)'; }}
      >
        <i data-lucide="arrow-left" style={{ width: 15, height: 15 }}></i>
        <span>Zurück</span>
      </button>

      <label style={{
        flex: 1, maxWidth: 420, display: 'flex', alignItems: 'center', gap: 10,
        background: 'var(--surface-2)', border: `1px solid ${searchFocus ? 'var(--accent)' : 'var(--border)'}`,
        borderRadius: 'var(--radius-sm)', padding: '9px 12px', color: 'var(--text-muted)',
        transition: 'border-color var(--dur-fast), box-shadow var(--dur-fast)',
        boxShadow: searchFocus ? '0 0 0 2px rgba(227,30,36,0.1)' : 'none',
      }}>
        <i data-lucide="search" style={{ width: 16, height: 16, flex: '0 0 auto' }}></i>
        <input
          placeholder="Globale Suche…"
          onFocus={() => setSearchFocus(true)}
          onBlur={() => setSearchFocus(false)}
          style={{
            flex: 1, border: 0, background: 'transparent', fontFamily: 'var(--font-sans)',
            fontSize: 13.5, color: 'var(--text)', outline: 'none',
          }}
        />
        <kbd style={{
          fontFamily: 'var(--font-mono)', fontSize: 9, letterSpacing: '0.08em',
          border: '1px solid var(--border-strong)', borderRadius: 3, padding: '2px 6px',
          color: 'var(--text-subtle)', background: 'var(--surface)',
        }}>CMD K</kbd>
      </label>

      <div style={{ marginLeft: 'auto', display: 'flex', alignItems: 'center', gap: 12 }}>
        <button style={{
          display: 'flex', alignItems: 'center', gap: 8, fontFamily: 'var(--font-mono)', fontSize: 12,
          letterSpacing: '0.03em', color: 'var(--text)', background: 'var(--accent-soft)',
          border: '1px solid var(--accent)', borderRadius: 'var(--radius-pill)',
          padding: '8px 14px', cursor: 'pointer', transition: 'all var(--dur-fast)',
        }}
        onMouseEnter={(e) => { e.target.style.background = 'var(--accent)'; e.target.style.color = '#fff'; }}
        onMouseLeave={(e) => { e.target.style.background = 'var(--accent-soft)'; e.target.style.color = 'var(--text)'; }}
        >
          <i data-lucide="circle-dollar-sign" style={{ width: 15, height: 15, color: 'var(--accent)' }}></i>
          <span>Credits <b style={{ color: 'var(--accent-text)' }}>{creditsAmount}</b></span>
        </button>

        <button
          aria-label={`Benachrichtigungen (${notificationCount} neu)`}
          style={{
            width: 38, height: 38, display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
            borderRadius: 'var(--radius-sm)', border: '1px solid var(--border)', background: 'var(--surface)',
            color: 'var(--text-muted)', cursor: 'pointer', position: 'relative',
            transition: 'all var(--dur-fast)',
          }}
          onMouseEnter={(e) => { e.target.style.borderColor = 'var(--text-muted)'; e.target.style.color = 'var(--text)'; }}
          onMouseLeave={(e) => { e.target.style.borderColor = 'var(--border)'; e.target.style.color = 'var(--text-muted)'; }}
        >
          <i data-lucide="bell" style={{ width: 17, height: 17 }}></i>
          {notificationCount > 0 && (
            <span style={{
              position: 'absolute', top: -5, right: -5, minWidth: 16, height: 16, padding: '0 4px',
              borderRadius: 9, background: 'var(--accent)', color: '#fff', fontFamily: 'var(--font-mono)',
              fontSize: 9, display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 600,
            }}>{notificationCount}</span>
          )}
        </button>

        <button
          aria-label="Benutzerprofil"
          style={{
            width: 36, height: 36, borderRadius: '50%', background: 'var(--carbon-800)',
            color: '#fff', fontFamily: 'var(--font-display)', fontStyle: 'italic', fontWeight: 800,
            fontSize: 13, display: 'flex', alignItems: 'center', justifyContent: 'center',
            border: '1px solid rgba(255,255,255,0.1)', cursor: 'pointer',
            transition: 'all var(--dur-fast)',
          }}
          onMouseEnter={(e) => { e.target.style.boxShadow = '0 0 0 2px var(--accent)'; }}
          onMouseLeave={(e) => { e.target.style.boxShadow = 'none'; }}
        >
          {userInitials}
        </button>
      </div>
    </header>
  );
}
window.Topbar = Topbar;
