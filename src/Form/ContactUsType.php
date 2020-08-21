<?php

namespace App\Form;

use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactUsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userEmail', EmailType::class, [
                'label' => 'form.ContactUs.UserEmail'
            ])
            ->add('firstandlastname', TextType::class, [
                'label' => 'form.ContactUs.Name'
            ])
            ->add('PhoneNumber', TextType::class, [
                'label' => 'form.ContactUs.PhoneNumber'
            ])
            ->add('subject', TextType::class, [
                'label' => 'form.ContactUs.Subject'
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'form.ContactUs.Content'
            ])
            ->add('captcha', RecaptchaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
