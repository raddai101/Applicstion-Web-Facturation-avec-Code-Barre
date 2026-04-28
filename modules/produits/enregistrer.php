<?php
// ============================================================
// modules/produits/enregistrer.php
// ============================================================
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-produits.php';
require_once dirname(__DIR__, 2) . '/auth/session.php';

verifier_role(['manager','superadmin']);
$page_title = 'Enregistrer un produit';

$code_pre = trim($_GET['code'] ?? '');
$errors   = [];
$success  = '';
$produit_existant = $code_pre ? chercher_produit_par_code($code_pre) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'code_barre'       => trim($_POST['code_barre'] ?? ''),
        'nom'              => trim($_POST['nom'] ?? ''),
        'prix_unitaire_ht' => $_POST['prix_unitaire_ht'] ?? '',
        'date_expiration'  => $_POST['date_expiration'] ?? '',
        'quantite_stock'   => $_POST['quantite_stock'] ?? '',
    ];
    $errors = valider_produit_form($data);
    if (empty($errors)) {
        $res = enregistrer_produit($data);
        $_SESSION['flash_ok'] = $res['message'];
        header('Location: ' . BASE_URL . '/modules/produits/liste.php');
        exit;
    }
}

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="full-page">
  <div class="page-header">
    <div>
      <div class="page-title">📦 Enregistrer un produit</div>
      <div class="page-sub">Associer un code-barres à ses informations commerciales</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/produits/liste.php" class="btn btn-secondary"> Catalogue</a>
  </div>

  <?php if ($errors): ?>
    <div class="alert alert-red">
      <div><strong>Erreurs de validation :</strong><ul style="margin:6px 0 0 16px">
        <?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul></div>
    </div>
  <?php endif; ?>

  <?php if ($produit_existant): ?>
    <div class="alert alert-amber">⚠️ Ce code-barres est déjà enregistré. Le formulaire ci-dessous mettra à jour le produit existant.</div>
  <?php endif; ?>

  <div class="card" style="max-width:600px">
    <div class="card-header">
      <div class="title-group">
        <span style="font-size:18px">📝</span>
        <div>
          <div class="section-label">Informations produit</div>
          <div class="section-sub">Tous les champs sont obligatoires</div>
        </div>
      </div>
    </div>
    <div class="card-body">

      <!-- Scanner intégré pour lire le code -->
      <div style="margin-bottom:16px">
        <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
          <div class="mode-tabs">
            <button class="mode-tab active" data-mode="cam">Caméra</button>
            <button class="mode-tab" data-mode="man">Manuel</button>
          </div>
          <span class="badge badge-blue" id="scan-badge">INACTIF</span>
        </div>
        <div class="video-wrap" id="vwrap" style="width:100%;height:160px">
          <video id="preview" autoplay playsinline muted></video>
          <div class="scan-overlay">
            <div class="sf"><div class="sc-tr"></div><div class="sc-bl"></div></div>
            <div class="scan-line" id="sline"></div>
          </div>
          <div class="vid-label" id="vlabel">Caméra inactive</div>
        </div>
        <div style="display:flex;gap:8px;margin-top:8px">
          <button class="btn btn-primary" id="btn-start" style="flex:1">▶ Scanner le code</button>
          <button class="btn btn-danger" id="btn-stop" style="display:none;flex:1">⏹ Arrêter</button>
        </div>
        <div class="manual-row" id="man-row" style="display:none;margin-top:8px">
          <input type="text" id="man-in" placeholder="Saisir code-barres...">
          <button id="btn-manual-ok">OK</button>
        </div>
        <div class="result-box" id="rbox" style="margin-top:8px">En attente...</div>
      </div>

      <form method="POST" id="form-produit">
        <div class="form-grid">
          <div class="form-field full">
            <label>Code-barres *</label>
            <input type="text" name="code_barre" id="field-code"
                   value="<?= htmlspecialchars($_POST['code_barre'] ?? $code_pre ?? ($produit_existant['code_barre'] ?? '')) ?>"
                   placeholder="Ex: 3017620422003" required>
          </div>
          <div class="form-field full">
            <label>Nom du produit *</label>
            <input type="text" name="nom"
                   value="<?= htmlspecialchars($_POST['nom'] ?? $produit_existant['nom'] ?? '') ?>"
                   placeholder="Ex: Huile de palme 1L" required>
          </div>
          <div class="form-field">
            <label>Prix unitaire HT (CDF) *</label>
            <input type="number" name="prix_unitaire_ht" min="1" step="any"
                   value="<?= htmlspecialchars($_POST['prix_unitaire_ht'] ?? $produit_existant['prix_unitaire_ht'] ?? '') ?>"
                   placeholder="1200" required>
          </div>
          <div class="form-field">
            <label>Quantité en stock *</label>
            <input type="number" name="quantite_stock" min="0"
                   value="<?= htmlspecialchars($_POST['quantite_stock'] ?? $produit_existant['quantite_stock'] ?? '') ?>"
                   placeholder="50" required>
          </div>
          <div class="form-field full">
            <label>Date d'expiration *</label>
            <input type="date" name="date_expiration" id="field-date-exp"
                   value="<?= htmlspecialchars($_POST['date_expiration'] ?? $produit_existant['date_expiration'] ?? '') ?>"
                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                   required>
            <small style="color:var(--text3);margin-top:4px;display:block">⚠️ La date doit être supérieure à aujourd'hui</small>
          </div>
          <div class="form-field full">
            <button type="submit" class="btn btn-primary btn-w100" style="margin-top:4px">
              💾 Enregistrer le produit
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/scanner.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // ✓ Initialiser le scanner
  scannerInit(function(code) {
    document.getElementById('field-code').value = code;
  });
  
  // ✓ Initialiser les listeners du mode manuel
  window.initManualModeListeners && window.initManualModeListeners();
});
</script>
