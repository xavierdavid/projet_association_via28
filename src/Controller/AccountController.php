<?php

namespace App\Controller;


use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountController extends AbstractController
{
    
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
