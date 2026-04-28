(function () {
    'use strict';

    let codeReader = null;
    let currentStream = null;

    function loadZXing(callback) {
        if (window.ZXing) {
            console.log("✓ ZXing déjà chargé en mémoire");
            return callback();
        }

        console.log("⏳ Chargement de ZXing depuis CDN...");
        const s = document.createElement('script');
        s.src = 'https://unpkg.com/@zxing/browser@0.1.5/umd/index.min.js';
                // Stop any existing media on the video element to avoid "already playing" warnings
                try {
                    if (videoEl && videoEl.srcObject) {
                        const tracks = videoEl.srcObject.getTracks();
                        tracks.forEach(t => { try{ t.stop(); }catch(e){} });
                        videoEl.srcObject = null;
                    }
                    if (videoEl && !videoEl.paused) videoEl.pause();
                } catch(e) {}

                codeReader = new ZXing.BrowserMultiFormatReader();
        const timeout = setTimeout(() => {
            console.error("❌ Timeout chargement ZXing (5s) - CDN trop lent");
            callback(new Error("ZXing CDN timeout"));
        }, 5000);

        s.onload = function() {
            clearTimeout(timeout);
            console.log("✓ ZXing chargé avec succès du CDN");
            callback();
        };

        s.onerror = function() {
            clearTimeout(timeout);
            console.error("❌ Erreur chargement ZXing du CDN - Tentative fichier local...");
            
            const s2 = document.createElement('script');
            // Déterminer dynamiquement la base pour charger le fichier local

                            if (vlabelEl) vlabelEl.textContent = '✓ Code détecté!';

            let localBase = '/facturation';
            try {
                const cur = (document.currentScript && document.currentScript.src)
                    ? document.currentScript.src
                    : (function(){
                        const ss = document.getElementsByTagName('script');
                        return ss.length ? ss[ss.length-1].src : '';
                    })();
                if (cur) {
                    localBase = cur.replace(/\/assets\/js\/[^\/]*$/,'').replace(/\/$/, '');
                } else if (window.BASE_URL) {
                    localBase = window.BASE_URL.replace(/\/$/, '');
                }
            } catch(e) {}

            s2.src = localBase + '/assets/lib/zxing.min.js';

            s2.onload = function() {
                console.log("✓ ZXing chargé depuis fichier local (fallback)");
                callback();
            };

            s2.onerror = function() {
                // MISE À JOUR UI — scanner démarré
                try {
                    if (scanBadge) { scanBadge.textContent = 'ACTIF'; scanBadge.className = 'badge badge-green'; }
                    if (btnStart) btnStart.style.display = 'none';
                    if (btnStop) btnStop.style.display = 'flex';
                    if (slineEl) slineEl.classList.add('on');
                    if (vlabelEl) vlabelEl.textContent = 'Recherche en cours...';
                    // lister les caméras pour afficher le bouton switch si plusieurs
                    if (camChip && typeof ZXing.BrowserCodeReader !== 'undefined' && ZXing.BrowserCodeReader.listVideoInputDevices) {
                        ZXing.BrowserCodeReader.listVideoInputDevices().then(function(devs){
                            try{
                                camChip.textContent = (devs.length||0) + ' cam';
                                if (devs.length>1 && btnSwitch) btnSwitch.style.display = 'flex';
                            }catch(e){}
                        }).catch(function(){});
                    }
                } catch(e) {}
                console.error("❌ Erreur chargement ZXing (CDN + local échoués)");
                callback(new Error("Impossible de charger ZXing"));
            };

            document.head.appendChild(s2);
        };

        document.head.appendChild(s);
    }

    window.initScanner = function (videoId, resultId, onDetected) {
        // ✅ GARDE: Empêcher double initialisation
        if (codeReader !== null) {
            console.warn("⚠️ Scanner déjà actif - Appel ignoré");
            return;
        }

        loadZXing(function(err) {
            if (err) {
                console.error("❌ ZXing non disponible:", err.message);
                const resultEl = document.getElementById(resultId);
                if (resultEl) {
                    resultEl.textContent = "❌ Erreur: Impossible de charger le scanner";
                    resultEl.style.color = "var(--red)";
                }
                return;
            }

            const videoEl  = document.getElementById(videoId);
            const resultEl = document.getElementById(resultId);

            // Éléments UI auxiliaires (peuvent ne pas exister selon la page)
            const vlabelEl = document.getElementById('vlabel');
            const slineEl  = document.getElementById('sline');
            const scanBadge = document.getElementById('scan-badge');
            const btnStart = document.getElementById('btn-start');
            const btnStop  = document.getElementById('btn-stop');
            const btnSwitch = document.getElementById('btn-switch');
            const fpsChip  = document.getElementById('fps-chip');
            const cntChip  = document.getElementById('cnt-chip');
            const camChip  = document.getElementById('cam-chip');

            if (!videoEl) {
                console.error("❌ Élément vidéo non trouvé (ID: " + videoId + ")");
                if (resultEl) {
                    resultEl.textContent = "❌ Erreur: Élément vidéo manquant";
                    resultEl.style.color = "var(--red)";
                }
                return;
            }

            console.log("✓ Élément vidéo trouvé, accès caméra...");

            if (resultEl) {
                resultEl.textContent = "🔄 Accès à la caméra...";
                resultEl.style.color = "var(--teal)";
            }

            // Vérifier permissions caméra
            if (navigator.permissions) {
                navigator.permissions.query({name: 'camera'}).then(result => {
                    console.log("📷 État permission caméra:", result.state);
                    if (result.state === 'denied') {
                        console.error("❌ Permission caméra refusée par l'utilisateur");
                        if (resultEl) {
                            resultEl.textContent = "Permission caméra refusée. Autorisez dans les paramètres du navigateur.";
                            resultEl.style.color = "var(--red)";
                        }
                    }
                });
            }

            // stop ancien scanner si actif
            if (codeReader) {
                try {
                    codeReader.reset();
                    console.log("✓ Ancien scanner arrêté");
                } catch(e) {
                    console.warn("Erreur lors de l'arrêt du scanner précédent:", e.message);
                }
                codeReader = null;
            }

            try {
                if (!window.ZXing) {
                    throw new Error("ZXing library not available");
                }

                console.log("✓ ZXing disponible - Création BrowserMultiFormatReader...");
                codeReader = new ZXing.BrowserMultiFormatReader();
                console.log("✓ BrowserMultiFormatReader créé");

                // Afficher le flux vidéo
                console.log("Initialisation décodage vidéo...");
                const decodePromise = codeReader.decodeFromVideoDevice(
                    null,
                    videoEl,
                    (result, err) => {

                        if (result) {
                            const code = result.getText();
                            console.log("✓✓✓ CODE DÉTECTÉ:", code);

                            if (resultEl) {
                                resultEl.textContent = "✓ Code détecté : " + code;
                                resultEl.style.color = "var(--green)";
                            }

                            if (navigator.vibrate) {
                                navigator.vibrate(120);
                                console.log("📳 Vibration activée");
                            }

                            // STOP propre
                            try {
                                codeReader.reset();
                                console.log("✓ Scanner arrêté proprement");
                            } catch(e) {
                                console.warn("⚠️ Erreur lors du reset:", e.message);
                            }
                            codeReader = null;

                            if (typeof onDetected === 'function') {
                                console.log("→ Appel callback avec code:", code);
                                onDetected(code);
                            }
                        } else if (err && !(err instanceof ZXing.NotFoundException)) {
                            console.warn("⚠️ Erreur scanner (non-NotFound):", err.message);
                        }
                    }
                );
                
                // Gestion de la promesse
                if (decodePromise && typeof decodePromise.catch === 'function') {
                    decodePromise.catch(function(err) {
                        console.error("❌ ERREUR ACCÈS CAMÉRA:", err.name, "-", err.message);
                        
                        if (resultEl) {
                            let message = "❌ Erreur caméra";
                            
                            if (err.name === 'NotAllowedError') {
                                message = "❌ Permission caméra refusée. Autorisez dans les paramètres du navigateur.";
                                console.error("   → Cause: Utilisateur a refusé l'accès caméra");
                            } else if (err.name === 'NotFoundError') {
                                message = "❌ Aucune caméra détectée. Vérifiez votre matériel.";
                                console.error("   → Cause: Pas de caméra connectée/reconnue");
                            } else if (err.name === 'NotReadableError') {
                                message = "❌ Caméra bloquée ou en utilisation. Fermez les autres applications.";
                                console.error("   → Cause: Caméra inaccessible (bloquée/en utilisation)");
                            } else if (err.name === 'SecurityError') {
                                message = "❌ Erreur sécurité. Assurez-vous d'utiliser HTTPS ou localhost.";
                                console.error("   → Cause: Protocole non sécurisé (HTTP)");
                            } else {
                                message = "❌ Erreur: " + err.message;
                                console.error("   → Cause inconnue:", err);
                            }
                            
                            resultEl.textContent = message;
                            resultEl.style.color = "var(--red)";
                        }
                        
                        // Cleanup
                        try {
                            if (codeReader) {
                                codeReader.reset();
                                codeReader = null;
                            }
                        } catch(e) {}
                    });
                }
                
                console.log("✓✓✓ SCANNER INITIALISÉ AVEC SUCCÈS");
            } catch (e) {
                console.error("❌ Exception lors de l'initialisation:", e.message);
                console.error("Stack trace:", e.stack);
                
                if (resultEl) {
                    resultEl.textContent = "❌ Erreur: " + e.message;
                    resultEl.style.color = "var(--red)";
                }
            }
        });
    };

    window.stopScanner = function () {
        console.log("⏹ Arrêt du scanner...");
        if (codeReader) {
            try {
                // Arrêter le flux vidéo complètement
                const videoEl = document.getElementById('preview');
                if (videoEl && videoEl.srcObject) {
                    const tracks = videoEl.srcObject.getTracks();
                    tracks.forEach(track => {
                        try { track.stop(); } catch(e) {}
                    });
                    videoEl.srcObject = null;
                }
                if (videoEl && !videoEl.paused) {
                    videoEl.pause();
                }
                
                // Reset ZXing
                codeReader.reset();
                console.log("✓ Scanner arrêté proprement");
            } catch(e) {
                console.warn("⚠️ Erreur lors de l'arrêt:", e.message);
            }
            codeReader = null;
            // UI update on stop
            try {
                const vlabelEl2 = document.getElementById('vlabel');
                const slineEl2  = document.getElementById('sline');
                const scanBadge2 = document.getElementById('scan-badge');
                const btnStart2 = document.getElementById('btn-start');
                const btnStop2  = document.getElementById('btn-stop');
                const fpsChip2  = document.getElementById('fps-chip');
                const camChip2  = document.getElementById('cam-chip');
                if (vlabelEl2) vlabelEl2.textContent = 'Caméra inactive';
                if (slineEl2) slineEl2.classList.remove('on');
                if (scanBadge2) { scanBadge2.textContent = 'INACTIF'; scanBadge2.className = 'badge badge-blue'; }
                if (btnStart2) btnStart2.style.display = 'flex';
                if (btnStop2)  btnStop2.style.display = 'none';
                if (fpsChip2) fpsChip2.textContent = '0 fps';
            } catch(e) {}
        } else {
            console.log("ℹ️ Aucun scanner actif");
        }
    };

    // Compatibilité ancienne API du projet
    window.scannerInit = function(onDetected) {
        // pages existantes appellent scannerInit(onDetected)
        // on suppose les IDs par défaut utilisés dans les pages : 'preview' et 'rbox'
        // conserver le callback pour d'autres wrappers (startScan)
        try { window.__scanner_onDetected = onDetected; } catch(e) {}
        window.initScanner && window.initScanner('preview', 'rbox', onDetected);
    };

    window.scannerSetResult = function(text, type) {
        const el = document.getElementById('rbox');
        if (!el) return;
        el.textContent = text;
        if (type === 'err') {
            el.style.color = 'var(--red)';
        } else if (type === 'ok' || type === 's') {
            el.style.color = 'var(--green)';
        } else {
            el.style.color = '';
        }
    };

    // Fournir startScan/stopScan si absents (compatibilité avec certaines pages)
    if (typeof window.startScan !== 'function') {
        window.startScan = function() {
            const cb = window.__scanner_onDetected || window.onBarcodeDetected || window.onDetect || null;
            if (typeof cb !== 'function') {
                console.warn('startScan: aucun callback détecté, le scanner démarrera sans callback');
            }
            window.initScanner && window.initScanner('preview', 'rbox', cb);
        };
    }

    if (typeof window.stopScan !== 'function') {
        window.stopScan = function() {
            if (window.stopScanner) return window.stopScanner();
            console.warn('stopScan: stopScanner non disponible');
        };
    }

    // switchCam wrapper: si la page définit sa propre fonction, on ne la remplace pas
    if (typeof window.switchCam !== 'function') {
        window.switchCam = function() {
            console.warn('switchCam: non implémenté dans le fallback scanner. Changez manuellement la caméra dans les paramètres du navigateur.');
        };
    }

})();