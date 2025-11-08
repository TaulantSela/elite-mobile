<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

function formatPrice(int|float $amount, string $suffix = ' ден'): string
{
    return number_format((float) $amount, 0, '.', ' ') . $suffix;
}

function fetchAllCategories(): array
{
    $connection = getDbConnection();
    $query = 'SELECT categoryid, categoryname FROM category ORDER BY categoryname ASC';
    $result = mysqli_query($connection, $query);

    if (!$result) {
        return [];
    }

    $categories = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = [
            'id' => (int) $row['categoryid'],
            'name' => $row['categoryname'],
        ];
    }

    mysqli_free_result($result);

    return $categories;
}

function fetchFeaturedProducts(int $limit = 8): array
{
    $connection = getDbConnection();
    $query = 'SELECT productid, productname, price, image FROM product ORDER BY price DESC LIMIT ?';
    $statement = mysqli_prepare($connection, $query);

    if (!$statement) {
        return [];
    }

    mysqli_stmt_bind_param($statement, 'i', $limit);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);

    if (!$result) {
        mysqli_stmt_close($statement);
        return [];
    }

    $products = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = [
            'id' => (int) $row['productid'],
            'name' => $row['productname'],
            'price' => (int) $row['price'],
            'image' => $row['image'],
        ];
    }

    mysqli_free_result($result);
    mysqli_stmt_close($statement);

    return $products;
}

function fetchLatestProducts(int $limit = 8): array
{
    $connection = getDbConnection();
    $query = 'SELECT productid, productname, price, image FROM product ORDER BY productid DESC LIMIT ?';
    $statement = mysqli_prepare($connection, $query);

    if (!$statement) {
        return [];
    }

    mysqli_stmt_bind_param($statement, 'i', $limit);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);

    if (!$result) {
        mysqli_stmt_close($statement);
        return [];
    }

    $products = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = [
            'id' => (int) $row['productid'],
            'name' => $row['productname'],
            'price' => (int) $row['price'],
            'image' => $row['image'],
        ];
    }

    mysqli_free_result($result);
    mysqli_stmt_close($statement);

    return $products;
}

function fetchHighlightedServices(int $limit = 3): array
{
    $connection = getDbConnection();
    $query = 'SELECT id, name, short_description, price, duration, icon FROM services ORDER BY is_featured DESC, price DESC LIMIT ?';
    $statement = mysqli_prepare($connection, $query);

    if (!$statement) {
        return [];
    }

    mysqli_stmt_bind_param($statement, 'i', $limit);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);

    if (!$result) {
        mysqli_stmt_close($statement);
        return [];
    }

    $services = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'short_description' => $row['short_description'],
            'price' => (int) $row['price'],
            'duration' => $row['duration'],
            'icon' => $row['icon'] ?? 'tool',
        ];
    }

    mysqli_free_result($result);
    mysqli_stmt_close($statement);

    return $services;
}

function showCategory(): void
{
    $connection = getDbConnection();
    $query = 'SELECT categoryid, categoryname FROM category ORDER BY categoryname';
    $result = mysqli_query($connection, $query);

    if (!$result) {
        return;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $categoryId = (int) $row['categoryid'];
        $categoryName = htmlspecialchars($row['categoryname'], ENT_QUOTES, 'UTF-8');

        echo "<a href='products.php?categoryid={$categoryId}' class='list-group-item'>{$categoryName}<span class='badge'></span></a>";
    }

    mysqli_free_result($result);
}

function showProduct(): void
{
    $product = findProductFromRequest();

    if (!$product) {
        echo '<p class="text-danger">Product not found.</p>';
        return;
    }

    $imagePath = 'img/' . $product['image'];
    $imagePathSafe = htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8');

    echo "<br/><img src='{$imagePathSafe}' alt='Product image' height='300px'>";
}

function productDetails(): void
{
    $product = findProductFromRequest();

    if (!$product) {
        echo '<p class="text-danger">Product not found.</p>';
        return;
    }

    $productName = htmlspecialchars($product['productname'], ENT_QUOTES, 'UTF-8');
    $categoryName = htmlspecialchars($product['categoryname'], ENT_QUOTES, 'UTF-8');
    $price = number_format((float) $product['price'], 2);
    $info = nl2br(htmlspecialchars($product['info'], ENT_QUOTES, 'UTF-8'));

    echo '<br/><br/>';
    echo "<h3 style='font-weight: bold;'>Name: {$productName}</h3>";
    echo "<h3 style='font-weight: bold;'>Category: {$categoryName}</h3>";
    echo "<h3 style='font-weight: bold;'>Price: {$price} den</h3>";
    echo "<h3 style='font-weight: bold;'>Info:</h3> {$info}";

    if ((int) $product['paid'] === 0) {
        renderPaypalForm((int) $product['productid'], $productName, (float) $product['price']);
    } else {
        echo "<button disabled class='btn btn-success' style='width: 100%;'>Paid</button>";
    }
}

function selectOrder(): void
{
    if (!hasSelectedCategory()) {
        echo '<br><h1 align="center">Please Select a Category !!</h1>';
        return;
    }

    echo "<br/>
            <select id='orderSelect' class='form-control'>
              <option value=''>Select order</option>
              <option value='price'>Price</option>
              <option value='productname'>Name</option>
          </select>
          <br/>";
}

function renderProducts(): void
{
    $categoryId = getSelectedCategoryId();

    if ($categoryId === null) {
        return;
    }

    $orderColumn = getOrderColumn();
    $connection = getDbConnection();

    $query = 'SELECT productid, productname, price, image FROM product WHERE categoryid = ?';
    if ($orderColumn !== null) {
        $query .= " ORDER BY {$orderColumn}";
    }

    $statement = mysqli_prepare($connection, $query);

    if (!$statement) {
        return;
    }

    mysqli_stmt_bind_param($statement, 'i', $categoryId);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);

    if (!$result) {
        mysqli_stmt_close($statement);
        return;
    }

    echo "<form method='GET' align='center'>";
    $counter = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $productId = (int) $row['productid'];
        $productName = htmlspecialchars($row['productname'], ENT_QUOTES, 'UTF-8');
        $price = number_format((float) $row['price'], 2);
        $imagePath = htmlspecialchars('img/' . $row['image'], ENT_QUOTES, 'UTF-8');

        echo "<a href='description.php?productid={$productId}' class='btn btn-default btn-xs' target='_blank'>";
        echo "<h3>{$productName}</h3>";
        echo "<img src='{$imagePath}' alt='{$productName}'><br>";
        echo "<h5>{$price} den</h5>";
        echo '</a>';

        $counter++;
        if ($counter > 2) {
            echo '<br/><br/>';
            $counter = 0;
        }

        echo '&nbsp;&nbsp;';
    }

    echo '</form>';

    mysqli_free_result($result);
    mysqli_stmt_close($statement);
}

function findProductFromRequest(): ?array
{
    if (!isset($_GET['productid'])) {
        return null;
    }

    $productId = (int) $_GET['productid'];

    if ($productId <= 0) {
        return null;
    }

    $connection = getDbConnection();
    $query = 'SELECT p.*, c.categoryname FROM product p JOIN category c ON p.categoryid = c.categoryid WHERE p.productid = ? LIMIT 1';
    $statement = mysqli_prepare($connection, $query);

    if (!$statement) {
        return null;
    }

    mysqli_stmt_bind_param($statement, 'i', $productId);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);

    if (!$result) {
        mysqli_stmt_close($statement);
        return null;
    }

    $product = mysqli_fetch_assoc($result) ?: null;

    mysqli_free_result($result);
    mysqli_stmt_close($statement);

    return $product;
}

function hasSelectedCategory(): bool
{
    return isset($_GET['categoryid']) && (int) $_GET['categoryid'] > 0;
}

function getSelectedCategoryId(): ?int
{
    if (!hasSelectedCategory()) {
        return null;
    }

    $categoryId = (int) $_GET['categoryid'];

    return $categoryId > 0 ? $categoryId : null;
}

function getOrderColumn(): ?string
{
    if (!isset($_GET['order'])) {
        return null;
    }

    $allowedColumns = ['price', 'productname'];
    $requested = $_GET['order'];

    return in_array($requested, $allowedColumns, true) ? $requested : null;
}

function renderPaypalForm(int $productId, string $productName, float $price): void
{
    $amount = number_format($price * 0.019, 2, '.', '');
    $safeName = htmlspecialchars($productName, ENT_QUOTES, 'UTF-8');

    echo "<form action='https://www.sandbox.paypal.com/cgi-bin/webscr' method='post'>
            <input type='hidden' name='cmd' value='_xclick'>
            <input type='hidden' name='business' value='vlerant.saiti-facilitator@live.com'>
            <input type='hidden' name='item_name' value='{$safeName}'>
            <input type='hidden' name='item_number' value='{$productId}'>
            <input type='hidden' name='amount' value='{$amount}'>
            <input type='hidden' name='no_shipping' value='0'>
            <input type='hidden' name='return' value='http://localhost/sela/includes/paypal.php'>
            <input type='hidden' name='cancel_return' value='http://localhost/sela/includes/paypal.php'>
            <input type='hidden' name='no_note' value='1'>
            <input type='hidden' name='currency_code' value='USD'>
            <input type='hidden' name='lc' value='US'>
            <input type='image' src='https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-small.png' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'>
        </form>";
}

?>