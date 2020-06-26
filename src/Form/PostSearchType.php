<?php

namespace App\Form;

use App\Entity\PostSearch;
use App\Entity\PostStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('PostTitle', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Tên dự án'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'Quá trình',
                'choices' => [
                    'Bản nháp' => PostStatus::POST_DRAFT,
                    'Bổ sung thông tin' => PostStatus::POST_WAITING_INFO,
                    'Quyên góp' => PostStatus::POST_COLLECTING,
                    'Chuyển khoản' => PostStatus::POST_TRANSFERT_FUND,
                    'Kết thúc' => PostStatus::POST_CLOSE,
                    'Tạm ngừng' => PostStatus::POST_STOP
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PostSearch::class,
            'csrf_protection' => false
        ]);
    }
}
