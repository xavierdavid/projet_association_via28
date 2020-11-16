<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Mail;
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
        // Si un utilisateur authentifié existe, on fait une redirection vers la page d'accueil de l'espace utilisateurs
        if ($this->getUser()) {
            return $this->redirectToRoute('account');
        }

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
    public function forgottenPass(Request $request, UserRepository $userRepository, SendEmail $email, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $manager, Mail $mail)
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
                // Affectation de la valeur du token à la propriété 'rest_token' de la classe User via le setter
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
            $mail->emailResetNotification($user, $resetUrl);

            // Envoi d'un message flash de confirmation
            $this->addFlash('message', 'Un email de réinitialisation de mot de passe vient de vous être envoyé à l\'adresse suivante : ' . $user->getEmail());

             // On redirige vers la page de login
             return $this->redirectToRoute('app_login');
        }

        // Envoi des données au template pour l'affichage
        return $this->render('security/forgotten_password.html.twig', [
            "resetForm" => $form->createView() // Envoi du formulaire à la vue
        ]);
    }

    /**
     * Permet de contrôler le traitement du formulaire de réinitialisation du mot de passe de l'utilisateur
     * @Route("/reset-password/{resetToken}", name="app_reset_password")
     * @return void
     */
    public function resetPassword($resetToken, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        // On récupère l'utilisateur ayant le token passé en paramètre à l'aide du manager de Doctrine et du repository
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['reset_token'=>$resetToken]);
        
        // Si l'utilisateur n'existe pas avec ce token 
        if(!$user) {
            $this->addFlash('danger', 'Aucun utilisateur reconnu avec ce token de réinitialisation');
            // On redirige vers la page de login
            return $this->redirectToRoute('app_login');
        }

        // Création du formulaire de réinitialisation du mot de passe
        $form = $this->createForm(ResetPasswordType::class, $user);
        // Récupération de la requête
        $form->handleRequest($request);
        // Vérification de la soumission et de la validité des données
        if($form->isSubmitted() && $form->isValid()) {
            // On supprime le token de réinitialisation de l'utilisateur 
            $user->setResetToken(null);
            // On récupère le nouveau mot de passe saisi dans le formulaire de réinitialisation 
            $new_password = $form->get('new_password')->getData();
            // On encode le nouveau mot de passe
            $password = $encoder->encodePassword($user,$new_password);
            // On affecte le mot de passe encodé à l'attribut password de l'entité User à l'aide de son setter
            $user->setPassword($password);
            // On envoie le nouveau mot de passe crypté dans la base à l'aide du manager de Doctrine
            $manager->persist($user);
            $manager->flush();
            // On envoie un message flash à l'utilisateur
            $this->addFlash('message', 'Mot de passe réinitialisé avec succès !');
            // On redirige vers la page de login
            return $this->redirectToRoute('app_login');
        }

        // Envoi des données au template pour l'affichage
        return $this->render('security/reset_password.html.twig', [
            "resetPasswordForm" => $form->createView(), // Envoi du formulaire à la vue
        ]);  
    }
}
