import { Link } from 'react-router-dom';
import { Password } from '@phosphor-icons/react';
import { PublicLayout } from '../components/layout/PublicLayout';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { portalRoutes } from '../lib/portalRoutes';

export default function PasswordResetConfirm() {
  return (
    <PublicLayout
      eyebrow="Neues Passwort"
      title="Die Passwort-Neuvergabe kann bereits im finalen Frontend gezeigt werden."
      description="Token-Pruefung, Speicherung und Erfolgsmeldungen bleiben spaeter in Symfony und werden an diesen Screen angeschlossen."
      footer="Static preview fuer die Reset-Bestaetigung."
    >
      <div className="space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex h-11 w-11 items-center justify-center rounded-sm bg-primary/15 text-primary">
            <Password className="h-6 w-6" weight="fill" />
          </div>
          <div>
            <h2 className="font-heading text-2xl font-bold">Neues Passwort setzen</h2>
            <p className="text-sm text-muted-foreground">Der Bildschirm ist statisch, der Portal-Ablauf folgt im naechsten Schritt.</p>
          </div>
        </div>

        <div className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="new-password">Neues Passwort</Label>
            <Input id="new-password" type="password" className="bg-secondary border-border" data-testid="reset-confirm-password" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="repeat-password">Passwort wiederholen</Label>
            <Input id="repeat-password" type="password" className="bg-secondary border-border" data-testid="reset-confirm-password-repeat" />
          </div>
        </div>

        <Button className="btn-gradient w-full text-white" type="button" data-testid="reset-confirm-submit">
          Passwort aktualisieren
        </Button>

        <div className="text-sm text-muted-foreground">
          <Link to={portalRoutes.login} className="text-foreground hover:text-primary">Zurueck zum Login</Link>
        </div>
      </div>
    </PublicLayout>
  );
}
