<?php

namespace Navigation;
/**
	Navigation code
*/

\Navigation\init();

function init()
{
	add_filter( 'walker_nav_menu_start_el', '\Navigation\headerMenuExt', 10, 4 );	

	add_action( 'init', '\Navigation\register_mobile_menu' );
	add_action( 'genesis_before', '\Navigation\add_mobile_nav_genesis' ); 	


	// Register Mobile Menu Menu link
	add_filter( 'wp_nav_menu','\Navigation\addMobileMenuLink',10,2 );

	// Add Pagination after loop
	add_action( 'genesis_after_loop', '\Navigation\addPagination' );


}


/**
 * Descriptions on Header Menu
 * @author Bill Erickson
 * @link http://www.billerickson.net/code/add-description-to-wordpress-menu-items/
 * 
 * @param string $item_output, HTML outputp for the menu item
 * @param object $item, menu item object
 * @param int $depth, depth in menu structure
 * @param object $args, arguments passed to wp_nav_menu()
 * @return string $item_output
 *
 *	$item parameters of interest:
 *	- menu_item_parent: (int) determines hierarchy
 *	- classes: (array) classes both from CSS Classes field and provided by WP


 */
function headerMenuExt( $item_output, $item, $depth, $args ) 
{	
	
	switch( $args->menu->slug )
	{ 
		case 'header-menu-1':
			if( in_array( 'box-menu', $item->classes ) )
			{
				$item_output = str_replace( '</a>', '<span class="box-top">' . $item->description . '</span></a>', $item_output );
			}
			if( in_array( 'box-item', $item->classes ) )
			{
				
				if( strpos( $item->description, '[' ) !== FALSE )	// Shortcode exists
				{
					$item_output	= do_shortcode( $item->description );
				}
				else
				{
					$item_output	= str_replace( '</a>', '<span class="description">' . $item->description . '</span></a>', $item_output );
				}
			}		

			break;
		case 'mobile-menu':
			if( in_array( 'menu-item-has-children', $item->classes ) )
			{
				$item_output	.= '<input type="checkbox" id="mobile-menu-child-' . $item->ID . '" class="mobile-menu-child" /><label for="mobile-menu-child-' . $item->ID . '" class="show-child"></label>';
			}

		

			break;
	}


	return $item_output;
}


/**
	Register Mobile Menu 
*/
function register_mobile_menu() 
{
	register_nav_menu( 'mobile-menu' ,__( 'Mobile Navigation Menu' ));
}

/**
	Add Mobile Menu location
*/
function add_mobile_nav_genesis() 
{
	wp_nav_menu( array( 'theme_location' => 'mobile-menu', 'container_class' => 'genesis-nav-menu' ) );
}

/**
	Add Mobile Menu Menu link
*/
function addMobileMenuLink( $items, $args )
{
	
	if( 'genesis-nav-menu' == $args->container_class && 'mobile-menu' == $args->menu->slug )
	{	
		$pre	= strstr(  $items, '<ul', TRUE ); 
		$mid	= strstr( $items, '<ul' ); 
		$post	= strrchr( $mid, '<' );
		$mid	= substr( $mid, 0, strripos( $mid, '<' ) ); 

		$items	= $pre . '<label for="mobile-menu" class="show">menu <span id="menu-icon-xs" class="menu-icon-xs"></span></label><input type="checkbox" name="mobile-menu" id="mobile-menu" /><span><label for="mobile-menu" class="hide">Close <span class="close-icon"></span></label>' . $mid . '</span>' . $post;
	}

	return $items;
}


/**
	Insert code into Hook genesis_after_content
*/
function addPagination()
{
	$context = \Timber::get_context();

	switch( $context['body_class'] )
	{
		case ( FALSE !== strpos( $context['body_class'], 'single-post' ) ):
			\Timber::render( 'single-post-pagination-after-content.twig', $context );
			break;
		case ( FALSE !== strpos( $context['body_class'], 'home' ) ):
			\Navigation\wpbeginner_numeric_posts_nav();
	//		genesis_numeric_posts_nav();
	//		\Timber::render( 'pagination-after-content.twig', $context );
			break;		
	} 
}

/**
	Pagination code
*/
function wpbeginner_numeric_posts_nav() {

	if( is_singular() )
		return;

	global $wp_query;

	/** Stop execution if there's only 1 page */
	if( $wp_query->max_num_pages <= 1 )
		return;

	$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
	$max   = intval( $wp_query->max_num_pages );

	/**	Add current page to the array */
	if ( $paged >= 1 )
		$links[] = $paged;

	/**	Add the pages around the current page to the array */
	if ( $paged >= 3 ) {
		$links[] = $paged - 1;
		$links[] = $paged - 2;
	}

	if ( ( $paged + 2 ) <= $max ) {
		$links[] = $paged + 2;
		$links[] = $paged + 1;
	}

	echo '<div class="navigation"><ul class="pagination">' . "\n";

	/**	Previous Post Link */
	if ( get_previous_posts_link() )
		printf( '<li>%s</li>' . "\n", get_previous_posts_link( '<<' ) );

	/**	Link to first page, plus ellipses if necessary */
	if ( ! in_array( 1, $links ) ) {
		$class = 1 == $paged ? ' class="active"' : '';

		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

		if ( ! in_array( 2, $links ) )
			echo '<li class="ellipsis">…</li>';
	}

	/**	Link to current page, plus 2 pages in either direction if necessary */
	sort( $links );
	foreach ( (array) $links as $link ) {
		$class = $paged == $link ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
	}

	/**	Link to last page, plus ellipses if necessary */
	if ( ! in_array( $max, $links ) ) {
		if ( ! in_array( $max - 1, $links ) )
			echo '<li class="ellipsis">…</li>' . "\n";

		$class = $paged == $max ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
	}

	/**	Next Post Link */
	if ( get_next_posts_link() )
		printf( '<li>%s</li>' . "\n", get_next_posts_link( '>>' ) ); 

	echo '</ul></div>' . "\n";

}



