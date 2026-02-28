<?php

namespace App\Controller;

use App\Entity\Article;
use App\Controller\ServiceController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function index(EntityManagerInterface $em, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $urls = [];
        $hostname = $request->getSchemeAndHttpHost();
        
        // Strip trailing slash if present
        $hostname = rtrim($hostname, '/');

        // Static routes
        $urls[] = ['loc' => $this->generateUrl('app_home'), 'changefreq' => 'weekly', 'priority' => '1.0'];
        $urls[] = ['loc' => $this->generateUrl('app_about'), 'changefreq' => 'monthly', 'priority' => '0.8'];
        $urls[] = ['loc' => $this->generateUrl('app_services'), 'changefreq' => 'monthly', 'priority' => '0.8'];
        $urls[] = ['loc' => $this->generateUrl('app_contacts'), 'changefreq' => 'monthly', 'priority' => '0.8'];
        $urls[] = ['loc' => $this->generateUrl('app_blog_index'), 'changefreq' => 'weekly', 'priority' => '0.8'];

        // Service routes
        foreach (ServiceController::SERVICES as $slug => $service) {
            $urls[] = [
                'loc' => $this->generateUrl('app_service_show', ['slug' => $slug]),
                'changefreq' => 'monthly',
                'priority' => '0.9'
            ];
        }

        // Blog articles (Published only)
        $articles = $em->getRepository(Article::class)->findBy(['status' => 'published']);
        foreach ($articles as $article) {
            $urls[] = [
                'loc' => $this->generateUrl('app_blog_show', ['slug' => $article->getSlug()]),
                'lastmod' => $article->getUpdatedAt() ? $article->getUpdatedAt()->format('Y-m-d') : $article->getCreatedAt()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.7'
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . $hostname . $url['loc'] . '</loc>';
            if (isset($url['lastmod'])) {
                $xml .= '<lastmod>' . $url['lastmod'] . '</lastmod>';
            }
            if (isset($url['changefreq'])) {
                $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
            }
            if (isset($url['priority'])) {
                $xml .= '<priority>' . $url['priority'] . '</priority>';
            }
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return new Response($xml, 200, [
            'Content-Type' => 'text/xml'
        ]);
    }
}
