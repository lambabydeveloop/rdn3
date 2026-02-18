<?php

namespace App\Controller;

use App\Service\ContentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/blog')]
class BlogController extends AbstractController
{
    public function __construct(private ContentService $contentService) {}

    #[Route('', name: 'app_blog_index')]
    public function index(): Response
    {
        $posts = $this->contentService->getPosts();

        return $this->render('blog/index.html.twig', [
            'posts' => $posts,
            'seo' => [
                'title' => 'Блог RDN.BY: SEO, GEO и AI Search',
                'description' => 'Практические статьи по SEO, индексированию и AI-поиску.',
                'canonical' => $this->generateUrl('app_blog_index', [], true),
                'schema_type' => 'Blog',
            ],
        ]);
    }

    #[Route('/{slug}', name: 'app_blog_post')]
    public function show(string $slug): Response
    {
        $post = $this->contentService->getPostBySlug($slug);
        if (!$post) {
            throw $this->createNotFoundException('Статья не найдена.');
        }

        return $this->render('blog/post.html.twig', [
            'post' => $post,
            'seo' => [
                'title' => $post['title'],
                'description' => $post['excerpt'],
                'canonical' => $this->generateUrl('app_blog_post', ['slug' => $slug], true),
                'schema_type' => 'BlogPosting',
            ],
        ]);
    }
}
