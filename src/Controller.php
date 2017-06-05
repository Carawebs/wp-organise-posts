<?php
namespace Carawebs\OrganisePosts;

use Carawebs\OrganisePosts\DisplayPosts;

/**
* @author  David Egan <david@carawebs.com>
* @license http://opensource.org/licenses/MIT
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
    public function __construct( Config $config)
    {
        $this->config = $config;
        $this->isAdmin = is_admin();
        $this->target_post_types = $config['CPTs'];
        $this->set_tax_screen_identifiers( [ 'project-category', 'category_name', 'tag' ] );
    }

    /**
    * Setup backend hooks.
    * Instantiate necessary objects if necessary.
    *
    * @return void
    */
    public function setupBackendActions()
    {
        if ( ! $this->isAdmin ) { return; }
        if ( empty( $this->target_post_types ) ) { return; }

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
    public function setupFrontendActions()
    {
        if (empty($this->target_post_types)) { return; }
        if (!in_array(get_queried_object(), $this->target_post_types)) { return; }
        add_filter( 'pre_get_posts', [ new Frontend\DisplayPosts(), 'display_posts' ] );
    }

    public function set_tax_screen_identifiers(array $identifiers = [])
    {
        $this->term_screen_identifiers = $identifiers;
    }

    /**
    * Load up page ordering scripts for the particular edit screen
    *
    * @TODO Add custom tax menu on the CPT screen
    * @see http://wordpress.stackexchange.com/a/582
    * @see https://gist.github.com/mikeschinkel/541505
    * add_action('restrict_manage_posts', [ $cptScreen, 'custom_taxonomy_nav' ] );
    */
    public function load_edit_screen()
    {
        $screen = get_current_screen();
        $this->post_type = $screen->post_type;

        if(!in_array($this->post_type, $this->target_post_types) or "do-not-proceed" === $this->gatekeeper($this->post_type)) {
            return;
        }

        $current_term = isset( $_GET['project-category'] ) ? $_GET['project-category'] : NULL;
        $this->cptScreen->set_post_type( $this->post_type );
        $this->termScreen->set_post_type( $this->post_type );
        $custom_taxonomy_screen = $this->is_filtered_term_edit_screen();

        // Allowed Post types only, and NOT a custom taxonomy term screen filter
        if( in_array ( $this->post_type, $this->target_post_types ) && empty( $custom_taxonomy_screen ) ) {
            $screenContext = $this->cptScreen;
            $cpt_actions = [
                'pre_get_posts'                       => 'orderby_menu_order',
                'manage_' . $this->post_type . '_posts_columns'        => 'add_menu_order_column',
                'manage_' . $this->post_type . '_posts_custom_column'  => 'show_menu_order_column',
                'wp'                                  => 'wp',
                'admin_head'                          => 'admin_head'
            ];
            $cpt_filters = [
                'manage_edit-project_sortable_columns'=> 'sortable_menu_order_column',
                'views_' . $screen->id                => 'sort_by_order_link'
            ];
        } else if( in_array( 'project-category', $custom_taxonomy_screen ) ) {
            $screenContext = $this->termScreen;
            $this->termScreen->set_term( $current_term );
            $cpt_actions = [
                'manage_project_posts_columns'        => 'add_menu_order_column',
                'manage_project_posts_custom_column'  => 'show_menu_order_column',
                'manage_project_posts_columns'        => 'add_new_project_column',
                'pre_get_posts'                       => 'custom_order',
                'wp'                                  => 'wp',
                'admin_head'                          => 'admin_head',
            ];
            $cpt_filters = [
                'manage_project_posts_custom_column'  => 'term_columns',
                'views_' . $screen->id                => 'sort_by_order_link',
            ];

            add_action('manage_project_posts_columns', [ $screenContext, 'add_menu_order_column']);
            add_action( 'manage_project_posts_custom_column', [ $screenContext,'show_menu_order_column']);
        } else {
            return;
        }

        $this->load_actions( $screenContext, $cpt_actions );
        $this->load_filters( $screenContext, $cpt_filters );
    }

    public function gatekeeper( $post_type )
    {
        if( ! in_array( $post_type, $this->target_post_types ) ) {
            return "do-not-proceed";
        }

        // is post type sortable?
        $sortable = ( post_type_supports( $post_type, 'page-attributes' ) || is_post_type_hierarchical( $post_type ) );
        if ( ! $sortable = apply_filters( 'simple_page_ordering_is_sortable', $sortable, $post_type ) ) {
            return "do-not-proceed";
        }

        // does user have the right to manage these post objects?
        if ( ! $this->check_edit_others_caps( $this->post_type ) ) {
            return "do-not-proceed";
        }
    }

    /**
    * Is this a filtered term edit screen?
    *
    * Screens of this type have the $_GET: `/edit.php?post_type=project&project-category=commercial`
    * The strings in this array are checked against $_GET elements on this screen
    * @TODO $taxonomies = get_taxonomies(); --- get all taxonomies, tidy them up and add them to the array of term filter pages.
    * @param  [type]  $term [description]
    * @return boolean       true if the current screen $_GET array contains one of the term identifiers
    */
    public function is_filtered_term_edit_screen ()
    {
        return array_filter( $this->term_screen_identifiers, function ( $term_identifier ) {
            return isset( $_GET[$term_identifier] );
        });
    }

    public function load_actions( Screen $context, array $actions )
    {
        foreach( $actions as $action => $method ) {
            add_action( $action, [ $context, $method ] );
        }
    }

    public function load_filters( Screen $context, array $filters, $priority = 10, $accepted_args = 1 )
    {
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
    private function check_edit_others_caps( $post_type )
    {
        $post_type_object = get_post_type_object( $post_type );
        $edit_others_cap = empty( $post_type_object ) ? 'edit_others_' . $post_type . 's' : $post_type_object->cap->edit_others_posts;
        return apply_filters( 'simple_page_ordering_edit_rights', current_user_can( $edit_others_cap ), $post_type );
    }

    /**
    * Load Text Domain
    * @return void
    */
    private function loadTextDomain()
    {
        $pathArr = explode( DIRECTORY_SEPARATOR, dirname(__DIR__) );
        load_plugin_textdomain( 'organise-posts', false, end($pathArr).'/lang' );
    }
}
