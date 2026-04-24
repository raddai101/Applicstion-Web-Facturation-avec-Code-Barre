/**
 * scanner/camera.js
 * Accès à la caméra, flux vidéo et capture de photo
 */

let stream = null;

/**
 * Démarre le flux vidéo de la caméra arrière dans l'élément #preview.
 * @returns {Promise<MediaStream>}
 */
export async function demarrerCamera() {
    const preview = document.getElementById('preview');
    if (!preview) throw new Error('Élément #preview introuvable');

    // Contraintes : préférer la caméra arrière (environment)
    const contraintes = {
        video: {
            facingMode: { ideal: 'environment' },
            width:  { ideal: 1280 },
            height: { ideal: 720 }
        }
    };

    try {
        stream = await navigator.mediaDevices.getUserMedia(contraintes);
    } catch {
        // Fallback : n'importe quelle caméra
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
    }

    preview.srcObject = stream;
    await preview.play();

    return stream;
}

/**
 * Arrête le flux vidéo et libère la caméra.
 */
export function arreterCamera() {
    if (stream) {
        stream.getTracks().forEach(t => t.stop());
        stream = null;
    }

    const preview = document.getElementById('preview');
    if (preview) {
        preview.srcObject = null;
        preview.pause();
    }
}

/**
 * Capture une image depuis le flux vidéo en cours.
 * Renvoie { dataUrl, blob, largeur, hauteur }
 * @param {number} qualite  Qualité JPEG (0-1, défaut 0.92)
 */
export function capturerPhoto(qualite = 0.92) {
    const preview = document.getElementById('preview');
    if (!preview || !stream) throw new Error('Caméra non active');

    const largeur  = preview.videoWidth  || 640;
    const hauteur  = preview.videoHeight || 480;

    const canvas = document.createElement('canvas');
    canvas.width  = largeur;
    canvas.height = hauteur;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(preview, 0, 0, largeur, hauteur);

    const dataUrl = canvas.toDataURL('image/jpeg', qualite);

    return new Promise((resolve, reject) => {
        canvas.toBlob(blob => {
            if (!blob) { reject(new Error('Échec création blob')); return; }
            resolve({ dataUrl, blob, largeur, hauteur, canvas });
        }, 'image/jpeg', qualite);
    });
}

/**
 * Indique si la caméra est actuellement active.
 */
export function cameraActive() {
    return stream !== null;
}
