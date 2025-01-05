<?php
namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Article;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaireController extends AbstractController
{
    #[Route('/comment/{articleId}/add', name: 'comment_add')]
    public function add(int $articleId, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'article par son ID
        $article = $entityManager->getRepository(Article::class)->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

        // Vérifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'utilisateur a le rôle ROLE_USER ou si c'est l'auteur de l'article avec le rôle ROLE_ADMIN
        $user = $this->getUser();

        // Si l'utilisateur n'est pas un ROLE_USER et que ce n'est pas l'auteur de l'article
        // ou qu'il n'a pas le rôle ROLE_ADMIN, alors interdire l'ajout de commentaire
        if (!$user->hasRole('ROLE_USER') && ($user->getId() !== $article->getAuthor()->getId() || !$user->hasRole('ROLE_ADMIN'))) {
            $this->addFlash('error', 'Vous devez être connecté en tant qu\'utilisateur ou administrateur pour commenter cet article.');
            return $this->redirectToRoute('article_show', ['id' => $articleId]);
        }

        // Créer un nouveau commentaire
        $commentaire = new Commentaire();
        $commentaire->setArticle($article);
        $commentaire->setUser($this->getUser()); // Associer le commentaire à l'utilisateur connecté

        // Créer et traiter le formulaire de commentaire
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajouter la date du commentaire
            $commentaire->setDateCommentaire(new \DateTime());

            // Persister et sauvegarder le commentaire en base de données
            $entityManager->persist($commentaire);
            $entityManager->flush();

            // Ajouter un message flash pour informer l'utilisateur
            $this->addFlash('success', 'Commentaire ajouté avec succès.');

            // Rediriger vers la page de l'article
            return $this->redirectToRoute('article_show', ['id' => $articleId]);
        }

        // Afficher le formulaire d'ajout de commentaire
        return $this->render('comment/add.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/comment/{id}/edit', name: 'comment_edit')]
    public function edit(Commentaire $commentaire, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire modifié avec succès.');

            return $this->redirectToRoute('article_show', ['id' => $commentaire->getArticle()->getId()]);
        }

        return $this->render('comment/edit.html.twig', [
            'form' => $form->createView(),
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/comment/{id}/delete', name: 'comment_delete')]
    public function delete(Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $articleId = $commentaire->getArticle()->getId();

        $entityManager->remove($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire supprimé avec succès.');

        return $this->redirectToRoute('article_show', ['id' => $articleId]);
    }
}
