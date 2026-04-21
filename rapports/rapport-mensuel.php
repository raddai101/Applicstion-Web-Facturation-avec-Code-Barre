<?php
include '../includes/header.php';
require_once '../includes/fonctions-factures.php';

$annee = isset($_GET['annee']) ? $_GET['annee'] : date("Y");
$mois = isset($_GET['mois']) ? $_GET['mois'] : date("m");

$factures = filtrer_factures_par_mois($annee, $mois);

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
    <h2>Rapport Mensuel</h2>

    <form method="GET">
        <label>Mois</label>
        <select name="mois">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= str_pad($m, 2, "0", STR_PAD_LEFT) ?>"
                    <?= $mois == str_pad($m, 2, "0", STR_PAD_LEFT) ? "selected" : "" ?>>
                    <?= str_pad($m, 2, "0", STR_PAD_LEFT) ?>
                </option>
            <?php endfor; ?>
        </select>

        <label>Année</label>
        <input type="number" name="annee" value="<?= $annee ?>">

        <button type="submit">Afficher</button>
    </form>
</div>

<?php if (empty($factures)): ?>
    <div class="rapport-box">
        Aucune facture enregistrée pour ce mois.
    </div>
<?php else: ?>

    <?php foreach ($factures as $f): ?>
        <div class="rapport-box">
            <p><strong>ID :</strong> <?= $f['id_facture'] ?></p>
            <p><strong>Date :</strong> <?= $f['date'] ?></p>
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
