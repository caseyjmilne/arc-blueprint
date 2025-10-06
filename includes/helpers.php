<?php
/**
 * ARC Blueprint - Global Helper Functions
 * These functions are in the global namespace for easy access
 */

if (!defined('ABSPATH')) {
    exit;
}

function arc_blueprint()
{
    return \ARC\Blueprint\Blueprint::getInstance();
}

function arc_get_fields($collection)
{
    return \ARC\Blueprint\Blueprint::getInstance()->getFields($collection);
}

function arc_validate_fields($collection, $data)
{
    return \ARC\Blueprint\Blueprint::getInstance()->validate($collection, $data);
}

function arc_render_form($collection, $data = [])
{
    return \ARC\Blueprint\Blueprint::getInstance()->renderForm($collection, $data);
}