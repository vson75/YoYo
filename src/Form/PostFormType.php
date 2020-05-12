<?php


namespace App\Form;


use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PostFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('content', CKEditorType::class, array(
                'config' => array(
                    'uiColor' => '#ffffff',
                    'toolbar' => 'my_toolbar_1',
                    'required' => true,
                    //...
                ),
            ));
    }

}