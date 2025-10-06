<?php

namespace ARC\Blueprint\Forms;

class Render
{
    private static $forms_registered = false;
    private static $scripts_enqueued = false;

    /**
     * Render a Blueprint form trigger element
     */
    public static function form($schema, $record_id = null, $attributes = [])
    {
        // Flag that we need to enqueue scripts
        self::$forms_registered = true;
        
        // Enqueue immediately if we haven't yet
        self::enqueue_scripts();

        // Build data attributes
        $data_attrs = [
            'data-blueprint-form' => '',
            'data-schema' => esc_attr($schema),
        ];

        if ($record_id !== null) {
            $data_attrs['data-record-id'] = esc_attr($record_id);
        }

        // Merge with custom attributes
        $all_attrs = array_merge($attributes, $data_attrs);

        // Build attribute string
        $attr_string = '';
        foreach ($all_attrs as $key => $value) {
            if ($value === '') {
                $attr_string .= ' ' . $key;
            } else {
                $attr_string .= sprintf(' %s="%s"', $key, $value);
            }
        }

        echo sprintf('<div%s></div>', $attr_string);
    }

    /**
     * Initialize the form renderer
     */
    public static function init()
    {
        // Hook into footer to ensure scripts are loaded even if enqueued late
        add_action('wp_footer', [__CLASS__, 'ensure_scripts_loaded'], 1);
        add_action('admin_footer', [__CLASS__, 'ensure_scripts_loaded'], 1);
    }

    /**
     * Ensure scripts are loaded in footer if forms were registered
     */
    public static function ensure_scripts_loaded()
    {
        if (self::$forms_registered && !self::$scripts_enqueued) {
            self::enqueue_scripts();
        }
    }

    /**
     * Enqueue the form scripts and styles
     */
    private static function enqueue_scripts()
    {
        if (self::$scripts_enqueued) {
            return;
        }

        $asset_file = ARC_BLUEPRINT_PATH . 'apps/blueprint-forms/build/index.asset.php';

        if (!file_exists($asset_file)) {
            return;
        }

        $asset = require $asset_file;
        $build_url = ARC_BLUEPRINT_URL . 'apps/blueprint-forms/build/';

        wp_enqueue_script(
            'arc-blueprint-forms',
            $build_url . 'index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_localize_script('arc-blueprint-forms', 'wpApiSettings', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);

        wp_enqueue_style(
            'arc-blueprint-forms',
            $build_url . 'index.css',
            [],
            $asset['version']
        );

        self::$scripts_enqueued = true;
    }
}