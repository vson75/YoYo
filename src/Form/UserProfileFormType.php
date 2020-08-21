<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class UserProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('iconFile',FileType::class, [
                'label' => 'form.UserProfile.iconFile',
                'help' => 'form.UserProfile.iconFileHelp',
                'mapped'=> false,
                'required'=>false,
                'constraints'=>[
                    new Image([
                        'maxSize' => '2M'
                    ])
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'form.userRegistration.firstname'
            ])
            ->add('lastname', TextType::class, [
                'label' => 'form.userRegistration.lastname'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
