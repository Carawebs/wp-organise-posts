<?php
namespace Carawebs\OrganisePosts\Settings;

abstract class PluginSettings {

  use Validator;
  use RenderFields;

  /**
  * ID of the settings
  * @var string
  */
  public $settings_id = '';

  /**
  * Tabs for the settings page
  * @var array
  */
  public $tabs = [ 'general' => 'General' ];

  /**
  * Settings from database
  * @var array
  */
  protected $settings = [];

  /**
  * Array of fields for each tab in this format:
  * `[ 'tab_slug' => [ 'field_name' => ['option_key'=>'option_value',] ] ]`
  * This array is populated as the `add_field()` method is called.
  * @var array
  */
  protected $fields = [];

  /**
  * Data from POST request
  * @var array
  */
  protected $posted_data = [];

  /**
  * Get the settings from the database
  * @return void
  */
  public function init_settings() {

    $this->settings = (array) get_option( $this->settings_id );

    //var_dump($this->settings);

    foreach ( $this->fields as $tab_key => $tab ) {

      foreach ( $tab as $name => $field ) {

        if( isset( $this->settings[ $name ] ) ) {

          $this->fields[ $tab_key ][ $name ]['default'] = $this->settings[ $name ];

        }

      }

    }

  }
  /**
  * Save settings from POST
  * @return [type] [description]
  */
  public function save_settings() {

    $_SESSION['saved'] = ['saved' => TRUE, 'key' => $this->nonce_key, 'action' =>$this->nonce_action];
    $_SESSION['saved']['nonce_fail'] = FALSE;


    if ( ! wp_verify_nonce( $_POST[$this->nonce_key], $this->nonce_action ) ) {

      $_SESSION['saved']['nonce_fail'] = TRUE;

      return new \WP_Error(__CLASS__, 'Invalid data.');

    }

    $this->posted_data = $_POST;

    if( empty( $this->settings ) ) {

      $this->init_settings();

    }

    foreach ($this->fields as $tab => $tab_data ) {

      foreach ($tab_data as $name => $field) {

        $this->settings[ $name ] = $this->{ 'validate_' . $field['type'] }( $name );

      }

    }

    update_option( $this->settings_id, $this->settings );

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

  /**
  * Add fields
  *
  * This method is called by the client code
  *
  * @param array $array options for the field to add
  * @param string $tab tab slug for this field, defaults to 'general'
  */
  public function add_field( $override_arguments, $tab = 'general' ) {

    $allowed_field_types = [
      'text',
      'textarea',
      'wpeditor',
      'select',
      'radio',
      'checkbox'
    ];

    // If a type is set that is not allowed, don't add the field
    if( isset( $override_arguments['type'] ) && $override_arguments['type'] != '' && ! in_array( $override_arguments['type'], $allowed_field_types ) ) {

      return;

    }

    $default_arguments = [
      'name'        => '',
      'title'       => '',
      'default'     => '',
      'placeholder' => '',
      'type'        => 'text',
      'options'     => [],
      'default'     => '',
      'desc'        => '',
    ];


    // Merge overrides into defaults
    $field_arguments = array_merge( $default_arguments, $override_arguments );

    if( $field_arguments['name'] == '' ) {

      return;

    }

    // Disallow duplicate field names
    foreach ( $this->fields as $tabs ) {

      if( isset( $tabs[ $field_arguments['name'] ] ) ) {

        trigger_error( 'There is already a field with name ' . $field_arguments['name'] );
        return;

      }

    }

    // If there are options set but no default, then use the first option as a default value
    if( ! empty( $field_arguments['options'] ) && $field_arguments['default'] == '' ) {

      $field_arguments_keys = array_keys( $field_arguments['options'] );
      $field_arguments['default'] = $field_arguments_keys[0];

    }

    // If there is no fields array for this tab, initialize one
    if( ! isset( $this->fields[ $tab ] ) ) {

      $this->fields[ $tab ] = [];

    }

    // The field arguments array, under the specified tab
    $this->fields[ $tab ][ $field_arguments['name'] ] = $field_arguments;

    // DEBUG @REMOVE
    // -------------------------------------------------------------------------
    $_SESSION['fields'] = $this->fields;
    // -------------------------------------------------------------------------

  }

  /**
  * Add tab
  * @param array $array options array with tab slug and title
  */
  public function add_tab( array $array ) {

    $defaults = [
      'slug'  => '',
      'title' => ''
    ];

    $array = array_merge( $defaults, $array );

    if( $array['slug'] == '' || $array['title'] == '' ) {

      return;

    }

    $this->tabs[ $array['slug'] ] = $array['title'];

  }

}
