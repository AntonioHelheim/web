<?php
define('WP_CACHE', true); // Added by SpeedyCache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'tecaivot_wp908' );

/** Database username */
define( 'DB_USER', 'tecaivot_wp908' );

/** Database password */
define( 'DB_PASSWORD', 'b.x5A[S2p8' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'nyqdpjcnwwppcm2skvnbo9pklmehar810f6vhqiwhvepkr1mwracvg24bcqyhkm6' );
define( 'SECURE_AUTH_KEY',  'uihzekleupxxh35pcdvj9ssb8p7mfbhvyda4qmacwsarduvvmwdeskttz0wn26hl' );
define( 'LOGGED_IN_KEY',    'jc646v1qnqipp7eizdfnicpjmj708fj0owdi4kp0ggi903btcn7duzgfhutbubwc' );
define( 'NONCE_KEY',        '6tykdzqc8sisld02idfr6ftybgprrmokvksqstwxsguq4xzdorqmnl3x72boes4m' );
define( 'AUTH_SALT',        'xrm92wtsyj7gdndrfrvx0emziimywvudhffdxalhaqe803flxsdo189h9wbmwlx7' );
define( 'SECURE_AUTH_SALT', 'ho75xppfbli0d1zr16xutl1iddklhwznnq0lsixd0xvnmfxgm0vihrbxlza1lszu' );
define( 'LOGGED_IN_SALT',   'knifrg0drdyeytdesq4yblcawdilan29ayu5gztnzw0mpqpvdjyhnl41s4r6t93h' );
define( 'NONCE_SALT',       'ncteeu5dgfgn0e49vrgdseqwwmihcablrm2y09yvrdsmbhczjbben67n3siopz1a' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp7j_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
