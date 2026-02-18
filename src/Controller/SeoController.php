<?php

namespace App\Controller;

use App\Service\SeoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SeoController extends AbstractController
{
    public function __construct(private SeoService $seoService) {}

    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function sitemap(): Response
    {
        $xml = $this->renderView('seo/sitemap.xml.twig', [
            'urls' => $this->seoService->getSitemapUrls(),
        ]);

        return new Response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    #[Route('/robots.txt', name: 'app_robots')]
    public function robots(): Response
    {
        $content = $this->seoService->getRobotsTxt($this->generateUrl('app_sitemap', [], true));

        return new Response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
