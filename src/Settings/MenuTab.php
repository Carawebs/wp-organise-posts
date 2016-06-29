<?php


namespace Carawebs\OrganisePosts\Settings;

/**
 * Factory class used to register fields under a unique tab on a menu page
 *
 * @see http://www.ibenic.com/creating-wordpress-menu-pages-oop/
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
  * @param array $array Field arguments
  */
  public function add_field( $array ) {

    $this->menu->add_field( $array, $this->slug );

  }

}
