<?php

namespace App\Service;

use App\Entity\User;
use Mailjet\Client;
use Mailjet\Resources;
use Twig\Environment;

class Mail 
{

    // Initialisatisation des clé de l'API de MailJet
    private $api_key = 'ma_cle_principale';
    private $api_key_secret = 'ma_cle_secrete';

    /**
     * @var Environment
     */
    private $renderer;

    /**
     * Le constructeur initialise la propriété $renderer (rendu HTML/Twig)
     */
    public function __construct(Environment $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Permet d'envoyer un email à un nouvel utilisateur pour activer son compte après inscription
     *
     * @param User $user // Utilisateur en cours d'activation
     * @return void
    */
    public function emailUserActivation($user)
    {
        // On crée une nouvelle instance de MailJet avec les clés de l'API
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        
        // Configuration du corps de l'email
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "xav.david28@gmail.com",
                        'Name' => "Le site associatif"
                    ],
                    'To' => [
                        [
                        'Email' => $user->getEmail(), // Email de l'utilisateur 
                        'Name' => $user->getFirstName().' ' .$user->getLastName() // Nom et prénom de l'utilisateur
                        ]
                    ],
                    'TemplateID' => 1898813, // Id du modèle de template créé à partir de l'interface de MailJet
                    'TemplateLanguage' => true,
                    'Subject' => "Activation de votre compte",
                    'Variables' => [
                        "email_content" => $this->renderer->render(
                            // templates/emails/activation_email.html.twig
                            'emails/activation_email.html.twig', [
                                // On envoie le token au template
                                "token"=> $user->getActivationToken()
                            ]),
                        'text/html'
                    ]
                ]
            ]
        ];   
    
        // Envoi de l'email
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        //$response->success() && dd($response->getData());
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
        // On crée une nouvelle instance de MailJet avec les clés de l'API
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        
        // Configuration du corps de l'email
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "xav.david28@gmail.com",
                        'Name' => "Le site associatif"
                    ],
                    'To' => [
                        [
                        'Email' => $user->getEmail(),
                        'Name' => $user->getFirstName().' ' .$user->getLastName()
                        ]
                    ],
                    'TemplateID' => 1898813, // Id du modèle de template créé à partir de l'interface de MailJet
                    'TemplateLanguage' => true,
                    'Subject' => "Réinitialisation de votre mot de passe",
                    'Variables' => [
                        "email_content" => $this->renderer->render(
                            // templates/emails/reset_password_email.html.twig
                            'emails/reset_password_email.html.twig', [
                                // On envoie l'url de réinitialisation au template
                                "resetUrl"=> $resetUrl
                            ]),
                        'text/html'
                    ]
                ]
            ]
        ];   
    
        // Envoi de l'email
        $response = $mj->post(Resources::$Email, ['body' => $body]);
    }
}




