<?php


namespace App\Form;


use App\Entity\Post;

use App\Entity\Tag;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class PostFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class, [
                'required' => true,
                'label'=> 'form.PostCreation.title',
                'attr' => [
                    'placeholder' => 'form.PostCreation.titlePlaceholder'
                ]
            ])
            ->add('imageFile',FileType::class, [
                'label' => 'form.PostCreation.imageFilePost' ,
                'mapped'=> false,
                'required'=>false,
                'attr' => [
                    'placeholder' => 'form.PostCreation.imageFilePostPlaceholder'
                ],
                'constraints'=>[
                    new Image([
                        'maxSize' => '5M'
                    ])
                ]
            ])
            ->add('content', CKEditorType::class, array(
                'label' => 'form.PostCreation.content',
                'config' => array(
                    'uiColor' => '#ffffff',
                    'toolbar' => 'my_toolbar_1',
                ),
            ))
            ->add('finishAt', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'form.PostCreation.finishAt',
                'help' => 'form.PostCreation.finishAtHelp',
                'attr' => [
                    'placeholder' => 'form.PostCreation.finishAtPlaceholder'
                ]
            ])
            ->add('targetAmount', MoneyType::class, [
                'required' => true,
                'label' => 'form.PostCreation.targetAmount',
                    'attr' => [
                        'placeholder' => 'form.PostCreation.targetAmountPlaceholder'
                    ]
            ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class
        ]);
    }

}