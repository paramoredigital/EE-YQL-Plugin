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

class Caching_library {
    
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
	 * @param $cache_group The folder to place the cache file in. Usually your addon short name.
	 * @return bool
	 * @author Jesse Bunch
	*/
	public function set_cache($cache_key, $cache_value, $cache_group) {

		$cache_file_path = $this->_get_cache_path($cache_key, $cache_group);

		if (!file_exists(dirname($cache_file_path))) {
			@mkdir(dirname($cache_file_path), 0777, TRUE);
		}
		
		if (!is_writable(dirname($cache_file_path))) {
			$this->EE->logger->developer('YQL plugin: Couldn\'t set the cache file because the cache directory is not writeable.', TRUE);
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
	 * @param $cache_group The folder to place the cache file in. Usually your addon short name.
	 * @param $cache_timeout The time in seconds that the cache should be valid
	 * @return string|bool 
	 * @author Jesse Bunch
	*/
	public function read_cache($cache_key, $cache_group, $cache_timeout = 0) {

		$cache_file_path = $this->_get_cache_path($cache_key, $cache_group);

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
	 * @param $cache_group The folder to place the cache file in. Usually your addon short name.
	 * @return string
	 * @author Jesse Bunch
	*/
	protected function _get_cache_path($filename, $cache_group) {
		return APPPATH.'cache/'.$cache_group.'/'.$filename.'';
	}
	
}


/* End of file caching_library.php */
/* Location: /system/expressionengine/third_party/yql/libraries/caching_library.php */