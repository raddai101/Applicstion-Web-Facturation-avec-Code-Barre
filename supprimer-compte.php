<?php
include '../../includes/header.php';
require_once '../../includes/fonctions-auth.php';

// Vérification du rôle
if ($_SESSION['user']['role'] !== 'superadmin') {
    echo "<div class='error'>Accès refusé.</div>";
    include '../../includes/footer.php';
    exit;
}

if (!isset($_GET['id'])) {
    echo "<div class='error'>Identifiant manquant.</div>";
    include '../../includes/footer.php';
    exit;
}

$id = $_GET['id'];

// Impossible de supprimer le superadmin
if ($id === 'superadmin') {
    echo "<div class='error'>Impossible de supprimer le Super Administrateur.</div>";
    include '../../includes/footer.php';
    exit;
}

$utilisateurs = charger_utilisateurs();
$nouvelle_liste = [];

foreach ($utilisateurs as $u) {
    if ($u['identifiant'] !== $id) {
        $nouvelle_liste[] = $u;
    }
}

sauvegarder_utilisateurs($nouvelle_liste);

echo "<div class='card' style='border-left:5px solid #1E9E63;'>
        <p>Compte supprimé avec succès.</p>
      </div>";

echo "<a href='gestion-comptes.php'><button>Retour</button></a>";

include '../../includes/footer.php';
?>
