<?php
/**
 * Environment Middleware Addon - OAuth Configuration Page
 * 
 * @author Emanuel Mistretta
 * @package redaxo\env-middleware
 */

// Get the addon instance
$addon = rex_addon::get('env-middleware');

// Check if settings are being saved
if (rex_post('func', 'string') === 'save') {
    // Process OAuth entries
    $oauthEntries = [];
    if (isset($_POST['oauth_entries']) && is_array($_POST['oauth_entries'])) {
        foreach ($_POST['oauth_entries'] as $entry) {
            // Only save entries with an OAuth URL and entry ID
            if (!empty($entry['url']) && !empty($entry['entry_id'])) {
                $oauthEntry = [
                    'entry_id' => $entry['entry_id'],
                    'url' => $entry['url'],
                    'grant_type' => $entry['grant_type'],
                ];
                
                // Add client credentials if the grant type is client_credentials
                if ($entry['grant_type'] === 'client_credentials') {
                    $oauthEntry['client_id'] = $entry['client_id'] ?? '';
                    $oauthEntry['client_secret'] = $entry['client_secret'] ?? '';
                }
                
                $oauthEntries[] = $oauthEntry;
            }
        }
    }
    
    // Save the OAuth entries to addon configuration
    $addon->setConfig('oauth_entries', $oauthEntries);
    
    // Show success message
    echo rex_view::success($addon->i18n('settings_saved', 'Settings saved successfully!'));
}

// Get existing OAuth entries
$oauthEntries = $addon->getConfig('oauth_entries', []);

// Display the OAuth settings page content
$content = '<div class="rex-form">
    <!-- Hidden dummy form to trick Chrome\'s password manager -->
    <div style="display:none">
        <input type="text" id="username" autocomplete="username">
        <input type="password" id="password" autocomplete="current-password">
    </div>
    <style>
        .client-secret-field {
            -webkit-text-security: disc;
            text-security: disc;
            -moz-text-security: disc;
            -ms-text-security: disc;
        }
    </style>
    <form action="' . rex_url::currentBackendPage() . '" method="post" autocomplete="off">
        <input type="hidden" name="func" value="save">
        
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">' . $addon->i18n('oauth_oauth_entries', 'OAuth Entries') . '</h3></div>
            <div class="panel-body">
                <p>' . $addon->i18n('oauth_description', 'Configure OAuth settings for environment variables') . '</p>
                
                <div id="oauth-entries-container">';
                
                // Display existing OAuth entries or a blank one if none exist
                if (empty($oauthEntries)) {
                    $content .= getOAuthEntryHtml(0);
                } else {
                    foreach ($oauthEntries as $index => $entry) {
                        $content .= getOAuthEntryHtml($index, $entry);
                    }
                }
                
                $content .= '
                </div>
                
                <button type="button" class="btn btn-default" id="add-oauth-entry">
                    <i class="rex-icon rex-icon-add"></i> ' . $addon->i18n('oauth_add_oauth_entry', 'Add OAuth Entry') . '
                </button>
            </div>
            <div class="panel-footer">
                <div class="rex-form-panel-footer">
                    <div class="btn-toolbar justify-content-end">
                        <button type="submit" class="btn btn-save btn-primary">' . $addon->i18n('save', 'Save') . '</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>';

// Helper function to generate an OAuth entry HTML
function getOAuthEntryHtml($index, $entry = null) {
    $addon = rex_addon::get('env-middleware');
    
    $entryId = $entry['entry_id'] ?? '';
    $url = $entry['url'] ?? '';
    $grantType = $entry['grant_type'] ?? 'client_credentials';
    $clientId = $entry['client_id'] ?? '';
    $clientSecret = $entry['client_secret'] ?? '';
    
    $html = '
    <div class="oauth-entry panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-10">
                    <div class="form-group">
                        <label>' . $addon->i18n('entry_id', 'Entry ID') . '</label>
                        <input type="text" class="form-control" name="oauth_entries[' . $index . '][entry_id]" value="' . htmlspecialchars($entryId) . '">
                        <p class="help-block">' . $addon->i18n('entry_id_help', 'A unique identifier for this OAuth entry') . '</p>
                    </div>
                    
                    <div class="form-group">
                        <label>' . $addon->i18n('oauth_oauth_url', 'OAuth URL') . '</label>
                        <input type="text" class="form-control" name="oauth_entries[' . $index . '][url]" value="' . htmlspecialchars($url) . '">
                    </div>
                    
                    <div class="form-group">
                        <label>' . $addon->i18n('oauth_oauth_grant_type', 'Grant Type') . '</label>
                        <select class="form-control oauth-grant-type" name="oauth_entries[' . $index . '][grant_type]">
                            <option value="client_credentials"' . ($grantType === 'client_credentials' ? ' selected' : '') . '>' . $addon->i18n('oauth_client_credentials', 'Client Credentials') . '</option>
                            <option value="password"' . ($grantType === 'password' ? ' selected' : '') . '>' . $addon->i18n('oauth_password', 'Password') . '</option>
                            <option value="authorization_code"' . ($grantType === 'authorization_code' ? ' selected' : '') . '>' . $addon->i18n('oauth_authorization_code', 'Authorization Code') . '</option>
                        </select>
                    </div>
                    
                    <div class="client-credentials-fields" ' . ($grantType !== 'client_credentials' ? 'style="display:none;"' : '') . '>
                        <div class="form-group">
                            <label>' . $addon->i18n('oauth_oauth_client_id', 'Client ID') . '</label>
                            <input type="text" class="form-control" name="oauth_entries[' . $index . '][client_id]" value="' . htmlspecialchars($clientId) . '">
                        </div>
                        
                        <div class="form-group">
                            <label>' . $addon->i18n('oauth_oauth_client_secret', 'Client Secret') . '</label>
                            <input type="text" class="form-control client-secret-field" name="oauth_entries[' . $index . '][client_secret]" value="' . htmlspecialchars($clientSecret) . '" autocomplete="chrome-off" data-lpignore="true" data-1password-ignore="true">
                        </div>
                    </div>
                </div>
                <div class="col-sm-2 text-right">
                    <button type="button" class="btn btn-danger remove-oauth-entry">
                        <i class="rex-icon rex-icon-delete"></i> ' . $addon->i18n('oauth_remove_oauth_entry', 'Remove') . '
                    </button>
                </div>
            </div>
        </div>
    </div>';
    
    return $html;
}

// JavaScript for dynamic form handling
$content .= '
<script type="text/javascript">
    $(document).ready(function() {
        // Add new OAuth entry
        $("#add-oauth-entry").on("click", function() {
            var count = $("#oauth-entries-container .oauth-entry").length;
            var entryHtml = `
                <div class="oauth-entry panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-10">
                                <div class="form-group">
                                    <label>' . $addon->i18n('entry_id', 'Entry ID') . '</label>
                                    <input type="text" class="form-control" name="oauth_entries[${count}][entry_id]" value="">
                                    <p class="help-block">' . $addon->i18n('entry_id_help', 'A unique identifier for this OAuth entry') . '</p>
                                </div>
                                
                                <div class="form-group">
                                    <label>' . $addon->i18n('oauth_oauth_url', 'OAuth URL') . '</label>
                                    <input type="text" class="form-control" name="oauth_entries[${count}][url]" value="">
                                </div>
                                
                                <div class="form-group">
                                    <label>' . $addon->i18n('oauth_oauth_grant_type', 'Grant Type') . '</label>
                                    <select class="form-control oauth-grant-type" name="oauth_entries[${count}][grant_type]">
                                        <option value="client_credentials">' . $addon->i18n('oauth_client_credentials', 'Client Credentials') . '</option>
                                        <option value="password">' . $addon->i18n('oauth_password', 'Password') . '</option>
                                        <option value="authorization_code">' . $addon->i18n('oauth_authorization_code', 'Authorization Code') . '</option>
                                    </select>
                                </div>
                                
                                <div class="client-credentials-fields">
                                    <div class="form-group">
                                        <label>' . $addon->i18n('oauth_oauth_client_id', 'Client ID') . '</label>
                                        <input type="text" class="form-control" name="oauth_entries[${count}][client_id]" value="">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>' . $addon->i18n('oauth_oauth_client_secret', 'Client Secret') . '</label>
                                        <input type="text" class="form-control client-secret-field" name="oauth_entries[${count}][client_secret]" value="" autocomplete="chrome-off" data-lpignore="true" data-1password-ignore="true">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2 text-right">
                                <button type="button" class="btn btn-danger remove-oauth-entry">
                                    <i class="rex-icon rex-icon-delete"></i> ' . $addon->i18n('oauth_remove_oauth_entry', 'Remove') . '
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $("#oauth-entries-container").append(entryHtml);
            toggleClientCredentialsFields();
        });
        
        // Remove OAuth entry
        $("#oauth-entries-container").on("click", ".remove-oauth-entry", function() {
            $(this).closest(".oauth-entry").remove();
        });
        
        // Toggle client credentials fields based on grant type
        $("#oauth-entries-container").on("change", ".oauth-grant-type", function() {
            toggleClientCredentialsFields();
        });
        
        // Initial toggle of client credentials fields
        toggleClientCredentialsFields();
        
        // Helper function to toggle client credentials fields
        function toggleClientCredentialsFields() {
            $(".oauth-grant-type").each(function() {
                var grantType = $(this).val();
                var entryContainer = $(this).closest(".oauth-entry");
                var clientCredentialsFields = entryContainer.find(".client-credentials-fields");
                
                if (grantType === "client_credentials") {
                    clientCredentialsFields.show();
                } else {
                    clientCredentialsFields.hide();
                }
            });
        }
        
        // Setup custom password masking for client secret fields
        function setupClientSecretFields() {
            $(".client-secret-field").each(function() {
                $(this).css({
                    "-webkit-text-security": "disc",
                    "text-security": "disc"
                });
            });
        }
        
        // Initialize the client secret fields
        setupClientSecretFields();
        
        // Watch for dynamically added client secret fields
        $("#oauth-entries-container").on("DOMNodeInserted", function() {
            setupClientSecretFields();
        });
    });
</script>';

echo $content;