<?php
include '../../includes/header.php';
require_once '../../includes/fonctions-produits.php';

// Vérification du rôle
if ($_SESSION['user']['role'] === 'caissier') {
    echo "<div class='error'>Accès refusé : seuls les Managers et Super Admin peuvent enregistrer des produits.</div>";
    include '../../includes/footer.php';
    exit;
}

$erreurs = [];
$succes = "";
$produit_existant = null;

// Si un code-barres est envoyé
if (isset($_GET['code'])) {
    $code = trim($_GET['code']);
    $produit_existant = trouver_produit($code);
}

// Si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code_barre = trim($_POST['code_barre']);
    $nom = trim($_POST['nom']);
    $prix = trim($_POST['prix_unitaire_ht']);
    $quantite = trim($_POST['quantite_stock']);
    $date_exp = trim($_POST['date_expiration']);

    if ($nom === "" || $prix === "" || $quantite === "" || $date_exp === "") {
        $erreurs[] = "Tous les champs sont obligatoires.";
    }

    if (!is_numeric($prix) || $prix <= 0) {
        $erreurs[] = "Le prix doit être un nombre positif.";
    }

    if (!is_numeric($quantite) || $quantite < 0) {
        $erreurs[] = "La quantité doit être un nombre positif.";
    }

    if (empty($erreurs)) {
        $produit = [
            "code_barre" => $code_barre,
            "nom" => $nom,
            "prix_unitaire_ht" => floatval($prix),
            "quantite_stock" => intval($quantite),
            "date_expiration" => $date_exp,
            "date_enregistrement" => date("Y-m-d")
        ];

        ajouter_produit($produit);
        $succes = "Produit enregistré avec succès.";
    }
}
?>

<div class="page-produits page-enregistrer-produit">

<div class="card">
    <h2>Enregistrer un Produit</h2>
</div>

<!-- Scanner -->
<div class="card">
    <h3>Scanner un code-barres</h3>
    <video id="preview" style="width:100%; border-radius:8px;"></video>
</div>

<script src="/facturation/assets/js/scanner.js"></script>

<?php if ($produit_existant): ?>
    <div class="card produit-info">
        <h3>Produit déjà enregistré</h3>
        <p><strong>Nom :</strong> <?= $produit_existant['nom'] ?></p>
        <p><strong>Prix :</strong> <?= $produit_existant['prix_unitaire_ht'] ?> CDF</p>
        <p><strong>Stock :</strong> <?= $produit_existant['quantite_stock'] ?></p>
        <p><strong>Expiration :</strong> <?= $produit_existant['date_expiration'] ?></p>
    </div>

<?php elseif (isset($_GET['code'])): ?>

    <div class="card">
        <h3>Nouveau produit</h3>

        <?php if (!empty($erreurs)): ?>
            <div class="error">
                <?php foreach ($erreurs as $e) echo "<p>$e</p>"; ?>
            </div>
        <?php endif; ?>

        <?php if ($succes): ?>
            <div class="card" style="border-left:5px solid #1E9E63;">
                <p><?= $succes ?></p>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="code_barre" value="<?= $_GET['code'] ?>">

            <label>Nom du produit</label>
            <input type="text" name="nom" required>

            <label>Prix unitaire HT (CDF)</label>
            <input type="number" name="prix_unitaire_ht" required>

            <label>Quantité en stock</label>
            <input type="number" name="quantite_stock" required>

            <label>Date d'expiration</label>
            <input type="date" name="date_expiration" required>

            <button type="submit">Enregistrer</button>
        </form>
    </div>

<?php endif; ?>

</div>

<?php include '../../includes/footer.php'; ?>
