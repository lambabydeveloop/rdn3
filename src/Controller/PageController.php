<?php

namespace App\Controller;

use App\Service\ContentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    public function __construct(private ContentService $contentService)
    {
    }

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('pages/home.html.twig', [
            'seo' => [
                'title' => 'RDN.BY - студия продвижения Вашего сайта и бизнеса',
                'description' => 'SEO, GEO, реклама и аналитика для роста посещаемости сайтов',
                'canonical' => $this->generateUrl('app_home', [], true),
            ],
            'managed_blocks' => $this->hydrateBlocks('home'),
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig', [
            'seo' => [
                'title' => 'О студии RDN.BY',
                'description' => 'Подход, философия и экспертиза',
                'canonical' => $this->generateUrl('app_about', [], true),
            ],
            'managed_blocks' => $this->hydrateBlocks('about'),
        ]);
    }

    #[Route('/contacts', name: 'app_contacts')]
    public function contacts(): Response
    {
        return $this->render('pages/contacts.html.twig', [
            'seo' => [
                'title' => 'Контакты digital-агентства RDN.BY | Связаться с нами',
                'description' => 'Наши контакты: адрес офиса в Минске, телефоны, email, реквизиты. Свяжитесь с нами для консультации по SEO, GEO, аналитике, рекламе и разработке.',
                'canonical' => $this->generateUrl('app_contacts', [], true),
            ],
            'managed_blocks' => $this->hydrateBlocks('contacts'),
        ]);
    }

    private function hydrateBlocks(string $page): array
    {
        $resolved = [];

        foreach ($this->contentService->getPageBlocks($page) as $item) {
            $block = isset($item['blockId']) ? $this->contentService->getBlockById((string)$item['blockId']) : null;
            $resolved[] = [
                'name' => $block['name'] ?? $item['blockId'] ?? 'Custom',
                'html' => $item['customHtml'] ?: ($block['html'] ?? ''),
                'css' => $block['css'] ?? '',
            ];
        }

        return $resolved;
    }

}
