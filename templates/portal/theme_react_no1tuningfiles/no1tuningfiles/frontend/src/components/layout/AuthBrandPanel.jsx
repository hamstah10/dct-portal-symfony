import { Gauge, Lightning, ShieldCheck } from '@phosphor-icons/react';

export const AuthBrandPanel = ({ portalName }) => {
  return (
    <div className="relative hidden overflow-hidden bg-[hsl(36,2%,10%)] px-10 py-12 lg:flex lg:flex-col lg:justify-center xl:px-16">
      <div
        className="absolute inset-x-0 top-0 h-2.5 opacity-30"
        style={{
          backgroundImage: 'repeating-conic-gradient(#000 0% 25%, #fff 0% 50%)',
          backgroundSize: '18px 18px',
        }}
      />
      <div className="pointer-events-none absolute -top-20 -right-24 h-[420px] w-[420px] rounded-full bg-primary/20 blur-3xl" />
      <div className="pointer-events-none absolute -left-16 bottom-16 h-2 w-52 -skew-x-[30deg] bg-primary/50" />
      <div className="pointer-events-none absolute -left-24 bottom-[88px] h-2 w-40 -skew-x-[30deg] bg-white/20" />

      <div className="relative font-mono text-xs text-primary">/ Händler-Portal</div>
      <h2 className="relative mt-3 max-w-lg font-heading text-4xl font-black leading-[1.05] text-white xl:text-[44px]">
        Tuning Files.
        <br />
        Aufträge.
        <br />
        <span className="text-primary">Vollgas.</span>
      </h2>

      <div className="relative mt-9 max-w-md space-y-3.5">
        <div className="flex items-center gap-3 text-sm text-white/70">
          <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm border border-primary/40 bg-primary/15">
            <Lightning className="h-4 w-4 text-primary" weight="fill" />
          </span>
          Individuelle Files in 30–60 Minuten
        </div>
        <div className="flex items-center gap-3 text-sm text-white/70">
          <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm border border-primary/40 bg-primary/15">
            <Gauge className="h-4 w-4 text-primary" weight="fill" />
          </span>
          Konfigurator, Preisliste &amp; DTC-Datenbank
        </div>
        <div className="flex items-center gap-3 text-sm text-white/70">
          <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm border border-primary/40 bg-primary/15">
            <ShieldCheck className="h-4 w-4 text-primary" weight="fill" />
          </span>
          Persönlicher Support für Ihren Betrieb
        </div>
      </div>

      <div className="relative mt-10 font-mono text-[11px] text-white/35">
        {portalName} · Händlerbereich für Upload, Download und Auftragsverwaltung.
      </div>
    </div>
  );
};
