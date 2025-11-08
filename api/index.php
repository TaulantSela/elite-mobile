<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$decodedPath = rawurldecode($uriPath);
$normalized = $decodedPath === '/' ? 'home.php' : ltrim($decodedPath, '/');

if ($normalized === '' || $normalized === 'index.php') {
    $normalized = 'home.php';
}

if (substr($normalized, -1) === '/') {
    $normalized .= 'index.php';
}

$targetPath = $projectRoot . '/' . $normalized;
$resolved = realpath($targetPath);

if ($resolved === false || strpos($resolved, $projectRoot) !== 0 || !is_file($resolved)) {
    http_response_code(404);
    echo '404 Not Found';
    return;
}

if (pathinfo($resolved, PATHINFO_EXTENSION) !== 'php') {
    http_response_code(403);
    echo '403 Forbidden';
    return;
}

chdir(dirname($resolved));
$_SERVER['SCRIPT_FILENAME'] = $resolved;
$_SERVER['SCRIPT_NAME'] = '/' . ltrim(str_replace($projectRoot, '', $resolved), '/');
$_SERVER['DOCUMENT_ROOT'] = $projectRoot;

require $resolved;
