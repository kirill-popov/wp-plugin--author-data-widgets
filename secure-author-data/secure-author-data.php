<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 */

/**
 * Plugin Name:     Secure Author Data Plugin
 * Description:     This is a plugin that creates widget with Author's data.
 * Author:          Kyrylo Popov
 * Text Domain:     secure-author-data
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Secure_Author_Data
 */

namespace Secure_Author_Data_Plugin;

if (!defined('ABSPATH')) {
    return;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SECURE_AUTHOR_DATA_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/secure-author-data-activator.php
 */
function activate_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/secure-author-data-activator.php';
	Secure_Author_Data_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/secure-author-data-deactivator.php
 */
function deactivate_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/secure-author-data-deactivator.php';
	Secure_Author_Data_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'Secure_Author_Data_Plugin\activate_plugin' );
register_deactivation_hook( __FILE__, 'Secure_Author_Data_Plugin\deactivate_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/secure-author-data.php';

function run_plugin_name() {
	$plugin = new Secure_Author_Data();
	$plugin->run();
}
run_plugin_name();
