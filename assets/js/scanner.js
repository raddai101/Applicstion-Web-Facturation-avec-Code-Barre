/**
 * SCANNER.js — Système de scan avec Quagga + 3 boutons (Capturer, Manuel, Redémarrer)
 */

var SCANNER = (function () {

    var quaggaCharge = false;
    var liveActif = false;
    var detectionFaite = false;

    // ─────────────────────────────────────────────────────────
    // 1. STYLES
    // ─────────────────────────────────────────────────────────
    function injecterStyles() {
        if (document.getElementById('scanner-css')) return;
        var s = document.createElement('style');
        s.id = 'scanner-css';
        s.textContent = `
            #preview {
                display: block;
                width: 100%;
                border-radius: 8px;
                background: #000;
                border: 3px solid #3498DB;
                object-fit: cover;
            }
            #scanner-wrapper {
                position: relative;
                max-width: 500px;
                margin: 0 auto;
            }
            #scanner-viseur {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 240px;
                height: 120px;
                border: 2px solid rgba(52, 152, 219, 0.9);
                border-radius: 6px;
                pointer-events: none;
                box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4);
                z-index: 10;
            }
            #scanner-viseur::before,
            #scanner-viseur::after {
                content: "";
                position: absolute;
                width: 20px;
                height: 20px;
                border-color: #3498DB;
                border-style: solid;
            }
            #scanner-viseur::before {
                top: -2px;
                left: -2px;
                border-width: 3px 0 0 3px;
            }
            #scanner-viseur::after {
                bottom: -2px;
                right: -2px;
                border-width: 0 3px 3px 0;
            }
            #scanner-ligne {
                position: absolute;
                left: 0;
                width: 100%;
                height: 2px;
                background: linear-gradient(to right, transparent, #E74C3C, transparent);
                animation: scanLigne 1.8s ease-in-out infinite;
                top: 0;
            }
            @keyframes scanLigne {
                0% { top: 0%; opacity: 1; }
                50% { top: 100%; opacity: 1; }
                100% { top: 0%; opacity: 1; }
            }
            #scanner-controls {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 15px;
                justify-content: center;
            }
            .btn-scan {
                padding: 10px 16px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: 600;
                font-size: 14px;
                transition: opacity 0.2s;
            }
            .btn-scan:hover { opacity: 0.85; }
            .btn-photo { background: #2ECC71; color: white; }
            .btn-manual { background: #3498DB; color: white; }
            .btn-restart { background: #95A5A6; color: white; }
            #scanner-status {
                margin: 15px auto;
                padding: 12px 15px;
                border-radius: 6px;
                font-weight: 600;
                text-align: center;
                font-size: 14px;
                max-width: 500px;
                background: #E8F5E9;
                color: #1B5E20;
                border: 2px solid #27AE60;
            }
        `;
        document.head.appendChild(s);
    }

    // ─────────────────────────────────────────────────────────
    // 2. CHARGEMENT QUAGGA
    // ─────────────────────────────────────────────────────────
    function chargerQuagga() {
        return new Promise(function (resolve, reject) {
            if (typeof Quagga !== 'undefined') { resolve(); return; }
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js';
            script.async = true;
            var timeout = setTimeout(function () {
                reject(new Error('Timeout chargement Quagga'));
            }, 15000);
            script.onload = function () {
                clearTimeout(timeout);
                quaggaCharge = true;
                resolve();
            };
            script.onerror = function () {
                clearTimeout(timeout);
                reject(new Error('Erreur chargement Quagga'));
            };
            document.head.appendChild(script);
        });
    }

    // ─────────────────────────────────────────────────────────
    // 3. STATUT
    // ─────────────────────────────────────────────────────────
    function afficherStatut(msg) {
        var el = document.getElementById('scanner-status');
        if (el) el.textContent = msg;
    }

    // ─────────────────────────────────────────────────────────
    // 4. DÉMARRER LE SCANNER EN MODE CONTINU
    // ─────────────────────────────────────────────────────────
    function demarrerLive() {
        if (liveActif) return;
        var preview = document.getElementById('preview');
        if (!preview) {
            afficherStatut('❌ Élément vidéo introuvable');
            return;
        }

        afficherStatut('⏳ Initialisation caméra...');
        console.log('[scanner] Démarrage Quagga');

        Quagga.init({
            inputStream: {
                name: 'Live',
                type: 'LiveStream',
                target: preview,
                constraints: {
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            },
            locator: { patchSize: 'medium', halfSample: true },
            decoder: { 
                readers: ['ean_reader', 'ean_8_reader', 'code_128_reader', 'code_39_reader', 'upc_reader'] 
            },
            frequency: 5,
            multiple: false
        }, function (err) {
            if (err) {
                console.error('[scanner] Erreur init:', err);
                afficherStatut('❌ Erreur: ' + (err.message || err));
                return;
            }

            console.log('[scanner] Quagga initialisé');
            liveActif = true;

            try {
                Quagga.start();
                console.log('[scanner] Quagga démarré');
                afficherStatut('📹 Scan en cours - Placez le code-barres devant la caméra');

                Quagga.onDetected(function (result) {
                    if (result && result.codeResult && result.codeResult.code && !detectionFaite) {
                        var code = result.codeResult.code;
                        detectionFaite = true;
                        console.log('[scanner] Code détecté:', code);
                        afficherStatut('✅ Code trouvé: ' + code);
                        Quagga.stop();
                        setTimeout(function () {
                            window.location.href = window.location.pathname + '?code=' + encodeURIComponent(code);
                        }, 800);
                    }
                });
            } catch (e) {
                console.error('[scanner] Erreur start:', e);
                afficherStatut('❌ Erreur démarrage');
                liveActif = false;
            }
        });
    }

    // ─────────────────────────────────────────────────────────
    // 5. CONSTRUCTION UI
    // ─────────────────────────────────────────────────────────
    function construireUI() {
        var preview = document.getElementById('preview');
        if (!preview) return;

        // Wrapper
        var wrapper = document.createElement('div');
        wrapper.id = 'scanner-wrapper';
        preview.parentNode.insertBefore(wrapper, preview);
        wrapper.appendChild(preview);

        // Viseur + ligne
        var viseur = document.createElement('div');
        viseur.id = 'scanner-viseur';
        var ligne = document.createElement('div');
        ligne.id = 'scanner-ligne';
        viseur.appendChild(ligne);
        wrapper.appendChild(viseur);

        // Boutons
        var controls = document.createElement('div');
        controls.id = 'scanner-controls';

        var btnCapture = document.createElement('button');
        btnCapture.className = 'btn-scan btn-photo';
        btnCapture.textContent = '📸 Capturer';
        btnCapture.onclick = function () {
            afficherStatut('📸 Capture en cours...');
        };
        controls.appendChild(btnCapture);

        var btnManuel = document.createElement('button');
        btnManuel.className = 'btn-scan btn-manual';
        btnManuel.textContent = '⌨️ Saisie manuelle';
        btnManuel.onclick = function () {
            var code = prompt('Entrez le code-barres:');
            if (code && code.trim()) {
                window.location.href = window.location.pathname + '?code=' + encodeURIComponent(code.trim());
            }
        };
        controls.appendChild(btnManuel);

        var btnRestart = document.createElement('button');
        btnRestart.className = 'btn-scan btn-restart';
        btnRestart.textContent = '🔄 Redémarrer';
        btnRestart.onclick = function () {
            detectionFaite = false;
            location.reload();
        };
        controls.appendChild(btnRestart);

        wrapper.parentNode.insertBefore(controls, wrapper.nextSibling);

        // Statut
        var status = document.createElement('div');
        status.id = 'scanner-status';
        status.textContent = '⏳ Chargement...';
        controls.parentNode.insertBefore(status, controls.nextSibling);
    }

    // ─────────────────────────────────────────────────────────
    // 6. INITIALISATION
    // ─────────────────────────────────────────────────────────
    function init() {
        var preview = document.getElementById('preview');
        if (!preview) {
            console.error('[scanner] #preview introuvable');
            return;
        }

        injecterStyles();
        construireUI();
        afficherStatut('⏳ Chargement Quagga...');

        chargerQuagga()
            .then(function () {
                console.log('[scanner] Quagga chargé ✓');
                demarrerLive();
            })
            .catch(function (err) {
                console.error('[scanner] Erreur:', err.message);
                afficherStatut('❌ ' + err.message + ' - Utilisez la saisie manuelle');
            });
    }

    window.addEventListener('beforeunload', function () {
        if (typeof Quagga !== 'undefined' && liveActif) {
            try { Quagga.stop(); } catch (e) {}
        }
    });

    return { init: init };
})();

document.addEventListener('DOMContentLoaded', function () {
    SCANNER.init();
});

