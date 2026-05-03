// ============================================
// MINI-BANQUE - FONCTIONS JAVASCRIPT UTILES
// ============================================

/**
 * Effectuer un appel API POST avec gestion d'erreurs
 * @param {string} endpoint - URL de l'endpoint API
 * @param {object} data - Données à envoyer en JSON
 * @returns {Promise<object>} - Réponse JSON de l'API
 */
async function apiPost(endpoint, data = {}) {
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error(`Erreur API (${endpoint}):`, error);
        throw error;
    }
}

/**
 * Effectuer un appel API GET avec gestion d'erreurs
 * @param {string} endpoint - URL de l'endpoint API
 * @returns {Promise<object>} - Réponse JSON de l'API
 */
async function apiGet(endpoint) {
    try {
        const response = await fetch(endpoint, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error(`Erreur API (${endpoint}):`, error);
        throw error;
    }
}

/**
 * Formater un nombre en devise tunisienne
 * @param {number} amount - Montant à formater
 * @returns {string} - Montant formaté (ex: "1 234.56 DT")
 */
function formatMontant(amount) {
    return parseFloat(amount).toLocaleString('fr-TN', {
        style: 'currency',
        currency: 'TND',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Formater une date et heure en français
 * @param {string} dateString - Date au format ISO ou autre
 * @returns {string} - Date formatée
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Afficher une notification toast
 * @param {string} message - Message à afficher
 * @param {string} type - Type: 'success', 'error', 'info', 'warning'
 * @param {number} duration - Durée en millisecondes (par défaut 4000ms)
 */
function showToast(message, type = 'info', duration = 4000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    const style = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 8px;
        font-weight: 500;
        z-index: 1000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-width: 400px;
    `;

    const typeStyles = {
        success: 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;',
        error: 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;',
        info: 'background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;',
        warning: 'background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7;'
    };

    toast.setAttribute('style', style + (typeStyles[type] || typeStyles.info));
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/**
 * Valider une adresse email
 * @param {string} email - Email à valider
 * @returns {boolean} - True si valide, False sinon
 */
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Valider un montant
 * @param {string|number} amount - Montant à valider
 * @returns {boolean} - True si valide, False sinon
 */
function validateAmount(amount) {
    const num = parseFloat(amount);
    return !isNaN(num) && num > 0;
}

/**
 * Masquer les éléments avec animation
 * @param {HTMLElement} element - Élément à masquer
 * @param {number} duration - Durée de l'animation en ms
 */
function slideUp(element, duration = 300) {
    return new Promise((resolve) => {
        element.style.animation = `slideUp ${duration}ms ease`;
        setTimeout(() => {
            element.style.display = 'none';
            resolve();
        }, duration);
    });
}

/**
 * Afficher les éléments avec animation
 * @param {HTMLElement} element - Élément à afficher
 * @param {number} duration - Durée de l'animation en ms
 */
function slideDown(element, duration = 300) {
    return new Promise((resolve) => {
        element.style.display = 'block';
        element.style.animation = `slideDown ${duration}ms ease`;
        setTimeout(resolve, duration);
    });
}

/**
 * Ajouter les keyframes d'animation au document
 */
function addAnimationStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(20px);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Vider un formulaire
 * @param {HTMLFormElement|string} form - Élément du formulaire ou son ID
 */
function clearForm(form) {
    if (typeof form === 'string') {
        form = document.getElementById(form);
    }
    if (form) {
        form.reset();
    }
}

/**
 * Désactiver un bouton pendant une opération
 * @param {HTMLButtonElement} button - Le bouton à désactiver
 * @param {Function} asyncOperation - Fonction asynchrone à exécuter
 * @param {string} originalText - Texte original du bouton (optionnel)
 */
async function withLoadingState(button, asyncOperation, originalText = null) {
    const loadingText = originalText || button.textContent;
    const isDisabled = button.disabled;

    button.disabled = true;
    const originalContent = button.innerHTML;
    button.innerHTML = '<span class="spinner" style="width: 16px; height: 16px;"></span> Chargement...';

    try {
        return await asyncOperation();
    } finally {
        button.disabled = isDisabled;
        button.innerHTML = originalContent;
    }
}

/**
 * Ajouter des écouteurs pour les touches Entrée dans les formulaires
 * @param {HTMLFormElement|string} formId - Formulaire ou son ID
 * @param {string} submitButtonSelector - Sélecteur du bouton soumettre
 */
function setupFormSubmitOnEnter(formId, submitButtonSelector = 'button[type="submit"]') {
    const form = typeof formId === 'string' ? document.getElementById(formId) : formId;
    if (!form) return;

    const inputs = form.querySelectorAll('input[type="text"], input[type="password"], input[type="email"], input[type="number"]');
    inputs.forEach(input => {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const submitBtn = form.querySelector(submitButtonSelector);
                if (submitBtn) submitBtn.click();
            }
        });
    });
}

/**
 * Ajouter une classe de focus pour les inputs
 */
function setupInputFocusStyles() {
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.classList.add('focused');
        });
        input.addEventListener('blur', () => {
            input.classList.remove('focused');
        });
    });
}

/**
 * Mettre en place la protection CSRF (si nécessaire)
 * @returns {string|null} - Token CSRF ou null
 */
function getCsrfToken() {
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    return tokenElement ? tokenElement.getAttribute('content') : null;
}

/**
 * Initialiser l'application au chargement du DOM
 */
document.addEventListener('DOMContentLoaded', () => {
    // Ajouter les styles d'animation
    addAnimationStyles();

    // Configuration des inputs
    setupInputFocusStyles();

    // Configuration de la soumission des formulaires
    setupFormSubmitOnEnter('loginForm', '.btn-primary');
});

// Export des fonctions pour utilisation dans les modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        apiPost,
        apiGet,
        formatMontant,
        formatDate,
        showToast,
        validateEmail,
        validateAmount,
        slideUp,
        slideDown,
        addAnimationStyles,
        clearForm,
        withLoadingState,
        setupFormSubmitOnEnter,
        setupInputFocusStyles,
        getCsrfToken
    };
}
