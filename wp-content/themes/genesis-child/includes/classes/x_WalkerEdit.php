<?php
namespace Ns123blog\WalkerEdit;

/**
 * This class is overwriting the default functionality of showing the WordPress menu
 * in the menu edit mode (Dashboard) which also shows the new custom fields
 *
 * Class WalkerEdit
 * @package 123blog
 * @author John Anderson
 */
class WalkerEdit extends \Walker_Nav_Menu
{
	private $curItem;

	// retrieve the curItem
	function start_lvl( &$output, $depth = 0, $args = array() )
	{
		exit( 'test' );
		var_dump( $output );
	}

	// store the curItem
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 )
	{
		parent::start_el( $output, $item, $depth = 0, $args = array(), $id = 0 );
		$this->curItem	= $item;
	}

}