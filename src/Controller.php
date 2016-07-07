<?php
/*
* This file is part of the organise-posts package.
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
  *
  * @param \Carawebs\OrganisePosts\Config $config Config data object
  */
  public function __construct( Config $config) {

    $this->config = $config;
    $this->isAdmin = is_admin();

  }

  /**
  * Setup backend hooks.
  * Instantiate necessary objects if necessary.
  *
  * @return void
  */
  public function setupBackendActions() {

    if ( ! $this->isAdmin ) { return; }

    $this->loadTextDomain();
    $this->cptScreen = new CPTScreen();
    $this->termScreen = new TermScreen();

    // NB: hooking wp_ajax actions to `load-edit.php` is too late
    add_action( 'wp_ajax_organise_posts_cpt_screen', [ $this->cptScreen, 'ajax_organise_posts_ordering' ] );
    add_action( 'wp_ajax_organise_posts_term_screen', [ $this->termScreen, 'ajax_organise_posts_ordering'] );
    add_action( 'load-edit.php', [ $this, 'load_edit_screen'] );

  }

  /**
   * Setup Frontend hooks
   *
   * @return void
   */
  public function setupFrontendActions() {

    add_filter( 'pre_get_posts', [ new Frontend\DisplayPosts(), 'display_posts' ] );

  }

  /**
   * Load up page ordering scripts for the edit screen
   */
  public function load_edit_screen() {

    // $wp_list_table = _get_list_table('WP_Posts_List_Table');
    // var_dump($wp_list_table);

    $cptScreen = ! empty( $cptScreen ) ? $cptScreen : $this->cptScreen;
    $termScreen = ! empty( $termScreen ) ? $termScreen : $this->termScreen;
    //new TaxCPTFilter(array('project' => array('project-category')));

    // Determine post type from the screen object
    $screen = get_current_screen();
    error_log( json_encode( $screen ) );

    $current_term = isset( $_GET['project-category'] ) ? $_GET['project-category'] : NULL;
    $post_type = $screen->post_type;

    // is post type sortable?
    $sortable = ( post_type_supports( $post_type, 'page-attributes' ) || is_post_type_hierarchical( $post_type ) );
    if ( ! $sortable = apply_filters( 'simple_page_ordering_is_sortable', $sortable, $post_type ) ) {
      return;
    }

    // does user have the right to manage these post objects?
    if ( ! $this->check_edit_others_caps( $post_type ) ) {
      return;
    }

    // Is this an excluded edit screen?
    // The strings in this array are checked against $_GET elements on this screen
    $taxonomies = get_taxonomies();
    $excluded_screens = [ 'project-category', 'category_name', 'tag' ];

    $custom_tax_screen = array_filter( $excluded_screens, function ( $excluded_screen ) {

      return isset( $_GET[$excluded_screen] );

    });

    error_log( var_export($taxonomies, true) );

    if( empty( $custom_tax_screen ) ) {

      $screenContext = $cptScreen;

      // @see http://wordpress.stackexchange.com/a/582
      // @see https://gist.github.com/mikeschinkel/541505
      //add_action('restrict_manage_posts', [ $cptScreen, 'custom_taxonomy_nav' ] );

      $cpt_actions = [
        'manage_project_posts_columns'        => 'add_menu_order_column',
        'manage_project_posts_custom_column'  => 'show_menu_order_column',
        'wp_ajax_simple_page_ordering'        => 'ajax_organise_posts_ordering',
        'wp'                                  => 'wp',
        'admin_head'                          => 'admin_head'
      ];
      $cpt_filters = [
        'manage_edit-project_sortable_columns'=> 'sortable_menu_order_column',
        'views_' . $screen->id                => 'sort_by_order_link'
      ];

    } else if( in_array( 'project-category', $custom_tax_screen ) ) {

      $screenContext = $termScreen;
      $termScreen->set_term( $current_term );
      $cpt_actions = [
        'manage_project_posts_columns'        => 'add_menu_order_column',
        'manage_project_posts_custom_column'  => 'show_menu_order_column',
        'manage_project_posts_columns'        => 'add_new_project_column',
        'pre_get_posts'                       => 'custom_order',
        'wp'                                  => 'wp',
        'admin_head'                          => 'admin_head'
      ];
      $cpt_filters = [
        'manage_project_posts_custom_column'  => 'term_columns',
        'views_' . $screen->id                => 'sort_by_order_link'
      ];

    } else {

      return;

    }

    $this->load_actions( $screenContext, $cpt_actions );
    $this->load_filters( $screenContext, $cpt_filters );

  }

  public function load_actions( Screen $context, array $actions ) {

    foreach( $actions as $action => $method ) {

      add_action( $action, [ $context, $method ] );

    }

  }

  public function load_filters( Screen $context, array $filters ) {

    foreach( $filters as $filter => $method ) {

      add_action( $filter, [ $context, $method ] );

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
