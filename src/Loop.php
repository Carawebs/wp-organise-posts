<?php

$term      = empty( $_POST['term'] )   ? false  : sanitize_text_field( $_POST['term'] );
$moved_ID  = empty( $_POST['id'] )     ? false  : (int) $_POST['id'];
$prev_ID   = empty( $_POST['previd'] ) ? false  : (int) $_POST['previd'];
$next_ID   = empty( $_POST['nextid'] ) ? false  : (int) $_POST['nextid'];
$start     = empty( $_POST['start'] )  ? 1      : (int) $_POST['start'];
$term_postmeta_key = $this->custom_taxonomy . '-' . $term;

// Query Here

$all_sibling_IDs = []; // Build this from WP_Query()

foreach( $all_sibling_IDs as $sibling_ID ) {

  $sibling_meta_order = get_post_meta( $sibling_ID, $term_postmeta_key, true );

  // The loop is at the next post
  if ( $next_ID === $sibling_ID ) {

    update_post_meta( $moved_ID, $term_postmeta_key, $start );
    $new_pos[$post->ID] = [ 'menu_order'  => $start ];
    $start++;
    update_post_meta( $next_ID, $term_postmeta_key, $start );
    $start++;

  }

  // After $next_ID and $start has been set - do nothing
  if ( isset( $new_pos[$post->ID] ) && $sibling_meta_order >= $start ) {

    $return_data->next = false;
    break;

  }

  // Increment the posts after $next_ID
  if ( $sibling_meta_order != $start ) {

    update_post_meta( $sibling_ID, $term_postmeta_key, $start );

  }

  $new_pos[$sibling_ID] = $start;

  $start++;

}

$q = new WP_Query( array(
    'meta_query' => [
        'relation' => 'OR',
        $term_postmeta_key => [ 'compare' => 'EXISTS', 'type' => 'NUMERIC' ],
        $term_postmeta_key => [ 'compare' => 'NOT EXISTS' ],
    ],
    'orderby' => [$term_postmeta_key => 'ASC'],
) );

$query->set( 'meta_query', array(
  'relation' => 'OR',
    array( 'key' => $key, 'compare' => 'EXISTS', 'type' => 'NUMERIC' ),
    array( 'key' => $key, 'compare' => 'NOT EXISTS' )
) );
$query->set( 'orderby', [ $key => 'ASC', 'menu_order' => 'ASC' ] );

?>

<form id="posts-filter" method="get">

<?php $wp_list_table->search_box( $post_type_object->labels->search_items, 'post' ); ?>

<input type="hidden" name="post_status" class="post_status_page" value="<?php echo !empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : 'all'; ?>" />
<input type="hidden" name="post_type" class="post_type_page" value="<?php echo $post_type; ?>" />
<?php if ( ! empty( $_REQUEST['show_sticky'] ) ) { ?>
<input type="hidden" name="show_sticky" value="1" />
<?php } ?>

<?php $wp_list_table->display(); ?>

</form>
