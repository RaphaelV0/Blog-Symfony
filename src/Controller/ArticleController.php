<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ArticleController extends AbstractController
{
    #[Route('/article/add', name: 'article_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Seuls les administrateurs peuvent ajouter des articles
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setDatePublication(new \DateTime());

            // Gérer l'upload de l'image si présente
            $file = $form->get('image')->getData();
            if ($file) {
                $newFilename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads_directory'), $newFilename);
                $article->setImage($newFilename);
            }

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/{id}', name: 'article_show')]
    public function show(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer un nouvel objet Commentaire
        $commentaire = new Commentaire();
        $commentaire->setArticle($article);  // Associer l'article au commentaire

        // Si l'utilisateur est connecté, associer l'utilisateur au commentaire
        if ($this->getUser()) {
            $commentaire->setUser($this->getUser());
        }

        // Créer le formulaire pour le commentaire
        $form = $this->createForm(CommentaireType::class, $commentaire);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajouter la date du commentaire
            $commentaire->setDateCommentaire(new \DateTime());
            $entityManager->persist($commentaire);
            $entityManager->flush();

            // Rediriger après la soumission
            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        // Passer l'article et le formulaire à la vue
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'articles' => $entityManager->getRepository(Article::class)->findAll(),
            'form' => $form->createView(),  // Passer le formulaire à la vue
        ]);
    }

    #[Route('/', name: 'article_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les articles
        $articles = $entityManager->getRepository(Article::class)->findAll();

        // Retourner les articles à la vue
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/article/{id}/edit', name: 'article_edit')]
    public function edit(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Seuls les administrateurs peuvent modifier des articles
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload de l'image si présente
            $file = $form->get('image')->getData();
            if ($file) {
                $newFilename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads_directory'), $newFilename);
                $article->setImage($newFilename);
            }

            $entityManager->flush();
            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/{id}/delete', name: 'article_delete')]
    public function delete(Article $article, EntityManagerInterface $entityManager): Response
    {
        // Seuls les administrateurs peuvent supprimer des articles
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('article_index');
    }

    // Route AJAX pour mettre à jour le titre ou le corps de l'article
    #[Route('/article/update', name: 'article_update', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $articleId = $request->get('id');
        $field = $request->get('field');
        $content = $request->get('content');

        $article = $entityManager->getRepository(Article::class)->find($articleId);

        if (!$article) {
            return new JsonResponse(['success' => false, 'message' => 'Article non trouvé'], 404);
        }

        // Mise à jour du champ spécifique (titre ou corps)
        if ($field === 'titre') {
            $article->setTitre($content);
        } elseif ($field === 'corps') {
            $article->setCorps($content);
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    // Route AJAX pour l'upload d'une image
    #[Route('/article/upload-image', name: 'article_upload_image', methods: ['POST'])]
    public function uploadImage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $articleId = $request->get('articleId');
        $file = $request->files->get('file');

        if ($file && $file->isValid()) {
            $article = $entityManager->getRepository(Article::class)->find($articleId);

            if (!$article) {
                return new JsonResponse(['success' => false, 'message' => 'Article non trouvé'], 404);
            }

            // Gérer l'upload de l'image
            $newFilename = uniqid() . '.' . $file->guessExtension();
            try {
                // Déplacez le fichier dans le répertoire d'uploads
                $file->move($this->getParameter('uploads_directory'), $newFilename);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'message' => 'Erreur lors de l\'upload de l\'image'], 500);
            }

            // Sauvegarder le nom de l'image dans l'article
            $article->setImage($newFilename);
            $entityManager->flush();

            return new JsonResponse(['success' => true, 'imageUrl' => '/uploads/images/' . $newFilename]);
        }

        return new JsonResponse(['success' => false, 'message' => 'Fichier non valide'], 400);
    }
}
