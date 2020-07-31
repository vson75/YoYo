<?php

namespace App\Form;

use App\Entity\PostTranslation;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', CKEditorType::class, array(
                'config' => [
                    'uiColor' => '#ffffff',
                    'toolbar' => 'my_toolbar_1',
                    ],)
            )
            ->add('lang', ChoiceType::class,[
                'choices'  => [
                    'EN' => 'en',
                    'FR' => 'fr'
            ],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PostTranslation::class,
        ]);
    }
}
