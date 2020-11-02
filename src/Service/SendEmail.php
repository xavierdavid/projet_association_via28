<?php

namespace App\Service;

use App\Entity\User;
use Twig\Environment;


class SendEmail {

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $renderer;

    /**
     * Constructeur qui récupère le service 'Swift Mailer' par injection de dépendance 
     * Le constructeur initialise la propriété $mailer et $renderer (rendu HTML/Twig)
     * $mailer est l'argument de '@mailer', identifiant du service (@) défini dans config/services.yaml
     *
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer, Environment $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    /**
     * Permet d'envoyer un email à un nouvel utilisateur pour activer son compte après inscription
     *
     * @param User $user // Utilisateur en cours d'activation
     * @return void
     */
    public function emailUserActivation(User $user)
    {
        /* Paramétrage de l'email */
        $email = (new \Swift_Message('Activation de votre compte'))
            ->setFrom('noreply@xavier.com')
            ->setTo($user->getEmail()) // Récupération de l'email de l'utilisateur en cours
            ->setBody(
                $this->renderer->render(
                    // templates/emails/activation_email.html.twig
                    'emails/activation_email.html.twig', [
                        // On envoie le token au template
                        "token"=> $user->getActivationToken()
                    ]),
                'text/html'
            )
        ;
        /* Envoi de l'email */
        $this->mailer->send($email);
    }

    /**
     * Permet d'envoyer un email à l'utilisateur pour réinitialiser son mot de passe
     *
     * @param User $user
     * @param [type] $resetUrl
     * @return void
     */
    public function emailResetNotification(User $user, $resetUrl)
    {
        /* Paramétrage de l'email */
        $email = (new \Swift_Message('Réinitialisation de votre mot de passe'))
            ->setFrom('noreply@xavier.com')
            ->setTo($user->getEmail()) // Récupération de l'email de l'utilisateur en cours
            ->setBody(
                $this->renderer->render(
                    // templates/emails/reset_password_email.html.twig
                    'emails/reset_password_email.html.twig', [
                        // On envoie l'url de réinitialisation au template
                        "resetUrl"=> $resetUrl
                    ]),
                'text/html'
            )
        ;
        /* Envoi de l'email */
        $this->mailer->send($email);
    }
}