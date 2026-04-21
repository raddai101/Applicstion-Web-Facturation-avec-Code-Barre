<?php
require_once __DIR__ . '/../config/config.php';

function charger_produits() {
    return charger_json(FILE_PRODUITS);
}

function sauvegarder_produits($produits) {
    sauvegarder_json(FILE_PRODUITS, $produits);
}

function trouver_produit($code_barre) {
    $produits = charger_produits();
    foreach ($produits as $p) {
        if ($p['code_barre'] === $code_barre) {
            return $p;
        }
    }
    return null;
}

function ajouter_produit($produit) {
    $produits = charger_produits();
    $produits[] = $produit;
    sauvegarder_produits($produits);
}
