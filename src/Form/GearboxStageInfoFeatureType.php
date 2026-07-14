<?php

namespace App\Form;

use App\Entity\GearboxStageInfoFeature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GearboxStageInfoFeatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Feature',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('included', CheckboxType::class, [
                'label' => 'Enthalten',
                'required' => false,
            ])
            ->add('sorting', IntegerType::class, [
                'label' => 'Sortierung',
                'required' => false,
                'empty_data' => '0',
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GearboxStageInfoFeature::class,
        ]);
    }
}
