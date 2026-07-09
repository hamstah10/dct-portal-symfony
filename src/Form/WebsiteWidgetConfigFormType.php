<?php

namespace App\Form;

use App\Entity\WebsiteWidgetConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

final class WebsiteWidgetConfigFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $colorConstraint = new Regex(
            pattern: '/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/',
            message: 'Bitte eine gueltige Hex-Farbe angeben, z. B. #d53b2a.',
        );

        $builder
            ->add('active', CheckboxType::class, [
                'label' => 'Widget aktiviert',
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'label' => 'Titel',
                'constraints' => [new NotBlank()],
            ])
            ->add('subtitle', TextType::class, [
                'label' => 'Untertitel',
                'required' => false,
            ])
            ->add('buttonText', TextType::class, [
                'label' => 'Button-Text',
                'constraints' => [new NotBlank()],
            ])
            ->add('successTitle', TextType::class, [
                'label' => 'Erfolgs-Titel',
                'constraints' => [new NotBlank()],
            ])
            ->add('successMessage', TextareaType::class, [
                'label' => 'Erfolgs-Nachricht',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('recipientEmailMode', ChoiceType::class, [
                'label' => 'Anfrage-Empfaenger',
                'choices' => WebsiteWidgetConfig::getRecipientEmailModeChoices(),
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('customRecipientEmail', EmailType::class, [
                'label' => 'Eigene Empfaenger-E-Mail',
                'required' => false,
                'constraints' => [new Email()],
            ])
            ->add('templateVariant', ChoiceType::class, [
                'label' => 'Layout',
                'choices' => WebsiteWidgetConfig::getTemplateChoices(),
            ])
            ->add('iframeHeight', IntegerType::class, [
                'label' => 'iFrame-Hoehe',
                'constraints' => [new NotBlank(), new GreaterThanOrEqual(320)],
            ])
            ->add('primaryColor', ColorType::class, [
                'label' => 'Primaerfarbe',
                'constraints' => [new NotBlank(), $colorConstraint],
            ])
            ->add('backgroundColor', ColorType::class, [
                'label' => 'Hintergrundfarbe',
                'constraints' => [new NotBlank(), $colorConstraint],
            ])
            ->add('textColor', ColorType::class, [
                'label' => 'Textfarbe',
                'constraints' => [new NotBlank(), $colorConstraint],
            ])
            ->add('selectBackgroundColor', ColorType::class, [
                'label' => 'Selectbox Hintergrundfarbe',
                'constraints' => [new NotBlank(), $colorConstraint],
            ])
            ->add('selectOpenBackgroundColor', ColorType::class, [
                'label' => 'Selectbox offen/Hover Hintergrundfarbe',
                'constraints' => [new NotBlank(), $colorConstraint],
            ])
            ->add('customCss', TextareaType::class, [
                'label' => 'Custom CSS',
                'required' => false,
                'attr' => ['rows' => 5],
            ])
            ->add('showPhone', CheckboxType::class, ['label' => 'Telefon anzeigen', 'required' => false])
            ->add('showEmail', CheckboxType::class, ['label' => 'E-Mail anzeigen', 'required' => false])
            ->add('showFinVin', CheckboxType::class, ['label' => 'FIN/VIN anzeigen', 'required' => false])
            ->add('showLicensePlate', CheckboxType::class, ['label' => 'Kennzeichen anzeigen', 'required' => false])
            ->add('showMileage', CheckboxType::class, ['label' => 'Kilometerstand anzeigen', 'required' => false])
            ->add('phoneRequired', CheckboxType::class, ['label' => 'Telefon Pflichtfeld', 'required' => false])
            ->add('emailRequired', CheckboxType::class, ['label' => 'E-Mail Pflichtfeld', 'required' => false])
            ->add('privacyRequired', CheckboxType::class, ['label' => 'Datenschutz-Checkbox erforderlich', 'required' => false])
            ->add('privacyUrl', TextType::class, [
                'label' => 'Datenschutz-URL',
                'required' => false,
            ])
            ->add('privacyTextHtml', TextareaType::class, [
                'label' => 'Datenschutz-Text',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('redirectAfterSubmit', TextType::class, [
                'label' => 'Weiterleitung nach Absenden',
                'required' => false,
            ])
            ->add('allowedDomains', TextareaType::class, [
                'label' => 'Erlaubte Domains',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('sendTrackingEvents', CheckboxType::class, [
                'label' => 'Tracking-Events senden',
                'required' => false,
            ])
            ->add('typeIconColor', ChoiceType::class, [
                'label' => 'Fahrzeugtyp-Icon Farbe',
                'choices' => ['Weiß' => 'white', 'Schwarz' => 'black'],
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WebsiteWidgetConfig::class,
        ]);
    }
}
