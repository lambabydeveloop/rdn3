<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findBy(['status' => 'published'], ['createdAt' => 'DESC']);
        
        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_blog_show')]
    public function show(string $slug, EntityManagerInterface $em): Response
    {
        $article = $em->getRepository(Article::class)->findOneBy(['slug' => $slug]);

        if (!$article) {
            throw $this->createNotFoundException('Статья не найдена');
        }

        // If it's a draft, only allow viewing by admins
        if ($article->getStatus() !== 'published' && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException('Статья пока не опубликована');
        }

        // Fetch recent articles for the sidebar tree, excluding the current one
        $recentArticles = $em->getRepository(Article::class)->findBy(
            ['status' => 'published'],
            ['createdAt' => 'DESC'],
            5
        );
        $recentArticles = array_filter($recentArticles, fn($a) => $a->getId() !== $article->getId());

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'recentArticles' => array_slice($recentArticles, 0, 4),
        ]);
    }
}
