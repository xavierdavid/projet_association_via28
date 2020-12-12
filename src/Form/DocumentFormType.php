<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DocumentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('document_file', FileType::class, [
                'attr' => [
                    'placeholder' => 'Rechercher'
                ],
                'required' => false, // champ facultatif
                'constraints' => array(
                    new File(),
                )
            ])
            ->add('caption', TextType::class, [
                'label' => 'Titre du document',
                'attr' => [
                    'placeholder' => 'Libellé du document'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
