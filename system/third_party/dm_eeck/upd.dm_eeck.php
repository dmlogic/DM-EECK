<?php
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
class Dm_eeck_upd {

	var $version	=	'1.1.2';
	var $module_name=	'Dm_eeck';
	
	// -----------------------------------------------------------------

	function Dm_eeck_upd(){
		$this->EE =& get_instance();
	}	
	
	// -----------------------------------------------------------------

	/**
	 * Installer
	 *
	 * @return boolean
	 */
	public function install() {

		// install module
		$module = array(	'module_name' => $this->module_name,
							'module_version' => $this->version,
							'has_cp_backend' => 'y',
							'has_publish_fields' => 'n' );

		$this->EE->db->insert('modules', $module);

		return TRUE;
	}

	// -----------------------------------------------------------------

	/**
	 * Unintaller
	 *
	 * @return boolean
	 */
	public function uninstall()	{

		$this->EE->db->where('module_name', $this->module_name);
		$this->EE->db->delete('modules');

		return TRUE;
	}

	// -----------------------------------------------------------------

	/**
	 * Keeps version number up-to-date
	 * 
	 * @param float $current
	 * @return boolean 
	 */
	public function update($current = '') {

		if ($current == $this->version) {
			return FALSE;
		}

		$this->EE->db->where('module_name',$this->module_name);
		$this->EE->db->update('modules',array(  'module_version' => $this->version));

		return TRUE;
	}

}