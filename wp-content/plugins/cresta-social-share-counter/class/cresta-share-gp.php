<?php
/**
 * Stumbleupon Get share
 */
class crestaShareSocialCount {
	private $url;
	private $token;
	function __construct($url) {
		$this->url=rawurlencode($url);
	}
	function get_linkedin() {
		$json_string = $this->get_json_values( 'https://www.linkedin.com/countserv/count/share?url='.$this->url.'&format=json' );
		if (is_wp_error($json_string)) {
			return 0;
		}
		$json = json_decode( $json_string, true );
		$json_result = isset( $json['count'] ) ? intval( $json['count'] ) : 0;
		return ($json_result) ? $json_result : '0';
	}
	function get_facebook($value) {
		$this->token=esc_attr($value);
		$json_string = $this->get_json_values('https://graph.facebook.com/v2.7/?id=' . $this->url . '&access_token=' . $this->token );
		if (is_wp_error($json_string)) {
			return 0;
		}
		$json = json_decode( $json_string, true );
		$json_result = (isset($json['share']['share_count']) ? intval($json['share']['share_count']) : 0);
		return ($json_result) ? $json_result : '0';
	}
	private function get_json_values( $url ) {
		$args            = array( 'timeout' => 6 );
		$response        = wp_remote_get( $url, $args );
		$json_response   = wp_remote_retrieve_body( $response );
		return $json_response;
	}
}

?>