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
/** The name of the database for WordPress */
define( 'DB_NAME', 'earthsigndesign' );

/** MySQL database username */
define( 'DB_USER', 'wp' );

/** MySQL database password */
define( 'DB_PASSWORD', 'wp' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          ',FsduP{A7gSXeTS)eC#N!-~rvuru|y%=s_#ig9*7XS];)[-@;i_b)kkKl+ ;61R9' );
define( 'SECURE_AUTH_KEY',   'egN?ELt5#POXyh[G;vk:z}O&2.aSA}<%n:Z9#Cr`JfA|n]U.t;y&8],3Rj gyIv*' );
define( 'LOGGED_IN_KEY',     'e%.X}SAEO=<4rJW>zr0dUMR~H[`@1aw}0(qs!xN1~N|nH3m@k^n6 /*iPWVECvRQ' );
define( 'NONCE_KEY',         'x8!gauzHf|*EK!+3@9Vo{C.on#P7z{si9~P4-p-s68DC:d*p4F0CBR #n[ J,n}E' );
define( 'AUTH_SALT',         '&smU2VVYU1eXBtN3iaSO[Z@Q,<FJkpMnA&Bx{|3q@?+Mzk/On??Am{?ezIF|Ico,' );
define( 'SECURE_AUTH_SALT',  'BOhY]ZD&AM|rH::#=LD03 @j%51+tL/6G8(=s??ORj a>VeLZg|&H/;vk+5Zw356' );
define( 'LOGGED_IN_SALT',    '0Aw*(4e:S$qTLg1W$(U}*AbnsRf|N1uNm>fVSxzHopO%%u[_>q~6M7[d=:.wgA[T' );
define( 'NONCE_SALT',        'L+jv3,w^08fgBgGhVbAP4JK:_jUc< w]6IjrD]@nt0c&]Z.o<Y+}HnGbNK&`G/& ' );
define( 'WP_CACHE_KEY_SALT', 'DQb?~B9fP9ESjtMM<ukUBrH~N&kc<X)`<trqwNY-2?ed93[y2ESD[U*`]^3x!=^V' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


define( 'WP_DEBUG', true );
define( 'SCRIPT_DEBUG', true );


/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
