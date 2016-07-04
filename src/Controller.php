<?php
/*
* This file is part of the gm-cookie-policy package.
*
* (c) David Egan <david@carawebs.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Carawebs\OrganisePosts;

/**
* @author  David Egan <david@carawebs.com>
* @license http://opensource.org/licenses/MIT MIT
* @package OrganisePosts
*/
class Controller
{
  /**
  * @var bool
  */
  private $isAdmin;

  /**
  * Controller constructor.
  */
  public function __construct() {

    $this->isAdmin = is_admin();

    //error_log( "From controller: " . json_encode($config['CPTs']) );

  }

  /**
  * Setup backend hooks.
  * Instantiate necessary objects if necessary.
  *
  * @param \Carawebs\OrganisePosts\Config                 $config
  * @param \Carawebs\OrganisePosts\SettingsPage|null      $settings
  * @param \Carawebs\OrganisePosts\RendererInterface|null $renderer
  */
  public function setupBackendActions( Config $config ) {

    if (! $this->isAdmin) {

      return;

    }

    $backend = new Backend();

    $this->loadTextDomain();

    // add_action( 'load-edit.php', function() {
    //   echo "<script>alert('load-edit.php hook')</script>";
    // } );

    add_action( 'load-edit.php', [ $backend, 'load_edit_screen'] );
    //add_action( 'wp_ajax_simple_page_ordering', [ $backend, 'ajax_simple_page_ordering'] );
    add_action( 'wp_ajax_simple_page_ordering', [ $backend, 'ajax_simple_page_ordering'] );

    // add_action( 'wp_ajax_simple_page_ordering', function() {
    //
    //   return "HELLO";
    //
    // } );

  }

  /**
  * Load text domain.
  */
  private function loadTextDomain() {
    // Load text domain
    $pathArr = explode( DIRECTORY_SEPARATOR, dirname(__DIR__) );
    load_plugin_textdomain( 'organise-posts', false, end($pathArr).'/lang' );

  }

}
