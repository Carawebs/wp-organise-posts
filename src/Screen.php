<?php
namespace Carawebs\OrganisePosts;

/**
* Base functionality for the screen set up
*/
abstract class Screen {

  /**
  * Append a sort by order link to the post actions
  *
  * Hooked to `'views_' . $screen->id`
  *
  * @param string $views
  * @return string
  */
  public function sort_by_order_link( $views ) {

    $class = ( get_query_var('orderby') == 'menu_order title' ) ? 'current' : '';
    $query_string = esc_url( remove_query_arg( array( 'orderby', 'order' ) ) );

    if ( ! is_post_type_hierarchical( get_post_type() ) ) {

      $query_string = add_query_arg( 'orderby', 'menu_order title', $query_string );
      $query_string = add_query_arg( 'order', 'asc', $query_string );

    }

    $views['byorder'] = sprintf('<a href="%s" class="%s">%s</a>', $query_string, $class, __("Sort by Order", 'simple-page-ordering'));

    return $views;

  }

  protected function helpMessage( array $args) {

    $screen = get_current_screen();
    $screen->add_help_tab(
      [
        'id' => $args['id'],
        'title' => $args['title'],
        'content' => $args['content'],
      ]
    );

  }

  /**
  * Checks to see if the current user has the capability to "edit others" for a post type
  *
  * @param string $post_type Post type name
  * @return bool True or false
  */
  protected function check_edit_others_caps( $post_type ) {

    $post_type_object = get_post_type_object( $post_type );
    $edit_others_cap = empty( $post_type_object ) ? 'edit_others_' . $post_type . 's' : $post_type_object->cap->edit_others_posts;
    return apply_filters( 'simple_page_ordering_edit_rights', current_user_can( $edit_others_cap ), $post_type );

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

    $previd   = empty( $_POST['previd'] )   ? false               : (int) $_POST['previd'];
    $nextid   = empty( $_POST['nextid'] )   ? false               : (int) $_POST['nextid'];
    $start    = empty( $_POST['start'] )    ? 1                   : (int) $_POST['start'];
    $excluded = empty( $_POST['excluded'] ) ? array( $post->ID )  : array_filter( (array) $_POST['excluded'], 'intval' );

    $new_pos = []; // store new positions for ajax
    $return_data = new \stdClass;

    do_action( 'simple_page_ordering_pre_order_posts', $post, $start );

    // attempt to get the intended parent... if either sibling has a matching parent ID, use that
    $parent_id = $post->post_parent;

    $next_post_parent = $nextid ? wp_get_post_parent_id( $nextid ) : false;

    // if the preceding post is the parent of the next post, move it inside
    if ( $previd == $next_post_parent ) {

      $parent_id = $next_post_parent;

    } elseif ( $next_post_parent !== $parent_id ) {

      // otherwise, if the next post's parent isn't the same as our parent, we need to study
      $prev_post_parent = $previd ? wp_get_post_parent_id( $previd ) : false;

      if ( $prev_post_parent !== $parent_id ) { // if the previous post is not our parent now, make it so!

        $parent_id = ( $prev_post_parent !== false ) ? $prev_post_parent : $next_post_parent;

      }

    }

    // if the next post's parent isn't our parent, it might as well be false (irrelevant to our query)
    if ( $next_post_parent !== $parent_id ) {

      $nextid = false;

    }

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
      'post_parent'             => $parent_id,
      'orderby'                 => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
      'post__not_in'            => $excluded,
      'update_post_term_cache'  => false,
      'update_post_meta_cache'  => false,
      'suppress_filters'        => true,
      'ignore_sticky_posts'     => true,
    ];

    if ( version_compare( $wp_version, '4.0', '<' ) ) {
      $siblings_query['orderby'] = 'menu_order title';
      $siblings_query['order'] = 'ASC';
    }

    $siblings = new \WP_Query( $siblings_query ); // fetch all the siblings (relative ordering)

    // don't waste overhead of revisions on a menu order change (especially since they can't *all* be rolled back at once)
    remove_action( 'pre_post_update', 'wp_save_post_revision' );

    foreach( $siblings->posts as $sibling ) :

    // don't handle the actual post
    if ( $sibling->ID === $post->ID ) {
      continue;
    }

    // if this is the post that comes after our repositioned post, set our repositioned post position and increment menu order
    if ( $nextid === $sibling->ID ) {

      wp_update_post([
        'ID'			=> $post->ID,
        'menu_order'	=> $start,
        'post_parent'	=> $parent_id,
      ]);

      $ancestors = get_post_ancestors( $post->ID );

      $new_pos[$post->ID] = [
        'menu_order'	=> $start,
        'post_parent'	=> $parent_id,
        'depth'			=> count( $ancestors ),
      ];

      $start++;

    }

    // if repositioned post has been set, and new items are already in the right order, we can stop
    if ( isset( $new_pos[$post->ID] ) && $sibling->menu_order >= $start ) {
      $return_data->next = false;
      break;
    }

    // set the menu order of the current sibling and increment the menu order
    if ( $sibling->menu_order != $start ) {
      wp_update_post(array(
        'ID' 			=> $sibling->ID,
        'menu_order'	=> $start,
      ));
    }
    $new_pos[$sibling->ID] = $start;
    $start++;

    if ( !$nextid && $previd == $sibling->ID ) {
      wp_update_post(array(
        'ID' 			=> $post->ID,
        'menu_order' 	=> $start,
        'post_parent' 	=> $parent_id
      ));
      $ancestors = get_post_ancestors( $post->ID );
      $new_pos[$post->ID] = array(
        'menu_order'	=> $start,
        'post_parent' 	=> $parent_id,
        'depth' 		=> count($ancestors) );
        $start++;
      }

    endforeach;

    // max per request
    if ( !isset( $return_data->next ) && $siblings->max_num_pages > 1 ) {
      $return_data->next = array(
        'id' 		=> $post->ID,
        'previd' 	=> $previd,
        'nextid' 	=> $nextid,
        'start'		=> $start,
        'excluded'	=> array_merge( array_keys( $new_pos ), $excluded ),
      );
    } else {
      $return_data->next = false;
    }

    do_action( 'simple_page_ordering_ordered_posts', $post, $new_pos );

    if ( ! $return_data->next ) {
      // if the moved post has children, we need to refresh the page (unless we're continuing)
      $children = get_posts(array(
        'numberposts'             => 1,
        'post_type'               => $post->post_type,
        'post_status'             => $post_stati,
        'post_parent'             => $post->ID,
        'fields'                  => 'ids',
        'update_post_term_cache'  => false,
        'update_post_meta_cache'  => false,
      ));

      if ( ! empty( $children ) ) {
        die( 'children' );
      }
    }

    $return_data->new_pos = $new_pos;

    error_log( "RETURN: " . json_encode( $return_data ) );

    die( json_encode( $return_data ) );

  }

  /**
  * Check requirements and send message back via AJAX in case of failure
  *
  * @return Object $post  Post object, if everything checks out
  */
  public function check_requirements() {

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

    return $post;

  }

  /**
  * Add Menu Order column to edit screen
  */
  function add_menu_order_column( $header_text_columns ) {

    $header_text_columns['menu_order'] = "Overall Order in Projects";
    return $header_text_columns;

  }

  /**
  * Show menu order column values in the 'menu_order' columns
  */
  function show_menu_order_column( $name ){
    global $post;

    switch ($name) {

      case 'menu_order':

      echo $post->menu_order;
      break;

      default:

      break;

    }

  }

  /**
  * make column sortable
  */
  function sortable_menu_order_column( $columns ){

    $columns['menu_order'] = 'menu_order';
    return $columns;

  }

}
