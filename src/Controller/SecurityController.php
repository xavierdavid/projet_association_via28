<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\SendEmail;
use App\Form\ForgottenPassType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Permet de contrôler le traitement du formulaire d'oubli du mot de passe de l'utilisateur
     * @Route("/forgotten-password", name="app_forgotten_password")
     * @return void
     */
    public function forgottenPass(Request $request, UserRepository $userRepository, SendEmail $email, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $manager)
    {
        // Création du formulaire d'oubli du mot de passe 
        $form = $this->createForm(ForgottenPassType::class);

        // Récupération de la requête pour récupérer les données envoyées via le formulaire
        $form->handleRequest($request);

        // Vérification des données (soumission et validité)
        if($form->isSubmitted() && $form->isValid()) {
            // Récupération des données (email) 
            $data = $form->getData();
            // On vérifie si un utilisateur a cet email en base de données à l'aide du repository
            $user = $userRepository->findOneByEmail($data['email']);
            // Si l'utilisateur n'existe pas 
            if(!$user) {
                // On envoie un message flash 
                $this->addFlash('danger', 'Cette adresse email n\'existe pas !');
                // On redirige vers la page de login
                return $this->redirectToRoute('app_login');
            }
            // Si l'utilisateur existe, on génère un token de réinitialisation avec le TokenGenerator Interface
            $resetToken = $tokenGenerator->generateToken();
            
            // On teste l'envoi du token de réinitialisation en base de données
            try {
                // Affectation de la valeur du token à la propriété 'rest_token' dela classe User via le setter
                $user->setResetToken($resetToken);
                // Envoi en base de données à l'aide du manager de Doctrine 
                $manager->persist($user);
                $manager->flush();
            } catch(\Exception $e) {
                // En cas d'échec d'envoi en base de données, on lance une alerte 
                $this->addFlash('warning', 'Une erreur est survenue ! : ' . $e->getMessage());
                // On redirige vers la page de login
                return $this->redirectToRoute('app_login');
            }

            // On génère une URL (associée à la route 'app_reset_password') de réinitialisation de mot de passe avec le token
            $resetUrl = $this->generateUrl('app_reset_password', ['resetToken'=>$resetToken], UrlGeneratorInterface::ABSOLUTE_PATH);

            // On envoi un email à l'utilisateur avec un lien contenant l'URL de réinitialisation
            // On utilise le service SendEmail pour envoyer l'email de réinitialisation à l'utilisateur 
            // On passe en paramètre : l'objet User et l'url de réinitialisation
            $email->emailResetNotification($user, $resetUrl);

            // Envoi d'un message flash de confirmation
            $this->addFlash('message', 'Un email de réinitialisation de mot de passe vient de vous être envoyé à l\'adresse suivante : ' . $user->getEmail());

             // On redirige vers la page de login
             return $this->redirectToRoute('app_login');
        }

        // Envoi des données au template pour l'affichage
        return $this->render('security/forgotten_password.html.twig', [
            "resetForm" => $form->createView() // Envoi du formulaire
        ]);
    }

    /**
     * Permet de contrôler le traitement du formulaire de réinitialisation du mot de passe de l'utilisateur
     * @Route("/reset-password/{resetToken}", name="app_reset_password")
     * @return void
     */
    public function resetPassword($resetToken, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder)
    {
        // On récupère l'utilisateur ayant le token passé en paramètre à l'aide du manager de Doctrine et du repository
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['reset_token'=>$resetToken]);
        
        // Si l'utilisateur n'existe pas avec ce token 
        if(!$user) {
            $this->addFlash('danger', 'Aucun utilisateur reconnu avec ce token de réinitialisation');
            // On redirige vers la page de login
            return $this->redirectToRoute('app_login');
        }
        
        // Si le formulaire a bien été soumis via la méthode Post 
        if($request->isMethod('POST')) {
            // On supprime le token de l'utilisateur 
            $user->setResetToken(null);
            // On récupère dans la requête le nouveau mot de passe transmis en post via le formulaire
            $newPassword = $request->request->get('password'); 
            // On récupère le deuxième mot de passe saisi par l'utilisateur pour confirmation
            $passwordVerify = $request->request->get('password_verify');

            // Si les deux mots de passe sont identiques 
            if($newPassword === $passwordVerify) {
                // On affecte le mot de passe récupéré en le chiffrant, à la propriété 'password' de l'entité USER à l'aide de son setter 
                $user->setPassword($passwordEncoder->encodePassword($user, $newPassword));
                // On envoie les données dans la base à l'aide du Manager de Doctrine
                $manager->persist($user);
                $manager->flush();
                // On envoie un message flash à l'utilisateur
                $this->addFlash('message', 'Mot de passe modifié avec succès !');
                // On redirige vers la page de login
                return $this->redirectToRoute('app_login');
            // Si les deux mots de passe ne sont pas identiques
            } else {
                // On envoie un message flash à l'utilisateur
                $this->addFlash('danger', 'Les deux mots de passe saisis ne sont pas identiques !');
                // On affiche de nouveau le formulaire de réinitialisation de mot de passe avec le token
                return $this->render('security/reset_password.html.twig', ['resetToken'=>$resetToken]);
            }
        // Sinon, si le formulaire n'a pas été envoyé en Post via le formulaire    
        } else {
            // On affiche de nouveau le formulaire de réinitialisation de mot de passe avec le token
            return $this->render('security/reset_password.html.twig', ['resetToken'=>$resetToken]);
        }
    }
}
