<?php
/*
Plugin Name: Easy 301 Redirects
Plugin URI: http://www.odihost.com
Description: Create a list of URLs that you would like to 301 redirect to another page or site. 
Version: 1.32
Author: Odihost.com
Author URI: http://www.odihost.com/
*/

/*  Copyright 2016  Ian  (email : ian@odihost.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include("includes/meta-box.php");

if (!class_exists("Easy301redirect")) {
	
	class Easy301redirect {
		
		/**
		 * create_menu function
		 * generate the link to the options page under settings
		 * @access public
		 * @return void
		 */
		function create_menu() {
		  add_options_page('Easy 301 Redirects', 'Easy 301 Redirects', 'manage_options', 'simple301options', array($this,'easy_options_page'));
		  add_options_page('Easy 301 Redirects Creator', 'Easy 301 Redirects Creator', 'manage_options', 'simple301optionscreator', array($this,'easy_options_page_creator'));
		}
		
		/**
		 * easy_options_page function
		 * generate the options page in the wordpress admin
		 * @access public
		 * @return void
		 */
		function easy_options_page() {
		?>
		<div class="wrap easy_301_redirects">
		
		<?php
			if (isset($_POST['301_redirects'])) {
				echo '<div id="message" class="updated"><p>Settings saved</p></div>';
			}
		?>
		
			<h2>Easy 301 Redirects</h2>
			<div class="notice"><p>If you like our plugin and feel it's useful, please review it <a href="https://wordpress.org/plugins/odihost-easy-redirect-301/" target="_blank">here</a>, so other people can feel the benefit as well. <br/><br/>We also offer Wordpress development. If you need Wordpress expert feel free to <a href="http://odihost.com/contact-us/" target="_blank">contact us</a>.</p>
			</div>
			<form method="post" id="easy_301_redirects_form" action="options-general.php?page=simple301options&savedata=true">
			
			<?php wp_nonce_field( 'save_easy_redirect', '_s301r_nonce' ); ?>
			<?php
			$redirects = get_option('301_redirects');
			$output = '';
			if (!empty($redirects)) {
				foreach ($redirects as $request => $destination) {
					$output .= $request.','.$destination."\n";
				}
			} // end if
			
			?>For help on how to setup this, please view our <a href="https://wordpress.org/plugins/odihost-easy-redirect-301/faq/">FAQ</a>

			<textarea name="str" id="str" cols="100" rows="20"><?php echo $output;?></textarea><br>
			<input type="hidden" name="easy_redirect" id="easy_redirect" value="0">
			<p class="submit"><input type="submit" name="submit_301" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
			</form>
			If you have any issues, please contact us here http://odihost.com/contact-us/
		</div>
		<?php
		} // end of function easy_options_page
		
		function easy_options_page_creator()
		{
			global $wpdb;
			if(isset($_REQUEST["original_site_url"]))
			{
				update_option("original_site_url",esc_url_raw($_REQUEST["original_site_url"]));
			}
			?>
			<form action="" method="post">
				<h1>Original/Live Site Url</h1> <input type="text" name="original_site_url" size="100" id="original_site_url" value="<?php echo get_option("original_site_url");?>">
				<input type="submit" value="Save">
			</form>
			You can copy the below 301 redirect to <a target="_blank" href="<?php echo get_option("siteurl");?>/wp-admin/options-general.php?page=simple301options"><?php echo get_option("siteurl");?>/wp-admin/options-general.php?page=simple301options</a><br/><br/>
			<?php
			$rows = $wpdb->get_results("select * from ".$wpdb->prefix."posts inner join ".$wpdb->prefix."postmeta on post_id = ".$wpdb->prefix."posts.ID where meta_key = 'original_site_url'");
			$original_site_url = get_option("original_site_url");
			$current_site_url = get_option("siteurl");
			foreach($rows as $row)
			{
				echo str_replace($original_site_url,"",$row->meta_value) .",".str_replace($current_site_url,$original_site_url,get_permalink($row->ID))."<br>";
			}
		}
		
		/**
		 * save_easy_redirect function
		 * save the redirects from the options page to the database
		 * @access public
		 * @param mixed $data
		 * @return void
		 */
		function save_easy_redirect($data) {
			if ( !current_user_can('manage_options') )  { wp_die( 'You do not have sufficient permissions to access this page.' ); }
			check_admin_referer( 'save_easy_redirect', '_s301r_nonce' );
			
			$data = $_POST['easy_redirect'];

			$redirects = array();
			
			$str = $_REQUEST["str"];

			$str = explode("\n", $str);
			function timeDiff($firstTime,$lastTime) {
				$firstTime=strtotime($firstTime);
				$lastTime=strtotime($lastTime);
				$timeDiff=$lastTime-$firstTime;
				return $timeDiff;
			}
			$redirects = array();
			foreach($str as $data)
			{
				$arr = explode(",",$data);
				if(trim($arr[1]) !="") 	$redirects[sanitize_text_field($arr[0])] = esc_url_raw($arr[1]);
			}
			
			update_option('301_redirects', $redirects);

		}
				

		/**
		 * redirect function
		 * Read the list of redirects and if the current page 
		 * is found in the list, send the visitor on her way
		 * @access public
		 * @return void
		 */

		function redirect() {
			// this is what the user asked for (strip out home portion, case insensitive)
			$user_request = str_ireplace(get_option('home'),'',$this->get_address());
			$user_request = rtrim($user_request,'/');
			$redirects = get_option('301_redirects');

			if (!empty($redirects)) {
				$do_redirect = '';
				// compare user request to each 301 stored in the db
				foreach ($redirects as $saved_request => $destination) {
					$do_redirect = "";
					if(urldecode($user_request) == rtrim($saved_request,'/')) {
						// simple comparison redirect
						$do_redirect = $destination;
					}

					if (strpos($saved_request,'*') !== false) {
						// wildcard redirect
		
						// Make sure it gets all the proper decoding and rtrim action
						$saved_request = str_replace('*','(.*)',$saved_request);
						$pattern = '/^' . str_replace( '/', '\/', rtrim( $saved_request, '/' ) ) . '/';

						$output = preg_replace($pattern, $destination, $user_request);
						//check for #1#,#2#,#3# in destination
						preg_match($pattern, $user_request,$preg_match);
						if(isset($preg_match[1])) $output= str_replace("#1#",$preg_match[1],$output);
						if(isset($preg_match[2])) $output= str_replace("#2#",$preg_match[2],$output);
						if(isset($preg_match[3])) $output= str_replace("#3#",$preg_match[3],$output);
						
						if ($output !== $user_request) {
							// pattern matched, perform redirect
							$do_redirect = $output;
						}
					}
					else if ($do_redirect !== '' && trim($do_redirect,'/') !== trim($user_request,'/')) {
						// check if destination needs the domain prepended
						if (strpos($do_redirect,'/') === 0){
							$do_redirect = home_url().$do_redirect;
						}
					}
					else { unset($redirects); }
					
					if($do_redirect !="")
					{
						header ('HTTP/1.1 301 Moved Permanently');
						header ('Location: ' . $do_redirect);
						exit();
					}
				}

			}

		} // end funcion redirect

		/**
		 * getAddress function
		 * utility function to get the full address of the current request
		 * credit: http://www.phpro.org/examples/Get-Full-URL.html
		 * @access public
		 * @return void
		 */
		function get_address() {
			// return the full address
			return $this->get_protocol().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		} // end function get_address
		
		function get_protocol() {
			// Set the base protocol to http
			$protocol = 'http';
			// check for https
			if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
    			$protocol .= "s";
			}
			
			return $protocol;
		} // end function get_protocol
		
	} // end class Easy301redirect
	
} // end check for existance of class

// instantiate
$easy_redirect_plugin = new Easy301redirect();

if (isset($easy_redirect_plugin)) {
	// add the redirect action, high priority
	add_action('init', array($easy_redirect_plugin,'redirect'), 1);

	// create the menu
	add_action('admin_menu', array($easy_redirect_plugin,'create_menu'));

	// if submitted, process the data
	if (isset($_POST['easy_redirect'])) {
		add_action('admin_init', array($easy_redirect_plugin,'save_easy_redirect'));
	}
}

// this is here for php4 compatibility
if(!function_exists('str_ireplace')){
  function str_ireplace($search,$replace,$subject){
    $token = chr(1);
    $haystack = strtolower($subject);
    $needle = strtolower($search);
    while (($pos=strpos($haystack,$needle))!==FALSE){
      $subject = substr_replace($subject,$token,$pos,strlen($search));
      $haystack = substr_replace($haystack,$token,$pos,strlen($search));
    }
    $subject = str_replace($token,$replace,$subject);
    return $subject;
  }
}