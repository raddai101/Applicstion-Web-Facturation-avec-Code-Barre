<?php require_once  'auth/session.php'; $title='accueil'; include 'includes/header.php'; ?>
<?php require_once 'config/config.php';?>
<link rel="stylesheet" href="/facturation/assets/css/index.css">

<div class="page-index">

    <div class="card welcome-card">
        <h2>Bienvenue, <?= $_SESSION['user']['nom_complet']; ?></h2>
        <p class="role">Rôle : <strong><?= ucfirst($_SESSION['user']['role']); ?></strong></p>
        <p>Choisissez une action ci-dessous pour commencer.</p>
    </div>

    <div class="dashboard">

        <!-- Bloc Facturation (visible pour tous) -->
        <a href=<?php echo BASE_URL."/modules".BASE_URL."/nouvelle-facture.php"?> class="dash-item facturation">
            <h3>Nouvelle Facture</h3>
            <p>Scanner des articles et générer une facture.</p>
        </a>

        <!-- Bloc Produits (Manager + Super Admin) -->
        <?php if ($_SESSION['user']['role'] !== 'caissier'): ?>
            <a href="/facturation/modules/produits/liste.php" class="dash-item produits">
                <h3>Produits</h3>
                <p>Gérer le stock et enregistrer de nouveaux produits.</p>
            </a>
        <?php endif; ?>

        <!-- Bloc Comptes (Super Admin uniquement) -->
        <?php if ($_SESSION['user']['role'] === 'superadmin'): ?>
            <a href="/facturation/modules/admin/gestion-comptes.php" class="dash-item admin">
                <h3>Comptes Utilisateurs</h3>
                <p>Créer, modifier ou supprimer des comptes.</p>
            </a>
        <?php endif; ?>

        <!-- Bloc Rapports (Manager + Super Admin) -->
        <?php if ($_SESSION['user']['role'] !== 'caissier'): ?>
            <a href="/facturation/rapports/rapport-journalier.php" class="dash-item rapports">
                <h3>Rapports</h3>
                <p>Consulter les ventes journalières et mensuelles.</p>
            </a>
        <?php endif; ?>

    </div>

</div>

<?php include 'includes/footer.php'; ?>
