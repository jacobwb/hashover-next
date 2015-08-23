<?php

	// Copyright (C) 2010-2015 Jacob Barkdull
	//
	//	This file is part of HashOver.
	//
	//	HashOver is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	HashOver is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		} else {
			exit ('<b>HashOver</b>: This is a class file.');
		}
	}

	class Setup extends Settings
	{
		public $mode;
		public $pageURL;
		public $pageTitle;
		public $encryption;
		public $userName = '';
		public $userPassword = '';
		public $userEmail = '';
		public $userEncryption = '';
		public $userWebsite = '';
		public $userIsLoggedIn = false;
		public $userIsAdmin = false;
		public $parsedURL;
		public $threadDirectory;
		public $URLQueries;
		public $dir;

		// Default metadata
		public $metadata = array (
			'title' => '',
			'url' => '',
			'status' => 'open',
			'latest' => array ()
		);

		public
		function __construct ($mode = 'javascript', $page_url, $page_title = '')
		{
			parent::__construct ();

			// Check if PHP version is the minimum required
			if (version_compare (PHP_VERSION, '5.3.3') < 0) {
				$version_parts = explode ('-', PHP_VERSION);
				$version = current ($version_parts);

				exit ($this->escapeOutput ('<b>HashOver</b>: PHP ' . $version . ' is too old. Must be at least version 5.3.3.', 'single'));
			}

			// Check for Blowfish hashing support
			if ((defined ('CRYPT_BLOWFISH') and CRYPT_BLOWFISH) === false) {
				exit ($this->escapeOutput ('<b>HashOver</b>: Failed to find CRYPT_BLOWFISH. Blowfish hashing support is required.', 'single'));
			}

			// Exit if encryption key is set to the default
			if ($this->encryptionKey === '8CharKey') {
				exit ($this->escapeOutput ('<b>HashOver</b>: You must use a UNIQUE encryption key in /hashover/scripts/settings.php', 'single'));
			}

			// Exit if notification email is set to the default
			if ($this->notificationEmail === 'example@example.com') {
				exit ($this->escapeOutput ('<b>HashOver</b>: You must use a UNIQUE notification e-mail in /hashover/scripts/settings.php', 'single'));
			}

			// Exit if administrative nickname is set to the default
			if ($this->adminName === 'admin') {
				exit ($this->escapeOutput ('<b>HashOver</b>: You must use a UNIQUE admin nickname in /hashover/scripts/settings.php', 'single'));
			}

			// Exit if administrative password is set to the default
			if ($this->adminPassword === 'password') {
				exit ($this->escapeOutput ('<b>HashOver</b>: You must use a UNIQUE admin password in /hashover/scripts/settings.php', 'single'));
			}

			// Set mode
			$this->mode = $mode;

			// Set page URL
			if (!empty ($page_url)) {
				$this->pageURL = $page_url;
			} else {
				// Error on failure
				exit ($this->escapeOutput ('<b>HashOver</b>: Failed to obtain page URL.', 'single'));
			}

			// Set page title
			if (!empty ($page_title)) {
				$this->pageTitle = $page_title;
			}

			// Instantiate encryption class
			$this->encryption = new Encryption ($this->encryptionKey);

			// Strip escape slashes from POST, GET, and COOKIE data
			if (get_magic_quotes_gpc ()) {
				$_GET = array_map ('stripslashes', $_GET);
				$_COOKIE = array_map ('stripslashes', $_COOKIE);
				$_POST = array_map ('stripslashes', $_POST);
			}

			// Cookie replacement search patterns
			$cookie_search = array (
				'&',
				'<',
				'>',
				'"',
				"'",
				'/'
			);

			// Cookie replacement characters
			$cookie_replace = array (
				'&amp;',
				'&lt;',
				'&gt;',
				'&quot;',
				'&#x27;',
				'&#x2F;'
			);

			// Remove harmful characters from cookies
			foreach ($_COOKIE as $name => $value) {
				$_COOKIE[$name] = str_replace ($cookie_search, $cookie_replace, $value);
			}

			// Setup user name via cookie
			if (!empty ($_COOKIE['name']) and $_COOKIE['name'] !== $this->defaultName) {
				$this->userName = $_COOKIE['name'];
			}

			// Setup user password via cookie
			if (!empty ($_COOKIE['password'])) {
				$this->userPassword = $_COOKIE['password'];
			}

			// Setup user e-mail via cookie
			if (!empty ($_COOKIE['email'])) {
				$encrypted_email = trim (html_entity_decode ($_COOKIE['email'], ENT_COMPAT, 'UTF-8'), " \r\n\t");
				$encryption_keys = !empty ($_COOKIE['encryption']) ? $_COOKIE['encryption'] : '';
				$decrypted_email = $this->encryption->decrypt ($encrypted_email, $encryption_keys);

				if (filter_var ($decrypted_email, FILTER_VALIDATE_EMAIL)) {
					$this->userEmail = $decrypted_email;
				}
			}

			// Setup user website via cookie
			if (!empty ($_COOKIE['website'])) {
				$this->userWebsite = $_COOKIE['website'];
			}

			// Check if user is logged in
			if (!empty ($_COOKIE['hashover-login'])) {
				$this->userIsLoggedIn = true;

				// Check if user is logged in as admin
				if ($this->userName === $this->adminName) {
					$decoded_password = trim (html_entity_decode ($this->userPassword, ENT_COMPAT, 'UTF-8'), " \r\n\t");
					$passwords_match = $this->encryption->verifyHash ($this->adminPassword, $decoded_password);

					if ($passwords_match === true) {
						$this->userIsAdmin = true;
					}
				}
			}

			// Turn page URL into array
			$this->parsedURL = parse_url ($this->pageURL);

			// Set initial path
			if (empty ($this->parsedURL['path']) or $this->parsedURL['path'] === '/') {
				$this->threadDirectory = 'index';
			} else {
				// Remove starting slash
				$this->threadDirectory = substr ($this->parsedURL['path'], 1);
			}

			if (!empty ($this->parsedURL['query'])) {
				$this->parsedURL['query'] = explode ('&', $this->parsedURL['query']);
				$ignore_queries = array ('hashover_reply', 'hashover_edit');
				$ignore_queries_file = $this->rootDirectory . '/ignore_queries.txt';

				// Remove unwanted URL queries
				if (file_exists ($ignore_queries_file)) {
					$ignore_queries_file = explode (PHP_EOL, file_get_contents ($ignore_queries_file));
					$ignore_queries = array_merge ($ignore_queries, $ignore_queries_file);
				}

				for ($q = 0, $ql = count ($this->parsedURL['query']); $q < $ql; $q++) {
					if (!in_array ($this->parsedURL['query'][$q], $ignore_queries, true)) {
						$equals = explode ('=', $this->parsedURL['query'][$q]);

						if (in_array ($equals[0], $ignore_queries, true)) {
							$this->pageURL = str_replace ($this->parsedURL['query'][$q], '', $this->pageURL);
						} else {
							$this->URLQueries .= (($q > 0) ? '&' : '') . $this->parsedURL['query'][$q];
						}
					}
				}

				$this->pageURL = trim ($this->pageURL, '?&');
				$this->threadDirectory .= '-' . $this->URLQueries;
			} else {
				$this->parsedURL['query'] = array ();
			}

			// Characters that aren't allowed in directory names
			$reserved_chars = array (
				'<',
				'>',
				':',
				'"',
				'/',
				'\\',
				'|',
				'?',
				'&',
				'!',
				'*',
				'.',
				'=',
				'_',
				'+',
				' '
			);

			// Replace reserved characters with dashes
			$this->threadDirectory = str_replace ($reserved_chars, '-', $this->threadDirectory);

			// Remove multiple dashes
			if (strpos ($this->threadDirectory, '--') !== false) {
				$this->threadDirectory = preg_replace ('/-{2,}/', '-', $this->threadDirectory);
			}

			// Strip HTML tags from page URL
			$this->pageURL = strip_tags (html_entity_decode ($this->pageURL, false, 'UTF-8'));

			// Encode HTML characters in page URL
			$this->pageURL = htmlspecialchars ($this->pageURL, false, 'UTF-8', false);

			// Strip HTML tags from page title
			$this->pageTitle = strip_tags (html_entity_decode ($this->pageTitle, false, 'UTF-8'));

			// Encode HTML characters in page title
			$this->pageTitle = htmlspecialchars ($this->pageTitle, false, 'UTF-8', false);

			// Remove leading and trailing dashes
			$this->threadDirectory = trim ($this->threadDirectory, '-');

			// Final comment directory name
			$this->dir = $this->rootDirectory . '/pages/' . $this->threadDirectory;
		}

		// Check if a give API format is enabled
		public
		function APIStatus ($api)
		{
			// Check if all available APIs are fully enabled
			if ($this->enablesAPI === true) {
				return 'enabled';
			}

			// Check if specific APIs are enabled
			if (is_array ($this->enablesAPI)) {
				// Check if API is partially enabled, if so return its value
				if (in_array ($api, array_keys ($this->enablesAPI), true)) {
					return $this->enablesAPI[$api];
				} else {
					// Check if the give API format is fully enabled
					if (in_array ($api, $this->enablesAPI)) {
						return 'enabled';
					}
				}
			}

			// Assume API is disabled by default
			return 'disabled';
		}

		// Output for JavaScript mode
		public
		function escapeOutput ($str, $type = '')
		{
			if ($this->mode === 'javascript') {
				if ($type !== 'single') {
					return 'hashover += \'' . str_replace (array ('\\\n', '\\\r', '\\\\n', "\'+", "+\'", "\t"), array ('\n', '\r', '\\n', "'+", "+'", ''), $str) . '\';' . PHP_EOL;
				} else {
					return 'document.getElementById (\'hashover\').innerHTML = "' . str_replace (array ('\\\n', '\\\r', '\"+', '+\"'), array ('\n', '\r', '"+', '+"'), addslashes ($str)) . '";' . PHP_EOL;
				}
			} else {
				return str_replace (array ('\n', '\r'), '', $str) . PHP_EOL;
			}
		}
	}

?>
