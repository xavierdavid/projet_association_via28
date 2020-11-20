<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Form\PostFormType;
use App\Form\CommentFormType;
use App\Repository\PostRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{

    protected $slugger;
    protected $fileUploader;
    
    /**
     * Constructeur de classe permettant de récupérer et d'initialiser les service SluggerInterface et FileUploader
     *
     * @param SluggerInterface $slugger
     * @param FileUploader $fileUploader
     */
    public function __construct(SluggerInterface $slugger, FileUploader $fileUploader)
    {
        $this->slugger = $slugger; 
        $this->fileUploader = $fileUploader;
    }
    
    /**
     * Contrôle l'affichage et le traitement du formulaire de création d'un post
     * @Route("/account/post/new", name="post_new")
     * @return void
     */
    public function new(Request $request, EntityManagerInterface $manager)
    {
        // Récupération de l'utilisateur authentifié
        $user= $this->getUser();
        // Création d'une nouvelle instance de l'objet Post
        $post = new Post();
        // Création du formulaire de création de post 
        $form = $this->createform(PostFormType::class, $post);
        // Récupération de la requête
        $form->handleRequest($request);
        // Traitement du formulaire
        if($form->isSubmitted() && $form->isValid()) {
            // On génère la date de création de la publication - Hydratation de la propriété 'created_at'
            $post->setCreatedAt(new \DateTime());
            // On génère la date de mise à jour - Hydratation de la propriété 'updated_at'
            $post->setUpdatedAt(new \DateTime());
            // On génère le slug à l'aide du service SluggerInterface - Hydratation de la propriété 'slug'
            $post->setSlug(strtolower($this->slugger->slug($post->getTitle())));
            // On affecte l'utilisateur authentifié au post - Hydratation de la propriété 'user'
            $post->setUser($user);
            // Récupération du fichier image téléchargé dans le formulaire dans le champ 'cover_image'
            $imageFile = $form['cover_image']->getData();
            // Si un fichier image est présent (Rappel : le champ est facultatif)...
            if($imageFile) {
                // On utilise le service FileUploader pour uploader le fichier vers le répertoire de stockage
                $newFilename = $this->fileUploader->upload($imageFile);
                // On met à jour la propriété imageFilename de l'entite Post pour stocker le nom du fichier dans la base de données - Hydratation de la propriété 'cover_image'
                $post->setCoverImage($newFilename); 
            }
            // On enregistre l'entité $post à l'aide du manager de Doctrine
            $manager->persist($post);

            // On demande au manager d'exécuter la requête ('INSERT INTO')
            $manager->flush();

             // Envoi d'un message flash de success
             $this->addFlash('message', 'Votre publication a été créée avec succès !');

             // On redirige vers la liste des publications
             return $this->redirectToRoute('post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'postForm' => $form->createView() // Envoi du formulaire de création de post à la vue
        ]);
    }

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

        // Récupération de l'utilisateur authentifié
        $user = $this->getUser();

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
            $comment->setCreatedAt(new \DateTime());
            // L'activation du commentaire est défini automatiquement par défaut dans l'entité
            // On hydrate notre objet à l'aide du manager de Doctrine et on envoi les données dans la base 
            $manager->persist($comment);
            $manager->flush();

            // Envoi d'un message flash de confirmation
            $this->addFlash('message', 'Votre commentaire a bien été ajouté !');
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentForm' => $form->createView() // Envoi du formulaire d'ajout de commentaire à la vue
        ]);
    }
}
