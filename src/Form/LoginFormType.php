<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', TextType::class, ['attr' => ['placeholder' => 'Input your email address.'], 'required'=>true]);
        $builder->add('password', PasswordType::class, ['attr' => ['placeholder' => 'Input your password.'], 'required'=>true]);
        $builder->add('remember', CheckboxType::class, ['label'=>'Remind me for the next 30 days', 'required'=>false]);
    }
}