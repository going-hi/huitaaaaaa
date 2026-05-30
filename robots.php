<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/seo.php';

header('Content-Type: text/plain; charset=UTF-8');

$sitemap = mb_seo_absolute_url('sitemap.xml');

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /cabinet.php\n";
echo "Disallow: /cabinet-\n";
echo "Disallow: /knowledge-catalog.php\n";
echo "Disallow: /category.php\n";
echo "Disallow: /article.php\n";
echo "Disallow: /search.php\n";
echo "Disallow: /documents.php\n";
echo "Disallow: /learning-materials.php\n";
echo "Disallow: /course.php\n";
echo "Disallow: /admin-\n";
echo "Disallow: /export.php\n";
echo "Disallow: /storage/\n";
echo "Disallow: /database/\n";
echo "Disallow: /lib/\n";
echo "\n";
echo 'Sitemap: ' . $sitemap . "\n";
