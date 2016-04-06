<?php
/*
Plugin Name: fcc-network-admin-ui
Plugin URI: https://github.com/openfcci/fcc-network-admin-ui
Description: A series of modules that adds or extends the functionality, tools and UI of the admin dashboard.
Author: Forum Communications Company
Version: 0.16.04.06
Author URI: http://forumcomm.com/
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/*--------------------------------------------------------------
# PLUGIN ACTIVATION/DEACTIVATION HOOKS
--------------------------------------------------------------*/

/**
 * Plugin Activation Hook
 */
function fcc_network_admin_ui_plugin_activation() {
	// Flush our rewrite rules on activation.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'fcc_network_admin_ui_plugin_activation' );

/**
 * Plugin Deactivation Hook
 */
function fcc_network_admin_ui_plugin_deactivation() {
	// Flush our rewrite rules on deactivation.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'fcc_network_admin_ui_plugin_deactivation' );

/*--------------------------------------------------------------
# LOAD INCLUDES FILES
--------------------------------------------------------------*/
