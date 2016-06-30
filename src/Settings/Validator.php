<?php
namespace Carawebs\OrganisePosts\Settings;

trait Validator {


  /**
   * Validate text field
   * @param  string $key name of the field
   * @return string
   */
  public function validate_text( $key ) {

    $text  = $this->get_option( $key );
    if ( isset( $this->posted_data[ $key ] ) ) {

      $text = wp_kses_post( trim( stripslashes( $this->posted_data[ $key ] ) ) );

    }

    return $text;
  }

  /**
   * Validate textarea field
   * @param  string $key name of the field
   * @return string
   */
  public function validate_textarea( $key ){
    $text  = $this->get_option( $key );

    if ( isset( $this->posted_data[ $key ] ) ) {
      $text = wp_kses( trim( stripslashes( $this->posted_data[ $key ] ) ),
        array_merge(
          array(
            'iframe' => array( 'src' => true, 'style' => true, 'id' => true, 'class' => true )
          ),
          wp_kses_allowed_html( 'post' )
        )
      );
    }
    return $text;
  }

  /**
   * Validate WPEditor field
   * @param  string $key name of the field
   * @return string
   */
  public function validate_wpeditor( $key ){
    $text  = $this->get_option( $key );

    if ( isset( $this->posted_data[ $key ] ) ) {
      $text = wp_kses( trim( stripslashes( $this->posted_data[ $key ] ) ),
        array_merge(
          array(
            'iframe' => array( 'src' => true, 'style' => true, 'id' => true, 'class' => true )
          ),
          wp_kses_allowed_html( 'post' )
        )
      );
    }
    return $text;
  }

  /**
   * Validate select field
   * @param  string $key name of the field
   * @return string
   */
  public function validate_select( $key ) {
    $value = $this->get_option( $key );
    if ( isset( $this->posted_data[ $key ] ) ) {
      $value = wc_clean( stripslashes( $this->posted_data[ $key ] ) );
    }
    return $value;
  }
  /**
   * Validate radio
   * @param  string $key name of the field
   * @return string
   */
  public function validate_radio( $key ) {
    $value = $this->get_option( $key );
    if ( isset( $this->posted_data[ $key ] ) ) {
      $value = sanitize_text_field( stripslashes( $this->posted_data[ $key ] ) );
    }
    return $value;
  }
  /**
   * Validate checkbox field
   * @param  string $key name of the field
   * @return string
   */
  public function validate_checkbox( $key ) {

    $status = '';
    if ( isset( $this->posted_data[ $key ] ) && ( 1 == $this->posted_data[ $key ] ) ) {
      $status = '1';
    }
    return $status;
  }

}
