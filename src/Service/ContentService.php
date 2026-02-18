<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;

class ContentService
{
    private string $storageDir;
    private Filesystem $filesystem;

    public function __construct(string $projectDir)
    {
        $this->storageDir = $projectDir . '/var/content';
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->storageDir);

        $this->initializeFile('blog.json', [
            [
                'slug' => 'kak-podgotovit-sait-k-ai-poisku',
                'title' => 'Как подготовить сайт к AI-поиску',
                'excerpt' => 'Практический чеклист для индексации в поисковиках и AI-ассистентах.',
                'content' => "## Что важно\n\n1. Структурированные данные schema.org\n2. Качественные мета-теги\n3. Техническая доступность и sitemap.xml",
                'publishedAt' => (new \DateTimeImmutable('-2 days'))->format(DATE_ATOM),
            ],
        ]);

        $this->initializeFile('blocks.json', [
            [
                'id' => 'hero-red',
                'name' => 'Hero / Red CTA',
                'description' => 'Акцентный hero-блок с CTA-кнопкой.',
                'html' => '<section class="rounded-2xl bg-red-600 text-white p-8"><h2 class="text-3xl font-bold mb-2">Ваш рост в SEO и GEO</h2><p class="mb-4">Запускаем прозрачный маркетинг, который измеряется в лидах.</p><a class="inline-block bg-white text-red-600 font-semibold px-4 py-2 rounded" href="/contacts">Получить консультацию</a></section>',
                'css' => '.custom-hero-red{box-shadow:0 18px 35px rgba(220,38,38,.25)}',
            ],
        ]);

        $this->initializeFile('pages.json', [
            'home' => [
                [
                    'blockId' => 'hero-red',
                    'customHtml' => '',
                ],
            ],
            'about' => [],
            'contacts' => [],
        ]);
    }

    public function getPosts(): array
    {
        $posts = $this->readJson('blog.json');
        usort($posts, static fn(array $a, array $b): int => strcmp($b['publishedAt'], $a['publishedAt']));

        return $posts;
    }

    public function getPostBySlug(string $slug): ?array
    {
        foreach ($this->getPosts() as $post) {
            if ($post['slug'] === $slug) {
                return $post;
            }
        }

        return null;
    }

    public function upsertPost(array $post): void
    {
        $posts = $this->readJson('blog.json');
        $updated = false;

        foreach ($posts as $index => $item) {
            if ($item['slug'] === $post['slug']) {
                $posts[$index] = $post;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $posts[] = $post;
        }

        $this->writeJson('blog.json', $posts);
    }

    public function getBlocks(): array
    {
        return $this->readJson('blocks.json');
    }

    public function getBlockById(string $id): ?array
    {
        foreach ($this->getBlocks() as $block) {
            if ($block['id'] === $id) {
                return $block;
            }
        }

        return null;
    }

    public function upsertBlock(array $block): void
    {
        $blocks = $this->readJson('blocks.json');
        $updated = false;

        foreach ($blocks as $index => $item) {
            if ($item['id'] === $block['id']) {
                $blocks[$index] = $block;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $blocks[] = $block;
        }

        $this->writeJson('blocks.json', $blocks);
    }

    public function getPageBlocks(string $page): array
    {
        $pages = $this->readJson('pages.json');

        return $pages[$page] ?? [];
    }

    public function savePageBlocks(string $page, array $items): void
    {
        $pages = $this->readJson('pages.json');
        $pages[$page] = $items;
        $this->writeJson('pages.json', $pages);
    }

    private function initializeFile(string $file, array $defaultData): void
    {
        $path = $this->storageDir . '/' . $file;
        if (!$this->filesystem->exists($path)) {
            $this->writeJson($file, $defaultData);
        }
    }

    private function readJson(string $file): array
    {
        $path = $this->storageDir . '/' . $file;
        $content = @file_get_contents($path);

        if ($content === false) {
            return [];
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function writeJson(string $file, array $data): void
    {
        file_put_contents(
            $this->storageDir . '/' . $file,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
