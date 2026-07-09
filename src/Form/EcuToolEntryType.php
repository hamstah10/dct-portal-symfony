<?php

namespace App\Form;

use App\Entity\EcuToolEntry;
use App\Entity\ToolManufacturer;
use App\Entity\ToolMethode;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcuToolEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('toolManufacturer', EntityType::class, [
                'class' => ToolManufacturer::class,
                'choice_label' => 'name',
                'label' => 'Tool-Hersteller',
                'required' => false,
                'placeholder' => '– Hersteller wählen –',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('toolMethods', EntityType::class, [
                'class' => ToolMethode::class,
                'choice_label' => 'name',
                'label' => 'Methoden',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => ['class' => 'form-select', 'size' => 5],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EcuToolEntry::class,
        ]);
    }
}
