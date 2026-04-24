# Guide d'utilisation du Scanner

## Comment scanner un code-barres

### Méthode automatique (recommandée)
1. Cliquez sur "Nouvelle Facture" ou "Enregistrer un Produit"
2. La caméra s'active automatiquement
3. Placez le code-barres devant la caméra
4. Le système détecte automatiquement le code et affiche le produit

### Méthode manuelle (si la caméra ne fonctionne pas)
1. Cliquez sur le bouton "Saisie manuelle"
2. Entrez le code-barres dans la boîte de dialogue
3. Appuyez sur OK

## Codes de test disponibles

Voici quelques codes-barres que vous pouvez tester :

| Code-barres | Produit | Prix HT | Stock |
|-------------|---------|---------|-------|
| 3017620422003 | Vain amour 1L | 1200 CDF | 50 |
| 1234567890123 | Pain complet 500g | 800 CDF | 25 |
| 9876543210987 | Lait frais 1L | 1500 CDF | 30 |
| 5556667778889 | Riz 2kg | 3500 CDF | 15 |

## Dépannage

### La caméra ne s'active pas
- Vérifiez que votre navigateur supporte l'accès à la caméra
- Accordez la permission d'accès à la caméra quand demandé
- Utilisez la saisie manuelle comme alternative

### Le code n'est pas reconnu
- Vérifiez que le code-barres est bien visible
- Assurez-vous que l'éclairage est suffisant
- Utilisez la saisie manuelle

### Erreur "Produit inconnu"
- Le produit n'est pas enregistré dans le système
- Demandez au Manager d'enregistrer le produit d'abord

## Fonctionnalités du scanner

- **Détection automatique** : Utilise QuaggaJS pour lire les codes-barres en temps réel
- **Formats supportés** : EAN-13, EAN-8, Code 128, Code 39, UPC, etc.
- **Saisie manuelle** : Alternative quand la caméra n'est pas disponible
- **Feedback visuel** : Confirmation quand un code est détecté</content>
<parameter name="filePath">c:\xampp\htdocs\facturation\SCANNER_README.md