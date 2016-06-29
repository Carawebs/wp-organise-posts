<?php
namespace Carawebs\OrganisePosts\Settings;

class Controller {

  /**
   * Gets populated on submenus, contains slug of parent menu
   * @var null
   */
  public $parent_id = NULL;

  public function __construct ( Config $config ) {

    $this->config       = $config->getConfig();
    $this->menu_options = $this->config['default_page_options'];

    $this->prepopulate();
    $this->add_tabs();

    //var_dump( $config );
    add_action( 'admin_menu', array( $this, 'add_page' ) );

  }

  /**
   * Populate some of required options
   *
   * If no 'title' is provided, use the slug to construct one. If no 'page_title' is provided,
   * use the 'title' to construct one. If no 'function' provided, set default.
   *
   * @return void
   */
  public function prepopulate() {

    $this->menu_options['title'] = $this->menu_options['title']
      ?: ucfirst( $this->menu_options['slug'] );

    $this->menu_options['page_title'] = $this->menu_options['page_title']
      ?: $this->menu_options['page_title'] = $this->menu_options['title'];

    $this->menu_options['function'] = $this->menu_options['function']
      ?: 'create_menu_page';

  }

  /**
   * Add the menu page
   *
   * `$this->menu_options` are based on rational defaults, overridden by arguments
   * passed in by the client code.
   *
   * @return void
   */
  public function add_page() {

    $menu_options = $this->config['default_page_options'];

    if( $this->parent_id != NULL ) {

      add_submenu_page(
        $this->parent_id,                         // Parent slug
        $this->menu_options['page_title'],        // Page title
        $this->menu_options['title'],             // Menu Title
        $this->menu_options['capability'],        // …required for this menu to be displayed
        $this->menu_options['slug'],              // Slug name to refer to the menu
        [$this, $this->menu_options['function'] ] // Function to output page content
      );

    } else {

      add_menu_page(
        $this->menu_options['page_title'],          // Display in title tags when menu selected
        $this->menu_options['title'],               // Title text for menu
        $this->menu_options['capability'],          // …required for this menu to be displayed
        $this->menu_options['slug'],                // Slug name to refer to this menu (should be unique for this menu)
        [ $this, $this->menu_options['function'] ], // Function to output page content // was `$this->functionToUse`,
        $this->menu_options['icon'],                // Icon URL | Dashicons helper class | 'none' | base64-encoded SVG using a data URI
        $this->menu_options['position']             // position in the menu order this one should appear
      );

    }

  }

  /**
   * Create the menu page
   * @return void
   */
  public function create_menu_page() {

    //$this->save_if_submit();

    // The default tab is 'general'
    // @TODO: set the default tab as a property for better "Single Responsibility"
    $tab = 'general';

    // If on a tabbed page, set the tab accordingly
    if( isset( $_GET['tab'] ) ) {

      $tab = $_GET['tab'];

    }

    //$this->init_settings();

    ?>
    <div class="wrap">
      <h2><?php echo $this->menu_options['page_title'] ?></h2>
      <?php

      // -----------------------------------------------------------------------
      // DEBUG - dump fields
      // var_dump($_SESSION['fields']);
      // var_dump( $_SESSION['saved']);
      // var_dump( $_POST );
      // -----------------------------------------------------------------------
        if ( ! empty( $this->menu_options['desc'] ) ) {
          ?><p class='description'><?php echo $this->menu_options['desc'] ?></p><?php
        }
        $this->render_tabs( $tab );
      ?>
      <form method="POST" action="">
        <div class="postbox">
          <div class="inside">
            <table class="form-table">
              <?php //$this->render_fields( $tab ); ?>
            </table>
            <?php //$this->save_button(); ?>
          </div>
        </div>
      </form>
    </div>
    <?php

  }

    /**
    * Add tab
    * @param array $array options array with tab slug and title
    */
    public function add_tabs() {

      foreach( $this->config['tabs'] as $tab ) {

        $this->tabs[ $tab['slug'] ] = $tab['title'];

      }

    }

    /**
     * Render the registered tabs
     * @param  string $active_tab the viewed tab
     * @return void
     */
    public function render_tabs( $active_tab = 'general' ) {

      if( count( $this->tabs ) > 1 ) {

        echo '<h2 class="nav-tab-wrapper">';

          foreach ($this->tabs as $key => $value) {

            echo '<a href="' . admin_url('admin.php?page=' . $this->menu_options['slug'] . '&tab=' . $key ) . '" class="nav-tab ' .  ( ( $key == $active_tab ) ? 'nav-tab-active' : '' ) . ' ">' . $value . '</a>';

          }
        echo '</h2>';
        echo '<br/>';

      }

    }

}
