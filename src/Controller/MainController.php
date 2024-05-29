<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll(); // Récupérer tous les articles depuis la base de données

        return $this->render('main/index.html.twig', [
            'articles' => $articles
        ]);
    }
}
