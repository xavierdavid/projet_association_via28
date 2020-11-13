<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    /**
     * Contrôle la récupération et l'affichage de tous les posts publiés sur l'espace utilisateur
     * @Route("/account/post", name="post_index")
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

        // Envoi des données à la vue
        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * Gère l'affichage de la page d'un post ainsi que le traitement du formulaire d'ajout de commentaire rattaché au post
     * @Route("account/post/{slug}", name="post_show")
     * @param [type] $slug
     * @return void
     */
    public function show($slug, PostRepository $repository, Request $request, EntityManagerInterface $manager)
    {
        // Récupération du post à partir du Repository des Posts
        $post = $repository->findOneBy([
            'slug' => $slug
        ]);

        // Récupération de l'utilisateur ayant publié le post
        $user = $post->getUser();

        // Si le post n'existe pas 
        if(!$post) {
            // On lève une exception
            throw $this->createNotFoundException('Le post recherché n\'existe pas !');
        }

        // On créé une instance de l'entité Comment 
        $comment = new Comment();

        // On crée le formulaire d'ajout de commentaire
        $form = $this->createForm(CommentFormType::class, $comment);

        // Récupération des données transmises avec la requête via le formulaire
        $form->handleRequest($request);
        // Vérification de la soumission et de la validité des données, puis envoi dans la base 
        if ($form->isSubmitted() && $form->isValid()) {
            // On gère les champs non gérés par le formulaire
            // On affecte le nouveau commentaire au post 
            $comment->setPost($post);
            // On affecte le nouveau commentaire à l'utilisateur
            $comment->setUser($user);
            // On crée génère la date de création du commentaire
            $comment->setCreatedAt(new \DateTime('now'));
            // L'activation du commentaire est défini automatiquement par défaut dans l'entité
            // On hydrate notre objet à l'aide du manager de Doctrine et on envoi les données dans la base 
            $manager->persist($comment);
            $manager->flush();

            // Envoi d'un message flash de confirmation
            $this->addFlash('message', 'Votre commentaire a bien été ajouté !');

            // On redirige vers la page de login
            //return $this->redirectToRoute('app_login');
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentForm' => $form->createView() // Envoi du formulaire d'ajout de commentaire à la vue
        ]);
    }
}
