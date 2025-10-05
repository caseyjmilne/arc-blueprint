<?php
/**
 * Plugin Name: ARC Blueprint
 * Description: Field definitions and form management for ARC Framework
 * Version: 1.0.0
 * Author: ARC Software Group
 * Requires PHP: 7.4
 */

namespace ARC\Blueprint;

if (!defined('ABSPATH')) {
    exit;
}

define('ARC_BLUEPRINT_VERSION', '1.0.0');
define('ARC_BLUEPRINT_PATH', plugin_dir_path(__FILE__));
define('ARC_BLUEPRINT_URL', plugin_dir_url(__FILE__));

class Blueprint
{
    private static $instance = null;
    private static $fields = [];
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct()
    {
        $this->registerAutoloader();
        $this->loadHelpers();
        $this->init();
    }

    private function registerAutoloader()
    {
        spl_autoload_register(function ($class) {
            // Only autoload classes in our namespace
            if (strpos($class, 'ARC\\Blueprint\\') !== 0) {
                return;
            }

            // Remove namespace prefix
            $class = str_replace('ARC\\Blueprint\\', '', $class);

            // Convert namespace separators to directory separators
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

            // Build the file path
            $file = ARC_BLUEPRINT_PATH . 'includes' . DIRECTORY_SEPARATOR . $class . '.php';

            // If the file exists, require it
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }

    private function loadHelpers()
    {
        require_once ARC_BLUEPRINT_PATH . 'helpers.php';
    }
    
    private function init()
    {
        add_action('arc_gateway_collection_registered', [$this, 'handleCollectionRegistration'], 10, 3);
        add_filter('arc_blueprint_get_fields', [$this, 'getFields'], 10, 1);

        // Initialize admin page
        new AdminPage();

        do_action('arc_blueprint_loaded');
    }
    
    public function handleCollectionRegistration($name, $model, $config)
    {
        if (isset($config['fields'])) {
            $this->registerFields($name, $config['fields']);
        }
    }
    
    public function registerFields($collection, $fields)
    {
        self::$fields[$collection] = $fields;
        do_action('arc_blueprint_fields_registered', $collection, $fields);
    }
    
    public function getFields($collection)
    {
        return self::$fields[$collection] ?? [];
    }
    
    public function getAllFields()
    {
        return self::$fields;
    }
    
    public function validate($collection, $data)
    {
        $fields = $this->getFields($collection);
        $errors = [];
        
        foreach ($fields as $field) {
            $key = $field->getKey();
            $value = $data[$key] ?? null;
            
            $fieldErrors = $field->validate($value);
            if (!empty($fieldErrors)) {
                $errors[$key] = $fieldErrors;
            }
        }
        
        return $errors;
    }
    
    public function generateMigration($collection, $tableName)
    {
        $fields = $this->getFields($collection);
        $columns = [];
        
        foreach ($fields as $field) {
            $columns[] = $field->getColumnDefinition();
        }
        
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$tableName} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            " . implode(",\n            ", $columns) . ",
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (id)
        ) {$charset_collate};";
        
        return $sql;
    }
    
    public function renderForm($collection, $data = [])
    {
        $fields = $this->getFields($collection);
        
        $html = '<div class="arc-blueprint-form">';
        
        foreach ($fields as $field) {
            $key = $field->getKey();
            $value = $data[$key] ?? null;
            $html .= $field->render($value);
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

Blueprint::getInstance();