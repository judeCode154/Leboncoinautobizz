<?php
require_once __DIR__ . '/../config/site.php';

$siteBaseUrl = autobizz_site_base_url();
header('Content-Type: application/xml; charset=utf-8');

if ($siteBaseUrl === null) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Configure SITE_URL before publishing the sitemap.';
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/init.php';

$resultat = mysqli_query($connexion, 'SELECT id_ads FROM annonce ORDER BY created_at DESC LIMIT 50000');

if (!$resultat) {
    http_response_code(500);
    echo 'Unable to generate sitemap.';
    exit;
}

$xmlEscape = function ($value) {
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
};

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
echo '<url><loc>' . $xmlEscape($siteBaseUrl . '/') . '</loc></url>';

while ($annonce = mysqli_fetch_assoc($resultat)) {
    $url = $siteBaseUrl . '/ad.php?id=' . (int) $annonce['id_ads'];
    echo '<url><loc>' . $xmlEscape($url) . '</loc></url>';
}

echo '</urlset>';
