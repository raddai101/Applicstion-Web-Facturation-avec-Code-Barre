<?php
/**
 * ---------------------------------------------------------
 *  FICHIER DE CONFIGURATION GLOBALE DU PROJET
 *  Système de Facturation PHP - JSON Only
 * ---------------------------------------------------------
 */

// Empêcher l'accès direct
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Racine du projet
define('BASE_PATH', dirname(__DIR__));

// URL de base du projet (pour les redirections)
define('BASE_URL', '/facturation');

// Chemins vers les fichiers JSON
define('FILE_PRODUITS', BASE_PATH . '/data/produits.json');
define('FILE_FACTURES', BASE_PATH . '/data/factures.json');
define('FILE_UTILISATEURS', BASE_PATH . '/data/utilisateurs.json');

// Taux de TVA (18% selon le TP)
define('TAUX_TVA', 0.18);

// Format de date par défaut
define('DATE_FORMAT', 'Y-m-d');

// Fuseau horaire
date_default_timezone_set('Africa/Kinshasa');

// Configuration du scanner (si besoin)
define('SCANNER_ACTIVE', true);

// Rôles autorisés
$ROLES_AUTORISES = [
    'caissier',
    'manager',
    'superadmin'
];

// Fonction utilitaire pour charger un fichier JSON
function charger_json($chemin) {
    if (!file_exists($chemin)) {
        return [];
    }
    return json_decode(file_get_contents($chemin), true);
}

// Fonction utilitaire pour sauvegarder un fichier JSON
function sauvegarder_json($chemin, $data) {
    file_put_contents($chemin, json_encode($data, JSON_PRETTY_PRINT));
}

