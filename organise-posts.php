<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://carawebs.com
 * @since             1.0.0
 * @package           OrganisePosts
 *
 * @wordpress-plugin
 * Plugin Name:       Organise Posts
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
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * Load Composer autoload if available, otherwise register a simple autoload callback.
 *
 * @return void
 */
function autoload() {

  static $done;

  // Go ahead if $done == NULL or the class doesn't exist
  if ( ! $done && ! class_exists( 'Carawebs\OrganisePosts\Plugin', true ) ) {

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

  $customWPMenu = new \Carawebs\OrganisePosts\Settings\Menu( array(
			'slug' => 'test_wpmenu',
			'title' => 'Organise Posts',
			'desc' => 'Settings for theme custom WordPress Menu',
			'icon' => 'dashicons-welcome-widgets-menus',
			'position' => 99,
      //'function'  => 'add_submenu_page'
		));
  $customWPMenu->add_field(array(
  	'name' => 'text',
  	'title' => 'Text Input',
  	'desc' => 'Input Description' ));

  $customWPMenu->add_field(array(
  	'name' => 'checkbox',
  	'title' => 'Checkbox Example',
  	'desc' => 'Check it to wake it',
  	'type' => 'checkbox'));

  $customWPMenu->add_field(array(
  	'name' => 'radio',
  	'title' => 'Radio Example',
  	'desc' => 'Make a selection',
  	'type' => 'radio',
    'options' => [
      'one' => 'Radio One',
      'two' => 'Radio Two'
      ]
  ));

  // $subMenu = new \Carawebs\OrganisePosts\Settings\SubMenu( array(
	// 		'slug' => 'sub_wpmenu',
	// 		'title' => 'Submenu',
	// 		'desc' => 'Settings for theme custom WordPress Menu',
	// 		'icon' => 'dashicons-welcome-widgets-menus',
	// 		'position' => 99,
	// 	), $customWPMenu);
  // $subMenu->add_field(array(
  // 	'name' => 'text',
  // 	'title' => 'Text Input',
  // 	'desc' => 'Input Description' ));


}

// function hooks() {
//
//   $settings = new \Carawebs\OrganisePosts\Hooks\Settings();
//   new \Carawebs\OrganisePosts\Hooks\AddAction( 'wp_head', $settings );
//
// }
/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
// Nothing more to do on AJAX requests
( defined('DOING_AJAX') && DOING_AJAX) or add_action( 'wp_loaded', function () {

    autoload();

    settings();

    // Controller class is responsible to instantiate objects and attach their methods to proper hooks.
    $controller = new Controller();

    // Instantiate config class
    $config = new Config(
        [ 'plugin-path'   => __FILE__ ],
        SettingsPage::defaults()
    );

    // Setup backend actions
    $controller->setupBackendActions($config);

    // Setup frontend action
    //$controller->setupFrontendActions($config);

});
