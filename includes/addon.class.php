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


if (!class_exists('PFFW_AddOn')) :

/**
 * The base class for addons
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class PFFW_AddOn {

	public function __construct() {
		add_action( 'pffw_addons_init', array( &$this, 'run_addon' ) );
	}
	
	/**
	 * Addons should implement this method to do their initialisation
	 * 
	 * @param PFFW_Plugin $pffw_plugin The plugin instance
	 */
	public abstract function run_addon( $pffw_plugin );
}

endif; // PFFW_AddOn