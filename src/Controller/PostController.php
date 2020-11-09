<?php

namespace App\Controller;

use App\Entity\Post;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    /**
     * Contrôle la récupération et l'affichage de tous les posts publiés sur l'espace utilisateur
     * @Route("/account/post", name="post")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        // Récupération les données des posts, par date de création et tri décroissant à l'aide de l'ORM doctrine et du repository des posts
        $data = $this->getDoctrine()->getRepository(Post::class)->findBy([], ['created_at' => 'desc']);

        // On remplit la variable 'posts' avec les données de la page que l'on souhaite récupérer en utilisant le paginator
        $posts = $paginator->paginate(
            $data, // On passe les données
            $request->query->getInt('page', 1), // Numéro de la page en cours (1 par défaut)
            2 // Nombre de posts par page 'ici 2 posts par page'
        );

        // Affichage test des données provenant de la base de données et stockées dans la variable $posts
        //dd($posts);

        // En voi des données à la vue
        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }
}
