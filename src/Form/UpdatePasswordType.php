<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // On utilise l'instance $builder de la classe FormBuilderInterface pour construire le formulaire
        $builder
            // On paramètre les champs de formulaire : types, options et attributs
            ->add('first_name', TextType::class, [
                'disabled' => true,
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre prénom"
                ]
            ])
            ->add('last_name', TextType::class,[
                'disabled' => true,
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre nom"
                ]
            ])
            ->add('email', EmailType::class, [
                'disabled' => true,
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre email"
                ]
            ])
            ->add('old_password', PasswordType::class, [
                'mapped' => false, // Ne pas lier ce champ à l'entité User
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre mot de passe actuel"
                ]
            ])
            ->add('new_password', RepeatedType::class, [  
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent être identiques !',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Nouveau mot de passe', 'attr'  => [
                    'placeholder' => "Veuillez saisir un nouveau mot de passe"
                ],],
                'second_options' => ['label' => 'Confirmer votre nouveau mot de passe', 'attr'  => [
                    'placeholder' => "Veuillez confirmer votre nouveau mot de passe"
                ],],
                // instead of being set onto the object directly,this is read and encoded in the controller
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer un mot de passe SVP',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comporter au minimum {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
