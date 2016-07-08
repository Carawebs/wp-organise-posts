<?php
namespace Carawebs\OrganisePosts\Settings;

class RenderFields {

  /**
  * Rendering fields
  * @param  string $tab slug of tab
  * @return void
  */
  public function render_fields( $tab ) {

    if( ! isset( $this->fields[ $tab ] ) ) {
      echo '<p>' . __( 'There are no settings for this page.', 'textdomain' ) . '</p>';
      return;
    }

    $this->nonce();

    foreach ( $this->fields[ $tab ] as $name => $field ) {

      //var_dump( $this->fields[ $tab ] );

      $this->{ 'render_' . $field['type'] }( $field );

    }

  }

  /**
   * Output nonce fields
   * @return string HTML markup for nonce fields
   */
  public function nonce() {

    ob_start();
    ?>
    <input type="hidden" name="action" value="<?= esc_attr( $this->action ) ?>">
    <input type="hidden" name="<?= esc_attr( $this->nonce_key ) ?>"
      value="<?= esc_attr( wp_create_nonce( $this->nonce_action ) ) ?>">
    <?php
    echo ob_get_clean();

  }

  /**
  * Render text field
  * @param  string $field options
  * @return string HTML markup for text field
  */
  public function render_text( $field ) {

    extract( $field );
    ob_start();
    echo "<input type='$type' name='$name' id='$name' value='$default' placeholder='$placeholder'/>";
    echo ! empty( $desc ) ? "<p class='description'>$desc</p>" : NULL;
    $fieldSpecific = ob_get_clean();
    echo $this->genericField( $fieldSpecific, $name, $title );

  }

  /**
  * Render textarea field
  * @param  string $field options
  * @return string HTML markup for textarea field
  */
  public function render_textarea( $field ) {

    extract( $field );
    ob_start();
    echo "<textarea name='$name' id='$name' placeholder='$placeholder'>$default</textarea>";
    echo ! empty( $desc ) ? "<p class='description'>$desc</p>" : NULL;
    $fieldSpecific = ob_get_clean();
    echo $this->genericField( $fieldSpecific, $name, $title );

  }

  /**
  * Render WPEditor field
  * @param  string $field  options
  * @return string HTML markup for wp_editor
  */
  public function render_wpeditor( $field ){

    extract( $field );
    ob_start();
    wp_editor( $default, $name, array('wpautop' => false) );
    echo ! empty( $desc ) ? "<p class='description'>$desc</p>" : NULL;
    $fieldSpecific = ob_get_clean();
    echo $this->genericField( $fieldSpecific, $name, $title );

  }

  /**
  * Render select field
  * @param  string $field options
  * @return string HTML markup for select field
  */
  public function render_select( $field ) {

    extract( $field );
    ob_start();
    echo "<select name='$name' id='$name'>";
      foreach ( $options as $value => $text ) {
        echo "<option " . selected( $default, $value, false ) . " value='$value'>$text</option>";
      }
    echo "</select>";
    echo ! empty( $desc ) ? "<p class='description'>$desc</p>" : NULL;
    $fieldSpecific = ob_get_clean();
    echo $this->genericField( $fieldSpecific, $name, $title );

  }

  /**
  * Render radio
  * @param  string $field options
  * @return string HTML markup for radio field
  */
  public function render_radio( $field ) {

    extract( $field );
    ob_start();
    foreach ( $options as $value => $text ) {
      echo "<input name='$name' id='$name' type='$type' " . checked( $default, $value, false ) . "value='$value'>$text</option><br/>";
    }
    echo ! empty( $desc ) ? "<p class='description'>$desc</p>" : NULL;
    $fieldSpecific = ob_get_clean();
    echo $this->genericField( $fieldSpecific, $name, $title );

  }

  /**
  * Render checkbox field
  * @param  string $field options
  * @return string HTML markup for checkbox field
  */
  public function render_checkbox( $field ) {
    //error_log( json_encode($field));

    extract( $field );
    ob_start();
    ?>
    <input type="hidden" name="<?= $name; ?>" value="0">
    <input <?php checked( $default, '1', true ); ?> type="<?= $type; ?>" name="<?= $name; ?>" id="<?= $name; ?>" value="1" placeholder="<?= $placeholder; ?>" />
    <?php
    echo ! empty( $desc ) ? "<p class='description'>$desc</p>" : NULL;
    $fieldSpecific = ob_get_clean();
    echo $this->genericField( $fieldSpecific, $name, $title );

  }

  /**
  * Render custom-post-type selector
  * @param  string $field options
  * @return string HTML markup for checkbox field
  */
  public function render_cpt_selector( $field ) {

    extract( $field );
    $post_types = get_post_types( [
      'show_ui' => true,
      'show_in_menu' => true,
      ],
      'objects' );
    $disallowed = ['attachment', 'thinking', 'extra-content', 'page', 'post'];

    ob_start();

    foreach ( $post_types  as $post_type ) {

      if( in_array( $post_type->name, $disallowed ) ) continue;
      //if ( $post_type->name == 'attachment' ) continue;
      $checked = NULL;
      if ( isset( $default ) && is_array( $default ) ) {
        $checked = in_array( $post_type->name, $default ) ? "checked='checked'" : NULL;
      }
    echo "<input type='hidden' name='{$name}[]' value='0'>";
    echo "<label><input type='checkbox' name='{$name}[]' value='{$post_type->name}'$checked>&nbsp;$post_type->label</label><br>";

    }

    echo ! empty( $desc ) ? "<p class='description'>$desc</p>" : NULL;

    $fieldSpecific = ob_get_clean();
    echo $this->genericField( $fieldSpecific, $name, $title );

  }

  public function render_message( $field ) {

    extract( $field );

    ob_start();
    include_once( CARAWEBS_ORGANISE_POSTS_PATH . $file );
    echo ob_get_clean();

  }

  /**
   * Generic field markup
   * @param  string $fieldSpecific Specific field markup
   * @param  string $name          Name
   * @param  string $title         Title
   * @return string                Field markup with field-specific markup inserted
   */
  public function genericField( $fieldSpecific, $name, $title ) {

    ob_start();
    ?>
    <tr>
      <th>
        <label for="<?= $name; ?>"><?= $title; ?></label>
      </th>
      <td>
        <?= $fieldSpecific ?>
      </td>
    </tr>
    <?php
    return ob_get_clean();

  }

}
