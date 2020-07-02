<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

class CreateOrganisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('CertificateOrganisation', FileType::class, [
                'required'=>false,
                'help' => 'dạng File PDF hoặc hình ảnh',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('BankAccountInformation', FileType::class, [
                'required'=>false,
                'help' => 'dạng File PDF hoặc hình ảnh',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('Awards', FileType::class, [
                'required'=>false,
                'help' => 'dạng File PDF hoặc hình ảnh',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('PhoneNumber', TelType::class, [
                'required'=>false
            ])
            ->add('Address', TextType::class, [
                'required'=>false
            ])
            ->add('OrganisationName', TextType::class, [
                'required'=>false
            ])
            ->add('Country', CountryType::class, [
                'required'=>false
            ])
            ->add('ZipCode', TextType::class, [
                'required'=>false
            ])
            ->add('City', TextType::class, [
                'required'=>false
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
