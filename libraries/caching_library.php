<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Addon Caching Library
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Library
 * @author		Jesse Bunch
 * @link		http://paramore.is/
 */

class Yql {
    
	/**
	 * Constructor
	 * @author Jesse Bunch
	*/
	public function __construct() {
		$this->EE =& get_instance();
	}
	
	/**
	 * Writes the value to the cache file
	 * @param $cache_key The cache file name. Should resemble a filename
	 * @param $cache_value The value to write to cache
	 * @return bool
	 * @author Jesse Bunch
	*/
	public function set_cache($cache_key, $cache_value) {

		$cache_file_path = $this->_get_cache_path($cache_key);
		
		if (!is_writable(dirname($cache_file_path))) {
			return FALSE;
		}

		if (empty($cache_value)) {
			return FALSE;
		}

		$write_result = file_put_contents($cache_file_path, $cache_value);

	}

	/**
	 * Attempts to read data from the cache
	 * @param $cache_key The cache file name. Should resemble a filename
	 * @param $cache_timeout The time in seconds that the cache should be valid
	 * @return string|bool 
	 * @author Jesse Bunch
	*/
	public function read_cache($cache_key, $cache_timeout = 0) {

		$cache_file_path = $this->_get_cache_path($cache_key);

		// Exists?
		if (!file_exists($cache_file_path)) {
			return FALSE;
		}

		// Readable?
		if (!is_readable($cache_file_path)) {
			return FALSE;
		}

		// Expired?
		$modified_time = filemtime($cache_file_path);
		$max_age_time = time() - $cache_timeout;
		if ($modified_time < $max_age_time) {
			return FALSE;
		}

		// All good, read the result
		return file_get_contents($cache_file_path);

	}

	/**
	 * Returns the path to the cache file
	 * @param $filename
	 * @return string
	 * @author Jesse Bunch
	*/
	protected function _get_cache_path($filename) {
		return APPPATH.'cache/'.$filename.'/';
	}
	
}


/* End of file caching_library.php */
/* Location: /system/expressionengine/third_party/yql/libraries/caching_library.php */