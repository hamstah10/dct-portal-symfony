<?php

namespace App\Form;

use App\Entity\AboAuswahlPreisstaffel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AboAuswahlPreisstaffelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('laufzeitMonate', IntegerType::class, [
                'label' => 'Laufzeit (Monate)',
                'attr' => ['min' => 1],
            ])
            ->add('preisMonatlich', NumberType::class, [
                'label' => 'Monatliche Zahlung (EUR/Monat)',
                'scale' => 2,
                'required' => false,
                'html5' => true,
            ])
            ->add('preisJaehrlich', NumberType::class, [
                'label' => 'Jaehrliche Zahlung (EUR/Monat)',
                'scale' => 2,
                'required' => false,
                'html5' => true,
            ])
            ->add('preisEinrichtung', NumberType::class, [
                'label' => 'Preis Einrichtung (einmalig, EUR)',
                'scale' => 2,
                'required' => false,
                'html5' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AboAuswahlPreisstaffel::class,
        ]);
    }
}
