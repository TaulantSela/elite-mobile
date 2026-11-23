<?php
declare(strict_types=1);

/**
 * PayPal sandbox checkout helpers.
 *
 * The page PayPal returns the buyer to can be opened by anyone with any query
 * string, so its parameters prove nothing. A product is marked as paid only
 * after PayPal itself confirms the transaction through Payment Data Transfer
 * (PDT), which needs the merchant's PDT identity token in PAYPAL_PDT_TOKEN.
 * Without the token, checkout still works but nothing is marked as paid.
 *
 * This file only defines functions; the front controller refuses direct
 * requests to anything under includes/.
 */

require_once __DIR__ . '/url_helpers.php';

const ELITE_PAYPAL_WEBSCR = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
const ELITE_PAYPAL_BUSINESS = 'vlerant.saiti-facilitator@live.com';
const ELITE_PAYPAL_CURRENCY = 'USD';

/** Price in denars → the USD amount sent to PayPal. */
function elite_paypal_amount(float $priceMkd): string
{
    return number_format($priceMkd * 0.019, 2, '.', '');
}

/**
 * Ask PayPal whether transaction $transactionId really paid for $productId.
 * Returns false on any doubt: no token, network error, or a mismatch.
 */
function elite_paypal_confirms(string $transactionId, int $productId, float $priceMkd): bool
{
    $token = (string) (getenv('PAYPAL_PDT_TOKEN') ?: '');

    if ($token === '' || $transactionId === '' || !function_exists('curl_init')) {
        return false;
    }

    $handle = curl_init(ELITE_PAYPAL_WEBSCR);
    curl_setopt_array($handle, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['cmd' => '_notify-synch', 'tx' => $transactionId, 'at' => $token]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($handle);

    if (!is_string($response)) {
        return false;
    }

    $lines = preg_split('/\r?\n/', trim($response)) ?: [];

    if (($lines[0] ?? '') !== 'SUCCESS') {
        return false;
    }

    $fields = [];
    foreach (array_slice($lines, 1) as $line) {
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $fields[urldecode($key)] = urldecode($value);
    }

    return ($fields['payment_status'] ?? '') === 'Completed'
        && strcasecmp($fields['receiver_email'] ?? '', ELITE_PAYPAL_BUSINESS) === 0
        && (int) ($fields['item_number'] ?? 0) === $productId
        && ($fields['mc_currency'] ?? '') === ELITE_PAYPAL_CURRENCY
        && ($fields['mc_gross'] ?? '') === elite_paypal_amount($priceMkd);
}
