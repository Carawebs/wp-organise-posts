<?php


namespace Carawebs\OrganisePosts\Settings;

/**
 * Factory class used to register fields and tabs
 *
 * @author  David Egan <david@carawebs.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package OrganisePosts
 */
class MenuTab {

  public $slug;

  public $title;

  public $menu;

  public function __construct( $options, MenuPage $menu ) {

    $this->slug = $options['slug'];
    $this->title = $options['title'];
    $this->menu = $menu;
    $this->menu->add_tab( $options );

  }

  /**
  * Add field to this tab
  * @param [type] $array [description]
  */
  public function add_field( $array ) {

    $this->menu->add_field( $array, $this->slug );

  }

}
