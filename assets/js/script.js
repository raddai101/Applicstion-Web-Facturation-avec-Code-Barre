/**
 * Scripts utilitaires globaux
 */

// Confirmation avant suppression
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

// Format monétaire
function formatCurrency(value) {
    return new Intl.NumberFormat('fr-CD', {
        style: 'currency',
        currency: 'CDF'
    }).format(value);
}

// Validation d'email
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validation de date
function isValidDate(dateString) {
    const re = /^\d{4}-\d{2}-\d{2}$/;
    return re.test(dateString);
}

document.addEventListener('DOMContentLoaded', function() {
    // Ajouter les confirmations de suppression aux boutons
    const deleteButtons = document.querySelectorAll('button[data-confirm]');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
});
