<?php
namespace Carawebs\OrganisePosts\Settings;

class SaveSettings {

  use Validator;

  /**
  * Save settings from POST
  * @return [type] [description]
  */
  public function save( array $nonce = [], array $settings = [] ) {

    extract( $nonce );

    $_SESSION['saved'] = ['saved' => TRUE, 'key' => $nonce_key, 'action' =>$nonce_action];
    $_SESSION['saved']['nonce_fail'] = FALSE;


    if ( ! wp_verify_nonce( $_POST[$nonce_key], $nonce_action ) ) {

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


}
