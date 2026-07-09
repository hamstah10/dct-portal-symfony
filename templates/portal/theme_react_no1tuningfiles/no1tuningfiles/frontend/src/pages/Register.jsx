import { Link } from 'react-router-dom';
import { UserPlus } from '@phosphor-icons/react';
import { PublicLayout } from '../components/layout/PublicLayout';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Checkbox } from '../components/ui/checkbox';
import { portalRoutes } from '../lib/portalRoutes';

export default function Register() {
  const bootstrap = window.portalReactBootstrap?.register ?? {};
  const formName = typeof bootstrap.formName === 'string' && bootstrap.formName.trim() !== ''
    ? bootstrap.formName
    : 'portal_registration_form';
  const values = bootstrap.values && typeof bootstrap.values === 'object' ? bootstrap.values : {};
  const errors = Array.isArray(bootstrap.errors) ? bootstrap.errors : [];

  const fieldName = (name) => `${formName}[${name}]`;

  return (
    <PublicLayout
      eyebrow="Registrierung"
      title="Firmenkunden-Registrierung"
      description="Erstelle dein Kundenkonto fuer das Portal. Die Daten werden direkt an Symfony uebermittelt und serverseitig validiert."
      footer="B2B Registrierung fuer das Kundenportal."
    >
      <form method="post" action={portalRoutes.register} className="space-y-6">
        <input type="hidden" name={fieldName('_token')} value={bootstrap.csrfToken || ''} />

        <div className="flex items-center gap-3">
          <div className="flex h-11 w-11 items-center justify-center rounded-sm bg-primary/15 text-primary">
            <UserPlus className="h-6 w-6" weight="fill" />
          </div>
          <div>
            <h2 className="font-heading text-2xl font-bold">Konto erstellen</h2>
            <p className="text-sm text-muted-foreground">Firmenkunden werden direkt im Portal angelegt.</p>
          </div>
        </div>

        {errors.length > 0 ? (
          <div className="rounded-sm border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200" data-testid="register-errors">
            <ul className="space-y-1">
              {errors.map((error, index) => (
                <li key={`${error}-${index}`}>{error}</li>
              ))}
            </ul>
          </div>
        ) : null}

        <div className="grid gap-4 md:grid-cols-2">
          <div className="space-y-2">
            <Label htmlFor="vorname">Vorname</Label>
            <Input id="vorname" name={fieldName('vorname')} defaultValue={values.vorname || ''} className="bg-secondary border-border" data-testid="register-firstname" required />
          </div>
          <div className="space-y-2">
            <Label htmlFor="nachname">Nachname</Label>
            <Input id="nachname" name={fieldName('nachname')} defaultValue={values.nachname || ''} className="bg-secondary border-border" data-testid="register-lastname" required />
          </div>
          <div className="space-y-2 md:col-span-2">
            <Label htmlFor="firma">Firma</Label>
            <Input id="firma" name={fieldName('firmenname')} defaultValue={values.firmenname || ''} className="bg-secondary border-border" data-testid="register-company" placeholder="Muster GmbH" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="mail">E-Mail</Label>
            <Input id="mail" name={fieldName('email')} type="email" defaultValue={values.email || ''} className="bg-secondary border-border" data-testid="register-email" autoComplete="email" required />
          </div>
          <div className="space-y-2">
            <Label htmlFor="telefon">Telefon</Label>
            <Input id="telefon" name={fieldName('telefon')} defaultValue={values.telefon || ''} className="bg-secondary border-border" data-testid="register-phone" autoComplete="tel" />
          </div>
          <div className="space-y-2 md:col-span-2">
            <Label htmlFor="adresse">Adresse</Label>
            <Input id="adresse" name={fieldName('adresse')} defaultValue={values.adresse || ''} className="bg-secondary border-border" data-testid="register-address" placeholder="Strasse, Hausnummer, PLZ Ort" required />
          </div>
          <div className="space-y-2">
            <Label htmlFor="website">Web-Adresse</Label>
            <Input id="website" name={fieldName('website')} defaultValue={values.website || ''} className="bg-secondary border-border" data-testid="register-website" placeholder="https://..." autoComplete="url" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="ust-id">USt-Id</Label>
            <Input id="ust-id" name={fieldName('ustId')} defaultValue={values.ustId || ''} className="bg-secondary border-border" data-testid="register-ust-id" />
          </div>
          <div className="space-y-2 md:col-span-2">
            <Label htmlFor="land">Land</Label>
            <Input id="land" name={fieldName('land')} defaultValue={values.land || 'Deutschland'} className="bg-secondary border-border" data-testid="register-country" required />
          </div>
          <div className="space-y-2 md:col-span-2">
            <Label htmlFor="firmenbeschreibung">Kurzbeschreibung</Label>
            <Input id="firmenbeschreibung" name={fieldName('firmenbeschreibung')} defaultValue={values.firmenbeschreibung || ''} className="bg-secondary border-border" data-testid="register-company-description" placeholder="Was macht eure Firma?" />
          </div>
          <div className="space-y-2">
            <Label htmlFor="kennwort">Passwort</Label>
            <Input id="kennwort" name={fieldName('plainPassword[first]')} type="password" defaultValue={values.plainPasswordFirst || ''} className="bg-secondary border-border" data-testid="register-password" autoComplete="new-password" minLength={8} required />
          </div>
          <div className="space-y-2">
            <Label htmlFor="kennwort2">Passwort wiederholen</Label>
            <Input id="kennwort2" name={fieldName('plainPassword[second]')} type="password" defaultValue={values.plainPasswordSecond || ''} className="bg-secondary border-border" data-testid="register-password-repeat" autoComplete="new-password" minLength={8} required />
          </div>
          <div className="md:col-span-2 flex items-start gap-3 rounded-sm border border-border bg-secondary/20 px-3 py-2.5">
            <Checkbox id="newsletter" name={fieldName('newsletter')} value="1" defaultChecked={Boolean(values.newsletter)} />
            <Label htmlFor="newsletter" className="cursor-pointer text-sm text-muted-foreground">
              Ich moechte Updates und relevante Informationen erhalten.
            </Label>
          </div>
        </div>

        <Button className="btn-gradient w-full text-white" type="submit" data-testid="register-submit">
          Registrierung starten
        </Button>

        <div className="text-sm text-muted-foreground">
          Bereits registriert? <Link to={portalRoutes.login} className="text-foreground hover:text-primary">Zum Login</Link>
        </div>
      </form>
    </PublicLayout>
  );
}
