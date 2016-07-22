<?php
namespace Carawebs\OrganisePosts\Settings;

/**
 * Class holds config data
 */
class Config {

  public function __construct( $plugin_slug, $yamlParser = NULL, $datafile = NULL ) {

    $this->yamlParser   = $yamlParser;
    $this->datafile     = ! empty( $datafile ) ? CARAWEBS_ORGANISE_POSTS_PATH . $datafile : CARAWEBS_ORGANISE_POSTS_PATH . 'src/Settings/settings-data.php';
    $this->plugin_slug  = $plugin_slug;
    $this->setConfig();
    $this->settings_id  = $this->config['default_page_options']['slug'];

  }

  /**
   * The config data
   */
  public function setConfig() {

    if( 'yml' === pathinfo( $this->datafile, PATHINFO_EXTENSION ) ) {

      $this->config = $this->convertYAML();

      // $openingString = "<?php\n return\n";
      // $closingString = ";";
      // $fp = fopen( CARAWEBS_ORGANISE_POSTS_PATH . 'src/Settings/settings.php', 'w' );
      // fwrite( $fp, $openingString . var_export($this->config, TRUE ) . $closingString );
      // fclose( $fp );

    } else {

      $this->config = include_once( $this->datafile );

    }

  }

  public function getConfig() {

    return $this->config;

  }

  public function convertYAML() {

    return $this->yamlParser->parse( file_get_contents( $this->datafile ) );

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
