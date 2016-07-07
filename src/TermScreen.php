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
    //$current_term = $query->query['project-category'];

    // Standardised key for post meta
    $key = $this->term_postmeta_key;

    $query->set( 'meta_query', [
      'relation' => 'OR',
        //[ 'key' => $key, 'compare' => 'EXISTS' ],
        [ 'key' => $key, 'type' => 'NUMERIC' ],
        [ 'key' => $key, 'compare' => 'NOT EXISTS' ]
     ]);

    $query->set( 'orderby', [ $key => 'ASC' ] );

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
    $movedID  = empty( $_POST['id'] )       ? false               : (int) $_POST['id'];
    $previd   = empty( $_POST['previd'] )   ? false               : (int) $_POST['previd'];
    $nextid   = empty( $_POST['nextid'] )   ? false               : (int) $_POST['nextid'];
    $start    = empty( $_POST['start'] )    ? 1                   : (int) $_POST['start'];
    $excluded = empty( $_POST['excluded'] ) ? [ $movedID ]        : array_filter( (array) $_POST['excluded'], 'intval' );
    $term_postmeta_key = $this->custom_taxonomy . '-' . $term;
    $new_pos = []; // store new positions for ajax
    $return_data = new \stdClass;

    $siblings = $this->siblings_query( $post, $term, $term_postmeta_key, $excluded );

    remove_action( 'pre_post_update', 'wp_save_post_revision' );

    error_log( json_encode( [ "SIBLINGS", $siblings->posts]));

    foreach( $siblings->posts as $sibling_ID ) :

      $sibling_meta_order = get_post_meta( $sibling_ID, $term_postmeta_key, true );

      // // The loop is at the next post
      // if ( $nextid === $sibling_ID ) {
      //
      //   update_post_meta( $movedID, $term_postmeta_key, $start );
      //   $new_pos[$post->ID] = [ 'menu_order'  => $start ];
      //   $start++;
      //   update_post_meta( $nextid, $term_postmeta_key, $start );
      //   $start++;
      //
      // }
      //
      // // After $next_ID and $start has been set - do nothing
      // if ( isset( $new_pos[$post->ID] ) && $sibling_meta_order >= $start ) {
      //
      //   $return_data->next = false;
      //   break;
      //
      // }
      //
      // // Increment the posts after $next_ID
      // if ( $sibling_meta_order != $start ) {
      //
      //   update_post_meta( $sibling_ID, $term_postmeta_key, $start );
      //
      // }
      //
      // $new_pos[$sibling_ID] = $start;
      //
      // $start++;

      // Get the order for this post on this term
      $sibling_meta_order = get_post_meta( $sibling_ID, $term_postmeta_key, true );

      // The moved post should be excluded from the query, but just in case, ignore it
      if ( $sibling_ID === $post->ID ) {
        continue;
      }

      // if this is the post that comes after our repositioned post, set the position
      // of the **repositioned** post, add to the AJAX return and increment the counter.
      if ( $nextid === $sibling_ID ) {

        $updated = update_post_meta( $post->ID, $term_postmeta_key, $start );

        error_log("FOLLOWING POST: " . $sibling_ID . ", MOVED: " . $post->post_title . ", Index: " . $start);

        $new_pos[$post->ID] = [
          'menu_order'	=> $start
        ];

        $start++;

      }

      // if repositioned post has been set, and new items are already in the right order, STOP
      if ( isset( $new_pos[$post->ID] ) && $sibling_meta_order >= $start ) {

        $return_data->next = false;
        break;

      }

      // set the menu order of the current sibling and increment the menu order
      if ( $sibling_meta_order != $start ) {

        update_post_meta( $sibling_ID, $term_postmeta_key, $start );

      }

      $new_pos[$sibling_ID] = $start;

      $start++;

      if ( ! $nextid && $previd == $sibling_ID ) {

        update_post_meta( $post->ID, $term_postmeta_key, $start );

        $start++;

      }

    endforeach;

    // max per request
    if ( ! isset( $return_data->next ) && $siblings->max_num_pages > 1 ) {

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

    $return_data->new_pos = $new_pos;

    error_log( "RETURN: " . json_encode( $return_data ) );

    die( json_encode( $return_data ) );

  }

  /**
   * [term_columns description]
   * @param  [type] $name [description]
   * @return [type]       [description]
   */
  function term_columns( $name ) {

    global $post;
    //var_dump($post);
    switch ($name) {
      case 'xxx':

        $order = get_post_meta( $post->ID, $this->term_postmeta_key, true );
        echo ! empty( $order ) ? $order : '999';
        break;
     default:
        break;
     }

  }

  function add_new_project_column( $header_text_columns ) {

    error_log( json_encode($header_text_columns));

    $header_text_columns['xxx'] = "Order in " . ucfirst($this->current_term) . " " . $this->custom_taxonomy;
    return $header_text_columns;

  }

  public function siblings_query( $post, $term, $term_postmeta_key, $excluded ) {

    error_log( "\$term_postmeta_key: " . $term_postmeta_key );

    $max_sortable_posts = (int) apply_filters( 'simple_page_ordering_limit', 50 );  // should reliably be able to do about 50 at a time

    if ( $max_sortable_posts < 5 ) {  // don't be ridiculous!

      $max_sortable_posts = 50;

    }

    // we need to handle all post stati, except trash (in case of custom stati)
    $post_stati = get_post_stati(array(
      'show_in_admin_all_list' => true,
    ));

    $siblings_query = [
      'depth'           => 1,
      'posts_per_page'  => $max_sortable_posts,
      'post_type'       => $post->post_type,
      'post_status'     => $post_stati,
      'fields'          => 'ids',
      'tax_query' => [
        [
          'taxonomy' => $this->custom_taxonomy,
          'field'    => 'slug',
          'terms'    => $term,
        ]
      ],
      // 'meta_key'                => $term_postmeta_key,
      // 'orderby'                 => ['meta_value' => 'ASC'],//'meta_value',
      'meta_query' => [
          'relation' => 'OR',[
            'key' => $term_postmeta_key,
            'type' => 'NUMERIC',
            'compare' => 'EXISTS'
          ],
          [
            'key' => $term_postmeta_key,
            'compare' => 'NOT EXISTS'
          ],
      ],
      'orderby' => [$term_postmeta_key => 'ASC'],
      //
      'post__not_in'            => $excluded,
      'update_post_term_cache'  => false,
      'update_post_meta_cache'  => false,
      'suppress_filters'        => true,
      'ignore_sticky_posts'     => true,
    ];

    return new \WP_Query( $siblings_query ); // fetch all the siblings (relative ordering)

  }

}