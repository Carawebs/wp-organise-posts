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

use Carawebs\OrganisePosts\DisplayPosts;

/**
* @author  David Egan <david@carawebs.com>
* @license http://opensource.org/licenses/MIT MIT
* @package OrganisePosts
*/
class Controller {
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
  * @return void
  */
  public function setupBackendActions( Config $config ) {

    if ( ! $this->isAdmin ) { return; }

    $this->loadTextDomain();

    $this->cptScreen = new CPTScreen();
    $this->termScreen = new TermScreen();

    add_action( 'wp_ajax_organise_posts_cpt_screen', [ $this->cptScreen, 'ajax_organise_posts_ordering' ] ); // hooking to `load-edit.php` is too late
    add_action( 'wp_ajax_organise_posts_term_screen', [ $this->termScreen, 'ajax_organise_posts_ordering'] ); // hooking to `load-edit.php` is too late

    add_action( 'load-edit.php', [ $this, 'load_edit_screen'] );

  }

  /**
   * Setup Frontend hooks
   *
   * @param  \Carawebs\OrganisePosts\Config $config Config data object
   * @return void
   */
  public function setupFrontendActions( Config $config ) {

    add_filter( 'pre_get_posts', [ new Frontend\DisplayPosts(), 'display_posts' ] );

  }

  /**
   * Load up page ordering scripts for the edit screen
   */
  public function load_edit_screen() {

    $cptScreen = ! empty( $cptScreen ) ? $cptScreen : $this->cptScreen;
    $termScreen = ! empty( $termScreen ) ? $termScreen : $this->termScreen;

    // Determine post type from the screen object
    $screen = get_current_screen();

    $current_term = isset( $_GET['project-category'] ) ? $_GET['project-category'] : NULL;
    $post_type = $screen->post_type;

    // is post type sortable?
    $sortable = ( post_type_supports( $post_type, 'page-attributes' ) || is_post_type_hierarchical( $post_type ) );		// check permission
    if ( ! $sortable = apply_filters( 'simple_page_ordering_is_sortable', $sortable, $post_type ) ) {
      return;
    }

    // does user have the right to manage these post objects?
    if ( ! $this->check_edit_others_caps( $post_type ) ) {
      return;
    }

    // Is this an excluded edit screen?
    // The strings in this array are checked against $_GET elements on this screen
    $excluded_screens = [ 'project-category', 'category_name', 'tag' ];
    $custom_tax_screen = array_filter( $excluded_screens, function ( $excluded_screen ) {

      return isset( $_GET[$excluded_screen] );

    });

    if( empty( $custom_tax_screen ) ) {

      add_action( 'wp_ajax_simple_page_ordering', [ $cptScreen, 'ajax_organise_posts_ordering' ] ); // The AJAX callback
      add_filter( 'views_' . $screen->id,         [ $cptScreen, 'sort_by_order_link' ] );           // Add view by menu order to views
      add_action( 'wp',                           [ $cptScreen, 'wp' ] );                           //
      add_action( 'admin_head',                   [ $cptScreen, 'admin_head' ] );

    } else if( in_array( 'project-category', $custom_tax_screen ) ) {

      //$termScreen->set_term( $current_term );

      add_action( 'pre_get_posts',        [ $termScreen, 'custom_order' ] );
      add_filter( 'views_' . $screen->id, [ $termScreen, 'sort_by_order_link' ] );  // add view by menu order to views
      add_action( 'wp',                   [ $termScreen, 'wp' ] );
      add_action( 'admin_head',           [ $termScreen, 'admin_head' ] );

      var_dump( $termScreen );

    }

  }

  /**
   * Checks to see if the current user has the capability to "edit others" for a post type
   *
   * @param string $post_type Post type name
   * @return bool True or false
   */
  private function check_edit_others_caps( $post_type ) {

    $post_type_object = get_post_type_object( $post_type );
    $edit_others_cap = empty( $post_type_object ) ? 'edit_others_' . $post_type . 's' : $post_type_object->cap->edit_others_posts;
    return apply_filters( 'simple_page_ordering_edit_rights', current_user_can( $edit_others_cap ), $post_type );

  }

  /**
   * Load Text Domain
   * @return void
   */
  private function loadTextDomain() {

    $pathArr = explode( DIRECTORY_SEPARATOR, dirname(__DIR__) );
    load_plugin_textdomain( 'organise-posts', false, end($pathArr).'/lang' );

  }

}
