<?php
namespace Carawebs\OrganisePosts\Settings;

/**
 * Class that renders the menu settings page
 */
class RenderPage extends RenderFields {

  /**
  * Array of fields for each tab in this format:
  * `[ 'tab_slug' => [ 'field_name' => ['option_key'=>'option_value',] ] ]`
  * This array is populated as the `add_field()` method is called.
  * @var array
  */
  protected $fields = [];

  function __construct( $argument ) {
    # code...
  }

  /**
   * Create the menu page
   * @return void
   */
  public function create_menu_page() {

    $this->saveIfSubmitted();

    // The default tab is 'general'
    // @TODO: set the default tab as a property for better "Single Responsibility"
    $tab = 'general';

    // If on a tabbed page, set the tab accordingly
    if( isset( $_GET['tab'] ) ) {

      $tab = $_GET['tab'];

    }

    $this->init_settings();

    ?>
    <div class="wrap">
      <h2><?php echo $this->menu_options['page_title'] ?></h2>
      <?php if( !empty( $this->updated ) && true === $this->updated ) : ?>
      <div id="message" class="updated below-h2">
        <!-- <?php //if ( $_GET['msg'] == 'update' ) : ?> -->
          <p><?php _e( 'Settings saved.' ); ?></p>
        <!-- <?php //endif; ?> -->
      </div>
      <?php endif; ?>
      <?php var_dump($_POST); ?>
      <?php
      echo ! empty( $this->menu_options['desc'] )
        ? "<p class='description'>{$this->menu_options['desc']}</p>" : NULL;
      $this->render_tabs( $tab );
      ?>
      <form method="POST" action="">
        <div class="postbox">
          <div class="inside">
            <table class="form-table">
              <?php $this->render_fields( $tab ); ?>
            </table>
            <?php $this->save_button(); ?>
          </div>
        </div>
      </form>
    </div>
    <?php

  }

  /**
  * Add fields
  *
  * This method is called by the client code
  *
  * @param array $array options for the field to add
  * @param string $tab tab slug for this field, defaults to 'general'
  */
  public function add_field( $tab = 'general', $args ) {

    $allowed_field_types = [
      'text',
      'textarea',
      'wpeditor',
      'select',
      'radio',
      'checkbox',
      'cpt_selector'
    ];

    // If a type is set that is not allowed, don't add the field
    if( isset( $args['type'] ) && $args['type'] != '' && ! in_array( $args['type'], $allowed_field_types ) ) {

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
    $field_arguments = array_merge( $default_arguments, $args );

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

    //If there are options set but no default, then use the first option as a default value
    if( ! empty( $field_arguments['options'] ) && $field_arguments['default'] == '' ) {

      $options_keys = array_keys( $field_arguments['options'] );
      $field_arguments['default'] = $options_keys[0];

    }

    // If there is no fields array for this tab, initialize one
    if( ! isset( $this->fields[ $tab ] ) ) {

      $this->fields[ $tab ] = [];

    }

    // The field arguments array, under the specified tab
    $this->fields[ $tab ][ $field_arguments['name'] ] = $field_arguments;

    //var_dump($this->fields);//json_encode( $this->fields));

  }

  /**
   * Render the save button
   * @return void
   */
  protected function save_button() {

    ?>
    <button type="submit" name="<?= $this->settings_id; ?>_save" class="button button-primary">
      <?php _e( 'Save', 'organise-posts' ); ?>
    </button>
    <?php

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

          $admin_url  = admin_url('admin.php?page=' . $this->menu_options['slug'] . '&tab=' . $key );
          $tab_class   = ( $key == $active_tab ) ? ' nav-tab-active' : NULL;
          echo "<a href='$admin_url' class='nav-tab$tab_class'>$value</a>";


        }
      echo '</h2>';
      echo '<br/>';

    }

  }

}
