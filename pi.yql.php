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
 * YQL Plugin
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Plugin
 * @author		Jesse Bunch
 * @link		http://paramore.is/
 */

$plugin_info = array(
	'pi_name'		=> 'YQL',
	'pi_version'	=> '1.0',
	'pi_author'		=> 'Jesse Bunch',
	'pi_author_url'	=> 'http://paramore.is/',
	'pi_description'=> 'Simple plugin that allows you to query the YQL service from your ExpressionEngine templates.',
	'pi_usage'		=> Yql::usage()
);


class Yql {

	/**
	 * Return data for the constructor
	 * @author Jesse Bunch
	*/
	public $return_data;
    
	/**
	 * Constructor
	 * @author Jesse Bunch
	*/
	public function __construct() {
		$this->EE =& get_instance();
	}

	/**
	 * exp:yql:query
	 * @param sql The query to execute
	 * @param param:key Replaces the @variables in your YQL query
	 * @param cache_timeout Local caching time. Defaults to 0 (no cache)
	 * @author Jesse Bunch
	*/
	public function query() {

		// Fetch params
		$sql = $this->EE->TMPL->fetch_param('sql', FALSE);
		$cache_timeout = $this->EE->TMPL->fetch_param('cache_timeout', 0);
		$params = $this->_fetch_colon_params('param');
		
		// No SQL, no results
		if (empty($sql)) {
			return $this->EE->TMPL->no_results();
		}

		// Construct the cache key
		$cache_key = $sql.serialize($params);
		$cache_key = md5($cache_key);

		// Fetch Cache
		if ($cache_timeout > 0) {
			
			$this->EE->load->library('cache_library');
			$cached_results = $this->EE->cache_library->read_cache($cache_key);
			
			// Find cache?
			if (FALSE !== $cached_results) {
				$cached_results = unserialize($cached_results);
				return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $cached_results);
			}

		}

		// Run the query
		$this->EE->load->library('yql_library');
		$results = $this->EE->yql_library->run_query($sql, $params);

		// exit($this->variable_to_html($results));

		// Set the cache
		if ($cache_timeout > 0) {
			$this->EE->load->library('cache_library');
			$cache_value = serialize($results);
			$this->EE->cache_library->set_cache($cache_key, $cache_value);
		}

		if (empty($results)) {
			return $this->EE->TMPL->no_results();
		}

		// Parse {results path="element.table[2].element2.array[0]"} tags
		if (preg_match_all("/{\s*results\s+path=(.*?)}/", $this->EE->TMPL->tagdata, $matches)) {
			foreach($matches[0] as $index => $match) {
				$this->EE->TMPL->tagdata = str_replace($match, 
					$this->_traverse_array($results, 
						trim($matches[1][$index], '\'"')
					),
					$this->EE->TMPL->tagdata
				);
			}
		}

		// Parse template
		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, array($results));

	}

	function variable_to_html($variable) {
	    if ($variable === true) {
	        return 'true';
	    } else if ($variable === false) {
	        return 'false';
	    } else if ($variable === null) {
	        return 'null';
	    } else if (is_array($variable)) {
	        $html = "<table border=\"1\">\n";
	        $html .= "<thead><tr><td><b>KEY</b></td><td><b>VALUE</b></td></tr></thead>\n";
	        $html .= "<tbody>\n";
	        foreach ($variable as $key => $value) {
	            $value = $this->variable_to_html($value);
	            $html .= "<tr><td>$key</td><td>$value</td></tr>\n";
	        }
	        $html .= "</tbody>\n";
	        $html .= "</table>";
	        return $html;
	    } else {
	        return strval($variable);
	    }
	}

	private function _traverse_array(&$array, $path) {
		
		$next_path = $path;
		$paths = explode('.', $path);
		$next_path = $paths[0];

		// Index or Assoc Key?
		$key_matches;
		$matched_value;
		if ($num_matches = preg_match('/([a-zA-Z\-\_]*)\[["]?([0-9a-zA-Z]+)["]?\]/', $next_path, $key_matches)) {
			if ($key_matches[1] != "") {
				$matched_value = (isset($array[$key_matches[1]][$key_matches[2]]))
					? $array[$key_matches[1]][$key_matches[2]]
					: FALSE;
			} else {
				$matched_value = (isset($array[$key_matches[2]]))
					? $array[$key_matches[2]]
					: FALSE;
			}
		} else {
			$matched_value = (isset($array[$next_path]))
				? $array[$next_path]
				: FALSE;
		}

		// Matched value an array?
		if (is_array($matched_value)) {
			array_shift($paths);
			return $this->_traverse_array($matched_value, implode('.', $paths));
		}

		return $matched_value;	

	}

	/**
	 * Extracts parameters from the tag param array that are
	 * considered to be colon parameters. e.g. attribute:param="value"
	 * @param string $colon_key The "attribute" part
	 * @return array key/value pairs (param = "value")
	 * @author Jesse Bunch
	*/
	private function _fetch_colon_params($colon_key) {

		// Get all params
		$all_params = $this->EE->TMPL->tagparams;

		// Pull out params that start with "custom:"
		$colon_params = array();
		if (is_array($all_params) && count($all_params)) {
			$colon_key_end_index = strlen($colon_key) + 1;
			foreach ($all_params as $key => $val) {
				if (strncmp($key, $colon_key, $colon_key_end_index-1) == 0) {
					$colon_params[substr($key, $colon_key_end_index)] = $val;
				}
			}					
		}

		return $colon_params;

	}
	
	/**
	 * Plugin usage
	 * @author Jesse Bunch
	*/
	public static function usage() {
		ob_start();
?>

 Since you did not provide instructions on the form, make sure to put plugin documentation here.
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

}


/* End of file pi.yql.php */
/* Location: /system/expressionengine/third_party/yql/pi.yql.php */