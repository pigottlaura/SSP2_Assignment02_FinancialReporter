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

// ** MySQL settings ** //
if($_SERVER['SERVER_NAME'] == "localhost"){
    /** The name of the database for WordPress */
    define('DB_NAME', 'ssp2_assignment02');
    /** MySQL database username */
    define('DB_USER', 'root');
    /** MySQL database password */
    define('DB_PASSWORD', '');
    /** MySQL hostname */
    define('DB_HOST', 'localhost');
} else {
    /** The name of the database for WordPress */
    define('DB_NAME', 'db1281003_SSP2_Assignment02');
    /** MySQL database username */
    define('DB_USER', 'u1281003_root2');
    /** MySQL database password */
    define('DB_PASSWORD', 'ABCdef123456');
    /** MySQL hostname */
    define('DB_HOST', '172.16.2.233');
}

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
define('AUTH_KEY',         'zPUP$hG(d7yF4&HYG=Jm7xL&(]p  z[irK2LmT%3dOqDWVb3->)[wKh{]w1;Ycs$');
define('SECURE_AUTH_KEY',  'TzG<6)A[+fIMn$m>[v6VI<2En%UDN|R[IF2P0A/Z1=P2I^t~5>b@/Fcwzltjr6.b');
define('LOGGED_IN_KEY',    '4r1B68G0B2hc_:[ ,C!15(*4-.L Lr$CPNKYhd+IePKW3W`W0z,+TfQKL^>vX$68');
define('NONCE_KEY',        'Yac<0FAoB&u>yFa*h^l)h%)@WzhLa^&2zNX{=B48XSk@sWUnFRck6i2EX1a`+Rt{');
define('AUTH_SALT',        'n 5YX83e_BU=LpCsHt$ol3Rmb}6rqC.G)uS3v!F+$1S)6H(ffA0ibGr2<RF;b)qy');
define('SECURE_AUTH_SALT', '=Es#L7.slY~~t_vt%%<y44qH{mCQ5;aj3G)<#y4M]R@8W# 6Zz?HFG$Xc~;=J-{X');
define('LOGGED_IN_SALT',   'QMUEU2+T&aw_0?5xUb<nA/p)x?=Px^Hu<@hIvOTg@2cmjkMN|# *K%1&G]H)Va.*');
define('NONCE_SALT',       'T&,4a5&gJrBAnc+u|B.4A.Z3D3E!Zxu4HSYe +0Jf>yDa=|y<qos5)O `JZ(pvGV');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
