<?php
/**
 * Constants used by this plugin
 * 
 * @package Plugin_Template
 * 
 * @author Williams Web Team
 * @version 1.0.0
 * @since 1.0.0
 */

if(!defined('WMS_WIDGET_PREFIX')) define( 'WMS_WIDGET_PREFIX', '. ');

// The current version of this plugin
if( !defined( 'WMS_REST_API_VERSION' ) ) define( 'WMS_REST_API_VERSION', '1.0.0' );

// The directory the plugin resides in
if( !defined( 'WMS_REST_API_DIRNAME' ) ) define( 'WMS_REST_API_DIRNAME', dirname( dirname( __FILE__ ) ) );

// The URL path of this plugin
if( !defined( 'WMS_REST_API_URLPATH' ) ) define( 'WMS_REST_API_URLPATH', WP_PLUGIN_URL . "/" . plugin_basename( WMS_REST_API_DIRNAME ) );

if( !defined( 'IS_AJAX_REQUEST' ) ) define( 'IS_AJAX_REQUEST', ( !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) );