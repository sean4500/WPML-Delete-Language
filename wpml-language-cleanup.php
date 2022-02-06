<?php
/**
 * Plugin Name:  WPML Language Cleanup
 * Description:  Allows the automatic cleanup of a disabled language
 * Author:       Sean Harvey
 */

namespace WPML_Cleanup;

// Disallow direct access
defined( 'ABSPATH' ) || die();

// Define version
define( 'WPML_LANGUAGE_CLEANUP_VERSION', '1.0' );

// On activation check to make WPML is active
function wpml_clean_activate(){
  // Support for multisite
  if( ! function_exists( 'is_plugin_active_for_network' ) ){
    include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
  }
  // Make sure WPML is active and current user can activate plugins, otherwise deactivate
  if( current_user_can( 'activate_plugins' ) && ! class_exists( 'SitePress' ) ){
    deactivate_plugins( plugin_basename( __FILE__ ) );
    // Throw an error in the WordPress admin console.
    $error_message = '<p style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;font-size: 13px;line-height: 1.5;color:#444;">' . esc_html__( 'This plugin requires ' ) . '<a href="' . esc_url( 'https://wpml.org/' ) . '">WPML</a>' . esc_html__( ' plugin to be active.' ) . '</p>';
    wp_die( $error_message );
  }
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\\wpml_clean_activate' );

// Load required files
require_once __DIR__ . '/includes/util.php';
require_once __DIR__ . '/admin/admin-pane.php';