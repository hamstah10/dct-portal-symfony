<?php

namespace App\Portals\Chiptuningfile\Form;

use App\Entity\Kunde;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vorname', TextType::class, ['label' => 'Vorname'])
            ->add('nachname', TextType::class, ['label' => 'Nachname'])
            ->add('email', EmailType::class, ['label' => 'E-Mail'])
            ->add('telefon', TextType::class, ['label' => 'Telefon', 'required' => false])
            ->add('firmenname', TextType::class, ['label' => 'Firma', 'required' => false])
            ->add('strasse', TextType::class, ['label' => 'Strasse', 'required' => false])
            ->add('hausnummer', TextType::class, ['label' => 'Hausnummer', 'required' => false])
            ->add('plz', TextType::class, ['label' => 'PLZ', 'required' => false])
            ->add('stadt', TextType::class, ['label' => 'Stadt', 'required' => false])
            ->add('land', TextType::class, ['label' => 'Land', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Kunde::class,
        ]);
    }
}
