<?php
/**
 * Environment Middleware Addon - Main Page
 * 
 * @author Your Name
 * @package redaxo\env-middleware
 */

// Display the addon title
echo rex_view::title(rex_i18n::msg('env_middleware_title'));

// Include the current subpage (settings, environments, proxy)
rex_be_controller::includeCurrentPageSubPath();