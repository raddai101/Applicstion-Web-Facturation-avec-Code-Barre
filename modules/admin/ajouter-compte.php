<?php
// ============================================================
// modules/admin/ajouter-compte.php
// ============================================================
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/fonctions-auth.php';
require_once dirname(__DIR__, 2) . '/auth/session.php';
verifier_role(['superadmin']);

$page_title = 'Ajouter un compte';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = trim($_POST['identifiant']  ?? '');
    $nom  = trim($_POST['nom_complet']  ?? '');
    $mdp  = trim($_POST['mot_de_passe'] ?? '');
    $role = trim($_POST['role']         ?? '');

    if (empty($id))   $errors[] = 'Identifiant requis.';
    if (empty($nom))  $errors[] = 'Nom complet requis.';
    if (strlen($mdp) < 4) $errors[] = 'Mot de passe trop court (min 4 caractères).';
    if (!in_array($role, ['caissier','manager','superadmin'])) $errors[] = 'Rôle invalide.';

    if (empty($errors)) {
        $users = charger_utilisateurs();
        foreach ($users as $u) {
            if ($u['identifiant'] === $id) { $errors[] = 'Identifiant déjà utilisé.'; break; }
        }
    }
    if (empty($errors)) {
        $users = charger_utilisateurs();
        $users[] = [
            'identifiant'  => $id,
            'mot_de_passe' => password_hash($mdp, PASSWORD_DEFAULT),
            'role'         => $role,
            'nom_complet'  => $nom,
            'date_creation'=> date('Y-m-d'),
            'actif'        => true
        ];
        sauvegarder_utilisateurs($users);
        $_SESSION['flash_ok'] = "Compte '$id' créé avec succès.";
        header('Location: ' . BASE_URL . '/modules/admin/gestion-comptes.php');
        exit;
    }
}
require_once dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="full-page">
  <div class="page-header">
    <div>
      <div class="page-title">➕ Ajouter un compte</div>
      <div class="page-sub">Créer un nouvel utilisateur</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/admin/gestion-comptes.php" class="btn btn-secondary">Retour</a>
  </div>
  <?php if ($errors): ?>
    <div class="alert alert-red"><ul style="margin:0 0 0 16px"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <div class="card" style="max-width:500px">
    <div class="card-header"><div class="title-group"><span style="font-size:18px">👤</span><div class="section-label">Informations du compte</div></div></div>
    <div class="card-body">
      <form method="POST">
        <div class="form-grid">
          <div class="form-field full"><label>Identifiant *</label><input type="text" name="identifiant" value="<?= htmlspecialchars($_POST['identifiant'] ?? '') ?>" placeholder="jean.dupont" required></div>
          <div class="form-field full"><label>Nom complet *</label><input type="text" name="nom_complet" value="<?= htmlspecialchars($_POST['nom_complet'] ?? '') ?>" placeholder="Jean Dupont" required></div>
          <div class="form-field full"><label>Mot de passe *</label><input type="password" name="mot_de_passe" placeholder="Minimum 4 caractères" required></div>
          <div class="form-field full">
            <label>Rôle *</label>
            <select name="role">
              <option value="caissier"   <?= ($_POST['role']??'') === 'caissier'    ? 'selected' : '' ?>>Caissier</option>
              <option value="manager"    <?= ($_POST['role']??'') === 'manager'     ? 'selected' : '' ?>>Manager</option>
              <option value="superadmin" <?= ($_POST['role']??'') === 'superadmin'  ? 'selected' : '' ?>>Super Administrateur</option>
            </select>
          </div>
          <div class="form-field full"><button type="submit" class="btn btn-primary btn-w100">💾 Créer le compte</button></div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
