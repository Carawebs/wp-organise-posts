<?php

/**
 * Data File holding config data for the plugin settings
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

return [

  // Page Options
  // ---------------------------------------------------------------------------
  'default_page_options' => [

    // Name of the menu item, and the options table key
    // -------------------------------------------------------------------------
    'slug'        => 'carawebs_organise_posts',

    // Title displayed on the top of the admin panel
    'title'       => 'Organise Posts',

    // Settings page title
    'page_title'  => 'Organise Posts',

    // slug of parent page - if NULL, then this is a top level menu
    // Options:
    // 'options-general.php'  Sub Page under 'Settings' menu item
    // 'tools.php'            Sub page under 'Tools' menu item
    'parent'      => NULL,

    // Unique ID of the menu item
    'id'          => NULL,

    // User role necessary to access the settings page
    'capability'  => 'manage_options',

    // Icon URL | Dashicons helper class | 'none' | base64-encoded SVG using a data URI
    // @see https://developer.wordpress.org/resource/dashicons/#format-gallery
    'icon'        => 'dashicons-format-gallery',

    // position in the menu order this menu item will appear
    'position'    => '89',

    // Description displayed below the title
    'desc'        => 'This is the description',

  ],

  // Set up tabs
  // ---------------------------------------------------------------------------
  'tabs' => [
    'general'   => [
      'slug' => 'general',
      'title' => 'General Settings'
    ],
    'contact'   => [
      'slug' => 'contact',
      'title' => 'Email Settings'
    ],
    'social_media' => [
      'slug' => 'social_media_settings',
      'title' => 'Social Media Settings'
    ]
  ],

  // Set up fields
  // ---------------------------------------------------------------------------
  'fields' => [
    [
      'tab' => 'general',
      'args' => [
        'name'  => 'address_line_1',
        'title' => 'Address Line One',
        'desc'  => 'The first line'
      ]
    ],
    [
      'tab' => 'general',
      'args' => [
        'name'  => 'checkbox',
        'title' => 'Checkbox Example',
        'desc'  => 'Check it to wake it',
        'type'  => 'checkbox'
      ]
    ],
    [
      'tab' => 'general',
      'args' => [
        'name' => 'radio',
        'title' => 'Radio Example',
        'desc' => 'Make a selection',
        'type' => 'radio',
        'options' => [
          '1' => 'Radio One',
          '2' => 'Radio Two'
        ]
      ]
    ],
    [
      'tab' => 'social_media_settings',
      'args' => [
        'name' => 'sm_wpeditor',
        'type' => 'wpeditor',
        'title' => 'WYSIWYG Input',
        'desc' => 'Input Description'
      ]
    ],
    [
      'tab' => 'contact',
      'args' => [
        'name' => 'main_wpeditor',
        'type' => 'wpeditor',
        'title' => 'WYSIWYG Input',
        'desc' => 'Input Description'
      ]
    ],
    [
      'tab' => 'contact',
      'args' => [
        'name' => 'checkboxer',
        'type' => 'radio',
        'title' => 'Make a selection',
        'desc' => 'Input Description',
        'options' => [
          '1' => 'Radio One',
          '2' => 'Radio Two'
        ]
      ]
    ],
    [
      'tab' => 'social_media_settings',
      'args' => [
        'name' => 'facebook',
        'type' => 'text',
        'title' => 'Facebook',
        'desc' => 'Enter the Facebook link',
      ]
    ],
    [
      'tab' => 'social_media_settings',
      'args' => [
        'name' => 'twitter',
        'type' => 'text',
        'title' => 'Twitter',
        'desc' => 'Enter the Twitter link',
      ]
    ]
  ]
];
