<?php
/**
	Widgets
*/

include_once CORE_INC . 'widgets/MobileMenu.php';


add_action( 'widgets_init', '\MobileMenu\registerMobileMenuWidget' );