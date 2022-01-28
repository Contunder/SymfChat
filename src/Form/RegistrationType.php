<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr'=>[
                    'placeholder' => 'Nom d\'Utilisateur',
                    'class'=>'form-control'
                ]])
            ->add('firstname', TextType::class,[
                'attr'=>[
                    'placeholder' => 'Nom',
                    'class'=>'form-control'
                ]])
            ->add('lastname', TextType::class, [
                'attr'=>[
                    'placeholder' => 'Prénom',
                    'class'=>'form-control'
                ]])
            ->add('email', EmailType::class, [
                'attr'=>[
                    'placeholder' => 'Email',
                    'class'=>'form-control'
                ]])
            ->add('password', PasswordType::class, [
                'attr'=>[
                    'placeholder' => 'Mot de Passe',
                    'class'=>'form-control'
                ]])
            ->add('confirm_password', PasswordType::class, [
                'attr'=>[
                    'placeholder' => 'Confirmation du Mot de Passe',
                    'class'=>'form-control'
                ]])
            ->add('pp', FileType::class, [
                'attr'=>[
                    'empty-data' =>'',
                    'value' => 'upload/pp/user-default.png',
                    'class'=>'form-control'
                ]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
