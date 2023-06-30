<?php

namespace App\Controller;

use jcobhams\NewsApi\NewsApi;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NewsController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function index(): Response
    {
        $newsapi = new NewsApi('a0a542601da24e418202c6a891a7c2ff');

        $response = $newsapi->getEverything('hôtellerie', null, null, null, null, null, 'fr', 'publishedAt', 15, null);

        $articles = $response->articles;

        // dd($articles);

        return $this->render('news/index.html.twig', [
            'controller_name' => 'NewsController',
            'articles' => $articles
        ]);
    }
}
