/**
 * scanner/audio.js
 * Feedback sonore lors de la détection d'un code-barres
 */

/**
 * Émet un bip court (800 Hz, 0.4s) via l'API Web Audio.
 * Silencieux si le navigateur ne supporte pas ou si l'audio est bloqué.
 */
export function jouerBip() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();

        const oscillateur = ctx.createOscillator();
        const gain        = ctx.createGain();

        oscillateur.connect(gain);
        gain.connect(ctx.destination);

        oscillateur.type            = 'sine';
        oscillateur.frequency.value = 800;

        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);

        oscillateur.start(ctx.currentTime);
        oscillateur.stop(ctx.currentTime + 0.4);
    } catch (e) {
        // Pas de son disponible — pas critique
        console.log('[audio] Son non disponible:', e.message);
    }
}
