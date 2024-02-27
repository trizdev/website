<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'portfolio-v2');

/** MySQL database username */
define('DB_USER', 'portfolio-v2');

/** MySQL database password */
define('DB_PASSWORD', '2gPNx53TtsLIuKy');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ';A*E Mb0.CZ9G-R~7A^$]w7Z?1wl:K$5-({^pJ.ncP?Ot+**0-;=mcARCI(R|`!D');
define('SECURE_AUTH_KEY',  'T5m^9^.0jogI73h`DTvx-15Q zwV]>[yE@Ksq)e9^<p*Y,y{Y=a3plF0F2,$^:5|');
define('LOGGED_IN_KEY',    'x;pADNArIu#>7|q:v6 +_1:.>jYWTrg kzW>a15l|jW8}3-U<pZofnjBlvH9eZ.{');
define('NONCE_KEY',        '!l@fL9Z{xg%%H~fZ|-2)%:.X=k;JJdnDl-ELB9AV^1+hN]Ma_3%jstBM|f+49tnJ');
define('AUTH_SALT',        '{~TB=c(AV%0~m?[x&9Q!aThMHY^:PGc=-w/0oo-Yg``WoWGF^3^2I+^~|tv#7zL}');
define('SECURE_AUTH_SALT', 'N=f0qhnH&Q}>{BlX~@! ?`BNvJl]-,|ZT&kAI$vWi{$c)vSVWG<Zzv&Jf_FYUO`!');
define('LOGGED_IN_SALT',   '%?,|+Jno3SJyx8LZ-g@|o/k_c+#~HkJ7)zh1Y%UMw:`~zw2|bq)hdm(A523oj(]Q');
define('NONCE_SALT',       '?XJpbl.?Kh67$D-xipWMnp)dw//*-3&)2<AfF?T!UdPCWa|mQ[Drf6}ATVsmyadf');


/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = "wp_39_";

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

define( 'WP_POST_REVISIONS', 5 );

/** Disable the Plugin and Theme Editor **/
define( 'DISALLOW_FILE_EDIT', true );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
