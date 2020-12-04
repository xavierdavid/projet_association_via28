<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Document;
use App\Entity\Association;
use App\Service\FileUploader;
use App\Form\AssociationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAssociationController extends AbstractController
{
    protected $slugger;
    protected $fileUploader;
    
    /**
     * Constructeur de classe permettant de récupérer et d'initialiser les services SluggerInterface et FileUploader
     *
     * @param SluggerInterface $slugger
     * @param FileUploader $fileUploader
     */
    public function __construct(SluggerInterface $slugger, FileUploader $fileUploader)
    {
        $this->slugger = $slugger; 
        $this->fileUploader = $fileUploader;
    }
    
    /**
     * Permet de contrôler l'affichage de la liste des associations
     * @Route("/admin/association", name="admin_association_index")
     */
    public function index()
    {
        return $this->render('admin_association/index.html.twig', [
            'controller_name' => 'AdminAssociationController',
        ]);
    }

    /**
     * Permet de contrôler l'affichage et le traitement du formulaire de création d'une association
     * @Route("/admin/association/new", name="admin_association_new")
     * @IsGranted("ROLE_ADMIN")
     *
     * @return void
     */
    public function new(Request $request, EntityManagerInterface $manager){

        // Création d'une nouvelle instance de l'entité 'Association'
        $association = new Association();
        // Création du formulaire de création d'une association
        $form = $this->createForm(AssociationFormType::class, $association);
        // On récupère la requête
        $form->handleRequest($request);
        // Traitement du formalaire après soumission et validation 
        if($form->isSubmitted() && $form->isValid()) {
            
            // On génère la date de création de la publication - Hydratation de la propriété 'created_at'
            $association->setCreatedAt(new \DateTime());
            // On génère la date de mise à jour - Hydratation de la propriété 'updated_at'
            $association->setUpdatedAt(new \DateTime());
            // On génère le slug à l'aide du service SluggerInterface - Hydratation de la propriété 'slug'
            $association->setSlug(strtolower($this->slugger->slug($association->getName())));

            // Récupération du fichier image du logo téléchargé dans le formulaire dans le champ 'cover_image'
            $coverImageFile = $form['cover_image']->getData();
            // Si un fichier image est présent (Rappel : le champ est facultatif)...
            if($coverImageFile) {
                // On utilise le service FileUploader pour uploader le fichier vers le répertoire de stockage
                $newCoverImageFilename = $this->fileUploader->upload($coverImageFile);
                // On met à jour la propriété cover_image de l'entite Association pour stocker le nom du fichier dans la base de données - Hydratation de la propriété 'cover_image'
                $association->setCoverImage($newCoverImageFilename); 
            }
            
            // On récupère les images multiples transmises 
            $images = $form->get('image')->getData();
            // On boucle sur les images 
            foreach($images as $image) {
                // Pour chaque image uploadée, on utilise le service FileUploader pour uploader chaque fichier image vers le répertoire de stockage
                $newImageFilename = $this->fileUploader->upload($image);
                // On crée une nouvelle instance de l'entité Image
                $img = new Image();
                // On stocke le nom de l'image en base de données en hydratant la propriété 'name' de l'entité 'Image'
                $img->setName($newImageFilename);
                // Puis on ajoute l'image à l'association
                $association->addImage($img);
            }

            // On récupère les fichiers transmis
            $documents = $form->get('document')->getData();
            // On boucle sur les fichiers 
            foreach($documents as $document) {
                // Pour chaque fichier uploadé, on utilise le service FileUploader pour uploader chaque document pdf vers le répertoire de stockage
                $newDocumentName = $this->fileUploader->upload($document);
                // On crée une nouvelle instance de l'entité File
                $document = new Document();
                // On stocke le nom du document en base de données en hydratant la propriété 'name' de l'entité 'Document'
                $document->setName($newDocumentName);
                // Puis on ajoute le document à l'association
                $association->addDocument($document);
            }
            
             // On enregistre l'entité $association à l'aide du manager de Doctrine
             $manager->persist($association);

             // On demande au manager d'exécuter la requête ('INSERT INTO')
             $manager->flush();
 
              // Envoi d'un message flash de succès
              $this->addFlash('message', 'Votre association a été créée avec succès !');
 
              // On redirige vers la liste des associations
              return $this->redirectToRoute('admin_association_new');
        }

        return $this->render('admin/association/new.html.twig', [
            'associationForm' => $form->createView()
        ]); 
    }
}
