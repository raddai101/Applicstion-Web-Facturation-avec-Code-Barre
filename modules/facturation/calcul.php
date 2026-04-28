<?php
// modules/facturation/calcul.php — API calcul totaux (AJAX JSON)
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-factures.php';
require_once dirname(__DIR__, 2) . '/auth/session.php';

header('Content-Type: application/json');
$input    = json_decode(file_get_contents('php://input'), true);
$articles = $input['articles'] ?? [];
if (empty($articles)) { echo json_encode(['error' => 'Aucun article']); exit; }
echo json_encode(calculer_totaux($articles));
