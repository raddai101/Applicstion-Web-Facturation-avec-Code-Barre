/**
 * Scanner de codes-barres
 * Utilise QuaggaJS pour détecter les codes-barres via la caméra
 */

let isScanning = false;
let quaggaInitialized = false;

document.addEventListener('DOMContentLoaded', function() {
    const preview = document.getElementById('preview');

    if (!preview) return;

    // Charger QuaggaJS dynamiquement
    loadQuaggaJS().then(() => {
        // Démarrer le scanner après le chargement de QuaggaJS
        startScanning();
    }).catch(err => {
        console.error('Erreur lors du chargement de QuaggaJS:', err);
        alert('Erreur lors du chargement du scanner. Veuillez rafraîchir la page.');
    });
});

function loadQuaggaJS() {
    return new Promise((resolve, reject) => {
        // Vérifier si QuaggaJS est déjà chargé
        if (typeof Quagga !== 'undefined') {
            resolve();
            return;
        }

        // Charger QuaggaJS depuis CDN
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js';
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function startScanning() {
    const preview = document.getElementById('preview');

    if (!preview) return;

    // Configuration QuaggaJS
    const config = {
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: preview,
            constraints: {
                width: 640,
                height: 480,
                facingMode: "environment" // Caméra arrière sur mobile
            }
        },
        locator: {
            patchSize: "medium",
            halfSample: true
        },
        numOfWorkers: 2,
        decoder: {
            readers: [
                "code_128_reader",
                "ean_reader",
                "ean_8_reader",
                "code_39_reader",
                "code_39_vin_reader",
                "codabar_reader",
                "upc_reader",
                "upc_e_reader",
                "i2of5_reader"
            ]
        },
        locate: true
    };

    // Initialiser QuaggaJS
    Quagga.init(config, function(err) {
        if (err) {
            console.error('Erreur d\'initialisation QuaggaJS:', err);
            alert('Impossible d\'initialiser le scanner. Vérifiez les permissions de la caméra.');
            return;
        }

        console.log('QuaggaJS initialisé avec succès');
        quaggaInitialized = true;

        // Démarrer le scanner
        Quagga.start();

        // Écouter les détections de codes-barres
        Quagga.onDetected(function(result) {
            const code = result.codeResult.code;
            console.log('Code-barres détecté:', code);

            // Traiter le code-barres détecté
            processBarcode(code);

            // Arrêter temporairement le scanner pour éviter les détections multiples
            stopScanning();
            setTimeout(() => {
                startScanning();
            }, 2000); // Redémarrer après 2 secondes
        });

        // Écouter les erreurs
        Quagga.onProcessed(function(result) {
            if (result) {
                // Afficher les informations de debug si nécessaire
                const drawingCtx = Quagga.canvas.ctx.overlay;
                const drawingCanvas = Quagga.canvas.dom.overlay;

                if (result.boxes) {
                    drawingCtx.clearRect(0, 0, parseInt(drawingCanvas.getAttribute("width")), parseInt(drawingCanvas.getAttribute("height")));
                    result.boxes.filter(function (box) {
                        return box !== result.box;
                    }).forEach(function (box) {
                        Quagga.ImageDebug.drawPath(box, {x: 0, y: 1}, drawingCtx, {color: "green", lineWidth: 2});
                    });
                }

                if (result.box) {
                    Quagga.ImageDebug.drawPath(result.box, {x: 0, y: 1}, drawingCtx, {color: "#00F", lineWidth: 2});
                }

                if (result.codeResult && result.codeResult.code) {
                    Quagga.ImageDebug.drawPath(result.line, {x: 'x', y: 'y'}, drawingCtx, {color: 'red', lineWidth: 3});
                }
            }
        });
    });
}

function processBarcode(code) {
    // Créer un formulaire caché pour envoyer le code au serveur
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = window.location.href;

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'code';
    input.value = code;

    form.appendChild(input);
    document.body.appendChild(form);

    // Afficher un feedback visuel
    showScanFeedback('Code détecté: ' + code);

    // Soumettre le formulaire après un court délai
    setTimeout(() => {
        form.submit();
    }, 500);
}

function showScanFeedback(message) {
    // Créer un élément de feedback
    let feedback = document.getElementById('scan-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.id = 'scan-feedback';
        feedback.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 1000;
            font-weight: bold;
        `;
        document.body.appendChild(feedback);
    }

    feedback.textContent = message;
    feedback.style.display = 'block';

    // Masquer après 3 secondes
    setTimeout(() => {
        feedback.style.display = 'none';
    }, 3000);
}

function stopScanning() {
    if (quaggaInitialized && isScanning) {
        Quagga.stop();
        isScanning = false;
        console.log('Scanner arrêté');
    }
}

// Arrêter le scanner quand la page se ferme
window.addEventListener('beforeunload', function() {
    stopScanning();
});

// Fonction pour saisie manuelle (alternative)
function manualScanInput() {
    const code = prompt('Entrez le code-barres manuellement:');
    if (code && code.trim()) {
        processBarcode(code.trim());
    }
}
