

Original Brief:
Hi Angela,

Custom Post Types will be ordered by an index that would be set when you drag and drop them in place.

>The price we've quoted is based on ordering projects by means of a single index - this means that the project that displays first on the "Commercial" page would also be the first "Commercial" project on the overall "Projects" page. You'd need to be a bit careful - if you dragged and dropped a project to the top of the "Commercial" projects, you might also be dragging this project to the top of the overall "Projects" page. I actually don't think this will be a problem for you, looking at how you are prioritising/ordering your posts.

If you need different ordering on the Project Category pages, so that the first project in "Commercial" is not necessarily the first "Commercial" project in the "Projects" page, this would involve quite a bit more complexity - we would need to create a second index, and use this to order the project category pages independently. To build in this level of control, it would bring the total cost up to £850.

To summarise:

    Create custom functionality to allow drag and drop ordering of projects: £380
    As above, but with project order (and drag & drop sorting) also applied to project category pages: £530
    Independent ordering of Project on the "Projects" page and individual project-category pages: £850


When dragged on a category page, write to postmeta:

* $key => $value where $key is the taxonomy ID and value is the index

## Build Notes
A post meta value is used to order projects on project-category taxonomy archive pages.

It was difficult to create a modified loop that displayed:

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
