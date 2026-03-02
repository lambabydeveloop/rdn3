<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Promo;
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

        // 1. Fetch all categories with their published articles for the Left Sidebar Tree
        $allCategories = $em->getRepository(Category::class)->findAll();
        $categoriesTree = [];
        foreach ($allCategories as $category) {
            $catArticles = [];
            foreach ($category->getArticles() as $catArt) {
                if ($catArt->getStatus() === 'published') {
                    $catArticles[] = $catArt;
                }
            }
            if (count($catArticles) > 0) {
                usort($catArticles, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
                $categoriesTree[] = [
                    'category' => $category,
                    'articles' => array_slice($catArticles, 0, 10)
                ];
            }
        }

        // 2. Fetch Recommended Articles (from the same categories)
        $recommendedArticles = [];
        $articleCategoryIds = array_map(fn($c) => $c->getId(), $article->getCategories()->toArray());
        
        if (!empty($articleCategoryIds)) {
            $qb = $em->getRepository(Article::class)->createQueryBuilder('a')
                ->join('a.categories', 'c')
                ->where('c.id IN (:cats)')
                ->andWhere('a.id != :id')
                ->andWhere('a.status = :status')
                ->setParameter('cats', $articleCategoryIds)
                ->setParameter('id', $article->getId())
                ->setParameter('status', 'published')
                ->orderBy('a.createdAt', 'DESC')
                ->setMaxResults(3);
                
            $recommendedArticles = $qb->getQuery()->getResult();
        }

        // 3. Fetch Active Promos
        $promos = $em->getRepository(Promo::class)->findBy(['isActive' => true], null, 5);

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'categoriesTree' => $categoriesTree,
            'recommendedArticles' => $recommendedArticles,
            'promos' => $promos,
        ]);
    }
}
