<?php
/**
 * Environment Middleware Addon - Functions
 * 
 * @author Your Name
 * @package redaxo\env-middleware
 */

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

/**
 * Gets an OAuth token by entry ID
 *
 * @param string $entryId The ID of the OAuth entry
 * @return array|null The OAuth token data or null if not found/error occurred
 */
function env_middleware_get_oauth_token($entryId) {
    $addon = rex_addon::get('env-middleware');
    $oauthEntries = $addon->getConfig('oauth_entries', []);
    $debugMode = $addon->getConfig('debug_mode', false);
    
    // Find the OAuth entry with the given ID
    $entry = null;
    foreach ($oauthEntries as $oauthEntry) {
        if (isset($oauthEntry['entry_id']) && $oauthEntry['entry_id'] === $entryId) {
            $entry = $oauthEntry;
            break;
        }
    }
    
    if (!$entry) {
        if ($debugMode) {
            error_log("Env-Middleware: OAuth entry with ID '{$entryId}' not found");
        }
        return null;
    }
    
    // Get token based on grant type
    switch ($entry['grant_type']) {
        case 'client_credentials':
            return env_middleware_get_token_client_credentials($entry);
        case 'password':
            // Password grant implementation would go here
            return null;
        case 'authorization_code':
            // Authorization code grant implementation would go here
            return null;
        default:
            if ($debugMode) {
                error_log("Env-Middleware: Unsupported grant type '{$entry['grant_type']}'");
            }
            return null;
    }
}

/**
 * Gets an OAuth token using client credentials grant
 *
 * @param array $entry The OAuth entry configuration
 * @return array|null The OAuth token data or null if error occurred
 */
function env_middleware_get_token_client_credentials($entry) {
    $addon = rex_addon::get('env-middleware');
    $debugMode = $addon->getConfig('debug_mode', false);
    
    if (empty($entry['url']) || empty($entry['client_id']) || empty($entry['client_secret'])) {
        if ($debugMode) {
            error_log("Env-Middleware: Missing required fields for client credentials grant");
        }
        return null;
    }
    
    // Create a new cURL resource
    $ch = curl_init();
    
    // Setup the cURL request
    curl_setopt($ch, CURLOPT_URL, $entry['url']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . base64_encode($entry['client_id'] . ':' . $entry['client_secret'])
    ]);
    
    // Execute the cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for errors
    if (curl_errno($ch)) {
        if ($debugMode) {
            error_log("Env-Middleware: cURL error: " . curl_error($ch));
        }
        curl_close($ch);
        return null;
    }
    
    // Close the cURL resource
    curl_close($ch);
    
    // Process the response
    if ($httpCode >= 200 && $httpCode < 300) {
        $tokenData = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $tokenData;
        } else {
            if ($debugMode) {
                error_log("Env-Middleware: JSON decode error: " . json_last_error_msg());
            }
            return null;
        }
    } else {
        if ($debugMode) {
            error_log("Env-Middleware: HTTP error: {$httpCode}, Response: {$response}");
        }
        return null;
    }
}