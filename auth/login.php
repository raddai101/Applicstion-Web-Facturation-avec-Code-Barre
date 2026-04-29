<?php
// ============================================================
// auth/login.php — Page de connexion
// ============================================================
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/fonctions-auth.php';

// Déjà connecté → rediriger
if (est_connecte()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error   = '';
$timeout = isset($_GET['timeout']);
$logout  = isset($_GET['logout']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant  = trim($_POST['identifiant']  ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');

    if (empty($identifiant) || empty($mot_de_passe)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $user = authentifier($identifiant, $mot_de_passe);
        if ($user) {
            $_SESSION['user'] = $user;
            $_SESSION['last_activity'] = time();
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        } else {
            $error = 'Identifiant ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — SuperMarché POS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="login-page">

<div class="login-wrapper">
  <div class="login-card">

    <div class="login-logo">
      <div class="logo-icon-lg">SM</div>
      <h1 class="login-title">SuperMarché POS</h1>
      <!-- <p class="login-sub">Système de Caisse — Connexion sécurisée</p> -->
    </div>

    <?php if ($timeout): ?>
      <div class="alert alert-amber">⏱ Session expirée. Veuillez vous reconnecter.</div>
    <?php endif; ?>
    <?php if ($logout): ?>
      <!-- <div class="alert alert-green">✓ Déconnexion réussie.</div> -->
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-red">✕ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="form-field">
        <label>Identifiant</label>
        <input type="text" name="identifiant" placeholder="admin / manager / caissier"
               value="<?= htmlspecialchars($_POST['identifiant'] ?? '') ?>" required autofocus>
      </div>
      <div class="form-field">
        <label>Mot de passe</label>
        <input type="password" name="mot_de_passe" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-login">Se connecter </button>
    </form>

    <!-- <div class="login-hint">
      <div class="hint-title">Comptes de démonstration</div>
      <div class="hint-row"><span class="hint-badge superadmin">admin</span><span>admin</span><span class="hint-pass">/ test</span></div>
      <div class="hint-row"><span class="hint-badge manager">manager</span><span>manager</span><span class="hint-pass">/ test</span></div>
      <div class="hint-row"><span class="hint-badge caissier">caissier</span><span>caissier</span><span class="hint-pass">/ test</span></div>
    </div> -->

  </div>
</div>

</body>
</html> 
