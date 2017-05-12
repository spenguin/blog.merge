<?php
// ** MySQL settings ** //
define('DB_NAME', 'blog.merge');    // The name of the database
define('DB_USER', 'root');     // Your MySQL username
define('DB_PASSWORD', ''); // ...and password
define('DB_HOST', 'localhost');    // 99% chance you won't need to change this value
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

define('WP_HOME','http://blog.merge/');
define('WP_SITEURL','http://blog.merge/');

// Change SECRET_KEY to a unique phrase.  You won't have to remember it later,
// so make it long and complicated.  You can visit http://api.wordpress.org/secret-key/1.0/
// to get a secret key generated for you, or just make something up.
define('SECRET_KEY', 'put your unique phrase here'); // Change this to a unique phrase.

// You can have multiple installations in one database if you give each a unique prefix
$table_prefix  = '123reg_';   // Only numbers, letters, and underscores please!

// Change this to localize WordPress.  A corresponding MO file for the
// chosen language must be installed to wp-content/languages.
// For example, install de.mo to wp-content/languages and set WPLANG to 'de'
// to enable German language support.
define ('WPLANG', 'en-GB');
$locale='en_GB';

/* That's all, stop editing! Happy blogging. */

define('ABSPATH', dirname(__FILE__).'/');
require_once(ABSPATH.'wp-settings.php');

#define('FORCE_SSL_ADMIN', true);
#define('FORCE_SSL_LOGIN', true);
#if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
  $_SERVER['HTTPS']='off';
#define( 'UPLOADS', 'wp-content/'.'library' );
/** Enable W3 Total Cache */
?>
