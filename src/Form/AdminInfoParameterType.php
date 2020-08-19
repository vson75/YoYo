<?php

namespace App\Form;

use App\Entity\AdminParameter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminInfoParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, [
                'required' => false
            ])
            ->add('address',TextType::class, [
                'required' => false
            ])
            ->add('codePostal',TextType::class, [
                'required' => false
            ])
            ->add('City',TextType::class, [
                'required' => false
            ])
            ->add('Country', CountryType::class, [
                'required' => false
            ])
            ->add('CompanyName', TextType::class, [
                'required' => false
            ])
            ->add('AppName', TextType::class, [
                'required' => false
            ])
            ->add('capitalSocial', MoneyType::class, [
                'required' => false
            ])
            ->add('sirenNumber', TextType::class, [
                'required' => false
            ])
            ->add('webHost', TextType::class, [
                'required' => false
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdminParameter::class,
        ]);
    }
}
