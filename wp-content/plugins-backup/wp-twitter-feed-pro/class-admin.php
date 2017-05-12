<?php
define("WPTF_NAME","Twitter Feed Pro");
define("WPTF_TAGLINE","The ultimate plugin for outputting tweets via shortcode :)");
define("WPTF_URL","http://peadig.com/wordpress-plugins/wp-twitter-feed-pro/");
define("WPTF_AUTHOR_TWITTER","alexmoss");
define("WPTF_DONATE_LINK","https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TPB967WJCR35N");

add_action('admin_init', 'wptf_pro_init' );
function wptf_pro_init(){
	register_setting( 'wptf_pro_options', 'wptf_pro' );
	$new_options = array(
		'consumer_key' => '',
		'consumer_secret' => '',
		'access_token' => '',
		'access_secret' => '',
		'errormessage' => 'Twitter cannot show tweets right now. Please try again.',
		'username' => 'peadig',
		'twitterJS' => 'yes',
		'num' => '5',
		'img' => 'yes',
		'HTTPSimg' => 'no',
		'term' => 'twitter',
		'hashtag' => 'WordPress',
		'followbutton' => 'yes',
		'followercount' => 'no',
		'largebutton' => 'yes',
		'lang' => 'en',
		'anchor' => '',
		'hashlinks' => 'yes',
		'newwindow' => 'yes',
		'timeline' => 'yes',
		'usephptime' => 'no',
		'tprefix' => '(about',
		'tsuffix' => 'ago)',
		'phptime' => 'j F Y \a\t h:ia',
		'linktotweet' => 'yes',
		'userintent' => 'yes',
		'useCSS' => 'yes',
		'ulclass' => 'twitterfeed',
		'liclass' => 'tweet',
		'intentclass' => 'intent',
		'timelineclass' => 'timeline',
		'linklove' => 'yes',
		'auth' => 'no',
		'APIwarning' => 'no',
	);
	add_option( 'wptf_pro', $new_options );
}


add_action('admin_menu', 'show_wptf_pro_options');
function show_wptf_pro_options() {
	add_options_page('Twitter Feed Pro Options', 'Twitter Feed Pro', 'manage_options', 'wptf_pro', 'wptf_pro_options');
}


function wptf_pro_fetch_rss_feed() {
    include_once(ABSPATH . WPINC . '/feed.php');
	$rss = fetch_feed("http://peadig.com/feed");
	if ( is_wp_error($rss) ) { return false; }
	$rss_items = $rss->get_items(0, 3);
    return $rss_items;
}

function wptf_pro_admin_notice(){
$options = get_option('wptf_pro');
if ($options['consumer_key']=="" || $options['consumer_secret']=="" || $options['access_token']=="" || $options['access_secret']=="") {
	$wptf_proadminurl = get_admin_url()."options-general.php?page=wptf_pro";
    echo '<div class="error">
       <p>You have not entered your Twitter API settings for the Twitter Feed Pro plugin to work. <a href="'.$wptf_proadminurl.'"><input type="submit" value="Configure" class="button-secondary" /></a></p>
    </div>';
}
}
add_action('admin_notices', 'wptf_pro_admin_notice');

function wptf_pro_activate_license() {
	if( isset( $_POST['wptf_pro_license_activate'] ) ) {
	 	if( ! check_admin_referer( 'wptf_pro_nonce', 'wptf_pro_nonce' ) )
			return;
		$license = trim( get_option( 'wptf_pro_license_key' ) );
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( WPTF_ITEM_NAME ) // the name of our product in EDD
		);
		$response = wp_remote_get( add_query_arg( $api_params, 'http://peadig.com' ), array( 'timeout' => 15, 'sslverify' => false ) );
		if ( is_wp_error( $response ) )
			return false;
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		update_option( 'wptf_pro_license_status', $license_data->license );
	}
}
add_action('admin_init', 'wptf_pro_activate_license');

function wptf_pro_deactivate_license() {
	if( isset( $_POST['wptf_pro_license_deactivate'] ) ) {
	 	if( ! check_admin_referer( 'wptf_pro_nonce', 'wptf_pro_nonce' ) )
			return; // get out if we didn't click the Activate button
		$license = trim( get_option( 'wptf_pro_license_key' ) );
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( WPTF_ITEM_NAME ) // the name of our product in EDD
		);
		$response = wp_remote_get( add_query_arg( $api_params, 'http://peadig.com' ), array( 'timeout' => 15, 'sslverify' => false ) );
		if ( is_wp_error( $response ) )
			return false;
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		if( $license_data->license == 'deactivated' )
			delete_option( 'wptf_pro_license_status' );
	}
}
add_action('admin_init', 'wptf_pro_deactivate_license');

// ADMIN PAGE
function wptf_pro_options() {
	$domain = get_option('siteurl');
//	settings_fields('wptf_pro_options');
	$options = get_option('wptf_pro');

	$license 	= get_option( 'wptf_pro_license_key' );
	$status 	= get_option( 'wptf_pro_license_status' );
	?>
    <link href="<?php echo plugins_url( 'admin.css' , __FILE__ ); ?>" rel="stylesheet" type="text/css">
    <div class="pea_admin_wrap">
        <div class="pea_admin_top">
            <h1><?php echo WPTF_NAME?> <small> - <?php echo WPTF_TAGLINE?></small></h1>
        </div>

        <div class="pea_admin_main_wrap">
            <div class="pea_admin_main_left">
                <div class="pea_admin_signup">
                    Want to know about updates to this plugin without having to log into your site every time? Want to know about other cool plugins we've made? Add your email and we'll add you to our very rare mail outs.

                    <!-- Begin MailChimp Signup Form -->
                    <div id="mc_embed_signup">
                    <form action="http://peadig.us5.list-manage2.com/subscribe/post?u=e16b7a214b2d8a69e134e5b70&amp;id=eb50326bdf" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                    <div class="mc-field-group">
                        <label for="mce-EMAIL">Email Address
                    </label>
                        <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL"><button type="submit" name="subscribe" id="mc-embedded-subscribe" class="pea_admin_green">Sign Up!</button>
                    </div>
                        <div id="mce-responses" class="clear">
                            <div class="response" id="mce-error-response" style="display:none"></div>
                            <div class="response" id="mce-success-response" style="display:none"></div>
                        </div>	<div class="clear"></div>
                    </form>
                    </div>

                    <!--End mc_embed_signup-->
                </div>



	<div class="wrap">
		<h2><?php _e('Plugin License Options'); ?></h2>
		<form method="post" action="options.php">

			<?php settings_fields('wptf_pro_license'); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e('License Key'); ?>
						</th>
						<td>
							<input id="wptf_pro_license_key" name="wptf_pro_license_key" type="text" value="<?php esc_attr_e( $license ); ?>" size="50" />
								<?php if( $status !== false && $status == 'valid' ) { ?>
									<?php wp_nonce_field( 'wptf_pro_nonce', 'wptf_pro_nonce' ); ?>
									<input type="submit" class="button-secondary" name="wptf_pro_license_deactivate" value="<?php _e('Deactivate License'); ?>"/><br />
							<label class="description" for="wptf_pro_license_key">Your key is active. You can now use the plugin and receive updates :)</label>
								<?php } else {
									wp_nonce_field( 'wptf_pro_nonce', 'wptf_pro_nonce' ); ?>
									<input type="submit" class="button-secondary" name="wptf_pro_license_activate" value="<?php _e('Activate License'); ?>"/><br />
							<label class="description" for="wptf_pro_license_key">Your key is inactive. Until you active the license the plugin will not work</label>
								<?php } ?>
						</td>
					</tr>
				</tbody>
			</table>

		</form>
	<?php if ($options['database']='1' && $status == 'valid' && false !== $license) { ?>
		<form method="post" action="options.php">
		<?php settings_fields('wptf_pro_options'); ?>
			<h3 class="title">Twitter App Setup</h3>
			<table class="form-table">
				<tr valign="top"><th scope="row"><a href="https://dev.twitter.com/apps" style="text-decoration:none" target="_blank">Twitter Apps Area</a></th>
					<td><small>this area lets you configure your Twitter App and (if you want) reset your consumer key and secret. You can also <a href="https://dev.twitter.com/apps/new" target="_blank">create a new application</a>. Fill out the application name, description and use <strong><?php echo $domain; ?></strong> as the website.</small><br></td>
				</tr>
<tr><td colspan="3"><p><strong>If you do not know how to find all this information, <a href="http://www.youtube.com/watch?v=CVz1MjqTXMg" target="_blank">this video</a> will help you.</strong></p></td></tr>
				<tr valign="top"><th scope="row"><label for="consumer_key">Consumer Key</label></th>
					<td><input id="consumer_key" type="text" name="wptf_pro[consumer_key]" value="<?php echo $options['consumer_key']; ?>" size="50" /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="consumer_secret">Consumer Secret</label></th>
					<td><input id="consumer_secret" type="text" name="wptf_pro[consumer_secret]" value="<?php echo $options['consumer_secret']; ?>" size="50" /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="access_token">Access Token</label></th>
					<td><input id="access_token" type="text" name="wptf_pro[access_token]" value="<?php echo $options['access_token']; ?>" size="50" /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="access_secret">Access Token Secret</label></th>
					<td><input id="access_secret" type="text" name="wptf_pro[access_secret]" value="<?php echo $options['access_secret']; ?>" size="50" /></td>
				</tr>
<tr><td colspan="3"><p><em>Also note that you can use one app for multiple sites/domains</em></p></td></tr>
			</table>

			<h3 class="title">Main Settings</h3>
			<table class="form-table">
				<tr valign="top"><th scope="row"><label for="twitterJS">Enable Twitter JS</label></th>
					<td><input id="twitterJS" name="wptf_pro[twitterJS]" type="checkbox" value="yes" <?php checked('yes', $options['twitterJS']); ?> /> <small>only disable this if you already have Twitter's JS call enabled elsewhere</small></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="username">Default Username</label></th>
					<td>@<input id="username" type="text" name="wptf_pro[username]" value="<?php echo $options['username']; ?>" /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="num">Number of Tweets to show</label></th>
					<td><input id="num" type="number" name="wptf_pro[num]" value="<?php echo $options['num']; ?>" size="10" /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="term">Default Search Term</label></th>
					<td><input id="term" type="text" name="wptf_pro[term]" value="<?php echo $options['term']; ?>" /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="hashtag">Default Hashtag</label></th>
					<td>#<input id="hashtag" type="text" name="wptf_pro[hashtag]" value="<?php echo $options['hashtag']; ?>" /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="errormessage">Connection Error Message</label></th>
					<td><input id="errormessage" type="text" name="wptf_pro[errormessage]" value="<?php echo $options['errormessage']; ?>" size="50" /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="linklove">Credit</label></th>
					<td><input id="linklove" name="wptf_pro[linklove]" type="checkbox" value="yes" <?php checked('yes', $options['linklove']); ?> /></td>
				</tr>

			</table>

			<h3 class="title">Tweet Options</h3>
			<table class="form-table">
				<tr valign="top"><th scope="row"><label for="img">Show profile pictures</label></th>
					<td><input id="img" name="wptf_pro[img]" type="checkbox" value="yes" <?php checked('yes', $options['img']); ?> /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="auth">Show Username before Tweet</label></th>
					<td><input id="auth" name="wptf_pro[auth]" type="checkbox" value="yes" <?php checked('yes', $options['auth']); ?> /> <small>inserts <strong>@username: </strong> before each tweet, which links to that username.</small></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="HTTPSimg">Use HTTPS for pictures</label></th>
					<td><input id="HTTPSimg" name="wptf_pro[HTTPSimg]" type="checkbox" value="yes" <?php checked('yes', $options['HTTPSimg']); ?> /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="followbutton">Append Twitter Button</label></th>
					<td><input id="followbutton" name="wptf_pro[followbutton]" type="checkbox" value="yes" <?php checked('yes', $options['followbutton']); ?> /> <small>inserts a Twitter follow button beneath the Twitter feed if the Twitter feed is about a user, and a Twitter search button if using the hashtag or search modes</small></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="largebutton">Large Twitter Button</label></th>
					<td><input id="largebutton" name="wptf_pro[largebutton]" type="checkbox" value="yes" <?php checked('yes', $options['largebutton']); ?> /></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="followercount">Show Follower Count</label></th>
					<td><input id="followercount" name="wptf_pro[followercount]" type="checkbox" value="yes" <?php checked('yes', $options['followercount']); ?> /> <small>shows the number of followers by your @username for the follow button</small></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="lang">Button Language</label></th>
					<td>
              <select id="lang" name="wptf_pro[lang]">
				  <option <?php if ($options['lang'] == "en" || $options['lang'] == "") {echo ' selected="selected"';} ?> value="en">English</option>
				  <option <?php if ($options['lang'] == "fr") {echo ' selected="selected"';} ?> value="fr">French</option>
				  <option <?php if ($options['lang'] == "ar") {echo ' selected="selected"';} ?> value="ar">Arabic</option>
				  <option <?php if ($options['lang'] == "ja") {echo ' selected="selected"';} ?> value="ja">Japanese</option>
				  <option <?php if ($options['lang'] == "es") {echo ' selected="selected"';} ?> value="es">Spanish</option>
				  <option <?php if ($options['lang'] == "de") {echo ' selected="selected"';} ?> value="de">German</option>
				  <option <?php if ($options['lang'] == "it") {echo ' selected="selected"';} ?> value="it">Italian</option>
				  <option <?php if ($options['lang'] == "id") {echo ' selected="selected"';} ?> value="id">Indonesian</option>
				  <option <?php if ($options['lang'] == "pt") {echo ' selected="selected"';} ?> value="pt">Portuguese</option>
				  <option <?php if ($options['lang'] == "ko") {echo ' selected="selected"';} ?> value="ko">Korean</option>
				  <option <?php if ($options['lang'] == "tr") {echo ' selected="selected"';} ?> value="tr">Turkish</option>
				  <option <?php if ($options['lang'] == "ru") {echo ' selected="selected"';} ?> value="ru">Russian</option>
				  <option <?php if ($options['lang'] == "nl") {echo ' selected="selected"';} ?> value="nl">Dutch</option>
				  <option <?php if ($options['lang'] == "fil") {echo ' selected="selected"';} ?> value="fil">Filipino</option>
				  <option <?php if ($options['lang'] == "msa") {echo ' selected="selected"';} ?> value="msa">Malay</option>
				  <option <?php if ($options['lang'] == "zh-tw") {echo ' selected="selected"';} ?> value="zh-tw">Traditional Chinese</option>
				  <option <?php if ($options['lang'] == "zh-cn") {echo ' selected="selected"';} ?> value="zh-cn">Simplified Chinese</option>
				  <option <?php if ($options['lang'] == "hi") {echo ' selected="selected"';} ?> value="hi">Hindi</option>
				  <option <?php if ($options['lang'] == "no") {echo ' selected="selected"';} ?> value="no">Norwegian</option>
				  <option <?php if ($options['lang'] == "sv") {echo ' selected="selected"';} ?> value="sv">Swedish</option>
				  <option <?php if ($options['lang'] == "fi") {echo ' selected="selected"';} ?> value="fi">Finnish</option>
				  <option <?php if ($options['lang'] == "da") {echo ' selected="selected"';} ?> value="da">Danish</option>
				  <option <?php if ($options['lang'] == "pl") {echo ' selected="selected"';} ?> value="pl">Polish</option>
				  <option <?php if ($options['lang'] == "hu") {echo ' selected="selected"';} ?> value="hu">Hungarian</option>
				  <option <?php if ($options['lang'] == "fa") {echo ' selected="selected"';} ?> value="fa">Farsi</option>
				  <option <?php if ($options['lang'] == "he") {echo ' selected="selected"';} ?> value="he">Hebrew</option>
				  <option <?php if ($options['lang'] == "ur") {echo ' selected="selected"';} ?> value="ur">Urdu</option>
				  <option <?php if ($options['lang'] == "th") {echo ' selected="selected"';} ?> value="th">Thai</option>
				  <option <?php if ($options['lang'] == "uk") {echo ' selected="selected"';} ?> value="uk">Ukrainian</option>
				  <option <?php if ($options['lang'] == "ca") {echo ' selected="selected"';} ?> value="ca">Catalan</option>
				  <option <?php if ($options['lang'] == "el") {echo ' selected="selected"';} ?> value="el">Greek</option>
				  <option <?php if ($options['lang'] == "eu") {echo ' selected="selected"';} ?> value="eu">Basque</option>
				  <option <?php if ($options['lang'] == "cs") {echo ' selected="selected"';} ?> value="cs">Czech</option>
				  <option <?php if ($options['lang'] == "gl") {echo ' selected="selected"';} ?> value="gl">Galician</option>
				  <option <?php if ($options['lang'] == "ro") {echo ' selected="selected"';} ?> value="ro">Romanian</option>
                </select>
</td>
				</tr>
				<tr valign="top"><th scope="row"><label for="anchor">Anchor Text</label></th>
					<td><input id="anchor" type="text" name="wptf_pro[anchor]" value="<?php echo $options['anchor']; ?>" /> <small>text for links within a tweet. If left blank a URL will be inserted</small></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="userlinks">Link to @usernames</label></th>
					<td><input id="userlinks" name="wptf_pro[userlinks]" type="checkbox" value="yes" <?php checked('yes', $options['userlinks']); ?> /> <small>Inserts a link to any @username who is mentioned in a tweet</small></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="hashlinks">Link to #hashtags</label></th>
					<td><input id="hashlinks" name="wptf_pro[hashlinks]" type="checkbox" value="yes" <?php checked('yes', $options['hashlinks']); ?> /> <small>Inserts a link to any #hashtag that is mentioned in a tweet</small></td>
				</tr>
			</table>

			<h3 class="title">Timeline Options</h3>
			<table class="form-table">
				<tr valign="top"><th scope="row"><label for="timeline">Show Timestamp</label></th>
					<td><input id="timeline" name="wptf_pro[timeline]" type="checkbox" value="yes" <?php checked('yes', $options['timeline']); ?> /> <small>Whether you want the tweet to append xx minutes/hours/days ago from the tweet</small></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="linktotweet">Link to Tweet</label></th>
					<td><input id="linktotweet" name="wptf_pro[linktotweet]" type="checkbox" value="yes" <?php checked('yes', $options['linktotweet']); ?> /> <small>this will turn the timestamp into a a link to the Tweet</small></td>
				</tr>
				<tr valign="top"><th scope="row">Conditional Tweet Text</th>
					<td>
<input id="tprefix" type="text" name="wptf_pro[tprefix]" value="<?php echo $options['tprefix']; ?>" size="5" /> XX
<input id="tsuffix" type="text" name="wptf_pro[tsuffix]" value="<?php echo $options['tsuffix']; ?>" size="5" />
					</td>
				</tr>
				<tr valign="top"><th scope="row"><label for="usephptime">Use PHP Timestamp</label></th>
					<td><input id="usephptime" name="wptf_pro[usephptime]" type="checkbox" value="yes" <?php checked('yes', $options['usephptime']); ?> /> <small>if ticked, the timestamp will be in date format instead of xx minutes/hours/days ago from now</small></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="phptime">PHP Timestamp format</label></th>
					<td><input id="phptime" type="text" name="wptf_pro[phptime]" value="<?php echo $options['phptime']; ?>" /> <small>The PHP date format to output if the above option is ticked. More formats can be found <a href="http://php.net/manual/en/function.date.php" target="_blank">here</a></small></td>
				</tr>
			</table>

			<h3 class="title">CSS Styling Options</h3>
			<table class="form-table">

				<tr valign="top"><th scope="row"><label for="useCSS">Use CSS file</label></th>
					<td><input id="usephptime" name="wptf_pro[useCSS]" type="checkbox" value="yes" <?php checked('yes', $options['useCSS']); ?> /> <small>disable this to use your own CSS file to style the classes in the next option</small></td>
				</tr>
				<tr valign="top"><th scope="row"><label for="twitterJS">Tweet CSS classes</label></th>
					<td><small>Tweets are output in a HTML list. Here, you can have an option to give the HTML list CSS classes which you then customise within your theme's design</small><br />
<code>
&#60;ul class="</code><input id="ulclass" type="text" name="wptf_pro[ulclass]" value="<?php echo $options['ulclass']; ?>" size="10" /><code>"&#62;<br />
&#60;li class="</code><input id="liclass" type="text" name="wptf_pro[liclass]" value="<?php echo $options['liclass']; ?>" size="10" /><code>"&#62;This is a tweet<br />&#60;span class="</code><input id="timelineclass" type="text" name="wptf_pro[timelineclass]" value="<?php echo $options['timelineclass']; ?>" size="10" /><code>"&#62;timeline to tweet&#60;/span&#62;<br />&#60;span class="</code><input id="intentclass" type="text" name="wptf_pro[intentclass]" value="<?php echo $options['intentclass']; ?>" size="10" /><code>"&#62;<br />&#60;a href="reply/retweet/favorite"&#62;&#60;img src="reply/retweet/favorite icons"&#62;&#60;/a&#62;&#60;/span&#62;&#60;/li&#62;&#60;/ul&#62;
</code></td>
				</tr>
			</table>


			<p class="submit">
			<?php submit_button(); ?>
			</p>
		</form>



               <div class="pea_admin_box">
			<h3 class="title">Using the Shortcode</h3>
			<table class="form-table">
				<tr valign="top"><td>
					<p>You can insert a Twitter Feed Pro manually in any page, post or template. Here's an example of using the shortcode:<br><code>[twitter-feed]</code></p>
					<p>You can also insert the shortcode directly into your theme with PHP:<br><code>&lt;?php echo do_shortcode('[twitter-feed]'); ?&gt;</code></p>
					<p>There are a number of options you can choose in the shortcode. You can either use the settings above or override them using the following options:</p>
					<ul>
					<li><strong>username</strong></li>
					<li><strong>mode</strong> feed/fav/search/hashtag</li>
					<li><strong>twitterJS</strong></li>
					<li><strong>lang</strong></li>
					<li><strong>num</strong> - number of tweets to output (maximum 200)</li>
					<li><strong>term</strong> - if "search" mode is chosen, this is the search term</li>
					<li><strong>hashtag</strong> - if "hashtag" mode is chosen, this is the hashtag</li>
					<li><strong>errormessage</strong> - if there are no tweets, or a failed connection, enter an error message</li>
					<li><strong>linklove</strong> - yes/no to provide a link back to the WordPress plugin homepage</li>
					<li><strong>img</strong> - show user profile pictures</li>
					<li><strong>followbutton</strong> - yes/no</li>
					<li><strong>timeline</strong> - yes/no</li>
					<li><strong>linktotweet</strong> - yes/no</li>
					<li><strong>usephptime</strong> - yes/no</li>
					<li><strong>ulclass</strong></li>
					<li><strong>liclass</strong></li>
					<li><strong>intentclass</strong></li>
					<li><strong>timelineclass</strong></li>
					<li><strong>near</strong> - geolocate tweets by location</li>
					<li><strong>within</strong> - the number of miles </li>
					</ul>
					<p>And here's an example of using a selection of options:<br><code>[twitter-feed username="alexmoss" num="1" ulclass="alexmoss-feed" linktotweet="no" linklove="yes"]</code></p>
					</td>
				</tr>
			</table>
</div>
<?php } ?>
</div>
</div>
            <div class="pea_admin_main_right">
                 <div class="pea_admin_box">

            <center><a href="http://peadig.com/?utm_source=<?php echo $domain; ?>&utm_medium=referral&utm_campaign=Twitter%2BFeed%2BPro%2BAdmin" target="_blank"><img src="<?php echo plugins_url( 'images/peadig-landscape-300.png' , __FILE__ ); ?>" width="220" height="69" title="Peadig">
            <strong>Peadig: the WordPress framework that Integrates Bootstrap</strong></a><br /><br />
            <a href="https://twitter.com/peadig" class="twitter-follow-button">Follow @peadig</a>
			<div class="fb-like" data-href="http://www.facebook.com/peadig" data-layout="button_count" data-action="like" data-show-faces="false"></div>
<div class="g-follow" data-annotation="bubble" data-height="20" data-href="//plus.google.com/116387945649998056474" data-rel="publisher"></div>
<br /><br /><br />


                </div>


                   <center> <h2>Share the plugin love!</h2>
                    <div id="fb-root"></div>
                    <script>(function(d, s, id) {
                      var js, fjs = d.getElementsByTagName(s)[0];
                      if (d.getElementById(id)) return;
                      js = d.createElement(s); js.id = id;
                      js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
                      fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));</script>
                    <div class="fb-like" data-href="<?php echo WPTF_URL; ?>" data-layout="button_count" data-show-faces="true"></div>

                    <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo WPTF_URL; ?>" data-text="Just been using <?php echo WPTF_NAME; ?> #WordPress plugin" data-via="<?php echo WPTF_AUTHOR_TWITTER; ?>" data-related="WPBrewers">Tweet</a>
                    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>

<a href="http://bufferapp.com/add" class="buffer-add-button" data-text="Just been using <?php echo WPTF_NAME; ?> #WordPress plugin" data-url="<?php echo WPTF_URL; ?>" data-count="horizontal" data-via="<?php echo WPTF_AUTHOR_TWITTER; ?>">Buffer</a><script type="text/javascript" src="http://static.bufferapp.com/js/button.js"></script>
                    <div class="g-plusone" data-size="medium" data-href="<?php echo WPTF_URL; ?>"></div>
                    <script type="text/javascript">
                      window.___gcfg = {lang: 'en-GB'};

                      (function() {
                        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                        po.src = 'https://apis.google.com/js/plusone.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                      })();
                    </script>
                    <su:badge layout="3" location="<?php echo WPTF_URL?>"></su:badge>
                    <script type="text/javascript">
                      (function() {
                        var li = document.createElement('script'); li.type = 'text/javascript'; li.async = true;
                        li.src = ('https:' == document.location.protocol ? 'https:' : 'http:') + '//platform.stumbleupon.com/1/widgets.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(li, s);
                      })();
                    </script>
<br /><br />
<a href="<?php echo WPTF_DONATE_LINK; ?>" target="_blank"><img class="paypal" src="<?php echo plugins_url( 'images/paypal.gif' , __FILE__ ); ?>" width="147" height="47" title="Please Donate - it helps support this plugin!"></a></center>

                <div class="pea_admin_box">
                    <h2>About the Author</h2>

                    <?php
                    $default = "http://reviews.evanscycles.com/static/0924-en_gb/noAvatar.gif";
                    $size = 70;
                    $alex_url = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( "alex@peadig.com" ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;
                    ?>

                    <p class="pea_admin_clear"><img class="pea_admin_fl" src="<?php echo $alex_url; ?>" alt="Alex Moss" /> <h3>Alex Moss</h3><br />Alex Moss is the Co-Founder of <a href="http://peadig.com/" target="_blank">Peadig</a>, a WordPress framework built with Bootstrap. He has also developed several WordPress plugins (which you can <a href="http://peadig.com/wordpress-plugins/?utm_source=<?php echo $domain; ?>&utm_medium=referral&utm_campaign=Twitter%2BFeed%2BPro%2BAdmin" target="_blank">view here</a>) totalling over 500,000 downloads.</p>
<center><br><a href="https://twitter.com/alexmoss" class="twitter-follow-button">Follow @alexmoss</a>
<div class="fb-subscribe" data-href="https://www.facebook.com/alexmoss1" data-layout="button_count" data-show-faces="false" data-width="220"></div>
<div class="g-follow" data-annotation="bubble" data-height="20" data-href="//plus.google.com/116608702739714446873" data-rel="author"></div>
</div>

                    <h2>More from Peadig</h2>
    <p class="pea_admin_clear">
                    <?php
$wptf_profeed = wptf_pro_fetch_rss_feed();
                echo '<ul>';
                foreach ( $wptf_profeed as $item ) {
			    	$url = preg_replace( '/#.*/', '', esc_url( $item->get_permalink(), $protocolls=null, 'display' ) );
					echo '<li>';
					echo '<a href="'.$url.'?utm_source='.$domain.'&utm_medium=RSS&utm_campaign=Twitter%2BFeed%2BPro%2BAdmin" target="_blank">'. esc_html( $item->get_title() ) .'</a> ';
					echo '</li>';
			    }
                echo '</ul>';
                    ?></p>


            </div>
        </div>
    </div>




<?php
}

?>