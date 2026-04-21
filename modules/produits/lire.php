<?php
include '../../includes/header.php';
require_once '../../includes/fonctions-produits.php';

$produit = null;

if (isset($_GET['code'])) {
    $produit = trouver_produit($_GET['code']);
}
?>

<div class="page-produits">

<div class="card">
    <h2>Informations Produit</h2>
</div>

<?php if (!$produit): ?>
    <div class="error">Produit introuvable.</div>
<?php else: ?>
    <div class="card produit-info">
        <p><strong>Nom :</strong> <?= $produit['nom'] ?></p>
        <p><strong>Prix :</strong> <?= $produit['prix_unitaire_ht'] ?> CDF</p>
        <p><strong>Stock :</strong> <?= $produit['quantite_stock'] ?></p>
        <p><strong>Expiration :</strong> <?= $produit['date_expiration'] ?></p>
    </div>
<?php endif; ?>

</div>

<?php include '../../includes/footer.php'; ?>
