<?php
/*  Copyright 2021 4wp

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

require_once( PFFW_INCLUDES_DIR . '/addon.class.php' );

require_once( dirname(__FILE__) . '/capabilities-admin-interface.class.php' );

if (!class_exists('PFFW_CapabilitiesAddOn')) :

/**
 * Add-on to manage capabilities used in the customer area
*
* 
*/
class PFFW_CapabilitiesAddOn extends PFFW_AddOn {

	public function run_addon( $plugin ) {
		$this->plugin = $plugin;
		
		// Init the admin interface if needed
		if ( is_admin() ) {
			$this->admin_interface = new PFFW_CapabilitiesAdminInterface( $plugin, $this );
		} 
	}	
	
	/** @var PFFW_Plugin */
	private $plugin;

	/** @var PFFW_CapabilitiesAdminInterface */
	private $admin_interface;
}

// Make sure the addon is loaded
global $pffw_caps_addon;
$pffw_caps_addon = new PFFW_CapabilitiesAddOn();

endif; // if (!class_exists('PFFW_CapabilitiesAddOn')) 
