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
