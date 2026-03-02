<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Service\ImageProcessingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/blog')]
class AdminBlogController extends AbstractController
{
    #[Route('', name: 'app_admin_blog_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->render('admin/blog/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/create', name: 'app_admin_blog_create')]
    public function create(EntityManagerInterface $em): Response
    {
        return $this->render('admin/blog/create.html.twig', [
            'categories' => $em->getRepository(Category::class)->findAll(),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_blog_edit', requirements: ['id' => '\d+'])]
    public function edit(Article $article, EntityManagerInterface $em): Response
    {
        return $this->render('admin/blog/create.html.twig', [
            'article' => $article,
            'categories' => $em->getRepository(Category::class)->findAll(),
        ]);
    }

    #[Route('/save', name: 'app_admin_blog_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['title'])) {
            return new JsonResponse(['success' => 0, 'message' => 'Invalid data'], 400);
        }

        $id = $data['id'] ?? null;
        if ($id) {
            $article = $em->getRepository(Article::class)->find($id);
            if (!$article) {
                return new JsonResponse(['success' => 0, 'message' => 'Article not found'], 404);
            }
        } else {
            $article = new Article();
        }

        $title = $data['title'];
        $slug = $data['slug'] ?: strtolower($slugger->slug($title)->toString());

        // Check unique slug logic
        $existing = $em->getRepository(Article::class)->findOneBy(['slug' => $slug]);
        if ($existing && $existing->getId() !== $article->getId()) {
            $slug .= '-' . uniqid();
        }

        $article->setTitle($title);
        $article->setSlug($slug);
        $article->setContent($data['content'] ?? []);
        $article->setSeoTitle($data['seoTitle'] ?? null);
        $article->setSeoDescription($data['seoDescription'] ?? null);
        $article->setCoverImage($data['coverImage'] ?? null);
        
        $status = $data['status'] ?? 'draft';
        $article->setStatus($status);

        $article->getCategories()->clear();
        if (!empty($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $catId) {
                $category = $em->getRepository(Category::class)->find($catId);
                if ($category) {
                    $article->addCategory($category);
                }
            }
        }

        $em->persist($article);
        $em->flush();

        // Handle Sitemap is now dynamic via SitemapController

        return new JsonResponse([
            'success' => 1,
            'id' => $article->getId(),
            'slug' => $article->getSlug(),
            'message' => 'Saved successfully'
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_blog_delete', methods: ['POST'])]
    public function delete(Article $article, Request $request, EntityManagerInterface $em): JsonResponse
    {

        $em->remove($article);
        $em->flush();

        return new JsonResponse(['success' => 1]);
    }

    #[Route('/upload-image', name: 'app_admin_blog_upload_image', methods: ['POST'])]
    public function uploadImage(Request $request, ImageProcessingService $imageService): JsonResponse
    {
        $file = $request->files->get('image'); // Editor.js sends it as 'image'
        if (!$file) {
            return new JsonResponse(['success' => 0, 'message' => 'No file provided'], 400);
        }

        try {
            $path = $imageService->processAndUpload($file);
            return new JsonResponse([
                'success' => 1,
                'file' => [
                    'url' => $path
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => 0, 'message' => $e->getMessage()], 500);
        }
    }
}
