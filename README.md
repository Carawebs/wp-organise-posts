Organise Posts by Drag and Drop
===============================
Set the display order of Custom Post Types by drag and drop on the posts edit screen. Ordering is achieved by an index that is set when you drag and drop them in place.

## Build Notes
A post meta value is used to order projects on project-category taxonomy archive pages.

It was tricky to create a modified loop that displayed:

* FIRST: projects with the relevant post meta key set, in the correct order as determined by the value
* SECOND: projects without the key

The key value pairing will be added after sorting, so it is important that all projects are shown, even if they don't have the key.

~~~
<?php
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
    $this_term = $query->query['project-category'];

    // Standardised key for post meta
    $key = "project-category-$this_term";

    $query->set( 'meta_query', [
        'relation' => 'OR',
        [ 'key' => $key, 'compare' => 'EXISTS' ],
        [ 'key' => $key, 'compare' => 'NOT EXISTS' ]
    ]);

    $query->set( 'orderby', [ $key => 'ASC', 'date' => 'DESC' ] );

}
~~~

This simple query returns all posts with the key, by value order:

~~~
<?php
public function custom_order ( $query ) {

    // Get the term that is being displayed for the given custom taxonomy
    $this_term = $query->query['project-category'];

    // Standardised key for post meta
    $key = "project-category-$this_term";

    // Set the meta_key and orderby it's value
    $query->set('meta_key', $key);
    $query->set('orderby', ['meta_value' => 'ASC', 'date' => 'DESC']);

}
~~~

~~~
<?php
// query in old format
$query->set( 'meta_query', array(
    'relation' => 'OR',
    array( 'key' => $key, 'compare' => 'EXISTS' ),
    array( 'key' => $key, 'compare' => 'NOT EXISTS' )
    ) );
    $query->set( 'orderby', [ $key => 'ASC', 'date' => 'DESC' ] );
~~~
