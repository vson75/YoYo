<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

class ConfirmTransferFundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('proofOfTransaction', FileType::class,[
                'required' => true,
                'label' => 'form.ConfirmTransferFund.proofOfTransaction',
                'help' => 'form.ConfirmTransferFund.proofOfTransactionHelp',
                'attr' => [
                    'placeholder' => 'form.ConfirmTransferFund.proofOfTransactionPlaceholder'
                ],
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
