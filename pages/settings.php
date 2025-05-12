<?php
/**
 * Environment Middleware Addon - Settings Page
 * 
 * @author Emanuel Mistretta
 * @package redaxo\env-middleware
 */

// Get the addon instance
$addon = rex_addon::get('env-middleware');

// Initialize variables
$error = '';
$success = '';
$debugMode = $addon->getConfig('debug_mode', false);

// Form processing
if (rex_post('save', 'boolean')) {
    // Save settings
    $newDebugMode = rex_post('debug_mode', 'boolean', false);
    
    $addon->setConfig('debug_mode', $newDebugMode);
    
    $debugMode = $newDebugMode;
    
    $success = $addon->i18n('settings_saved', 'Settings saved successfully!');
}

// Error and success messages
if (!empty($error)) {
    echo rex_view::error($error);
}
if (!empty($success)) {
    echo rex_view::success($success);
}

// Prepare the page content
$content = '
<form action="' . rex_url::currentBackendPage() . '" method="post">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">' . $addon->i18n('general_settings', 'General Settings') . '</h3>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="debug_mode" value="1" ' . ($debugMode ? 'checked' : '') . ' />
                        ' . $addon->i18n('debug_mode', 'Debug Mode') . '
                    </label>
                </div>
                <p class="help-block">' . $addon->i18n('debug_mode_help', 'When enabled, debug information will be output to the error log') . '</p>
            </div>
        </div>
    </div>
    
    <div class="panel-footer">
        <div class="rex-form-panel-footer">
            <div class="btn-toolbar justify-content-end">
                <button class="btn btn-save btn-primary" type="submit" name="save" value="1">' . $addon->i18n('save', 'Save') . '</button>
            </div>
        </div>
    </div>
</form>';

// Output the page content
echo $content;