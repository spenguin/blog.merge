<?php

namespace Core;
/**
 *  Functions
 *
 * @package 123 Reg Blog
 * @author  John Anderson <john.anderson@godaddy.com>
 *
 */
// Useful global constants
define( 'CORE_URL', get_stylesheet_directory_uri() );
define( 'CORE_TEMPLATE_URL', get_template_directory_uri() . '-child' );
define( 'CORE_PATH', get_template_directory() . '-child/' ); 
define( 'CORE_INC', CORE_PATH . 'includes/' );
define( 'CORE_PARTIALS', CORE_PATH . 'partials/' );
define( 'CORE_TEMPLATES', CORE_PATH . 'templates/' );
define( 'CORE_PLUGINS_PATH', plugins_url() );
define( 'TWIG_PATH', CORE_PATH . '/views/' );

require_once CORE_INC . 'theme.php';
require_once CORE_INC . 'navigation.php';
require_once CORE_INC . 'shortcodes.php';
require_once CORE_INC . 'search.php';
require_once CORE_INC . 'widgets.php';
//require_once CORE_INC . 'classes.php';

\Timber::$locations = TWIG_PATH;

// Show admin bar in the front-end view
// You can remove it if you append /?hegNoAdminBar to the URL
// This will work only if you're logged in
if (isset($_GET['hegNoAdminBar'])) {
	show_admin_bar( false );
//    add_filter('show_admin_bar', '__return_false');
}
