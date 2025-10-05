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
        ?>
        <div class="wrap">
            <h1>ARC Blueprint</h1>
        </div>
        <?php
    }
}
