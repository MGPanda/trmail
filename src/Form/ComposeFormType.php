<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ComposeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("receiver", EmailType::class, ['attr' => ['placeholder' => 'Who are you sending this to?'], 'required' => true]);
        $builder->add("subject", TextType::class, ['attr' => ['placeholder' => 'Write a subject']]);
        $builder->add("content", TextareaType::class, ['attr' => ['placeholder' => 'Write your message!']]);
    }
}