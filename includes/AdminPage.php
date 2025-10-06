<?php

namespace ARC\Blueprint;

use ARC\Blueprint\Forms\Render;

class AdminPage
{
    /**
     * Initialize the admin page
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
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
            <?php Render::form('ticket'); ?>

            <hr style="margin: 40px 0;">

            <h3>Edit Mode: "ticket" (Record ID: 2)</h3>
            <?php Render::form('ticket', 2); ?>
        </div>
        <?php
    }
}