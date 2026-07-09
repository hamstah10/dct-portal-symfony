<?php

declare(strict_types=1);

namespace App\Portals\Chiptuningfile\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\UX\Dropzone\Form\DropzoneType;

final class OrderDummyUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('upload', DropzoneType::class, [
                'label' => 'Originaldatei',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Datei hier ablegen oder klicken',
                ],
                'constraints' => [
                    new File(maxSize: '64M'),
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Upload-Notiz',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Optionaler Dummy-Hinweis zum Upload',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
