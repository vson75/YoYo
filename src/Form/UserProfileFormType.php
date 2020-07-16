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
                'label' => 'Ảnh đại diện',
                'mapped'=> false,
                'required'=>false,
                'constraints'=>[
                    new Image([
                        'maxSize' => '2M'
                    ])
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Họ'
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Tên'
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
