<?php
return [

  // Page Options
  // ---------------------------------------------------------------------------
  'default_page_options' => [
    'slug'        => 'organise-posts',          // Name of the menu item
    'title'       => 'Fantastic Page',          // Title displayed on the top of the admin panel
    'page_title'  => 'Fantastic Page',         // Settings Page Title
    'parent'      => NULL,                      // id of parent, if blank, then this is a top level menu
    'id'          => '',                        // Unique ID of the menu item
    'capability'  => 'manage_options',          // User role
    'icon'        => 'dashicons-admin-generic', // Menu icon for top level menus only http://melchoyce.github.io/dashicons/
    'position'    => NULL,                      // Menu position. Can be used for both top and sub level menus
    'desc'        => '',                        // Description displayed below the title
    'function'    => ''
  ],

  // Set up tabs
  // ---------------------------------------------------------------------------
  'tabs' => [
    'general'   => [
      'slug' => 'general',
      'title' => 'General Settings'
    ],
    'contact'   => [
      'slug' => 'email_settings',
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
      'name'  => 'address_line_1',
      'title' => 'Address Line One',
      'desc'  => 'The first line'
    ],
    [
      'name'  => 'checkbox',
      'title' => 'Checkbox Example',
      'desc'  => 'Check it to wake it',
      'type'  => 'checkbox'
    ],
    [
      'name' => 'radio',
      'title' => 'Radio Example',
      'desc' => 'Make a selection',
      'type' => 'radio',
      'options' => [
        '1' => 'Radio One',
        '2' => 'Radio Two'
        ]
    ],
    [
      'name' => 'main_wpeditor',
      'type' => 'wpeditor',
      'title' => 'WYSIWYG Input',
      'desc' => 'Input Description'
    ]
  ]
];
