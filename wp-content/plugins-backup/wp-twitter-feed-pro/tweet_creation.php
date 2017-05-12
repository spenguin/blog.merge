<?php
	if (empty($tweet['text'])) {
		global $user_ID;
		if( $user_ID ) {
			if(current_user_can('level_10')) {
				$wptf_probox = '<p style="color:red;text-shadow: 1px 1px #cecece;">Something isn\'t right. <a href="'.get_admin_url().'options-general.php?page=wptf_pro">Click here</a> go to the settings page.</p>';
			}
		} else {
			$wptf_probox = '<p>'.$options['$errormessage'].'</p>';
		}
	} else {
	if ($HTTPSimg=="yes") {
		$img=$tweet['user']['profile_image_url_https'];
	} else {
		$img=$tweet['user']['profile_image_url'];
	}
	if ($anchor != "") {
		$tweet['text'] = preg_replace("/(http:\/\/)(.*?)\/([\w\.\/\&\=\?\-\,\:\;\#\_\~\%\+]*)/", "<a href=\"\\0\" rel=\"external nofollow\">".$anchor."</a>", $tweet['text']);
	} else {
		$tweet['text'] = preg_replace("/(http:\/\/)(.*?)\/([\w\.\/\&\=\?\-\,\:\;\#\_\~\%\+]*)/", "<a href=\"\\0\" rel=\"external nofollow\">\\0</a>", $tweet['text']);
}
	if ($hashlinks=="yes") {
		$tweet['text'] = preg_replace("(#([a-zA-Z0-9\_]+))", "<a href=\"http://twitter.com/search?q=%23\\1\" rel=\"external nofollow\">\\0</a>", $tweet['text']);
	}

	if ($userlinks=="yes") {
		$tweet['text'] = preg_replace("(@([a-zA-Z0-9\_]+))", "<a href=\"https://twitter.com/intent/user?screen_name=\\1\" rel=\"nofollow\">\\0</a>", $tweet['text']);
	}


	if ($timeline == "yes") {
		$now = time();
		$when = ($now - (strtotime($tweet['created_at'])));

		$posted = "";
		if ($usephptime != "yes") {
					$posted = $tprefix." ".human_time_diff( strtotime($tweet['created_at']), current_time('timestamp') )." ".$tsuffix;
		} else {
			$date = date($options['phptime'], strtotime($tweet['created_at']));
			$posted = $date;
		}
		if ($options['linktotweet'] == "yes") {
			$posted = '<a href="http://twitter.com/'.$tweet['user']['screen_name'].'/statuses/'.$tweet['id_str'].'" rel="external nofollow">'.$posted.'</a>';
		}
	}
}
$wptf_probox .= '<li'.$liclass.'>';
	if ($options['img'] == "yes") {
		$wptf_probox .= '<a href="https://twitter.com/intent/user?screen_name=' . $tweet['user']['screen_name'] . '" rel="nofollow"><img src="' . $img . '" height="48" width="48" alt="' . $tweet['user']['screen_name'] . '" title="' . $tweet['user']['screen_name'] . '" style="float: left;"></a>';
	}
$wptf_probox .= '<p>';
		if (!empty($options['useCSS']) && $options['useCSS'] == 'yes') {
		if (!empty($options['auth']) && $options['auth'] == "yes") {
		$wptf_probox .= '<a href="https://twitter.com/intent/user?screen_name=' . $tweet['user']['screen_name'] . '" rel="nofollow">@' . $tweet['user']['screen_name'] . '</a>: ';
 		}
$wptf_probox .= $tweet['text'] . '</p><span'.$intentclass.'><a href="http://twitter.com/intent/retweet?related=' . $tweet['user']['screen_name'] . '&amp;tweet_id=' . $tweet['id_str'] . '" rel="nofollow"><img src="'.plugins_url( 'images/retweet.png' , __FILE__ ).'" height="16" width="16" alt="Retweet"></a> <a href="http://twitter.com/intent/tweet?related=' . $tweet['user']['screen_name'] . '&amp;in_reply_to=' . $tweet['id_str'] . '" rel="nofollow"><img src="'.plugins_url( 'images/reply.png' , __FILE__ ).'" height="16" width="16" alt="Reply"></a> <a href="http://twitter.com/intent/favorite?related=' . $tweet['user']['screen_name'] . '&amp;tweet_id=' . $tweet['id_str'] . '" rel="nofollow"><img src="'.plugins_url( 'images/favorite.png' , __FILE__ ).'" height="16" width="16" alt="Favorite"></a></span>
<span'.$timelineclass.'>'.$posted.'<br /></span></li>';
}
?>