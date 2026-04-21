<?php
include '../../includes/header.php';
require_once '../../includes/fonctions-produits.php';
require_once '../../includes/fonctions-factures.php';

if (!isset($_SESSION['facture']) || empty($_SESSION['facture'])) {
    echo "<div class='error'>Aucun article dans la facture.</div>";
    include '../../includes/footer.php';
    exit;
}

$articles = $_SESSION['facture'];
$totaux = calculer_totaux($articles);

// Décrémentation du stock
$produits = charger_produits();

foreach ($articles as $a) {
    foreach ($produits as &$p) {
        if ($p['code_barre'] === $a['code_barre']) {
            $p['quantite_stock'] -= $a['quantite'];
        }
    }
}

sauvegarder_produits($produits);

// Création facture
$factures = charger_factures();

$facture = [
    "id_facture" => generer_id_facture(),
    "date" => date("Y-m-d"),
    "heure" => date("H:i:s"),
    "caissier" => $_SESSION['user']['identifiant'],
    "articles" => $articles,
    "total_ht" => $totaux['total_ht'],
    "tva" => $totaux['tva'],
    "total_ttc" => $totaux['total_ttc']
];

$factures[] = $facture;
sauvegarder_factures($factures);

// Nettoyage
$_SESSION['facture'] = [];

header("Location: /facturation/modules/facturation/afficher-facture.php?id=" . $facture['id_facture']);
exit;
?>
