/**
 * WordPress Configuration Template
 * 
 * Copy this file to wp-config.php and update the values
 */

// ** Database settings ** //
define('DB_NAME', getenv('DB_NAME') ?: 'wordpress_db');
define('DB_USER', getenv('DB_USER') ?: 'wordpress_user');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'your_password_here');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// ** Authentication Unique Keys and Salts ** //
// Generate these from: https://api.wordpress.org/secret-key/1.1/salt/
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

// ** WordPress Database Table prefix ** //
$table_prefix = getenv('TABLE_PREFIX') ?: 'wp_';

// ** Performance Settings ** //
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
define('WP_CACHE', true);

// ** Security Settings ** //
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);
define('WP_AUTO_UPDATE_CORE', 'minor');

// ** Debug Settings ** //
define('WP_DEBUG', getenv('WP_DEBUG') === 'true');
define('WP_DEBUG_LOG', getenv('WP_DEBUG_LOG') === 'true');
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', getenv('SCRIPT_DEBUG') === 'true');

// ** Multisite Settings (optional) ** //
// define('WP_ALLOW_MULTISITE', true);
// define('MULTISITE', true);
// define('SUBDOMAIN_INSTALL', false);
// define('DOMAIN_CURRENT_SITE', 'example.com');
// define('PATH_CURRENT_SITE', '/');
// define('SITE_ID_CURRENT_SITE', 1);
// define('BLOG_ID_CURRENT_SITE', 1);

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
