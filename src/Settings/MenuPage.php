<?php
namespace Carawebs\OrganisePosts\Settings;

class MenuPage extends RenderPage {

  use Validator;
  //use SaveSettings;
  /**
  * Settings from database
  * @var array
  */
  protected $settings = [];

  protected $action       = 'carawebs-organise-posts-save';

  protected $nonce_action = 'carawebs-organise-posts-nonce';

  protected $nonce_key    = '_carawebs-organise-posts';

  protected $settings_id;

  /**
  * Gets populated on submenus, contains slug of parent menu
  * @var null
  */
  public $parent_id = NULL;

  //public function __construct ( Config $config, $saveSettings ) {
  public function __construct ( Config $config ) {

    // Config object
    $this->config       = $config->getConfig();

    // Set the unique ID for these settings
    $this->settings_id = $this->config['default_page_options']['slug'];

    // If a submenu page has been specified, set `$this->parent_id`
    $this->parent_id = $this->config['default_page_options']['parent'] ?: NULL;

    $this->menu_options = $this->config['default_page_options'];

    $this->field_args   = $this->config['fields'];


    $this->addFields();
    $this->prepopulate();

    add_action( 'admin_menu', [ $this, 'add_page' ] );

    // deprecating this:
    //add_action( 'wordpressmenu_page_save_' . $this->settings_id, array( $this, 'saveSettings' ) );
    $this->add_tabs();

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

    $this->menu_options['title'] = ! empty( $this->menu_options['title'] )
    ? $this->menu_options['title'] : ucfirst( $this->menu_options['slug'] );

    $this->menu_options['page_title'] = ! empty( $this->menu_options['page_title'] )
    ? $this->menu_options['page_title'] : $this->menu_options['page_title'] = $this->menu_options['title'];

    $this->menu_options['function'] = ! empty( $this->menu_options['function'] )
    ? $this->menu_options['function'] : 'create_menu_page';

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

  public function addFields() {

    foreach( $this->field_args as $field ) {

      $this->add_field( $field['tab'], $field['args'] );

    }

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
  * Get the settings from the database
  * @return void
  */
  public function init_settings() {

    $this->settings = (array) get_option( $this->settings_id );

    foreach ( $this->fields as $tab_key => $tab ) {

      foreach ( $tab as $name => $field ) {

        if( isset( $this->settings[ $name ] ) ) {

          $this->fields[ $tab_key ][ $name ]['default'] = $this->settings[ $name ];

        }

      }

    }

  }

  /**
   * Trigger the saveSettings method if the button for this menu is submitted
   * @return void
   */
  protected function saveIfSubmitted() {

    if( isset( $_POST[ $this->settings_id . '_save' ] ) ) {

      $return = $this->saveSettings();

      if( is_wp_error( $return ) ) {

          echo $return->get_error_message();

      }

    }

    // if( isset( $_POST[ $this->settings_id . '_save' ] ) ) {
    //  do_action( 'wordpressmenu_page_save_' . $this->settings_id );
    // }

  }

  /**
  * Save settings from `$_POST` array on form submission
  *
  * Runs a nonce check, validates each field value, builds an array of validated values
  * and updates the option in the database.
  *
  * @uses `update_option()`
  * @return void
  */
  public function saveSettings() {

    if ( ! wp_verify_nonce( $_POST[$this->nonce_key], $this->nonce_action ) ) {

      return new \WP_Error(__CLASS__, 'Invalid data.');

    }

    $this->posted_data = $_POST;

    if( empty( $this->settings ) ) {

      $this->init_settings();

    }

    foreach ( $this->fields as $tab => $tab_fields_data ) {

      foreach ( $tab_fields_data as $field_name => $field_data ) {

        if( 'message' === $field_data['type']) { continue; }

        //$this->settings[ $field_name ] = $this->{ 'validate_' . $field_data['type'] }( $field_name );
        $this->settings[ $field_name ] = $this->{ 'validate_' . $field_data['type'] }( $field_name );

      }

    }

    $this->updated = update_option( $this->settings_id, $this->settings );

  }

  /**
  * Gets an option from the settings API, using defaults if necessary to prevent undefined notices.
  *
  * @param  string $key
  * @param  mixed  $empty_value
  * @return mixed  The value specified for the option or a default value for the option.
  */
  public function get_option( $key, $empty_value = NULL ) {

    if ( empty( $this->settings ) ) {

      $this->init_settings();

    }

    // Get option default if unset.
    if ( ! isset( $this->settings[ $key ] ) ) {

      $form_fields = $this->fields;

      foreach ( $this->tabs as $tab_key => $tab_title ) {

        if( isset( $form_fields[ $tab_key ][ $key ] ) ) {

          $this->settings[ $key ] = isset( $form_fields[ $tab_key ][ $key ]['default'] ) ? $form_fields[ $tab_key ][ $key ]['default'] : '';

        }

      }

    }

    if ( ! is_null( $empty_value ) && empty( $this->settings[ $key ] ) && '' === $this->settings[ $key ] ) {

      $this->settings[ $key ] = $empty_value;

    }

    return $this->settings[ $key ];

  }

}
