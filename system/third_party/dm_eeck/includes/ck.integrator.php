<?php
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

/*
 * This does the heavy lifting to generate the necessary bits
 * for the CK Finder config
 */
class Ck_integrator {

	public function  __construct() {

		if ( ! isset($_SESSION)) @session_start();
	}

	// -----------------------------------------------------------------

	/**
	 * Checks for a valid eeck session.
	 * If found, then we must be logged in to CMS
	 *
	 * @return boolean 
	 */
	public static function authenticate() {

		// got an integer?  Good enough.
		if(isset($_SESSION['eeck']['group_id']) && (int) $_SESSION['eeck']['group_id']  ) {

			return true;
		}

		// must be bad
		return false;
	}

	// -----------------------------------------------------------------

	/**
	 * Return the base path for this particular resource type
	 * 
	 * @return string 
	 */
	public static function base_url() {

		if(isset($_GET['type']) && isset($_SESSION['eeck']['resourcetypes'][$_GET['type']])) {
			return $_SESSION['eeck']['resourcetypes'][$_GET['type']]['url'];
		}

		// shouldn't ever end up here as authentication would have booted us out before
		return '/';
	}

	// -----------------------------------------------------------------

	/**
	 * Return the base path for this particular resource type
	 *
	 * @return string
	 */
	public static function base_dir() {

		if(isset($_GET['type']) && isset($_SESSION['eeck']['resourcetypes'][$_GET['type']])) {
			return $_SESSION['eeck']['resourcetypes'][$_GET['type']]['directory'];
		}

		// shouldn't ever end up here as authentication would have booted us out before
		return '/';
	}
	
	// -----------------------------------------------------------------

	/**
	 * Return all info about Resource Types
	 *
	 * @return array 
	 */
	public static function resource_types() {

		$out = array();

		if(isset($_SESSION['eeck']['resourcetypes'])) {

			foreach($_SESSION['eeck']['resourcetypes'] as $res) {

				// append the resource name to the upload destination so we can hide thumbnails in the root
				$res['url'] = $res['url'].$res['name'].'/';
				$res['directory'] = $res['directory'].$res['name'].'/';
				
				$out[] = $res;
			}
		}

		return $out;

	}

	// -----------------------------------------------------------------

	/**
	 * Transforms the upload location session into
	 * a CK formatted Access Control list
	 * 
	 * @return array
	 */
	public static function access_control() {

		$acs = array();

		// start by denying all
		$acs[] = Array(
			'role' => '*',
			'resourceType' => '*',
			'folder' => '/',

			'folderView' => false,
			'folderCreate' => false,
			'folderRename' => false,
			'folderDelete' => false,

			'fileView' => false,
			'fileUpload' => false,
			'fileRename' => false,
			'fileDelete' => false);

		// now loop each one we've got saved and give full perms
		if(isset($_SESSION['eeck']['acl']) && is_array($_SESSION['eeck']['acl'])) {

			foreach($_SESSION['eeck']['acl'] as $a) {
				$acs[] = Array(
					'role' => 'group'.$a[0],
					'resourceType' => $a[1],
					'folder' => '/',

					'folderView' => true,
					'folderCreate' => true,
					'folderRename' => true,
					'folderDelete' => true,

					'fileView' => true,
					'fileUpload' => true,
					'fileRename' => true,
					'fileDelete' => true);
			}
		}

		return $acs;
	}

	// -----------------------------------------------------------------

	/**
	 * Return a value for thumbail config
	 *
	 * @param string $type
	 * @return int 
	 */
	public static function thumbs($type) {

		// a suitable default
		$ret = 100;

		switch($type) {
			
			case 'width':
				$ret = $_SESSION['eeck']['thumb_width'];
				break;

			case 'height':
				$ret = $_SESSION['eeck']['thumb_height'];
				break;

			case 'quality':
				$ret = $_SESSION['eeck']['thumb_quality'];
				break;
		}

		return $ret;

	}

	// -----------------------------------------------------------------

	/**
	 * Dump some data to a file so we can see what's going on
	 * 
	 * @param array $data
	 * @param string $path 
	 */
	public static function debug($data,$path) {

		$fp = @fopen($path,'w+');
		@fwrite($fp, print_r($data,true));
		@fclose($fp);

	}

}
// END