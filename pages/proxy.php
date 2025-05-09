<?php
/**
 * Environment Middleware Addon - Proxy Configuration Page
 * 
 * @author Your Name
 * @package redaxo\env-middleware
 */

// Get the addon instance
$addon = rex_addon::get('env-middleware');

// Check if settings are being saved
if (rex_post('func', 'string') === 'save') {
    // Process Proxy entries
    $proxyEntries = [];
    if (isset($_POST['proxy_entries']) && is_array($_POST['proxy_entries'])) {
        foreach ($_POST['proxy_entries'] as $entry) {
            // Only save entries with a proxy ID and OAuth entry ID
            if (!empty($entry['proxy_id']) && !empty($entry['oauth_entry_id'])) {
                $proxyEntry = [
                    'proxy_id' => $entry['proxy_id'],
                    'oauth_entry_id' => $entry['oauth_entry_id'],
                    'disable_ssl_verification' => isset($entry['disable_ssl_verification']) ? true : false,
                ];
                
                $proxyEntries[] = $proxyEntry;
            }
        }
    }
    
    // Save the Proxy entries to addon configuration
    $addon->setConfig('proxy_entries', $proxyEntries);
    
    // Show success message
    echo rex_view::success($addon->i18n('settings_saved', 'Settings saved successfully!'));
}

// Get existing Proxy entries
$proxyEntries = $addon->getConfig('proxy_entries', []);

// Get OAuth entries for dropdown selection
$oauthEntries = $addon->getConfig('oauth_entries', []);

// Display the Proxy settings page content
$content = '<div class="rex-form">
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="func" value="save">
        
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">' . $addon->i18n('proxy_proxy_entries', 'Proxy Entries') . '</h3></div>
            <div class="panel-body">
                <p>' . $addon->i18n('proxy_description', 'Configure proxy settings for environment variables') . '</p>
                
                <div id="proxy-entries-container">';
                
                // Display existing Proxy entries or a blank one if none exist
                if (empty($proxyEntries)) {
                    $content .= getProxyEntryHtml(0, null, $oauthEntries);
                } else {
                    foreach ($proxyEntries as $index => $entry) {
                        $content .= getProxyEntryHtml($index, $entry, $oauthEntries);
                    }
                }
                
                $content .= '
                </div>
                
                <button type="button" class="btn btn-default" id="add-proxy-entry">
                    <i class="rex-icon rex-icon-add"></i> ' . $addon->i18n('proxy_add_proxy_entry', 'Add Proxy Entry') . '
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

// Helper function to generate a Proxy entry HTML
function getProxyEntryHtml($index, $entry = null, $oauthEntries = []) {
    $addon = rex_addon::get('env-middleware');
    
    $proxyId = $entry['proxy_id'] ?? '';
    $oauthEntryId = $entry['oauth_entry_id'] ?? '';
    $disableSslVerification = $entry['disable_ssl_verification'] ?? false;
    
    $html = '
    <div class="proxy-entry panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-10">
                    <div class="form-group">
                        <label>' . $addon->i18n('proxy_id', 'Proxy ID') . '</label>
                        <input type="text" class="form-control" name="proxy_entries[' . $index . '][proxy_id]" value="' . htmlspecialchars($proxyId) . '">
                        <p class="help-block">' . $addon->i18n('proxy_id_help', 'A unique identifier for this proxy entry') . '</p>
                    </div>
                    
                    <div class="form-group">
                        <label>' . $addon->i18n('proxy_oauth_entry_id', 'OAuth Entry') . '</label>
                        <select class="form-control" name="proxy_entries[' . $index . '][oauth_entry_id]">';
                        
                        // Add empty option
                        $html .= '<option value="">' . $addon->i18n('proxy_select_oauth_entry', 'Select OAuth Entry') . '</option>';
                        
                        // Add all available OAuth entries
                        foreach ($oauthEntries as $oauthEntry) {
                            if (!empty($oauthEntry['entry_id'])) {
                                $selected = ($oauthEntryId === $oauthEntry['entry_id']) ? ' selected' : '';
                                $html .= '<option value="' . htmlspecialchars($oauthEntry['entry_id']) . '"' . $selected . '>' . htmlspecialchars($oauthEntry['entry_id']) . '</option>';
                            }
                        }
                        
                        $html .= '</select>
                        <p class="help-block">' . $addon->i18n('proxy_oauth_entry_id_help', 'Select the OAuth entry to use for authentication') . '</p>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="proxy_entries[' . $index . '][disable_ssl_verification]" value="1" ' . ($disableSslVerification ? 'checked' : '') . '>
                                ' . $addon->i18n('disable_ssl_verification', 'Disable SSL Certificate Verification') . '
                            </label>
                        </div>
                        <p class="help-block">' . $addon->i18n('disable_ssl_verification_help', 'WARNING: Only enable this in development environments with self-signed certificates. This is a security risk in production.') . '</p>
                    </div>
                </div>
                <div class="col-sm-2 text-right">
                    <button type="button" class="btn btn-danger remove-proxy-entry">
                        <i class="rex-icon rex-icon-delete"></i> ' . $addon->i18n('proxy_remove_proxy_entry', 'Remove') . '
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
        // Add new Proxy entry            $("#add-proxy-entry").on("click", function() {
            var count = $("#proxy-entries-container .proxy-entry").length;
            var entryHtml = `
                <div class="proxy-entry panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-10">
                                <div class="form-group">
                                    <label>' . $addon->i18n('proxy_id', 'Proxy ID') . '</label>
                                    <input type="text" class="form-control" name="proxy_entries[${count}][proxy_id]" value="">
                                    <p class="help-block">' . $addon->i18n('proxy_id_help', 'A unique identifier for this proxy entry') . '</p>
                                </div>
                                
                                <div class="form-group">
                                    <label>' . $addon->i18n('proxy_oauth_entry_id', 'OAuth Entry') . '</label>
                                    <select class="form-control" name="proxy_entries[${count}][oauth_entry_id]">`;
                                    <p class="help-block">' . $addon->i18n('proxy_oauth_entry_id_help', 'Select the OAuth entry to use for authentication') . '</p>
                                </div>
                            </div>
                            <div class="col-sm-2 text-right">
                                <button type="button" class="btn btn-danger remove-proxy-entry">
                                    <i class="rex-icon rex-icon-delete"></i> ' . $addon->i18n('proxy_remove_proxy_entry', 'Remove') . '
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $("#proxy-entries-container").append(entryHtml);
        });
        
        // Remove Proxy entry
        $("#proxy-entries-container").on("click", ".remove-proxy-entry", function() {
            $(this).closest(".proxy-entry").remove();
        });
    });
</script>';

echo $content;