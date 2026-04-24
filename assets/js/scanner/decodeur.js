/**
 * scanner/decodeur.js
 * Chargement de Quagga.js et décodage de codes-barres
 * depuis une image (photo) ou en mode vidéo continu.
 */

const QUAGGA_CDN = 'https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js';
const TIMEOUT_MS = 12000;

// ─── Formats supportés ────────────────────────────────────────────────────────
const READERS = [
    'ean_reader',
    'ean_8_reader',
    'code_128_reader',
    'code_39_reader',
    'upc_reader',
    'upc_e_reader',
    'codabar_reader',
    'i2of5_reader'
];

// ─── Chargement du script Quagga depuis le CDN ───────────────────────────────

let _quaggaPromise = null;

export function chargerQuagga() {
    if (_quaggaPromise) return _quaggaPromise;

    _quaggaPromise = new Promise((resolve, reject) => {
        if (typeof Quagga !== 'undefined') { resolve(Quagga); return; }

        const script = document.createElement('script');
        script.src   = QUAGGA_CDN;
        script.async = true;

        const timer = setTimeout(() => {
            reject(new Error(`Timeout chargement Quagga (${TIMEOUT_MS / 1000}s)`));
        }, TIMEOUT_MS);

        script.onload = () => {
            clearTimeout(timer);
            if (typeof Quagga === 'undefined') {
                reject(new Error('Quagga non défini après chargement'));
            } else {
                resolve(Quagga);
            }
        };

        script.onerror = () => {
            clearTimeout(timer);
            reject(new Error('Impossible de charger Quagga depuis le CDN'));
        };

        document.head.appendChild(script);
    });

    return _quaggaPromise;
}

// ─── Décodage depuis une image (mode photo) ───────────────────────────────────

/**
 * Tente de décoder un code-barres dans un canvas ou une URL d'image.
 *
 * @param {HTMLCanvasElement|string} source  Canvas ou dataUrl JPEG
 * @returns {Promise<{code: string, format: string}>}
 */
export function decoderDepuisImage(source) {
    return new Promise(async (resolve, reject) => {
        const Q = await chargerQuagga().catch(reject);
        if (!Q) return;

        // Quagga.decodeSingle attend une URL ou un objet avec src
        const config = {
            src: typeof source === 'string' ? source : source.toDataURL('image/jpeg', 0.92),
            numOfWorkers: 0,          // 0 = synchrone (obligatoire pour decodeSingle)
            inputStream: {
                size: 800             // résolution de traitement
            },
            locator: {
                patchSize: 'medium',
                halfSample: false     // false = plus précis sur photo fixe
            },
            decoder: {
                readers: READERS
            },
            locate: true             // localise automatiquement le code
        };

        Q.decodeSingle(config, result => {
            if (result && result.codeResult && result.codeResult.code) {
                resolve({
                    code:   result.codeResult.code,
                    format: result.codeResult.format
                });
            } else {
                reject(new Error('Aucun code-barres détecté sur la photo'));
            }
        });
    });
}

// ─── Décodage en mode vidéo continu (LiveStream) ─────────────────────────────

let _liveActif = false;

/**
 * Initialise Quagga en mode flux vidéo live.
 *
 * @param {HTMLElement} target     Élément cible (balise <video>)
 * @param {function}    onDetecte  Callback({ code, format })
 * @param {function}    onErreur   Callback(Error)
 */
export async function demarrerLive(target, onDetecte, onErreur) {
    const Q = await chargerQuagga().catch(onErreur);
    if (!Q) return;

    if (_liveActif) arreterLive();

    Q.init({
        inputStream: {
            name: 'Live',
            type: 'LiveStream',
            target,
            constraints: {
                width:      { ideal: 1280 },
                height:     { ideal: 720 },
                facingMode: { ideal: 'environment' }
            },
            area: { top: '20%', right: '10%', left: '10%', bottom: '20%' }
        },
        locator: {
            patchSize: 'medium',
            halfSample: true
        },
        decoder: { readers: READERS },
        frequency: 8,   // détections/sec (plus bas = moins de CPU)
        multiple: false
    }, err => {
        if (err) { onErreur(err); return; }
        _liveActif = true;
        Q.start();

        Q.onDetected(result => {
            if (result?.codeResult?.code) {
                onDetecte({ code: result.codeResult.code, format: result.codeResult.format });
            }
        });
    });
}

/**
 * Arrête Quagga en mode live.
 */
export function arreterLive() {
    if (typeof Quagga !== 'undefined' && _liveActif) {
        try { Quagga.stop(); } catch (_) { /* silencieux */ }
        _liveActif = false;
    }
}
