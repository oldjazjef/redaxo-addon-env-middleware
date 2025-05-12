<?php
/**
 * Environment Middleware Addon
 *
 * @author Emanuel Mistretta
 * @package redaxo\env-middleware
 */

// Register the i18n files
if (rex::isBackend() && is_object(rex::getUser())) {
    rex_i18n::addDirectory(rex_path::addon('env-middleware', 'lang'));
}

// Include functions
require_once __DIR__ . '/functions/functions.php';

// Add JavaScript environment variables to frontend
if (!rex::isBackend()) {
    rex_extension::register('OUTPUT_FILTER', function(rex_extension_point $ep) {
        $addon = rex_addon::get('env-middleware');
        $jsVariableName = $addon->getConfig('js_variable_name', 'ENV');
        $activeEnvironment = $addon->getConfig('active_environment', '');
        $environments = $addon->getConfig('environments', []);
        $debugMode = $addon->getConfig('debug_mode', false);
        
        // Debug information
        if ($debugMode) {
            error_log('Env-Middleware: Active Environment: ' . $activeEnvironment);
            error_log('Env-Middleware: JS Variable Name: ' . $jsVariableName);
            error_log('Env-Middleware: Available Environments: ' . print_r($environments, true));
        }
        
        // Only inject if there's an active environment and we have environment data
        if (!empty($activeEnvironment) && isset($environments[$activeEnvironment])) {
            $envData = $environments[$activeEnvironment];
            
            // Format data as JavaScript object
            $jsOutput = '<script type="text/javascript">' . PHP_EOL;
            $jsOutput .= 'window.' . $jsVariableName . ' = ' . json_encode($envData, JSON_PRETTY_PRINT) . ';' . PHP_EOL;
            
            // Add debug console log if debug mode is enabled
            if ($debugMode) {
                $jsOutput .= 'console.log("Env-Middleware: ' . $jsVariableName . ' loaded", window.' . $jsVariableName . ');' . PHP_EOL;
            }
            
            $jsOutput .= '</script>' . PHP_EOL;
            
            // Insert before </head> or </body>, whichever comes first
            $content = $ep->getSubject();
            
            // Try to insert before </head>
            if (strpos($content, '</head>') !== false) {
                $content = str_replace('</head>', $jsOutput . '</head>', $content);
            } 
            // If </head> not found, try to insert before </body>
            elseif (strpos($content, '</body>') !== false) {
                $content = str_replace('</body>', $jsOutput . '</body>', $content);
            }
            // If neither tag found, append to the end
            else {
                $content .= $jsOutput;
            }
            
            $ep->setSubject($content);
        } elseif ($debugMode) {
            error_log('Env-Middleware: No environment data to inject. Active env: ' . $activeEnvironment);
        }
    }, rex_extension::LATE);
}