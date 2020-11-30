<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class AccountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // On paramètre les champs de formulaire : types, options et attributs
            ->add('first_name', TextType::class, [
                'label'=>'Prénom',
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre prénom"
                ]
            ])
            ->add('last_name', TextType::class,[
                'label'=>'Nom',
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre nom"
                ]
            ])
            ->add('email', EmailType::class, [
                'label'=>'Email',
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre email"
                ]
            ])
            ->add('cover_image', FileType::class, [
                'label'=>'Ajouter une image de profil',
                'multiple' => false,
                'mapped' => false, // Champ non lié à la base de données
                'required' => false, // champ facultatif
            ])
            ->add('address', TextType::class, [
                'label'=>'Adresse',
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre adresse postale"
                ]
            ])
            ->add('postal', TextType::class, [
                'label'=>'Code postal',
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre code postal"
                ]
            ])
            ->add('city', TextType::class, [
                'label'=>'Ville',
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre ville"
                ]
            ])
            ->add('country', CountryType::class, [
                'label'=>'Pays',
                'attr'  => [
                    'placeholder' => "Veuillez sélectionner votre pays"
                ]
            ])
            ->add('phone', TelType::class, [
                'label'=>'Téléphone',
                'attr'  => [
                    'placeholder' => "Veuillez saisir votre numéro de téléphone"
                ]
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
