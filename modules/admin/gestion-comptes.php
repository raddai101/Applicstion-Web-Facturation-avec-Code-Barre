<?php
require_once '../../auth/session.php';
$title = 'Gestion-Comptes';
include '../../includes/header.php';
require_once '../../includes/fonctions-auth.php';

// Vérification du rôle Super Admin
if ($_SESSION['user']['role'] !== 'superadmin') {
    echo "<div class='error'>Accès refusé : vous n'avez pas les permissions nécessaires.</div>";
    include '../../includes/footer.php';
    exit;
}

$utilisateurs = charger_utilisateurs();
?>

<div class="page-admin">

    <div class="card">
        <h2>Gestion des Comptes Utilisateurs</h2>

        <a href="/facturation/modules/admin/ajouter-comptes.php">
            <button>Ajouter un compte</button>
        </a>
    </div>

    <?php if (empty($utilisateurs)): ?>
        <div class="card">
            <p>Aucun utilisateur enregistré.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($utilisateurs as $u): ?>
        <div class="user-card">
            <p><strong>Identifiant :</strong> <?= htmlspecialchars($u['identifiant']) ?></p>
            <p><strong>Nom complet :</strong> <?= htmlspecialchars($u['nom_complet']) ?></p>
            <p class="role"><strong>Rôle :</strong> <?= ucfirst($u['role']) ?></p>
            <p><strong>Actif :</strong> <?= $u['actif'] ? "Oui" : "Non" ?></p>
            <p><strong>Date de création :</strong> <?= $u['date_creation'] ?></p>

            <?php if ($u['role'] !== 'superadmin'): ?>
                <a href="/facturation/modules/admin/supprimer-compte.php?id=<?= urlencode($u['identifiant']) ?>">
                    <button style="background:#E74C3C;">Supprimer</button>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>

<?php include '../../includes/footer.php'; ?>
