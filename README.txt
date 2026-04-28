============================================================
  SuperMarché POS — Système de Facturation avec Codes-Barres
  Université Protestante au Congo — TP PHP Procédural 2025-2026
============================================================

DÉPLOIEMENT LOCAL
-----------------
1. Copier le dossier facturation/ dans votre répertoire web :
   - XAMPP/WAMP : htdocs/facturation/
   - Linux Apache : /var/www/html/facturation/

2. Configurer BASE_URL dans config/config.php si nécessaire
   (défaut : /facturation)

3. Donner les droits d'écriture au dossier data/ :
   chmod 775 data/
   chmod 664 data/*.json

4. Accéder via : http://localhost/facturation/

COMPTES PAR DÉFAUT
------------------
  admin    / test   → Super Administrateur
  manager  / test   → Manager
  caissier / test   → Caissier

ARBORESCENCE
------------
facturation/
├── index.php              ← Caisse principale
├── config/config.php      ← Paramètres globaux
├── auth/                  ← Login / Logout / Session
├── modules/
│   ├── produits/          ← Enregistrement & catalogue
│   ├── facturation/       ← Nouvelle facture & affichage
│   └── admin/             ← Gestion comptes
├── data/                  ← Fichiers JSON (persistence)
├── includes/              ← Fonctions PHP réutilisables
├── assets/
│   ├── css/style.css      ← Design complet
│   └── js/scanner.js      ← Scanner ZXing
└── rapports/              ← Rapports journalier & mensuel

SCANNER ZXing
-------------
- Formats : EAN-13, EAN-8, CODE-128, QR, UPC-A, ITF, PDF-417, DATA-MATRIX
- Nécessite HTTPS ou localhost pour accéder à la caméra
- Mode manuel disponible en fallback
============================================================
