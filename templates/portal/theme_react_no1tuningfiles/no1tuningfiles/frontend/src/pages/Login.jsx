import { Link } from 'react-router-dom';
import { Shield, ArrowRight, CheckCircle } from '@phosphor-icons/react';
import { PublicLayout } from '../components/layout/PublicLayout';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { portalRoutes } from '../lib/portalRoutes';
import { getPortalDesignBootstrap } from '../lib/portalDesignBootstrap';

export default function Login() {
  const bootstrap = window.portalReactBootstrap?.login ?? {};
  const portalDesign = getPortalDesignBootstrap();
  const loginError = typeof bootstrap.error === 'string' && bootstrap.error.trim() !== ''
    ? bootstrap.error.replace('Invalid credentials.', 'E-Mail oder Passwort sind nicht korrekt.')
    : '';
  const hasLoginError = loginError !== '';

  return (
    <PublicLayout
      eyebrow="Händlerbereich"
      title="Im Händler-Portal anmelden"
      description="Zugang zu Ihrem professionellen File-Service-System mit vergünstigten Preisen, schneller Bearbeitung und persönlichem Support."
      footer={`${portalDesign.portalName} Händlerbereich für Upload, Download und Auftragsverwaltung.`}
      logoWhite
    >
      <form method="post" action={portalRoutes.login} className="space-y-6">
        <input type="hidden" name="_csrf_token" value={bootstrap.csrfToken || ''} />

        <div className="flex items-start gap-4">
          <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-md border border-primary/40 bg-primary/15 text-primary shadow-[0_10px_25px_hsla(var(--primary),0.25)]">
            <Shield className="h-6 w-6" weight="fill" />
          </div>
          <div className="space-y-1">
            <h2 className="font-heading text-[clamp(1.35rem,2vw,2rem)] font-extrabold leading-tight text-white">
              Willkommen zurück
            </h2>
            <p className="text-sm leading-relaxed text-slate-300">
              Bitte melden Sie sich mit Ihren Zugangsdaten an, um Uploads, Aufträge und fertige Files zu verwalten.
            </p>
          </div>
        </div>

        <div className="grid gap-2.5 rounded-md border border-white/10 bg-white/[0.03] p-4 text-sm text-slate-300">
          <div className="flex items-center gap-2">
            <CheckCircle weight="fill" className="h-4 w-4 text-primary" />
            Vergünstigte Händlerkonditionen
          </div>
          <div className="flex items-center gap-2">
            <CheckCircle weight="fill" className="h-4 w-4 text-primary" />
            Tuning-Files in 30–60 Minuten
          </div>
          <div className="flex items-center gap-2">
            <CheckCircle weight="fill" className="h-4 w-4 text-primary" />
            Persönlicher Support für Ihren Betrieb
          </div>
        </div>

        {hasLoginError ? (
          <div className="rounded-md border border-red-500/40 bg-red-500/15 px-4 py-3 text-sm text-red-100 shadow-[0_0_0_1px_rgba(239,68,68,0.14)]" data-testid="login-error" role="alert" aria-live="polite">
            <div className="font-semibold text-red-200">Anmeldung fehlgeschlagen</div>
            <div className="mt-1">{loginError}</div>
          </div>
        ) : null}

        <div className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="email" className="text-slate-200">E-Mail</Label>
            <Input id="email" name="email" type="email" defaultValue={bootstrap.lastUsername || ''} placeholder="kunde@beispiel.de" autoComplete="email" required aria-invalid={hasLoginError} className={hasLoginError ? 'h-11 border-red-500/60 bg-red-500/10 text-white placeholder:text-red-200/70 focus-visible:ring-red-500/60' : 'h-11 border-white/15 bg-white/[0.03] text-white placeholder:text-slate-500 focus-visible:border-primary focus-visible:ring-primary/30'} data-testid="login-email" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="password" className="text-slate-200">Passwort</Label>
            <Input id="password" name="password" type="password" placeholder="••••••••" autoComplete="current-password" required aria-invalid={hasLoginError} className={hasLoginError ? 'h-11 border-red-500/60 bg-red-500/10 text-white placeholder:text-red-200/70 focus-visible:ring-red-500/60' : 'h-11 border-white/15 bg-white/[0.03] text-white placeholder:text-slate-500 focus-visible:border-primary focus-visible:ring-primary/30'} data-testid="login-password" />
          </div>
        </div>

        <div className="flex flex-col gap-3 sm:flex-row">
          <Button className="btn-gradient h-11 text-white shadow-[0_14px_30px_hsla(var(--primary),0.35)] hover:brightness-110 sm:flex-1" type="submit" data-testid="login-submit">
            Zum Händler-Portal
            <ArrowRight className="ml-2 h-4 w-4" weight="bold" />
          </Button>
          <Button asChild variant="outline" className="h-11 border-white/20 bg-white/[0.02] text-slate-100 hover:bg-white/[0.08] hover:text-white sm:flex-1">
            <Link to={portalRoutes.register}>Zugang anfragen</Link>
          </Button>
        </div>

        <div className="flex flex-col gap-2 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
          <Link to={portalRoutes.passwordResetRequest} className="text-slate-300 hover:text-white">
            Passwort vergessen?
          </Link>
          <span>B2B Login für Händler und File-Service Partner.</span>
        </div>
      </form>
    </PublicLayout>
  );
}
