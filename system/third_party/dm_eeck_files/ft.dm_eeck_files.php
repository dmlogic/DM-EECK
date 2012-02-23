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
class Dm_eeck_files_ft extends EE_Fieldtype {

	public $info = array(
		'name'		=> 'EECK Finder',
		'version'	=> '1.1.2'
	);

	public $has_array_data = true;

	private $helper;
	private $eeck_settings;
	private $res_list;
	private $file_lookup = false;
	private $upload_error = '';
	private $image_exts = array('jpg','jpeg','png','gif');

	private $upload_options = array(
		'direct' => 'root folder',
		'date' => 'year/month folder',
		'entry_id' => 'entry_id folder'
	);

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 */
	function Dm_eeck_files_ft()	{

		if(APP_VER < '2.1.5') {
			parent::EE_Fieldtype();
		} else {
			parent::__construct();
		}

		// help!
		require_once(PATH_THIRD.'dm_eeck/includes/eeck_helper.php');
		$this->helper = new eeck_helper();

		if(isset($_GET['D']) && $_GET['D'] == 'cp') {
			$this->file_lookup = true;
		}

		// we'll need the settings from the Editor field type
		$this->eeck_settings = $this->helper->load_editor_settings();
	}

	// -----------------------------------------------------------------

	/**
	 * Install - check we have CK Editor
	 *
	 * @return array
	 */
	public function install() {

		// must have the main daddy installed
		$this->helper->check_install();

		return array();
	}

	// --------------------------------------------------------------------

	/**
	 * Display the field type
	 */
	public function display_field($data,$matrix = 0) {
		
		// need a unique ID for this fieldset
		if(!isset($this->EE->session->cache['eeck']['findercount'])) {
			$this->EE->session->cache['eeck']['findercount'] = 0;
		}
		$this->EE->session->cache['eeck']['findercount'] ++;

		// include required js
		$this->helper->include_editor_js($this->eeck_settings['eeck_ckepath'], $this->eeck_settings['eeck_ckfpath']);
		$this->include_finder_js();

		// sort out vars for view
		$tdata['resource_type'] = $this->settings['eeck_upload_location'];
		$rp = $this->lookup_resource_path($this->settings['eeck_upload_location']);

		if(!$rp ) {
			return '<strong>'.$this->EE->lang->line('dm_eeck_bad_resource_types').'</strong>';
		}

		$tdata['resource_path'] = $rp['url'];

		list($file_url, $file_size, $file_date) = $this->parse_file_string($data);

		// lookup actual file data
		if($file_url && $this->file_lookup) {
			$fullpath = str_replace($rp['url'],$rp['server_path'],$file_url);
			list($file_size,$file_date) = $this->file_info($file_url);
		}

		$tdata['src'] = $this->helper->transparent_gif;
		$tdata['file_url'] = urldecode($file_url);
		$tdata['field_name']  = $this->field_name;
		$tdata['matrix'] = $matrix;
		$tdata['fclass'] = '';
		$tdata['uid'] = 'eeck_fileset_'.$this->EE->session->cache['eeck']['findercount'];
		$tdata['is_image'] = false;
		$tdata['start_folder'] = '';

		// got an existing file
		if ($data != '' )  {

			$tdata['disp'] = '';
			$tdata['file_data'] = $data;

			$bits = explode('.',$file_url);
			$ext = strtolower( array_pop($bits) );

			// got an image file
			if(in_array($ext, $this->image_exts)) {
				$tdata['src'] = $this->image_thumbnail($file_url,$tdata['resource_path'],$tdata['resource_type']);
				$tdata['is_image'] = urldecode($file_url);
			// got a non-image file
			} else {
				$tdata['fclass'] = 'eeck-file eeck-file-'.$ext;
			}

			// set the start folder for CK finder
			$folder = explode('/',$file_url);
			array_pop($folder);
			$folder = str_replace(
					array($rp['url'],$this->settings['eeck_upload_location']),
					array('',''),
					implode('/',$folder)
					);
			$tdata['start_folder'] = ",'".$folder."'";

		// no file selected
		} else {
			$tdata['disp'] = 'style="display:none"';
			$tdata['file_data'] = '';
		}

		if($file_size) {
			$file_size = ($file_size > 1024) ? number_format( ($file_size/1024) ).'Mb' : number_format($file_size).'k';
		}
		$tdata['file_size']  = $file_size;
		$tdata['fdisp'] = ($file_size != '') ? '' : 'style="display:none"';


		if($matrix == 1) {
			return $tdata;
		}
		return $this->EE->load->view('field',$tdata,true);
	}

	// --------------------------------------------------------------------

	/**
	 * Output the data to the front end
	 *
	 * @param string $data
	 * @param array $params
	 * @param mixed $tagdata
	 * @return string
	 */
	public function replace_tag($data, $params = array(), $tagdata = FALSE)	{

		// prepare our source data
		$tagdata = ($tagdata) ? $tagdata : '';

		list($eeck_url, $eeck_size, $eeck_date,$matrix) = $this->parse_file_string($data);

		// matrix fields don't have params, so just send the URL. Similarly for single tags
		if($matrix || $tagdata == '' || $eeck_url == '') {
			return $eeck_url;
		}

		// figure out the filename
		$bits = explode('/',$eeck_url);
		$eeck_filename = urldecode(array_pop($bits));

		// lookup info from the actual file if we decide to
		if( (isset($params['file_lookup']) && $params['file_lookup'] == 'y') ) {
			$path = realpath('.'.implode('/',$bits)).'/'.$eeck_filename;
			list($eeck_size,$eeck_date) = $this->file_info($path);
		}

		// format the file size as required
		if($eeck_size != '') {

			$formats = array('k','m');
			$format = (isset($params['size']) && in_array($params['size'], $formats )) ? $params['size'] : $formats[0];
			$eeck_size = ($format == 'm') ? $eeck_size/1024 : $eeck_size;
			$eeck_size =($format == 'm') ?  number_format($eeck_size,1) : number_format($eeck_size);

		}

		// format the file date as required
		if($eeck_date != '') {
			$eeck_date = strtotime($eeck_date);
			$format = (isset($params['date_format']) ) ? $params['date_format'] : 'd/m/Y';
			$eeck_date = date($format,$eeck_date);
		}

		// file extension
		$ext = explode('.',$eeck_filename);
		$eeck_extension = strtolower(end($ext));

		// perform the replace
		$search = array(LD.'eeck_url'.RD,LD.'eeck_size'.RD,LD.'eeck_date'.RD,LD.'eeck_filename'.RD,LD.'eeck_extension'.RD);
		$replace = array($eeck_url,$eeck_size,$eeck_date,$eeck_filename,$eeck_extension);

		// and return
		return str_replace($search, $replace, $tagdata);
	}

	// -----------------------------------------------------------------

	/**
	 * Field settings
	 *
	 * @param array $data
	 */
	public function display_settings($data,$matrix = false) {

		$this->eeck_settings = $this->helper->load_editor_settings();

		$loc = (isset($data['eeck_upload_location'])) ? $data['eeck_upload_location'] : '';
		$drop = form_dropdown('eeck_upload_location', $this->resource_type_list(), $loc);

		$uoption = (isset($data['eeck_upload_option'])) ? $data['eeck_upload_option'] : 'direct';
		$upload = form_dropdown('eeck_upload_option', $this->upload_options, $uoption);

		// send back matrix data if required
		if($matrix) {
			return array(
				array( $this->EE->lang->line('dm_eeck_resource_type'), $drop ),
				array( $this->EE->lang->line('dm_eeck_upload_option'), $upload )
				);
		}

		// or add CP table row
		$this->EE->table->add_row( '<strong>'.$this->EE->lang->line('dm_eeck_resource_type').'</strong>', $drop );
		$this->EE->table->add_row( '<strong>'.$this->EE->lang->line('dm_eeck_upload_option').'</strong>', $upload );

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
			'eeck_upload_location' => $this->EE->input->post('eeck_upload_location'),
			'eeck_upload_option' => $this->EE->input->post('eeck_upload_option'),
			'field_fmt' => 'none',
			'field_show_fmt' => 'n'
				);
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
		$this->helper->save_session_vars();

		// prep data for JS functions
		$data = array(
			'eeck_ckfpath' => $this->eeck_settings['eeck_ckfpath'],
			'tgif' => $this->helper->transparent_gif,
			'defaulticon' => $this->EE->config->item('theme_folder_url').'cp_global_images/default.png'
		);

		// add js to head
		$this->EE->cp->add_js_script(array('plugin' => 'fancybox'));
		$this->EE->cp->add_to_head(
			$this->EE->javascript->inline( $this->EE->load->view('js',$data,true) ).
			'<script type="text/javascript" src="'.$this->EE->config->item('theme_folder_url').'third_party/dm_eeck/finder.js?v='.$this->info['version'].'"></script>'
		);

		$data = array(
			'theme_path' => $this->eeck_settings['eeck_ckfpath'].'skins/'.$this->eeck_settings['eeck_finderskin'].'/images/icons/32/',
			'ee_theme_path' => $this->EE->config->item('theme_folder_url'),
			'extensions' => $this->helper->fileicons
		);

		// add css to head
		$this->EE->cp->add_to_head(

			'<link type="text/css" rel="stylesheet" href="'.$this->EE->config->item('theme_folder_url').'cp_themes/default/css/fancybox.css" />'.
			'<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').'third_party/dm_eeck/finder.css" />'.
			$this->EE->load->view('css',$data,true)
		);

	}

	// -----------------------------------------------------------------

	/**
	 * Add extra JS for Matrix fields
	 *
	 * @return mixed
	 */
	private function include_matrix_js() {

		if(isset($this->EE->session->cache['eeck']['matrix_js']) ) {
			return;
		}

		$this->EE->session->cache['eeck']['matrix_js'] = true;

		// get all the file types and put them in a validation object
		$validation = '';
		foreach($this->eeck_settings['eeck_resourcetypes'] as $type) {
			$validation .= 'eeckvalidation.'.$type['name'].' = [\'';
			$validation .= implode("','",explode(',',$type['extensions']));
			$validation .= "'];\n";
		}

		// add js to foot
		$this->EE->cp->add_to_foot(
			'<script type="text/javascript" src="'.$this->EE->config->item('theme_folder_url').'third_party/dm_eeck/finder_matrix.js"></script>'.
			$this->EE->javascript->inline( $validation )
		);
	}

 	// -----------------------------------------------------------------

	/**
	 * Convert a CK Image path into a thumbnail path
	 *
	 * @param string $src
	 * @param string $respath
	 * @param string $restype
	 * @return string
	 */
	private function image_thumbnail($src,$respath,$restype) {

		return str_replace($respath.$restype.'/',$respath.'_thumbs/'.$restype.'/',$src);
	}

	// -----------------------------------------------------------------

	/**
	 * Get the names of our resource lists
	 * suitable for drop down
	 *
	 * @return array
	 */
	private function resource_type_list() {

		if($this->res_list != false) {
			return $this->res_list;
		}

		if(is_array($this->eeck_settings['eeck_resourcetypes'])) {
			$this->res_list = array();

			foreach($this->eeck_settings['eeck_resourcetypes'] as $res) {
				$this->res_list[$res['name']] = $res['name'];
			}
		}

		return $this->res_list;
	}

	// -----------------------------------------------------------------

	/**
	 * Lookup a file path to an upload location relating to a CK Resource
	 *
	 * @param string $id
	 * @return string
	 */
	private function lookup_resource_path($id) {

		foreach($this->eeck_settings['eeck_resourcetypes'] as $res) {
			if($res['name'] == $id) {
				$location = $this->helper->upload_location($res['upload_location'], false);
				return $location;
			}
		}

		return '';
	}

	// -----------------------------------------------------------------

	/**
	 * Validate a file submission
	 *
	 * @param string $data
	 * @return mixed
	 */
	public function validate($data) {

		$field = 'upload_field_id_'.$this->settings['field_id'];
		if(isset($_FILES[$field]['tmp_name'])) {

			$valid = $this->validate_file($_FILES[$field]['tmp_name'],
										 $_FILES[$field]['name'],
										 $_FILES[$field]['size'],
										 $this->settings['eeck_upload_location']);
			if($valid == false) {
				return $this->upload_error;
			}

		}
		return TRUE ;

	}

	// -----------------------------------------------------------------

	/**
	 * Validate a matrix cell
	 *
	 * @return mixed
	 */
	public function validate_cell($data) {

		$field = 'upload_field_id_'.$this->settings['field_id'];

		if(isset($_FILES[$field]) && isset($_FILES[$field]['tmp_name'][$this->settings['row_name']][$this->settings['col_name']])) {

			$tmpname = $_FILES[$field]['tmp_name'][$this->settings['row_name']][$this->settings['col_name']];
			$name = $_FILES[$field]['name'][$this->settings['row_name']][$this->settings['col_name']];
			$size = $_FILES[$field]['size'][$this->settings['row_name']][$this->settings['col_name']];

			if(!$this->validate_file($tmpname,$name,$size,$this->settings['eeck_upload_location'])) {
				return $this->upload_error;
			}
		}

		return TRUE;
	}

	// -----------------------------------------------------------------

	/**
	 * Pre-process submitted data for saving
	 *
	 * @param string $data
	 * @return string
	 */
	public function save($data) {
		return $data;
	}

	// -----------------------------------------------------------------

	/**
	 * We do most of our processing after save so we have an entry_id
	 *
	 * @param string $data
	 * @return string
	 */
	public function post_save($data) {

		// now we check if there's a file upload
		$field = 'upload_field_id_'.$this->settings['field_id'];
		if(isset($_FILES[$field]['tmp_name'])) {

			$data = $this->upload_file($_FILES[$field]['tmp_name'],
										 $_FILES[$field]['name'],
										 $_FILES[$field]['size'],
										 $this->settings['eeck_upload_location']);

			$uData = array();
			$uData[$this->settings['field_name']] = $data;

			$this->EE->db->where('entry_id',$this->settings['entry_id']);
			$this->EE->db->update('channel_data',$uData);
		}

	}

	// -----------------------------------------------------------------

	/**
	 * Validates a file for upload
	 *
	 * @param string $tmpfile
	 * @param string $destname
	 * @param string $size
	 * @param string $loc
	 * @return boolean
	 */
	public function validate_file($tmpfile,$destname,$size,$loc) {

		// check we have a file
		if(!file_exists($tmpfile)) {
			$this->upload_error = $this->EE->lang->line('dm_eeck_upload_error1');
			return false;
		}

		// check access control
		$allowed = false;
		if(!isset($_SESSION['eeck']['acl']) || !is_array($_SESSION['eeck']['acl'])) {
			$this->upload_error = $this->EE->lang->line('dm_eeck_upload_error2');
			return false;
		}
		foreach($_SESSION['eeck']['acl'] as $acl) {
			if( ($acl[0] == $this->EE->session->userdata['group_id']) && $loc == $acl[1]) {
				$allowed = true;
				break;
			}
		}
		if(!$allowed) {
			$this->upload_error = $this->EE->lang->line('dm_eeck_upload_error3');
			return false;
		}

		// check filetype and filesize allowances
		if(!isset($_SESSION['eeck']['resourcetypes'][$loc])) {
			$this->upload_error = $this->EE->lang->line('dm_eeck_upload_error4');
			return false;
		}

		$bits = explode('.',$destname);
		$ext = strtolower(end($bits));
		if($_SESSION['eeck']['resourcetypes'][$loc]['maxSize'] > 0) {

			// convert max to bytes and compare
			$max = ($_SESSION['eeck']['resourcetypes'][$loc]['maxSize'] * 1024) * 1024;
			if($size > $max) {
				$this->upload_error = $this->EE->lang->line('dm_eeck_upload_error5');
				return false;
			}

			// check extension
			$allowed = explode(',',$_SESSION['eeck']['resourcetypes'][$loc]['allowedExtensions']);
			if(!in_array($ext, $allowed)) {
				$this->upload_error = $this->EE->lang->line('dm_eeck_upload_error6');
				return false;
			}
		}

		return true;
	}

	// -----------------------------------------------------------------

	/**
	 * Custom uploader based on the bits of the CI library that we want
	 *
	 * @param string $tmpfile
	 * @param string $destname
	 * @param int $size
	 * @param string $loc
	 * @return string
	 */
	private function upload_file($tmpfile,$destname,$size,$loc) {

		$bits = explode('.',$destname);
		$ext = end($bits);

		// sort out destination folder
		$this->EE->load->library('upload');
		$suffix = $this->upload_folder_suffix($_SESSION['eeck']['resourcetypes'][$loc]['directory'].$loc.'/');
		$this->EE->upload->upload_path = $_SESSION['eeck']['resourcetypes'][$loc]['directory'].$loc.'/'.$suffix;
		$this->ensure_folder($this->EE->upload->upload_path);
		if(!$this->EE->upload->validate_upload_path()) {
			$this->upload_error = $this->EE->lang->line('dm_eeck_upload_error7');
			return false;
		}

		// bit of name sanitising
		$destname = $this->EE->upload->clean_file_name($destname);

		// remove whitespace. Don't care if you don't want this, you're getting it!
		$destname = preg_replace("/\s+/", "_", $destname);

		// adjust filename for dupes
		$this->EE->upload->file_ext = ".$ext";
		if(!$destname = $this->EE->upload->set_filename($this->EE->upload->upload_path, $destname)) {
			$this->upload_error = $this->EE->lang->line('dm_eeck_upload_error8');
			return false;
		}

		// now move to final location
		if ( ! @copy($tmpfile, $this->EE->upload->upload_path.$destname)) {
			if ( ! @move_uploaded_file($tmpfile, $this->EE->upload->upload_path.$destname)) {
				 $this->upload_error = $this->EE->lang->line('dm_eeck_upload_error9');
				 return false;
			}
		}

		// next make the thumbnail if we need one
		if(in_array(strtolower($ext), $this->image_exts)) {

			$this->EE->load->library('image_lib');

			$sourceImageAttr = @getimagesize($this->EE->upload->upload_path.$destname);
			$imageWidth = isset($sourceImageAttr[0]) ? $sourceImageAttr[0] : 0;
			$imageHeight = isset($sourceImageAttr[1]) ? $sourceImageAttr[1] : 0;
			$imageBits = isset($sourceImageAttr["bits"]) ? $sourceImageAttr["bits"] : 8;
			$imageChannels = isset($sourceImageAttr["channels"]) ? $sourceImageAttr["channels"] : 3;
			$this->helper->setMemoryForImage($imageWidth, $imageHeight, $imageBits, $imageChannels);

			// make sure the thumb upload folder is present and get correct suffix
			$tsuffix = $this->upload_folder_suffix($_SESSION['eeck']['resourcetypes'][$loc]['directory'].'_thumbs/'.$loc.'/');

			// drive a truck over any existing file
			$fum = $_SESSION['eeck']['resourcetypes'][$loc]['directory'].'_thumbs/'.$loc.'/'.$tsuffix.$destname;
			$fum = str_replace("\\", "/", $fum);
			if(file_exists($fum)) {
				@unlink($fum);
			}

			// now run a basic CI image resize
			$config['source_image'] = $this->EE->upload->upload_path.$destname;
			$config['new_image'] = $fum;
			$config['width'] = $this->eeck_settings['eeck_twidth'];
			$config['height'] = $this->eeck_settings['eeck_theight'];
			$config['quality'] = $this->eeck_settings['eeck_tquality'];
			$this->EE->image_lib->initialize($config);
			$this->EE->image_lib->resize();

		}

		// return the new filedata for saving
		$info = $this->file_info($this->EE->upload->upload_path.$destname);
		return $_SESSION['eeck']['resourcetypes'][$loc]['url'].$loc.'/'.$suffix.$destname.'?b='.$info[0].'&d='.$info[1];

	}

	// -----------------------------------------------------------------

	/**
	 * Make a folder if it's not present
	 *
	 * @param string $folder
	 */
	private function ensure_folder($folder) {

		if(!is_dir($folder)) {
			@mkdir($folder,0777,true);
		}
	}

	// -----------------------------------------------------------------

	/**
	 * Work out the correct subfolder for an upload (if any)
	 * and return it
	 *
	 * @param string $path
	 * @return string
	 */
	private function upload_folder_suffix($path) {

		$this->ensure_folder($path);

		switch($this->settings['eeck_upload_option']) {

			case 'date':
				$suffix = date('Y').'/'.date('m').'/';;
				$this->ensure_folder($path.$suffix);
				return $suffix;

			case 'entry_id':
				$suffix = $this->settings['entry_id'].'/';
				$this->ensure_folder($path.$suffix);
				return $suffix;

			default:
				return '';

		}
	}

	// -----------------------------------------------------------------

	/**
	 * Lookup file info from disk if we don't trust CK Finder
	 *
	 * @param string $path
	 * @return array
	 */
	private function file_info($path) {

		$out = array('', '');

		if(file_exists($path)) {
			$info = stat($path);
			$out[0] = ceil($info['size'] / 1024);
			$out[1] = date('YmdHis',$info['mtime']);
		}

		return $out;
	}


	// -----------------------------------------------------------------

	// Matrix functions down here

	/**
	 * Piggy-back on to display_settings with a slight variation
	 *
	 * @param array $data
	 * @return string
	 */
	public function display_cell_settings($data) {

		return $this->display_settings($data,true);
	}

	// -----------------------------------------------------------------

	/**
	 * Save submission from a Matrix cell. All about the file submission
	 *
	 * @param string $data
	 * @return string
	 */
	public function save_cell( $data ) {

		$field = 'upload_field_id_'.$this->settings['field_id'];
		if(isset($_FILES[$field]['tmp_name'][$this->settings['row_name']][$this->settings['col_name']])) {

			$tmpname = $_FILES[$field]['tmp_name'][$this->settings['row_name']][$this->settings['col_name']];
			$name = $_FILES[$field]['name'][$this->settings['row_name']][$this->settings['col_name']];
			$size = $_FILES[$field]['size'][$this->settings['row_name']][$this->settings['col_name']];

			// nasty I know, but Matrix lacking in validation...
			if(!$this->validate_file($tmpname,$name,$size,$this->settings['eeck_upload_location'])) {
				$this->EE->output->fatal_error( $this->upload_error );
			}

			$data = $this->upload_file($tmpname,$name,$size,$this->settings['eeck_upload_location']);
		}

		// Append a pointer so we know this is matrix when we come to output
		$data .= '&matrix';

		return $data;
	}

	// -----------------------------------------------------------------

	/**
	 * Display a Matrix cell
	 * Mainly uses display_field() with a few adjustments
	 *
	 * @param string $data
	 * @return string
	 */
	public function display_cell($data) {

		// remove the tag that marks this as a matrix field, we'll put it back shortly
		$data = str_replace('&matrix','',$data);

		// we'll need the settings from the Editor field type
		$this->eeck_settings = $this->helper->load_editor_settings();

		// include required js
		$this->helper->include_editor_js($this->eeck_settings['eeck_ckepath'], $this->eeck_settings['eeck_ckfpath']);
		$this->include_finder_js();
		$this->include_matrix_js();

		// much data is same as non-matrix display, so start with that
		$tdata = $this->display_field($data, 1);

		// must be an error message
		if(!is_array($tdata)) {
			return $tdata;
		}

		if($tdata['file_url']) {
			$tdata['file_url'] = end(explode('/',$tdata['file_url']));
		}
		$tdata['field_name']  = $this->cell_name;
		
		$out = $this->EE->load->view('field',$tdata,true);

		return $out;
	}

	// -----------------------------------------------------------------

	/**
	 * Split a file path into it's component bits
	 *
	 * @param string $str
	 * @return array
	 */
	private function parse_file_string($str) {

		$out = array('','','',false);

		if(empty($str)) {
			return $out;
		}

		$info = parse_url( $str );

		$out[0] = $info['path'];

		if(!isset($info['query'])) {
			return $out;
		}

		if(preg_match('/b=([\d]+)/',$info['query'],$bytes)) {
			$out[1] = $bytes[1];
		}

		if(preg_match('/d=([\d]+)/',$info['query'],$bytes)) {
			$out[2] = $bytes[1];
		}

		if(preg_match('/&matrix$/',$info['query'])) {
			$out[3] = true;
		}
		
		return $out;
	}

}