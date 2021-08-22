<?php
/*
Plugin Name: Contact Form If
Author: Satoshi Kaneyasu
Plugin URI: https://github.com/sk-plus/contact-form-if
Description: This is my great plugin!
Version: 0.1
Author URI: https://github.com/sk-plus/contact-form-if
Domain Path: /

Text Domain: contact-form-if
*/

define( 'WPCFIF_PLUGIN', __FILE__ );

define( 'WPCFIF_PLUGIN_BASENAME', plugin_basename( WPCFIF_PLUGIN ) );

define( 'WPCFIF_PLUGIN_NAME', trim( dirname( WPCFIF_PLUGIN_BASENAME ), '/' ) );

define( 'WPCFIF_PLUGIN_DIR', untrailingslashit( dirname( WPCFIF_PLUGIN ) ) );

require_once WPCFIF_PLUGIN_DIR . '/includes/class-wpcfif.php';
WPCFIF::get_instance();

