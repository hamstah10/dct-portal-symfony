<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ToolManufacturer;
use App\Entity\WinolsConfigurationRule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WinolsConfigurationRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Bezeichnung',
            ])
            ->add('toolManufacturers', EntityType::class, [
                'class' => ToolManufacturer::class,
                'choice_label' => 'name',
                'label' => 'Tool-Hersteller',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'by_reference' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('allowedModes', ChoiceType::class, [
                'label' => 'Verwendbar fuer',
                'choices' => [
                    'Slave' => WinolsConfigurationRule::MODE_SLAVE,
                    'Master' => WinolsConfigurationRule::MODE_MASTER,
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('allowedExtensions', TextareaType::class, [
                'label' => 'Erlaubte Dateiendungen',
                'required' => false,
                'attr' => ['rows' => 3],
                'help' => 'Eine Endung pro Zeile oder kommasepariert, z. B. bin, ori, mod.',
            ])
            ->add('active', ChoiceType::class, [
                'label' => 'Aktiv',
                'choices' => [
                    'Ja' => true,
                    'Nein' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WinolsConfigurationRule::class,
        ]);
    }
}
