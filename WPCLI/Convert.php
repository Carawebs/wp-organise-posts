<?php
namespace Carawebs\OrganisePosts\WPCLI;

class Convert extends \WP_CLI_Command {

  /**
   * Convert from YAML to PHP array
   *
   * <source>
   * : A required filename
   *
   * <destination>
   * : A required filename
   *
   * @alias cw
   *
   * @param  [type] $args      [description]
   * @param  [type] $assocArgs [description]
   * @return [type]            [description]
   */
  public function carawebs_convert( $args, $assocArgs ) {

    list( $source, $destination ) = $args;

    \WP_CLI::log( json_encode( $args ) );
    \WP_CLI::log( "The source file is $source and the destination file is $destination" );

    \WP_CLI::success( 'Hello everybody! xxx' );

  }

}

\WP_CLI::add_command( 'convert', __NAMESPACE__ . '\Convert' );
