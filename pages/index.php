<?php
/**
 * Environment Middleware Addon - Main Page
 * 
 * @author Your Name
 * @package redaxo\env-middleware
 */

// Prevent direct access to this file
if (!defined('REDAXO')) {
    die('Direct access denied!');
}

// Get the addon instance
$addon = rex_addon::get('env-middleware');

// Redirect to the first subpage (settings)
rex_response::sendRedirect(rex_url::currentBackendPage(['page' => 'env-middleware/settings']));