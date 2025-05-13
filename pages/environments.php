<?php

/**
 * Environment Middleware Addon - Environments Page
 * 
 * @author Emanuel Mistretta
 * @package redaxo\env-middleware
 */

// Get the addon instance
$addon = rex_addon::get('env-middleware');

$user = rex::getUser();
$canSelect = $user->isAdmin() || $user->hasPerm('env-middleware[environments-edit]') || $user->hasPerm('env-middleware[environments-select]');
$canEdit = $user->isAdmin() || $user->hasPerm('env-middleware[environments-edit]');


// Initialize variables
$error = '';
$success = '';
$environmentData = $addon->getConfig('environments', []);
$activeEnvironment = $addon->getConfig('active_environment', '');
$jsVariableName = $addon->getConfig('js_variable_name', 'ENV');

// Form processing
if (rex_post('save', 'boolean')) {

    // Save environment entries
    $newEnvironments = [];
    $entries = rex_post('env_entries', 'array', []);

    // Restructure entries into a more suitable format
    foreach ($entries as $entry) {
        if (!empty($entry['name'])) {
            $newEnvironments[$entry['name']] = isset($entry['value']) ? $entry['value'] : '';
        }
    }

    // Get JavaScript variable name and active environment
    $newJsVariableName = rex_post('js_variable_name', 'string', 'ENV');
    $newActiveEnvironment = rex_post('active_environment', 'string', '');

    // Save environments and settings
    $addon->setConfig('environments', $newEnvironments);
    $addon->setConfig('js_variable_name', $newJsVariableName);
    $addon->setConfig('active_environment', $newActiveEnvironment);

    // Update local variables to reflect saved changes
    $environmentData = $newEnvironments;
    $jsVariableName = $newJsVariableName;
    $activeEnvironment = $newActiveEnvironment;

    $success = $addon->i18n('settings_saved', 'Settings saved successfully!');
}

// Prepare the page content
$content = '';

// Error and success messages
if (!empty($error)) {
    echo rex_view::error($error);
}
if (!empty($success)) {
    echo rex_view::success($success);
}

// Start the form

$content .= '
<form action="' . rex_url::currentBackendPage() . '" method="post">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">' . $addon->i18n('environments', 'Environments') . '</h3>
        </div>
        <div class="panel-body">
            <p>' . $addon->i18n('environments_description', 'Configure environment variables') . '</p>
            
            <table class="table" id="env-entries-table">
                <thead>
                    <tr>
                        <th>' . $addon->i18n('name', 'Name') . '</th>
                        <th>' . $addon->i18n('value', 'Value') . '</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>';

// Display existing environments or a blank row if none exist
if (empty($environmentData)) {
    $content .= '
                    <tr class="env-entry">
                        <td><input ' . ($canEdit ? '' : 'readonly') . ' type="text" class="form-control" name="env_entries[0][name]" value="" /></td>
                        <td><input ' . ($canEdit ? '' : 'readonly') . ' type="text" class="form-control" name="env_entries[0][value]" value="" /></td>
                        <td><button ' . ($canEdit ? '' : 'disabled') . ' type="button" class="btn btn-delete btn-danger btn-xs"><i class="rex-icon rex-icon-delete"></i></button></td>
                    </tr>';
} else {
    $i = 0;
    foreach ($environmentData as $name => $value) {
        $content .= '
                    <tr class="env-entry">
                        <td><input ' . ($canEdit ? '' : 'readonly') . ' type="text" class="form-control" name="env_entries[' . $i . '][name]" value="' . htmlspecialchars($name) . '" /></td>
                        <td><input ' . ($canEdit ? '' : 'readonly') . ' type="text" class="form-control" name="env_entries[' . $i . '][value]" value="' . htmlspecialchars($value) . '" /></td>
                        <td><button ' . ($canEdit ? '' : 'disabled') . ' type="button" class="btn btn-delete btn-danger btn-xs"><i class="rex-icon rex-icon-delete"></i></button></td>
                    </tr>';
        $i++;
    }
}



$content .= '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">
                            <button ' . ($canEdit ? '' : 'disabled') . ' type="button" class="btn btn-default btn-add"><i class="rex-icon rex-icon-add"></i> ' . $addon->i18n('add_environment', 'Add Environment') . '</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">' . $addon->i18n('frontend_access', 'Frontend Access') . '</h3>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label for="js-variable-name">' . $addon->i18n('javascript_variable_name', 'JavaScript Variable Name') . '</label>
                <input type="text" class="form-control" id="js-variable-name" name="js_variable_name" value="' . htmlspecialchars($jsVariableName) . '" ' . ($canEdit ? '' : 'readonly') . ' />
                <p class="help-block">' . $addon->i18n('javascript_variable_help', 'Your environment variables will be accessible via window.{0} in the frontend', htmlspecialchars($jsVariableName)) . '</p>
            </div>
            
            <div class="form-group">
                <label for="active-environment">' . $addon->i18n('select_active_environment', 'Select Active Environment') . '</label>
                <select class="form-control" id="active-environment" name="active_environment" ' . ($canSelect ? '' : 'readonly') . '>
                    <option value="">' . $addon->i18n('none', 'None') . '</option>';

// Add environment options
foreach ($environmentData as $name => $value) {
    $selected = ($activeEnvironment === $name) ? ' selected="selected"' : '';
    $content .= '
                    <option value="' . htmlspecialchars($name) . '"' . $selected . '>' . htmlspecialchars($name) . '</option>';
}

$content .= '
                </select>
                <p class="help-block">' . $addon->i18n('active_environment_help', 'The selected environment will be available in the frontend') . '</p>
            </div>
        </div>
    </div>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">' . $addon->i18n('test_settings', 'Test Current Configuration') . '</h3>
        </div>
        <div class="panel-body">
            <p>' . $addon->i18n('current_configuration', 'Current Configuration') . ':</p>
            <pre>';

// Display current configuration
$content .= 'Active Environment: ' . ($activeEnvironment ?: 'Not set') . "\n";
$content .= 'JS Variable Name: ' . $jsVariableName . "\n\n";

if (!empty($activeEnvironment) && isset($environmentData[$activeEnvironment])) {
    $content .= 'Environment Variables (' . $activeEnvironment . ')' . ":\n";
    $content .= json_encode($environmentData[$activeEnvironment], JSON_PRETTY_PRINT);
} else {
    $content .= 'No active environment set or no variables defined.';
}

$content .= '</pre>
            <div class="alert alert-info">
                ' . $addon->i18n('frontend_test_instructions', 'To test in the frontend, add <code>console.log(window.{0});</code> to your JavaScript code or run it in the browser console.', htmlspecialchars($jsVariableName)) . '
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

// Add JavaScript for dynamic form handling
echo '
<script type="text/javascript">
    $(document).ready(function() {
        // Add new environment entry
        $(".btn-add").on("click", function() {
            var lastRow = $("#env-entries-table tbody tr:last");
            var newRow = lastRow.clone();
            var index = parseInt(newRow.find("input").first().attr("name").match(/\d+/)[0]) + 1;
            
            newRow.find("input").each(function() {
                var name = $(this).attr("name").replace(/\d+/, index);
                $(this).attr("name", name).val("");
            });
            
            $("#env-entries-table tbody").append(newRow);
        });
        
        // Delete environment entry
        $("#env-entries-table").on("click", ".btn-delete", function() {
            var table = $("#env-entries-table");
            if (table.find("tbody tr").length > 1) {
                $(this).closest("tr").remove();
            } else {
                $(this).closest("tr").find("input").val("");
            }
        });
    });
</script>';
