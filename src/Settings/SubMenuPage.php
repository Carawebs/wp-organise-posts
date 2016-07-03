<?php
namespace Carawebs\OrganisePosts\Settings;

class SubMenuPage extends MenuPage {
	function __construct( Config $config, MenuPage $parent ) {

		parent::__construct( $config );
		$this->parent_id = $parent->settings_id;
	}

}
