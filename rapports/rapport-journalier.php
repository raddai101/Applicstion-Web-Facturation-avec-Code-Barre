<?php
require_once  '../auth/session.php';
$title = 'rapport-journalier';
include '../includes/header.php';
require_once '../includes/fonctions-factures.php';

$date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
$factures = filtrer_factures_par_date($date);

$total_ht = 0;
$total_tva = 0;
$total_ttc = 0;

foreach ($factures as $f) {
    $total_ht += $f['total_ht'];
    $total_tva += $f['tva'];
    $total_ttc += $f['total_ttc'];
}
?>

<div class="page-rapports">

<div class="card">
    <h2>Rapport Journalier</h2>

    <form method="GET">
        <label>Date</label>
        <input type="date" name="date" value="<?= $date ?>">
        <button type="submit">Afficher</button>
    </form>
</div>

<?php if (empty($factures)): ?>
    <div class="rapport-box">
        Aucune facture enregistrée pour cette date.
    </div>
<?php else: ?>

    <?php foreach ($factures as $f): ?>
        <div class="rapport-box">
            <p><strong>ID :</strong> <?= $f['id_facture'] ?></p>
            <p><strong>Heure :</strong> <?= $f['heure'] ?></p>
            <p><strong>Caissier :</strong> <?= $f['caissier'] ?></p>
            <p><strong>Total TTC :</strong> <?= $f['total_ttc'] ?> CDF</p>
        </div>
    <?php endforeach; ?>

    <div class="total-box">
        Total HT : <?= $total_ht ?> CDF<br>
        TVA : <?= $total_tva ?> CDF<br>
        Total TTC : <?= $total_ttc ?> CDF
    </div>

<?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
