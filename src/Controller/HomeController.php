<?php

namespace App\Controller;

use App\Service\Mail;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * Contrôle l'affichage de la page d'accueil du site
     * @Route("/", name="home")
     */
    public function index(Mail $mail)
    {

        return $this->render('home/index.html.twig', [   
        ]);
    }
}
