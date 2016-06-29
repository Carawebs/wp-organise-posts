<?php
namespace Carawebs\OrganisePosts\Settings;

trait Config {

  public function getConfig( $configName = NULL ) {

    if( NULL != $configName ) {

      $keyName = __TRAIT__ . $configName;

      if ( ! Container::exists( $keyName ) ) {

          Container::set( $keyName, new \Lib\Config($configName) );

      }

    } else {

      // the config is set in this trait
      return $this->config;

    }

  }

  private $config = [



  ];

}
