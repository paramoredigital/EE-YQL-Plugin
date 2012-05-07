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
 * cURL Library
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Library
 * @author		Jesse Bunch
 * @link		http://paramore.is/
 */

class Curl_library {
    
	/**
	 * Constructor
	 * @author Jesse Bunch
	*/
	public function __construct() {
		$this->EE =& get_instance();
	}

	/**
	 * Wraps the cURL library for use in the addon classes
	 * @param url The URL to fetch
	 * @return string|bool FALSE if failure
	 * @author Jesse Bunch
	*/
	public function do_curl($url) {

		// Do we have fopen?
		// PHP Docs say this is preferred over cURL
		if (ini_get('allow_url_fopen') === TRUE) {
			$response = file_get_contents($url);
		}

		// Do we have curl?
		elseif (function_exists('curl_init')) {

			// Our cURL options
			$options = array(
				CURLOPT_URL =>  $url, 
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CONNECTTIMEOUT => 10,
			); 
			
			// Initialize cURL
		    $curl = curl_init();
			curl_setopt_array($curl, $options);

			// Get response
			$response = curl_exec($curl);
			$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			// Validate
			if ($response === FALSE || $http_code != '200') {
				return FALSE;
			}

			// Close the request
			curl_close($curl);

		}
		
		// Shucks...
		else {
			$response = FALSE;
		}

		// Return the response
		return $response;

	}
	
}


/* End of file curl_library.php */
/* Location: /system/expressionengine/third_party/yql/libraries/curl_library.php */