<?php

namespace App\Service;

use Symfony\Component\Routing\RouterInterface;

class SeoService
{
    public function __construct(
        private RouterInterface $router,
        private ContentService $contentService,
    ) {}

    public function getSitemapUrls(): array
    {
        $urls = [];

        $staticRoutes = [
            'app_home' => [],
            'app_about' => [],
            'app_contacts' => [],
            'app_services' => [],
            'app_blog_index' => [],
        ];

        foreach ($staticRoutes as $name => $params) {
            $urls[] = [
                'loc' => $this->router->generate($name, $params, RouterInterface::ABSOLUTE_URL),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        foreach (['seo', 'geo', 'analytics', 'ads', 'razrabotka'] as $slug) {
            $urls[] = [
                'loc' => $this->router->generate('app_service_show', ['slug' => $slug], RouterInterface::ABSOLUTE_URL),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        foreach ($this->contentService->getPosts() as $post) {
            $urls[] = [
                'loc' => $this->router->generate('app_blog_post', ['slug' => $post['slug']], RouterInterface::ABSOLUTE_URL),
                'lastmod' => (new \DateTimeImmutable($post['publishedAt']))->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        return $urls;
    }

    public function getRobotsTxt(string $sitemapUrl): string
    {
        return <<<TXT
User-agent: *
Allow: /

# Поисковые системы
User-agent: Googlebot
Allow: /

User-agent: Yandex
Allow: /

User-agent: Bingbot
Allow: /

# AI-краулеры
User-agent: GPTBot
Allow: /

User-agent: ChatGPT-User
Allow: /

User-agent: OAI-SearchBot
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: PerplexityBot
Allow: /

Sitemap: {$sitemapUrl}
TXT;
    }
}
