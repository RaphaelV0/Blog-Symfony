<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ArticleController extends AbstractController
{
    #[Route('/article/add', name: 'article_add')]
public function add(Request $request, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_USER');

    $article = new Article();
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $article->setDatePublication(new \DateTime());
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
        $commentaire->setDateCommentaire(new \DateTime());  // Ajouter la date du commentaire

        // Créer le formulaire pour le commentaire
        $form = $this->createForm(CommentaireType::class, $commentaire);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder le commentaire dans la base de données
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
    $this->denyAccessUnlessGranted('ROLE_USER'); // Vérifie que l'utilisateur est user

    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
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
    $this->denyAccessUnlessGranted('ROLE_USER');

    $entityManager->remove($article);
    $entityManager->flush();

    return $this->redirectToRoute('article_index');
}


}
