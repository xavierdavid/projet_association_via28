<?php

namespace App\Form;

use App\Entity\Association;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;

class AssociationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' =>'Nom de l\'association',
                'attr'=> [
                    'placeholder'=>'Veuillez renseigner le nom de l\'association'
                ]
            ])
            ->add('address', TextType::class, [
                'label' =>'Adresse du siège',
                'attr'  => [
                    'placeholder' => "Veuillez saisir l'adresse du siège social"
                ]
            ])
            ->add('postal', TextType::class, [
                'label' =>'Code postal',
                'attr'  => [
                    'placeholder' => "Veuillez saisir le code postal du siège"
                ]
            ])
            ->add('city', TextType::class, [
                'label' =>'Commune du siège',
                'attr'  => [
                    'placeholder' => "Veuillez saisir la commune du siège"
                ]
            ])
            ->add('phone', TelType::class, [
                'label' =>'Téléphone de l\'association',
                'attr'  => [
                    'placeholder' => "Veuillez saisir le numéro de téléphone de l'association"
                ]
            ])
            ->add('email', EmailType::class, [
                'label' =>'Email de l\'association',
                'attr'  => [
                    'placeholder' => "Veuillez saisir l'email de l'association"
                ]
            ])
            ->add('cover_image', FileType::class, [
                'label' => 'Ajouter un logo',
                'multiple' => false, // Pas d'ajout multiple
                'mapped' => false, // Champ non lié à la base de données
                'required' => true, // champ requis
            ])
            ->add('introduction', TextareaType::class, [
               'label' => 'Slogan de l\'association',
               'attr'  => [
                   'placeholder' => 'Ajouter un texte d\'introduction', 
               ]
            ])
            ->add('object', TextareaType::class, [
                'label' => 'Objet social',
                'attr'  => [
                   'placeholder' => 'Renseigner l\'objet de l\'association', 
               ]
            ])
            ->add('content',CKEditorType::class, [
                'label' => 'Présentation de l\'association',
                'attr'  => [
                   'placeholder' => 'Présentez votre association', 
               ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Ajouter des images',
                'multiple' => true, // Ajout d'images multiples
                'required' => false, // champ facultatif
                'mapped' => false, // Champ non lié à la base de données
            ])

            ->add('document', FileType::class,[
                'label' => 'Joindre des documents au format PDF',
                'multiple' => true, // Ajout de fichiers multiples
                'required' => false, // Champ facultatif
                'mapped' => false, // Champ non lié à la base de données
                /* 'constraints' => [ // Contraintes concernant le fichier de type PDF à uploader
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez ajouter un document de type PDF SVP...',
                    ])
                ] */
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Association::class,
        ]);
    }
}
