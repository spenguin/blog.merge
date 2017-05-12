<?php

/**
 * This is an authentication backend that uses a file to manage passwords.
 *
 * The backend file must conform to Apache's htdigest format
 *
 * @package Sabre
 * @subpackage DAV
 * @copyright Copyright (C) 2007-2011 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Sabre_DAV_Auth_Backend_Wordpress extends Sabre_DAV_Auth_Backend_AbstractBasic {

	/**
	 * Creates the backend object.
	 *
	 * If the filename argument is passed in, it will parse out the specified file fist.
	 *
	 * @param string $filename
	 * @param string $tableName The PDO table name to use
	 * @return void
	 */
	public function __construct() {

	}

	protected function validateUserPass($username, $password) {
		filepress_log(0,'Authenticate '.$username);
		$user = wp_authenticate($username, $password);

		if(is_wp_error($user)) return false;
		 
		if (!user_can( $user->ID, 'edit_plugins' )) return false;
		
		filepress_log(0,'Authentication successful');
		
		return true;
	}


}
