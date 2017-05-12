<?php
/*
Plugin Name:  Twitter Feed Pro
Plugin URI:   http://peadig.com/wordpress/plugins/wp-twitter-feed-pro/?utm_source=WordPress%2BAdmin&utm_medium=referral&utm_campaign=Twitter%2BFeed%2BPro
Description:  Twitter Feed Pro WordPress Plugin lets you output any Twitter timeline feed, favorite feed, search or hashtag into your WordPress site! Using Twitter's new API (v1.1), this is the most up to date, versatile and fully customisable Twitter Feed for websites that still outputs tweets as flat HTML.
Version:      1.2.3
Author: Alex Moss
Author URI: http://alex-moss.co.uk/


Copyright (C) 2010-2013, Alex Moss - alex@peadig.com
All rights reserved.


Resale of this plugin is absolutely forbidden and any edits to this plugin invalidates support.
*/


define( 'WPTF_ITEM_NAME', 'Twitter Feed Pro' );

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

// retrieve our license key from the DB
$license_key = trim( get_option( 'wptf_pro_license_key' ) );

// setup the updater
$edd_updater = new EDD_SL_Plugin_Updater( 'http://peadig.com', __FILE__, array(
		'version' 	=> '1.2.3',
		'license' 	=> $license_key,
		'item_name' => 'Twitter Feed Pro',
		'author' 	=> 'Alex Moss'
	)
);

function wptf_pro_register_option() {
	register_setting('wptf_pro_license', 'wptf_pro_license_key', 'wptf_pro_sanitize_license' );
}
add_action('admin_init', 'wptf_pro_register_option');

function wptf_pro_sanitize_license( $new ) {
	$old = get_option( 'wptf_pro_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'wptf_pro_license_status' );
	}
	return $new;
}

function wptf_pro_setup() {
	global $wp_version;
	$license = trim( get_option( 'wptf_pro_license_key' ) );
	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( WPTF_ITEM_NAME )
	);
	$response = wp_remote_get( add_query_arg( $api_params, 'http://peadig.com' ), array( 'timeout' => 15, 'sslverify' => false ) );
	if ( is_wp_error( $response ) )
		return false;
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	$options = get_option('wptf_pro');
	if( $license_data->license == 'valid' ) {
//		echo '<span style="color:green;">valid: '.$options['database'].'</span>';
		$options['database']='1';
	} else {
		$options['database']='0';
//		echo '<span style="color:red;">invalid: '.$options['database'].'</span>';
	}
	update_option('wptf_pro', $options);

}

ini_set('precision', 20);

if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) )
	require 'class-admin.php';
else
	require 'class-frontend.php';


// Add settings link on plugin page
function wptf_pro_link($links) {
  $settings_link = '<a href="options-general.php?page=wptf_pro">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}


$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'wptf_pro_link' );
?>