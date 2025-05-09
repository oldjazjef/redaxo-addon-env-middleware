<?php
/**
 * Environment Middleware Addon
 * 
 * @author Your Name
 * @package redaxo\env-middleware
 */

// Prevent direct access to this file
if (!defined('REDAXO')) {
    die('Direct access denied!');
}

// Register the autoloader for the addon's classes
rex_psr4_autoload::register('Oldjazjef\\EnvMiddleware\\', __DIR__ . '/lib');

// Load addon functions
require_once __DIR__ . '/functions/functions.php';