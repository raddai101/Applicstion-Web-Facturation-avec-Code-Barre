/**
 * assets/js/scanner.js  —  Point d'entrée principal
 * ─────────────────────────────────────────────────
 * Orchestre les modules :
 *   scanner/ui.js       → interface utilisateur (styles, statut, viseur)
 *   scanner/camera.js   → flux vidéo et capture photo
 *   scanner/decodeur.js → chargement Quagga + décodage (photo ou live)
 *   scanner/audio.js    → bip de confirmation
 *   scanner/actions.js  → redirection GET et saisie manuelle
 *
 * MODE PAR DÉFAUT : photo
 *   L'utilisateur voit le flux vidéo en temps réel.
 *   Il appuie sur « Prendre une photo » → l'image est analysée par Quagga.
 *   Résultat instantané (< 1 s) sans consommer du CPU en continu.
 *
 * MODE FALLBACK (si la photo échoue 2×) : live continu
 */

import {
    injecterStyles,
    construireUI,
    afficherStatut,
    afficherAperçuPhoto,
    masquerAperçuPhoto,
    toggleLigneScan
} from './scanner/ui.js';

import {
    demarrerCamera,
    arreterCamera,
    capturerPhoto,
    cameraActive
} from './scanner/camera.js';

import {
    chargerQuagga,
    decoderDepuisImage,
    demarrerLive,
    arreterLive
} from './scanner/decodeur.js';

import { jouerBip }                     from './scanner/audio.js';
import { traiterCodeBarre, saisieManuelle } from './scanner/actions.js';

// ─── État interne ─────────────────────────────────────────────────────────────
let echecPhotos  = 0;   // compteur d'échecs en mode photo
let modeActuel   = 'photo'; // 'photo' | 'live'
let detectionFaite = false; // évite les doubles détections

// ─── Initialisation au chargement du DOM ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    const preview = document.getElementById('preview');
    if (!preview) return; // la page ne contient pas de scanner

    injecterStyles();

    // Construction de l'interface (viseur, boutons, zones statut)
    construireUI(
        onBtnPhoto,      // bouton "Prendre une photo"
        onBtnManuel,     // bouton "Saisie manuelle"
        onBtnRedemarrer  // bouton "Redémarrer"
    );

    afficherStatut('⏳ Chargement de Quagga…', 'chargement');

    try {
        await chargerQuagga();
        afficherStatut('📹 Démarrage de la caméra…', 'chargement');
        await demarrerCamera();
        toggleLigneScan(true);
        afficherStatut('✅ Caméra active — Appuyez sur 📸 pour scanner', 'actif');
    } catch (err) {
        console.error('[scanner] Erreur init:', err);
        afficherStatut('❌ ' + err.message + ' — Utilisez la saisie manuelle.', 'erreur');
    }
});

// ─── Gestionnaires de boutons ─────────────────────────────────────────────────

async function onBtnPhoto() {
    if (detectionFaite) return;
    if (!cameraActive()) {
        afficherStatut('⚠️ Caméra non active. Redémarrez le scanner.', 'erreur');
        return;
    }

    afficherStatut('📸 Capture en cours…', 'chargement');
    masquerAperçuPhoto();

    try {
        const { dataUrl, canvas } = await capturerPhoto();
        afficherAperçuPhoto(dataUrl);
        afficherStatut('🔍 Analyse de la photo…', 'chargement');

        const { code, format } = await decoderDepuisImage(canvas);
        onCodeDetecte(code, format);
        echecPhotos = 0;

    } catch (err) {
        echecPhotos++;
        console.warn('[scanner] Échec photo #' + echecPhotos + ':', err.message);

        if (echecPhotos >= 2 && modeActuel !== 'live') {
            afficherStatut('📷 Passage en mode détection continue…', 'info');
            basculerLive();
        } else {
            afficherStatut(
                '⚠️ Aucun code détecté. Recadrez et réessayez. (' + echecPhotos + '/2)',
                'erreur',
                4000
            );
        }
    }
}

function onBtnManuel() {
    saisieManuelle(code => {
        afficherStatut('⌨️ Code manuel : ' + code, 'détecté');
        jouerBip();
        detectionFaite = true;
        arreterScanner();
        traiterCodeBarre(code);
    });
}

async function onBtnRedemarrer() {
    detectionFaite = false;
    echecPhotos    = 0;
    modeActuel     = 'photo';
    masquerAperçuPhoto();
    arreterLive();

    try {
        await demarrerCamera();
        toggleLigneScan(true);
        afficherStatut('🔄 Scanner redémarré — Appuyez sur 📸 pour scanner', 'actif');
    } catch (err) {
        afficherStatut('❌ ' + err.message, 'erreur');
    }
}

// ─── Bascule vers le mode live ────────────────────────────────────────────────

function basculerLive() {
    modeActuel = 'live';
    arreterCamera(); // libère le flux manuel ; Quagga gérera sa propre caméra

    const preview = document.getElementById('preview');
    demarrerLive(
        preview,
        ({ code, format }) => onCodeDetecte(code, format),
        err => afficherStatut('❌ Mode live : ' + err.message, 'erreur')
    );

    afficherStatut('📹 Détection continue active — placez le code devant la caméra', 'actif');
}

// ─── Callback commun : code détecté ──────────────────────────────────────────

function onCodeDetecte(code, format) {
    if (detectionFaite) return;
    detectionFaite = true;

    console.log('[scanner] ✅ Code détecté :', code, '| Format :', format);
    afficherStatut('✅ Code détecté : ' + code, 'détecté');
    jouerBip();
    arreterScanner();
    traiterCodeBarre(code);
}

// ─── Arrêt propre ─────────────────────────────────────────────────────────────

function arreterScanner() {
    toggleLigneScan(false);
    arreterLive();
    arreterCamera();
}

// Nettoyage avant fermeture de la page
window.addEventListener('beforeunload', arreterScanner);

// ─── Fonctions globales (appelées depuis PHP/HTML) ────────────────────────────
// Nécessaire pour les boutons inline onclick="..."

window.manualScanInput = onBtnManuel;

window.testCode = function(code) {
    afficherStatut('🧪 Code test : ' + code, 'détecté');
    jouerBip();
    traiterCodeBarre(code);
};
