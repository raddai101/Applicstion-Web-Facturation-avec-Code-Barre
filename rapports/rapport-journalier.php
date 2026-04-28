<?php
// ============================================================
// rapports/rapport-journalier.php
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__) . '/includes/fonctions-factures.php';
require_once dirname(__DIR__) . '/auth/session.php';
verifier_role(['manager','superadmin']);

$page_title = 'Rapport Journalier';
$date_param = $_GET['date'] ?? date('Y-m-d');
$factures   = factures_du_jour();

// Filtrer par date si spécifié
if ($date_param !== date('Y-m-d')) {
    $all = charger_factures();
    $factures = array_filter($all, fn($f) => $f['date'] === $date_param);
}

$total_ttc   = array_sum(array_column($factures, 'total_ttc'));
$total_ht    = array_sum(array_column($factures, 'total_ht'));
$total_tva   = array_sum(array_column($factures, 'tva'));
$nb_articles = 0;
foreach ($factures as $f) { $nb_articles += count($f['articles']); }

require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="full-page">
  <div class="page-header">
    <div>
      <div class="page-title">📊 Rapport Journalier</div>
      <div class="page-sub"><?= $date_param ?></div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <form method="GET" style="display:flex;gap:8px;align-items:center">
        <input type="date" name="date" value="<?= $date_param ?>" style="width:160px">
        <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
      </form>
      <a href="<?= BASE_URL ?>/rapports/rapport-mensuel.php" class="btn btn-secondary">📅 Mensuel</a>
    </div>
  </div>

  <div class="stat-grid">
    <div class="stat-card stat-accent"><div class="stat-val"><?= count($factures) ?></div><div class="stat-label">Factures émises</div></div>
    <div class="stat-card stat-green"><div class="stat-val"><?= formater_montant($total_ttc) ?></div><div class="stat-label">Chiffre d'affaires TTC</div></div>
    <div class="stat-card"><div class="stat-val" style="color:var(--text2)"><?= formater_montant($total_ht) ?></div><div class="stat-label">Total HT</div></div>
    <div class="stat-card stat-amber"><div class="stat-val"><?= formater_montant($total_tva) ?></div><div class="stat-label">TVA collectée</div></div>
    <div class="stat-card"><div class="stat-val"><?= $nb_articles ?></div><div class="stat-label">Articles vendus</div></div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="title-group"><span style="font-size:18px">📋</span><div class="section-label">Détail des factures</div></div>
    </div>
    <?php if (empty($factures)): ?>
      <div class="empty-state"><div class="empty-icon">📋</div><div class="empty-title">Aucune facture ce jour</div></div>
    <?php else: ?>
      <table class="data-table">
        <thead>
          <tr><th>ID Facture</th><th>Heure</th><th>Caissier</th><th>Articles</th><th>Total HT</th><th>TVA</th><th>Total TTC</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach (array_reverse(array_values($factures)) as $f): ?>
          <tr>
            <td class="mono" style="color:var(--accent2);font-weight:600"><?= htmlspecialchars($f['id_facture']) ?></td>
            <td><?= $f['heure'] ?></td>
            <td><?= htmlspecialchars($f['caissier']) ?></td>
            <td style="font-weight:600"><?= count($f['articles']) ?></td>
            <td class="mono"><?= formater_montant($f['total_ht']) ?></td>
            <td class="mono" style="color:var(--amber)"><?= formater_montant($f['tva']) ?></td>
            <td class="mono" style="color:var(--green);font-weight:700"><?= formater_montant($f['total_ttc']) ?></td>
            <td><a href="<?= BASE_URL ?>/modules/facturation/afficher-facture.php?id=<?= urlencode($f['id_facture']) ?>" class="btn btn-secondary btn-sm">👁 Voir</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
