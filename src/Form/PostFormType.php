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
                'label'=> false
            ])
            ->add('imageFile',FileType::class, [
                'mapped'=> false,
                'required'=>false,
                'constraints'=>[
                    new Image([
                        'maxSize' => '5M'
                    ])
                ]
            ])
            ->add('content', CKEditorType::class, array(
                'config' => array(
                    'uiColor' => '#ffffff',
                    'toolbar' => 'my_toolbar_1',
                ),
            ))
            ->add('finishAt', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Hạn chót bạn muốn quyên góp tiền',
                'help' => 'Nếu bạn chưa có ngày cụ thể, hệ thống sẽ để mặc định là 30 ngày. Bạn có thể gia hạn sau'
            ])
            ->add('targetAmount', MoneyType::class, [
                'label' => 'Số tiền bạn muốn quyên góp được'
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