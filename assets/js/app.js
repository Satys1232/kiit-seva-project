/**
 * KIIT SEVA - Main Application JavaScript
 * Core functionality and utilities for the platform
 */

// Application initialization
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    // Initialize common components
    initializeNavigation();
    initializeModals();
    initializeTooltips();
    initializeFormValidation();
    initializeLoadingStates();
    
    // Initialize page-specific functionality
    const currentPage = getCurrentPage();
    initializePageSpecific(currentPage);
    
    console.log('KIIT SEVA application initialized successfully');
}

/**
 * Get current page identifier
 */
function getCurrentPage() {
    const path = window.location.pathname;
    
    if (path.includes('/dashboard/')) return 'dashboard';
    if (path.includes('/booking/')) return 'booking';
    if (path.includes('/tracking/')) return 'tracking';
    if (path.includes('/feedback/')) return 'feedback';
    if (path.includes('/auth/')) return 'auth';
    
    return 'home';
}

/**
 * Initialize page-specific functionality
 */
function initializePageSpecific(page) {
    switch (page) {
        case 'booking':
            if (typeof initializeBooking === 'function') {
                initializeBooking();
            }
            break;
        case 'tracking':
            if (typeof initializeTracking === 'function') {
                initializeTracking();
            }
            break;
        case 'feedback':
            if (typeof initializeFeedback === 'function') {
                initializeFeedback();
            }
            break;
        case 'auth':
            initializeAuthForms();
            break;
    }
}

/**
 * Navigation functionality
 */
function initializeNavigation() {
    // Mobile menu toggle
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const mobileNav = document.querySelector('.mobile-nav');
    
    if (mobileToggle && mobileNav) {
        mobileToggle.addEventListener('click', function() {
            mobileNav.classList.toggle('show');
            mobileToggle.classList.toggle('active');
        });
    }
    
    // User dropdown
    const userButton = document.querySelector('.user-button');
    const userDropdown = document.querySelector('.dropdown-menu');
    
    if (userButton && userDropdown) {
        userButton.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            userDropdown.classList.remove('show');
        });
    }
    
    // Active page highlighting
    highlightActivePage();
}

/**
 * Highlight active page in navigation
 */
function highlightActivePage() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link, .mobile-nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) {
            link.classList.add('active');
        }
    });
}

/**
 * Modal functionality
 */
function initializeModals() {
    // Create modal backdrop if it doesn't exist
    if (!document.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
        `;
        document.body.appendChild(backdrop);
    }
    
    // Modal triggers
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-modal-target]')) {
            const targetId = e.target.getAttribute('data-modal-target');
            openModal(targetId);
        }
        
        if (e.target.matches('[data-modal-close]') || e.target.closest('[data-modal-close]')) {
            closeModal();
        }
    });
    
    // Close modal on backdrop click
    document.querySelector('.modal-backdrop')?.addEventListener('click', closeModal);
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
}

/**
 * Open modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = document.querySelector('.modal-backdrop');
    
    if (modal && backdrop) {
        modal.style.display = 'block';
        backdrop.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Focus management
        const firstFocusable = modal.querySelector('input, button, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) {
            firstFocusable.focus();
        }
    }
}

/**
 * Close modal
 */
function closeModal() {
    const modals = document.querySelectorAll('.modal');
    const backdrop = document.querySelector('.modal-backdrop');
    
    modals.forEach(modal => {
        modal.style.display = 'none';
    });
    
    if (backdrop) {
        backdrop.style.display = 'none';
    }
    
    document.body.style.overflow = '';
}

/**
 * Tooltip functionality
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
        element.addEventListener('focus', showTooltip);
        element.addEventListener('blur', hideTooltip);
    });
}

/**
 * Show tooltip
 */
function showTooltip(e) {
    const element = e.target;
    const text = element.getAttribute('data-tooltip');
    
    if (!text) return;
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.875rem;
        z-index: 1050;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    document.body.appendChild(tooltip);
    
    // Position tooltip
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    
    // Show tooltip
    setTimeout(() => {
        tooltip.style.opacity = '1';
    }, 10);
    
    // Store reference for cleanup
    element._tooltip = tooltip;
}

/**
 * Hide tooltip
 */
function hideTooltip(e) {
    const element = e.target;
    const tooltip = element._tooltip;
    
    if (tooltip) {
        tooltip.remove();
        delete element._tooltip;
    }
}

/**
 * Form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(input);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(input);
            });
        });
    });
}

/**
 * Validate form
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required validation
    if (required && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Type-specific validation
    if (value) {
        switch (type) {
            case 'email':
                if (!isValidEmail(value)) {
                    showFieldError(field, 'Please enter a valid email address');
                    return false;
                }
                break;
            case 'tel':
                if (!isValidPhone(value)) {
                    showFieldError(field, 'Please enter a valid phone number');
                    return false;
                }
                break;
            case 'password':
                if (value.length < 8) {
                    showFieldError(field, 'Password must be at least 8 characters long');
                    return false;
                }
                break;
        }
    }
    
    // Custom validation
    const pattern = field.getAttribute('pattern');
    if (pattern && value && !new RegExp(pattern).test(value)) {
        const message = field.getAttribute('data-error-message') || 'Please enter a valid value';
        showFieldError(field, message);
        return false;
    }
    
    // Show success state
    field.classList.add('is-valid');
    return true;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid', 'is-valid');
    
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Loading states
 */
function initializeLoadingStates() {
    // Handle form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        if (submitButton) {
            showLoading(submitButton);
        }
    });
}

/**
 * Show loading state
 */
function showLoading(element, text = 'Loading...') {
    element.disabled = true;
    element.innerHTML = `
        <span class="spinner"></span>
        ${text}
    `;
    element.classList.add('loading');
}

/**
 * Hide loading state
 */
function hideLoading(element, originalText = 'Submit') {
    element.disabled = false;
    element.innerHTML = originalText;
    element.classList.remove('loading');
}

/**
 * Authentication forms
 */
function initializeAuthForms() {
    // Password visibility toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
    });
    
    // Password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"][data-strength]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            updatePasswordStrength(this);
        });
    });
}

/**
 * Update password strength indicator
 */
function updatePasswordStrength(input) {
    const password = input.value;
    const strength = calculatePasswordStrength(password);
    
    let strengthIndicator = input.parentNode.querySelector('.password-strength');
    if (!strengthIndicator) {
        strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        input.parentNode.appendChild(strengthIndicator);
    }
    
    const strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const strengthColors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745'];
    
    strengthIndicator.innerHTML = `
        <div class="strength-bar">
            <div class="strength-fill" style="width: ${strength * 20}%; background-color: ${strengthColors[strength - 1] || '#dc3545'}"></div>
        </div>
        <small class="strength-text">${strengthLevels[strength - 1] || 'Very Weak'}</small>
    `;
}

/**
 * Calculate password strength
 */
function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

/**
 * Utility functions
 */

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate phone number (Indian format)
 */
function isValidPhone(phone) {
    const phoneRegex = /^[6-9]\d{9}$/;
    const cleanPhone = phone.replace(/\D/g, '');
    return phoneRegex.test(cleanPhone);
}

/**
 * Format date
 */
function formatDate(date, format = 'readable') {
    const d = new Date(date);
    
    switch (format) {
        case 'readable':
            return d.toLocaleDateString('en-IN', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        case 'short':
            return d.toLocaleDateString('en-IN');
        case 'time':
            return d.toLocaleTimeString('en-IN', {
                hour: '2-digit',
                minute: '2-digit'
            });
        default:
            return d.toLocaleDateString('en-IN');
    }
}

/**
 * Show notification
 */
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1060;
        min-width: 300px;
        max-width: 500px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    // Type-specific styling
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };
    
    notification.style.borderLeft = `4px solid ${colors[type] || colors.info}`;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove
    if (duration > 0) {
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, duration);
    }
}

/**
 * AJAX helper
 */
function ajax(options) {
    const defaults = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const config = Object.assign({}, defaults, options);
    
    return fetch(config.url, {
        method: config.method,
        headers: config.headers,
        body: config.data ? JSON.stringify(config.data) : null
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .catch(error => {
        console.error('AJAX error:', error);
        showNotification('An error occurred. Please try again.', 'error');
        throw error;
    });
}

/**
 * Debounce function
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export functions for use in other scripts
window.KiitSeva = {
    showNotification,
    ajax,
    debounce,
    throttle,
    formatDate,
    isValidEmail,
    isValidPhone,
    showLoading,
    hideLoading,
    openModal,
    closeModal
};