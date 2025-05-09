<?php
/**
 * Environment Middleware Addon - Functions
 * 
 * @author Your Name
 * @package redaxo\env-middleware
 */

if (!defined('REDAXO')) {
    die('Direct access denied!');
}

/**
 * Gets an environment variable with optional default value
 *
 * @param string $name The name of the environment variable
 * @param mixed $default Default value if the environment variable is not set
 * @return mixed The value of the environment variable or the default value
 */
function env_middleware_get($name, $default = null) {
    $value = getenv($name);
    if ($value === false) {
        return $default;
    }
    return $value;
}

/**
 * Checks if an environment variable exists
 *
 * @param string $name The name of the environment variable
 * @return bool True if the environment variable exists, false otherwise
 */
function env_middleware_exists($name) {
    return getenv($name) !== false;
}