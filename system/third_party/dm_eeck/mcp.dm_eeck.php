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
class Dm_eeck_mcp {

	private $helper;
	private $eeck_settings;

	public function Dm_eeck_mcp(){
		$this->EE =& get_instance();

		require_once(PATH_THIRD.'dm_eeck/includes/eeck_helper.php');
		$this->helper = new eeck_helper();
	}

	// -----------------------------------------------------------------

	/**
	 * Display library
	 * 
	 * @return string 
	 */
	public function index() {

		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('dm_eeck'));

		// add all the js
		$this->eeck_settings = $this->helper->load_editor_settings();
		$this->helper->save_session_vars($this->eeck_settings);
		$this->helper->include_editor_js($this->eeck_settings['eeck_ckepath'], $this->eeck_settings['eeck_ckfpath']);

		$data = array('restypes' => array());
		foreach($this->eeck_settings['eeck_resourcetypes'] as $type) {
			$data['restypes'][] = $type['name'];
		}

		if(in_array('Images', $data['restypes'])) {
			$data['selected_type'] = 'Images';
 		} else {
			$data['selected_type'] = current($data['restypes']);
		}

		return $this->EE->load->view('standalone',$data,true);
	}
}