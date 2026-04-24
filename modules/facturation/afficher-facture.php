<?php
require_once __DIR__ . '/../auth/session.php';
$title = 'Genrer facture';
include '../../includes/header.php';
require_once '../../includes/fonctions-factures.php';

$factures = charger_factures();
$facture = null;

foreach ($factures as $f) {
    if ($f['id_facture'] === $_GET['id']) {
        $facture = $f;
        break;
    }
}

if (!$facture) {
    echo "<div class='error'>Facture introuvable.</div>";
    include '../../includes/footer.php';
    exit;
}
?>

<div class="page-facturation">

<div class="card">
    <h2>Facture <?= $facture['id_facture'] ?></h2>
    <p><strong>Date :</strong> <?= $facture['date'] ?> à <?= $facture['heure'] ?></p>
    <p><strong>Caissier :</strong> <?= $facture['caissier'] ?></p>
</div>

<table class="table">
    <tr>
        <th>Désignation</th>
        <th>Prix HT</th>
        <th>Qté</th>
        <th>Sous-total</th>
    </tr>

    <?php foreach ($facture['articles'] as $a): ?>
        <tr>
            <td><?= $a['nom'] ?></td>
            <td><?= $a['prix_unitaire_ht']?></td>
            <td><?= $a['quantite']?></td>
            <td><?= $a['sous_total_ht'] ?></td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <td colspan="3"><strong>Total HT</strong></td>
        <td><?= $facture['total_ht'] ?></td>
    </tr>

    <tr>
        <td colspan="3"><strong>TVA (18%)</strong></td>
        <td><?= $facture['tva'] ?></td>
    </tr>

    <tr>
        <td colspan="3"><strong>Total TTC</strong></td>
        <td><?= $facture['total_ttc'] ?></td>
    </tr>
</table>

</div>

<?php include '../../includes/footer.php'; ?>
