<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image_file', FileType::class, [
                /* 'attr' => [
                    'placeholder' => 'Rechercher'
                ], */
                'required' => false, // champ facultatif
                'constraints' => array(
                    new File(),
                )
                //'mapped' => false, // Champ non lié à la base de données
            ])
            ->add('caption', TextType::class, [
                'attr' => [
                    'placeholder' => 'Titre'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
