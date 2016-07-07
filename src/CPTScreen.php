<?php

namespace Carawebs\OrganisePosts;

/**
* Class that modifies the custom post type edit screen
*/
class CPTScreen extends Screen {

  /**
  * Initialize sorting scripts on the edit page, if posts are sorted by menu order
  */
  public  function wp() {

    $orderby = get_query_var('orderby');

    if ( ( is_string( $orderby ) && 0 === strpos( $orderby, 'menu_order' ) ) || ( isset( $orderby['menu_order'] ) && $orderby['menu_order'] == 'ASC' ) ) {

      $script_name = '/assets/scripts/simple-page-ordering.dev.js';
      wp_enqueue_script( 'simple-page-ordering', CARAWEBS_ORGANISE_POSTS_BASE_URL . $script_name, array('jquery-ui-sortable'), '2.1', true );
      wp_enqueue_style( 'simple-page-ordering', plugins_url( 'simple-page-ordering.css', __FILE__ ) );

    }

  }

  /**
  * Add page ordering help to the help tab
  */
  public function admin_head() {

    $screen = get_current_screen();

    $screen->add_help_tab(
    [
      'id' => 'simple_page_ordering_help_tab',
      'title' => 'Carawebs Ordering',
      'content' => '<p>' . __( 'To reposition an item, simply drag and drop the row by "clicking and holding" it anywhere (outside of the links and form controls) and moving it to its new position.', 'simple-page-ordering' ) . '</p>',
    ]);

  }

  function custom_taxonomy_nav() {

    global $typenow;
    global $wp_query;

    if ($typenow=='listing') {
      $taxonomy = 'project-category';
      $current_taxonomy = get_taxonomy($taxonomy);
      wp_dropdown_categories(array(
        'show_option_all' =>  __("Show All {$current_taxonomy->label}"),
        'taxonomy'        =>  $taxonomy,
        'name'            =>  'business',
        'orderby'         =>  'name',
        'selected'        =>  $wp_query->query['term'],
        'hierarchical'    =>  true,
        'depth'           =>  3,
        'show_count'      =>  true, // Show # listings in parens
        'hide_empty'      =>  true, // Don't show businesses w/o listings
      ));
    }
  }

}
