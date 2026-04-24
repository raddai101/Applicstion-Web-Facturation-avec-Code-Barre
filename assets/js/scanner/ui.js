/**
 * scanner/ui.js
 * Gestion de l'interface utilisateur du scanner
 * Styles, messages de statut, animations
 */

export function injecterStyles() {
    if (document.getElementById('scanner-styles')) return;

    const style = document.createElement('style');
    style.id = 'scanner-styles';
    style.textContent = `
        /* ── Zone vidéo / aperçu ── */
        #preview {
            display: block;
            width: 100%;
            max-width: 500px;
            height: 300px;
            border: 3px solid #3498DB;
            border-radius: 8px;
            background: #000;
            margin: 0 auto;
            object-fit: cover;
        }

        /* ── Conteneur principal du scanner ── */
        #scanner-wrapper {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }

        /* ── Viseur (overlay) ── */
        #scanner-viseur {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 220px;
            height: 120px;
            border: 2px solid rgba(52, 152, 219, 0.8);
            border-radius: 6px;
            pointer-events: none;
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.35);
            z-index: 10;
        }

        #scanner-viseur::before,
        #scanner-viseur::after {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            border-color: #3498DB;
            border-style: solid;
        }
        #scanner-viseur::before {
            top: -2px; left: -2px;
            border-width: 3px 0 0 3px;
        }
        #scanner-viseur::after {
            bottom: -2px; right: -2px;
            border-width: 0 3px 3px 0;
        }

        /* ── Ligne de scan animée ── */
        #scanner-ligne {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(to right, transparent, #E74C3C, transparent);
            animation: scan-ligne 1.8s ease-in-out infinite;
        }

        @keyframes scan-ligne {
            0%   { top: 0%; opacity: 1; }
            50%  { top: 100%; opacity: 1; }
            100% { top: 0%; opacity: 1; }
        }

        /* ── Boutons de contrôle ── */
        #scanner-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
            justify-content: center;
        }

        .btn-scanner {
            padding: 9px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: opacity .2s, transform .1s;
        }
        .btn-scanner:hover  { opacity: .85; }
        .btn-scanner:active { transform: scale(.97); }

        .btn-photo    { background: #2ECC71; color: #fff; }
        .btn-manual   { background: #3498DB; color: #fff; }
        .btn-restart  { background: #95A5A6; color: #fff; }

        /* ── Aperçu photo prise ── */
        #photo-preview-wrap {
            display: none;
            max-width: 500px;
            margin: 12px auto 0;
            text-align: center;
        }
        #photo-preview {
            max-width: 100%;
            border-radius: 8px;
            border: 2px solid #BDC3C7;
        }
        #photo-preview-label {
            font-size: 13px;
            color: #7F8C8D;
            margin-top: 4px;
        }

        /* ── Messages de statut ── */
        #scanner-status {
            display: none;
            margin-top: 12px;
            padding: 11px 15px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            font-size: 14px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        #scanner-status.actif    { display:block; background:#E8F5E9; color:#1B5E20; border:2px solid #27AE60; }
        #scanner-status.détecté  { display:block; background:#C8E6C9; color:#0D3320; border:2px solid #1B5E20; animation: pulse .4s; }
        #scanner-status.erreur   { display:block; background:#FFEBEE; color:#B71C1C; border:2px solid #D32F2F; }
        #scanner-status.info     { display:block; background:#E3F2FD; color:#0D47A1; border:2px solid #1565C0; }
        #scanner-status.chargement { display:block; background:#FFF8E1; color:#E65100; border:2px solid #FF9800; }

        @keyframes pulse {
            0%   { transform: scale(1); }
            50%  { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Affiche un message de statut sous le scanner.
 * @param {string} message  Texte à afficher
 * @param {'actif'|'détecté'|'erreur'|'info'|'chargement'} type
 * @param {number|null} duree  Auto-masquage après N ms (null = permanent)
 */
export function afficherStatut(message, type = 'info', duree = null) {
    let el = document.getElementById('scanner-status');

    if (!el) {
        el = document.createElement('div');
        el.id = 'scanner-status';
        const wrapper = document.getElementById('scanner-wrapper') || document.getElementById('preview')?.parentNode;
        wrapper?.appendChild(el);
    }

    el.textContent = message;
    el.className   = type;

    if (duree) {
        clearTimeout(el._timer);
        el._timer = setTimeout(() => { el.style.display = 'none'; }, duree);
    }
}

/**
 * Affiche ou masque la ligne de scan animée dans le viseur.
 */
export function toggleLigneScan(visible) {
    const ligne = document.getElementById('scanner-ligne');
    if (ligne) ligne.style.display = visible ? 'block' : 'none';
}

/**
 * Construit le wrapper HTML autour de l'élément #preview
 * (viseur, contrôles, aperçu photo).
 */
export function construireUI(onPhoto, onManuel, onRedemarrer) {
    const preview = document.getElementById('preview');
    if (!preview) return;

    // Wrapper
    const wrapper = document.createElement('div');
    wrapper.id = 'scanner-wrapper';
    preview.parentNode.insertBefore(wrapper, preview);
    wrapper.appendChild(preview);

    // Viseur
    const viseur = document.createElement('div');
    viseur.id = 'scanner-viseur';
    const ligne = document.createElement('div');
    ligne.id = 'scanner-ligne';
    viseur.appendChild(ligne);
    wrapper.appendChild(viseur);

    // Contrôles
    const controls = document.createElement('div');
    controls.id = 'scanner-controls';

    const btnPhoto = document.createElement('button');
    btnPhoto.type      = 'button';
    btnPhoto.className = 'btn-scanner btn-photo';
    btnPhoto.textContent = '📸 Prendre une photo';
    btnPhoto.onclick   = onPhoto;

    const btnManuel = document.createElement('button');
    btnManuel.type      = 'button';
    btnManuel.className = 'btn-scanner btn-manual';
    btnManuel.textContent = '⌨️ Saisie manuelle';
    btnManuel.onclick   = onManuel;

    const btnRestart = document.createElement('button');
    btnRestart.type      = 'button';
    btnRestart.className = 'btn-scanner btn-restart';
    btnRestart.textContent = '🔄 Redémarrer';
    btnRestart.onclick   = onRedemarrer;

    controls.appendChild(btnPhoto);
    controls.appendChild(btnManuel);
    controls.appendChild(btnRestart);
    wrapper.parentNode.insertBefore(controls, wrapper.nextSibling);

    // Aperçu de la photo prise
    const photoWrap = document.createElement('div');
    photoWrap.id = 'photo-preview-wrap';
    const photoImg   = document.createElement('img');
    photoImg.id = 'photo-preview';
    const photoLabel = document.createElement('p');
    photoLabel.id = 'photo-preview-label';
    photoLabel.textContent = 'Photo analysée par Quagga';
    photoWrap.appendChild(photoImg);
    photoWrap.appendChild(photoLabel);
    controls.insertAdjacentElement('afterend', photoWrap);

    // Zone statut
    const statusEl = document.createElement('div');
    statusEl.id = 'scanner-status';
    photoWrap.insertAdjacentElement('afterend', statusEl);
}

/**
 * Affiche la miniature de la photo capturée.
 */
export function afficherAperçuPhoto(dataUrl) {
    const wrap = document.getElementById('photo-preview-wrap');
    const img  = document.getElementById('photo-preview');
    if (wrap && img) {
        img.src = dataUrl;
        wrap.style.display = 'block';
    }
}

/**
 * Cache la miniature de la photo.
 */
export function masquerAperçuPhoto() {
    const wrap = document.getElementById('photo-preview-wrap');
    if (wrap) wrap.style.display = 'none';
}
