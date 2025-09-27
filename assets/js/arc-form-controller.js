/**
 * ArcFormController - Pure JavaScript
 * Handles form submission to ARC REST API with field-aware validation
 * 
 * Usage:
 * const form = new ArcFormController('my-form', formConfig);
 */

class ArcFormController {
    constructor(formId, config = {}) {
        this.formId = formId;
        this.form = document.getElementById(formId);
        this.fields = {};
        this.endpoint = config.endpoint || '';
        this.nonce = config.nonce || '';
        this.options = config.options || {};
        
        // Map fields by name for easy lookup
        if (config.fields) {
            config.fields.forEach(field => {
                this.fields[field.name] = field;
            });
        }
        
        this.init();
    }
    
    init() {
        if (!this.form) {
            console.error('ArcFormController: Form not found:', this.formId);
            return;
        }
        
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Optional: real-time validation
        if (this.options.validateOnBlur) {
            this.setupBlurValidation();
        }
    }
    
    setupBlurValidation() {
        Object.keys(this.fields).forEach(fieldName => {
            const element = this.form.querySelector(`[name="${fieldName}"]`);
            if (element) {
                element.addEventListener('blur', () => {
                    const value = this.getFieldValue(element, this.fields[fieldName]);
                    const error = this.validateField(this.fields[fieldName], value);
                    if (error) {
                        this.showFieldError(fieldName, error);
                    } else {
                        this.clearFieldError(fieldName);
                    }
                });
            }
        });
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        this.clearAllErrors();
        
        const formData = {};
        let hasErrors = false;
        
        // Process each configured field
        for (const [fieldName, fieldConfig] of Object.entries(this.fields)) {
            const element = this.form.querySelector(`[name="${fieldName}"]`);
            
            if (!element) {
                console.warn('ArcFormController: Field element not found:', fieldName);
                continue;
            }
            
            const value = this.getFieldValue(element, fieldConfig);
            
            // Validate
            const error = this.validateField(fieldConfig, value);
            if (error) {
                this.showFieldError(fieldName, error);
                hasErrors = true;
                continue;
            }
            
            formData[fieldName] = value;
        }
        
        if (hasErrors) {
            return;
        }
        
        await this.submitToAPI(formData);
    }
    
    getFieldValue(element, fieldConfig) {
        switch (fieldConfig.type) {
            case 'checkbox':
                return element.checked;
            case 'number':
                return element.value ? Number(element.value) : null;
            case 'select-multiple':
                return Array.from(element.selectedOptions).map(opt => opt.value);
            default:
                const value = element.value.trim();
                return value || null;
        }
    }
    
    validateField(fieldConfig, value) {
        // Required validation
        if (fieldConfig.required && !value) {
            return `${fieldConfig.label} is required`;
        }
        
        // Skip other validations if empty and not required
        if (!value && value !== 0) {
            return null;
        }
        
        const validation = fieldConfig.validation || {};
        
        // Min length (for strings)
        if (validation.min_length && value.length < validation.min_length) {
            return `${fieldConfig.label} must be at least ${validation.min_length} characters`;
        }
        
        // Max length
        if (validation.max_length && value.length > validation.max_length) {
            return `${fieldConfig.label} must be no more than ${validation.max_length} characters`;
        }
        
        // Min value (for numbers)
        if (validation.min !== undefined && value < validation.min) {
            return `${fieldConfig.label} must be at least ${validation.min}`;
        }
        
        // Max value
        if (validation.max !== undefined && value > validation.max) {
            return `${fieldConfig.label} must be no more than ${validation.max}`;
        }
        
        // Pattern validation
        if (validation.pattern) {
            const regex = new RegExp(validation.pattern);
            if (!regex.test(value)) {
                return validation.pattern_message || `${fieldConfig.label} format is invalid`;
            }
        }
        
        // Email validation
        if (fieldConfig.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                return `${fieldConfig.label} must be a valid email`;
            }
        }
        
        // URL validation
        if (fieldConfig.type === 'url' && value) {
            try {
                new URL(value);
            } catch {
                return `${fieldConfig.label} must be a valid URL`;
            }
        }
        
        return null;
    }
    
    async submitToAPI(data) {
        const submitButton = this.form.querySelector('button[type="submit"]');
        const originalText = submitButton?.textContent;
        
        try {
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = this.options.submittingText || 'Submitting...';
            }
            
            const headers = {
                'Content-Type': 'application/json'
            };
            
            if (this.nonce) {
                headers['X-WP-Nonce'] = this.nonce;
            }
            
            const response = await fetch(this.endpoint, {
                method: this.options.method || 'POST',
                headers: headers,
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                this.handleSuccess(result);
            } else {
                this.handleError(result);
            }
            
        } catch (error) {
            console.error('ArcFormController submission error:', error);
            this.showMessage('An error occurred. Please try again.', 'error');
            
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }
    }
    
    handleSuccess(result) {
        this.showMessage(
            this.options.successMessage || 'Saved successfully!',
            'success'
        );
        
        if (this.options.resetOnSuccess !== false) {
            this.form.reset();
        }
        
        // Dispatch custom event
        this.form.dispatchEvent(new CustomEvent('arc:form:success', {
            detail: { result },
            bubbles: true
        }));
        
        // Optional callback
        if (typeof this.options.onSuccess === 'function') {
            this.options.onSuccess(result);
        }
        
        // Optional redirect
        if (this.options.redirectOnSuccess) {
            setTimeout(() => {
                window.location.href = this.options.redirectOnSuccess;
            }, this.options.redirectDelay || 1500);
        }
    }
    
    handleError(result) {
        const message = result.message || 'Failed to save';
        this.showMessage(message, 'error');
        
        // Handle field-specific errors from API
        if (result.errors && typeof result.errors === 'object') {
            Object.entries(result.errors).forEach(([field, error]) => {
                this.showFieldError(field, error);
            });
        }
        
        // Dispatch custom event
        this.form.dispatchEvent(new CustomEvent('arc:form:error', {
            detail: { result },
            bubbles: true
        }));
        
        // Optional callback
        if (typeof this.options.onError === 'function') {
            this.options.onError(result);
        }
    }
    
    showFieldError(fieldName, message) {
        const errorElement = this.form.querySelector(`.arc-field-error[data-field="${fieldName}"]`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }
    
    clearFieldError(fieldName) {
        const errorElement = this.form.querySelector(`.arc-field-error[data-field="${fieldName}"]`);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
    }
    
    clearAllErrors() {
        this.form.querySelectorAll('.arc-field-error').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
        
        const messageContainer = this.form.querySelector('.arc-form-message');
        if (messageContainer) {
            messageContainer.textContent = '';
            messageContainer.className = 'arc-form-message';
        }
    }
    
    showMessage(message, type) {
        const messageContainer = this.form.querySelector('.arc-form-message');
        if (messageContainer) {
            messageContainer.textContent = message;
            messageContainer.className = `arc-form-message arc-message-${type}`;
        }
    }
    
    // Public API
    updateConfig(config) {
        if (config.fields) {
            this.fields = {};
            config.fields.forEach(field => {
                this.fields[field.name] = field;
            });
        }
        if (config.endpoint) this.endpoint = config.endpoint;
        if (config.nonce) this.nonce = config.nonce;
        if (config.options) this.options = { ...this.options, ...config.options };
    }
    
    destroy() {
        this.form.removeEventListener('submit', this.handleSubmit);
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ArcFormController;
}

// Make available globally
if (typeof window !== 'undefined') {
    window.ArcFormController = ArcFormController;
}