<?php
declare(strict_types=1);

/**
 * PayPal sends the buyer back here after checkout (and on cancel).
 * The product is marked as paid only if PayPal confirms the transaction;
 * see includes/paypal.php.
 */

require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/paypal.php';

$productId = (int) ($_GET['item_number'] ?? 0);
$transactionId = (string) ($_GET['tx'] ?? '');

if ($productId > 0 && $transactionId !== '') {
    $connection = getDbConnection();
    $statement = mysqli_prepare($connection, 'SELECT price FROM product WHERE productid = ? AND paid = 0 LIMIT 1');

    if ($statement) {
        mysqli_stmt_bind_param($statement, 'i', $productId);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($statement);

        if ($row !== null && elite_paypal_confirms($transactionId, $productId, (float) $row['price'])) {
            $update = mysqli_prepare($connection, 'UPDATE product SET paid = 1 WHERE productid = ?');

            if ($update) {
                mysqli_stmt_bind_param($update, 'i', $productId);
                mysqli_stmt_execute($update);
                mysqli_stmt_close($update);
            }
        }
    }
}

header('Location: ' . elite_url($productId > 0 ? 'description.php?productid=' . $productId : 'products.php'));
exit;
