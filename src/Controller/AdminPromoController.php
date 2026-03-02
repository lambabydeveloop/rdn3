<?php

namespace App\Controller;

use App\Entity\Promo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/promo')]
class AdminPromoController extends AbstractController
{
    #[Route('', name: 'app_admin_promo_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $promos = $em->getRepository(Promo::class)->findAll();
        return $this->render('admin/promo/index.html.twig', [
            'promos' => $promos,
        ]);
    }

    #[Route('/create', name: 'app_admin_promo_create')]
    #[Route('/edit/{id}', name: 'app_admin_promo_edit')]
    public function createOrEdit(Request $request, EntityManagerInterface $em, ?Promo $promo = null): Response
    {
        if ($request->attributes->get('_route') === 'app_admin_promo_edit' && !$promo) {
            throw $this->createNotFoundException('Promo not found');
        }

        if (!$promo) {
            $promo = new Promo();
        }

        if ($request->isMethod('POST')) {
            $promo->setTitle($request->request->get('title'));
            $promo->setDescription($request->request->get('description'));
            $promo->setLabel($request->request->get('label'));
            $promo->setUrl($request->request->get('url'));
            $promo->setActionText($request->request->get('actionText'));
            $promo->setStyleName($request->request->get('styleName', 'dark'));
            $promo->setIsActive($request->request->has('isActive'));

            $em->persist($promo);
            $em->flush();

            return $this->redirectToRoute('app_admin_promo_index');
        }

        return $this->render('admin/promo/create.html.twig', [
            'promo' => $promo,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_promo_delete', methods: ['POST'])]
    public function delete(Promo $promo, EntityManagerInterface $em): Response
    {
        $em->remove($promo);
        $em->flush();
        return $this->redirectToRoute('app_admin_promo_index');
    }
}
