<?php
// ============================================================
// modules/produits/lire.php — API AJAX lecture produit
// ============================================================
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-produits.php';
require_once dirname(__DIR__, 2) . '/auth/session.php';

header('Content-Type: application/json; charset=utf-8');

$code = trim($_GET['code'] ?? '');
if (!$code) {
    echo json_encode(['found' => false, 'error' => 'Code-barres manquant']);
    exit;
}

$produit = chercher_produit_par_code($code);
if ($produit) {
    echo json_encode(['found' => true, 'produit' => $produit]);
} else {
    echo json_encode(['found' => false, 'code' => $code]);
}
