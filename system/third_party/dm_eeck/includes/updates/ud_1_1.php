<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dm_eeck_ud_1_1 {

	public function __construct() {
		$this->EE =& get_instance();
	}

	// -----------------------------------------------------------------

	public function do_update($settings) {
		
		// only do this in the control panel as it might take a while
		if(!defined('REQ') || REQ !== 'CP') {
			return;
		}

		// upgrade all 'native' fieldtypes
		$this->upgrade_ee_fields();

		// now we upgrade matrix
		$this->upgrade_matrix_fields();

		// upgrade our version number
		$settings['eeck_version'] = '1.1.2';
		$this->EE->db->where('name','dm_eeck');
		$this->EE->db->update('fieldtypes',array('settings' =>  base64_encode( serialize($settings) ) ) );

		// done
		unset($this->EE->session->cache['eeck']);
		unset($_SESSION['eeck']);

		// we must reload the page if we're editing an entry
		if($this->EE->input->get('M') == 'entry_form') {
			$out = array(
				'content' => $this->EE->lang->line('dm_eeck_upgraded'),
			);
			$this->EE->output->show_message($out);
		}
		
		return $settings;
	}

	// -----------------------------------------------------------------

	private function upgrade_ee_fields() {

		// find all the eeck_finder fieldtypes
		$query = $this->EE->db->select('field_id')->where('field_type','dm_eeck_files')->get('channel_fields');

		if(!$query->num_rows()) {
			return;
		}

		// loop them all
		$ids = array();
		foreach($query->result() as $id) {
			$ids[] = 'field_id_'.$id->field_id;
		}

		$cdata = $this->EE->db->select('entry_id,'.implode(',',$ids))->get('channel_data');
		foreach($cdata->result_array() as $entry ) {

			$update = array();

			// replace pipes for URL args
			foreach($ids as $field) {

				if(!$entry[$field]) {
					continue;
				}
				$current = explode('|',$entry[$field]);
				if(count($current) != 3 ) {
					continue;
				}
				$update[$field] = $current[0].'?b='.$current[1].'&d='.$current[2];
			}

			// run the update for this entry
			if(!empty($update)) {
				$this->EE->db->where('entry_id',$entry['entry_id'])->update('channel_data',$update);
			}
		}
	}

	// -----------------------------------------------------------------

	private function upgrade_matrix_fields() {
		
		// check if matrix is installed and of a good version
		$check = $this->EE->db->where('field_type','matrix')->count_all_results('channel_fields');

		// no. nothing to do
		if(!$check) {
			return;
		}

		// establish Matrix Finder colums
		$query = $this->EE->db->select('col_id')->where('col_type','dm_eeck_files')->get('matrix_cols');

		if(!$query->num_rows()) {
			return;
		}

		// loop them all
		$ids = array();
		foreach($query->result() as $id) {
			$ids[] = 'col_id_'.$id->col_id;
		}

		$cdata = $this->EE->db->select('row_id,'.implode(',',$ids))->get('matrix_data');
		foreach($cdata->result_array() as $entry ) {

			$update = array();

			// replace pipes for URL args
			foreach($ids as $field) {

				if(!$entry[$field]) {
					continue;
				}
				
				// saw a couple of instances of duff data, let's fix them
				if($entry[$field] == '|matrix') {
					$this->EE->db->where('row_id',$entry['row_id'])->update('matrix_data',array($field => ""));
					$entry[$field] = '';
				}
				
				$current = explode('|',$entry[$field]);
				if(count($current) != 4 ) {
					continue;
				}
				$update[$field] = $current[0].'?b='.$current[1].'&d='.$current[2].'&matrix';
			}

			// run the update for this entry
			if(!empty($update)) {
				$this->EE->db->where('row_id',$entry['row_id'])->update('matrix_data',$update);
			}
		}
	}
}