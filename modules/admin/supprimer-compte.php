<?php
include '../../includes/header.php';
require_once '../../includes/fonctions-auth.php';

// Vérification du rôle : seuls les Super Admin peuvent supprimer
if ($_SESSION['user']['role'] !== 'superadmin') {
    echo "<div class='error'>Accès refusé : vous n'avez pas les permissions nécessaires.</div>";
    include '../../includes/footer.php';
    exit;
}

// Vérification de l'identifiant transmis
if (!isset($_GET['id'])) {
    echo "<div class='error'>Identifiant manquant.</div>";
    include '../../includes/footer.php';
    exit;
}

$id = $_GET['id'];

// Protection : impossible de supprimer le Super Admin
if ($id === 'superadmin') {
    echo "<div class='error'>Impossible de supprimer le Super Administrateur.</div>";
    include '../../includes/footer.php';
    exit;
}

// Charger la liste des utilisateurs
$utilisateurs = charger_utilisateurs();
$nouvelle_liste = [];

// Filtrer la liste en excluant l'utilisateur à supprimer
foreach ($utilisateurs as $u) {
    if ($u['identifiant'] !== $id) {
        $nouvelle_liste[] = $u;
    }
}

// Sauvegarder la nouvelle liste
sauvegarder_utilisateurs($nouvelle_liste);

// Message de confirmation
echo "<div class='card' style='border-left:5px solid #1E9E63;'>
        <p>Compte supprimé avec succès.</p>
      </div>";

echo "<a href='gestion-comptes.php'><button>Retour</button></a>";

include '../../includes/footer.php';
?>
