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
*/
class Controller
{
  /**
  * @var bool
  */
  private $isAdmin;

  /**
  * Controller constructor.
  */
  public function __construct() {

    $this->isAdmin = is_admin();

  }

  /**
  * Setup backend hooks.
  * Instantiate necessary objects if necessary.
  *
  * @param \Carawebs\OrganisePosts\Config                 $config
  * @param \Carawebs\OrganisePosts\SettingsPage|null      $settings
  * @param \Carawebs\OrganisePosts\RendererInterface|null $renderer
  */
  public function setupBackendActions( Config $config ) {

    if (! $this->isAdmin) {
      return;
    }

    $this->loadTextDomain();

    // Setup settings page
    // add_action('admin_menu', function () use ( $config, $settings, $renderer ) {
    //
    //   $renderer or $renderer = new SimpleRenderer();
    //   $settings or $settings = new SettingsPage($config, $renderer);
    //   $settings->setup();
    //
    // });
    //
    // // Save setting page form when submitted
    // add_action('admin_post_'.SettingsPage::ACTION, function () use ( $config, $settings, $renderer ) {
    //
    //   $renderer or $renderer = new SimpleRenderer();
    //   $settings or $settings = new SettingsPage($config, $renderer);
    //   $settings->save();
    //   exit();
    //
    // });

  }

  /**
  * Load text domain.
  */
  private function loadTextDomain() {
    // Load text domain
    $pathArr = explode(DIRECTORY_SEPARATOR, dirname(__DIR__));
    load_plugin_textdomain('organise-posts', false, end($pathArr).'/lang');

  }

}
