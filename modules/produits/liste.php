<?php
// ============================================================
// modules/produits/liste.php — Catalogue produits
// ============================================================
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-produits.php';
require_once dirname(__DIR__, 2) . '/auth/session.php';

verifier_role(['manager','superadmin']);
$page_title = 'Catalogue Produits';
$produits   = charger_produits();
$flash_ok   = flash('flash_ok');

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="full-page">
  <div class="page-header">
    <div>
      <div class="page-title">📦 Catalogue Produits</div>
      <div class="page-sub"><?= count($produits) ?> produit(s) enregistré(s)</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/produits/enregistrer.php" class="btn btn-primary">+ Nouveau produit</a>
  </div>

  <?php if ($flash_ok): ?>
    <div class="alert alert-green">✓ <?= htmlspecialchars($flash_ok) ?></div>
  <?php endif; ?>

  <div class="stat-grid">
    <div class="stat-card stat-accent">
      <div class="stat-val"><?= count($produits) ?></div>
      <div class="stat-label">Produits total</div>
    </div>
    <div class="stat-card stat-green">
      <div class="stat-val"><?= count(array_filter($produits, fn($p) => $p['quantite_stock'] > 10)) ?></div>
      <div class="stat-label">En stock normal</div>
    </div>
    <div class="stat-card stat-amber">
      <div class="stat-val"><?= count(array_filter($produits, fn($p) => $p['quantite_stock'] > 0 && $p['quantite_stock'] <= 10)) ?></div>
      <div class="stat-label">Stock faible</div>
    </div>
    <div class="stat-card">
      <div class="stat-val" style="color:var(--red)"><?= count(array_filter($produits, fn($p) => $p['quantite_stock'] === 0)) ?></div>
      <div class="stat-label">Rupture de stock</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="title-group">
        <span style="font-size:18px">📋</span>
        <div class="section-label">Liste des produits</div>
      </div>
      <input type="text" id="search" placeholder="🔍 Rechercher..." style="width:220px" oninput="filterTable()">
    </div>
    <?php if (empty($produits)): ?>
      <div class="empty-state">
        <div class="empty-icon">📦</div>
        <div class="empty-title">Catalogue vide</div>
        <div style="font-size:12px;color:var(--text3)">Scannez des produits pour les enregistrer</div>
      </div>
    <?php else: ?>
      <table class="data-table" id="prod-table">
        <thead>
          <tr>
            <th>Code-barres</th>
            <th>Nom</th>
            <th>Prix HT</th>
            <th>Stock</th>
            <th>Expiration</th>
            <th>Enregistré</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($produits as $p): ?>
          <tr>
            <td class="mono"><?= htmlspecialchars($p['code_barre']) ?></td>
            <td style="font-weight:600"><?= htmlspecialchars($p['nom']) ?></td>
            <td class="mono" style="color:var(--accent2)"><?= number_format($p['prix_unitaire_ht'], 0, ',', '.') ?> <?= DEVISE ?></td>
            <td>
              <?php
                $s = (int)$p['quantite_stock'];
                $cls = $s > 10 ? 'badge-green' : ($s > 0 ? 'badge-amber' : 'badge-red');
              ?>
              <span class="badge <?= $cls ?>"><?= $s ?></span>
            </td>
            <td><?= htmlspecialchars($p['date_expiration']) ?></td>
            <td style="color:var(--text3);font-size:11px"><?= htmlspecialchars($p['date_enregistrement']) ?></td>
            <td>
              <a href="<?= BASE_URL ?>/modules/produits/enregistrer.php?code=<?= urlencode($p['code_barre']) ?>"
                 class="btn btn-secondary btn-sm">✏ Modifier</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
<script>
function filterTable() {
  var q = document.getElementById('search').value.toLowerCase();
  document.querySelectorAll('#prod-table tbody tr').forEach(function(tr) {
    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>
