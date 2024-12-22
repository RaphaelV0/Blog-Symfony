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
        $article = $entityManager->getRepository(Article::class)->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

        $commentaire = new Commentaire();
        $commentaire->setArticle($article);

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setDateCommentaire(new \DateTime());
            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');

            return $this->redirectToRoute('article_show', ['id' => $articleId]);
        }

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
