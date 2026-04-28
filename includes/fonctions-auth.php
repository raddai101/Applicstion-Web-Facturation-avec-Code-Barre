<?php
// ============================================================
// includes/fonctions-auth.php — Fonctions d'authentification
// ============================================================

function charger_utilisateurs() {
    $data = file_get_contents(USERS_FILE);
    return json_decode($data, true) ?? [];
}

function sauvegarder_utilisateurs(array $users) {
    file_put_contents(USERS_FILE, json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function authentifier(string $identifiant, string $mot_de_passe): ?array {
    $users = charger_utilisateurs();
    foreach ($users as $u) {
        if ($u['identifiant'] === $identifiant && $u['actif'] === true) {
            if (password_verify($mot_de_passe, $u['mot_de_passe'])) {
                return $u;
            }
        }
    }
    return null;
}

function est_connecte(): bool {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['identifiant']);
}

function utilisateur_courant(): ?array {
    return $_SESSION['user'] ?? null;
}

function verifier_role(array $roles_autorises): void {
    if (!est_connecte()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, $roles_autorises, true)) {
        $_SESSION['flash_error'] = "Accès non autorisé pour votre rôle.";
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function a_role(string $role): bool {
    $user = utilisateur_courant();
    if (!$user) return false;
    $hierarchy = ['caissier' => 1, 'manager' => 2, 'superadmin' => 3];
    $user_level  = $hierarchy[$user['role']]  ?? 0;
    $role_level  = $hierarchy[$role]          ?? 0;
    return $user_level >= $role_level;
}

function generer_id_facture(): string {
    $factures = charger_factures_data();
    $date     = date('Ymd');
    $num      = count($factures) + 1;
    return 'FAC-' . $date . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
}

function charger_factures_data(): array {
    $data = file_get_contents(FACTURES_FILE);
    return json_decode($data, true) ?? [];
}

function flash(string $key): ?string {
    $val = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $val;
}

