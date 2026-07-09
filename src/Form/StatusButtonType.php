<?php

namespace App\Form;

use App\Entity\Auftrag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class StatusButtonType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['choices_as_buttons'] = true;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => Auftrag::getStatusChoices(),
            'expanded' => true,
            'multiple' => false,
            'attr' => [
                'class' => 'status-buttons',
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
