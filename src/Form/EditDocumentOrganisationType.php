<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EditDocumentOrganisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Document0', FileType::class, [
                'required' => true,
                'help' => 'form.documentOrganisation.help',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('Document1', FileType::class, [
                'required'=>false,
                'help' => 'form.documentOrganisation.help',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('Document2', FileType::class, [
                'required'=>false,
                'help' => 'form.documentOrganisation.help',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('Document3', FileType::class, [
                'required'=>false,
                'help' => 'form.documentOrganisation.help',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('Document4', FileType::class, [
                'required'=>false,
                'help' => 'form.documentOrganisation.help',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('Document5', FileType::class, [
                'required'=>false,
                'help' => 'form.documentOrganisation.help',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('Document6', FileType::class, [
                'required'=>false,
                'help' => 'form.documentOrganisation.help',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('Document7', FileType::class, [
                'required'=>false,
                'help' => 'form.documentOrganisation.help',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('Document8', FileType::class, [
                'required'=>false,
                'help' => 'form.documentOrganisation.help',
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
            ->add('Document9', FileType::class, [
                'required'=>false,
                'help' => 'form.documentOrganisation.help',
                'constraints'=>[
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ]
                    ])
                ]
            ])
            ->add('DocType0', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                    'form.documentOrganisation.CertifOrganisation' => 'Certificate_organisation',
                    'form.documentOrganisation.BankAccount' => 'Bank_account_information',
                    'form.documentOrganisation.Award' => 'Awards_justification',
                ],
            ])
            ->add('DocType1', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'form.documentOrganisation.CertifOrganisation' => 'Certificate_organisation',
                    'form.documentOrganisation.BankAccount' => 'Bank_account_information',
                    'form.documentOrganisation.Award' => 'Awards_justification',
                ],
            ])
            ->add('DocType2', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'form.documentOrganisation.CertifOrganisation' => 'Certificate_organisation',
                    'form.documentOrganisation.BankAccount' => 'Bank_account_information',
                    'form.documentOrganisation.Award' => 'Awards_justification',
                ],
            ])
            ->add('DocType3', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'form.documentOrganisation.CertifOrganisation' => 'Certificate_organisation',
                    'form.documentOrganisation.BankAccount' => 'Bank_account_information',
                    'form.documentOrganisation.Award' => 'Awards_justification',
                ],
            ])
            ->add('DocType4', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'form.documentOrganisation.CertifOrganisation' => 'Certificate_organisation',
                    'form.documentOrganisation.BankAccount' => 'Bank_account_information',
                    'form.documentOrganisation.Award' => 'Awards_justification',
                ],
            ])
            ->add('DocType5', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'form.documentOrganisation.CertifOrganisation' => 'Certificate_organisation',
                    'form.documentOrganisation.BankAccount' => 'Bank_account_information',
                    'form.documentOrganisation.Award' => 'Awards_justification',
                ],
            ])
            ->add('DocType6', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'form.documentOrganisation.CertifOrganisation' => 'Certificate_organisation',
                    'form.documentOrganisation.BankAccount' => 'Bank_account_information',
                    'form.documentOrganisation.Award' => 'Awards_justification',
                ],
            ])
            ->add('DocType7', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'form.documentOrganisation.CertifOrganisation' => 'Certificate_organisation',
                    'form.documentOrganisation.BankAccount' => 'Bank_account_information',
                    'form.documentOrganisation.Award' => 'Awards_justification',
                ],
            ])
            ->add('DocType8', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'form.documentOrganisation.CertifOrganisation' => 'Certificate_organisation',
                    'form.documentOrganisation.BankAccount' => 'Bank_account_information',
                    'form.documentOrganisation.Award' => 'Awards_justification',
                ],
            ])
            ->add('DocType9', ChoiceType::class, [
                'required' => false,
                'choices'  => [
                    'form.documentOrganisation.CertifOrganisation' => 'Certificate_organisation',
                    'form.documentOrganisation.BankAccount' => 'Bank_account_information',
                    'form.documentOrganisation.Award' => 'Awards_justification',
                ],
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
