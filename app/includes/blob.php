<?php

declare(strict_types=1);

/**
 * Vercel Blob upload helper.
 *
 * There is no official PHP SDK for Vercel Blob, so this talks to the Blob HTTP
 * API directly. The request contract mirrors the `@vercel/blob` `put()` method
 * (API version 12): a single PUT to
 *   https://blob.vercel-storage.com/?pathname=<pathname>
 * authenticated with the store's read-write token. The store persists the file
 * and returns a permanent, CDN-backed public URL which we store in place of a
 * local filename — the fix for Vercel's ephemeral, read-only filesystem.
 */

const BLOB_API_ENDPOINT = 'https://blob.vercel-storage.com';
const BLOB_API_VERSION = '12';

/** The read-write token (empty string when Blob is not configured). */
function blob_token(): string
{
    return (string) (getenv('BLOB_READ_WRITE_TOKEN') ?: '');
}

/** Whether Blob uploads are available in this environment. */
function blob_enabled(): bool
{
    return blob_token() !== '' && function_exists('curl_init');
}

/**
 * The store id is embedded in a read-write token:
 * `vercel_blob_rw_<storeId>_<secret>`.
 */
function blob_store_id(string $token): string
{
    $parts = explode('_', $token);

    return $parts[3] ?? '';
}

/**
 * Upload a local file to Vercel Blob and return its public URL, or null on
 * failure (the caller falls back to local disk / surfaces an error).
 *
 * @param string $sourcePath  Readable local path (e.g. an upload tmp_name).
 * @param string $pathname    Destination within the store, e.g. "products/iphone-17.png".
 * @param string $contentType MIME type, e.g. "image/png".
 */
function blob_put(string $sourcePath, string $pathname, string $contentType): ?string
{
    $token = blob_token();

    if ($token === '' || !function_exists('curl_init') || !is_readable($sourcePath)) {
        return null;
    }

    $body = file_get_contents($sourcePath);

    if ($body === false) {
        return null;
    }

    $pathname = ltrim($pathname, '/');
    $endpoint = BLOB_API_ENDPOINT . '/?pathname=' . rawurlencode($pathname);

    $headers = [
        'authorization: Bearer ' . $token,
        'x-api-version: ' . BLOB_API_VERSION,
        'x-vercel-blob-store-id: ' . blob_store_id($token),
        'x-vercel-blob-access: public',
        'x-content-type: ' . $contentType,
        'x-add-random-suffix: 1',
        'x-cache-control-max-age: 31536000',
        'content-type: ' . $contentType,
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlError = curl_error($ch);

    if (!is_string($response) || $status < 200 || $status >= 300) {
        error_log(sprintf(
            '[blob] upload failed (status=%d, curl=%s): %s',
            $status,
            $curlError,
            is_string($response) ? substr($response, 0, 300) : '(no body)'
        ));

        return null;
    }

    $data = json_decode($response, true);

    if (!is_array($data) || empty($data['url'])) {
        error_log('[blob] unexpected response: ' . substr($response, 0, 300));

        return null;
    }

    return (string) $data['url'];
}
