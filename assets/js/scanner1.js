/**
 * Scanner de codes-barres utilisant Quagga.js
 * Optimisé pour la détection rapide et fiable des codes-barres
 */

import Quagga from 'quagga'; // ES6
const Quagga = require('quagga').default; // Common JS (important: default)

let isScanning = false;
let detectedCode = null;

document.addEventListener('DOMContentLoaded', function() {
    const preview = document.getElementById('preview');

    if (!preview) return;

    // Ajouter le CSS
    addScannerStyles();

    // Charger Quagga.js
    loadQuaggaJS().then(() => {
        console.log('✓ Quagga.js chargé avec succès');
        startScanning();
    }).catch(err => {
        console.error('❌ Erreur Quagga:', err);
        showStatus('❌ Erreur : Impossible de charger la bibliothèque', 'error');
    });
});

function addScannerStyles() {
    const style = document.createElement('style');
    style.textContent = `
        #preview {
            display: block;
            width: 100%;
            max-width: 500px;
            height: 400px;
            border: 3px solid #3498DB;
            border-radius: 8px;
            background: #000;
            margin: 0 auto;
        }

        #scanner-canvas {
            display: none;
        }

        #scanner-status {
            margin-top: 15px;
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            display: none;
        }

        #scanner-status.active {
            display: block;
            background: #E8F5E9;
            color: #1B5E20;
            border: 2px solid #27AE60;
        }

        #scanner-status.detected {
            display: block;
            background: #C8E6C9;
            color: #0D3320;
            border: 2px solid #1B5E20;
            animation: pulse 0.5s;
        }

        #scanner-status.error {
            display: block;
            background: #FFEBEE;
            color: #B71C1C;
            border: 2px solid #D32F2F;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    `;
    document.head.appendChild(style);
}

function loadQuaggaJS() {
    return new Promise((resolve, reject) => {
        if (typeof Quagga !== 'undefined') {
            console.log('Quagga.js déjà chargé');
            resolve();
            return;
        }

        console.log('Chargement de Quagga.js...');
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js';
        script.async = true;
        
        const timeout = setTimeout(() => {
            reject(new Error('Timeout lors du chargement de Quagga (10s)'));
        }, 10000);

        script.onload = () => {
            clearTimeout(timeout);
            console.log('✓ Quagga.js chargé');
            resolve();
        };

        script.onerror = () => {
            clearTimeout(timeout);
            reject(new Error('Impossible de charger Quagga.js depuis le CDN'));
        };

        document.head.appendChild(script);
    });
}

function startScanning() {
    const preview = document.getElementById('preview');

    if (!preview || typeof Quagga === 'undefined') {
        console.error('Preview ou Quagga non disponible');
        showStatus('❌ Erreur: Scanner non disponible', 'error');
        return;
    }

    console.log('Initialisation de Quagga...');

    Quagga.init({
        inputStream: {
            name: 'Live',
            type: 'LiveStream',
            target: preview,
            constraints: {
                width: 640,
                height: 480,
                facingMode: 'environment'
            },
            area: {
                top: '0%',
                right: '0%',
                left: '0%',
                bottom: '0%'
            },
            singleChannel: false
        },
        decoder: {
            workers: {
                num: 2,
                mixed: true,
                useWorker: true
            },
            debug: {
                showPattern: false,
                showCanvas: false,
                showPatternLabel: false,
                showFrequency: false,
                drawBoundingBox: false,
                showCenterpoint: false,
                drawScanline: false,
                log: false
            }
        },
        locator: {
            patchSize: 'medium',
            halfSample: true
        },
        frequency: 10,
        multiple: false
    }, function(err) {
        if (err) {
            console.error('❌ Erreur Quagga:', err);
            showStatus('❌ Erreur: ' + err.message, 'error');
            return;
        }

        console.log('✓ Quagga initialisé');
        Quagga.start();
        isScanning = true;
        showStatus('📹 Caméra active - Scannez un code-barres', 'active');

        // Écouter les détections
        Quagga.onDetected(onBarcodeDetected);
    });
}

function onBarcodeDetected(result) {
    if (!result || !result.codeResult) return;

    const code = result.codeResult.code;
    
    // Éviter les doublons rapides
    if (detectedCode === code) {
        return;
    }

    detectedCode = code;

    console.log('✓✓✓ CODE DÉTECTÉ:', code, 'Format:', result.codeResult.format);
    showStatus('✓ Code détecté: ' + code, 'detected');
    
    playBeep();
    processBarcode(code);
    
    Quagga.stop();
    isScanning = false;
}

function processBarcode(code) {
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = window.location.href;

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'code';
    input.value = code;

    form.appendChild(input);
    document.body.appendChild(form);

    setTimeout(() => {
        form.submit();
    }, 500);
}

function showStatus(message, type = '') {
    let status = document.getElementById('scanner-status');

    if (!status) {
        status = document.createElement('div');
        status.id = 'scanner-status';
        const preview = document.getElementById('preview');
        preview.parentNode.insertBefore(status, preview.nextSibling);
    }

    status.textContent = message;
    status.className = type ? type : '';
    status.style.display = 'block';

    if (type !== 'error' && type !== 'detected') {
        setTimeout(() => {
            if (status.classList.contains(type)) {
                status.style.display = 'none';
            }
        }, 5000);
    }
}

function playBeep() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.value = 800;
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    } catch (e) {
        console.log('Son non disponible');
    }
}

function manualScanInput() {
    const code = prompt('Entrez le code-barres manuellement:');
    if (code && code.trim()) {
        showStatus('Code manuel: ' + code.trim(), 'detected');
        processBarcode(code.trim());
    }
}

function testCode(code) {
    showStatus('Code test: ' + code, 'detected');
    processBarcode(code);
}

function stopScanning() {
    if (typeof Quagga !== 'undefined') {
        try {
            Quagga.stop();
        } catch (e) {
            console.log('Erreur arrêt Quagga:', e);
        }
    }
    isScanning = false;
    showStatus('Scanner arrêté', 'error');
}

window.addEventListener('beforeunload', function() {
    stopScanning();
});
