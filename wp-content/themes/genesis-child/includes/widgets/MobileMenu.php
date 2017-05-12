<?php
/**
	Widget to generate Mobile Menu code
*/

namespace MobileMenu;


// Register the widget.
function registerMobileMenuWidget() { 
 	register_widget( '\MobileMenu\MobileMenuWidget' );
}


class MobileMenuWidget extends \WP_Widget
{
	
	public function __construct() 
	{
	    $widget_options = array( 
		    'classname' => 'mobile-menu',
		    'description' => 'Widget to provide Mobile Menu with required additional HTML',
	    );
	    parent::__construct( 'mobile_menu_widget', 'Mobile Menu Widget', $widget_options );
	}


	// Create the widget output.
	public function widget( $args, $instance ) 
	{
		$menuLocation	= $instance['mobileMenuWidgetMenu'];

		echo $args['before_widget']
		?>
<!--		<a class="nav-toggle-xs" href="#">MENU <span id="menu-icon-xs" class="menu-icon-xs"></span></a>-->
		<?php
			wp_nav_menu( array( 'menu' => $menuLocation ) );

	    	echo $args['after_widget'];
	}

  
	// Create the admin area widget settings form.
	public function form( $instance ) 
	{
	    $MobileMenuWidgetMenu	= !empty( $instance['mobileMenuWidgetMenu'] ) ? $instance['mobileMenuWidgetMenu'] : '';
	    $menus					= get_registered_nav_menus();
	    ?>
	    <p>
	    	<select name="mobileMenuWidgetMenu">
		    	<?php
		    		foreach( $menus as $location => $description ): ?>
		    			<option value="<php echo $location; ?>" <?php echo ( $location == $MobileMenuWidgetMenu ) ? 'selected="selected"' : ''; ?> ><?php echo $description; ?></option>
	    		<?php endforeach; ?>
	    	</select>

	    </p>
	    <?php
	}


	// Apply settings to the widget instance.
	public function update( $new_instance, $old_instance ) 
	{
	    $instance = $old_instance;
	    $instance[ 'mobileMenuWidgetMenu' ] = strip_tags( $new_instance[ 'mobileMenuWidgetMenu' ] );
	    return $instance;
	}

}