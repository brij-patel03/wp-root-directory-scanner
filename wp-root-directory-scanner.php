<?php

/**
 * Plugin Name: WP Root Directory Scanner
 * Description:  This plugin scan the root directory of the WordPress installation and list the files and directories on the WordPress admin side.
 * Version: 1.0
 * Author: Brijesh Patel
 * Author URI: https://profiles.wordpress.org/brijesh03/
 * License: GPLv2 or later
 * Text Domain: wprds
 * Domain Path: /languages
 */

defined('ABSPATH') or die('No script kiddies please!');
/**
 *	Define constants
 */
define('WPRDS_VERSION', '1.0');
define('WPRDS_DB_VERSION', '1.0');
define('WPRDS_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
define('WPRDS_PLUGINS_DIR', plugin_dir_path(__DIR__));
define('WPRDS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPRDS_PLUGIN_ASSETS_URL', WPRDS_PLUGIN_URL . "assets/");
define('WPRDS_TEXT_DOMAIN', 'wprds');

/**
 * Global variables
 */
global $wpdb, $wprds_table_prefix, $wprds_menu_page_capability;
$wprds_table_prefix          = $wpdb->prefix;
$wprds_menu_page_capability  = 'manage_options';

/**
 * WP Root Directory Scanner table name 
 */
define('WPRDS_SCANNER_TABLE', $wprds_table_prefix . 'wp_root_directory_scanner');

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, 'wprds_plugin_activation');

/**
 * Plugin dectivation hook
 */
register_deactivation_hook(__FILE__, 'wprds_plugin_deactivation');

/**
 * Add required files
 */
foreach (glob(WPRDS_PLUGIN_DIR_PATH . "includes/*.php") as $fileName) {
    require_once $fileName;
}

/**
 * All Actions, Filters and Shortcode
 */
add_action('admin_menu', 'wprds_add_admin_menu');
add_action('admin_enqueue_scripts', 'wprds_add_script_and_style');
add_action("wp_ajax_root_dir_scan", "root_dir_scan_cb");
?>