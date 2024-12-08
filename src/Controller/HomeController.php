<?php
namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Récupérer le dernier article publié
        $lastArticle = $articleRepository->findOneBy(
            ['etatPublication' => true], // Articles publiés
            ['datePublication' => 'DESC'] // Trier par date de publication, du plus récent
        );

        // Récupérer les 5 derniers articles récents
        $recentArticles = $articleRepository->findBy(
            ['etatPublication' => true], // Articles publiés
            ['datePublication' => 'DESC'], // Trier par date
            5 // Limiter à 5 articles
        );

        return $this->render('home/index.html.twig', [
            'lastArticle' => $lastArticle, // Dernier article
            'recentArticles' => $recentArticles, // Articles récents
        ]);
    }
}
