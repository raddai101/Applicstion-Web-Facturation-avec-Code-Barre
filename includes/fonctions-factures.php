<?php
// ============================================================
// includes/fonctions-factures.php — Fonctions facturation
// ============================================================

function charger_factures(): array {
    $data = file_get_contents(FACTURES_FILE);
    return json_decode($data, true) ?? [];
}

function sauvegarder_factures(array $factures): void {
    file_put_contents(FACTURES_FILE, json_encode(array_values($factures), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function calculer_totaux(array $articles): array {
    $total_ht = 0;
    foreach ($articles as $a) {
        $total_ht += $a['sous_total_ht'];
    }
    $tva       = round($total_ht * TVA_TAUX, 2);
    $total_ttc = $total_ht + $tva;
    return ['total_ht' => $total_ht, 'tva' => $tva, 'total_ttc' => $total_ttc];
}

function creer_facture(array $articles, string $caissier): array {
    $factures    = charger_factures();
    $date        = date('Y-m-d');
    $heure       = date('H:i:s');
    $id          = 'FAC-' . date('Ymd') . '-' . str_pad(count($factures) + 1, 3, '0', STR_PAD_LEFT);
    $totaux      = calculer_totaux($articles);

    $facture = array_merge([
        'id_facture' => $id,
        'date'       => $date,
        'heure'      => $heure,
        'caissier'   => $caissier,
        'articles'   => $articles,
    ], $totaux);

    // Décrémenter stock
    foreach ($articles as $a) {
        decrementer_stock($a['code_barre'], $a['quantite']);
    }

    $factures[] = $facture;
    sauvegarder_factures($factures);
    return $facture;
}

function factures_du_jour(): array {
    $today    = date('Y-m-d');
    $factures = charger_factures();
    return array_filter($factures, fn($f) => $f['date'] === $today);
}

function factures_du_mois(string $annee_mois): array {
    $factures = charger_factures();
    return array_filter($factures, fn($f) => str_starts_with($f['date'], $annee_mois));
}

function formater_montant(float $montant): string {
    return number_format($montant, 0, ',', '.') . ' ' . DEVISE;
}
