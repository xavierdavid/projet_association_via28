<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\AccountFormType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountController extends AbstractController
{
    protected $slugger;
    protected $fileUploader;
    
    /**
     * Constructeur de classe permettant de récupérer et d'initialiser les service SluggerInterface et FileUploader
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
     * Permet d'afficher la page de profil de l'utilisateur authentifié
     * @Route("/account", name="account")
     * @return Response
     */
    public function index()
    {
        // On récupère l'utilisateur authentifié
        $user = $this->getUser(); 
        return $this->render('account/index.html.twig', [
            'user' => $user
        ]);
    }
        
    /**
     * Contrôle l'affichage et le traitement du formulaire d'édition du profil de l'utilisateur authentifié
     * @Route("/account/user/edit", name="user_edit")
     * @return Response
     */
    public function edit(Request $request, EntityManagerInterface $manager)
    {
        // Récupération de l'utilisateur authentifié
        $user = $this->getUser();
        // Création du formulaire d'édition
        $form = $this->createForm(AccountFormType::class, $user);
        // Récupération de la requête
        $form->handleRequest($request);
        // Traitement du formulaire
        if($form->isSubmitted() && $form->isValid()) {
            // On génère le slug à l'aide du service SluggerInterface - Hydratation de la propriété 'slug' de l'entité User
            $user->setSlug(strtolower($this->slugger->slug($user->getFirstName(). ' ' . $user->getLastName())));
            // Récupération du fichier image téléchargé dans le formulaire dans le champ 'cover_image'
            $imageFile = $form['cover_image']->getData();
            // Si un nouveau fichier image a été uploadé via le formulaire (Rappel : le champ est facultatif)...
            if($imageFile) {
                // Si l'utilisateur a déjà une image d'avatar 
                if($user->getCoverImage()) {
                    // Alors on supprime cette ancienne image du dossier 'uploads'
                    unlink($this->getParameter('file_directory').'/'.$user->getCoverImage());
                }
                // On utilise le service FileUploader pour uploader le nouveau fichier vers le répertoire de stockage
                $newFilename = $this->fileUploader->upload($imageFile);
                // On met à jour la propriété imageFilename de l'entite User pour stocker le nom du fichier dans la base de données - Hydratation de la propriété 'cover_image'
                $user->setCoverImage($newFilename); 
            }
            // On enregistre l'entité $user à l'aide du manager de Doctrine
            $manager->persist($user);
            // On demande au manager d'exécuter la requête ('INSERT INTO')
            $manager->flush();

             // Envoi d'un message flash de success
             $this->addFlash('message', 'Votre profil a été modifié avec succès !');

             // On redirige vers la liste des publications
             return $this->redirectToRoute('account');
        }

        return $this->render('account/user_edit.html.twig', [
            'accountForm' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * Permet d'afficher la page de profil d'un adhérent utilisateur
     * @Route("/account/user/{slug}", name="user_show")
     * @param User $user
     * @return Response
     */
    public function show(User $user)
    {
        return $this->render('account/user_show.html.twig', [
            'user' => $user
        ]);
    }


}
