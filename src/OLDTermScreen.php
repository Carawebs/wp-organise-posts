<?php
namespace Carawebs\OrganisePosts;

/**
 * Process AJAX from the CPT term screen
 */
class TermScreen extends Screen {

  private $term_postmeta_key;

  private $custom_taxonomy = 'project-category';

  public function __construct () {


  }

  /**
   * Initialize the sorting scripts
   */
  public  function wp() {

    $orderby = get_query_var('orderby');
    error_log( "ORDERBY: " . json_encode( $orderby ) );

    // 'orderby' is a string, and: 'menu_order' is in first position of $orderby (not set), or 'orderby' set to 'menu_order' ASC NOT SURE THIS IS RIGHT!!
    //if ( ( is_string( $orderby ) && 0 === strpos( $orderby, 'project-category' ) ) || ( isset( $orderby['menu_order'] ) && $orderby['menu_order'] == 'ASC' ) ) {
    if ( ( is_array( $orderby ) && 0 === strpos( array_keys( $orderby)[0], 'project-category' ) ) ) {

      $script_name = '/assets/scripts/organise-posts-term-screen.js';
      wp_register_script( 'organise-posts-term', CARAWEBS_ORGANISE_POSTS_BASE_URL . $script_name, array('jquery-ui-sortable'), '2.1', true );

      wp_localize_script( 'organise-posts-term', 'carawebs_organise_posts', [ 'current_term' => $this->current_term ] );

      wp_enqueue_script( 'organise-posts-term' );

      //wp_enqueue_script( 'organise-posts-term', CARAWEBS_ORGANISE_POSTS_BASE_URL . $script_name, array('jquery-ui-sortable'), '2.1', true );

    }

  }

  /**
   * Add page ordering help to the help tab
   */
  public function admin_head() {

    $args = [
      'id' => 'simple_page_ordering_help_tab',
      'title' => 'Carawebs Ordering',
      'content' => '<p>' . __( 'No DRAG and drop ordering possible here, YET!', 'simple-page-ordering' ) . '</p>',
    ];

    $this->helpMessage ( $args );

  }

  public function set_term( $term ) {

    $this->current_term = $term;
    $this->term_postmeta_key = $this->custom_taxonomy . '-' . $term;

  }

  /**
   * Set a custom order for posts
   *
   * This is a callback for the `pre_get_posts` filter hook
   *
   * @see https://codex.wordpress.org/Plugin_API/Action_Reference/pre_get_posts
   * @param  object $query The $query object - passed by reference.
   * @return void
   */
  public function custom_order ( $query ) {

    // Get the term that is being displayed for the given custom taxonomy
    $current_term = $query->query['project-category'];

    // Standardised key for post meta
    // $key = "project-category-$this_term";
    $key = $this->term_postmeta_key;

    $query->set( 'meta_query', [
      'relation' => 'OR',
        [ 'key' => $key, 'compare' => 'EXISTS' ],
        [ 'key' => $key, 'compare' => 'NOT EXISTS' ]
     ]);

    $query->set( 'orderby', [ $key => 'ASC', 'date' => 'DESC' ] );

  }

  /**
   * The sorting logic
   *
   * Receives `$_POST` data via AJAX, returns json encoded array of results
   *
   * @return array json encoded array of data
   */
  public function ajax_organise_posts_ordering() {

    error_log("\$_POST: " . json_encode( $_POST ) );

    // check that we have what we need
    if ( empty( $_POST['id'] ) || ( ! isset( $_POST['previd'] ) && ! isset( $_POST['nextid'] ) ) ) {
      die(-1);
    }

    // real post?
    if ( ! $post = get_post( $_POST['id'] ) ) {
      die(-1);
    }

    // does user have the right to manage these post objects?
    if ( ! $this->check_edit_others_caps( $post->post_type ) ) {
      die(-1);
    }

    // badly written plug-in hooks for save post can break things
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
      error_reporting( 0 );
    }

    global $wp_version;

    $term     = empty( $_POST['term'] )     ? false               : sanitize_text_field( $_POST['term'] );
    $previd   = empty( $_POST['previd'] )   ? false               : (int) $_POST['previd'];
    $nextid   = empty( $_POST['nextid'] )   ? false               : (int) $_POST['nextid'];
    $start    = empty( $_POST['start'] )    ? 1                   : (int) $_POST['start'];
    $excluded = empty( $_POST['excluded'] ) ? array( $post->ID )  : array_filter( (array) $_POST['excluded'], 'intval' );
    $term_postmeta_key = $this->custom_taxonomy . '-' . $term;
    error_log( "\$term_postmeta_key: " . $term_postmeta_key );

    $new_pos = []; // store new positions for ajax
    $return_data = new \stdClass;

    do_action( 'simple_page_ordering_pre_order_posts', $post, $start );

    $max_sortable_posts = (int) apply_filters( 'simple_page_ordering_limit', 50 );  // should reliably be able to do about 50 at a time

    if ( $max_sortable_posts < 5 ) {  // don't be ridiculous!

      $max_sortable_posts = 50;

    }

    // we need to handle all post stati, except trash (in case of custom stati)
    $post_stati = get_post_stati(array(
      'show_in_admin_all_list' => true,
    ));

    $siblings_query = [
      'depth'                   => 1,
      'posts_per_page'          => $max_sortable_posts,
      'post_type'               => $post->post_type,
      'post_status'             => $post_stati,
      //
      'tax_query' => [
        [
          'taxonomy' => $this->custom_taxonomy,
          'field'    => 'slug',
          'terms'    => $term,
        ]
      ],
      //'order'                   => 'ASC',
      'meta_key'                => $term_postmeta_key,
      'orderby'                 => ['meta_value' => 'ASC', 'date' => 'DESC'],//'meta_value',
      'post__not_in'            => $excluded,
      'update_post_term_cache'  => false,
      'update_post_meta_cache'  => false,
      'suppress_filters'        => true,
      'ignore_sticky_posts'     => true,
    ];

    $siblings = new \WP_Query( $siblings_query ); // fetch all the siblings (relative ordering)

    // don't waste overhead of revisions on a menu order change (especially since they can't *all* be rolled back at once)
    remove_action( 'pre_post_update', 'wp_save_post_revision' );

    //error_log( json_encode($siblings->posts));
    foreach( $siblings->posts as $sibling ) :

      error_log( "POST ID: " . $sibling->ID . " Index: " . $start);

      // don't handle the actual post
      if ( $sibling->ID === $post->ID ) {
        continue;
      }

      // if this is the post that comes after our repositioned post, set our repositioned post position and increment menu order
      if ( $nextid === $sibling->ID ) {

        // increment post meta
        // ---------------------------------------------------------------------
        // $term_postmeta_key
        $test = update_post_meta( $post->ID, $term_postmeta_key, (int)$start );

        // error_log( "result of update_post_meta() \$post->ID: " . $post->ID );
        // error_log( "result of update_post_meta() \$term_postmeta_key: " . $term_postmeta_key );
        // error_log( "result of update_post_meta() \$start: " . $start );
        // error_log( "result of update_post_meta(): " . $test );

        $start++;

      }

      $sibling_meta_order = get_post_meta( $sibling->ID, (int)$term_postmeta_key, true );

      // if repositioned post has been set, and new items are already in the right order, we can stop
      // @TODO $sibling->menu_order !!!!!
      if ( isset( $new_pos[$post->ID] ) && $sibling_meta_order >= $start ) {
        $return_data->next = false;
        break;
      }

      // set the menu order of the current sibling and increment the menu order
      // @TODO set the menu order of the current sibling and increment the postmeta value
      // get_postmeta( $sibling->ID, $term_postmeta_key, true )
      //$sibling_meta_order = get_post_meta( $sibling->ID, $term_postmeta_key, true );
      //if ( $sibling->menu_order != $start ) {
      if ( $sibling_meta_order != $start ) {

        //update_post_meta( $sibling->ID, $term_postmeta_key, $start );
        $test = update_post_meta( $sibling->ID, $term_postmeta_key, $start );

        // error_log( "result of update_post_meta() \$post->ID: " . $sibling->ID );
        // error_log( "result of update_post_meta() \$term_postmeta_key: " . $term_postmeta_key );
        // error_log( "result of update_post_meta() \$start: " . $start );
        // error_log( "result of update_post_meta(): " . $test );

      }

      $new_pos[$sibling->ID] = $start;

      $start++;

      if ( ! $nextid && $previd == $sibling->ID ) {

        // @TODO
        update_post_meta( $post->ID, $term_postmeta_key, (int)$start );
        // wp_update_post(array(
        //   'ID' 			=> $post->ID,
        //   'menu_order' 	=> $start,
        //   'post_parent' 	=> $parent_id
        // ));
        // $ancestors = get_post_ancestors( $post->ID );
        //
        // $new_pos[$post->ID] = array(
        //   'menu_order'	=> $start,
        //   'post_parent' 	=> $parent_id,
        //   'depth' 		=> count($ancestors) );

        $start++;

      }

    endforeach;

    // max per request
    if ( !isset( $return_data->next ) && $siblings->max_num_pages > 1 ) {

      $return_data->next = array(
        'id'        => $post->ID,
        'previd'    => $previd,
        'nextid'    => $nextid,
        'start'     => $start,
        'excluded'  => array_merge( array_keys( $new_pos ), $excluded ),
      );

    } else {

      $return_data->next = false;

    }

    do_action( 'simple_page_ordering_ordered_posts', $post, $new_pos );

    // if ( ! $return_data->next ) {
    //
    //   // if the moved post has children, we need to refresh the page (unless we're continuing)
    //   $children = get_posts(array(
    //     'numberposts'             => 1,
    //     'post_type'               => $post->post_type,
    //     'post_status'             => $post_stati,
    //     'post_parent'             => $post->ID,
    //     'fields'                  => 'ids',
    //     'update_post_term_cache'  => false,
    //     'update_post_meta_cache'  => false,
    //   ));
    //
    //   if ( ! empty( $children ) ) {
    //
    //     die( 'children' );
    //
    //   }
    // }

    $return_data->new_pos = $new_pos;

    error_log( "RETURN: " . json_encode( $return_data ) );

    die( json_encode( $return_data ) );

  }

  function term_columns( $name ) {

    global $post;
    //var_dump($post);
    switch ($name) {
      case 'xxx':

        $order = get_post_meta( $post->ID, 'project-category-transport', true );
        //$order = $post->menu_order;
        echo $order;
        break;
     default:
        break;
     }

  }

  function add_new_project_column( $header_text_columns ) {

    error_log( json_encode($header_text_columns));

    $header_text_columns['xxx'] = ucfirst("$this->current_term Order");
    return $header_text_columns;

  }

}
