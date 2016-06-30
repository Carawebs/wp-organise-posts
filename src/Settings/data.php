<?php
return [

  // Page Options
  // ---------------------------------------------------------------------------
  'default_page_options' => [
    'slug'        => 'test_wpmenu',             // Name of the menu item
    'title'       => 'Fantastic Page',          // Title displayed on the top of the admin panel
    'page_title'  => 'Fantastic Page',          // Settings Page Title
    'parent'      => NULL,                      // id of parent, if blank, then this is a top level menu
    'id'          => '',                        // Unique ID of the menu item
    'capability'  => 'manage_options',          // User role
    'icon'        => 'dashicons-admin-generic', // Menu icon for top level menus only http://melchoyce.github.io/dashicons/
    'position'    => NULL,                      // Menu position. Can be used for both top and sub level menus
    'desc'        => 'This is the description', // Description displayed below the title
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
