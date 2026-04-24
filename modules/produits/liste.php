<?php
require_once  '../../auth/session.php';
$title = 'liste-produits';
include '../../includes/header.php';
require_once '../../includes/fonctions-produits.php';

$produits = charger_produits();
?>

<div class="page-produits page-liste-produits">

<div class="card">
    <h2>Liste des Produits</h2>
</div>

<table class="table">
    <tr>
        <th>Code-barres</th>
        <th>Nom</th>
        <th>Prix HT</th>
        <th>Stock</th>
        <th>Expiration</th>
    </tr>

    <?php foreach ($produits as $p): ?>
        <tr>
            <td><?= $p['code_barre'] ?></td>
            <td><?= $p['nom'] ?></td>
            <td><?= $p['prix_unitaire_ht'] ?> CDF</td>
            <td><?= $p['quantite_stock'] ?></td>
            <td><?= $p['date_expiration'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>

</div>

<?php include '../../includes/footer.php'; ?>
