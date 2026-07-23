<?php
declare(strict_types=1);

if (!function_exists('elite_base_path')) {
    function elite_base_path(): string
    {
        static $basePath = null;

        if ($basePath !== null) {
            return $basePath;
        }

        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');

        if ($scriptName === '') {
            $basePath = '';
            return $basePath;
        }

        $directory = str_replace('\\', '/', dirname($scriptName));

        if ($directory === '/' || $directory === '\\' || $directory === '.') {
            $directory = '';
        }

        $basePath = rtrim($directory, '/');

        return $basePath;
    }
}

if (!function_exists('elite_url')) {
    function elite_url(string $path = ''): string
    {
        $basePath = elite_base_path();
        $normalized = ltrim($path, '/');

        if ($normalized === '') {
            return $basePath === '' ? '/' : $basePath . '/';
        }

        $prefix = $basePath === '' ? '' : $basePath . '/';

        return $prefix . $normalized;
    }
}

if (!function_exists('elite_asset')) {
    function elite_asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('product_image_src')) {
    /**
     * Resolve a product image reference to a usable <img> src.
     *
     * Uploaded images are stored as full Vercel Blob URLs and returned as-is;
     * legacy seed images are bare filenames served from the local /img folder.
     * Both resolve to root-absolute/absolute URLs, so they render identically
     * regardless of the page's <base href>.
     */
    function product_image_src(?string $image, string $fallback = 'img/elite-mobile_logo.png'): string
    {
        $image = trim((string) $image);

        if ($image === '') {
            return elite_asset($fallback);
        }

        if (preg_match('#^https?://#i', $image)) {
            return $image;
        }

        return elite_asset('img/' . ltrim($image, '/'));
    }
}
