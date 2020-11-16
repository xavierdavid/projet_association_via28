<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Mail;
use App\Service\SendEmail;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * Contrôle l'affichage et le traitement du formulaire d'inscription des utilisateurs
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UserAuthenticator $authenticator, SendEmail $email, Mail $mail): Response
    {
        // Création d'une nouvelle instance de la classe 'User' pour créer un nouvel utilisateur
        $user = new User();
        // Création du formulaire d'inscription rattaché à l'entité User
        $form = $this->createForm(RegistrationFormType::class, $user);
        // Récupération des données transmises avec la requête via le formulaire
        $form->handleRequest($request);
        // Vérification de la soumission et de la validité des données, puis envoi dans la base 
        if ($form->isSubmitted() && $form->isValid()) {
            // Encodage du mot de passe de l'utilisateur
            // On demande à l'encoder du composant de sécurité de 'hasher' le mot de passe de l'utilisateur en utilisant l'algorithm 'auto'
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            // Création du token d'activation du compte utilisateur 
            $user->setActivationToken(md5(uniqid()));

            // Création du rôle utilisateur. Par défaut, Symfony, dans la table User, définit un rôle 'ROLE_USER' par défaut à tous les utilisateurs (getRoles()) 

            // Création d'une cover_image par défaut pour l'utilisateur 
            $user->setCoverImage("user_avatar.jpg");

            // Création automatique du slug dans le cycle de vie de l'entité User à l'aide de Slugify

            // Récupération du manager de Doctrine
            $entityManager = $this->getDoctrine()->getManager();
            // Préparation de l'envoi à l'aide du manager 
            $entityManager->persist($user);
            // Envoi en base de données 
            $entityManager->flush();

            // Envoi d'un email avec le service Mail permettant à l'utilisateur d'activer son compte
            $mail->emailUserActivation($user);

            // Envoi d'un message flash de confirmation
            $this->addFlash('message', 'Un email vient de vous être envoyé à l\'adresse ' . $user->getEmail() . ' pour activer votre compte');

            // On redirige vers la page de login
            return $this->redirectToRoute('app_login');
            
            /* return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            ); */
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(), // Envoi du formulaire d'inscription à la vue
        ]);
    }

    /**
     * Contrôle l'activation du compte utilisateur
     * @Route("/activation/{token}", name="activation")
     *
     * @return void
     */
    public function activation($token, UserRepository $repository)
    {
        // On récupère l'utilisateur ayant le token passé en paramètre (et dont le compte n'est pas encore activé)
        $user = $repository->findOneBy(['activation_token'=> $token]);

        // Si aucun utilisateur n'existe avec ce token
        if(!$user) {
            // On affiche une erreur 404
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }

        // On supprime ensuite le token de l'utilisateur pour activer son compte
        $user->setActivationToken(null);
        // On envoie la modification en base de données à l'aide du manager de Doctrine
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush($user);

        // On envoie un message flash à l'utilisateur indiquant que son compte est activé
        $this->addFlash('message', 'Votre compte a bien été activé !');

        // On redirige vers le formulaire de login 
        return $this->redirectToRoute('app_login');
    }
}
