<?php
include '../../includes/header.php';
require_once '../../includes/fonctions-produits.php';
require_once '../../includes/fonctions-factures.php';

if ($_SESSION['user']['role'] === 'manager' || $_SESSION['user']['role'] === 'superadmin' || $_SESSION['user']['role'] === 'caissier') {
    // OK
} else {
    echo "<div class='error'>Accès refusé.</div>";
    include '../../includes/footer.php';
    exit;
}

if (!isset($_SESSION['facture'])) {
    $_SESSION['facture'] = [];
}

$erreur = "";
$produit = null;

// Recherche produit après scan
if (isset($_GET['code'])) {
    $produit = trouver_produit($_GET['code']);
    if (!$produit) {
        $erreur = "Produit inconnu. Demandez au Manager de l’enregistrer.";
    }
}

// Ajout d’un article
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code_barre'];
    $quantite = intval($_POST['quantite']);

    $produit = trouver_produit($code);

    if (!$produit) {
        $erreur = "Produit introuvable.";
    } elseif ($quantite > $produit['quantite_stock']) {
        $erreur = "Stock insuffisant.";
    } else {
        $sous_total = $produit['prix_unitaire_ht'] * $quantite;

        $_SESSION['facture'][] = [
            "code_barre" => $code,
            "nom" => $produit['nom'],
            "prix_unitaire_ht" => $produit['prix_unitaire_ht'],
            "quantite" => $quantite,
            "sous_total_ht" => $sous_total
        ];
    }
}
?>

<div class="page-facturation">

<div class="card">
    <h2>Nouvelle Facture</h2>
</div>

<!-- Scanner -->
<div class="card">
    <h3>Scanner un article</h3>
    <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
        Placez le code-barres devant la caméra. Le système le détectera automatiquement.
    </p>
    <video id="preview" autoplay playsinline muted style="width:100%; border-radius:8px; background:#000; display:block;"></video>
    <div style="margin-top: 10px;">
        <button type="button" onclick="manualScanInput()" style="background: #3498DB; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
            Saisie manuelle
        </button>
    </div>
</div>

<script src="/facturation/assets/js/scanner.js"></script>

<?php if ($erreur): ?>
    <div class="error"><?= $erreur ?></div>
<?php endif; ?>

<?php if ($produit): ?>
    <div class="card">
        <h3><?= $produit['nom'] ?></h3>
        <p><strong>Prix :</strong> <?= $produit['prix_unitaire_ht'] ?> CDF</p>
        <p><strong>Stock :</strong> <?= $produit['quantite_stock'] ?></p>

        <form method="POST">
            <input type="hidden" name="code_barre" value="<?= $produit['code_barre'] ?>">

            <label>Quantité</label>
            <input type="number" name="quantite" min="1" required>

            <button type="submit">Ajouter</button>
        </form>
    </div>
<?php endif; ?>

<!-- Tableau des articles -->
<div class="card">
    <h3>Articles ajoutés</h3>

    <table class="table">
        <tr>
            <th>Désignation</th>
            <th>Prix HT</th>
            <th>Qté</th>
            <th>Sous-total</th>
        </tr>

        <?php foreach ($_SESSION['facture'] as $a): ?>
            <tr>
                <td><?= $a['nom'] ?></td>
                <td><?= $a['prix_unitaire_ht'] ?></td>
                <td><?= $a['quantite'] ?></td>
                <td><?= $a['sous_total_ht'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="/facturation/modules/facturation/calcul.php">
        <button style="margin-top:15px;">Valider la facture</button>
    </a>
</div>

</div>

<?php include '../../includes/footer.php'; ?>
