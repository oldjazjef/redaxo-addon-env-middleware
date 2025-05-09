<?php
/**
 * Environment Middleware Addon - API Proxy Endpoint
 * 
 * @author Your Name
 * @package redaxo\env-middleware
 */

class rex_api_proxy_request extends rex_api_function
{
    protected $published = true;
    
    /**
     * Main execution method
     */
    public function execute()
    {
        // Set appropriate response headers
        header('Content-Type: application/json');
        
        // Get query parameters
        $proxyId = rex_request('proxy-id', 'string', '');
        $targetUrl = rex_request('target', 'string', '');
        
        // Validate required parameters
        if (empty($proxyId) || empty($targetUrl)) {
            return $this->sendError('Missing required parameters: proxy-id and target must be provided', 400);
        }
        
        // Load the addon configuration
        $addon = rex_addon::get('env-middleware');
        $proxyEntries = $addon->getConfig('proxy_entries', []);
        $oauthEntries = $addon->getConfig('oauth_entries', []);
        $debugMode = $addon->getConfig('debug_mode', false);
        
        // Debug log
        if ($debugMode) {
            $this->debugLog("Proxy request initiated: proxy-id=$proxyId, target=$targetUrl");
        }
        
        // Find the proxy entry by id
        $proxyEntry = null;
        foreach ($proxyEntries as $entry) {
            if ($entry['proxy_id'] === $proxyId) {
                $proxyEntry = $entry;
                break;
            }
        }
        
        if (!$proxyEntry) {
            return $this->sendError("Proxy ID not found: $proxyId", 404);
        }
        
        // Get proxy-specific SSL verification setting
        $disableSslVerification = isset($proxyEntry['disable_ssl_verification']) ? (bool)$proxyEntry['disable_ssl_verification'] : false;
        
        if ($debugMode && $disableSslVerification) {
            $this->debugLog("SSL verification is disabled for proxy: $proxyId");
        }
        
        // Get the associated OAuth entry
        $oauthEntryId = $proxyEntry['oauth_entry_id'];
        $oauthEntry = null;
        foreach ($oauthEntries as $entry) {
            if ($entry['entry_id'] === $oauthEntryId) {
                $oauthEntry = $entry;
                break;
            }
        }
        
        if (!$oauthEntry) {
            return $this->sendError("OAuth entry not found for proxy: $oauthEntryId", 404);
        }
        
        // Get or refresh the token
        $token = $this->getToken($oauthEntry, $debugMode, $disableSslVerification);
        
        if (!$token || isset($token['error'])) {
            $errorMessage = isset($token['error']) ? $token['error'] : 'Failed to retrieve token';
            return $this->sendError($errorMessage, 500);
        }
        
        // Forward the request to the target with the token
        $result = $this->forwardRequest($targetUrl, $token, $debugMode, $disableSslVerification);
        
        // Return the result
        echo json_encode($result);
        exit;
    }
    
    /**
     * Get or refresh the OAuth token
     * 
     * @param array $oauthEntry The OAuth entry configuration
     * @param bool $debugMode Whether debug mode is enabled
     * @param bool $disableSslVerification Whether to disable SSL verification
     * @return array The token data
     */
    private function getToken($oauthEntry, $debugMode = false, $disableSslVerification = false)
    {
        // Check if we have a stored token for this OAuth entry
        $tokenStorageKey = 'oauth_token_' . $oauthEntry['entry_id'];
        $token = rex_addon::get('env-middleware')->getConfig($tokenStorageKey, null);
        
        // Check if token exists and is still valid (not expired)
        if ($token && isset($token['expires_at']) && $token['expires_at'] > time()) {
            if ($debugMode) {
                $this->debugLog("Using existing token for {$oauthEntry['entry_id']}, expires at " . date('Y-m-d H:i:s', $token['expires_at']));
            }
            return $token;
        }
        
        // If token exists but is expired, try to refresh it
        if ($token && isset($token['refresh_token'])) {
            if ($debugMode) {
                $this->debugLog("Refreshing token for {$oauthEntry['entry_id']}");
            }
            
            $refreshedToken = $this->refreshToken($oauthEntry, $token['refresh_token'], $debugMode, $disableSslVerification);
            if ($refreshedToken && !isset($refreshedToken['error'])) {
                return $refreshedToken;
            }
            
            // If refresh fails, fall back to requesting a new token
            if ($debugMode) {
                $this->debugLog("Token refresh failed, requesting new token");
            }
        }
        
        // Request a new token
        return $this->requestNewToken($oauthEntry, $debugMode, $disableSslVerification);
    }
    
    /**
     * Request a new OAuth token
     * 
     * @param array $oauthEntry The OAuth entry configuration
     * @param bool $debugMode Whether debug mode is enabled
     * @param bool $disableSslVerification Whether to disable SSL verification
     * @return array The token data
     */
    private function requestNewToken($oauthEntry, $debugMode = false, $disableSslVerification = false)
    {
        $url = $oauthEntry['url'];
        $grantType = $oauthEntry['grant_type'];
        
        if ($debugMode) {
            $this->debugLog("Requesting new token from $url with grant type $grantType");
        }
        
        $headers = ['Content-Type: application/x-www-form-urlencoded'];
        $data = ['grant_type' => $grantType];
        
        // Handle client credentials grant type
        if ($grantType === 'client_credentials' && !empty($oauthEntry['client_id']) && !empty($oauthEntry['client_secret'])) {
            // Add Basic Auth header
            $auth = base64_encode($oauthEntry['client_id'] . ':' . $oauthEntry['client_secret']);
            $headers[] = 'Authorization: Basic ' . $auth;
        }
        
        // Make the request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Configure SSL verification
        $this->configureCurlSsl($ch, $disableSslVerification, $debugMode);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            if ($debugMode) {
                $this->debugLog("cURL error requesting token: $error");
            }
            return ['error' => "cURL error: $error"];
        }
        
        if ($httpCode < 200 || $httpCode >= 300) {
            if ($debugMode) {
                $this->debugLog("Token request failed with HTTP code $httpCode: $response");
            }
            return ['error' => "Token request failed with HTTP code $httpCode: $response"];
        }
        
        // Parse the response
        $tokenData = json_decode($response, true);
        if (!$tokenData) {
            if ($debugMode) {
                $this->debugLog("Failed to parse token response: $response");
            }
            return ['error' => "Failed to parse token response: $response"];
        }
        
        // Add expiration time if provided
        if (isset($tokenData['expires_in'])) {
            $tokenData['expires_at'] = time() + $tokenData['expires_in'];
        }
        
        // Store the token for future use
        $tokenStorageKey = 'oauth_token_' . $oauthEntry['entry_id'];
        rex_addon::get('env-middleware')->setConfig($tokenStorageKey, $tokenData);
        
        if ($debugMode) {
            $this->debugLog("New token obtained and stored for {$oauthEntry['entry_id']}");
        }
        
        return $tokenData;
    }
    
    /**
     * Refresh an existing OAuth token
     * 
     * @param array $oauthEntry The OAuth entry configuration
     * @param string $refreshToken The refresh token
     * @param bool $debugMode Whether debug mode is enabled
     * @param bool $disableSslVerification Whether to disable SSL verification
     * @return array The refreshed token data
     */
    private function refreshToken($oauthEntry, $refreshToken, $debugMode = false, $disableSslVerification = false)
    {
        $url = $oauthEntry['url'];
        
        if ($debugMode) {
            $this->debugLog("Refreshing token from $url");
        }
        
        $headers = ['Content-Type: application/x-www-form-urlencoded'];
        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ];
        
        // For client credentials, add Basic Auth
        if (!empty($oauthEntry['client_id']) && !empty($oauthEntry['client_secret'])) {
            $auth = base64_encode($oauthEntry['client_id'] . ':' . $oauthEntry['client_secret']);
            $headers[] = 'Authorization: Basic ' . $auth;
        }
        
        // Make the request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Configure SSL verification
        $this->configureCurlSsl($ch, $disableSslVerification, $debugMode);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            if ($debugMode) {
                $this->debugLog("cURL error refreshing token: $error");
            }
            return ['error' => "cURL error: $error"];
        }
        
        if ($httpCode < 200 || $httpCode >= 300) {
            if ($debugMode) {
                $this->debugLog("Token refresh failed with HTTP code $httpCode: $response");
            }
            return ['error' => "Token refresh failed with HTTP code $httpCode: $response"];
        }
        
        // Parse the response
        $tokenData = json_decode($response, true);
        if (!$tokenData) {
            if ($debugMode) {
                $this->debugLog("Failed to parse refresh token response: $response");
            }
            return ['error' => "Failed to parse refresh token response: $response"];
        }
        
        // Add expiration time if provided
        if (isset($tokenData['expires_in'])) {
            $tokenData['expires_at'] = time() + $tokenData['expires_in'];
        }
        
        // Store the refreshed token
        $tokenStorageKey = 'oauth_token_' . $oauthEntry['entry_id'];
        rex_addon::get('env-middleware')->setConfig($tokenStorageKey, $tokenData);
        
        if ($debugMode) {
            $this->debugLog("Token refreshed successfully for {$oauthEntry['entry_id']}");
        }
        
        return $tokenData;
    }
    
    /**
     * Forward the request to the target with the token
     * 
     * @param string $targetUrl The target URL
     * @param array $token The token data
     * @param bool $debugMode Whether debug mode is enabled
     * @param bool $disableSslVerification Whether to disable SSL verification
     * @return array The response data
     */
    private function forwardRequest($targetUrl, $token, $debugMode = false, $disableSslVerification = false)
    {
        if ($debugMode) {
            $this->debugLog("Forwarding request to $targetUrl");
        }
        
        // Get the access token
        $accessToken = $token['access_token'] ?? '';
        if (empty($accessToken)) {
            return ['error' => 'No access token available'];
        }
        
        // Get original request method and data
        $method = $_SERVER['REQUEST_METHOD'];
        $data = null;
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        // Get request headers to forward (except Host and Authorization which we'll set)
        $requestHeaders = [];
        foreach (getallheaders() as $name => $value) {
            if (strtolower($name) != 'host' && strtolower($name) != 'authorization') {
                $requestHeaders[] = "$name: $value";
            }
        }
        
        // Add the Authorization header with the token
        $requestHeaders[] = "Authorization: Bearer $accessToken";
        
        // For POST, PUT, PATCH methods, get the request body
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $data = file_get_contents('php://input');
            
            // If no content type is set, default to JSON for API requests
            if (empty($contentType)) {
                $contentType = 'application/json';
                $requestHeaders[] = "Content-Type: $contentType";
            }
        }
        
        // Initialize cURL
        $ch = curl_init($targetUrl);
        
        // Set request method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        // If there's data, send it
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        
        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Configure SSL verification
        $this->configureCurlSsl($ch, $disableSslVerification, $debugMode);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            if ($debugMode) {
                $this->debugLog("cURL error forwarding request: $error");
            }
            return ['error' => "cURL error: $error"];
        }
        
        // Try to parse JSON response
        if (strpos($contentType, 'application/json') !== false) {
            $parsed = json_decode($response, true);
            if ($parsed !== null) {
                return $parsed;
            }
        }
        
        // If not JSON or parsing failed, return raw response with metadata
        return [
            'status_code' => $httpCode,
            'content_type' => $contentType,
            'raw_response' => $response
        ];
    }
    
    /**
     * Send an error response
     * 
     * @param string $message The error message
     * @param int $statusCode The HTTP status code
     */
    private function sendError($message, $statusCode = 500)
    {
        http_response_code($statusCode);
        echo json_encode(['error' => $message]);
        exit;
    }
    
    /**
     * Log debug messages
     * 
     * @param string $message The debug message
     */
    private function debugLog($message)
    {
        error_log('Env-Middleware API: ' . $message);
    }
    
    /**
     * Configure a cURL handle with common settings and SSL verification
     *
     * @param resource $ch The cURL handle
     * @param bool $disableSslVerification Whether to disable SSL verification
     * @param bool $debugMode Whether debug mode is enabled
     */
    private function configureCurlSsl($ch, $disableSslVerification = false, $debugMode = false)
    {
        if ($disableSslVerification) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            
            if ($debugMode) {
                $this->debugLog("SSL verification disabled for request");
            }
        } else {
            if ($debugMode) {
                $this->debugLog("SSL verification enabled for request");
            }
        }
    }
}
