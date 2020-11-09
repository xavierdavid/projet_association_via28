<?php

namespace App\Controller;

use App\Form\UpdatePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountPasswordController extends AbstractController
{
    /**
     * Contrôle le traitement et l'affichage du formulaire de modification du mot de passe utilisateur
     * @Route("/account/update-password", name="account_password")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder,EntityManagerInterface $manager)
    {
        // Récupération de l'utilisateur authentifié
        $user = $this->getUser();
        // Création du formulaire de modification du mot de passe
        $form = $this->createForm(UpdatePasswordType::class, $user);
        // Récupération de la requête
        $form->handleRequest($request);
        // Vérification de la soumission et de la validité des données
        if($form->isSubmitted() && $form->isValid()) {
            // Récupération de l'ancien mot de passe saisi dans le formulaire
            $old_password = $form->get('old_password')->getData();
            // Affichage du contenu de la variable $old_password
            //dd($old_password);

            // Si le mot de passe actuel saisi dans le formulaire est identique au mot de passe crypté et stocké dans la base de données
            if($encoder->isPasswordValid($user, $old_password)) {
                //die('C\'est OK'); // Affiche OK et met fin au programme
                
                // On récupère le nouveau mot de passe saisi 
                $new_password = $form->get('new_password')->getData();
                //dd($new_password);
                // On encode le nouveau mot de passe
                $password = $encoder->encodePassword($user,$new_password);
                // On affecte le mot de passe encodé à l'attribut password de l'entité User à l'aide de son setter
                $user->setPassword($password);
                // On envoie le nouveau mot de passe crypté dans la base à l'aide du manager de Doctrine
                $manager->persist($user);
                $manager->flush();
                // On envoie un message flash à l'utilisateur
                $this->addFlash('message', 'Mot de passe modifié avec succès !');
                // On redirige vers la page d'accueil du compte utilisateur
                return $this->redirectToRoute('account');
            // Sinon, si le mot de passe actuel saisi n'est pas valide
            } else {
                // On envoie un message flash à l'utilisateur
                $this->addFlash('danger', 'Votre mot de passe actuel n\'est pas valide !');
                // On redirige vers la page du formulaire de modification du mot de passe
                return $this->redirectToRoute('account_password');
            }
        }
        
        return $this->render('account/update_password.html.twig', [
           'updatePasswordForm' => $form->createView() // Envoi du formulaire à la vue 
        ]);
    }
}
