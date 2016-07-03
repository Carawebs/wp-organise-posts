<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    PluginName
 * @subpackage PluginName/includes
 */

namespace Carawebs\OrganisePosts;

/**
* Fired during plugin activation.
*
* This class defines all code necessary to run during the plugin's activation.
*
* @since      1.0.0
* @package    PluginName
* @subpackage PluginName/includes
* @author     Your Name <email@example.com>
*/
class Activator {

 /**
 * Short Description. (use period)
 *
 * Long Description.
 *
 * @since    1.0.0
 */
 public function activate() {

   error_log( "ACTIVATED, from the  " . get_class(). " CLASS, yeah!!!!!!");

 }

}
