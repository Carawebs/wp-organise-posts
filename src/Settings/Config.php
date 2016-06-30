<?php
namespace Carawebs\OrganisePosts\Settings;

/**
 * Class holds config data
 */
class Config {

  public function __construct( $plugin_slug ) {

    $this->plugin_slug = $plugin_slug;
    $this->setConfig();
    $this->settings_id = $this->config['default_page_options']['slug'];

  }

  /**
   * The config data
   */
  public function setConfig() {

    $this->config = include_once( CW_ORGANISE_POSTS_PLUGIN_PATH . 'src/Settings/data.php' );

  }

  public function getConfig() {

    return $this->config;

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

}
