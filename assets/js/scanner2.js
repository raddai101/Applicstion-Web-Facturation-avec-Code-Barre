/**
 * ============================================================
 *  scanner.js — Système de scan de codes-barres
 *  Compatible XAMPP / navigateur sans bundler
 * ============================================================
 *
 *  SECTIONS :
 *  1. CONFIG         — constantes globales
 *  2. UI             — styles, viseur, statuts, aperçu photo
 *  3. AUDIO          — bip de confirmation
 *  4. CAMERA         — accès caméra et capture photo
 *  5. DECODEUR       — chargement Quagga + décodage
 *  6. ACTIONS        — redirection GET, saisie manuelle
 *  7. ORCHESTRATEUR  — point d'entrée, gestion du flux
 * ============================================================
 */

var SCANNER = (function () {

    // ════════════════════════════════════════════════════════
    // 1. CONFIG
    // ════════════════════════════════════════════════════════

    var CFG = {
        quaggaCDN : 'https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js',
        timeoutMs : 12000,
        readers   : [
            'ean_reader', 'ean_8_reader',
            'code_128_reader', 'code_39_reader',
            'upc_reader', 'upc_e_reader'
        ]
    };

    var etat = {
        stream         : null,
        liveActif      : false,
        detectionFaite : false,
        echecPhotos    : 0,
        mode           : 'photo',    // 'photo' | 'live'
        quaggaCharge   : false
    };

    // ════════════════════════════════════════════════════════
    // 2. UI  —  styles, viseur, statuts, aperçu photo
    // ════════════════════════════════════════════════════════

    function injecterStyles() {
        if (document.getElementById('scanner-css')) return;
        var s = document.createElement('style');
        s.id  = 'scanner-css';
        s.textContent = '\
#preview {\
  display:block;width:100%;max-width:500px;height:280px;\
  border:3px solid #3498DB;border-radius:8px;\
  background:#000;margin:0 auto;object-fit:cover;\
}\
#scanner-wrapper {\
  position:relative;max-width:500px;margin:0 auto;\
}\
#scanner-viseur {\
  position:absolute;top:50%;left:50%;\
  transform:translate(-50%,-50%);\
  width:220px;height:110px;\
  border:2px solid rgba(52,152,219,.9);\
  border-radius:6px;pointer-events:none;\
  box-shadow:0 0 0 9999px rgba(0,0,0,.38);\
  z-index:10;\
}\
#scanner-viseur::before,#scanner-viseur::after {\
  content:"";position:absolute;\
  width:18px;height:18px;\
  border-color:#3498DB;border-style:solid;\
}\
#scanner-viseur::before{top:-2px;left:-2px;border-width:3px 0 0 3px;}\
#scanner-viseur::after {bottom:-2px;right:-2px;border-width:0 3px 3px 0;}\
#scanner-ligne {\
  position:absolute;left:0;width:100%;height:2px;\
  background:linear-gradient(to right,transparent,#E74C3C,transparent);\
  animation:scanLigne 1.8s ease-in-out infinite;top:0;\
}\
@keyframes scanLigne{\
  0%{top:0%;opacity:1;}50%{top:100%;opacity:1;}100%{top:0%;opacity:1;}\
}\
#scanner-controls {\
  display:flex;flex-wrap:wrap;gap:8px;\
  margin-top:10px;justify-content:center;\
}\
.btn-scan {\
  padding:10px 18px;border:none;border-radius:5px;\
  cursor:pointer;font-weight:600;font-size:14px;\
  transition:opacity .2s,transform .1s;\
}\
.btn-scan:hover{opacity:.85;}.btn-scan:active{transform:scale(.97);}\
.btn-photo  {background:#2ECC71;color:#fff;}\
.btn-manual {background:#3498DB;color:#fff;}\
.btn-restart{background:#95A5A6;color:#fff;}\
#photo-preview-wrap{\
  display:none;max-width:500px;margin:10px auto 0;text-align:center;\
}\
#photo-preview{max-width:100%;border-radius:8px;border:2px solid #BDC3C7;}\
#photo-preview-label{font-size:12px;color:#7F8C8D;margin-top:4px;}\
#scanner-status{\
  display:none;margin:10px auto 0;padding:11px 15px;\
  border-radius:6px;font-weight:600;text-align:center;\
  font-size:14px;max-width:500px;\
}\
#scanner-status.actif     {display:block;background:#E8F5E9;color:#1B5E20;border:2px solid #27AE60;}\
#scanner-status.detecte   {display:block;background:#C8E6C9;color:#0D3320;border:2px solid #1B5E20;animation:pulse .4s;}\
#scanner-status.erreur    {display:block;background:#FFEBEE;color:#B71C1C;border:2px solid #D32F2F;}\
#scanner-status.info      {display:block;background:#E3F2FD;color:#0D47A1;border:2px solid #1565C0;}\
#scanner-status.chargement{display:block;background:#FFF8E1;color:#E65100;border:2px solid #FF9800;}\
@keyframes pulse{0%{transform:scale(1);}50%{transform:scale(1.03);}100%{transform:scale(1);}}';
        document.head.appendChild(s);
    }

    function creerBtn(texte, classes, handler) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = classes;
        btn.textContent = texte;
        btn.addEventListener('click', handler);
        return btn;
    }

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
        viseur.id  = 'scanner-viseur';
        var ligne  = document.createElement('div');
        ligne.id   = 'scanner-ligne';
        viseur.appendChild(ligne);
        wrapper.appendChild(viseur);

        // Boutons
        var controls = document.createElement('div');
        controls.id  = 'scanner-controls';
        controls.appendChild(creerBtn('📸 Prendre une photo', 'btn-scan btn-photo',   onBtnPhoto));
        controls.appendChild(creerBtn('⌨️ Saisie manuelle',   'btn-scan btn-manual',  onBtnManuel));
        controls.appendChild(creerBtn('🔄 Redémarrer',        'btn-scan btn-restart', onBtnRedemarrer));
        wrapper.parentNode.insertBefore(controls, wrapper.nextSibling);

        // Aperçu photo
        var photoWrap  = document.createElement('div');  photoWrap.id = 'photo-preview-wrap';
        var photoImg   = document.createElement('img');  photoImg.id  = 'photo-preview';
        var photoLabel = document.createElement('p');    photoLabel.id = 'photo-preview-label';
        photoLabel.textContent = 'Dernière photo analysée';
        photoWrap.appendChild(photoImg);
        photoWrap.appendChild(photoLabel);
        controls.insertAdjacentElement('afterend', photoWrap);

        // Statut
        var statusEl = document.createElement('div'); statusEl.id = 'scanner-status';
        photoWrap.insertAdjacentElement('afterend', statusEl);
    }

    function afficherStatut(msg, type, duree) {
        var el = document.getElementById('scanner-status');
        if (!el) return;
        el.textContent = msg;
        el.className   = type || '';
        if (duree) {
            clearTimeout(el._t);
            el._t = setTimeout(function () { el.style.display = 'none'; }, duree);
        }
    }

    function afficherApercu(dataUrl) {
        var wrap = document.getElementById('photo-preview-wrap');
        var img  = document.getElementById('photo-preview');
        if (wrap && img) { img.src = dataUrl; wrap.style.display = 'block'; }
    }

    function masquerApercu() {
        var wrap = document.getElementById('photo-preview-wrap');
        if (wrap) wrap.style.display = 'none';
    }

    function toggleLigne(visible) {
        var el = document.getElementById('scanner-ligne');
        if (el) el.style.display = visible ? 'block' : 'none';
    }

    // ════════════════════════════════════════════════════════
    // 3. AUDIO
    // ════════════════════════════════════════════════════════

    function jouerBip() {
        try {
            var ctx  = new (window.AudioContext || window.webkitAudioContext)();
            var osc  = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.value = 800;
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.4);
        } catch (e) { /* pas de son disponible */ }
    }

    // ════════════════════════════════════════════════════════
    // 4. CAMERA
    // ════════════════════════════════════════════════════════

    function demarrerCamera() {
        return new Promise(function (resolve, reject) {
            var preview = document.getElementById('preview');
            if (!preview) { reject(new Error('#preview introuvable')); return; }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                reject(new Error('getUserMedia non supporté. Utilisez Chrome/Firefox en HTTPS ou localhost.'));
                return;
            }

            var contraintes = {
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }
            };

            navigator.mediaDevices.getUserMedia(contraintes)
                .catch(function () {
                    return navigator.mediaDevices.getUserMedia({ video: true });
                })
                .then(function (stream) {
                    etat.stream = stream;
                    preview.srcObject = stream;
                    return preview.play();
                })
                .then(function () { resolve(); })
                .catch(function (err) { reject(err); });
        });
    }

    function arreterCamera() {
        if (etat.stream) {
            etat.stream.getTracks().forEach(function (t) { t.stop(); });
            etat.stream = null;
        }
        var preview = document.getElementById('preview');
        if (preview) { preview.srcObject = null; preview.pause(); }
    }

    function capturerPhoto() {
        return new Promise(function (resolve, reject) {
            var preview = document.getElementById('preview');
            if (!preview || !etat.stream) { reject(new Error('Caméra non active')); return; }

            var w = preview.videoWidth  || 640;
            var h = preview.videoHeight || 480;
            var canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(preview, 0, 0, w, h);
            var dataUrl = canvas.toDataURL('image/jpeg', 0.92);

            canvas.toBlob(function (blob) {
                if (!blob) { reject(new Error('Échec création blob')); return; }
                resolve({ dataUrl: dataUrl, canvas: canvas });
            }, 'image/jpeg', 0.92);
        });
    }

    // ════════════════════════════════════════════════════════
    // 5. DECODEUR  (Quagga)
    // ════════════════════════════════════════════════════════

    function chargerQuagga() {
        return new Promise(function (resolve, reject) {
            if (typeof Quagga !== 'undefined') { resolve(); return; }

            var script = document.createElement('script');
            script.src  = CFG.quaggaCDN;
            script.async = true;

            var timer = setTimeout(function () {
                reject(new Error('Timeout chargement Quagga (' + (CFG.timeoutMs / 1000) + 's)'));
            }, CFG.timeoutMs);

            script.onload = function () {
                clearTimeout(timer);
                if (typeof Quagga === 'undefined') {
                    reject(new Error('Quagga non défini après chargement'));
                } else {
                    resolve();
                }
            };
            script.onerror = function () {
                clearTimeout(timer);
                reject(new Error('Impossible de charger Quagga depuis le CDN. Vérifiez votre connexion internet.'));
            };
            document.head.appendChild(script);
        });
    }

    function decoderDepuisPhoto(canvas) {
        return new Promise(function (resolve, reject) {
            Quagga.decodeSingle({
                src          : canvas.toDataURL('image/jpeg', 0.92),
                numOfWorkers : 0,
                inputStream  : { size: 800 },
                locator      : { patchSize: 'medium', halfSample: false },
                decoder      : { readers: CFG.readers },
                locate       : true
            }, function (result) {
                if (result && result.codeResult && result.codeResult.code) {
                    resolve({ code: result.codeResult.code, format: result.codeResult.format });
                } else {
                    reject(new Error('Aucun code détecté sur la photo'));
                }
            });
        });
    }

    function demarrerLive() {
        if (etat.liveActif) return;
        var preview = document.getElementById('preview');
        Quagga.init({
            inputStream: {
                name       : 'Live', type: 'LiveStream', target: preview,
                constraints: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }
            },
            locator  : { patchSize: 'medium', halfSample: true },
            decoder  : { readers: CFG.readers },
            frequency: 8,
            multiple : false
        }, function (err) {
            if (err) { afficherStatut('❌ Mode live : ' + err.message, 'erreur'); return; }
            etat.liveActif = true;
            Quagga.start();
            Quagga.onDetected(function (result) {
                if (result && result.codeResult && result.codeResult.code) {
                    onCodeDetecte(result.codeResult.code, result.codeResult.format);
                }
            });
            afficherStatut('📹 Détection continue — placez le code devant la caméra', 'actif');
        });
    }

    function arreterLive() {
        if (typeof Quagga !== 'undefined' && etat.liveActif) {
            try { Quagga.stop(); } catch (e) { /* silencieux */ }
            etat.liveActif = false;
        }
    }

    // ════════════════════════════════════════════════════════
    // 6. ACTIONS
    // ════════════════════════════════════════════════════════

    function traiterCode(code) {
        var form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = 'code'; input.value = code.trim();
        form.appendChild(input);
        document.body.appendChild(form);
        setTimeout(function () { form.submit(); }, 600);
    }

    // ════════════════════════════════════════════════════════
    // 7. ORCHESTRATEUR
    // ════════════════════════════════════════════════════════

    function onCodeDetecte(code, format) {
        if (etat.detectionFaite) return;
        etat.detectionFaite = true;
        console.log('[scanner] ✅ Code :', code, '| Format :', format || '—');
        afficherStatut('✅ Code détecté : ' + code, 'detecte');
        jouerBip();
        arreterLive();
        arreterCamera();
        toggleLigne(false);
        traiterCode(code);
    }

    function onBtnPhoto() {
        if (etat.detectionFaite) return;
        if (!etat.stream) {
            afficherStatut('⚠️ Caméra non active. Cliquez sur 🔄 Redémarrer.', 'erreur');
            return;
        }
        afficherStatut('📸 Capture en cours…', 'chargement');
        masquerApercu();

        capturerPhoto()
            .then(function (res) {
                afficherApercu(res.dataUrl);
                afficherStatut('🔍 Analyse de la photo…', 'chargement');
                return decoderDepuisPhoto(res.canvas);
            })
            .then(function (res) {
                etat.echecPhotos = 0;
                onCodeDetecte(res.code, res.format);
            })
            .catch(function (err) {
                etat.echecPhotos++;
                console.warn('[scanner] Échec photo #' + etat.echecPhotos + ' :', err.message);
                if (etat.echecPhotos >= 2 && etat.mode !== 'live') {
                    etat.mode = 'live';
                    afficherStatut('📷 2 échecs — passage en mode vidéo continu…', 'info');
                    arreterCamera();
                    demarrerLive();
                } else {
                    afficherStatut(
                        '⚠️ Aucun code détecté. Recadrez et réessayez (' + etat.echecPhotos + '/2).',
                        'erreur', 4000
                    );
                }
            });
    }

    function onBtnManuel() {
        var code = prompt('Entrez le code-barres manuellement :');
        if (code && code.trim()) {
            afficherStatut('⌨️ Code manuel : ' + code.trim(), 'detecte');
            jouerBip();
            etat.detectionFaite = true;
            arreterLive();
            arreterCamera();
            traiterCode(code.trim());
        }
    }

    function onBtnRedemarrer() {
        etat.detectionFaite = false;
        etat.echecPhotos    = 0;
        etat.mode           = 'photo';
        masquerApercu();
        arreterLive();
        arreterCamera();

        afficherStatut('⏳ Redémarrage de la caméra…', 'chargement');
        demarrerCamera()
            .then(function () {
                toggleLigne(true);
                afficherStatut('✅ Caméra active — Appuyez sur 📸 pour scanner', 'actif');
            })
            .catch(function (err) {
                afficherStatut('❌ ' + err.message, 'erreur');
            });
    }

    function init() {
        var preview = document.getElementById('preview');
        if (!preview) return;

        injecterStyles();
        construireUI();

        afficherStatut('⏳ Chargement de Quagga…', 'chargement');

        chargerQuagga()
            .then(function () {
                afficherStatut('📷 Démarrage de la caméra…', 'chargement');
                return demarrerCamera();
            })
            .then(function () {
                toggleLigne(true);
                afficherStatut('✅ Caméra active — Appuyez sur 📸 pour scanner', 'actif');
            })
            .catch(function (err) {
                console.error('[scanner] init error:', err);
                afficherStatut('❌ ' + err.message + ' — Utilisez la saisie manuelle.', 'erreur');
            });
    }

    window.addEventListener('beforeunload', function () {
        arreterLive();
        arreterCamera();
    });

    return { init: init };

})();

// ── Fonctions globales appelées depuis les onclick="" PHP ─────────────────────
function manualScanInput() {
    var code = prompt('Entrez le code-barres manuellement :');
    if (code && code.trim()) {
        var form = document.createElement('form');
        form.method = 'GET';
        form.action = window.location.pathname;
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = 'code'; input.value = code.trim();
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

function testCode(code) {
    var form = document.createElement('form');
    form.method = 'GET';
    form.action = window.location.pathname;
    var input = document.createElement('input');
    input.type = 'hidden'; input.name = 'code'; input.value = code;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// ── Démarrage automatique ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    SCANNER.init();
});
