<?php
// ============================================================
// modules/admin/supprimer-compte.php
// ============================================================
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__, 2) . '/auth/session.php';
verifier_role(['superadmin']);

$id    = trim($_GET['id'] ?? '');
$users = charger_utilisateurs();

// Sécurité : ne pas supprimer son propre compte
if ($id === $_SESSION['user']['identifiant']) {
    $_SESSION['flash_error'] = 'Vous ne pouvez pas supprimer votre propre compte.';
    header('Location: ' . BASE_URL . '/modules/admin/gestion-comptes.php');
    exit;
}

$new_users = array_filter($users, fn($u) => $u['identifiant'] !== $id);
if (count($new_users) === count($users)) {
    $_SESSION['flash_error'] = "Compte introuvable : $id";
} else {
    sauvegarder_utilisateurs(array_values($new_users));
    $_SESSION['flash_ok'] = "Compte '$id' supprimé.";
}
header('Location: ' . BASE_URL . '/modules/admin/gestion-comptes.php');
exit;
