<?php if ( ! defined('EXT')) exit('No direct script access allowed');
/**
 * DM EECK v1.1.2 for Expression Engine 2
 *
 * This software is copywright DM Logic Ltd
 * www.dmlogic.com
 *
 * You may use this software on commercial and
 * non commercial websites AT YOUR OWN RISK.
 * No warranty is provided nor liability accepted.
 *
 * Any feedback / feature requests gratefully received.
 *
 */
class eeck_helper {

	public $version = '1.1.2';

	private $acl_groups = false;

	public $transparent_gif = 'cp_global_images/transparent.gif';
	public $fileicons = array(	'ai','avi','bmp','cs','dll',
								'exe','gif','jpg','js','mdb',
								'mp3', 'ogg','pdf','rdp','swf',
								'swt', 'txt','vsd','xml','zip',
								'doc' => array('doc','docx'),
								'fla' => array('fla','flv'),
								'ppt' => array('ppt','pptx'),
								'xls' => array('xls','xlsx')
							);

	// -----------------------------------------------------------------

	function  __construct() {
		$this->EE =& get_instance();

		$this->EE->load->helper('form');

		$this->EE->lang->loadfile('dm_eeck','dm_eeck');

		$this->transparent_gif = $this->EE->config->item('theme_folder_url').$this->transparent_gif;

		if ( ! isset($_SESSION)) @session_start();

	}

	// -----------------------------------------------------------------

	/**
	 * Save the required session vars for CK Finder
	 */
	public function save_session_vars() {

		// once per page is fine
		if(isset($this->EE->session->cache['eeck']['ck_vars_saved']) ) {
			return;
		}

		$this->EE->session->cache['eeck']['ck_vars_saved'] = true;

		$settings = $this->load_editor_settings();

		// basics
		$_SESSION['eeck']['group_id'] = $this->EE->session->userdata['group_id'];
		$_SESSION['eeck']['integration_path'] = PATH_THIRD.'dm_eeck/includes/';
		$_SESSION['CKFinder_UserRole'] = 'group'.$this->EE->session->userdata['group_id'];
		$_SESSION['eeck']['thumb_width'] = $settings['eeck_twidth'];
		$_SESSION['eeck']['thumb_height'] = $settings['eeck_theight'];
		$_SESSION['eeck']['thumb_quality'] = $settings['eeck_tquality'];

		// now get all the resource types & access control
		if(!is_array($settings['eeck_resourcetypes'])) {
			return;
		}
		$rt = array();
		$acl = array();

		foreach($settings['eeck_resourcetypes'] as $res) {

			$path_details = $this->upload_location($res['upload_location']);
			if(!$path_details) {
				continue;
			}

			// Resource types
			$size = ( (int) $res['size'] > 0) ? $res['size'].'M' : 0;
			$rt[$res['name']] = array(
				'name' => $res['name'],
				'url' => $path_details['url'],
				'directory' => $path_details['server_path'],
				'maxSize' => $size,
				'allowedExtensions' => $res['extensions'],
				//'deniedExtensions' => '' // maybe later
			);

			// access control - one for each member group and resource type
			foreach($this->access_control_groups() as $group) {
				if(!in_array($group['group_id'],$path_details['noaccess'])) {
					$acl[] = array($group['group_id'],$res['name']);
				}
			}
		}
		$_SESSION['eeck']['resourcetypes'] = $rt;
		$_SESSION['eeck']['acl'] = $acl;

	}

	// -----------------------------------------------------------------

	/**
	 * Include all the JS for CK Editor & Finder
	 */
	function include_editor_js($ckepath,$ckfpath) {

		if(isset($this->EE->session->cache['eeck']['include_js'])) {
			return;
		}

		$this->EE->session->cache['eeck']['include_js'] = true;

		$this->EE->cp->add_to_head('
			<script type="text/javascript" src="'.$ckfpath.'ckfinder.js"></script>
			<script type="text/javascript" src="'.$ckepath.'ckeditor.js"></script>
		' );

	}

	// -----------------------------------------------------------------

	/**
	 * Return an array of upload locations
	 *
	 * @param string $url
	 * @return mixed
	 */
	public function upload_locations() {

		$this->EE->load->model('tools_model');
		$query = $this->EE->tools_model->get_upload_preferences();

		$upload_dirs = array();

		if ($query->num_rows() ) {
			foreach($query->result_array() as $row) {
				$upload_dirs[$row['id']] = $row['name'];
			}
		}

		return $upload_dirs;
	}

	// -----------------------------------------------------------------

	/**
	 * Get details about a single upload location
	 *
	 * @return array
	 */
	public function upload_location($id,$getaccess = true) {

		// get basic data
		$this->EE->db->select('server_path,url');
		$this->EE->db->where('id',$id);
		$this->EE->db->where('site_id', $this->EE->config->item('site_id'));
		$q = $this->EE->db->get('upload_prefs');

		$out = $q->row_array();

		if($out && $getaccess) {
			$out['noaccess'] = array();

			// now get access restrictions
			$this->EE->db->select('member_group');
			$this->EE->db->where('upload_id',$id);
			$q = $this->EE->db->get('upload_no_access');

			foreach($q->result() as $res) {
				$out['noaccess'][] = $res->member_group;
			}
		}

		return $out;
	}

	// -----------------------------------------------------------------

	/**
	 * Get an array of all member groups
	 *
	 * @param boolean $include_admin
	 * @return array
	 */
	public function access_control_groups($include_admin = true) {

		// get all the groups
		if(!is_array($this->acl_groups)) {

			$sql = 'SELECT group_id,group_title
					FROM exp_member_groups
					WHERE site_id='.$this->EE->config->item('site_id').'
						AND (
							(group_id > 4 AND can_access_files = "y")';
			if($include_admin) {
				$sql .= ' OR group_id = 1';
			}
			$sql .= ')
				ORDER BY group_id ASC';
			$query = $this->EE->db->query($sql);
			foreach($query->result_array() as $group) {
				$this->acl_groups[] = $group;
			}
		}

		return $this->acl_groups;
	}

	// -----------------------------------------------------------------

	/**
	 * Check for presence of main fieldtype
	 */
	public function check_install() {

		// check we have EECK Editor
		$this->EE->db->where('name','dm_eeck');
		$query = $this->EE->db->get('fieldtypes');

		if(!$query->num_rows) {
			$this->EE->output->fatal_error(  $this->EE->lang->line('dm_eeck_requires_eeck') );
		}
	}

	// -----------------------------------------------------------------

	/**
	 * Load up the global settings for CK Editor fieldtype
	 */
	public function load_editor_settings() {

		if(!isset($this->EE->session->cache['eeck']['eeck_settings']) ) {

			$this->EE->db->select('settings');
			$this->EE->db->where('name','dm_eeck');
			$query = $this->EE->db->get('fieldtypes');

			$row = $query->row();
			$data = $row->settings;
			$data = base64_decode($data);
			$data = unserialize($data);

			// check if we need to upgrade
			$data = $this->upgrade($data);

			$settings = (isset($data[$this->EE->config->item('site_id')] ) ) ? $data[$this->EE->config->item('site_id')] : array();

			$this->EE->session->cache['eeck']['eeck_settings'] = $settings;
		}

		return $this->EE->session->cache['eeck']['eeck_settings'];
	}

	// -----------------------------------------------------------------

	/**
	 * From CK Finder
	 *
	 * @param int $imageWidth
	 * @param int $imageHeight
	 * @param int $imageBits
	 * @param int $imageChannels
	 * @return boolean
	 */
	public function setMemoryForImage($imageWidth, $imageHeight, $imageBits, $imageChannels)  {

		$MB = 1048576;  // number of bytes in 1M
        $K64 = 65536;    // number of bytes in 64K
        $TWEAKFACTOR = 2.4;  // Or whatever works for you
        $memoryNeeded = round( ( $imageWidth * $imageHeight
        * $imageBits
        * $imageChannels / 8
        + $K64
        ) * $TWEAKFACTOR
        ) + 3*$MB;

        //ini_get('memory_limit') only works if compiled with "--enable-memory-limit" also
        //Default memory limit is 8MB so well stick with that.
        //To find out what yours is, view your php.ini file.
        $memoryLimit = $this->returnBytes(@ini_get('memory_limit'))/$MB;
        if (!$memoryLimit) {
            $memoryLimit = 8;
        }

        $memoryLimitMB = $memoryLimit * $MB;
        if (function_exists('memory_get_usage')) {
            if (memory_get_usage() + $memoryNeeded > $memoryLimitMB) {
                $newLimit = $memoryLimit + ceil( ( memory_get_usage()
                + $memoryNeeded
                - $memoryLimitMB
                ) / $MB
                );
                if (@ini_set( 'memory_limit', $newLimit . 'M' ) === false) {
                    return false;
                }
            }
        } else {
            if ($memoryNeeded + 3*$MB > $memoryLimitMB) {
                $newLimit = $memoryLimit + ceil(( 3*$MB
                + $memoryNeeded
                - $memoryLimitMB
                ) / $MB
                );
                if (false === @ini_set( 'memory_limit', $newLimit . 'M' )) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * convert shorthand php.ini notation into bytes, much like how the PHP source does it
     * @link http://pl.php.net/manual/en/function.ini-get.php
     *
     * @static
     * @access public
     * @param string $val
     * @return int
     */
    public function returnBytes($val) {
        $val = trim($val);
        if (!$val) {
            return 0;
        }
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

	// -----------------------------------------------------------------

	/**
	 * Perform any upgrade(s) required
	 *
	 * @param array $settings
	 * @return void
	 */
	private function upgrade($settings) {

		// upgrade msm compatible version
		if(!isset($settings['eeck_msm'])) {
			$settings = $this->run_upgrade('ud_msm',$settings);
		}

		// upgrade to 1.1.2
		if(!isset($settings['eeck_version']) || $settings['eeck_version'] < '1.1.2') {
			$settings = $this->run_upgrade('ud_1_1',$settings);
		}

		return $settings;

	}

	// -----------------------------------------------------------------

	/**
	 * Load and run and update file
	 *
	 * @param string $file
	 * @param array $settings
	 */
	private function run_upgrade($file,$settings) {

		$path = PATH_THIRD.'/dm_eeck/includes/updates/'.$file.'.php';

		if(!file_exists($path)) {
			return;
		}

		require_once($path);

		$className = 'Dm_eeck_'.$file;

		$UD = new $className();
		$UD->do_update($settings);
		unset($UD);

	}

}