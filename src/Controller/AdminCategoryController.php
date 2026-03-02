<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/category')]
class AdminCategoryController extends AbstractController
{
    #[Route('', name: 'app_admin_category_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Category::class)->findAll();
        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/create', name: 'app_admin_category_create')]
    #[Route('/edit/{id}', name: 'app_admin_category_edit')]
    public function createOrEdit(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, ?Category $category = null): Response
    {
        if ($request->attributes->get('_route') === 'app_admin_category_edit' && !$category) {
            throw $this->createNotFoundException('Category not found');
        }

        if (!$category) {
            $category = new Category();
        }

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $category->setName($name);
            $slug = $request->request->get('slug');
            if (!$slug) {
                $slug = strtolower($slugger->slug($name)->toString());
            }
            $category->setSlug($slug);
            $category->setColor($request->request->get('color', '#6366f1'));

            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('app_admin_category_index');
        }

        return $this->render('admin/category/create.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, EntityManagerInterface $em): Response
    {
        $em->remove($category);
        $em->flush();
        return $this->redirectToRoute('app_admin_category_index');
    }
}
