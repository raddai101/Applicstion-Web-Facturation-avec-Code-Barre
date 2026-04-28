<?php
// ============================================================
// config/config.php — Paramètres globaux du système
// ============================================================

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/facturation');

// Chemins des fichiers de données
define('DATA_PATH',        BASE_PATH . '/data');
define('PRODUITS_FILE',    DATA_PATH . '/produits.json');
define('FACTURES_FILE',    DATA_PATH . '/factures.json');
define('USERS_FILE',       DATA_PATH . '/utilisateurs.json');

// Paramètres fiscaux
define('TVA_TAUX', 0.16);          // 16%
define('DEVISE',   'CDF');

// Session
define('SESSION_TIMEOUT', 3600);   // 1 heure

// Timezone
date_default_timezone_set('Africa/Kinshasa');

// Démarrage de session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-création fichiers data si absents
foreach ([PRODUITS_FILE, FACTURES_FILE] as $f) {
    if (!file_exists($f)) file_put_contents($f, '[]');
}
if (!file_exists(USERS_FILE)) {
    $default = [
        [
            'identifiant'   => 'admin',
            'mot_de_passe'  => password_hash('test', PASSWORD_DEFAULT),
            'role'          => 'superadmin',
            'nom_complet'   => 'Administrateur Principal',
            'date_creation' => date('Y-m-d'),
            'actif'         => true
        ],
        [
            'identifiant'   => 'manager',
            'mot_de_passe'  => password_hash('test', PASSWORD_DEFAULT),
            'role'          => 'manager',
            'nom_complet'   => 'Manager Principal',
            'date_creation' => date('Y-m-d'),
            'actif'         => true
        ],
        [
            'identifiant'   => 'caissier',
            'mot_de_passe'  => password_hash('test', PASSWORD_DEFAULT),
            'role'          => 'caissier',
            'nom_complet'   => 'Caissier Principal',
            'date_creation' => date('Y-m-d'),
            'actif'         => true
        ]
    ];
    file_put_contents(USERS_FILE, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
