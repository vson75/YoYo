<?php

namespace App\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UpdateAdvancementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image1', FileType::class, [
                'label' => 'form.UpdateAdvancement.imageLabel',
                'help' => 'form.UpdateAdvancement.image1Help',
                'required' => true ,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('image2', FileType::class, [
                'label' => 'form.UpdateAdvancement.imageLabel',
                'required' => false ,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('image3', FileType::class, [
                'label' => 'form.UpdateAdvancement.imageLabel',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('image4', FileType::class, [
                'label' => 'form.UpdateAdvancement.imageLabel',
                'required' => false ,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('image5', FileType::class, [
                'label' => 'form.UpdateAdvancement.imageLabel',
                'required' => false ,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('objectEmail', TextType::class, [
                'required' => true,
                'label' => 'form.UpdateAdvancement.subject',
                'attr' => [
                    'placeholder' => 'form.UpdateAdvancement.subjectPlaceholder'
                ],
            ])
            ->add('email',CKEditorType::class, [
                'label' => 'form.UpdateAdvancement.email',
                'required' => true , 
                'attr' => [
                    'placeholder' => 'form.UpdateAdvancement.emailPlaceholder'
                ],
                'config' => [
                    'uiColor' => '#ffffff',
                    'toolbar' => 'my_toolbar_1',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
