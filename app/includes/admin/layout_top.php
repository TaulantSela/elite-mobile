<?php
declare(strict_types=1);

require_once __DIR__ . '/../url_helpers.php';

$adminActive = $adminActive ?? '';
$adminTitle = $adminTitle ?? 'Dashboard';
$adminUser = $_SESSION['username'] ?? 'admin';
$adminInitial = strtoupper(substr($adminUser, 0, 1));

$adminNav = [
    ['key' => 'dashboard',  'label' => 'Dashboard',   'icon' => 'fa-gauge-high',  'href' => elite_asset('dashboard.php')],
    ['key' => 'products',   'label' => 'Products',    'icon' => 'fa-mobile-screen', 'href' => elite_asset('admin/admin_products.php')],
    ['key' => 'categories', 'label' => 'Categories',  'icon' => 'fa-layer-group', 'href' => elite_asset('admin/admin_categories.php')],
    ['key' => 'add',        'label' => 'Add product', 'icon' => 'fa-circle-plus', 'href' => elite_asset('admin/admin_insertproduct.php')],
];

$ae = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/head.html'; ?>
<body class="admin">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="<?php echo $ae(elite_asset('dashboard.php')); ?>">
            <img src="<?php echo $ae(elite_asset('img/elite-mobile_logo.png')); ?>" alt="Elite Mobile">
            <div><strong>Elite Mobile</strong><span>Admin</span></div>
        </a>
        <nav class="admin-nav">
            <div class="nav-label">Manage</div>
            <?php foreach ($adminNav as $item): ?>
                <a href="<?php echo $ae($item['href']); ?>" class="<?php echo $adminActive === $item['key'] ? 'is-active' : ''; ?>">
                    <i class="fa-solid <?php echo $ae($item['icon']); ?>"></i> <?php echo $ae($item['label']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-foot">
            <a class="admin-view-site" href="<?php echo $ae(elite_asset('home.php')); ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> View store
            </a>
            <a href="<?php echo $ae(elite_asset('admin/admin_logout.php')); ?>">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex;align-items:center;gap:0.85rem;">
                <button class="admin-menu-btn" type="button" aria-label="Toggle menu" onclick="document.body.classList.toggle('nav-open')">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <div class="crumb">Admin panel</div>
                    <h1><?php echo $ae($adminTitle); ?></h1>
                </div>
            </div>
            <div class="admin-user">
                <span class="avatar"><?php echo $ae($adminInitial); ?></span>
                <span><?php echo $ae($adminUser); ?></span>
            </div>
        </header>
        <main class="admin-content">
