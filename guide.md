facturation/
│ index.php
│
├── config/
│   └── config.php
│
├── auth/
│   ├── login.php
│   ├── logout.php
│   └── session.php
│
├── modules/
│   ├── produits/
│   │   ├── enregistrer.php
│   │   ├── lire.php
│   │   └── liste.php
│   │
│   ├── facturation/
│   │   ├── nouvelle-facture.php
│   │   ├── calcul.php
│   │   └── afficher-facture.php
│   │
│   └── admin/
│       ├── gestion-comptes.php
│       ├── ajouter-compte.php
│       └── supprimer-compte.php
│
├── data/
│   ├── produits.json
│   ├── factures.json
│   └── utilisateurs.json
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── fonctions-produits.php
│   ├── fonctions-factures.php
│   └── fonctions-auth.php
│
├── assets/
│   ├── css/style.css
│   └── js/scanner.js
│
└── rapports/
    ├── rapport-journalier.php
    └── rapport-mensuel.php
