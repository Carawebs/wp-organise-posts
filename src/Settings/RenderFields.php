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

      $this->{ 'render_' . $field['type'] }( $field );

    }

  }

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
  * @return void
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
  * @return void
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
  * @return void
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
  * @return void
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
  * @return void
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
  * @return void
  */
  public function render_checkbox( $field ) {

    extract( $field );
    ob_start();
    ?>
    <input <?php checked( $default, '1', true ); ?> type="<?= $type; ?>" name="<?= $name; ?>" id="<?= $name; ?>" value="1" placeholder="<?= $placeholder; ?>" />
    <?= $desc;
    $fieldSpecific = ob_get_clean();
    echo $this->genericField( $fieldSpecific, $name, $title );

  }

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
