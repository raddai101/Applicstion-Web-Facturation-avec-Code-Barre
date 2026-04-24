<?php
require_once __DIR__ . '/../auth/session.php';
$title = 'Gestion-Comptes';
include '../../includes/header.php';
require_once '../../includes/fonctions-auth.php';

// Vérification du rôle
if ($_SESSION['user']['role'] !== 'superadmin') {
    echo "<div class='error'>Accès refusé : vous n'avez pas les permissions nécessaires.</div>";
    include '../../includes/footer.php';
    exit;
}

$erreurs = [];
$succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identifiant = trim($_POST['identifiant']);
    $nom_complet = trim($_POST['nom_complet']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $role = trim($_POST['role']);

    // Validations
    if ($identifiant === "" || $nom_complet === "" || $mot_de_passe === "") {
        $erreurs[] = "Tous les champs sont obligatoires.";
    }

    if (!in_array($role, ['caissier', 'manager', 'superadmin'])) {
        $erreurs[] = "Rôle invalide.";
    }

    // Vérifier si identifiant existe déjà
    if (trouver_utilisateur($identifiant)) {
        $erreurs[] = "Cet identifiant existe déjà.";
    }

    if (empty($erreurs)) {
        $utilisateurs = charger_utilisateurs();

        $nouveau = [
            "identifiant" => $identifiant,
            "mot_de_passe" => password_hash($mot_de_passe, PASSWORD_DEFAULT),
            "role" => $role,
            "nom_complet" => $nom_complet,
            "date_creation" => date(DATE_FORMAT),
            "actif" => true
        ];

        $utilisateurs[] = $nouveau;
        sauvegarder_utilisateurs($utilisateurs);

        $succes = "Compte créé avec succès.";
    }
}
?>

<div class="page-admin">

<div class="card">
    <h2>Ajouter un Compte</h2>

    <?php if (!empty($erreurs)): ?>
        <div class="error">
            <?php foreach ($erreurs as $e) echo "<p>$e</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($succes): ?>
        <div class="card" style="border-left:5px solid #1E9E63;">
            <p><?= $succes ?></p>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Identifiant</label>
        <input type="text" name="identifiant" required>

        <label>Nom complet</label>
        <input type="text" name="nom_complet" required>

        <label>Mot de passe</label>
        <input type="password" name="mot_de_passe" required>

        <label>Rôle</label>
        <select name="role">
            <?php foreach($ROLES_AUTORISES as $rl) :?>
                <?="<option value=caissier>$rl</option>"; ?>
            <?php endforeach; ?>
        </select>

        <button type="submit">Créer le compte</button>
    </form>

</div>

</div>

<?php include '../../includes/footer.php'; ?>
