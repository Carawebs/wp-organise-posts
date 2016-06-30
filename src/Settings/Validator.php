<?php
namespace Carawebs\OrganisePosts\Settings;

/**
 * Validator methods for user input in the settings pages
 *
 * Each method differs according to the type of field being validated, but each method:
 * - Returns existing relevant options data from the database
 * - Overrides this with the validated POSTed value if this is set
 */
trait Validator {

  /**
   * Validate text field
   *
   * @uses wp_kses_post()
   * @uses trim()
   * @uses stripslashes()
   * @param  string $key name of the field
   * @return string $text
   */
  public function validate_text( $key ) {

    $text  = $this->get_option( $key );

    if ( isset( $this->posted_data[ $key ] ) ) {

      $text = wp_kses_post( trim( stripslashes( $this->posted_data[ $key ] ) ) );

    }

    return $text;

  }

  /**
   * Validate textarea field - same as the wp_editor validation, so send it on!
   *
   * @param  string $key name of the field
   * @return string
   */
  public function validate_textarea( $key ) {

    return $this->validate_wpeditor( $key );

  }

  /**
   * Validate wp_editor field
   *
   * @uses wp_kses()
   * @uses trim()
   * @uses stripslashes()
   * @uses wp_kses_allowed_html()
   * @param  string $key name of the field
   * @return string
   */
  public function validate_wpeditor( $key ){
    $text  = $this->get_option( $key );

    if ( isset( $this->posted_data[ $key ] ) ) {

      $content = trim( stripslashes( $this->posted_data[ $key ] ) );

      $allowed_tags = array_merge(
        [ 'iframe' => [ 'src' => true, 'style' => true, 'id' => true, 'class' => true ] ],
        wp_kses_allowed_html( 'post' )
      );

      $text = wp_kses( $content, $allowed_tags );
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
