<?php
/**
 * Plugin Name: NextPress
 * Plugin URI: https://github.com/axistaylor/wpgraphql-next-content-render
 * Description: A WPGraphQL extension. Adds the queries needed to render WP content on a 1:1 scale in a headless environment.
 * Version: 0.0.1
 * Author: kidunot89
 * Author URI: https://axistaylor.com
 * Text Domain: nextpress
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * WPGraphQL requires at least: 1.27.0+
 *
 * @package     NextPress
 * @author      kidunot89
 * @license     GPL-3
 */

namespace NextPress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Setups WPGraphQL for WooCommerce constants
 *
 * @return void
 */
function constants() {
	// Plugin version.
	if ( ! defined( 'NEXTPRESS_VERSION' ) ) {
		define( 'NEXTPRESS_VERSION', '0.0.1' );
	}
	// Plugin Folder Path.
	if ( ! defined( 'NEXTPRESS_PLUGIN_DIR' ) ) {
		define( 'NEXTPRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}
	// Plugin Folder URL.
	if ( ! defined( 'NEXTPRESS_PLUGIN_URL' ) ) {
		define( 'NEXTPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}
	// Plugin Root File.
	if ( ! defined( 'NEXTPRESS_PLUGIN_FILE' ) ) {
		define( 'NEXTPRESS_PLUGIN_FILE', __FILE__ );
	}
	// Whether to autoload the files or not.
	if ( ! defined( 'NEXTPRESS_AUTOLOAD' ) ) {
		define( 'NEXTPRESS_AUTOLOAD', true );
	}
}

/**
 * Returns path to plugin root directory.
 *
 * @return string
 */
function get_plugin_directory() {
	return trailingslashit( NEXTPRESS_PLUGIN_DIR );
}

/**
 * Returns path to plugin "includes" directory.
 *
 * @return string
 */
function get_includes_directory() {
	return trailingslashit( NEXTPRESS_PLUGIN_DIR ) . 'includes/';
}

/**
 * Returns url to a plugin file.
 *
 * @param string $filepath  Relative path to plugin file.
 *
 * @return string
 */
function plugin_file_url( $filepath ) {
	return plugins_url( $filepath, __FILE__ );
}

function load_plugin_includes() {
    $include_directory_path = get_includes_directory();

    require_once $include_directory_path . 'class-model.php';
    require_once $include_directory_path . 'class-dataloader.php';
    require_once $include_directory_path . 'class-schema.php';
}

function init() {
    load_plugin_includes();
    add_filter(
        'graphql_data_loaders', 
        function( $loaders, $context ) {
            $loader                = new Uri_Assets\DataLoader( $context );
            $loaders['uri_assets'] = &$loader;

            return $loaders;
        },
        10,
        2
    );
    new Uri_Assets\Schema();
}
add_action( 'graphql_init', __NAMESPACE__ . '\init' );

function check_dependencies() {
    if ( class_exists( '\WPGraphQL' ) ) {
        return;
    }

    add_action(
        'admin_notices',
        static function ()  {
            ?>
            <div class="error notice">
                <p>
                    <?php esc_html__( 'WPGraphQL must be active for "NextPress" to work', 'nextpress' ); ?>
                </p>
            </div>
            <?php
        }
    );
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\check_dependencies' );

// Load constants.
constants();