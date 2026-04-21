<?php
require_once __DIR__ . '/../config/config.php';

function charger_utilisateurs() {
    return charger_json(FILE_UTILISATEURS);
}

function sauvegarder_utilisateurs($utilisateurs) {
    sauvegarder_json(FILE_UTILISATEURS, $utilisateurs);
}

function trouver_utilisateur($identifiant) {
    $utilisateurs = charger_utilisateurs();
    foreach ($utilisateurs as $u) {
        if ($u['identifiant'] === $identifiant) {
            return $u;
        }
    }
    return null;
}

function verifier_connexion($identifiant, $mot_de_passe) {
    $user = trouver_utilisateur($identifiant);
    if (!$user || !$user['actif']) return false;

    if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
        return $user;
    }
    return false;
}
