<?php
 return
array (
  'default_page_options' => 
  array (
    'slug' => 'carawebs_yaml_organise_posts',
    'title' => 'YAML Organise',
    'page_title' => 'YAML Organise Posts',
    'parent' => NULL,
    'id' => NULL,
    'capability' => 'manage_options',
    'icon' => 'dashicons-format-gallery',
    'position' => 89,
    'desc' => 'This is the description',
  ),
  'tabs' => 
  array (
    'general' => 
    array (
      'slug' => 'general',
      'title' => 'General Settings',
    ),
    'contact' => 
    array (
      'slug' => 'contact',
      'title' => 'Email Settings',
    ),
    'more' => 
    array (
      'slug' => 'more',
      'title' => 'More Settings',
    ),
  ),
  'fields' => 
  array (
    0 => 
    array (
      'tab' => 'general',
      'args' => 
      array (
        'name' => 'textarea_content',
        'title' => 'Text Content',
        'desc' => 'The first line',
        'type' => 'textarea',
      ),
    ),
    1 => 
    array (
      'tab' => 'general',
      'args' => 
      array (
        'name' => 'address_line_2',
        'title' => 'Address Line Two',
        'desc' => 'The second line',
        'type' => 'select',
        'options' => 
        array (
          'one' => 'First Option',
          'two' => 'Second Option',
          'third' => 'Third Option',
        ),
      ),
    ),
    2 => 
    array (
      'tab' => 'general',
      'args' => 
      array (
        'name' => 'main_content',
        'title' => 'Some Content',
        'desc' => 'add some wysiwyg magic!',
        'type' => 'wpeditor',
      ),
    ),
    3 => 
    array (
      'tab' => 'general',
      'args' => 
      array (
        'name' => 'radio_test',
        'title' => 'Radio Test',
        'desc' => 'radio radio!',
        'type' => 'radio',
        'options' => 
        array (
          'one' => 'First Option',
          'two' => 'Second Option',
          'third' => 'Third Option',
        ),
      ),
    ),
    4 => 
    array (
      'tab' => 'contact',
      'args' => 
      array (
        'name' => 'town',
        'title' => 'Town',
        'desc' => 'Enter the town',
      ),
    ),
    5 => 
    array (
      'tab' => 'more',
      'args' => 
      array (
        'name' => 'more',
        'title' => 'More stuff',
        'desc' => 'MOre The second line',
        'type' => 'checkbox',
      ),
    ),
  ),
);