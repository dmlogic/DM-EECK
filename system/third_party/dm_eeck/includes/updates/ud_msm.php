<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dm_eeck_ud_msm {
	
	public function __construct() {
		$this->EE =& get_instance();
	}
	
	// -----------------------------------------------------------------
	
	public function do_update($data) {
		
		// log that we've done this
		$new_settings = array(
			'eeck_msm' => 'y',
		);

		// get all sites
		$this->EE->db->select('site_id');
		$sites = $this->EE->db->get('sites');

		// for now we'll assume each site has the same settings, user will need to adjust
		foreach($sites->result() as $site) {
			$new_settings[$site->site_id] = $data;
		}

		$this->EE->db->where('name','dm_eeck');
		$this->EE->db->update('fieldtypes',array('settings' =>  base64_encode( serialize($new_settings) ) ) );
		
		return $new_settings;
	}
	
}