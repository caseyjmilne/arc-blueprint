<?php

namespace ARC\Blueprint;

class AdminPage
{
    /**
     * Initialize the admin page
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    /**
     * Add menu page to WordPress admin
     */
    public function addMenuPage()
    {
        add_menu_page(
            'ARC Blueprint',           // Page title
            'ARC Blueprint',           // Menu title
            'manage_options',          // Capability
            'arc-blueprint',           // Menu slug
            [$this, 'renderPage'],     // Callback function
            'dashicons-admin-generic', // Icon
            30                         // Position
        );
    }

    /**
     * Enqueue scripts and styles for the admin page
     */
    public function enqueueScripts($hook)
    {
        // Only load on our admin page
        if ($hook !== 'toplevel_page_arc-blueprint') {
            return;
        }

        $asset_file = ARC_BLUEPRINT_PATH . 'apps/blueprint-forms/build/index.asset.php';

        if (!file_exists($asset_file)) {
            return;
        }

        $asset = require $asset_file;
        $build_url = ARC_BLUEPRINT_URL . 'apps/blueprint-forms/build/';

        // Enqueue our React app
        wp_enqueue_script(
            'arc-blueprint-forms',
            $build_url . 'index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        // Localize script with WordPress REST API settings
        wp_localize_script('arc-blueprint-forms', 'wpApiSettings', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);

        // Enqueue CSS
        wp_enqueue_style(
            'arc-blueprint-forms',
            $build_url . 'index.css',
            [],
            $asset['version']
        );
    }

    /**
     * Render the admin page
     */
    public function renderPage()
    {
        $schemas = SchemaRegistry::all();
        ?>
        <div class="wrap">
            <h1>ARC Blueprint</h1>

            <h2>Registered Schemas</h2>
            <ul>
                <?php foreach ($schemas as $key => $schemaClass): ?>
                    <li><strong><?php echo esc_html($key); ?>:</strong> <?php echo esc_html($schemaClass); ?></li>
                <?php endforeach; ?>
            </ul>

            <h2>Test Forms</h2>

            <h3>Create Mode: "ticket"</h3>
            <div data-blueprint-form data-schema="ticket"></div>

            <hr style="margin: 40px 0;">

            <h3>Edit Mode: "ticket" (Record ID: 2)</h3>
            <div data-blueprint-form data-schema="ticket" data-record-id="2"></div>
        </div>
        <?php
    }
}
