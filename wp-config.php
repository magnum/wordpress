<?php

if(file_exists(dirname(__FILE__) . '/wp-config-local.php')) {
    require_once(dirname(__FILE__) . '/wp-config-local.php');
		return;
}


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
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',         'n(</R/W;vmy@)P=<sH4kccAfj[XN$4N/NTd&S,@4=Dk 97cXmor1`hxC6=C]HK^ ' );
define( 'SECURE_AUTH_KEY',  'bFmS,f*vK(rvpRtJGEghc+xab.#F}X1^)$0_aF&&_/TGkc()s=k8JS}79YpyZ^7n' );
define( 'LOGGED_IN_KEY',    'W:w_o1=5/ZVCX9h+z[@<9A5!dGX_E`Y/1RB0*p=dhkm(v:#|V%NaoB{<;NfNm&;b' );
define( 'NONCE_KEY',        'oL(y)~IJ#+XH/p]_vNf8T)I{_+2%b b8sY@LpM~=B|QXw84h>:rL@tQ*)D|snLD~' );
define( 'AUTH_SALT',        'j~PJT-N9LzRdv(pZx(cvLBoYuVN~TExq>.qdSpEW3t[gfU;L,C0Z)N0Am-)y1Mo$' );
define( 'SECURE_AUTH_SALT', 'qm&rpM&ip[Zm=$B^u72kCF<.OWiR%3-|OxwL-O4J{[DDkb[4amX@CBj7&v,)AbQO' );
define( 'LOGGED_IN_SALT',   '`O3Z2tH-hsyRw D2<>[#1|b<,+u{6]cqjdv]!p2l&Vl|;wXq2+vhQpYlkMP=tCX:' );
define( 'NONCE_SALT',       '!uA<J)MI?bJ2jBj%?e/y/V(Ntju8zJf/hQ92+@>voCK2Fqns.e>50=!QMt?e!AU3' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
