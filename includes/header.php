<?php
// ============================================================
// includes/header.php — En-tête commun (nav + topbar)
// ============================================================
$user    = utilisateur_courant();
$role    = $user['role'] ?? '';
$current = basename($_SERVER['PHP_SELF']);
$dir     = basename(dirname($_SERVER['PHP_SELF']));

function nav_active(string $page, string $current_page, string $current_dir = ''): string {
    if ($page === $current_page) return 'active';
    if ($page === $current_dir)  return 'active';
    return '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'SuperMarché POS' ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="topnav">
  <div class="logo">
    <div class="logo-icon">SM</div>
    <div>
      <div class="logo-text">SuperMarché POS</div>
      <div class="logo-sub">Système de Caisse v1.0</div>
    </div>
  </div>

  <div class="nav-tabs">
    <a href="<?= BASE_URL ?>/index.php" class="nav-tab <?= nav_active('index.php', $current) ?>">🧾 Caisse</a>

    <?php if (a_role('manager')): ?>
    <a href="<?= BASE_URL ?>/modules/produits/liste.php" class="nav-tab <?= nav_active('produits', $dir) ?>">📦 Produits</a>
    <a href="<?= BASE_URL ?>/rapports/rapport-journalier.php" class="nav-tab <?= nav_active('rapports', $dir) ?>">📊 Rapports</a>
    <?php endif; ?>

    <?php if (a_role('superadmin')): ?>
    <a href="<?= BASE_URL ?>/modules/admin/gestion-comptes.php" class="nav-tab <?= nav_active('admin', $dir) ?>">⚙️ Admin</a>
    <?php endif; ?>
  </div>

  <div class="nav-right">
    <div class="status-dot"></div>
    <div class="user-chip">
      <div class="user-avatar"><?= strtoupper(substr($user['nom_complet'] ?? 'U', 0, 2)) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['nom_complet'] ?? '') ?></div>
    </div>
    <span class="badge badge-<?= $role ?>"><?= ucfirst($role === 'superadmin' ? 'Admin' : $role) ?></span>
    <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-logout" title="Déconnexion">⏻</a>
  </div>
</nav>
