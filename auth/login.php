<?php
session_start();
require_once '../config/config.php';
require_once '../includes/fonctions-auth.php';

$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    $user = verifier_connexion($identifiant, $mot_de_passe);

    if ($user) {
        $_SESSION['user'] = $user;
        header("Location: /facturation/index.php");
        exit;
    } else {
        $erreur = "Identifiant ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/facturation/assets/css/style.css">
    <link rel="stylesheet" href="/facturation/assets/css/login.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <h2>Connexion</h2>

        <?php if ($erreur): ?>
            <div class="error"><?= $erreur ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Identifiant</label>
            <input type="text" name="identifiant" required>

            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" required>

            <button type="submit">Se connecter</button>
        </form>
    </div>
</div>

</body>
</html>
