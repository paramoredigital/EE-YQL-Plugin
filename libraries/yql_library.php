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
 * YQL Query Library
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Library
 * @author		Jesse Bunch
 * @link		http://paramore.is/
 */

class Yql_library {

	const YQL_SERVICE_URL = 'http://query.yahooapis.com/v1/public/yql';
    
	/**
	 * Constructor
	 * @author Jesse Bunch
	*/
	public function __construct() {
		$this->EE =& get_instance();
	}

	/**
	 * Fires off a query to the YQL service
	 * @param $sql string
	 * @param $params array Key/Value pairs to replace in the query
	 * @return array|FALSE
	 * @author Jesse Bunch
	*/
	public function run_query($sql, $params = array()) {
		
		// Add the rest of the params
		$params['q'] = $sql;
		$params['format'] = 'json';
		$params['callback'] = '';
		// $params['debug'] = 'true';
		// $params['diagnostics'] = 'true';

		// Build the query string
		$parsed_params = array();
		foreach($params as $key => $value) {
			$parsed_params[] = urlencode($key).'='.urlencode($value);
		}
		$param_string = implode('&', $parsed_params);

		// Build the final URL
		// $param_string = http_build_query($params);
		$query_url = Yql_library::YQL_SERVICE_URL.'?'.$param_string;

		// Fire off the request
		$this->EE->load->library('curl_library');
		$curl_result = $this->EE->curl_library->do_curl($query_url);
		
		// Unserialize the result
		$result_array = json_decode($curl_result, TRUE);

		// var_dump($result_array);
		// exit;

		// Return the result, good or bad
		return (!is_null($result_array['query']['results']))
			? $result_array['query']['results']
			: FALSE;

	}
	
}


/* End of file yql_library.php */
/* Location: /system/expressionengine/third_party/yql/libraries/yql_library.php */