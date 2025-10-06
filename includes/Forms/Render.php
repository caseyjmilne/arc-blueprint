<?php

namespace ARC\Blueprint\Forms;

class Render
{
    private static $forms_registered = false;

    /**
     * Render a Blueprint form trigger element
     * 
     * @param string $schema The schema key (e.g., 'ticket')
     * @param int|null $record_id Optional record ID for edit mode
     * @param array $attributes Additional HTML attributes
     * @return void
     */
    public static function form($schema, $record_id = null, $attributes = [])
    {
        // Flag that we need to enqueue scripts
        self::$forms_registered = true;

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
     * Check if forms have been registered on this page
     */
    public static function has_forms()
    {
        return self::$forms_registered;
    }

    /**
     * Initialize the form renderer hooks
     */
    public static function init()
    {
        // Enqueue on both frontend and admin
        add_action('wp_enqueue_scripts', [__CLASS__, 'maybe_enqueue_scripts'], 999);
        add_action('admin_enqueue_scripts', [__CLASS__, 'maybe_enqueue_scripts'], 999);
    }

    /**
     * Conditionally enqueue scripts if forms are present
     */
    public static function maybe_enqueue_scripts()
    {
        // Only enqueue if forms were actually rendered
        if (!self::$forms_registered) {
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
    }
}