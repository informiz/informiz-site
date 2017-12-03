<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'WPCACHEHOME', '/home1/niraamit/public_html/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('WP_CACHE', true); //Added by WP-Cache Manager
define('DB_NAME', 'niraamit_wrdp1');

/** MySQL database username */
define('DB_USER', 'niraamit_wrdp1');

/** MySQL database password */
define('DB_PASSWORD', 'gDg3CH5eQCWPf');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'LYn2iRIH!=gVXqGfr;94M5\`G9TD45mI|(mms9Rfpj?<OSRmC8uftVvOTY=1Mr#<_cr0FijtF\`=vx*zBH~_');
define('SECURE_AUTH_KEY',  'vsPdx$uoiI)VPw9$cTN3=7m^cSvAV)CMiq9h$QyAzhZj6\`sX/qBT^rcHZ|z/E~H/<QohT');
define('LOGGED_IN_KEY',    '|)fTIOgOty=qEvu^PvoSm-tF)/-w2KWi2~1?8hfk$ZqB9Y6>l!00c?;fyzi8WHkJs?');
define('NONCE_KEY',        ')oKgU/*B5$m4rH;dT|lU0<B*CaZ7fYD#siAot0Uz@6Kss=g6lUzietc)H(UD6VUvYB!qn<ysJv:;Qx|W(Q\`8');
define('AUTH_SALT',        'W3@_gjL95(rfv>riCap1lG8vj(rUz!=lqUq63-jUK<-ZXAk\`oXjM7<0Tbz$XH)xIS<Z4sL0:m<');
define('SECURE_AUTH_SALT', '|p3ComttG351\`q!wVFx@cDfxwQ9@~^LA#hm/>=|yY=eb-bKM|l=CY1MGWl7m|T>RKe^xe2fiacZXynj$');
define('LOGGED_IN_SALT',   'GaXiVC*=6T*xpy?f)q6*O3l=f@J!7S0Bl)P3v>Y^t^v0Z5~N:M$2B=J:4$hYx/?~niO(lza?#8ft1');
define('NONCE_SALT',       '!;4hbx\`<R)(^fa7mlPxqGZ?wF_jZPa?;rj=|N53P3@_ZHWv#uCW0(Eb$2sp1a?aIcrE5aw)-cPjK^I;9');

/**#@-*/
define('AUTOSAVE_INTERVAL', 600 );
define('WP_POST_REVISIONS', 1);
define( 'WP_CRON_LOCK_TIMEOUT', 120 );
define( 'WP_AUTO_UPDATE_CORE', true );
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
add_filter( 'auto_update_plugin', '__return_true' );
add_filter( 'auto_update_theme', '__return_true' );
