<?php

namespace App\Form;

use App\Entity\Kunde;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
final class KundeAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Kunde::class,
            'placeholder' => 'Kunde suchen...',
            'searchable_fields' => ['vorname', 'nachname', 'email', 'firmenname', 'kundennummer'],
            'choice_label' => static function (Kunde $kunde): string {
                $kundennummer = trim((string) $kunde->getKundennummer());
                if ('' === $kundennummer) {
                    $kundennummer = sprintf('Kunde-%d', (int) ($kunde->getId() ?? 0));
                }

                $name = trim(sprintf('%s %s', (string) $kunde->getVorname(), (string) $kunde->getNachname()));
                if ('' === $name) {
                    $name = (string) ($kunde->getEmail() ?? 'Unbekannt');
                }

                $firma = $kunde->getFirma()?->getName() ?? $kunde->getFirmenname() ?? 'Keine Firma';

                return sprintf('%s | %s | %s', $kundennummer, $name, $firma);
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
