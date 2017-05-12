<?php
/*
 Plugin Name: Filepress
 Plugin URI: http://www.choppedcode.com
 Description: Filepress is a plugin that allows you to edit your Wordpress files from any file browser.

 Author: choppedcode
 Version: 1.0.1
 Author URI: http://www.choppedcode.com/
 */

define('FILEPRESS','Filepress');
define('FILEPRESS_VERSION','1.0.1');

if (!defined("BLOGUPLOADDIR")) {
	$upload=wp_upload_dir();
	define("BLOGUPLOADDIR",$upload['path']);
}

if (!defined("FILEPRESS_PLUGIN")) {
	$filepress_plugin=str_replace(realpath(dirname(__FILE__).'/..'),"",dirname(__FILE__));
	$filepress_plugin=substr($filepress_plugin,1);
	define("FILEPRESS_PLUGIN", $filepress_plugin);
}

define("FILEPRESS_URL", WP_CONTENT_URL . "/plugins/".FILEPRESS_PLUGIN."/");

register_deactivation_hook(__FILE__,'filepress_deactivate');

require(dirname(__FILE__).'/includes/http.class.php');
require(dirname(__FILE__).'/controlpanel.php');

add_action('admin_head','filepress_admin_header');
add_action("init","filepress_init");
add_action('admin_notices','cc_whmcs_admin_notices');

function filepress_deactivate() {
	$filepress_options=filepress_options();
	delete_option('filepress_log');
	foreach ($filepress_options as $value) {
		delete_option( $value['id'] );
	}

	delete_option("filepress_log");
}

function filepress_init() {
	if (is_admin()) return;
	filepress_log(0,$_SERVER);
	if (($_SERVER['REQUEST_METHOD']=='PROPFIND')
	//|| ($_SERVER['REQUEST_METHOD']=='GET') {
	|| strstr(strtolower($_SERVER['HTTP_USER_AGENT']),'webdav')) {
		require(dirname(__FILE__).'/dav/lib/Sabre/autoload.php');
		ob_start();
		if (!session_id()) @session_start();
		ob_end_clean();
		require(dirname(__FILE__).'/dav/fileserver.php');
		die();
	}
}

function filepress_admin_header() {
	echo '<link rel="stylesheet" type="text/css" href="' . FILEPRESS_URL . 'css/admin.css" media="screen" />';
}

function filepress_log($type=0,$msg='',$filename="",$linenum=0) {
	if ($type==0) $type='Debug';
	if (get_option('filepress_debug')) {
		if (is_array($msg)) $msg=print_r($msg,true);
		$v=get_option('filepress_log');
		if (!is_array($v)) $v=array();
		array_unshift($v,array(time(),$type,$msg));
		update_option('filepress_log',$v);
	}
}

function cc_whmcs_admin_notices() {
	$errors=array();
	$warnings=array();
	$files=array();
	$dirs=array();

	$upload=wp_upload_dir();
	if ($upload['error']) $errors[]=$upload['error'];
	if (get_option('filepress_debug')) $warnings[]="Debug is active, once you finished debugging, it's recommended to turn this off";
	if (phpversion() < '5') $warnings[]="You are running PHP version ".phpversion().". We recommend you upgrade to PHP 5.3 or higher.";
	if (ini_get("zend.ze1_compatibility_mode")) $warnings[]="You are running PHP in PHP 4 compatibility mode. We recommend you turn this option off.";
	if (!function_exists('curl_init')) $errors[]="You need to have cURL installed. Contact your hosting provider to do so.";

	if (count($warnings) > 0) {
		echo "<div id='zing-warning' style='background-color:greenyellow' class='updated fade'><p><strong>";
		foreach ($warnings as $message) echo FILEPRESS.': '.$message.'<br />';
		echo "</strong> "."</p></div>";
	}
	if (count($errors) > 0) {
		echo "<div id='zing-warning' style='background-color:pink' class='updated fade'><p><strong>";
		foreach ($errors as $message) echo FILEPRESS.':'.$message.'<br />';
		echo "</strong> "."</p></div>";
	}
}
