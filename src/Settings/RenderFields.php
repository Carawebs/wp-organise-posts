<?php
namespace Carawebs\OrganisePosts\Settings;

trait RenderFields {

  /**
  * Rendering fields
  * @param  string $tab slug of tab
  * @return void
  */
  public function render_fields( $tab ) {

    if( ! isset( $this->fields[ $tab ] ) ) {
      echo '<p>' . __( 'There are no settings on these page.', 'textdomain' ) . '</p>';
      return;
    }
    foreach ( $this->fields[ $tab ] as $name => $field ) {

      $this->{ 'render_' . $field['type'] }( $field );

    }

  }

  /**
  * Render text field
  * @param  string $field options
  * @return void
  */
  public function render_text( $field ){
    extract( $field );
    ?>

    <tr>
      <th>
        <label for="<?php echo $name; ?>"><?php echo $title; ?></label>
      </th>
      <td>
        <input type="<?php echo $type; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo $default; ?>" placeholder="<?php echo $placeholder; ?>" />
        <?php if( $desc != '' ) {
          echo '<p class="description">' . $desc . '</p>';
        }?>
      </td>
    </tr>

    <?php
  }

  /**
  * Render textarea field
  * @param  string $field options
  * @return void
  */
  public function render_textarea( $field ){
    extract( $field );
    ?>

    <tr>
      <th>
        <label for="<?php echo $name; ?>"><?php echo $title; ?></label>
      </th>
      <td>
        <textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" placeholder="<?php echo $placeholder; ?>" ><?php echo $default; ?></textarea>
        <?php if( $desc != '' ) {
          echo '<p class="description">' . $desc . '</p>';
        }?>
      </td>
    </tr>

    <?php
  }

  /**
  * Render WPEditor field
  * @param  string $field  options
  * @return void
  */
  public function render_wpeditor( $field ){

    extract( $field );
    ?>

    <tr>
      <th>
        <label for="<?php echo $name; ?>"><?php echo $title; ?></label>
      </th>
      <td>
        <?php wp_editor( $default, $name, array('wpautop' => false) ); ?>
        <?php if( $desc != '' ) {
          echo '<p class="description">' . $desc . '</p>';
        }?>
      </td>
    </tr>

    <?php
  }

  /**
  * Render select field
  * @param  string $field options
  * @return void
  */
  public function render_select( $field ) {
    extract( $field );
    ?>

    <tr>
      <th>
        <label for="<?php echo $name; ?>"><?php echo $title; ?></label>
      </th>
      <td>
        <select name="<?php echo $name; ?>" id="<?php echo $name; ?>" >
          <?php
          foreach ($options as $value => $text) {
            echo '<option ' . selected( $default, $value, false ) . ' value="' . $value . '">' . $text . '</option>';
          }
          ?>
        </select>
        <?php if( $desc != '' ) {
          echo '<p class="description">' . $desc . '</p>';
        }?>
      </td>
    </tr>

    <?php
  }

  /**
  * Render radio
  * @param  string $field options
  * @return void
  */
  public function render_radio( $field ) {
    extract( $field );
    ?>

    <tr>
      <th>
        <label for="<?php echo $name; ?>"><?php echo $title; ?></label>
      </th>
      <td>
        <?php
        foreach ($options as $value => $text) {
          echo '<input name="' . $name . '" id="' . $name . '" type="'.  $type . '" ' . checked( $default, $value, false ) . ' value="' . $value . '">' . $text . '</option><br/>';
        }
        ?>
        <?php if( $desc != '' ) {
          echo '<p class="description">' . $desc . '</p>';
        }?>
      </td>
    </tr>

    <?php
  }
  
  /**
  * Render checkbox field
  * @param  string $field options
  * @return void
  */
  public function render_checkbox( $field ) {
    extract( $field );
    ?>

    <tr>
      <th>
        <label for="<?php echo $name; ?>"><?php echo $title; ?></label>
      </th>
      <td>
        <input <?php checked( $default, '1', true ); ?> type="<?php echo $type; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="1" placeholder="<?php echo $placeholder; ?>" />
        <?php echo $desc; ?>
      </td>
    </tr>

    <?php
  }

}
