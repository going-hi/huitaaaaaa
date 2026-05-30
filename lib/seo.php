<?php

declare(strict_types=1);

/** Базовый URL сайта (prod: задайте MB_SITE_URL=https://example.com). */
function mb_site_base_url(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $env = getenv('MB_SITE_URL');
    if (is_string($env) && $env !== '') {
        return $cached = rtrim($env, '/');
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));

    return $cached = ($https ? 'https' : 'http') . '://' . $host;
}

function mb_seo_absolute_url(string $path = '/'): string
{
    if ($path === '' || $path === '/') {
        return mb_site_base_url() . '/';
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    return mb_site_base_url() . '/' . ltrim($path, '/');
}

/**
 * @param array{
 *   title: string,
 *   description: string,
 *   path?: string,
 *   image?: string,
 *   type?: string,
 *   keywords?: list<string>,
 *   robots?: string,
 *   json_ld?: list<array<string,mixed>>,
 *   og_locale?: string
 * } $config
 */
function mb_seo_render_head(array $config): void
{
    $title = trim($config['title']);
    $description = trim($config['description']);
    $path = $config['path'] ?? '/';
    $type = $config['type'] ?? 'website';
    $robots = $config['robots'] ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    $locale = $config['og_locale'] ?? 'ru_RU';
    $canonical = mb_seo_absolute_url($path);
    $image = isset($config['image']) && $config['image'] !== ''
        ? mb_seo_absolute_url($config['image'])
        : mb_seo_absolute_url('logo.png');

    echo '  <title>' . mb_h($title) . "</title>\n";
    echo '  <meta name="description" content="' . mb_h($description) . "\">\n";
    if (!empty($config['keywords'])) {
        echo '  <meta name="keywords" content="' . mb_h(implode(', ', $config['keywords'])) . "\">\n";
    }
    echo '  <meta name="robots" content="' . mb_h($robots) . "\">\n";
    echo '  <meta name="author" content="MindBase, ООО «Инним»">' . "\n";
    echo '  <meta name="theme-color" content="#0f0f12">' . "\n";
    echo '  <link rel="canonical" href="' . mb_h($canonical) . "\">\n";
    echo '  <link rel="alternate" hreflang="ru" href="' . mb_h($canonical) . "\">\n";
    echo '  <link rel="alternate" hreflang="x-default" href="' . mb_h($canonical) . "\">\n";

    echo '  <meta property="og:site_name" content="MindBase">' . "\n";
    echo '  <meta property="og:locale" content="' . mb_h($locale) . "\">\n";
    echo '  <meta property="og:type" content="' . mb_h($type) . "\">\n";
    echo '  <meta property="og:title" content="' . mb_h($title) . "\">\n";
    echo '  <meta property="og:description" content="' . mb_h($description) . "\">\n";
    echo '  <meta property="og:url" content="' . mb_h($canonical) . "\">\n";
    echo '  <meta property="og:image" content="' . mb_h($image) . "\">\n";
    echo '  <meta property="og:image:alt" content="MindBase — платформа базы знаний">' . "\n";

    echo '  <meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '  <meta name="twitter:title" content="' . mb_h($title) . "\">\n";
    echo '  <meta name="twitter:description" content="' . mb_h($description) . "\">\n";
    echo '  <meta name="twitter:image" content="' . mb_h($image) . "\">\n";

    if (!empty($config['json_ld'])) {
        $graphs = $config['json_ld'];
        if (count($graphs) === 1) {
            $payload = array_merge(['@context' => 'https://schema.org'], $graphs[0]);
        } else {
            $payload = ['@context' => 'https://schema.org', '@graph' => $graphs];
        }
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        echo '  <script type="application/ld+json">' . $json . "</script>\n";
    }
}

/** @return list<array<string,mixed>> JSON-LD для главной страницы. */
function mb_seo_landing_json_ld(string $pageUrl, array $faqItems): array
{
    $orgId = $pageUrl . '#organization';
    $websiteId = $pageUrl . '#website';
    $appId = $pageUrl . '#software';

    $faqEntities = [];
    foreach ($faqItems as $item) {
        $faqEntities[] = [
            '@type' => 'Question',
            'name' => $item['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $item['answer'],
            ],
        ];
    }

    return [
        [
            '@type' => 'Organization',
            '@id' => $orgId,
            'name' => 'MindBase',
            'legalName' => 'ООО «Инним»',
            'url' => $pageUrl,
            'logo' => mb_seo_absolute_url('logo.png'),
            'description' => 'Разработчик бесплатной платформы корпоративной базы знаний MindBase для команд и компаний.',
            'sameAs' => [],
        ],
        [
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'url' => $pageUrl,
            'name' => 'MindBase',
            'description' => 'Бесплатная платформа базы знаний для команд: статьи, поиск, роли, документы и обучение.',
            'inLanguage' => 'ru-RU',
            'publisher' => ['@id' => $orgId],
        ],
        [
            '@type' => 'SoftwareApplication',
            '@id' => $appId,
            'name' => 'MindBase',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'url' => $pageUrl,
            'description' => 'Корпоративная wiki и база знаний: Markdown-статьи, иерархия разделов, группы доступа, документы и курсы.',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'RUB',
                'availability' => 'https://schema.org/InStock',
            ],
            'publisher' => ['@id' => $orgId],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => $pageUrl . '#faq',
            'mainEntity' => $faqEntities,
        ],
    ];
}

/** @return list<array{loc:string,changefreq:string,priority:string}> */
function mb_seo_public_sitemap_entries(): array
{
    return [
        ['loc' => mb_seo_absolute_url('/'), 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => mb_seo_absolute_url('register.php'), 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => mb_seo_absolute_url('login.php'), 'changefreq' => 'monthly', 'priority' => '0.5'],
    ];
}
