# Dépendances locales

## ZXing

Ce dossier contient les dépendances locales pour éviter de charger depuis le CDN.

### Installation de ZXing

Téléchargez la dernière version minifiée de ZXing:

```bash
cd /var/www/html/Facturation-System/assets/lib

# Télécharger ZXing depuis unpkg
curl -o zxing.min.js https://unpkg.com/@zxing/browser@0.1.5/umd/index.min.js
```

Ou avec wget:

```bash
wget -O zxing.min.js https://unpkg.com/@zxing/browser@0.1.5/umd/index.min.js
```

Le fichier `zxing.min.js` sera utilisé automatiquement par `scanner.js`.

**Fallback**: Si le fichier local n'existe pas, le scanner reviendra automatiquement au CDN.
