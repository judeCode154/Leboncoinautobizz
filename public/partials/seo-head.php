<?php
require_once dirname(__DIR__, 2) . '/config/site.php';

$seoTitle = isset($seoTitle) ? trim($seoTitle) : 'AutoBizz | Petites annonces automobiles';
$seoDescription = isset($seoDescription) ? trim($seoDescription) : 'Découvrez et publiez des annonces automobiles près de chez vous sur AutoBizz.';
$seoRobots = isset($seoRobots) ? $seoRobots : 'index, follow';
$seoCanonicalUrl = isset($seoCanonicalPath) ? autobizz_site_url($seoCanonicalPath) : null;
$seoFaviconPath = isset($seoFaviconPath) ? $seoFaviconPath : 'assets/favicon.png?v=1';
?>
<link rel="icon" type="image/png" sizes="64x64" href="<?= htmlspecialchars($seoFaviconPath, ENT_QUOTES, 'UTF-8'); ?>">
<title><?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="robots" content="<?= htmlspecialchars($seoRobots, ENT_QUOTES, 'UTF-8'); ?>">
<?php if ($seoCanonicalUrl !== null): ?>
    <link rel="canonical" href="<?= htmlspecialchars($seoCanonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="<?= htmlspecialchars(isset($seoOgType) ? $seoOgType : 'website', ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:site_name" content="AutoBizz">
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seoCanonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="<?= !empty($seoImageUrl) ? 'summary_large_image' : 'summary'; ?>">
    <?php if (!empty($seoImageUrl)): ?>
        <meta property="og:image" content="<?= htmlspecialchars($seoImageUrl, ENT_QUOTES, 'UTF-8'); ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars($seoImageUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <?php if (!empty($seoStructuredData)): ?>
        <?php $seoStructuredJson = json_encode($seoStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
        <?php if ($seoStructuredJson !== false): ?>
            <script type="application/ld+json"><?= $seoStructuredJson; ?></script>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
