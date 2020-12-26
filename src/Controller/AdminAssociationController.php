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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $manager){

        // Création d'une nouvelle instance de l'entité 'Association'
        $association = new Association();
        // Création du formulaire de création d'une association
        $form = $this->createForm(AssociationFormType::class, $association);
        // On récupère la requête
        $form->handleRequest($request);
        // Traitement du formulaire après soumission et validation 
        if($form->isSubmitted() && $form->isValid()) {
           // On génère la date de création de la publication - Hydratation de la propriété 'created_at'
            $association->setCreatedAt(new \DateTime());
            // On génère la date de mise à jour - Hydratation de la propriété 'updated_at'
            $association->setUpdatedAt(new \DateTime());
            // On génère le slug à l'aide du service SluggerInterface - Hydratation de la propriété 'slug'
            $association->setSlug(strtolower($this->slugger->slug($association->getName())));
            // Récupération du fichier image du logo téléchargé dans le formulaire dans le champ 'cover_image'
            $coverImageFile = $form['cover_image']->getData();
            //dd($form['cover_image']);
            // Si un fichier image est présent (Rappel : le champ est facultatif)...
            if($coverImageFile) {
                //dd($coverImageFile);
                // On utilise le service FileUploader pour uploader le fichier vers le répertoire de stockage
                $newCoverImageFilename = $this->fileUploader->upload($coverImageFile);
                // On met à jour la propriété cover_image de l'entite Association pour stocker le nom du fichier dans la base de données - Hydratation de la propriété 'cover_image'
                $association->setCoverImage($newCoverImageFilename); 
            }
            
            // On parcourt les entités 'Image' ajoutées dans les sous-formulaires CollectionType
            foreach($association->getImage() as $image) {
                // Si un fichier image uploadé a été transmis 
                if($image->getImageFile()) {
                    // Pour chaque entité 'Image', on récupère le fichier image uploadé
                    $uploadImage = $image->getImageFile();
                    // Pour chaque image uploadée, on utilise le service FileUploader pour uploader chaque fichier image vers le répertoire de stockage
                    $newImageFilename = $this->fileUploader->upload($uploadImage);
                    // On stocke le nom de l'image en base de données en hydratant la propriété 'name' de l'entité 'Image'
                    $image->setName($newImageFilename);
                } else {
                    // Si aucun fichier image n'a été transmis mais que néanmoins le titre de l'image a été renseigné
                    // Alors par défaut, on donne le nom 'undifined' à l'image (le champ 'name' étant obligatoire)
                    $image->setName('Undefined');
                }
            }

            // On parcourt les entités 'Document' ajoutés dans les sous-formulaires CollectionType
            foreach($association->getDocument() as $document) {
                // Si un fichier document uploadé a été transmis 
                if($document->getDocumentFile()) {
                    // Pour chaque entité 'Document', on récupère le fichier document uploadé
                    $uploadDocument = $document->getDocumentFile();
                    // Pour chaque document uploadé, on utilise le service FileUploader pour uploader chaque fichier document vers le répertoire de stockage
                    $newDocumentFilename = $this->fileUploader->upload($uploadDocument);
                    // On stocke le nom de l'image en base de données en hydratant la propriété 'name' de l'entité 'Image'
                    $document->setName($newDocumentFilename);
                } else {
                    // Si aucun fichier document n'a été transmis mais que néanmoins le titre du document a été renseigné
                    // Alors par défaut, on donne le nom 'undifined' au document (le champ 'name' étant obligatoire)
                    $image->setName('Undefined');
                }
            }

            // On enregistre l'entité $association à l'aide du manager de Doctrine
            $manager->persist($association);

            // On demande au manager d'exécuter la requête ('INSERT INTO')
            $manager->flush(); 

            // Envoi d'un message flash de succès
            $this->addFlash('message', "Votre association <strong>{$association->getName()}</strong> a été créée avec succès !");
 
            // On redirige vers la liste des associations
            return $this->redirectToRoute('admin_association_new');
        }

        return $this->render('admin/association/new.html.twig', [
            'associationForm' => $form->createView()
        ]); 
    }

    /**
     * Permet de contrôler l'affichage et le traitement du formulaire d'édition de l'association
     * @Route("/admin/association/{slug}/edit", name="admin_association_edit")
     * IsGranted("ROLE_ADMIN")
     * @param Association $association
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function edit(Association $association, Request $request, EntityManagerInterface $manager)
    {
        // Récupération de l'association à modifier à l'aide du ParamConverter

        // Récupération de la collection d'objets images de l'association stockées dans la base de données
        $originalImageCollection = new ArrayCollection();
        foreach ($association->getImage() as $image) {
            $originalImageCollection->add($image);
        }

        // Récupération de la collection d'objets documents de l'association stockées dans la base de données
        $originalDocumentCollection = new ArrayCollection();
        foreach ($association->getDocument() as $document) {
            $originalDocumentCollection->add($document);
        }

        // Création du formulaire d'édition
        $form = $this->createForm(AssociationFormType::class, $association);
        // Récupération de la requête
        $form->handleRequest($request);
        // Traitement de la soumission et de la validation du formulaire d'édition
        if($form->isSubmitted() && $form->isValid()) {
            // On ne génère plus la date de création de la publication 
           
            // On génère la date de mise à jour - Hydratation de la propriété 'updated_at'
            $association->setUpdatedAt(new \DateTime());
            // On génère le slug à l'aide du service SluggerInterface - Hydratation de la propriété 'slug'
            $association->setSlug(strtolower($this->slugger->slug($association->getName())));
            // Récupération du fichier image du logo téléchargé dans le formulaire dans le champ 'cover_image'
            $coverImageFile = $form['cover_image']->getData();
            //dd($form['cover_image']);
            // Si un fichier image est présent (Rappel : le champ est facultatif)...
            if($coverImageFile) {
                // Si une ancienne image principale existe déjà pour l'association
                if($association->getCoverImage()) {
                    // On supprime cette ancienne image du dossier 'uploads'
                    unlink($this->getParameter('file_directory').'/'.$association->getCoverImage());
                }
                // On utilise le service FileUploader pour uploader le fichier vers le répertoire de stockage
                $newCoverImageFilename = $this->fileUploader->upload($coverImageFile);
                // On met à jour la propriété cover_image de l'entite Association pour stocker le nom du fichier dans la base de données - Hydratation de la propriété 'cover_image'
                $association->setCoverImage($newCoverImageFilename); 
            }

            // On parcourt les entités 'Image' ajoutées dans les sous-formulaires CollectionType
            foreach($association->getImage() as $image) {
                // Si un fichier image uploadé a été transmis 
                if($image->getImageFile()) {
                    // Pour chaque entité 'Image', on récupère le fichier image uploadé
                    $uploadImage = $image->getImageFile();
                    // Pour chaque image uploadée, on utilise le service FileUploader pour uploader chaque fichier image vers le répertoire de stockage
                    $newImageFilename = $this->fileUploader->upload($uploadImage);
                    // On stocke le nom de l'image en base de données en hydratant la propriété 'name' de l'entité 'Image'
                    $image->setName($newImageFilename);
                } else {
                    // Si aucun fichier image n'a été transmis mais que néanmoins le titre de l'image a été renseigné
                    // Alors par défaut, si l'image n'a pas encore de nom, on lui donne le nom 'Undefined'(le champ 'name' étant obligatoire)
                    if($image->getName() == null) {
                        $image->setName('Undefined');
                    }
                }
            }
            
            // On parcourt la collection d'images de l'association stockée dans la base de données
            foreach ($originalImageCollection as $image) {
                // Si une des images de cette collection n'est pas présente dans l'un des sous-formulaires soumis 
                if (false === $association->getImage()->contains($image)) {
                    // Alors on supprime le fichier de cette image du dossier 'uploads' si et seulement si son nom est différent de 'Undefined'
                    if($image->getName() !== 'Undefined') {
                        unlink($this->getParameter('file_directory').'/'.$image->getName());
                    }
                    // Puis on supprime l'objet image concerné de la base de données
                    $manager->remove($image);
                }
            }

            // On parcourt les entités 'Document' ajoutés dans les sous-formulaires CollectionType
            foreach($association->getDocument() as $document) {
                // Si un fichier image uploadé a été transmis 
                if($document->getDocumentFile()) {
                    // Pour chaque entité 'Document', on récupère le fichier document uploadé
                    $uploadDocument = $document->getDocumentFile();
                    // Pour chaque document uploadé, on utilise le service FileUploader pour uploader chaque fichier document vers le répertoire de stockage
                    $newDocumentFilename = $this->fileUploader->upload($uploadDocument);
                    // On stocke le nom de l'image en base de données en hydratant la propriété 'name' de l'entité 'Image'
                    $document->setName($newDocumentFilename);
                } else {
                    // Si aucun fichier document n'a été transmis mais que néanmoins le titre du document a été renseigné
                    // Alors par défaut, si le document n'a pas encore de nom, on lui donne le nom 'undefined' (le champ 'name' étant obligatoire)
                    if($document->getName() == null) {
                        $document->setName('Undefined');
                    }
                }
            }

            // On parcourt la collection de documents de l'association stockée dans la base de données
            foreach ($originalDocumentCollection as $document) {
                // Si un des documents de cette collection n'est pas présent dans l'un des sous-formulaires soumis 
                if (false === $association->getDocument()->contains($document)) {
                    // Alors on supprime le fichier de ce document du dossier 'uploads' si et seulement si son nom est différent de 'Undefined'
                    if($document->getName() !== 'Undefined') {
                        unlink($this->getParameter('file_directory').'/'.$document->getName());
                    }
                    // Puis on supprime l'objet document concerné de la base de données
                    $manager->remove($document);
                }
            }

            // On enregistre l'entité $association à l'aide du manager de Doctrine
            $manager->persist($association);

            // On demande au manager d'exécuter la requête ('INSERT INTO')
            $manager->flush(); 

            // Envoi d'un message flash de succès
            $this->addFlash('message', "Votre association <strong>{$association->getName()}</strong> a été modifiée avec succès !");
 
            // On redirige vers la liste des associations
            return $this->redirectToRoute('admin_association_new');
        }

        // Retour de la réponse
        return $this->render('admin/association/edit.html.twig', [
            'associationForm' => $form->createView(),
            'association' => $association
        ]);
    }  
    
   /*  *
     * Permet de supprimer une image associée à l'association à l'aide d'Ajax
     * @Route("admin/image/{id}/delete", name="association_delete_image", methods={"DELETE"})
     *
     * @param Image $image
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function deleteImage(Image $image, Request $request, EntityManagerInterface $manager)
    {
        // Récupération de l'image à l'aide du ParamConverter
        
        // On récupère les données au format Json avec Ajax en associatif
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token (envoyé en Ajax avec javascript) est valide
        if($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])){
            // On récupère le nom de l'image à supprimer
            $imageName = $image->getName();
            // On supprime le fichier image du dossier 'uploads'
            unlink($this->getParameter('file_directory').'/'.$imageName);
            // On supprime l'image de la base de données
            $manager->remove($image);
            $manager->flush();
            
            // On retourne une réponse en Json
            return new JsonResponse(['success' => 1]);
        } else {
            // Si le token n'est pas valide 
            return new JsonResponse(['error' => 'Token invalide !'], 400);
        }
    }

    /**
     * Permet de supprimer un document associé à l'association à l'aide d'Ajax
     * @Route("admin/document/{id}/delete", name="association_delete_document", methods={"DELETE"})
     *
     * @param Document $document
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function deleteDocument(Document $document, Request $request, EntityManagerInterface $manager)
    {
        // Récupération de l'image à l'aide du ParamConverter
        
        // On récupère les données au format Json avec Ajax en associatif
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token (envoyé en Ajax avec javascript) est valide
        if($this->isCsrfTokenValid('delete' . $document->getId(), $data['_token'])){
            // On récupère le nom du document à supprimer
            $documentName = $document->getName();
            // On supprime le fichier document du dossier 'uploads'
            unlink($this->getParameter('file_directory').'/'.$documentName);
            // On supprime le document de la base de données
            $manager->remove($document);
            $manager->flush();
            
            // On retourne une réponse en Json
            return new JsonResponse(['success' => 1]);
        } else {
            // Si le token n'est pas valide 
            return new JsonResponse(['error' => 'Token invalide !'], 400);
        }
    }


}
