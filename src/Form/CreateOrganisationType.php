<?php

namespace App\Form;


use App\Entity\RequestOrganisationDocument;
use App\Entity\RequestOrganisationInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
                'help' => 'form.CreateOrganisation.fileFormat',
                'label' => 'form.CreateOrganisation.CertificateOrganisation',
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
                'help' => 'form.CreateOrganisation.fileFormatJpgPdf',
                'label' => 'form.CreateOrganisation.BankAccountInformation',
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
            ->add('Awards0', FileType::class, [
                'required'=>false,
                'help' => 'form.CreateOrganisation.fileFormat',
                'label' => 'form.CreateOrganisation.award',
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
            ->add('Awards1', FileType::class, [
                'required'=>false,
                'help' => 'form.CreateOrganisation.fileFormat',
                'label' => 'form.CreateOrganisation.award',
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
            ->add('Awards2', FileType::class, [
                'required'=>false,
                'help' => 'form.CreateOrganisation.fileFormat',
                'label' => 'form.CreateOrganisation.award',
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
            ->add('Awards3', FileType::class, [
                'required'=>false,
                'help' => 'form.CreateOrganisation.fileFormat',
                'label' => 'form.CreateOrganisation.award',
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
            ->add('Awards4', FileType::class, [
                'required'=>false,
                'help' => 'form.CreateOrganisation.fileFormat',
                'label' => 'form.CreateOrganisation.award',
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
                'required'=>false,
                'label' => 'form.CreateOrganisation.PhoneNumber'
            ])
            ->add('Address', TextType::class, [
                'required'=>true,
                'label' => 'form.CreateOrganisation.Address'
            ])
            ->add('OrganisationName', TextType::class, [
                'required'=>true,
                'label' => 'form.CreateOrganisation.OrganisationName'
            ])
            ->add('Country', CountryType::class, [
                'required'=>true,
                'label' => 'form.CreateOrganisation.Country'
            ])
            ->add('ZipCode', TextType::class, [
                'required'=>false,
                'label' => 'form.CreateOrganisation.ZipCode'
            ])
            ->add('City', TextType::class, [
                'required'=>true,
                'label' => 'form.CreateOrganisation.City'
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
