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

remove_action( 'genesis_footer', 'genesis_do_footer' ); // Removes Genesis footer from theme

add_action( 'genesis_after_footer', '\Theme\sub_footer', 6 );

function sub_footer()
{
	?>
	<div class="copyright"><center>Copyright &copy; <?php echo date( 'Y' ); ?> 123 Reg Ltd.</center></div>
	<?php
}

add_action( 'genesis_footer', '\Theme\new_footer', 6 );

function new_footer()
{
	\Timber::render( 'footer.twig', array() );
}


