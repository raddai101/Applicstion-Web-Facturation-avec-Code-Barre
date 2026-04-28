<?php
// ============================================================
// auth/session.php — Vérification session & sécurité
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/fonctions-auth.php';

// Timeout session
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/auth/login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

if (!est_connecte()) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}
