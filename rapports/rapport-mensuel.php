<?php
// ============================================================
// rapports/rapport-mensuel.php
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__) . '/includes/fonctions-factures.php';
require_once dirname(__DIR__) . '/auth/session.php';
verifier_role(['manager','superadmin']);

$page_title  = 'Rapport Mensuel';
$mois_param  = $_GET['mois'] ?? date('Y-m');
$factures    = factures_du_mois($mois_param);

$total_ttc  = array_sum(array_column($factures, 'total_ttc'));
$total_ht   = array_sum(array_column($factures, 'total_ht'));
$total_tva  = array_sum(array_column($factures, 'tva'));

// Regrouper par jour
$par_jour = [];
foreach ($factures as $f) {
    $j = $f['date'];
    if (!isset($par_jour[$j])) $par_jour[$j] = ['nb' => 0, 'total' => 0];
    $par_jour[$j]['nb']++;
    $par_jour[$j]['total'] += $f['total_ttc'];
}
krsort($par_jour);

require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="full-page">
  <div class="page-header">
    <div>
      <div class="page-title">📅 Rapport Mensuel</div>
      <div class="page-sub"><?= $mois_param ?></div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <form method="GET" style="display:flex;gap:8px;align-items:center">
        <input type="month" name="mois" value="<?= $mois_param ?>" style="width:160px">
        <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
      </form>
      <a href="<?= BASE_URL ?>/rapports/rapport-journalier.php" class="btn btn-secondary">📊 Journalier</a>
    </div>
  </div>

  <div class="stat-grid">
    <div class="stat-card stat-accent"><div class="stat-val"><?= count($factures) ?></div><div class="stat-label">Factures du mois</div></div>
    <div class="stat-card stat-green"><div class="stat-val"><?= formater_montant($total_ttc) ?></div><div class="stat-label">CA TTC du mois</div></div>
    <div class="stat-card"><div class="stat-val" style="color:var(--text2)"><?= formater_montant($total_ht) ?></div><div class="stat-label">Total HT</div></div>
    <div class="stat-card stat-amber"><div class="stat-val"><?= formater_montant($total_tva) ?></div><div class="stat-label">TVA collectée</div></div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="title-group"><span style="font-size:18px">📈</span><div class="section-label">Synthèse par jour</div></div>
    </div>
    <?php if (empty($par_jour)): ?>
      <div class="empty-state"><div class="empty-icon">📅</div><div class="empty-title">Aucune donnée pour ce mois</div></div>
    <?php else: ?>
      <table class="data-table">
        <thead><tr><th>Date</th><th>Nb Factures</th><th>Total TTC</th></tr></thead>
        <tbody>
          <?php foreach ($par_jour as $date => $d): ?>
          <tr>
            <td><?= $date ?></td>
            <td style="font-weight:600"><?= $d['nb'] ?></td>
            <td class="mono" style="color:var(--green);font-weight:700"><?= formater_montant($d['total']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
