<?php
/**
 * ARC Form Controller
 * Handles JS-based form processing with field awareness
 * 
 * Place in: arc-blueprint/src/Forms/ArcFormController.php
 */

namespace ARC\Blueprint\Forms;

class ArcFormController {
    
    protected $form_id;
    protected $fields;
    protected $endpoint;
    protected $config;
    
    public function __construct($form_id, $fields = [], $endpoint = '') {
        $this->form_id = $form_id;
        $this->fields = $fields;
        $this->endpoint = $endpoint;
        $this->config = [];
    }
    
    /**
     * Set the API endpoint for form submission
     */
    public function setEndpoint($endpoint) {
        $this->endpoint = $endpoint;
        return $this;
    }
    
    /**
     * Add a field configuration
     */
    public function addField($name, $config = []) {
        $this->fields[$name] = array_merge([
            'name' => $name,
            'type' => 'text',
            'label' => ucfirst($name),
            'required' => false,
            'validation' => []
        ], $config);
        
        return $this;
    }
    
    /**
     * Set multiple fields at once
     */
    public function setFields($fields) {
        foreach ($fields as $field) {
            if (is_array($field) && isset($field['name'])) {
                $this->addField($field['name'], $field);
            }
        }
        return $this;
    }
    
    /**
     * Add configuration options
     */
    public function setConfig($key, $value) {
        $this->config[$key] = $value;
        return $this;
    }
    
    /**
     * Get the complete configuration array for JS
     */
    public function getJSConfig() {
        return [
            'formId' => $this->form_id,
            'fields' => array_values($this->fields),
            'endpoint' => $this->endpoint,
            'nonce' => wp_create_nonce('arc_form_' . $this->form_id),
            'config' => $this->config
        ];
    }
    
    /**
     * Render the inline JS controller
     */
    public function renderJS() {
        $config = $this->getJSConfig();
        ?>
        <script>
        (function() {
            class ArcFormController {
                constructor(config) {
                    this.formId = config.formId;
                    this.form = document.getElementById(this.formId);
                    this.fields = {};
                    this.endpoint = config.endpoint;
                    this.nonce = config.nonce;
                    this.config = config.config || {};
                    
                    // Map fields by name
                    config.fields.forEach(field => {
                        this.fields[field.name] = field;
                    });
                    
                    this.init();
                }
                
                init() {
                    if (!this.form) {
                        console.error('ARC Form not found:', this.formId);
                        return;
                    }
                    
                    this.form.addEventListener('submit', (e) => this.handleSubmit(e));
                }
                
                async handleSubmit(e) {
                    e.preventDefault();
                    
                    this.clearErrors();
                    
                    const formData = {};
                    let hasErrors = false;
                    
                    // Process each configured field
                    for (const [fieldName, fieldConfig] of Object.entries(this.fields)) {
                        const element = this.form.querySelector(`[name="${fieldName}"]`);
                        
                        if (!element) {
                            console.warn('Field element not found:', fieldName);
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
                    
                    // Submit to API
                    await this.submitToAPI(formData);
                }
                
                getFieldValue(element, fieldConfig) {
                    if (fieldConfig.type === 'checkbox') {
                        return element.checked;
                    }
                    
                    if (fieldConfig.type === 'number') {
                        return element.value ? Number(element.value) : null;
                    }
                    
                    return element.value.trim();
                }
                
                validateField(fieldConfig, value) {
                    // Required validation
                    if (fieldConfig.required && !value) {
                        return `${fieldConfig.label} is required`;
                    }
                    
                    // Skip other validations if empty and not required
                    if (!value) {
                        return null;
                    }
                    
                    // Min length
                    if (fieldConfig.validation?.min_length && value.length < fieldConfig.validation.min_length) {
                        return `${fieldConfig.label} must be at least ${fieldConfig.validation.min_length} characters`;
                    }
                    
                    // Max length
                    if (fieldConfig.validation?.max_length && value.length > fieldConfig.validation.max_length) {
                        return `${fieldConfig.label} must be no more than ${fieldConfig.validation.max_length} characters`;
                    }
                    
                    // Pattern validation
                    if (fieldConfig.validation?.pattern) {
                        const regex = new RegExp(fieldConfig.validation.pattern);
                        if (!regex.test(value)) {
                            return fieldConfig.validation.pattern_message || `${fieldConfig.label} format is invalid`;
                        }
                    }
                    
                    return null;
                }
                
                async submitToAPI(data) {
                    const submitButton = this.form.querySelector('button[type="submit"]');
                    const originalText = submitButton.textContent;
                    
                    try {
                        submitButton.disabled = true;
                        submitButton.textContent = this.config.submittingText || 'Submitting...';
                        
                        const response = await fetch(this.endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': this.nonce
                            },
                            body: JSON.stringify(data)
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok) {
                            this.handleSuccess(result);
                        } else {
                            this.handleError(result);
                        }
                        
                    } catch (error) {
                        console.error('Form submission error:', error);
                        this.showMessage('An error occurred. Please try again.', 'error');
                        
                    } finally {
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                }
                
                handleSuccess(result) {
                    this.showMessage(
                        this.config.successMessage || 'Saved successfully!',
                        'success'
                    );
                    
                    if (this.config.resetOnSuccess !== false) {
                        this.form.reset();
                    }
                    
                    // Trigger custom event
                    this.form.dispatchEvent(new CustomEvent('arc:form:success', {
                        detail: { result }
                    }));
                    
                    // Optional redirect
                    if (this.config.redirectOnSuccess) {
                        setTimeout(() => {
                            window.location.href = this.config.redirectOnSuccess;
                        }, 1500);
                    }
                }
                
                handleError(result) {
                    const message = result.message || 'Failed to save';
                    this.showMessage(message, 'error');
                    
                    // Trigger custom event
                    this.form.dispatchEvent(new CustomEvent('arc:form:error', {
                        detail: { result }
                    }));
                }
                
                showFieldError(fieldName, message) {
                    const errorElement = this.form.querySelector(`.arc-field-error[data-field="${fieldName}"]`);
                    if (errorElement) {
                        errorElement.textContent = message;
                        errorElement.style.display = 'block';
                    }
                }
                
                clearErrors() {
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
            }
            
            // Initialize with config from PHP
            const config = <?php echo json_encode($config); ?>;
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    new ArcFormController(config);
                });
            } else {
                new ArcFormController(config);
            }
        })();
        </script>
        <?php
    }
    
    /**
     * Render basic form styles
     */
    public static function renderStyles() {
        ?>
        <style>
        .arc-form-js .arc-field {
            margin-bottom: 1.5rem;
        }
        
        .arc-form-js .arc-field label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .arc-form-js .arc-field .required {
            color: #dc3545;
        }
        
        .arc-form-js .arc-field input[type="text"],
        .arc-form-js .arc-field input[type="email"],
        .arc-form-js .arc-field input[type="number"],
        .arc-form-js .arc-field textarea,
        .arc-form-js .arc-field select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .arc-form-js .arc-field textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .arc-form-js .arc-field-error {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .arc-form-js .arc-form-message {
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 4px;
            display: none;
        }
        
        .arc-form-js .arc-message-success {
            display: block;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .arc-form-js .arc-message-error {
            display: block;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        </style>
        <?php
    }
}