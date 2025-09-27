<?php
/**
 * ARC Form Helper
 * Enqueues the pure JS form controller and localizes config
 * 
 * Place in: arc-blueprint/src/Forms/FormHelper.php
 */

namespace ARC\Blueprint\Forms;

class FormHelper {
    
    /**
     * Enqueue the ArcFormController script
     */
    public static function enqueueFormController() {
        wp_enqueue_script(
            'arc-form-controller',
            ARC_BLUEPRINT_URL . 'assets/js/arc-form-controller.js',
            [],
            ARC_BLUEPRINT_VERSION,
            true
        );
    }
    
    /**
     * Localize form configuration for a specific form
     * 
     * @param string $handle Script handle (defaults to 'arc-form-controller')
     * @param string $object_name JS object name (e.g., 'arcDocFormConfig')
     * @param array $config Form configuration
     */
    public static function localizeFormConfig($handle, $object_name, $config) {
        $default_config = [
            'formId' => '',
            'fields' => [],
            'endpoint' => '',
            'nonce' => '',
            'options' => []
        ];
        
        $config = array_merge($default_config, $config);
        
        wp_localize_script($handle, $object_name, $config);
    }
    
    /**
     * Build form config from collection fields
     * 
     * @param string $collection Collection name (e.g., 'docs')
     * @param string $form_id HTML form ID
     * @param array $options Additional options
     * @return array Form configuration
     */
    public static function buildFormConfig($collection, $form_id, $options = []) {
        $fields = [];
        
        // Get fields from Blueprint if available
        if (function_exists('arc_get_fields')) {
            $blueprint_fields = arc_get_fields($collection);
            
            foreach ($blueprint_fields as $field) {
                $fields[] = [
                    'name' => $field->getKey(),
                    'type' => $field->getType(),
                    'label' => $field->getLabel(),
                    'required' => $field->isRequired(),
                    'validation' => $field->getValidation() ?? []
                ];
            }
        }
        
        return [
            'formId' => $form_id,
            'fields' => $fields,
            'endpoint' => rest_url("arc-gateway/v1/{$collection}"),
            'nonce' => wp_create_nonce('wp_rest'),
            'options' => array_merge([
                'successMessage' => 'Saved successfully!',
                'submittingText' => 'Saving...',
                'resetOnSuccess' => true
            ], $options)
        ];
    }
    
    /**
     * Render form styles (can be called once in footer)
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
        .arc-form-js .arc-field input[type="url"],
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

    /**
     * Render a complete form with one function call
     * 
     * @param string $mode 'create' or 'edit'
     * @param string $collection Collection name (e.g., 'docs')
     * @param array $options Configuration options
     * @return void
     */
    public static function renderForm($mode, $collection, $options = []) {
        // Get fields from Blueprint
        $fields = arc_get_fields($collection);
        
        if (empty($fields)) {
            echo '<div class="container"><p>No fields defined for ' . esc_html($collection) . ' collection.</p></div>';
            return;
        }
        
        // Build field configs for JS
        $field_configs = [];
        foreach ($fields as $field) {
            $field_configs[] = [
                'name' => $field->getKey(),
                'type' => $field->getType(),
                'label' => $field->getLabel(),
                'required' => $field->isRequired(),
                'validation' => $field->getValidation() ?? []
            ];
        }
        
        // Generate form ID
        $form_id = $options['formId'] ?? 'arc-' . $collection . '-form';
        $config_var = $options['configVar'] ?? 'arc' . ucfirst($collection) . 'FormConfig';
        
        // Enqueue JS controller
        self::enqueueFormController();
        
        // Determine HTTP method and endpoint
        $method = $mode === 'edit' ? 'PUT' : 'POST';
        $endpoint = $options['endpoint'] ?? home_url("/wp-json/arc-gateway/v1/{$collection}");
        
        // Build form config
        $form_config = [
            'formId' => $form_id,
            'fields' => $field_configs,
            'endpoint' => $endpoint,
            'nonce' => wp_create_nonce('wp_rest'),
            'options' => array_merge([
                'method' => $method,
                'successMessage' => ucfirst($mode) . ' successful!',
                'submittingText' => ucfirst($mode) . 'ing...',
                'resetOnSuccess' => $mode === 'create'
            ], $options['jsOptions'] ?? [])
        ];
        
        // Localize config
        self::localizeFormConfig('arc-form-controller', $config_var, $form_config);
        
        // Render styles
        self::renderStyles();
        
        // Get existing data if edit mode
        $data = null;
        if ($mode === 'edit' && isset($options['data'])) {
            $data = $options['data'];
        }
        
        // Render form HTML
        $title = $options['title'] ?? ucfirst($mode) . ' ' . ucfirst($collection);
        $submit_text = $options['submitText'] ?? ucfirst($mode);
        
        ?>
        <div class="arc-form-container">
            <h1><?php echo esc_html($title); ?></h1>
            
            <form id="<?php echo esc_attr($form_id); ?>" class="arc-form-js">
                <?php
                // Render each field
                foreach ($fields as $field) {
                    $key = $field->getKey();
                    $value = $data ? ($data->$key ?? null) : null;
                    echo $field->render($value);
                }
                ?>

                <button type="submit" class="arc-submit"><?php echo esc_html($submit_text); ?></button>
                <div class="arc-form-message"></div>
            </form>
        </div>
        
        <script>
        // Initialize form controller after footer
        (function() {
            function initForm() {
                if (typeof ArcFormController === 'undefined') {
                    console.error('ArcFormController not loaded');
                    return;
                }
                if (typeof <?php echo $config_var; ?> === 'undefined') {
                    console.error('Form config not found');
                    return;
                }
                new ArcFormController(<?php echo $config_var; ?>.formId, <?php echo $config_var; ?>);
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initForm);
            } else {
                initForm();
            }
        })();
        </script>
        <?php
    }

}