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
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		if (isset($_GET['source'])) {
			header('Content-type: text/plain; charset=UTF-8');
			exit(file_get_contents(basename(__FILE__)));
		}
	}

	class Setup extends Settings
	{
		public $text,
		       $mode,
		       $is_mobile = false,
		       $encryption,
		       $page_title,
		       $page_url,
		       $parse_url,
		       $ref_path,
		       $ref_queries,
		       $dir,
		       $data_template,
		       $data_format_class,
		       $user_is_admin = false;

		public function __construct($mode = 'javascript', $hashover_title = '')
		{
			parent::__construct();

			// Set timezone
			date_default_timezone_set($this->timezone);

			$cookie_search = array('&', '<', '>', '"', "'", '/');
			$cookie_replace = array('&amp;', '&lt;', '&gt;', '&quot;', '&#x27;', '&#x2F;');
			$reserved_chars = array('<', '>', ':', '"', '/', '\\', '|', '?', '&', '!', '*', '.', '=', '_', '+', ' ');

			// Set output mode
			$this->mode = $mode;

			// Check if PHP version is the minimum required
			if (version_compare(PHP_VERSION, '5.3.3') < 0) {
				exit($this->escape_output('<b>HashOver</b>: PHP ' . current(explode('-', PHP_VERSION)) . ' is too old. Must be at least version 5.3.3.', 'single'));
			}

			// Check for Blowfish hashing support
			if (!(defined('CRYPT_BLOWFISH') and CRYPT_BLOWFISH)) {
				exit($this->escape_output('<b>HashOver</b>: Failed to find CRYPT_BLOWFISH. Blowfish hashing support is required.', 'single'));
			}

			// Exit if encryption key is set to the default
			if ($this->encryption_key == '8CharKey') {
				exit($this->escape_output('<b>HashOver</b>: You must use a UNIQUE encryption key in /hashover/scripts/settings.php', 'single'));
			}

			// Exit if notification email is set to the default
			if ($this->notification_email == 'example@example.com') {
				exit($this->escape_output('<b>HashOver</b>: You must use a UNIQUE notification e-mail in /hashover/scripts/settings.php', 'single'));
			}

			// Exit if administrative nickname is set to the default
			if ($this->admin_name == 'admin') {
				exit($this->escape_output('<b>HashOver</b>: You must use a UNIQUE admin nickname in /hashover/scripts/settings.php', 'single'));
			}

			// Exit if administrative password is set to the default
			if ($this->admin_password == 'password') {
				exit($this->escape_output('<b>HashOver</b>: You must use a UNIQUE admin password in /hashover/scripts/settings.php', 'single'));
			}

			// Check if visitor is on mobile device
			if (!empty($_SERVER['HTTP_USER_AGENT'])) {
				if (preg_match('/(android|blackberry|phone)/i', $_SERVER['HTTP_USER_AGENT'])) {
					$this->is_mobile = true;
					$this->image_format = 'svg';
				}
			}

			// Attempt to obtain URL via POST or GET or HTTP REFERER
			if (!empty($_POST['url'])) {
				$this->page_url = $_POST['url'];
			} else {
				if (!empty($_GET['url'])) {
					$this->page_url = $_GET['url'];
				} else {
					if ($mode == 'javascript') {
						if (!empty($_SERVER['HTTP_REFERER'])) {
							$this->page_url = $_SERVER['HTTP_REFERER'];
						} else {
							// Error on failure
							exit($this->escape_output('<b>HashOver</b>: Failed to obtain page URL.', 'single'));
						}
					} else {
						$scheme = 'http' . ((!empty($_SERVER['HTTPS'])) ? 's' : '') . '://';
						$this->page_url = $scheme . $this->domain . $_SERVER['REQUEST_URI'];
					}
				}

				// Error if the script wasn't requested by this server
				if (!empty($_SERVER['HTTP_REFERER']) and !preg_match('/' . $this->domain . '/i', parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST))) {
					exit($this->escape_output('<b>HashOver</b>: External use not allowed.', 'single'));
				}
			}

			// Attempt to obtain URL via POST or GET
			if (empty($hashover_title) and $mode != 'api') {
				if (!empty($_POST['title'])) {
					$this->page_title = $_POST['title'];
				} else {
					if (!empty($_GET['title'])) {
						$this->page_title = $_GET['title'];
					} else {
						// Error on failure
						exit($this->escape_output('<b>HashOver</b>: Failed to obtain page title.', 'single'));
					}
				}
			} else {
				$this->page_title = $hashover_title;
			}

			// Remove harmful characters from cookies
			foreach ($_COOKIE as $name => $value) {
				foreach ($cookie_search as $key => $character) {
					$_COOKIE[$name] = str_replace($character, $cookie_replace[$key], $value);
				}
			}

			// Check if user is logged in as admin
			if (!empty($_COOKIE['name']) and $_COOKIE['name'] === $this->admin_name) {
				if (!empty($_COOKIE['password']) and $_COOKIE['password'] === $this->admin_password) {
					$this->user_is_admin = true;
				}
			}

			// Default comment information fields
			$this->data_template = array(
				'name' => '',
				'password' => '',
				'login_id' => '',
				'email' => '',
				'encryption' => '',
				'website' => '',
				'date' => '',
				'body' => '',
				'likes' => 0,
				'dislikes' => 0,
				'notifications' => 'no',
				'status' => 'approved'
			);

			$this->metadata = array(
				'title' => '',
				'url' => '',
				'status' => 'open',
				'latest' => array()
			);

			// Encryption
			$this->encryption = new Encryption($this->encryption_key);

			// Set language locale
			$locales = new Locales($this->language);
			$this->text = $locales->locale;

			// Turn page URL into array
			$this->parse_url = parse_url($this->page_url);

			// Set initial path
			if ($this->parse_url['path'] == '/') {
				$this->ref_path = 'index';
			} else {
				// Remove starting slash and replace reserved characters with dashes
				$this->ref_path = substr($this->parse_url['path'], 1);
				$this->ref_path = str_replace($reserved_chars, '-', $this->ref_path);
			}

			if (!empty($this->parse_url['query'])) {
				$this->parse_url['query'] = explode('&', $this->parse_url['query']);
				$ignore_queries = array('hashover_reply', 'hashover_edit');
				$ignore_queries_file = './ignore_queries.txt';

				// Remove unwanted URL queries
				if (file_exists($ignore_queries_file)) {
					$ignore_queries_file = explode(PHP_EOL, file_get_contents($ignore_queries_file));
					$ignore_queries = array_merge($ignore_queries, $ignore_queries_file);
				}

				for ($q = 0, $ql = count($this->parse_url['query']); $q < $ql; $q++) {
					if (!in_array($this->parse_url['query'][$q], $ignore_queries, true)) {
						$equals = explode('=', $this->parse_url['query'][$q]);

						if (in_array($equals[0], $ignore_queries, true)) {
							$this->page_url = str_replace($this->parse_url['query'][$q], '', $this->page_url);
						} else {
							$this->ref_queries .= (($q > 0) ? '&' : '') . $this->parse_url['query'][$q];
						}
					}
				}

				$this->page_url = trim($this->page_url, '?&');
				$this->ref_path .= '-' . str_replace($reserved_chars, '-', $this->ref_queries);
			} else {
				$this->parse_url['query'] = array();
			}

			// Remove multiple dashes
			if (strpos($this->ref_path, '--') !== false) {
				$this->ref_path = preg_replace('/-{2,}/', '-', $this->ref_path);
			}

			// Strip HTML tags from page URL
			$this->page_url = strip_tags(html_entity_decode($this->page_url, false, 'UTF-8'));

			// Encode HTML characters in page URL
			$this->page_url = htmlspecialchars($this->page_url, false, 'UTF-8', false);

			// Strip HTML tags from page title
			$this->page_title = strip_tags(html_entity_decode($this->page_title, false, 'UTF-8'));

			// Encode HTML characters in page title
			$this->page_title = htmlspecialchars($this->page_title, false, 'UTF-8', false);

			// Remove leading and trailing dashes
			$this->ref_path = trim($this->ref_path, '-');

			// Final comment directory name
			$this->dir = './pages/' . $this->ref_path;
		}

		// Check if a give API format is enabled
		public function api_status($api)
		{
			// Check if all available APIs are fully enabled
			if ($this->enable_api == 'yes') {
				return 'enabled';
			}

			// Check if specific APIs are enabled
			if (is_array($this->enable_api)) {
				// Check if API is partially enabled, if so return its value
				if (in_array($api, array_keys($this->enable_api), true)) {
					return $this->enable_api[$api];
				} else {
					// Check if the give API format is fully enabled
					if (in_array($api, $this->enable_api)) {
						return 'enabled';
					}
				}
			}

			// Assume API is disabled by default
			return 'disabled';
		}

		// Output for JavaScript mode
		public function escape_output($str, $type = '')
		{
			if ($this->mode == 'javascript') {
				if ($type != 'single') {
					return 'hashover += \'' . str_replace(array('\\\n', '\\\r', '\\\\n', "\'+", "+\'", "\t"), array('\n', '\r', '\\n', "'+", "+'", ''), $str) . '\';' . PHP_EOL;
				} else {
					return 'document.getElementById(\'hashover\').innerHTML = "' . str_replace(array('\\\n', '\\\r', '\"+', '+\"'), array('\n', '\r', '"+', '+"'), addslashes($str)) . '";' . PHP_EOL;
				}
			} else {
				return str_replace(array('\n', '\r'), '', $str) . PHP_EOL;
			}
		}
	}

?>
