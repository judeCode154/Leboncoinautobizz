<?php

if (!defined('AUTOBIZZ_SITE_URL')) {
    // Set SITE_URL in the server environment to the public base URL at deployment.
    $siteUrlFromEnvironment = getenv('SITE_URL');
    define('AUTOBIZZ_SITE_URL', $siteUrlFromEnvironment === false ? '' : $siteUrlFromEnvironment);
}

function autobizz_site_base_url()
{
    $configuredUrl = AUTOBIZZ_SITE_URL;

    if (trim($configuredUrl) === '') {
        return null;
    }

    $configuredUrl = rtrim(trim($configuredUrl), '/');
    $parts = parse_url($configuredUrl);

    if (
        !filter_var($configuredUrl, FILTER_VALIDATE_URL)
        || !isset($parts['scheme'], $parts['host'])
        || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
        || isset($parts['user'])
        || isset($parts['pass'])
        || isset($parts['query'])
        || isset($parts['fragment'])
    ) {
        return null;
    }

    return $configuredUrl;
}

function autobizz_site_url($path = '')
{
    $baseUrl = autobizz_site_base_url();

    if ($baseUrl === null) {
        return null;
    }

    return $path === '' ? $baseUrl . '/' : $baseUrl . '/' . ltrim($path, '/');
}

function autobizz_meta_excerpt($value, $length = 160)
{
    $value = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $length, 'UTF-8');
    }

    return substr($value, 0, $length);
}
