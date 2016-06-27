<?php
/*
 * This file is part of the gm-cookie-policy package.
 *
 * (c) David Egan <david@carawebs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carawebs\OrganisePosts;

/**
 * @author  David Egan <david@carawebs.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package OrganisePosts
 * @link    https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/
 * @link    http://phantombear.net/closure-bind-saved-me-from-reflection/
 * @link    http://php.net/manual/en/class.closure.php
 */
final class SimpleRenderer implements RendererInterface {
  /**
   * Simple render engine. Template files can access variables using `$this->variableName`.
   * No variables other than the ones passed in `$data` param are available in templates.
   *
   *   echo $this->renderer->render(
   *   dirname(__DIR__).'/templates/settings.php',
   *   $this->settingsContext()
   *   );
   *
   * Closure::bind — Duplicates a closure with a specific bound object and class scope
   *
   *
   * @param string $template Full path to template file to render
   * @param array $data
   * @return string
   */
  public function render( $template, array $data = [] ) {

      $context = (object) $data;

      $closure = function( $template ) {

        ob_start();

        if( is_readable( $template) ) {

          include( $template );

        }

        return trim( ob_get_clean() );

      };

      $renderer = \Closure::bind( $closure, $context, 'Carawebs\OrganisePosts\SimpleRenderer' );

      return $renderer( $template );

  }

}
