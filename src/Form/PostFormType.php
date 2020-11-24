<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Category;
use App\Entity\Keywords;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label'=>'Titre',
                'attr'=> [
                    'placeholder'=>'Veuillez donner un titre à votre publication'
                ]
            ])
            ->add('content', CKEditorType::class)
            ->add('cover_image', FileType::class, [
                'label'=>'Ajouter une image',
                'multiple' => false,
                'mapped' => false, // Champ non lié à la base de données
                'required' => false, // champ facultatif
            ])
            ->add('keyword', EntityType::class, [
                'class'=> Keywords::class,
                'multiple'=> true, // Choix multiples
                'expanded'=> true,
                'choice_label'=>'keyword',
                'label'=> 'Mots-clés'
            ])
            ->add('category', EntityType::class,[
                'class'=> Category::class,
                'multiple'=> true, // Choix multiples
                'choice_label'=>'name',
                'label'=> 'Catégories'
            ])
            ->add('envoyer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
