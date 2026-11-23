<?php
declare(strict_types=1);

/**
 * Sends email through Resend's HTTP API (RESEND_API_KEY, MAIL_FROM).
 *
 * Without an API key nothing is sent. Locally (outside Vercel) the message's
 * link is written to the PHP error log instead, so the reset flow can be
 * tested; on Vercel it is never logged.
 */
function elite_send_mail(string $to, string $subject, string $text, string $html, string $devLink = ''): bool
{
    $apiKey = (string) (getenv('RESEND_API_KEY') ?: '');
    $from = (string) (getenv('MAIL_FROM') ?: 'Elite Mobile <onboarding@resend.dev>');

    if ($apiKey === '') {
        if (getenv('VERCEL')) {
            error_log('[mail] RESEND_API_KEY is not set; email not sent.');
        } else {
            error_log("[mail] RESEND_API_KEY is not set. Would send \"{$subject}\" to {$to}. Link: {$devLink}");
        }

        return false;
    }

    $handle = curl_init('https://api.resend.com/emails');
    curl_setopt_array($handle, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['from' => $from, 'to' => [$to], 'subject' => $subject, 'text' => $text, 'html' => $html]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

    if ($status < 200 || $status >= 300) {
        error_log('[mail] Resend returned HTTP ' . $status . ': ' . substr((string) $response, 0, 300));

        return false;
    }

    return true;
}
