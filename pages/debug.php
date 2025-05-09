<?php
/**
 * Environment Middleware Addon - Debug Page
 */

// Output debug information
echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">API Debug Information</h3>
    </div>
    <div class="panel-body">
        <pre>';
        
$debugInfo = Oldjazjef\EnvMiddleware\EnvMiddleware::getApiDebugInfo();
echo json_encode($debugInfo, JSON_PRETTY_PRINT);

echo '</pre>
    </div>
</div>';

// Test proxies
echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Proxy Entries</h3>
    </div>
    <div class="panel-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Proxy ID</th>
                    <th>OAuth Entry</th>
                    <th>SSL Verification</th>
                    <th>Test</th>
                </tr>
            </thead>
            <tbody>';

$proxyEntries = rex_addon::get('env-middleware')->getConfig('proxy_entries', []);
$oauthEntries = rex_addon::get('env-middleware')->getConfig('oauth_entries', []);

if (empty($proxyEntries)) {
    echo '<tr><td colspan="4">No proxy entries configured</td></tr>';
} else {
    foreach ($proxyEntries as $entry) {
        $proxyId = $entry['proxy_id'];
        $oauthId = $entry['oauth_entry_id'];
        $disableSsl = isset($entry['disable_ssl_verification']) && $entry['disable_ssl_verification'] ? 'Disabled' : 'Enabled';
        $sslClass = isset($entry['disable_ssl_verification']) && $entry['disable_ssl_verification'] ? 'text-danger' : 'text-success';
        
        // Find OAuth entry name
        $oauthName = 'N/A';
        foreach ($oauthEntries as $oauth) {
            if ($oauth['entry_id'] === $oauthId) {
                $oauthName = $oauth['entry_name'];
                break;
            }
        }
        
        // Generate test URL
        $testUrl = rex_url::frontend('index.php', [
            'rex-api-call' => 'proxy_request',
            'proxy-id' => $proxyId,
            'target' => 'https://httpbin.org/get'
        ]);
        
        echo '<tr>
            <td>' . htmlspecialchars($proxyId) . '</td>
            <td>' . htmlspecialchars($oauthName) . '</td>
            <td><span class="' . $sslClass . '">' . $disableSsl . '</span>' . (isset($entry['disable_ssl_verification']) && $entry['disable_ssl_verification'] ? ' (Warning: Security risk!)' : '') . '</td>
            <td><a href="' . $testUrl . '" target="_blank" class="btn btn-sm btn-primary">Test</a></td>
        </tr>';
    }
}

echo '</tbody>
        </table>
    </div>
</div>';
