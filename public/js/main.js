// Main JavaScript functionality for Budgie

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initModals();
    initFilters();
    initMobileMenu();
    initFormValidation();
});

// Modal functionality
function initModals() {
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target);
        }
    });

    // Close modal with escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.active');
            if (openModal) {
                closeModal(openModal);
            }
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modal) {
    if (typeof modal === 'string') {
        modal = document.getElementById(modal);
    }
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Filter functionality
function initFilters() {
    const filterInputs = document.querySelectorAll('.filter-input');
    filterInputs.forEach(input => {
        input.addEventListener('input', function() {
            filterTable(this);
        });
    });
}

function filterTable(input) {
    const table = input.closest('.table-container').querySelector('.table');
    const filterValue = input.value.toLowerCase();
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(filterValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Mobile menu functionality
function initMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('mobileOverlay');

    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            if (overlay) {
                overlay.classList.toggle('active', sidebar.classList.contains('open'));
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            this.classList.remove('active');
        });
    }
}

// Form validation
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });

    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });

    // Password confirmation
    const passwordFields = form.querySelectorAll('input[name="password"]');
    const confirmPasswordFields = form.querySelectorAll('input[name="confirm_password"]');
    
    if (passwordFields.length > 0 && confirmPasswordFields.length > 0) {
        const password = passwordFields[0].value;
        const confirmPassword = confirmPasswordFields[0].value;
        
        if (password && confirmPassword && password !== confirmPassword) {
            showFieldError(confirmPasswordFields[0], 'Passwords do not match');
            isValid = false;
        }
    }

    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
    field.style.borderColor = '#ef4444';
}

function clearFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    field.style.borderColor = '';
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('fr-FR').format(new Date(date));
}

function formatNumber(number) {
    return new Intl.NumberFormat('fr-FR').format(number);
}

/**
 * Global form submit confirmation handler for inline onsubmit="return confirmSubmit(event, '...')"
 */
function confirmSubmit(e, message, title, confirmText, confirmBtnClass) {
    if (e) {
        if (typeof e.preventDefault === 'function') e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
    }
    const target = e ? (e.target || e.srcElement) : null;
    const form = target ? (target.tagName === 'FORM' ? target : target.closest('form')) : null;

    showConfirm({
        title: title || 'Confirmation',
        message: message || 'Êtes-vous sûr de vouloir effectuer cette action ?',
        confirmText: confirmText || 'Supprimer',
        confirmBtnClass: confirmBtnClass || 'btn-danger'
    }, function() {
        if (form) {
            HTMLFormElement.prototype.submit.call(form);
        }
    });

    return false;
}

/**
 * Show a styled in-app confirmation modal instead of browser native confirm()
 */
function showConfirm(options, onConfirm) {
    let opts = typeof options === 'string' ? { message: options } : (options || {});
    const modal = document.getElementById('customConfirmModal');
    if (!modal) {
        if (confirm(opts.message || 'Êtes-vous sûr ?')) {
            if (onConfirm) onConfirm();
        }
        return;
    }

    document.getElementById('confirmModalTitle').textContent = opts.title || 'Confirmation';
    document.getElementById('confirmModalMessage').textContent = opts.message || 'Êtes-vous sûr de vouloir effectuer cette action ?';

    const okBtn = document.getElementById('confirmModalOkBtn');
    const cancelBtn = document.getElementById('confirmModalCancelBtn');

    okBtn.textContent = opts.confirmText || 'Confirmer';
    okBtn.className = 'btn ' + (opts.confirmBtnClass || 'btn-danger');
    cancelBtn.textContent = opts.cancelText || 'Annuler';

    const cleanup = () => {
        closeModal(modal);
        okBtn.onclick = null;
        cancelBtn.onclick = null;
    };

    okBtn.onclick = function() {
        cleanup();
        if (onConfirm) onConfirm();
    };

    cancelBtn.onclick = function() {
        cleanup();
    };

    openModal('customConfirmModal');
}

/**
 * Show a styled in-app alert modal instead of browser native alert()
 */
function showAlert(options) {
    let opts = typeof options === 'string' ? { message: options } : (options || {});
    const modal = document.getElementById('customAlertModal');
    if (!modal) {
        alert(opts.message || '');
        return;
    }

    document.getElementById('alertModalTitle').textContent = opts.title || 'Information';
    document.getElementById('alertModalMessage').textContent = opts.message || '';

    const okBtn = document.getElementById('alertModalOkBtn');
    okBtn.textContent = opts.btnText || 'D\'accord';

    okBtn.onclick = function() {
        closeModal(modal);
    };

    openModal('customAlertModal');
}

function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    showConfirm({ message: message });
}

function showToast(message, type = 'info') {
    showAlert({ title: type.toUpperCase(), message: message });
}

// Export functions for global use
window.confirmSubmit = confirmSubmit;
window.showConfirm = showConfirm;
window.showAlert = showAlert;
window.Budgie = {
    openModal,
    closeModal,
    formatCurrency,
    formatDate,
    formatNumber,
    confirmSubmit,
    showConfirm,
    showAlert,
    confirmDelete,
    showToast
};
