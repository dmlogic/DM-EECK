<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * DM EECK v1.1.2 for Expression Engine 2
 *
 * This software is copywright DM Logic Ltd
 * www.dmlogic.com
 *
 * Licensed under the Open Software License version 3.0
 * http://opensource.org/licenses/OSL-3.0
 *
 */
class Dm_eeck_acc {

	public $name = 'DM EECK Accessory';
	public $id = 'dm_eeck_menu_acc';
	public $version = '1.1.2';
	public $description = 'Replaces link to File Manager with link to CK Finder library. Provides interface for ee_link plugin';

	// -----------------------------------------------------------------

	function Dm_eeck_acc() {
		$this->EE =& get_instance();

	}

	// -----------------------------------------------------------------
	
	/**
	 * Replace the File Manager link with one to CK Finder
	 */
	public function set_sections() {
		
		$this->EE->load->library('javascript');

		$str = <<<END
dm_eeck_fl_link = $("#navigationTabs a[href$=content_files]");
if(dm_eeck_fl_link.size()) {
	dm_eeck_fl_link.attr("href",dm_eeck_fl_link.attr("href").replace('C=content_files','C=addons_modules&M=show_module_cp&module=dm_eeck'));
	$("#accessoryTabs .dm_eeck_menu_acc").parent().remove();
}
END;
		$this->EE->javascript->output($str);
	}
}