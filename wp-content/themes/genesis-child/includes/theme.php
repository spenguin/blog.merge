<?php

namespace Theme;

/**
 *  Theme Setup
 *
 *  This setup function attaches all of the site-wide functions
 *  to the correct actions and filters. All the functions themselves
 *  are defined below this setup function.
 *
 */

	

	// Start the Engine
require_once( get_template_directory() . '/lib/init.php' );

//* We tell the name of our child theme
define( 'Child_Theme_Name', __( 'Genesis Child', 'genesis-child' ) );
//* We tell the web address of our child theme (More info & demo)
//define( 'Child_Theme_Url', 'http://gsquaredstudios.com' );
//* We tell the version of our child theme
define( 'Child_Theme_Version', '1.0' );

//* Add HTML5 markup structure from Genesis
add_theme_support( 'html5' );

//* Add HTML5 responsive recognition
add_theme_support( 'genesis-responsive-viewport' );

// Add support for custom header.
add_theme_support( 'custom-header', array(
	'width'           => 600,
	'height'          => 160,
	'header-selector' => '.site-title a',
	'header-text'     => false,
	'flex-height'     => true,
) );





/*add_action( 'genesis_entry_header', '\Theme\post_image' );

function post_image()
{
	global $post;

	echo get_the_post_thumbnail( $post->ID, 'thumbnail' );
}*/




add_action( 'genesis_before_content_sidebar_wrap', '\Theme\before_content' );
add_action( 'genesis_after_content_sidebar_wrap', '\Theme\after_content' );

/**
	Adds Social Links above Sidebar
*/
function before_content()
{
	$context['url']	= './wp-content/themes/genesis-child/';	// [FIX] - need a better path

	\Timber::render( 'content-before-content-sidebar-wrap.twig', $context );
}

function after_content()
{
	\Timber::render_string( '</div>' );
}


/**
	Modify the loop
*/

remove_action( 'genesis_loop', 'genesis_do_loop' );

add_action( 'genesis_loop', '\Theme\new_loop' );

function new_loop()
{
	$context	= \Timber::get_context();

	$context['posts']	= \Timber::get_posts();

	if( FALSE !== strpos( $context['body_class'], 'single-post' ) )
	{
		\Timber::render( 'single.twig', $context );
	}
	else
	{
		\Timber::render( 'loop.twig', $context );
	}

}

//add_action( 'genesis_before_sidebar_widget_area', '\Theme\before_sidebar_widget_area' );

/**
 *  Adds content to the Area Before Sidebar Widgets
 */
function before_sidebar_widget_area()
{
	\Timber::render( 'content-before-sidebar-widget-area.twig', array() );
}


/**
	Footer modifications
*/
remove_action( 'genesis_footer', 'genesis_do_footer' ); // Removes Genesis footer from theme

add_action( 'genesis_footer', '\Theme\new_footer', 6 );

function new_footer()
{
	$context['menu']	= new \TimberMenu( 'Header menu 2' ); //wp_nav_menu( array( 'menu' => 'Header menu 2' ) );
	$context['url']		= './wp-content/themes/genesis-child/';	// [FIX] - need a better path

	\Timber::render( 'footer.twig', $context );
}



add_action( 'genesis_after_footer', '\Theme\sub_footer', 6 );

function sub_footer()
{
	?>
	<div class="copyright"><center>Copyright &copy; <?php echo date( 'Y' ); ?> 123 Reg Ltd.</center></div>
	<?php
}