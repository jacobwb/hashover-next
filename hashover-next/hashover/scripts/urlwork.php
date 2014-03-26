<?php

	// Copyright (C) 2014 Jacob Barkdull
	//
	//	This program is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	This program is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with this program.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	// Set Canonical URL
	if (!isset($canon_url) and isset($script_query)) {
		if (isset($_POST['canon_url']) and !empty($_POST['canon_url'])) {
			$canon_url = (preg_match('/([http|https]):\/\//i', $_POST['canon_url'])) ? $_POST['canon_url'] : 'http://' . $_POST['canon_url'];
		} else if (isset($_GET['canon_url']) and !empty($_GET['canon_url'])) {
			$canon_url = (preg_match('/([http|https]):\/\//i', $_GET['canon_url'])) ? $_GET['canon_url'] : 'http://' . $_GET['canon_url'];
		}
	}

	// Get full page URL or Canonical URL
	if ($mode == 'javascript') {
		if (isset($_SERVER['HTTP_REFERER']) and !isset($_GET['rss'])) {
			// Check if the script was requested by this server
			if (!preg_match('/' . $domain . '/i', parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST))) {
				exit(jsAddSlashes('<b>HashOver - Error:</b> External use not allowed.', 'single'));
			}
			$page_url = (!isset($canon_url) or empty($canon_url)) ? $_SERVER['HTTP_REFERER'] : $canon_url;
		} else {
			if (!isset($_GET['rss'])) {
				exit(jsAddSlashes('<b>HashOver - Error:</b> No way to get page URL, HTTP referrer not set.', 'single'));
			} else {
				$page_url = $_GET['rss'];
			}
		}
	} else {
		$page_url = (!isset($canon_url) or empty($canon_url)) ? 'http://' . $domain . $_SERVER['REQUEST_URI'] : $canon_url;
		$page_url .= (isset($_GET['hashover_reply'])) ? '?hashover_reply=' . $_GET['hashover_reply'] : ((isset($_GET['hashover_edit'])) ? '?hashover_edit=' . $_GET['hashover_edit'] : '');
	}

	// Set URL to "count_link" query value
	if (isset($script_query)) {
		if (isset($_GET['count_link']) and !empty($_GET['count_link'])) {
			$page_url = $_GET['count_link'];
		}
	}

	// Clean URL for comment thread directory name
	$parse_url = parse_url($page_url); // Turn page URL into array
	$ref_path  = ($parse_url['path'] == '/') ? 'index' : str_replace(array('/', '.', '='), '-', substr($parse_url['path'], 1));
	$ref_queries = (isset($parse_url['query'])) ? explode('&', $parse_url['query']) : array();
	$ignore_queries = array('hashover_reply', 'hashover_edit');
	$parse_url['query'] = '';

	// Remove unwanted URL queries
	if (file_exists('./ignore_queries.txt') and isset($parse_url['query'])) {
		$ignore_queries = array_merge($ignore_queries, explode(PHP_EOL, file_get_contents('ignore_queries.txt')));
	}

	for ($q = 0; $q <= (count($ref_queries) - 1); $q++) {
		if (!in_array($ref_queries[$q], $ignore_queries) and !empty($ref_queries[$q])) {
			if (!in_array(basename($ref_queries[$q], '=' . end(explode('=', $ref_queries[$q]))), $ignore_queries)) {
				$parse_url['query'] .= ($q > 0 and !empty($parse_url['query'])) ? '&' . $ref_queries[$q] : $ref_queries[$q];
			}
		}
	}

	if (!empty($parse_url['query'])) {
		$ref_path .= '-' . str_replace(array('/', '.', '='), '-', $parse_url['query']);
	}

	// Page comments directory
	if ($ref_path != 'hashover-php') {
		$dir = 'pages/' . $ref_path;
	} else {
		exit(jsAddSlashes('<b>HashOver - Error:</b> Failure setting comment directory name'));
	}

?>
