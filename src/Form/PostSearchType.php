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
                    'placeholder' => 'form.PostSearch.PostTitlePlaceholder'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'placeholder' => 'form.PostSearch.StatusPlaceholder',
                'choices' => [
                    'form.PostSearch.Draft' => PostStatus::POST_DRAFT,
                    'form.PostSearch.WaitingValidation' => PostStatus::POST_WAITING_VALIDATION,
                    'form.PostSearch.WaitingInfo' => PostStatus::POST_WAITING_INFO,
                    'form.PostSearch.Collecting' => PostStatus::POST_COLLECTING,
                    'form.PostSearch.TransfertFund' => PostStatus::POST_TRANSFERT_FUND,
                    'form.PostSearch.Close' => PostStatus::POST_CLOSE,
                    'form.PostSearch.Stop' => PostStatus::POST_STOP

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
