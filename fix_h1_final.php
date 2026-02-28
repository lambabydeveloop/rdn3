<?php

$files = [
    'templates/pages/services/index.html.twig',
    'templates/pages/services/ads/adsmain.html.twig',
    'templates/pages/services/analytics/mainanalitycs.html.twig',
    'templates/pages/services/develop_sites/maindevelop.html.twig',
    'templates/pages/services/geo/geomain.html.twig',
    'templates/pages/services/seo/seomain.html.twig',
    'templates/pages/about.html.twig',
    'templates/pages/contacts.html.twig',
    'templates/blog/index.html.twig'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        $content = preg_replace(
            '/class="text-\[clamp\(.*?\)\] (.*?)"/',
            'class="text-5xl md:text-9xl max-w-[100vw] overflow-wrap-anywhere $1"',
            $content
        );
        
        // Remove 'leading-[0.85]' and replace it with 'leading-none' to match original
        $content = str_replace('leading-[0.85]', 'leading-none', $content);

        file_put_contents($file, $content);
        echo "Fixed $file\n";
    }
}
