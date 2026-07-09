import { Link } from 'react-router-dom';
import { Gauge, Lightning, ShieldCheck } from '@phosphor-icons/react';
import { portalRoutes } from '../../lib/portalRoutes';
import { getPortalDesignBootstrap } from '../../lib/portalDesignBootstrap';

export const PublicLayout = ({ eyebrow, title, description, children, footer, logoWhite = false }) => {
  const portalDesign = getPortalDesignBootstrap();
  const backgroundImage = '/assets/portals/chiptuningfile/login-engine.png';

  return (
    <div className="relative min-h-screen overflow-hidden bg-background text-foreground">
      <div
        className="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-45"
        style={{ backgroundImage: `url(${backgroundImage})` }}
      />
      <div className="absolute inset-0 bg-[radial-gradient(circle_at_18%_24%,rgba(227,28,35,0.30),transparent_42%),radial-gradient(circle_at_84%_14%,rgba(255,255,255,0.10),transparent_38%),linear-gradient(120deg,rgba(4,6,10,0.96)_0%,rgba(7,10,16,0.90)_42%,rgba(11,15,24,0.95)_100%)]" />
      <div className="pointer-events-none absolute inset-0">
        <div
          className="absolute -left-12 -top-12 h-[280px] w-[280px] rounded-full blur-3xl"
          style={{ backgroundColor: `${portalDesign.primaryColor}55` }}
        />
        <div className="absolute -bottom-12 -right-12 h-[260px] w-[260px] rounded-full bg-white/[0.06] blur-3xl" />
      </div>

      <div className="relative z-10 mx-auto flex min-h-screen w-full max-w-[1400px] flex-col px-5 py-6 sm:px-8 sm:py-8 lg:px-12 lg:py-10">
        <header className="flex items-center justify-between border-b border-white/10 pb-5">
          <Link to={portalRoutes.login} className="font-heading text-2xl font-extrabold tracking-tight text-white">
            {portalDesign.logoUrl ? (
              <img
                src={portalDesign.logoUrl}
                alt={portalDesign.portalName}
                className={`h-10 max-w-[220px] object-contain ${logoWhite ? 'brightness-0 invert' : ''}`}
              />
            ) : (
              portalDesign.portalName
            )}
          </Link>

          <div className="hidden items-center gap-4 text-sm text-slate-300 lg:flex">
            <span className="inline-flex items-center gap-2 rounded-sm border border-white/10 bg-white/[0.04] px-3 py-1.5">
              <Gauge className="h-4 w-4 text-primary" weight="fill" />
              B2B File-Service
            </span>
            <span className="inline-flex items-center gap-2 rounded-sm border border-white/10 bg-white/[0.04] px-3 py-1.5">
              <Lightning className="h-4 w-4 text-primary" weight="fill" />
              30–60 Min. Bearbeitung
            </span>
            <span className="inline-flex items-center gap-2 rounded-sm border border-white/10 bg-white/[0.04] px-3 py-1.5">
              <ShieldCheck className="h-4 w-4 text-primary" weight="fill" />
              Händlerbereich
            </span>
          </div>
        </header>

        <main className="grid flex-1 items-center gap-10 py-8 lg:grid-cols-[minmax(0,1.05fr)_minmax(28rem,34rem)] xl:gap-16">
          <section className="space-y-6 lg:pr-6 xl:pr-10">
            <div className="inline-flex items-center rounded-sm border border-primary/35 bg-primary/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-primary">
              {eyebrow}
            </div>

            <div className="max-w-3xl space-y-5">
              <h1 className="max-w-2xl font-heading text-4xl font-black leading-tight tracking-tight text-white md:text-5xl">
                {title}
              </h1>
              <p className="max-w-2xl text-base leading-7 text-slate-300 md:text-lg">
                {description}
              </p>
            </div>
          </section>

          <section className="w-full max-w-[34rem] justify-self-end rounded-md border border-white/15 bg-[rgba(10,14,22,0.78)] p-5 shadow-[0_24px_80px_rgba(0,0,0,0.45)] backdrop-blur-md sm:p-6 md:p-8">
            {children}
          </section>
        </main>

        <footer className="border-t border-white/10 pt-5 text-sm text-slate-400">
          {footer}
        </footer>
      </div>
    </div>
  );
};
