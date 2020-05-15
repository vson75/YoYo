<?php


namespace App\Form;


use App\Entity\Post;

use App\Entity\Tag;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class, [
                'help' => 'bạn điền tiêu đề vào đây',
                'required' => true
            ])
            ->add('content', CKEditorType::class, array(
                'config' => array(
                    'uiColor' => '#ffffff',
                    'toolbar' => 'my_toolbar_1',
                ),
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class
        ]);
    }

}