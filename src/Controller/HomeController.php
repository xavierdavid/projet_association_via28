<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Contrôle l'affichage de la page d'accueil du site
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [   
        ]);
    }
}
