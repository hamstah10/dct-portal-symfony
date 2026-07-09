import { FileArrowUp, ClockCounterClockwise, CheckCircle } from '@phosphor-icons/react';
import { DashboardLayout } from '../components/layout/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Badge } from '../components/ui/badge';
import { Button } from '../components/ui/button';

const uploads = [
  { id: 'UP-2026-0412', file: 'bmw_f30_330d_original.bin', customer: 'BMW 330d F30', status: 'In Bearbeitung', credits: 85 },
  { id: 'UP-2026-0407', file: 'audi_a4_stage1.frf', customer: 'Audi A4 2.0 TDI', status: 'Abgeschlossen', credits: 65 },
  { id: 'UP-2026-0398', file: 'golf7gti_backup.bin', customer: 'VW Golf 7 GTI', status: 'Wartet auf Rueckfrage', credits: 75 },
];

export default function Uploads() {
  return (
    <DashboardLayout>
      <div className="space-y-6">
        <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
          <div>
            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-primary">Portal Modul</p>
            <h1 className="mt-2 font-heading text-3xl font-black tracking-tight">Uploads</h1>
            <p className="mt-3 max-w-2xl text-muted-foreground">
              Statische React-Ansicht fuer die Upload-Uebersicht. Liste, Filter und Aktionen werden spaeter an die bestehenden Symfony-Daten angeschlossen.
            </p>
          </div>
          <Button className="btn-gradient text-white" type="button" data-testid="uploads-new-order">
            <FileArrowUp className="mr-2 h-4 w-4" weight="bold" />
            Neuer Upload
          </Button>
        </div>

        <div className="grid gap-4 md:grid-cols-3">
          <Card className="bg-card border-border"><CardHeader className="border-b border-border pb-4"><CardTitle className="flex items-center gap-2 text-lg"><FileArrowUp className="h-5 w-5 text-primary" weight="fill" />Eingereicht</CardTitle></CardHeader><CardContent className="pt-6 text-3xl font-heading font-bold">128</CardContent></Card>
          <Card className="bg-card border-border"><CardHeader className="border-b border-border pb-4"><CardTitle className="flex items-center gap-2 text-lg"><ClockCounterClockwise className="h-5 w-5 text-primary" weight="fill" />Offen</CardTitle></CardHeader><CardContent className="pt-6 text-3xl font-heading font-bold">9</CardContent></Card>
          <Card className="bg-card border-border"><CardHeader className="border-b border-border pb-4"><CardTitle className="flex items-center gap-2 text-lg"><CheckCircle className="h-5 w-5 text-primary" weight="fill" />Ausgeliefert</CardTitle></CardHeader><CardContent className="pt-6 text-3xl font-heading font-bold">119</CardContent></Card>
        </div>

        <Card className="bg-card border-border">
          <CardHeader className="border-b border-border pb-4"><CardTitle className="text-lg">Letzte Uploads</CardTitle></CardHeader>
          <CardContent className="p-0">
            <div className="overflow-x-auto">
              <table className="min-w-full text-left text-sm">
                <thead className="border-b border-border bg-secondary/50 text-muted-foreground">
                  <tr><th className="px-6 py-3 font-medium">Referenz</th><th className="px-6 py-3 font-medium">Datei</th><th className="px-6 py-3 font-medium">Fahrzeug</th><th className="px-6 py-3 font-medium">Status</th><th className="px-6 py-3 font-medium">Credits</th></tr>
                </thead>
                <tbody>
                  {uploads.map((upload) => (
                    <tr key={upload.id} className="border-b border-border/70">
                      <td className="px-6 py-4 font-mono text-xs text-primary">{upload.id}</td>
                      <td className="px-6 py-4 text-foreground">{upload.file}</td>
                      <td className="px-6 py-4 text-muted-foreground">{upload.customer}</td>
                      <td className="px-6 py-4"><Badge className="border border-primary/30 bg-primary/10 text-primary">{upload.status}</Badge></td>
                      <td className="px-6 py-4 font-semibold text-foreground">{upload.credits}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
