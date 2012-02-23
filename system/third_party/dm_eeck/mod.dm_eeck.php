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
class Dm_eeck {

	public function __construct() {
		$this->EE =& get_instance();
	}

	// -----------------------------------------------------------------

	public function authenticate() {

		require_once(PATH_THIRD.'dm_eeck/includes/eeck_helper.php');
		$this->helper = new eeck_helper();
		$this->helper->save_session_vars();
	}

}