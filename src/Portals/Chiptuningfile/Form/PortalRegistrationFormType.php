<?php

namespace App\Portals\Chiptuningfile\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PortalRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vorname', TextType::class, [
                'label' => 'Vorname',
                'mapped' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('nachname', TextType::class, [
                'label' => 'Nachname',
                'mapped' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('firmenname', TextType::class, [
                'label' => 'Firma',
                'mapped' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-Mail',
                'mapped' => false,
                'constraints' => [new NotBlank(), new Email()],
            ])
            ->add('telefon', TextType::class, [
                'label' => 'Telefon',
                'mapped' => false,
                'required' => false,
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'mapped' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('website', TextType::class, [
                'label' => 'Web-Adresse',
                'mapped' => false,
                'required' => false,
            ])
            ->add('ustId', TextType::class, [
                'label' => 'USt-Id',
                'mapped' => false,
                'required' => false,
            ])
            ->add('land', TextType::class, [
                'label' => 'Land',
                'mapped' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('firmenbeschreibung', TextType::class, [
                'label' => 'Firmenbeschreibung',
                'mapped' => false,
                'required' => false,
            ])
            ->add('newsletter', CheckboxType::class, [
                'label' => 'Ich moechte Updates und relevante Informationen erhalten',
                'mapped' => false,
                'required' => false,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['label' => 'Passwort'],
                'second_options' => ['label' => 'Passwort wiederholen'],
                'invalid_message' => 'Die Passwoerter stimmen nicht ueberein.',
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 8),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
