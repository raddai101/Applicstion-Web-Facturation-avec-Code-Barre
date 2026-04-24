<?php
// require_once __DIR__ . '/../auth/session.php';

// Détection de la page active
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>

    <!-- CSS global -->
    <link rel="stylesheet" href="/facturation/assets/css/style.css">

    <!-- CSS spécifique selon module -->
    <?php if (strpos($_SERVER['REQUEST_URI'], 'produits') !== false): ?>
        <link rel="stylesheet" href="/facturation/assets/css/produits.css">
    <?php elseif (strpos($_SERVER['REQUEST_URI'], 'facturation') !== false): ?>
        <link rel="stylesheet" href="/facturation/assets/css/facturation.css">
    <?php elseif (strpos($_SERVER['REQUEST_URI'], 'admin') !== false): ?>
        <link rel="stylesheet" href="/facturation/assets/css/admin.css">
    <?php elseif (strpos($_SERVER['REQUEST_URI'], 'rapports') !== false): ?>
        <link rel="stylesheet" href="/facturation/assets/css/rapports.css">
    <?php endif; ?>
</head>

<body>

<header>
    <div class="logo">
        <h1>Facturation POS</h1>
    </div>

    <nav>
        <a href="/facturation/index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Accueil</a>

        <a href="/facturation/modules/facturation/nouvelle-facture.php"
           class="<?= $current_page === 'nouvelle-facture.php' ? 'active' : '' ?>">
           Facturation
        </a>

        <?php if ($_SESSION['user']['role'] !== 'caissier'): ?>
            <a href="/facturation/modules/produits/liste.php"
               class="<?= $current_page === 'liste.php' ? 'active' : '' ?>">
               Produits
            </a>
        <?php endif; ?>

        <?php if ($_SESSION['user']['role'] === 'superadmin'): ?>
            <a href="/facturation/modules/admin/gestion-comptes.php"
               class="<?= $current_page === 'gestion-comptes.php' ? 'active' : '' ?>">
               Comptes
            </a>
        <?php endif; ?>

        <!-- <a href="/facturation/test-scanner.php" style="background: #E74C3C; color: white;">Test Scanner</a> -->

        <a href="/facturation/auth/logout.php">Déconnexion</a>
    </nav>
</header>

<div class="container">
