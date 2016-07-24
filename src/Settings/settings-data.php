<?php
$settings = array (
  'default_page_options' =>
    array (
      'slug' => 'carawebs_organiser',
      'title' => 'Organiser',
      'page_title' => 'Organiser',
      'parent' => NULL,
      'id' => NULL,
      'capability' => 'manage_options',
      'icon' => 'dashicons-admin-settings',
      'position' => 89,
      'desc' => 'Settings for the post organiser drag-and-drop functionality',
    ),
  'tabs' =>
    array (
      'general' =>
        array (
          'slug' => 'general',
          'title' => 'Main Settings',
        ),
      'contact' =>
        array (
          'slug' => 'help',
          'title' => 'Help',
        ),
    ),
  'fields' =>
    array (
      0 => array (
        'tab' => 'general',
        'args' =>
          array (
            'type' => 'cpt_selector',
            'name' => 'CPTs',
            'title' => 'Select Post Types',
            'desc' => 'Select the post types for which you\'d like to allow sorting',
            'default' => 0,
          ),
      ),
      1 => array (
        'tab' => 'help',
        'args' =>
          array (
            'name' => 'help',
            'title' => 'Main Help',
            'desc' => NULL,
            'type' => 'message',
            'file' => '/templates/message.php',
          ),
      ),
    ),
);
return $settings;
