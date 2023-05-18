<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'celr' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         ')%2x.[H5Sk`uYG[k;^_c-K19AVfxn}?gW&1q1.8~T(&]:`e7:H%HTb8,y]H~fap4' );
define( 'SECURE_AUTH_KEY',  'umJNAj %Mi*{^&d@o5  M%CY*{GpK_c$/Ri70;&:t^^<q36B6TdA0`u~.JigNSm8' );
define( 'LOGGED_IN_KEY',    ';i(BLwixG0pNH0B#NU*9)2@i]dfan&9v&{`4.~Cz3.oI:6FYDhSTA|:)~HX<v-9K' );
define( 'NONCE_KEY',        '#62.x?J%qIndik3Z7Np1n)i;U*0c)vVS</0_:D(f>xZDKNr563m*itH=ukAJ5X99' );
define( 'AUTH_SALT',        'tVj6,USebgljv#z1.1T`8.OARsSDS@T|AVQky8{Qf1X?=DvoG+vEAVcdM0:S,oEb' );
define( 'SECURE_AUTH_SALT', 'rRb(<0DRI,[(Enjl4Z36.;<Zln@Dc$qP]u^i}sQ$i!{CsdV>=KHgqE7d5yMb%s8]' );
define( 'LOGGED_IN_SALT',   'rS|;){{$.&];tIzQ4Ah{;2)^$Qo0kVx&3n=aLF|W+gK(s#-sauFX?a)Y9*98&z>W' );
define( 'NONCE_SALT',       '6:$C#8/8oOAQh|`IbcoUnH9tG:<@TGF)@dHCP~$ESGF2>kP/LnQr|5HwVvBUBp8@' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'celr';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
