<?php
require_once __DIR__ . '/../config/config.php';

function charger_factures() {
    return charger_json(FILE_FACTURES);
}

function sauvegarder_factures($factures) {
    sauvegarder_json(FILE_FACTURES, $factures);
}

function generer_id_facture() {
    $date = date("Ymd");
    $factures = charger_factures();
    $count = count($factures) + 1;
    return "FAC-$date-" . str_pad($count, 3, "0", STR_PAD_LEFT);
}

function calculer_totaux($articles) {
    $total_ht = 0;

    foreach ($articles as $a) {
        $total_ht += $a['sous_total_ht'];
    }

    $tva = $total_ht * TAUX_TVA;
    $total_ttc = $total_ht + $tva;

    return [
        "total_ht" => $total_ht,
        "tva" => $tva,
        "total_ttc" => $total_ttc
    ];
}

/* ------------------------------
   FONCTIONS POUR LES RAPPORTS
--------------------------------*/

function filtrer_factures_par_date($date) {
    $factures = charger_factures();
    $resultat = [];

    foreach ($factures as $f) {
        if ($f['date'] === $date) {
            $resultat[] = $f;
        }
    }

    return $resultat;
}

function filtrer_factures_par_mois($annee, $mois) {
    $factures = charger_factures();
    $resultat = [];

    foreach ($factures as $f) {
        list($y, $m, $d) = explode("-", $f['date']);
        if ($y == $annee && $m == $mois) {
            $resultat[] = $f;
        }
    }

    return $resultat;
}
