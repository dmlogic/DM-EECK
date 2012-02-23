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
class Dm_eeck_ext {

	public $settings        = array();
    public $name            = 'CK Editor for Expression Engine';
    public $description     = 'Adds authentication for CK Finder';
    public $settings_exist  = 'n';
    public $docs_url        = 'http://dmlogic.com';
	public $version			= '1.1.2';

	// -----------------------------------------------------------------

	function Dm_eeck_ext() {

		$this->EE =& get_instance();

		if ( ! isset($_SESSION)) @session_start();
	}

	// -----------------------------------------------------------------

	/**
	 * Ensure authentication is not left behind when we logout
	 * That's the sole reason this ext is here...
	 */
	public function logout_user() {
		if(isset($_SESSION['eeck'])) {
			unset($_SESSION['eeck']);
		}
	}

	// -----------------------------------------------------------------

	/**
	 * Enable the extension
	 */
	function activate_extension() {

		$this->EE->db->insert('extensions', array(
			'extension_id' => '',
			'class'	=> __CLASS__,
			'method' => "logout_user",
			'hook' => "cp_member_logout",
			'settings' => '',
			'priority' => 10,
			'version'	=> $this->version,
			'enabled'	=> "y")
		);
	}

	// -----------------------------------------------------------------

	/**
	 * Disable the extension
	 */
	function disable_extension() {

		$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->delete('extensions');
	}

	// -----------------------------------------------------------------

	/**
	 * Update extension
	 */
	function update_extension($current='') {

		if ($current == '' OR $current == $this->version) {
			return FALSE;
		}

		$data = array();
		$data['version'] = $this->version;
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', $data);
	}
}