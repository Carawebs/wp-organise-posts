<?php
namespace Carawebs\OrganisePosts\Settings;

class SubMenu extends Menu {
	function __construct( $options, Menu $parent ) {

		parent::__construct( $options );
		$this->parent_id = $parent->settings_id;

	}
  
}
