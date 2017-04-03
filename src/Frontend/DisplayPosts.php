<?php
namespace Carawebs\OrganisePosts\Frontend;

/**
* Functionality for displaying posts on the Frontend of the site
*/
class DisplayPosts {

    /**
    * Set a custom order for posts
    *
    * Callback for the `pre_get_posts` filter hook
    *
    * @see https://codex.wordpress.org/Plugin_API/Action_Reference/pre_get_posts
    * @param  object $query The $query object - passed by reference.
    * @return void
    */
    public function custom_order ( $query )
    {
        // Get the term that is being displayed for the given custom taxonomy
        $this_term = $query->query['project-category'];

        // Standardised key for post meta
        $key = "project-category-$this_term";

        // Set the meta_key and orderby it's value
        $query->set('meta_key', $key);
        $query->set('orderby', ['meta_value' => 'ASC', 'date' => 'DESC']);

    }

    public function display_posts( $query )
    {
        // Check if on frontend and main query is modified
        if(!is_admin() && $query->is_main_query()) {
            // Get the term that is being displayed for the given custom taxonomy
            $this_term = ! empty( $query->query['project-category']) ? $query->query['project-category'] : NULL;

            // if( empty( $this_term ) ) { return; }
            if( empty( $this_term ) ) {
                $query->set( 'orderby', [ 'menu_order' => 'ASC' ] );
            } else {
                // Standardised key for post meta index
                $key = "project-category-$this_term";

                $query->set( 'meta_query', array(
                    'relation' => 'OR',
                    array( 'key' => $key, 'compare' => 'EXISTS', 'type' => 'NUMERIC' ),
                    array( 'key' => $key, 'compare' => 'NOT EXISTS' )
                ) );
                $query->set( 'orderby', [ $key => 'ASC', 'menu_order' => 'ASC' ] );
            }
        }
    }
}
