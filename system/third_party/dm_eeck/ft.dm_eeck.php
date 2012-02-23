<?php if ( ! defined('EXT')) exit('No direct script access allowed');
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
class Dm_eeck_ft extends EE_Fieldtype {

	public $info = array(
		'name'		=> 'EECK Editor',
		'version'	=> '1.1.2'
	);

	public $defaults = array(
							'eeck_config_settings' => 'default.php',
							'eeck_ckepath' => '/assets/ckeditor/',
							'eeck_ckfpath' => '/assets/ckfinder/',
							'eeck_finderskin' => 'kama',
							'eeck_twidth' => 100,
							'eeck_theight' => 100,
							'eeck_tquality' => 80,
							'eeck_resourcetypes' => array(),
							'eeck_msm' => 'y'
							);
	private $helper;
	private $placeholder = '<p>&nbsp;</p>';
	private $res_list = false;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 */
	function Dm_eeck_ft() {
		
		if(APP_VER < '2.1.5') {
			parent::EE_Fieldtype();
		} else {
			parent::__construct();
		}

		// help!
		require_once(PATH_THIRD.'dm_eeck/includes/eeck_helper.php');
		$this->helper = new eeck_helper();

		$this->defaults['eeck_version'] = $this->helper->version;
		$this->defaults['eeck_resourcetypes'] = $this->default_resource_types();

	}

	// --------------------------------------------------------------------

	/**
	 * Display the field type
	 *
	 * @param string $data
	 * @param string $matrixname
	 * @return string 
	 */
	public function display_field($data,$matrixname = false) {
		
		$this->helper->load_editor_settings();
		$this->settings = array_merge($this->settings,$this->EE->session->cache['eeck']['eeck_settings']);

		// need settings before continuing
		$ok = true;
		foreach(array_keys($this->defaults) as $key) {
			if(!isset($this->settings[$key])) {
				echo "boo $key";
				$ok = false;
			}
		}

		if(!$ok) {
			return '<p><strong>'.$this->EE->lang->line('dm_eeck_bad_settings').'</strong></p>';
		}

		$fieldname = ($matrixname) ? $matrixname : $this->field_name;
		$fieldid = md5($fieldname);

		// include the JS in the header
		$this->helper->include_editor_js($this->settings['eeck_ckepath'], $this->settings['eeck_ckfpath']);

		// get our settings for this field
		$myConf = ($this->settings['config_settings']) ? $this->settings['config_settings'] : $this->settings['eeck_config_settings'];
		$myImages = $this->get_resource_value('eeck_image_upload','Images');
		$myFiles = $this->get_resource_value('eeck_file_upload','Files');
		$myFlash = $this->get_resource_value('eeck_flash_upload','Flash');
		//echo "$myImages,$myFiles,$myFlash";

		// load out config file
		$conf = $this->load_config_file($myConf);
		if($conf != '') $conf .= ',';

		// add on integration for CK finder
		$conf .= $this->integrate_ckfinder($this->settings['eeck_ckfpath'],$myImages,$myFiles,$myFlash);

		// make the editor code
		if(!$matrixname) {
			$str = 'CKEDITOR.replace( "'.$fieldname .'",{'.$conf.'});';
			$this->EE->javascript->output($str);
		}

		// and output
		$content = ($data) ? $data : $this->placeholder; // placeholder content needed to supress JS error in FF
		$textarea = '<textarea cols="80" id="'.$fieldid.'" name="'.$fieldname.'" rows="10">'.$content.'</textarea>';
		
		return ($matrixname) ? array($textarea,$conf) : $textarea;
	}

	// -----------------------------------------------------------------

	/**
	 * Pre-process data for saving
	 *
	 * @param string $data
	 * @return string
	 */
	public function save($data) {

		// remove firebug junk
		$data =  preg_replace('/<div firebugversion="[\d\.]+" id="_firebugConsole" style="display: none;">\s*..<\/div>\s*(<br \/>)?/s', '', $data);
		
		// remove blank placeholder if present
		if(trim($data) == $this->placeholder) {
			return '';
		}
		
		return $data;

	}

	// -----------------------------------------------------------------
	
	/**
	 * Establish the correct resource location string
	 * for CK finder integration
	 *
	 * @param string $field
	 * @param string $default
	 * @return string 
	 */
	private function get_resource_value($field,$default) {

		if(!isset($this->settings[$field])) {
			return false;
		}

		switch($this->settings[$field]) {

			case 'EECK_DEFAULT':
				return $default;
				break;

			default:
				return $this->settings[$field];
				break;
		}

	}

	// -----------------------------------------------------------------

	/**
	 * Save settings for this field
	 *
	 * @param array $data
	 * @return array
	 */
	public function save_settings($data) {

		return array(
			'config_settings' => $this->EE->input->post('config_settings'),
			'eeck_image_upload' => $this->EE->input->post('eeck_image_upload'),
			'eeck_file_upload' => $this->EE->input->post('eeck_file_upload'),
			'eeck_flash_upload' => $this->EE->input->post('eeck_flash_upload'),
			'field_fmt' => 'none',
			'field_show_fmt' => 'n'
				);
	}

	// --------------------------------------------------------------------

	/**
	 * Display & amend settings for this field
	 *
	 * @param array $data
	 * @param boolean $matrix
	 * @return mixed
	 */
	public function display_settings($data,$matrix = false) {

		$conf = (isset($data['config_settings']) && !empty($data['config_settings'])) ? $data['config_settings'] : '';

		// which config file to use
		$confs = $this->config_file_listing(true);
		
		// upload locations
		$res = $this->resource_type_list();

		// make the drop-down arrays
		$images = array();
		if(in_array('Images', $res) && !isset($data['eeck_image_upload']) ) {
			$data['eeck_image_upload'] = 'Images';
		}
		foreach($res as $r) {
			$images[$r] = $r;
		}

		$files = array();
		if(in_array('Files', $res) && !isset($data['eeck_file_upload']) ) {
			$data['eeck_file_upload'] = 'Files';
		}
		foreach($res as $r) {
			$files[$r] = $r;
		}

		$flash = array();
		if(in_array('Flash', $res) && !isset($data['eeck_flash_upload']) ) {
			$data['eeck_flash_upload'] = 'Flash';
		}
		foreach($res as $r) {
			$flash[$r] = $r;
		}

		// make the form elements
		$si = (isset($data['eeck_image_upload']) && !empty($data['eeck_image_upload'])) ? $data['eeck_image_upload'] : '';
		$sf = (isset($data['eeck_file_upload']) && !empty($data['eeck_file_upload'])) ? $data['eeck_file_upload'] : '';
		$sv = (isset($data['eeck_flash_upload']) && !empty($data['eeck_flash_upload'])) ? $data['eeck_flash_upload'] : '';


		// the output for matrix config
		if($matrix) {

			return array(
				array($this->EE->lang->line('dm_eeck_config_settings'),form_dropdown('config_settings', $confs, $conf)),
				array($this->EE->lang->line('dm_eeck_image_resource'),form_dropdown('eeck_image_upload', $images, $si)),
				array($this->EE->lang->line('dm_eeck_file_resource'),form_dropdown('eeck_file_upload', $files, $sf)),
				array($this->EE->lang->line('dm_eeck_flash_resource'),form_dropdown('eeck_flash_upload', $flash, $sv))
			);
			
		// the output for custom field config
		} else {
			$this->EE->table->add_row(
				$this->EE->lang->line('dm_eeck_config_settings'),
				form_dropdown('config_settings', $confs, $conf)
			);

			$this->EE->table->add_row(
				$this->EE->lang->line('dm_eeck_image_resource'),
				form_dropdown('eeck_image_upload', $images, $si)
			);

			$this->EE->table->add_row(
				$this->EE->lang->line('dm_eeck_file_resource'),
				form_dropdown('eeck_file_upload', $files, $sf)
			);

			$this->EE->table->add_row(
				$this->EE->lang->line('dm_eeck_flash_resource'),
				form_dropdown('eeck_flash_upload', $flash, $sv)
			);
		}

	}

	// -----------------------------------------------------------------

	/**
	 * Get the names of our resource lists
	 *
	 * @return array 
	 */
	private function resource_type_list() {

		$this->load_site_settings();

		if($this->res_list != false) {
			return $this->res_list;
		}

		if(is_array($this->settings['eeck_resourcetypes'])) {
			$this->res_list = array();

			foreach($this->settings['eeck_resourcetypes'] as $res) {
				$this->res_list[] = $res['name'];
			}
		}

		return $this->res_list;
	}

	// -----------------------------------------------------------------

	/**
	 * Global settings
	 * 
	 * @return string 
	 */
	public function display_global_settings() {

		$this->load_site_settings();

		$val = array_merge($this->settings, $_POST);

		// values
		$eeck_config_settings = (isset($val['eeck_config_settings']) ) ? $val['eeck_config_settings'] : '';
		$eeck_ckepath = (isset($val['eeck_ckepath'])) ? $val['eeck_ckepath'] : $this->defaults['eeck_ckepath'];
		$eeck_ckfpath = (isset($val['eeck_ckfpath']) ) ? $val['eeck_ckfpath'] : $this->defaults['eeck_ckfpath'];
		$eeck_resourcetypes = (isset($val['eeck_resourcetypes']) ) ? $val['eeck_resourcetypes'] : $this->defaults['eeck_resourcetypes'];
		$eeck_finderskin = (isset($val['eeck_finderskin']) ) ? $val['eeck_finderskin'] : $this->defaults['eeck_finderskin'];
		$eeck_twidth = (isset($val['eeck_twidth']) ) ? $val['eeck_twidth'] : $this->defaults['eeck_twidth'];
		$eeck_theight = (isset($val['eeck_theight']) ) ? $val['eeck_theight'] : $this->defaults['eeck_theight'];
		$eeck_tquality = (isset($val['eeck_tquality']) ) ? $val['eeck_tquality'] : $this->defaults['eeck_tquality'];


		// form items
		$data['eeck_ckfpath'] = form_input('eeck_ckfpath', $eeck_ckfpath, 'id="eeck_ckfpath" style="width:400px"');
		$data['eeck_ckepath'] = form_input('eeck_ckepath', $eeck_ckepath, 'id="eeck_ckepath" style="width:400px"');
		$data['eeck_finderskin'] = form_input('eeck_finderskin', $eeck_finderskin, 'id="eeck_finderskin" style="width:400px"');
		$data['eeck_config_settings'] = form_dropdown('eeck_config_settings', $this->config_file_listing(), $eeck_config_settings, 'id="eeck_config_settings"');
		$data['spare_resources_field'] = $this->EE->load->view('resource_type',array('upload_location' => form_dropdown('eeck_resource_location[]', $this->helper->upload_locations())),true);
		$data['eeck_resourcetypes'] = $this->display_resource_types($eeck_resourcetypes);
		$data['eeck_twidth'] = form_input('eeck_twidth', $eeck_twidth, 'id="eeck_twidth" style="width:50px" maxlength="3"');
		$data['eeck_theight'] = form_input('eeck_theight', $eeck_theight, 'id="eeck_theight" style="width:50px" maxlength="3"');
		$data['eeck_tquality'] = form_input('eeck_tquality', $eeck_tquality, 'id="eeck_tquality" style="width:50px" maxlength="3"');

		$form = $this->EE->load->view('global_settings',$data,true);

		$this->EE->cp->add_to_head(
				'<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').'third_party/dm_eeck/editor.css" />'.
				$this->EE->load->view('css',array('theme_path'=>$this->EE->config->item('theme_folder_url')),true)
		);

		return $form;
	}

	// -----------------------------------------------------------------

	/**
	 * Save the above
	 *
	 * @return array
	 */
	public function save_global_settings() {

		$backatya = $this->settings;
		$site_id = $this->EE->config->item('site_id');

		// basic text fields
		$backatya[$this->EE->config->item('site_id')] = array(
			'eeck_config_settings' => $_POST['eeck_config_settings'],
			'eeck_ckepath' => $_POST['eeck_ckepath'],
			'eeck_ckfpath' => $_POST['eeck_ckfpath'],
			'eeck_finderskin' => $_POST['eeck_finderskin'],
			'eeck_twidth' => (int) $_POST['eeck_twidth'],
			'eeck_theight' => (int) $_POST['eeck_theight'],
			'eeck_tquality' => (int) $_POST['eeck_tquality'],
			'eeck_resourcetypes' => $this->process_submitted_resources($_POST)
		);

		//print_r($backatya);exit;
		return $backatya;
	}

	// -----------------------------------------------------------------

	/**
	 * Turn the mush of submitted fields for Resource Types and 
	 * access control into a usable array
	 * 
	 * @param array $data
	 * @return array 
	 */
	private function process_submitted_resources($data) {

		$out = array();
		$errors = array();
		$used = array();

		$this->EE->load->library('form_validation');

		if(!isset($data['eeck_resource_name']) || !is_array($data['eeck_resource_name']) ) {
			return $out;
		}

		for($i = 0 ; $i < count($data['eeck_resource_name']) ; $i++) {
			
			if( empty($data['eeck_resource_name'][$i]) ) {
				continue;
			}

			// names must be unique
			if(in_array($data['eeck_resource_name'][$i], $used)) {
				$errors[] = '- '.$this->EE->lang->line('dm_eeck_val_resource_name')." '".$data['eeck_resource_name'][$i]."' ".$this->EE->lang->line('dm_eeck_val_already');

			// names must be alphanumeric
			} elseif(!$this->EE->form_validation->alpha_dash($data['eeck_resource_name'][$i])) {
				$errors[] = '- '.$this->EE->lang->line('dm_eeck_val_alpha');
			}

			$used[] = $data['eeck_resource_name'][$i];
			
			$out[] = array(
				'name' => $data['eeck_resource_name'][$i],
				'upload_location' => $data['eeck_resource_location'][$i],
				'size' => $data['eeck_resource_size'][$i],
				'extensions' => $data['eeck_resource_extensions'][$i],
				);
		}

		// cheap and dirty validation message
		if(count($errors)) {
			$str = $this->EE->lang->line('dm_eeck_val_correct_errors').':<br />';
			$str .= implode('<br />',$errors);
			$this->EE->output->fatal_error($str);
		}

		return $out;
	}

	// -----------------------------------------------------------------

	/**
	 * Make sure we've got some default settings
	 *
	 * @return array
	 */
	public function install() {
		return $this->defaults;
	}
	
	// -----------------------------------------------------------------

	/**
	 * Generate code to integrate CK Finder with CK Editor
	 *
	 * @return string
	 */
	private function integrate_ckfinder($path,$images = 'Images', $files = '', $flash = 'Flash') {

		$str = '';
		if($images != false) {
			$i1 = ($images != '') ? '?Type='.$images : '';
			$i2 = ($images != '') ? '&type='.$images : '';

			$str .= 'filebrowserImageBrowseUrl: "'.$path.'ckfinder.html'.$i1.'",
					filebrowserImageUploadUrl: "'.$path.'core/connector/php/connector.php?command=QuickUpload'.$i2.'"';
		}

		if($files != false) {
			$f1 = ($files != '') ? '?Type='.$files : '';
			$f2 = ($files != '') ? '&type='.$files : '';

			if($str != '') $str .= ',';
			$str .= 'filebrowserBrowseUrl: "'.$path.'ckfinder.html'.$f1.'",
					filebrowserUploadUrl: "'.$path.'core/connector/php/connector.php?command=QuickUpload'.$f2.'"';
		}

		if($flash != false) {

			$v1 = ($flash != '') ? '?Type='.$flash : '';
			$v2 = ($flash != '') ? '&type='.$flash : '';

			if($str != '') $str .= ',';
			$str .= 'filebrowserFlashBrowseUrl: "'.$path.'ckfinder.html'.$v1.'",
					filebrowserFlashUploadUrl: "'.$path.'core/connector/php/connector.php?command=QuickUpload'.$v2.'"';
		}

		// make sure the session vars are all set
		$this->helper->save_session_vars($this->settings);

		return $str;
	}

	// -----------------------------------------------------------------

	/**
	 * Get an array of all config files with their descriptions
	 *
	 * @return array
	 */
	private function config_file_listing($includenull = false) {

		$this->EE->load->helper('directory');
		$map = $map = directory_map(PATH_THIRD.'dm_eeck/config/',1);

		$out = array();

		if($includenull) {
			$out[] = $this->EE->lang->line('dm_eeck_global_default');
		}

		$name = '';

		foreach($map as $file) {
			unset($name);
			include(PATH_THIRD.'dm_eeck/config/'.$file);
			if(!isset($name)) {
				continue;
			}
			$out[$file] = $name;
		}

		return $out;
	}

	// -----------------------------------------------------------------

	/**
	 * Load CK Editor config settings
	 *
	 * @param string $file
	 * @return array
	 */
	private function load_config_file($file) {

		$file = PATH_THIRD.'dm_eeck/config/'.$file;

		if(file_exists($file)) {
			require($file);
			if(isset($editor_config)) {
				return $editor_config;
			}
		}

		return '';
	}

	// -----------------------------------------------------------------

	/**
	 * The default Resource types for CK Finder
	 */
	private function default_resource_types() {

		$config = Array();

		$config[] = Array(
				'name' => 'Images',
				'upload_location' => '1',
				'size' => "16",
				'extensions' => 'bmp,gif,jpeg,jpg,png',
				);

		$config[] = Array(
				'name' => 'Files',
				'upload_location' => '1',
				'size' => 0,
				'extensions' => '7z,aiff,asf,avi,bmp,csv,doc,docx,fla,flv,gif,gz,gzip,jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf,png,ppt,pptx,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,xlsx,zip',
				);

		$config[] = Array(
				'name' => 'Flash',
				'upload_location' => '0',
				'size' => 0,
				'extensions' => 'swf,flv',
				);

		return $config;

	}

	// -----------------------------------------------------------------

	/**
	 * Generate the form of available resource types
	 * 
	 * @param array $resources
	 * @param array $submitted
	 * @return string 
	 */
	private function display_resource_types($resources,$submitted=array() ) {

		// turn post data into config array
		if(isset($submitted['eeck_resource_name']) && is_array($submitted['eeck_resource_name'])) {
			$resources = $this->process_submitted_resources($submitted);
		}

		// now create the form
		$out = '';
		foreach($resources as  $resource) {

			$resource['upload_location'] = form_dropdown('eeck_resource_location[]', $this->helper->upload_locations(),$resource['upload_location']);

			$out .= $this->EE->load->view('resource_type',$resource,true);
		}

		return $out;

	}

	// -----------------------------------------------------------------

	// Matrix functions down here

	/**
	 * Piggy-back onto the existing settings function,
	 * it's almost the same
	 * 
	 * @param array $data
	 * @return array 
	 */
	public function display_cell_settings($data) {

		return $this->display_settings($data,true);
	}

	// -----------------------------------------------------------------

	/**
	 * Display just the textarea for Matrix.
	 *
	 * Can't apply CK Editor at this stage as the ID may be unknown
	 * So we'll stash the config values in the HTML and use Matrix
	 * events to turn on the editor as required. See the JS include
	 * for the code that does that
	 * 
	 * @param string $data
	 * @return string 
	 */
	public function display_cell($data) {

		// get the field markup and Editor config from the normal display function
		list($textarea, $ckconfig)  =  $this->display_field($data,$this->cell_name);
		
		// include the matrix functions if not already done so
		if(!isset($this->EE->session->cache['eeck']['mconfigs']) ) {
			$this->include_matrix_js();
			$this->EE->session->cache['eeck']['mconfigs'] = array();
		}

		// stash the editor init config code for this matrix column id
		if (!isset($this->EE->session->cache['eeck']['mconfigs'][$this->col_id])) {

			$this->EE->cp->add_to_foot(
				$this->EE->javascript->inline( 'eeckconf.col_id_'.$this->col_id.'={'.$ckconfig.'};')
			);

			$this->EE->session->cache['eeck']['mconfigs'][$this->col_id] = true;
		}
		return $textarea;
	}

	// -----------------------------------------------------------------

	/**
	 * Include additional js to cope with matrix display and sorting
	 */
	private function include_matrix_js() {

		$this->EE->cp->add_to_foot(
				'<script type="text/javascript" src="'.$this->EE->config->item('theme_folder_url').'third_party/dm_eeck/editor_matrix.js"></script>'
		);
	}

	// -----------------------------------------------------------------

	/**
	 * Abstract this out 'cos we don't need it on the front end
	 */
	private function load_site_settings() {
		
		$this->helper->load_editor_settings();
		$this->settings = array_merge($this->settings,$this->EE->session->cache['eeck']['eeck_settings']);
	}
}