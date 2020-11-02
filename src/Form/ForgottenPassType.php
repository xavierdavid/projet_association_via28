<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ForgottenPassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Formulaire de réinitialisation du mot de passe de l'utilisateur
        $builder
            ->add('email', EmailType::class, [
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre email"
                ]
            ])
            ->add('envoyer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
