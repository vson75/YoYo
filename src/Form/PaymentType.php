<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label' => false,
                //set currency = false to hide the icon EUR in the form
                'currency' => false,
                'constraints' => [
                    new Constraints\NotBlank
                ]
            ])
            ->add('giveForSite', MoneyType::class, [
                'label' => false,
                //set currency = false to hide the icon EUR in the form
                'currency' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'csrf_protection' => false
        ]);
    }
}
