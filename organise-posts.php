<?php

/**
* The plugin bootstrap file
*
* @link              http://carawebs.com
* @since             1.0.0
* @package           OrganisePosts
*
* @wordpress-plugin
* Plugin Name:       Carawebs Organise Posts
* Plugin URI:        http://carawebs.com
* Description:       Organise posts and custom posts by dragging and dropping into the required order.
* Version:           1.0.0
* Author:            David Egan
* Author URI:        http://davidegan.me
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       organise-posts
* Domain Path:       /languages
*/
namespace Carawebs\OrganisePosts;

use Carawebs\OrganisePosts\Settings;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
* Define constants for this plugin
*/
define( 'CARAWEBS_ORGANISE_POSTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'CARAWEBS_ORGANISE_POSTS_BASE_URL', plugins_url( NULL, __FILE__ ) );
define( 'CARAWEBS_ORGANISE_POSTS_SLUG', 'carawebs_organise_posts' );

/**
* Load Composer autoload if available, otherwise register a simple autoload callback.
*
* @return void
*/
function autoload() {
    static $done;
    // Go ahead if $done == NULL or the class doesn't exist
    if ( ! $done && ! class_exists( 'Carawebs\OrganisePosts\Controller', true ) ) {
        $done = true;
        file_exists( __DIR__.'/vendor/autoload.php' )
        ? require_once __DIR__.'/vendor/autoload.php'
        : spl_autoload_register( function ( $class ) {
            if (strpos($class, __NAMESPACE__) === 0) {
                $name = str_replace('\\', '/', substr($class, strlen(__NAMESPACE__)));
                require_once __DIR__."/src{$name}.php";
            }
        });
    }
}

function settings() {
    // Menu Page
    $settings = new Settings\MenuPage(
        //new Settings\Config('organise-posts', new \Symfony\Component\Yaml\Parser(), 'src/Settings/data2.yml' )
        new Settings\Config('organise-posts' )
    );
}

add_action( 'wp_loaded', function () {
    autoload();
    settings();
    $config = new Config();
    // Controller class is responsible for instantiating objects and attaching their methods to appropriate hooks.
    $controller = new Controller( $config );
    // Setup backend actions
    $controller->setupBackendActions();
    // Setup frontend action
    $controller->setupFrontendActions();
});

require_once( __DIR__."/src/Activator.php" ); // NB: no autoloader at this point!
register_activation_hook( __FILE__, [ new Activator(), 'activate' ] );

require_once( __DIR__."/src/Deactivator.php" );
register_deactivation_hook( __FILE__, [ new Deactivator(), 'deactivate' ] );
