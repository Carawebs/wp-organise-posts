<?php
namespace Carawebs\OrganisePosts\Settings;

class Config {

  public $menu_page_args = [
    'slug'      => 'test_wpmenu',
    'title'     => 'Organise Posts',
    'desc'      => 'Settings for theme custom WordPress Menu',
    'icon'      => 'dashicons-welcome-widgets-menus',
    'position'  => 99,
  ];

  public function settings() {

    $menu_page = new \Carawebs\OrganisePosts\Settings\MenuPage( $this->menu_page_args, 'options-general.php' );
    // Build a settings page as a sub-page of an existing menu item (in this case, under the "Settings" Menu)
    // $menu_page = new \Carawebs\OrganisePosts\Settings\Menu( $menu_page_args, 'options-general.php' );

    $menu_page->add_field(
      [
        'name'  => 'address_line_1',
        'title' => 'Address Line One',
        'desc'  => 'The first line'
      ]
    );

    $menu_page->add_field(
      [
        'name'  => 'checkbox',
        'title' => 'Checkbox Example',
        'desc'  => 'Check it to wake it',
        'type'  => 'checkbox'
      ]
    );

    $menu_page->add_field(
      [
        'name' => 'radio',
        'title' => 'Radio Example',
        'desc' => 'Make a selection',
        'type' => 'radio',
        'options' => [
          '1' => 'Radio One',
          '2' => 'Radio Two'
          ]
      ]
    );

    $menu_page->add_field(
      [
        'name' => 'radio2',
        'title' => 'Radio Example 2',
        'desc' => 'Make a selection',
        'type' => 'radio',
        'options' => [
          'one' => 'Radio One',
          'two' => 'Radio Two',
          'three' => 'Radio Three'
          ]
      ]
    );

    $menu_page->add_field(
      [
        'name' => 'main_wpeditor',
        'type' => 'wpeditor',
        'title' => 'WYSIWYG Input',
        'desc' => 'Input Description'
      ]
    );

    // Creating tab with our custom wordpress menu
    $customTab = new \Carawebs\OrganisePosts\Settings\MenuTab(
      [
        'slug' => 'email_settings',
        'title' => 'Email Settings'
      ],
      $menu_page );

    $customTab->add_field(
      [
        'name' => 'main_email',
        'title' => 'Main Email',
        'type' => 'text',
      ] );

    // Creating tab with our custom wordpress menu
    $SMTab = new \Carawebs\OrganisePosts\Settings\MenuTab(
      [
        'slug' => 'social_media_settings',
        'title' => 'Social Media'
      ],
      $menu_page );

    $SMTab->add_field(
      [
        'name' => 'facebook',
        'title' => 'Facebook',
        'type' => 'text',
      ]);

    $SMTab->add_field(
      [
        'name' => 'twitter',
        'title' => 'Twitter',
        'type' => 'text',
      ]);

    $SMTab->add_field(
      [
        'name' => 'sm_wpeditor',
        'type' => 'wpeditor',
        'title' => 'WYSIWYG Input',
        'desc' => 'Enter some stuff'
      ]
    );

  }

}
