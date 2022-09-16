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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'gp_wetson' );

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
define( 'AUTH_KEY',         'Q8L,kNKjEN[?4d:NN-GM2fgcj#%t7=/_e/5l_%6Ov4}bVH<r(>TXOK<%NN]Qb8JX' );
define( 'SECURE_AUTH_KEY',  '%Ty}A>k49W,JXu(_L:g ~Mf5#mn1i5EQnzZabgh~cmS|Mn:,?]_G%X$|KC9iGanc' );
define( 'LOGGED_IN_KEY',    'A0RM}T$l&eQ7Vn~aS_yBAGsHWDk|yt?yAe,f6/R7W$Dp;mg0gO*X.#]%Zo0,g;Nc' );
define( 'NONCE_KEY',        'cKpo.Ndv}I*S!4/>W1CkcWxsW1dq8@w}&}feIffZ]DH!Hk|,rmh/52,p|FC!D Kb' );
define( 'AUTH_SALT',        'i!Sj#S=pO,dP0>/FqnV|Zr%qN:1ovTJV;k!J>$bt}F|j4Y7~BZB,%_#$JD/^+8^P' );
define( 'SECURE_AUTH_SALT', '#/R]$0[+:,$CthQ)R5B71!`Lz`BG&&ude*tD0rP[{l{vS Lp[2Kz(eo8Y2!G0h`P' );
define( 'LOGGED_IN_SALT',   '|CSeG7711M)!<wGQJS:S^*=d5uV#QYMU,z~WC.OKGMW-t@y~Qoo=~r#&LV6se6$%' );
define( 'NONCE_SALT',       '<L/eRwGH{F_pYUR33*bZNiDrOOtr&L7~aernAWUuc~)aF@LT3WI[&_C.DSaPk.gF' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
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
