<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    /**
     * Permet de construire un formulaire d'inscription d'utilisateurs à l'entité User
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // On utilise l'instance $builder de la classe FormBuilderInterface pour construire le formulaire
        $builder
            // On paramètre les champs de formulaire : types, options et attributs
            ->add('first_name', TextType::class, [
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre prénom"
                ]
            ])
            ->add('last_name', TextType::class,[
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre nom"
                ]
            ])
            ->add('address', TextType::class, [
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre adresse postale"
                ]
            ])
            ->add('phone', TextType::class, [
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre numéro de téléphone"
                ]
            ])
            ->add('email', EmailType::class, [
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre email"
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [  
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent être identiques !',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe', 'attr'  => [
                    'placeholder' => "Veuillez saisir un mot de passe"
                ],],
                'second_options' => ['label' => 'Confirmer votre mot de passe', 'attr'  => [
                    'placeholder' => "Veuillez confirmer votre mot de passe"
                ],],
                // instead of being set onto the object directly,this is read and encoded in the controller
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'entrer un mot de passe SVP',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comporter au minimum {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('rgpd_validation', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'J\'accepte la politique de traitement des données personnelles',
                    ]),
                ],
            ])
            //->add('slug') // Le slug est généré automatiquement dans le cycle de vie de l'entité User
            //->add('cover_image') // L'image par défaut est gérée par le contrôleur
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
