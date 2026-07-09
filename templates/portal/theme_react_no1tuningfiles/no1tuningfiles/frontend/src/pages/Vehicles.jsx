import { CarProfile, MagnifyingGlass, Engine } from '@phosphor-icons/react';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Input } from '../components/ui/input';
import { Button } from '../components/ui/button';

const vehicles = [
  { manufacturer: 'BMW', model: '330d', engine: '3.0d', power: '245 PS', ecu: 'EDC17CP45' },
  { manufacturer: 'Audi', model: 'A4 2.0 TDI', engine: '2.0 TDI', power: '190 PS', ecu: 'EDC17C64' },
  { manufacturer: 'Mercedes', model: 'C220d', engine: '2.0d', power: '194 PS', ecu: 'MD1CP001' },
  { manufacturer: 'Volkswagen', model: 'Golf 7 GTI', engine: '2.0 TSI', power: '245 PS', ecu: 'MG1CS011' },
];

export default function Vehicles() {
  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
          <div>
            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Portal Modul</p>
            <h1 className="mt-2 font-heading text-3xl font-black tracking-tight">Fahrzeugdatenbank</h1>
            <p className="mt-3 max-w-2xl text-muted-foreground">
              Statische React-Ansicht fuer Suche, Trefferliste und Datenkarten. Die bestehende Symfony-Suche kann spaeter direkt an diese Oberflaeche angeschlossen werden.
            </p>
          </div>
        </div>

        <Card className="bg-card border-border">
          <CardHeader className="border-b border-border pb-4"><CardTitle className="flex items-center gap-2 text-lg"><MagnifyingGlass className="h-5 w-5 text-primary" weight="fill" />Fahrzeugsuche</CardTitle></CardHeader>
          <CardContent className="space-y-4 pt-6">
            <div className="grid gap-4 md:grid-cols-[1.4fr_1fr_1fr_auto]">
              <Input className="bg-secondary border-border" placeholder="BMW, Audi, Mercedes ..." data-testid="vehicles-search-brand" />
              <Input className="bg-secondary border-border" placeholder="Modell" data-testid="vehicles-search-model" />
              <Input className="bg-secondary border-border" placeholder="Steuergeraet" data-testid="vehicles-search-ecu" />
              <Button className="btn-gradient text-white" type="button" data-testid="vehicles-search-submit">Suchen</Button>
            </div>
          </CardContent>
        </Card>

        <div className="grid gap-4 xl:grid-cols-2">
          {vehicles.map((vehicle) => (
            <Card key={`${vehicle.manufacturer}-${vehicle.model}`} className="bg-card border-border">
              <CardHeader className="border-b border-border pb-4"><CardTitle className="flex items-center gap-2 text-lg"><CarProfile className="h-5 w-5 text-primary" weight="fill" />{vehicle.manufacturer} {vehicle.model}</CardTitle></CardHeader>
              <CardContent className="grid gap-4 pt-6 md:grid-cols-2">
                <div><p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Motor</p><p className="mt-2 text-base font-semibold text-foreground">{vehicle.engine}</p></div>
                <div><p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">Leistung</p><p className="mt-2 text-base font-semibold text-foreground">{vehicle.power}</p></div>
                <div className="md:col-span-2"><p className="flex items-center gap-2 text-xs uppercase tracking-[0.2em] text-muted-foreground"><Engine className="h-4 w-4" weight="fill" />Steuergeraet</p><p className="mt-2 font-mono text-sm text-primary">{vehicle.ecu}</p></div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </DashboardLayout>
  );
}
