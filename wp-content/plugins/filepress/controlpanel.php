<?php
function filepress_options() {
	global $filepress_shortname,$cc_login_type,$current_user;
	$filepress_shortname = "filepress";

	$url='<strong>'.get_site_url().'/filepress/'.'</strong>';
	
	$filepress_options[] = array(  "name" => "Preparing Wordpress",
            "type" => "heading",
			"desc" => "<ol><li>Edit your .htaccess file in the root directory of your installation</li><li>If you have permalinks turned on, you will already have a .htaccess file, otherwise you will need to create it manually.</li><li>Add the following lines at the top of the file:
			<br />RewriteEngine On<br />RewriteCond %{REQUEST_URI} ^/filepress<br />RewriteRule ^(.*) wp-login.php/ [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
			</li></ol>");	
$filepress_options[] = array(  "name" => "Mac OSX Finder",
            "type" => "heading",
			"desc" => "<ol><li>Select the 'Go' menu in Finder</li><li>Enter ".$url." in the  'Server Address' field</li><li>Enter your Wordpress user id and password when prompted to access your directory</li></ol>");

	$filepress_options[] = array(  "name" => "Windows 7 File Explorer",
            "type" => "heading",
			"desc" => "<ol><li>Windows 7 doesn't support Basic Authentication by default so we need to make a registry change.</li><li>Open the registry editor</li><li>Lookup HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\WebClient\Parameters</li><li>Change the DWORD value of 'BasicAuthLevel' to 2</li><li>Reboot</li><li>Open 'Computer' from your start menu or desktop</li><li>Open the 'Map network drive' wizard</li><li>Enter $url</li><li>Select 'other credentials'</li><li>Enter your Wordpress user id and password when prompted to access your directory</li></ol>");
	
	//HKLM\SYSTEM\CurrentControlSet\Services\WebClient\Parameters and setting the DWORD value "BasicAuthLevel" to 2, then reboot
	$filepress_options[] = array(  "name" => "Important Notes",
            "type" => "heading",
			"desc" => "<ul><li>Don't forget the trailing / at the end of the URL</li><li>Be very careful when you are making changes to the Windows registry</li><li>If you are not using a HTTPS connection, the username and password are being sent in clear</li></ul>");
	
	$filepress_options[] = array(  "name" => "Debug Settings",
            "type" => "heading",
			"desc" => "");
	
	$filepress_options[] = array(	"name" => "Debug",
			"desc" => "If you have problems with the plugin, activate the debug mode to generate a debug log for our support team",
			"id" => $filepress_shortname."_debug",
			"type" => "checkbox");
	
	ksort($filepress_options);
	
	return $filepress_options;
}

function filepress_add_admin() {

	global $filepress_shortname;

	$filepress_options=filepress_options();

	if (isset($_GET['page']) && ($_GET['page'] == "cc-ce-bridge-cp")) {
		
		if ( isset($_REQUEST['action']) && 'install' == $_REQUEST['action'] ) {
			delete_option('filepress_log');

			foreach ($filepress_options as $value) {
				if( isset( $_REQUEST[ $value['id'] ] ) ) {
					update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
				}
			}
			header("Location: options-general.php?page=cc-ce-bridge-cp&installed=true");
			die;
		}
	}

	add_options_page(FILEPRESS, FILEPRESS, 'administrator', 'cc-ce-bridge-cp','filepress_admin');
}

function filepress_admin() {

	global $filepress_shortname;

	$controlpanelOptions=filepress_options();

	if ( isset($_REQUEST['installed']) ) echo '<div id="message" class="updated fade"><p><strong>'.FILEPRESS.' installed.</strong></p></div>';
	if ( isset($_REQUEST['error']) ) echo '<div id="message" class="updated fade"><p>The following error occured: <strong>'.$_REQUEST['error'].'</strong></p></div>';
	
	?>
<div class="wrap">
<div id="cc-left" style="position:relative;float:left;width:80%">
<h2><b><?php echo FILEPRESS; ?></b></h2>

	<?php
	$filepress_version=get_option("filepress_version");
	$submit='Update';
	?>
<form method="post">

<?php require(dirname(__FILE__).'/includes/cpedit.inc.php')?>

<p class="submit"><input name="install" type="submit" value="<?php echo $submit;?>" /> <input
	type="hidden" name="action" value="install"
/></p>
</form>
<hr />
<?php  
	if (get_option('filepress_debug')) {
		echo '<h2 style="color: green;">Debug log</h2>';
		echo '<textarea rows=10 cols=80>';
		$r=get_option('filepress_log');
		if ($r) {
			$v=$r;
			foreach ($v as $m) {
				echo date('H:i:s',$m[0]).' '.$m[1].chr(13).chr(10);
				echo $m[2].chr(13).chr(10);
			}
		}
		echo '</textarea><hr />';
	}
?>

</div> <!-- end cc-left -->
<?php
}
add_action('admin_menu', 'filepress_add_admin'); ?>