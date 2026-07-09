import { Link } from 'react-router-dom';
import { EnvelopeSimpleOpen } from '@phosphor-icons/react';
import { PublicLayout } from '../components/layout/PublicLayout';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { portalRoutes } from '../lib/portalRoutes';

export default function PasswordResetRequest() {
  return (
    <PublicLayout
      eyebrow="Passwort Reset"
      title="Die Reset-Strecke ist bereits als React-Ansicht vorbereitet."
      description="Im naechsten Schritt wird diese Ansicht an den bestehenden Symfony-Reset-Prozess und die Mail-Logik angebunden."
      footer="Static preview fuer den Reset-Request."
    >
      <div className="space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex h-11 w-11 items-center justify-center rounded-sm bg-primary/15 text-primary">
            <EnvelopeSimpleOpen className="h-6 w-6" weight="fill" />
          </div>
          <div>
            <h2 className="font-heading text-2xl font-bold">Passwort vergessen</h2>
            <p className="text-sm text-muted-foreground">Wir schicken spaeter hierueber den echten Symfony-Reset-Link.</p>
          </div>
        </div>

        <div className="space-y-2">
          <Label htmlFor="reset-mail">E-Mail-Adresse</Label>
          <Input id="reset-mail" type="email" className="bg-secondary border-border" data-testid="reset-request-email" />
        </div>

        <Button className="btn-gradient w-full text-white" type="button" data-testid="reset-request-submit">
          Reset-Link anfordern
        </Button>

        <div className="text-sm text-muted-foreground">
          <Link to={portalRoutes.login} className="text-foreground hover:text-primary">Zurueck zum Login</Link>
        </div>
      </div>
    </PublicLayout>
  );
}
