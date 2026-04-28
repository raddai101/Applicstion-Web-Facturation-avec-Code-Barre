<?php
// modules/facturation/nouvelle-facture.php — Redirige vers index.php (caisse)
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__, 2) . '/auth/session.php';
verifier_role(['caissier','manager','superadmin']);
header('Location: ' . BASE_URL . '/index.php');
exit;
