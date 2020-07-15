<?php

namespace App\Form;

use App\Entity\RequestOrganisationInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('OrganisationName')
            ->add('Address')
            ->add('ZipCode')
            ->add('City')
            ->add('Country')
            ->add('PhoneNumber')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RequestOrganisationInfo::class,
        ]);
    }
}
