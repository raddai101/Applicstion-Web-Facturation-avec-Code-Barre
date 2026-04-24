<?php
require_once  '../../auth/session.php';
$title = 'Scanner-Facture';
include '../../includes/header.php';
require_once '../../includes/fonctions-produits.php';
require_once '../../includes/fonctions-factures.php';

// ── Contrôle d'accès ──────────────────────────────────────────────────────────
$rolesAutorises = ['manager', 'superadmin', 'caissier'];
if (!in_array($_SESSION['user']['role'], $rolesAutorises)) {
    echo "<div class='error'>Accès refusé.</div>";
    include '../../includes/footer.php';   
    exit;
}

if (!isset($_SESSION['facture'])) {
    $_SESSION['facture'] = [];
}

$erreur  = "";
$produit = null;
$codeScanne = null;

// ── Recherche produit après scan ──────────────────────────────────────────────
if (isset($_GET['code']) && $_GET['code'] !== '') {
    $codeScanne = trim($_GET['code']);
    $produit    = trouver_produit($codeScanne);
    // $produit === null → produit inconnu → afficher formulaire d'enregistrement
}

// ── Enregistrement d'un nouveau produit inconnu ───────────────────────────────
$nouveauProduitEnregistre = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'nouveau_produit') {
    $codeScanne = trim($_POST['code_barre']);
    $nom        = trim($_POST['nom']);
    $prix       = trim($_POST['prix_unitaire_ht']);
    $quantite   = trim($_POST['quantite_stock']);
    $dateExp    = trim($_POST['date_expiration']);

    // Validation
    if (empty($nom === '' || $prix === '' || $quantite === '' || $dateExp === '')) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif (!is_numeric($prix) || floatval($prix) <= 0) {
        $erreur = "Le prix doit être un nombre positif.";
    } elseif (!is_numeric($quantite) || intval($quantite) < 0) {
        $erreur = "La quantité doit être un entier positif ou nul.";
    } elseif (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $dateExp)) {
        $erreur = "La date d'expiration doit être au format MM-JJ-AAAA.";
    } else {
        // Conversion MM-JJ-AAAA → Y-m-d pour le stockage
        [$mm, $jj, $aaaa] = explode('-', $dateExp);
        $dateStockage = "$aaaa-$mm-$jj";

        if (!checkdate((int)$mm, (int)$jj, (int)$aaaa)) {
            $erreur = "Date invalide.";
        } else {
            ajouter_produit([
                "code_barre"          => $codeScanne,
                "nom"                 => $nom,
                "prix_unitaire_ht"    => floatval($prix),
                "quantite_stock"      => intval($quantite),
                "date_expiration"     => $dateStockage,
                "date_enregistrement" => date("Y-m-d")
            ]);
            $produit = trouver_produit($codeScanne);
            $nouveauProduitEnregistre = true;
        }
    }
}

// ── Ajout d'un article à la facture en cours ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter_article') {
    $code     = $_POST['code_barre'];
    $quantite = intval($_POST['quantite']);
    $produit  = trouver_produit($code);

    if (!$produit) {
        $erreur = "Produit introuvable.";
    } elseif ($quantite < 1) {
        $erreur = "La quantité doit être au moins 1.";
    } elseif ($quantite > $produit['quantite_stock']) {
        $erreur = "Stock insuffisant (disponible : " . $produit['quantite_stock'] . ").";
    } else {
        $_SESSION['facture'][] = [
            "code_barre"       => $code,
            "nom"              => $produit['nom'],
            "prix_unitaire_ht" => $produit['prix_unitaire_ht'],
            "quantite"         => $quantite,
            "sous_total_ht"    => $produit['prix_unitaire_ht'] * $quantite
        ];
        // Réinitialise pour afficher le scanner à nouveau
        $produit    = null;
        $codeScanne = null;
    }
}
?>

<div class="page-facturation">

<!-- ── En-tête ── -->
<div class="card">
    <h2>Nouvelle Facture</h2>
    <p style="color:#7F8C8D; font-size:14px;">
        Caissier : <strong><?= htmlspecialchars($_SESSION['user']['nom_complet']) ?></strong>
    </p>
</div>

<?php if ($erreur): ?>
    <div class="error"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════════════════
     SECTION SCANNER  (masquée si on affiche déjà les infos d'un produit)
════════════════════════════════════════════════════════════════════════════ -->
<?php if (!$produit && !$codeScanne): ?>
<div class="card">
    <h3>📷 Scanner un article</h3>
    <p style="font-size:13px; color:#666; margin-bottom:10px;">
        Appuyez sur <strong>📸 Prendre une photo</strong> pour capturer le code-barres.
        La détection est instantanée. Si la photo échoue deux fois, le scanner
        bascule automatiquement en mode vidéo continu.
    </p>

    <!-- L'élément <video> est la cible de la caméra -->
    <video id="preview" autoplay playsinline muted style="width:100%; border-radius:8px; background:#000; display:block;"></video>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════════════════
     PRODUIT CONNU : afficher ses infos + formulaire quantité
════════════════════════════════════════════════════════════════════════════ -->
<?php if ($produit && !$nouveauProduitEnregistre): ?>
<div class="card" style="border-left: 5px solid #27AE60;">
    <h3><?= htmlspecialchars($produit['nom']) ?></h3>
    <p><strong>Code-barres :</strong> <?= htmlspecialchars($produit['code_barre']) ?></p>
    <p><strong>Prix unitaire HT :</strong> <?= number_format($produit['prix_unitaire_ht'], 2) ?> CDF</p>
    <p><strong>Stock disponible :</strong> <?= $produit['quantite_stock'] ?> unité(s)</p>
    <p><strong>Expiration :</strong> <?= htmlspecialchars($produit['date_expiration']) ?></p>

    <form method="POST" style="margin-top:15px;">
        <input type="hidden" name="action"     value="ajouter_article">
        <input type="hidden" name="code_barre" value="<?= htmlspecialchars($produit['code_barre']) ?>">

        <label for="quantite">Quantité à facturer</label>
        <input type="number" id="quantite" name="quantite"
               min="1" max="<?= $produit['quantite_stock'] ?>"
               value="1" required style="width:100px;">

        <button type="submit" style="margin-left:10px;">➕ Ajouter à la facture</button>
    </form>

    <p style="margin-top:12px;">
        <a href="nouvelle-facture.php" style="color:#3498DB; font-size:13px;">
            ← Scanner un autre article
        </a>
    </p>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════════════════
     PRODUIT INCONNU : formulaire d'enregistrement rapide
════════════════════════════════════════════════════════════════════════════ -->
<?php if ($codeScanne && !$produit): ?>
<div class="card" style="border-left: 5px solid #E67E22;">
    <h3>⚠️ Produit inconnu</h3>
    <p>
        Le code <strong><?= htmlspecialchars($codeScanne) ?></strong> n'est pas encore
        enregistré dans le système. Remplissez le formulaire ci-dessous pour l'ajouter.
    </p>

    <form method="POST" style="margin-top:15px;">
        <input type="hidden" name="action"     value="nouveau_produit">
        <input type="hidden" name="code_barre" value="<?= htmlspecialchars($codeScanne) ?>">

        <label for="nom">Nom du produit</label>
        <input type="text" id="nom" name="nom"
               placeholder="Ex : Fanta Orange 50cl" required>

        <label for="prix_unitaire_ht">Prix unitaire HT (CDF)</label>
        <input type="number" id="prix_unitaire_ht" name="prix_unitaire_ht"
               step="0.01" min="0.01" placeholder="Ex : 1200" required>

        <label for="date_expiration">Date d'expiration (MM-JJ-AAAA)</label>
        <input type="text" id="date_expiration" name="date_expiration"
               placeholder="Ex : 12-31-2026"
               pattern="\d{2}-\d{2}-\d{4}"
               title="Format attendu : MM-JJ-AAAA"
               required>

        <label for="quantite_stock">Quantité initiale en stock</label>
        <input type="number" id="quantite_stock" name="quantite_stock"
               min="0" placeholder="Ex : 50" required>

        <div style="margin-top:15px; display:flex; gap:10px; flex-wrap:wrap;">
            <button type="submit">💾 Enregistrer le produit</button>
            <a href="nouvelle-facture.php">
                <button type="button" style="background:#95A5A6;">
                    ← Rescanner
                </button>
            </a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Confirmation nouveau produit enregistré -->
<?php if ($nouveauProduitEnregistre && $produit): ?>
<div class="card" style="border-left:5px solid #1E9E63; background:#F0FFF4;">
    <p>✅ <strong><?= htmlspecialchars($produit['nom']) ?></strong> enregistré avec succès !</p>
</div>

<div class="card" style="border-left: 5px solid #27AE60;">
    <h3><?= htmlspecialchars($produit['nom']) ?></h3>
    <p><strong>Prix unitaire HT :</strong> <?= number_format($produit['prix_unitaire_ht'], 2) ?> CDF</p>
    <p><strong>Stock :</strong> <?= $produit['quantite_stock'] ?> unité(s)</p>

    <form method="POST" style="margin-top:15px;">
        <input type="hidden" name="action"     value="ajouter_article">
        <input type="hidden" name="code_barre" value="<?= htmlspecialchars($produit['code_barre']) ?>">

        <label for="quantite2">Quantité à facturer</label>
        <input type="number" id="quantite2" name="quantite"
               min="1" max="<?= $produit['quantite_stock'] ?>"
               value="1" required style="width:100px;">

        <button type="submit" style="margin-left:10px;">➕ Ajouter à la facture</button>
    </form>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════════════════
     ARTICLES AJOUTÉS À LA FACTURE EN COURS
════════════════════════════════════════════════════════════════════════════ -->
<div class="card">
    <h3>🧾 Articles de la facture en cours</h3>

    <?php if (empty($_SESSION['facture'])): ?>
        <p style="color:#95A5A6; font-style:italic;">
            Aucun article pour l'instant. Scannez un produit pour commencer.
        </p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Prix HT (CDF)</th>
                    <th>Qté</th>
                    <th>Sous-total HT</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalHT = 0;
                foreach ($_SESSION['facture'] as $a):
                    $totalHT += $a['sous_total_ht'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($a['nom']) ?></td>
                    <td><?= number_format($a['prix_unitaire_ht'], 2) ?></td>
                    <td><?= $a['quantite'] ?></td>
                    <td><?= number_format($a['sous_total_ht'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>Total HT</strong></td>
                    <td><strong><?= number_format($totalHT, 2) ?> CDF</strong></td>
                </tr>
                <tr>
                    <td colspan="3">TVA (18 %)</td>
                    <td><?= number_format($totalHT * 0.18, 2) ?> CDF</td>
                </tr>
                <tr>
                    <td colspan="3"><strong>Total TTC</strong></td>
                    <td><strong><?= number_format($totalHT * 1.18, 2) ?> CDF</strong></td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top:15px; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="/facturation/modules/facturation/calcul.php">
                <button>✅ Valider la facture</button>
            </a>
            <a href="nouvelle-facture.php">
                <button style="background:#3498DB;">📷 Scanner un autre article</button>
            </a>
        </div>
    <?php endif; ?>
</div>

</div><!-- .page-facturation -->

<!-- Chargement du scanner (modules ES6) -->
<script type="module" src="/facturation/assets/js/scanner.js"></script>

<?php include '../../includes/footer.php'; ?>
