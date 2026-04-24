/**
 * scanner/actions.js
 * Actions déclenchées après la détection d'un code-barres :
 *  - redirection vers la page courante avec ?code=...
 *  - saisie manuelle
 */

/**
 * Redirige vers la page courante en ajoutant le code-barres en paramètre GET.
 * Un délai court laisse le temps à l'UI d'afficher le retour visuel.
 *
 * @param {string} code     Code-barres détecté
 * @param {number} delai    Millisecondes avant soumission (défaut 600)
 */
export function traiterCodeBarre(code, delai = 600) {
    const form  = document.createElement('form');
    form.method = 'GET';
    form.action = window.location.pathname; // pas de query string existante

    const input = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'code';
    input.value = code.trim();

    form.appendChild(input);
    document.body.appendChild(form);

    setTimeout(() => form.submit(), delai);
}

/**
 * Ouvre une boîte de dialogue pour saisir un code manuellement,
 * puis appelle le callback avec le code saisi.
 *
 * @param {function} onCode  Callback(code: string)
 */
export function saisieManuelle(onCode) {
    const code = prompt('Entrez le code-barres manuellement :');
    if (code && code.trim()) {
        onCode(code.trim());
    }
}
