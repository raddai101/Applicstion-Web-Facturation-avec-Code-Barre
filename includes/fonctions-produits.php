<?php
// ============================================================
// includes/fonctions-produits.php — Fonctions produits
// ============================================================

function charger_produits(): array {
    $data = file_get_contents(PRODUITS_FILE);
    return json_decode($data, true) ?? [];
}

function sauvegarder_produits(array $produits): void {
    file_put_contents(PRODUITS_FILE, json_encode(array_values($produits), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function chercher_produit_par_code(string $code): ?array {
    foreach (charger_produits() as $p) {
        if ($p['code_barre'] === $code) return $p;
    }
    return null;
}

function produit_perime(array $produit): bool {
    if (empty($produit['date_expiration'])) {
        return false;
    }
    $expiration = strtotime($produit['date_expiration']);
    $aujourdHui  = strtotime(date('Y-m-d'));
    return $expiration < $aujourdHui;
}

function enregistrer_produit(array $data): array {
    $produits = charger_produits();
    $code     = trim($data['code_barre']);

    // Vérifie doublon
    foreach ($produits as &$p) {
        if ($p['code_barre'] === $code) {
            // Mise à jour
            $p['nom']               = trim($data['nom']);
            $p['prix_unitaire_ht']  = (float)$data['prix_unitaire_ht'];
            $p['date_expiration']   = $data['date_expiration'];
            $p['quantite_stock']    = (int)$data['quantite_stock'];
            sauvegarder_produits($produits);
            return ['success' => true, 'message' => 'Produit mis à jour.', 'produit' => $p];
        }
    }

    // Nouveau produit
    $nouveau = [
        'code_barre'          => $code,
        'nom'                 => trim($data['nom']),
        'prix_unitaire_ht'    => (float)$data['prix_unitaire_ht'],
        'date_expiration'     => $data['date_expiration'],
        'quantite_stock'      => (int)$data['quantite_stock'],
        'date_enregistrement' => date('Y-m-d'),
    ];
    $produits[] = $nouveau;
    sauvegarder_produits($produits);
    return ['success' => true, 'message' => 'Produit enregistré.', 'produit' => $nouveau];
}

function decrementer_stock(string $code, int $quantite): bool {
    $produits = charger_produits();
    foreach ($produits as &$p) {
        if ($p['code_barre'] === $code) {
            if ($p['quantite_stock'] < $quantite) return false;
            $p['quantite_stock'] -= $quantite;
            sauvegarder_produits($produits);
            return true;
        }
    }
    return false;
}

function valider_produit_form(array $data): array {
    $errors = [];
    if (empty($data['code_barre']))                         $errors[] = 'Code-barres requis.';
    if (empty($data['nom']))                                $errors[] = 'Nom du produit requis.';
    if (!isset($data['prix_unitaire_ht']) || !is_numeric($data['prix_unitaire_ht']) || $data['prix_unitaire_ht'] <= 0)
                                                            $errors[] = 'Prix invalide (doit être > 0).';
    if (!isset($data['quantite_stock']) || !is_numeric($data['quantite_stock']) || $data['quantite_stock'] < 0)
                                                            $errors[] = 'Quantité invalide.';
    if (empty($data['date_expiration']) || !strtotime($data['date_expiration']))
                                                            $errors[] = 'Date d\'expiration invalide.';
    
    // ✓ Vérifier que la date d'expiration n'est pas dans le passé
    if (!empty($data['date_expiration']) && strtotime($data['date_expiration'])) {
        $dateExpiration = strtotime($data['date_expiration']);
        $dateAujourd = strtotime(date('Y-m-d'));
        if ($dateExpiration < $dateAujourd) {
            $errors[] = 'La date d\'expiration ne peut pas être antérieure à aujourd\'hui.';
        }
    }
    
    return $errors;
}
