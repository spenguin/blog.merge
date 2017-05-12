<?php
/**
	Search form
*/
namespace Search;

\Search\register();

function register()
{
	
	// Front End hooks
	add_filter( 'wp_nav_menu_items','\Search\addSearchToWpMenu',10,2 );

	// CMS hooks
	add_action( 'admin_menu', '\Search\MySearchFormPage' );
    add_action( 'admin_init', '\Search\registerSearchFormOptions' );	

}

/**
	Renders and attaches Search Form in Primary Nav as nav element
*/
function addSearchToWpMenu( $items, $args ) 
{
	if( 'primary' === $args->theme_location ) 
	{
		$searchStr	= get_option( 'searchInputFieldValue', 'Search all Posts!' );
		$items .= '<li class="menu-item menu-item-search small-5 columns">';
		$items .= '<form method="get" class="menu-search-form" action="' . get_bloginfo( 'url' ) . '/"><input class="text_input" type="text" value="' . $searchStr . '" name="s" id="s" onfocus="if (this.value == \'' . $searchStr . '\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'' . $searchStr . '\';}" /><input type="submit" class="my-wp-search fa fa-search" value="&#xf002;" /></form>';
		$items .= '</li>';
	}
	return $items;
}

function MySearchFormPage() {
	add_options_page( 
		'searchForm',
		'Search Form',
		'manage_options',
		'searchFormOptions',
		'\Search\searchFormOptionsDisplay'
	);
}

function registerSearchFormOptions()
{
	register_setting( 'searchForm', 'searchInputFieldValue', '\Search\checkValueString' );
}


/**
	Validate Value string for Search Form
*/
function checkValueString( $array )
{
	return $array;
}


function searchFormOptionsDisplay()
{
	require_once TWIG_PATH . 'admin/searchFormOptionsDisplay.php';	
}