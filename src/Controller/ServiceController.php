<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServiceController extends AbstractController
{
    private array $services = [
        'seo' => [
            'title' => 'SEO-продвижение сайтов в Минске и РБ | Комплексное SEO под ключ',
            'description' => 'Профессиональное SEO-продвижение сайтов в Беларуси. Выводим в ТОП-10 Яндекс и Google. Оплата за результат. Честные кейсы и прозрачная отчетность.',
            'template' => 'pages/services/seo/seomain.html.twig',
        ],
        'geo' => [
            'title' => 'GEO-продвижение для ИИ | Оптимизация под ChatGPT, YandexGPT, Perplexity, Gemini',
            'description' => ' Профессиональная GEO-оптимизация вашего сайта для генеративных нейросетей. Делаем ваш контент источником ответов для ИИ и привлекаем клиентов из нового поиска.',
            'template' => 'pages/services/geo/geomain.html.twig',
        ],
        'analytics' => [
            'title' => 'Веб-аналитика Яндекс и Google | Аудит, настройка, сквозная аналитика',
            'description' => 'Профессиональная веб-аналитика под ключ. Аудит счетчиков, настройка целей, тепловые карты, сквозная аналитика и A/B тесты. Превращаем данные в прибыль.',
            'template' => 'pages/services/analytics/mainanalitycs.html.twig',
        ],
        'ads' => [
            'title' => 'Контекстная реклама в Яндекс.Директ и Google Ads | Профессиональное управление, создание с нуля, доработки и исправления',
            'description' => 'Профессиональное управление контекстной рекламой в Яндекс.Директ и Google Ads. Аудит кампаний, семантика, A/B тесты, снижение стоимости лида. Реальные кейсы и прозрачная отчетность.',
            'template' => 'pages/services/ads/adsmain.html.twig',
        ],
        'razrabotka' => [
            'title' => 'Разработка сайтов под ключ | CMS и Symfony | Студия веб-разработки',
            'description' => 'Профессиональная разработка сайтов в Минске и Беларуси. Лендинги, интернет-магазины, корпоративные порталы на CMS или Symfony. Индивидуальный подход, прозрачные цены и экспертная поддержка.',
            'template' => 'pages/services/develop_sites/maindevelop.html.twig',
        ]
    ];

    #[Route('/services', name: 'app_services')]
    public function index(): Response
    {
        return $this->render('pages/services/index.html.twig', [
            'services' => $this->services,
            'seo' => [
                'title' => 'Все услуги digital-агентства | SEO, GEO, аналитика, реклама, разработка',
                'description' => 'Комплексный подход к развитию вашего бизнеса в интернете: SEO-продвижение, GEO-оптимизация для ИИ, веб-аналитика, контекстная реклама и разработка сайтов под ключ.',
                'canonical' => $this->generateUrl('app_services', [], true),
            ]
        ]);
    }

    #[Route('/services/{slug}', name: 'app_service_show')]
    public function show(string $slug): Response
    {
        if (!isset($this->services[$slug])) {
            throw $this->createNotFoundException('Услуга не найдена');
        }

        $service = $this->services[$slug];

        return $this->render($service['template'], [
            'service' => $service,
            'slug' => $slug,
            'seo' => [
                'title' => $service['title'],
                'description' => $service['description'],
                'canonical' => $this->generateUrl('app_service_show', ['slug' => $slug], true),
            ]
        ]);
    }
}
