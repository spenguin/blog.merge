<?php

//ADD TWITTER JS
function wptf_pro_js() {
	$options = get_option('wptf_pro');
	if ($options['twitterJS'] == 'yes' || $options['twitterJS'] == 'on') {
?>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
<?php
	}
}
add_action('wp_head', 'wptf_pro_js');

    function prefix_add_wptf_stylesheet() {
		$options = get_option('wptf_pro');
		if (!empty($options['useCSS']) && $options['useCSS'] == 'yes') {
	        wp_register_style( 'wptf-style', plugins_url('wptf.css', __FILE__) );
    	    wp_enqueue_style( 'wptf-style' );
		}
    }
    add_action( 'wp_enqueue_scripts', 'prefix_add_wptf_stylesheet' );

function wptf_proshortcode($wptf_proatts) {
	$options = get_option('wptf_pro');
	extract(shortcode_atts(array(
		"wptf_pro" => get_option('wptf_pro')
    ), $wptf_proatts));
 	if (!empty($wptf_proatts)) {
        foreach ($wptf_proatts as $key => $option)
            $options[$key] = $option;
	}
	if ($options['consumer_key']=="" || $options['consumer_secret']=="" || $options['access_token']=="" || $options['access_secret']=="") {
		global $user_ID;
		if( $user_ID ) {
			if(current_user_can('level_10')) {
		$wptf_probox = '<p style="color:red;">Twitter is not connecting to the API. <a href="'.get_admin_url().'options-general.php?page=wptf_pro">Click here</a> double check your API settings. If they are correct please wait 24 hours to see if it persists.</p>';
			}
		} else {
			$wptf_probox = '<p>'.$options['errormessage'].'</p>';
			}
	} else {
	require_once 'tmhOAuth/tmhOAuth.php';
	require_once 'tmhOAuth/tmhUtilities.php';
	$tmhOAuth = new tmhOAuth(array(
		'consumer_key'    => $options['consumer_key'],
		'consumer_secret' => $options['consumer_secret'],
		'user_token'      => $options['access_token'],
		'user_secret'     => $options['access_secret'],
	));
	if ($options['ulclass']!="") {$ulclass=' class="'.$options['ulclass'].'"';}
	if ($options['liclass']!="") {$liclass=' class="'.$options['liclass'].'"';}
	if ($options['intentclass']!="") {$intentclass=' class="'.$options['intentclass'].'"';}
	if ($options['timelineclass']!="") {$timelineclass=' class="'.$options['timelineclass'].'"';}

	if (empty($options['mode'])) {$mode='';} else {$mode=$options['mode'];}
	if (empty($options['HTTPSimg'])) {$HTTPSimg='';} else {$HTTPSimg=$options['HTTPSimg'];}
	if (empty($options['followbutton'])) {$followbutton='';} else {$followbutton=$options['followbutton'];}
	if (empty($options['language'])) {$language='';} else {$language=$options['lang'];}
	if (empty($options['userlinks'])) {$userlinks='';} else {$userlinks=$options['userlinks'];}
	if (empty($options['hashlinks'])) {$hashlink='';} else {$hashlinks=$options['hashlinks'];}
	if (empty($options['timeline'])) {$timeline='';} else {$timeline=$options['timeline'];}
	if (empty($options['usephptime'])) {$usephptime='';} else {$usephptime=$options['usephptime'];}
	if (empty($options['tprefix'])) {$tprefix='';} else {$tprefix=$options['tprefix'];}
	if (empty($options['tsuffix'])) {$tsuffix='';} else {$tsuffix=$options['tsuffix'];}
	if (empty($options['usephptime'])) {$usephptime='';} else {$tmonths=$options['usephptime'];}
	if (empty($options['term'])) {$term='';} else {$term=$options['term'];}
	if (empty($options['hashtag'])) {$term='%23';} else {$term='%23'.$options['hashtag'];}
	if (empty($options['num'])) {$num='';} else {$num=$options['num'];}
	if (empty($options['anchor'])) {$anchor='';} else {$anchor=$options['anchor'];}
	if (empty($options['lang'])) {$lang='';} else {$lang=$options['lang'];}
	if (empty($options['followercount'])) {$followercount='';} else {$followercount=$options['followercount'];}
	if ($mode=="mentions") {$term='%40'.$options['username'];}
	if ($mode=="search") {$term=$options['term'];}
	if ($mode=="retweets") {$term='"RT%20%40'.$options['username'].'"';}
	if ($mode=="public") {$term='"'.$options['username'].'%20"';}
	if (empty($options['near'])) {$near='';} else {$near=$options['near'];}
	if (empty($options['within'])) {$within='';} else {$within=$options['within'];}
	if ($near!='') {$term.='near%3A"'.$options['near'].'"';}
	if ($within!='' && $near!='') {$term.='%20within%3A'.$options['within'].'mi';}

	$wptf_probox = '<ul'.$ulclass.'>';

	if ($mode=="fav") {
			$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/favorites/list'), array(
			'screen_name' => $options['username'],
			'lang' => $lang,
			'count'=>$options['num']
		));
	} elseif ($mode=="list") {
			$code = $tmhOAuth->request('GET', $tmhOAuth->url('/1.1/lists/statuses'), array(
			'owner_screen_name' => $options['username'],
			'slug' => $options['list'],
			'lang' => $lang,
			'count'=>$options['num']
		));
	} elseif ($mode=="search" || $mode=="hashtag" || $mode=="public"  || $mode=="mentions" || $mode=="retweets") {
		$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/search/tweets'), array(
			'q' => $term,
			'lang' => $lang,
			'count'=>$options['num']
		));
	} else {
			$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'), array(
			'screen_name' => $options['username'],
			'count'=>$options['num'],
			'lang' => $lang,
			'contributor_details'=>'false'
		));
	}



if ($code != 200) {
		global $user_ID;
		if( $user_ID ) {
			if(current_user_can('level_10')) {
		$wptf_probox = '<p style="color:red;">Twitter Feed API settings are incorrect. <a href="'.get_admin_url().'options-general.php?page=wptf_pro">Click here</a> to check and try again.</p>';
			}
		} else {
				$wptf_probox = '<p>'.$options['errormessage'].'</p>';
			}
} else {

	$output = json_decode($tmhOAuth->response['response'],true);
if ($mode=="search" || $mode=="hashtag" || $mode=="public"  || $mode=="mentions" || $mode=="retweets") {
	foreach($output['statuses'] as $tweet){ 
		include	 'tweet_creation.php';	
	}
} else {
	foreach($output as $tweet){ 
		include	 'tweet_creation.php';
	}
}



}

$wptf_probox .=  '</ul>';


if ($followbutton == "yes" && $mode!="search") {
	if ($options['largebutton'] == "yes") {
		$large=' data-size="large"';
	}
	if ($lang != "en") {
		$language=' data-lang="'.$lang.'"';
	}
	if ($mode=="hashtag") {
		$wptf_probox .=  '<a href="https://twitter.com/intent/tweet?button_hashtag='.$options['hashtag'].'" class="twitter-hashtag-button" data-related="'.$options['username'].'"'.$language.$large.'>Tweet #'.$options['hashtag'].'</a>';
	} else {
		if ($followercount != "yes") {
			$count=' data-show-count="false"';
		} else {
			$count='';
		}
		$wptf_probox .=  '<a href="https://twitter.com/'.$options['username'].'" class="twitter-follow-button"'.$language.$count.$large.'>Follow @'.$options['username'].'</a>';
	}
}


	}
	return $wptf_probox;
}
add_filter('widget_text', 'do_shortcode');
add_shortcode('twitter-feed', 'wptf_proshortcode');

?>