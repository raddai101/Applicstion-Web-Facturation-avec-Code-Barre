<?php
// ============================================================
// modules/admin/gestion-comptes.php
// ============================================================
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__, 2) . '/auth/session.php';
verifier_role(['superadmin']);

$page_title = 'Gestion des Comptes';
$users      = charger_utilisateurs();
$flash_ok   = flash('flash_ok');
$flash_err  = flash('flash_error');

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="full-page">
  <div class="page-header">
    <div>
      <div class="page-title">⚙️ Gestion des Comptes</div>
      <div class="page-sub"><?= count($users) ?> compte(s) — Contrôle d'accès basé sur les rôles</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/admin/ajouter-compte.php" class="btn btn-primary">+ Ajouter un compte</a>
  </div>

  <?php if ($flash_ok):  ?><div class="alert alert-green">✓ <?= htmlspecialchars($flash_ok) ?></div><?php endif; ?>
  <?php if ($flash_err): ?><div class="alert alert-red">✕ <?= htmlspecialchars($flash_err) ?></div><?php endif; ?>

  <div class="stat-grid">
    <?php
    $roles = ['superadmin'=>0,'manager'=>0,'caissier'=>0];
    foreach ($users as $u) { if (isset($roles[$u['role']])) $roles[$u['role']]++; }
    ?>
    <div class="stat-card stat-accent"><div class="stat-val"><?= count($users) ?></div><div class="stat-label">Comptes total</div></div>
    <div class="stat-card"><div class="stat-val" style="color:var(--purple)"><?= $roles['superadmin'] ?></div><div class="stat-label">Super Admins</div></div>
    <div class="stat-card stat-amber"><div class="stat-val"><?= $roles['manager'] ?></div><div class="stat-label">Managers</div></div>
    <div class="stat-card stat-green"><div class="stat-val"><?= $roles['caissier'] ?></div><div class="stat-label">Caissiers</div></div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="title-group"><span style="font-size:18px">👥</span><div class="section-label">Liste des utilisateurs</div></div>
    </div>
    <table class="data-table">
      <thead>
        <tr><th>Identifiant</th><th>Nom complet</th><th>Rôle</th><th>Statut</th><th>Créé le</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td class="mono"><?= htmlspecialchars($u['identifiant']) ?></td>
          <td style="font-weight:600"><?= htmlspecialchars($u['nom_complet']) ?></td>
          <td><span class="badge badge-<?= $u['role'] === 'superadmin' ? 'superadmin' : $u['role'] ?>">
            <?= ucfirst($u['role'] === 'superadmin' ? 'Super Admin' : $u['role']) ?>
          </span></td>
          <td><span class="badge <?= $u['actif'] ? 'badge-green' : 'badge-red' ?>"><?= $u['actif'] ? 'Actif' : 'Inactif' ?></span></td>
          <td style="color:var(--text3);font-size:11px"><?= htmlspecialchars($u['date_creation']) ?></td>
          <td>
            <?php if ($u['identifiant'] !== $_SESSION['user']['identifiant']): ?>
              <a href="<?= BASE_URL ?>/modules/admin/supprimer-compte.php?id=<?= urlencode($u['identifiant']) ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Supprimer <?= htmlspecialchars($u['identifiant']) ?> ?')">🗑 Supprimer</a>
            <?php else: ?>
              <span style="font-size:11px;color:var(--text3)">Compte courant</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
