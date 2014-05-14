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

	class Setup extends Settings {
		public $text, $mode, $canon_url, $is_mobile = false, $page_url, $parse_url, $ref_path, $ref_queries, $dir;

		public function __construct() {
			global $mode, $canon_url, $script_query;
			parent::__construct();

			// Set language locale
			$locales = new Locales($this->setting['language']);
			$this->text = $locales->locale;

			// Set output mode
			$this->mode = (isset($mode) ? $mode : 'javascript');

			// Set canonical URL
			if (isset($canon_url)) {
				$this->canon_url = $canon_url;
			}

			// Check if visitor is on mobile device
			if (preg_match('/android/i', $_SERVER['HTTP_USER_AGENT']) or preg_match('/blackberry/i', $_SERVER['HTTP_USER_AGENT']) or preg_match('/phone/i', $_SERVER['HTTP_USER_AGENT'])) {
				$this->is_mobile = true;
				$this->setting['image_format'] = 'svg';
			}

			// Remove harmful characters from cookies
			foreach ($_COOKIE as $name => $value) {
				$search = array('&', '<', '>', '"', "'", '/');
				$replace = array('&amp;', '&lt;', '&gt;', '&quot;', '&#x27;', '&#x2F;');

				foreach ($search as $key => $character) {
					$_COOKIE[$name] = str_replace($character, $replace[$key], $value);
				}
			}

			// Set Canonical URL
			if (!isset($canon_url) and isset($script_query)) {
				if (!empty($_POST['canon_url'])) {
					$this->canon_url = (preg_match('/([http|https]):\/\//i', $_POST['canon_url'])) ? $_POST['canon_url'] : 'http://' . $_POST['canon_url'];
				} else if (!empty($_GET['canon_url'])) {
					$this->canon_url = (preg_match('/([http|https]):\/\//i', $_GET['canon_url'])) ? $_GET['canon_url'] : 'http://' . $_GET['canon_url'];
				}
			}

			// Get full page URL or Canonical URL
			if ($this->mode != 'php') {
				if (isset($_SERVER['HTTP_REFERER']) and !isset($_GET['rss'])) {
					// Check if the script was requested by this server
					if (!preg_match('/' . $this->setting['domain'] . '/i', parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST))) {
						exit($this->escape_output('<b>HashOver - Error:</b> External use not allowed.', 'single'));
					}

					$this->page_url = (empty($this->canon_url)) ? $_SERVER['HTTP_REFERER'] : $this->canon_url;
				} else {
					if (!isset($_GET['rss'])) {
						exit($this->escape_output('<b>HashOver - Error:</b> No way to get page URL, HTTP referrer not set.', 'single'));
					} else {
						$this->page_url = $_GET['rss'];
					}
				}
			} else {
				if (empty($this->canon_url)) {
					$this->page_url = 'http://' . $this->setting['domain'] . $_SERVER['REQUEST_URI'];
				} else {
					$this->page_url = $this->canon_url;
					$this->page_url .= (isset($_GET['hashover_reply'])) ? '?hashover_reply=' . $_GET['hashover_reply'] : ((isset($_GET['hashover_edit'])) ? '?hashover_edit=' . $_GET['hashover_edit'] : '');
				}
			}

			// Set URL to "count_link" query value
			if (isset($script_query)) {
				if (!empty($_GET['count_link'])) {
					$this->page_url = $_GET['count_link'];
				}
			}

			// Clean URL for comment thread directory name
			$this->parse_url = parse_url($this->page_url); // Turn page URL into array
			$this->ref_path  = ($this->parse_url['path'] == '/') ? 'index' : str_replace(array('/', '.', '='), '-', substr($this->parse_url['path'], 1));
			$this->ref_queries = (isset($this->parse_url['query'])) ? explode('&', $this->parse_url['query']) : array();
			$ignore_queries = array('hashover_reply', 'hashover_edit');
			$this->parse_url['query'] = '';

			// Remove unwanted URL queries
			if (file_exists('./ignore_queries.txt') and isset($this->parse_url['query'])) {
				$ignore_queries = array_merge($ignore_queries, explode(PHP_EOL, file_get_contents('ignore_queries.txt')));
			}

			for ($q = 0; $q < count($this->ref_queries); $q++) {
				if (!in_array($this->ref_queries[$q], $ignore_queries) and !empty($this->ref_queries[$q])) {
					$equals = explode('=', $this->ref_queries[$q]);

					if (!in_array(basename($this->ref_queries[$q], '=' . end($equals)), $ignore_queries)) {
						$this->parse_url['query'] .= ($q > 0 and !empty($this->parse_url['query'])) ? '&' . $this->ref_queries[$q] : $this->ref_queries[$q];
					}
				}
			}

			if (!empty($this->parse_url['query'])) {
				$this->ref_path .= '-' . str_replace(array('/', '.', '='), '-', $this->parse_url['query']);
			}

			// Page comments directory
			if ($this->ref_path != 'hashover-php') {
				$this->dir = '.' . $this->setting['root_dir'] . 'pages/' . $this->ref_path;
			} else {
				exit($this->escape_output('<b>HashOver - Error:</b> Failure setting comment directory name'));
			}

			// Create comment thread directory & error on fail
			if (!file_exists($this->dir) and !isset($_GET['count_link'])) {
				if (!@mkdir($this->dir, 0755) and !@chmod($this->dir, 0755)) {
					exit($this->escape_output('<b>HashOver - Error:</b> Failed to create comment thread directory at "' . $this->dir . '"', 'single'));
				}
			}
		}

		// Output for JavaScript mode
		public function escape_output($str, $type = '') {
			if ($this->mode != 'php') {
				if ($type != 'single') {
					return 'hashover += \'' . str_replace(array('\\\n', '\\\r', '\\\\n', "\'+", "+\'", "\t"), array('\n', '\r', '\\n', "'+", "+'", ''), addcslashes($str, "'")) . '\';' . PHP_EOL;
				} else {
					return 'document.write("' . str_replace(array('\\\n', '\\\r', '\"+', '+\"'), array('\n', '\r', '"+', '+"'), addslashes($str)) . '");' . PHP_EOL;
				}
			} else {
				return str_replace(array('\n', '\r'), '', $str) . PHP_EOL;
			}
		}
	}

?>
