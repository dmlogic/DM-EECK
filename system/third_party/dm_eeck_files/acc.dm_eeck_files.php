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
class Dm_eeck_files_acc {

	public $name = 'DM EECK Category Image Browse';
	public $id = 'dm_eeck_cat_img_select';
	public $version = '1.1.2';
	public $description = 'Adds CK Finder select to Category Image URL';
	public $sections = array();

	private $eeck_settings;

	// -----------------------------------------------------------------

	/**
	 * Constructor
	 */
	function Dm_eeck_files_acc() {
		$this->EE =& get_instance();

	}

	// -----------------------------------------------------------------

	/**
	 * Replace the File Manager link with one to CK Finder
	 */
	function set_sections() {

		$this->sections[] = '<script type="text/javascript" charset="utf-8">$("#accessoryTabs a.'.$this->id.'").parent().remove();</script>';

		if($this->EE->input->get('C') == 'admin_content' && $this->EE->input->get('M') == 'category_edit' ) {

			// help!
			require_once(PATH_THIRD.'dm_eeck/includes/eeck_helper.php');
			$this->helper = new eeck_helper();

			// we'll need the settings from the Editor field type
			$this->eeck_settings = $this->helper->load_editor_settings();

			$this->include_finder_js();
		}

	}

	// -----------------------------------------------------------------

	/**
	 * Include all the necessary JS to run the finder and callback
	 *
	 * @return mixed
	 */
	private function include_finder_js() {

		if(isset($this->EE->session->cache['eeck']['finder_js']) ) {
			return;
		}

		$this->EE->session->cache['eeck']['finder_js'] = true;
		$this->helper->save_session_vars($this->eeck_settings);

		// prep data for JS functions
		$data = array(
			'eeck_ckfpath' => $this->eeck_settings['eeck_ckfpath'],
			'tgif' => '',
			'eeck_restype' => 'Images'
		);

		$this->helper->include_editor_js($this->eeck_settings['eeck_ckepath'], $this->eeck_settings['eeck_ckfpath']);

		// add js to head
		$this->EE->cp->add_js_script(array('plugin' => 'fancybox'));
		$this->EE->cp->add_to_head(
			$this->EE->javascript->inline( $this->EE->load->view('js',$data,true) ).
			'<script type="text/javascript" src="'.$this->EE->config->item('theme_folder_url').'third_party/dm_eeck/category_img.js"></script>'
		);

	}
}