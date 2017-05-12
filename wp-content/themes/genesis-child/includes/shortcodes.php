<?php
/**
	Shortcodes
*/
namespace Shortcodes;

\Shortcodes\register();

function register(){
   add_shortcode( 'login', '\Shortcodes\login' );
//   add_shortcode( 'twitter-feed', '\Shortcodes\twitterFeed' );
}


function login()
{
	$o	= array();
	$o[]	= '<form>';
	$o[]	= '<input type="text" name="username" />';
	$o[]	= '<input type="submit" value="submit" name="submit" />';
	$o[]	= '</form>';

	return join( "\r\n", $o );
}

function twitterFeed()
{
	return NULL;
}