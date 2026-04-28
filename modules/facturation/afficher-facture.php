<?php
// ============================================================
// modules/facturation/afficher-facture.php
// ============================================================
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-factures.php';
require_once dirname(__DIR__, 2) . '/auth/session.php';

verifier_role(['caissier','manager','superadmin']);
$page_title = 'Facture';

$id      = trim($_GET['id'] ?? '');
$factures = charger_factures();
$facture  = null;
foreach ($factures as $f) {
    if ($f['id_facture'] === $id) { $facture = $f; break; }
}
if (!$facture) {
    $_SESSION['flash_error'] = 'Facture introuvable : ' . htmlspecialchars($id);
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="full-page">
  <div class="page-header no-print">
    <div>
      <div class="page-title">🧾 Facture <?= htmlspecialchars($facture['id_facture']) ?></div>
      <div class="page-sub"><?= $facture['date'] ?> à <?= $facture['heure'] ?></div>
    </div>
    <div style="display:flex;gap:8px">
      <button onclick="window.print()" class="btn btn-secondary">🖨 Imprimer</button>
      <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">+ Nouvelle facture</a>
    </div>
  </div>

  <div class="card" style="max-width:680px">
    <div class="card-header">
      <div class="title-group">
        <span style="font-size:18px">🧾</span>
        <div>
          <div class="section-label"><?= htmlspecialchars($facture['id_facture']) ?></div>
          <div class="section-sub">Caissier: <?= htmlspecialchars($facture['caissier']) ?></div>
        </div>
      </div>
      <span class="badge badge-green">VALIDÉE</span>
    </div>
    <div class="card-body">
      <!-- Tableau articles -->
      <table class="data-table" style="margin-bottom:20px">
        <thead>
          <tr>
            <th>Désignation</th>
            <th>Prix unit. HT</th>
            <th>Qté</th>
            <th>Sous-total HT</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($facture['articles'] as $a): ?>
          <tr>
            <td style="font-weight:600"><?= htmlspecialchars($a['nom']) ?></td>
            <td class="mono"><?= formater_montant($a['prix_unitaire_ht']) ?></td>
            <td style="font-weight:600"><?= $a['quantite'] ?></td>
            <td class="mono" style="color:var(--accent2)"><?= formater_montant($a['sous_total_ht']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Totaux -->
      <div style="max-width:300px;margin-left:auto">
        <div class="tr-row tr-ht" style="margin-bottom:10px">
          <span class="tr-label" style="font-size:13px">Total HT</span>
          <span class="tr-val" style="font-size:14px"><?= formater_montant($facture['total_ht']) ?></span>
        </div>
        <div class="tr-row tr-tva" style="margin-bottom:10px">
          <span class="tr-label" style="font-size:13px">TVA (<?= TVA_TAUX * 100 ?>%)</span>
          <span class="tr-val" style="font-size:14px;color:var(--amber)"><?= formater_montant($facture['tva']) ?></span>
        </div>
        <div class="total-big">
          <span class="tb-label">NET À PAYER</span>
          <span class="tb-val"><?= formater_montant($facture['total_ttc']) ?></span>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
