<?php
require_once __DIR__ . '/../config/site.php';

header('Content-Type: text/plain; charset=utf-8');
$siteBaseUrl = autobizz_site_base_url();

if ($siteBaseUrl === null) {
    echo "User-agent: *\nAllow: /\n";
    exit;
}

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Sitemap: " . $siteBaseUrl . "/sitemap.xml\n";
