<?php namespace HashOver;

// Copyright (C) 2010-2019 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


class Misc
{
	// Allowed JavaScript constructors
	protected static $objects = array (
		'HashOver',
		'HashOverCountLink',
		'HashOverLatest'
	);

	// XSS-unsafe characters to search for
	protected static $searchXSS = array (
		'&',
		'<',
		'>',
		'"',
		"'",
		'/',
		'\\'
	);

	// XSS-safe replacement character entities
	protected static $replaceXSS = array (
		'&amp;',
		'&lt;',
		'&gt;',
		'&quot;',
		'&#x27;',
		'&#x2F;',
		'&#92;'
	);

	// Return JSON or JSONP function call
	public static function jsonData ($data, $self_error = false)
	{
		// Encode JSON data
		$json = json_encode ($data);

		// Return JSON as-is if the request isn't for JSONP
		if (!isset ($_GET['jsonp']) or !isset ($_GET['jsonp_object'])) {
			return $json;
		}

		// Otherwise, make JSONP callback index XSS safe
		$index = self::makeXSSsafe ($_GET['jsonp']);

		// Make JSONP object constructor name XSS safe
		$object = self::makeXSSsafe ($_GET['jsonp_object']);

		// Check if constructor is allowed, if not use default
		$allowed_object = in_array ($object, self::$objects, true);
		$object = $allowed_object ? $object : 'HashOver';

		// Check if the JSONP index contains a numeric value
		if (is_numeric ($index) or $self_error === true) {
			// If so, cast index to positive integer
			$index = ($self_error === true) ? 0 : (int)(abs ($index));

			// Construct JSONP function call
			$jsonp = sprintf ('%s.jsonp[%d] (%s);', $object, $index, $json);

			// And return the JSONP script
			return $jsonp;
		}

		// Otherwise, return an error
		return self::jsonData (array (
			'message' => 'JSONP index must have a numeric value.',
			'type' => 'error'
		), true);
	}

	// Makes a string XSS-safe by removing harmful characters
	public static function makeXSSsafe ($string)
	{
		return str_replace (self::$searchXSS, self::$replaceXSS, $string);
	}

	// Returns error in HTML paragraph
	public static function displayError ($error = 'Something went wrong!', $mode = 'php')
	{
		// Initial error message data
		$data = array ();

		// Make error message XSS safe
		$xss_safe = self::makeXSSsafe ($error);

		// Treat JSONP as JavaScript
		if ($mode === 'json' and isset ($_GET['jsonp'])) {
			$mode = 'javascript';
		}

		// Decide how to display error
		switch ($mode) {
			// Minimal JavaScript to display error message on page
			case 'javascript': {
				$data[] = 'var hashover = document.getElementById (\'hashover\') || document.body;';
				$data[] = 'var error = \'<p><b>HashOver</b>: ' . $xss_safe . '</p>\';' . PHP_EOL;
				$data[] = 'hashover.innerHTML += error;';

				break;
			}

			// RSS XML to indicate error
			case 'rss': {
				$data[] = '<?xml version="1.0" encoding="UTF-8"?>';
				$data[] = '<error>HashOver: ' . $xss_safe . '</error>';

				break;
			}

			// JSON to indicate error
			case 'json': {
				$data[] = self::jsonData (array (
					'message' => $error,
					'type' => 'error'
				));

				break;
			}

			// Default just return the error message
			default: {
				$data[] = '<p><b>HashOver</b>: ' . $error . '</p>';
				break;
			}
		}

		// Convert error message data to string
		$message = implode (PHP_EOL, $data);

		return $message;
	}

	// Returns error in HTML paragraph
	public static function displayException (\Exception $error, $mode = 'php')
	{
		return self::displayError ($error->getMessage (), $mode);
	}

	// Returns an array item or a given default value
	public static function getArrayItem (array $data, $key)
	{
		return !empty ($data[$key]) ? $data[$key] : false;
	}
	
	// Return a well styled HTML display of the content of an array
	public static function var_export($arr, $title='', $b_htmlspecialchars=1, $is_object = ''){

			$html = !empty($title) ? '<h3>'.$title.'</h3>' : '';
			$html .= "\n<div class='_var_export' style='margin-left:100px;font-family:monospace;'>";

			if (is_object($arr)){
				$is_object = true;
				$arr = get_object_vars($arr);
			}else if ($is_object==''){
				$is_object = false;
			}

		if (is_array($arr)){
				if (count($arr)==0){
					$html .= "&nbsp;";
				}else{
					$ii=0;
					foreach ($arr as $k=>$ele){
						$html .= "\n\t<div style='float:left;'><b style='".($is_object ? 'background-color:rgba(0,0,0,0.1);padding:2px':'')."'>$k <span class='arrow' style='color:#822;'>&rarr;</span> </b></div>"
								."\n\t<div style='border:1px #ddd solid;font-size:10px;font-family:sans-serif;'>";
						$html .= is_object($ele) ? self::var_export(get_object_vars($ele),'',$b_htmlspecialchars,true) : self::var_export($ele,'',$b_htmlspecialchars,false);
						$html .= "</div>";
						$html .= "\n\t<div style='float:none;clear:both;'></div>";
						$ii++;
					}
				}
		}else if ($arr===NULL){
				$html .= "&nbsp;";
		}else if ($arr === 'b:0;' || substr($arr,0,2)=='a:'){
				$uns = f_unserialize($arr);
				if (is_array($uns))
					$html .= htmlspecialchars($arr).'<br /><br />'.self::var_export($uns).'<br />';
				else
					$html .= $b_htmlspecialchars==1 ? htmlspecialchars($arr) : $arr;
			}else{
				$html .= $b_htmlspecialchars==1 ? htmlspecialchars($arr) : $arr;
			}
		$html .= "</div>";
		return $html;
	}

	static public function xml2array($contents, $get_attributes=1) {
		if(!$contents) return array();

		if(!function_exists('xml_parser_create')) return array();

		//Get the XML parser of PHP - PHP must have this module for the parser to work
		$parser = xml_parser_create();
		xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_parse_into_struct( $parser, $contents, $xml_values );
		xml_parser_free( $parser );

		if(!$xml_values) return;//Hmm...

		//Initializations
		$xml_array = array();
		$parents = array();
		$opened_tags = array();
		$arr = array();

		$current = &$xml_array;

		//Go through the tags.
		foreach($xml_values as $data) {
			unset($attributes,$value);//Remove existing values, or there will be trouble

			//This command will extract these variables into the foreach scope
			// tag(string), type(string), level(int), attributes(array).
			extract($data);//We could use the array by itself, but this cooler.

			$result = '';
			if($get_attributes) {//The second argument of the function decides this.
				$result = array();
				if(isset($value)) $result['value'] = $value;

				//Set the attributes too.
				if(isset($attributes)) {
					foreach($attributes as $attr => $val) {
						if($get_attributes == 1) $result['__attr__'][$attr] = $val; //Set all the attributes in a array called 'attr'
						/**  :TODO: should we change the key name to '_attr'? Someone may use the tagname 'attr'. Same goes for 'value' too */
					}
				}
			} elseif(isset($value)) {
				$result = $value;
			}

			//See tag status and do the needed.
			if($type == "open") {//The starting of the tag '<tag>'
				$parent[$level-1] = &$current;

				if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
					$current[$tag] = $result;
					$current = &$current[$tag];

				} else { //There was another element with the same tag name
					if(isset($current[$tag][0])) {
						array_push($current[$tag], $result);
					} else {
						$current[$tag] = array($current[$tag],$result);
					}
					$last = count($current[$tag]) - 1;
					$current = &$current[$tag][$last];
				}

			} elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
				//See if the key is already taken.
				if(!isset($current[$tag])) { //New Key
					$current[$tag] = $result;

				} else { //If taken, put all things inside a list(array)
					if((is_array($current[$tag]) and $get_attributes == 0)//If it is already an array...
							or (isset($current[$tag][0]) and is_array($current[$tag][0]) and $get_attributes == 1)) {
						array_push($current[$tag],$result); // ...push the new element into that array.
					} else { //If it is not an array...
						$current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
					}
				}

			} elseif($type == 'close') { //End of tag '</tag>'
				$current = &$parent[$level-1];
			}
		}

		return($xml_array);
	}
	
	static public function add_imported_comments($setup,$threads,$comments){

		$a_added = array();
		$a_msg   = array();
		$db = new ParseSQL ($setup);
		
		// Get existing threads
		$result = $db->SQL('SELECT thread FROM `page-info`',null);
		if ($result !== false) {
			// Fetch all comments
			$res_arr = $result->fetchAll (\PDO::FETCH_ASSOC);
			$db_threads = array();
			if (is_array($res_arr)){
				foreach ($res_arr as $arr) $db_threads[$arr['thread']] = '1';
			}
		}else{
			$db_threads = array();
		}

		// Get existing comments
		//$result = $db->SQL('SELECT * FROM `comments`',null);
		$result = $db->SQL('SELECT thread,comment,date FROM `comments`',null);
		if ($result !== false) {
			// Fetch all comments
			$res_arr = $result->fetchAll (\PDO::FETCH_ASSOC);
			$db_comments = array();
			// group the comments by thread and datetime. Also we calculate the maximum comment-position
			if (is_array($res_arr)){
				foreach ($res_arr as $arr) {
					// convert date format
					$date = new \DateTime($arr['date']);
					$iso_date = $date->format(DATE_ISO8601);
					// add to array, under its thread
					if (!isset($db_comments[$arr['thread']])){
						$db_comments[$arr['thread']] = array('max'=>0);
					}
					$pos = intval($arr['comment']);
					if ($pos > $db_comments[$arr['thread']]['max']) $db_comments[$arr['thread']]['max'] = $pos;
					$db_comments[$arr['thread']][$iso_date] = $arr;
				}
			}
		}else{
			$db_comments = array();
		}

		// check all the comments to be inserted to check if they already exist 
		// and prepare 2 arrays to insert to dabase: new_threads and new comments
		$new_threads = array();
		$new_comments = array();
		if (count($comments)>0){
			$i_positions = array();
			foreach ($comments as $idc=>$arr){
				// convert date format
				$date = new \DateTime($arr['date']);
				$iso_date = $date->format(DATE_ISO8601);
				// thread
				if (!isset($threads[$arr['thread_id']])) continue;
				$url = urldecode($threads[$arr['thread_id']]['url']);
				$url = parse_url($url, PHP_URL_PATH).'-'.parse_url($url, PHP_URL_QUERY);
				$threadName = $setup->getSafeThreadName($url);
				// does exist the thread on db ?
				if (!isset($db_threads[$threadName]) && !isset($new_threads[$threadName])){
					$new_threads[$threadName] = array(
						'domain'=>'all',
						'thread'=>$threadName,
						'url'=>urldecode($threads[$arr['thread_id']]['url']),
						'title'=>$threads[$arr['thread_id']]['title']
					);
				}
				// does exist the comment on db ?
				if (!isset($db_comments[$threadName]) 
					 || !isset($db_comments[$threadName][$iso_date])){
						 
					 // calculate position
					 if (empty($arr['parent_id'])){
						 // this is a root comment
						 if (!isset($db_comments[$threadName])){
							$db_comments[$threadName] = array('max'=>1);
						 }else{
							$db_comments[$threadName]['max'] = $db_comments[$threadName]['max'] + 1;
						 }
						 $i_positions[$idc] = array('pos'=>$db_comments[$threadName]['max'],'childs'=>0);
					 }else if (isset($i_positions[$arr['parent_id']])){
						 // this is a response to a previous comment
						 $i_positions[$arr['parent_id']]['childs'] = $i_positions[$arr['parent_id']]['childs'] + 1;
						 $pos = $i_positions[$arr['parent_id']]['pos'].'-'.$i_positions[$arr['parent_id']]['childs'];
						 $i_positions[$idc] = array('pos'=>$pos,'childs'=>0);
					 }else{
						 continue;
					 }
					 
					 $new_comments[] = array(
						'domain'=>'all',
						'thread'=>$threadName,
						'comment'=>$i_positions[$idc]['pos'], // 1-4
						'body'=>$arr['body'],
						'date'=>$iso_date,
						'name'=>$arr['name'],
					 );
						 
				}
			}
		}
		/* for debug
		echo Misc::var_export($db_threads,'$db_threads');
		echo Misc::var_export($db_comments,'$db_comments');
		echo Misc::var_export($threads,'$threads');
		echo Misc::var_export($comments,'$comments');
		*/
		
		// Insert new threads at database
		$n_added = array('threads'=>0,'comments'=>0);
		if (count($new_threads)>0){
			$sql = 'INSERT INTO `page-info` '
					.'(`domain`,`thread`,`url`,`title`) VALUES '
					.'(:domain,:thread,:url,:title)';
			foreach ($new_threads as $arr){
				$result = $db->SQL($sql,$arr);
				if ($result === false) 
					$a_msg[] = "<p><b>SQL error inserting thread:</b> ".var_export($result)."</p>";
				else
					$n_added['threads'] = $n_added['threads'] + 1;
			}
		}
		
		// Insert new comments at database
		if (count($new_comments)>0){
			$sql = 'INSERT INTO `comments` '
					.'(`domain`,`thread`,`comment`,`body`,`date`,`name`) VALUES '
					.'(:domain,:thread,:comment,:body,:date,:name)';
			foreach ($new_comments as $arr){
				$result = $db->SQL($sql,$arr);
				if ($result === false) 
					$a_msg[] = "<p><b>SQL error inserting comment:</b> ".var_export($result)."</p>";
				else
					$n_added['comments'] = $n_added['comments'] + 1;
			}
		}
		
		/*
		echo Misc::var_export($new_threads,'$new_threads - ADDED '.$n_added['threads']);
		echo Misc::var_export($new_comments,'$new_comments - ADDED '.$n_added['comments']);
		echo Misc::var_export($a_msg,'Errors: '.count($a_msg));
		*/
		
		return array($n_added,$a_msg);

	}
	
}
