<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/seo.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex', true);

$entries = mb_seo_public_sitemap_entries();
$lastmod = gmdate('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($entries as $entry) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo '    <lastmod>' . $lastmod . "</lastmod>\n";
    echo '    <changefreq>' . htmlspecialchars($entry['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</changefreq>\n";
    echo '    <priority>' . htmlspecialchars($entry['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</priority>\n";
    echo "  </url>\n";
}
echo "</urlset>\n";
