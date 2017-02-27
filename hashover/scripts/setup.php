<?php

// Copyright (C) 2010-2017 Jacob Barkdull
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
	public $usage;
	public $encryption;
	public $remoteAccess = false;
	public $pageURL;
	public $pageTitle;
	public $filePath;
	public $threadDirectory;
	public $dir;
	public $URLQueryList = array ();
	public $URLQueries;
	public $executingScript = false;

	// Default metadata
	public $metadata = array (
		'title' => '',
		'url' => '',
		'status' => 'open',
		'latest' => array ()
	);

	// Characters that aren't allowed in directory names
	public $reservedCharacters = array (
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

	public $extensions = array (
		'date',
		'dom',
		'mbstring',
		'mcrypt',
		'pcre',
		'PDO',
		'SimpleXML'
	);

	public function __construct (array $usage)
	{
		$this->usage = $usage;
		$this->misc = new Misc ($usage['mode']);

		// Execute parent constructor
		parent::__construct ();

		// Check if PHP version is the minimum required
		if (version_compare (PHP_VERSION, '5.3.3') < 0) {
			$version_parts = explode ('-', PHP_VERSION);
			$version = current ($version_parts);

			throw new Exception ('PHP ' . $version . ' is too old. Must be at least version 5.3.3.');
		}

		// Check for required extensions
		$this->extensionsLoaded ($this->extensions);

		// JSON settings file path
		$json_settings = $this->rootDirectory . '/settings.json';

		// Check for JSON settings file; parse it if it exists
		if (file_exists ($json_settings)) {
			$this->JSONSettings ($json_settings);
		}

		// Throw exception if for Blowfish hashing support isn't detected
		if ((defined ('CRYPT_BLOWFISH') and CRYPT_BLOWFISH) === false) {
			throw new Exception ('Failed to find CRYPT_BLOWFISH. Blowfish hashing support is required.');
		}

		// Throw exception if notification email is set to the default
		if ($this->notificationEmail === 'example@example.com') {
			throw new Exception ('You must use a UNIQUE notification e-mail in ' . __FILE__);
		}

		// Throw exception if encryption key is set to the default
		if ($this->encryptionKey === '8CharKey') {
			throw new Exception ('You must use a UNIQUE encryption key in ' . __FILE__);
		}

		// Throw exception if administrative password is set to the default
		if ($this->adminPassword === 'password') {
			throw new Exception ('You must use a UNIQUE admin password in ' . __FILE__);
		}

		// Throw exception if the script wasn't requested by this server
		if ($this->usage['mode'] === 'javascript' and $this->refererCheck () === false) {
			throw new Exception ('External use not allowed.');
		}

		// Check if we are placing HashOver at a specific script's position
		if (!empty ($_GET['hashover-script'])) {
			// If so, make the script query XSS safe
			$hashover_script = $this->misc->makeXSSsafe ($_GET['hashover-script']);

			// Check if the script query contains a numeric value
			if (is_numeric ($hashover_script)) {
				// If so, set it as the executing script
				$this->executingScript = (int)($hashover_script);
			} else {
				// If not, throw an exception
				throw new Exception ('Script query must have a numeric value.');
			}
		}

		// Instantiate encryption class
		$this->encryption = new Encryption ($this->encryptionKey);

		// Check if visitor is on mobile device
		if (!empty ($_SERVER['HTTP_USER_AGENT'])) {
			if (preg_match ('/(android|blackberry|phone)/i', $_SERVER['HTTP_USER_AGENT'])) {
				// Adjust settings to accommodate
				$this->isMobile = true;
				$this->imageFormat = 'svg';
			}
		}
	}

	public function extensionsLoaded (array $extensions)
	{
		// Throw exceptions if an extension isn't loaded
		foreach ($extensions as $extension) {
			if (extension_loaded ($extension) === false) {
				throw new Exception ('Failed to detect required extension: ' . $extension . '.');
			}
		}
	}

	protected function JSONSettings ($path)
	{
		// Load and decode JSON settings file
		$json_settings = json_decode (file_get_contents ($path), true);

		// Return void on failure
		if ($json_settings === null) {
			return;
		}

		// Loop through each setting
		foreach ($json_settings as $key => $value) {
			// Convert setting name to camelCase
			$title_case_key = ucwords (str_replace ('-', ' ', strtolower ($key)));
			$setting = lcfirst (str_replace (' ', '', $title_case_key));

			// Check if the JSON setting property exists in the defaults
			if (property_exists ($this, $setting)) {
				// Check if the JSON value is the same type as the default
				if (gettype ($value) === gettype ($this->{$setting})) {
					// Override default setting
					$this->{$setting} = $value;
				}
			}
		}

		// Synchronize settings
		$this->syncSettings ();
	}

	protected function getDomainWithPort ($url = '')
	{
		// Parse URL
		$url = parse_url ($url);

		if ($url === false or empty ($url['host'])) {
			throw new Exception ('Failed to obtain domain name.');
			return false;
		}

		// If URL has a port, return domain with port
		if (!empty ($url['port'])) {
			return $url['host'] . ':' . $url['port'];
		}

		// Otherwise return domain without port
		return $url['host'];
	}

	protected function refererCheck ()
	{
		// No referer set
		if (empty ($_SERVER['HTTP_REFERER'])) {
			return false;
		}

		// Get HTTP referer domain with port
		$domain = $this->getDomainWithPort ($_SERVER['HTTP_REFERER']);

		// Check if the script was requested by this server
		if ($domain === $this->domain) {
			return true;
		}

		// Check if the script was requested from an allowed domain
		foreach ($this->allowedDomains as $allowed_domain) {
			$sub_regex = '/^' . preg_quote ('\*\.') . '/';
			$safe_domain = preg_quote ($allowed_domain);
			$domain_regex = preg_replace ($sub_regex, '(?:.*?\.)*', $safe_domain);
			$domain_regex = '/^' . $domain_regex . '$/i';

			if (preg_match ($domain_regex, $domain)) {
				// Setup remote access
				$this->remoteAccess = true;
				$this->httpRoot = $this->absolutePath . $this->httpRoot;
				$this->allowsLikes = false;
				$this->allowsDislikes = false;
				$this->usesAJAX = false;
				$this->syncSettings ();

				return true;
			}
		}

		return false;
	}

	protected function getRequest ($key)
	{
		if (empty ($_GET[$key]) and empty ($_POST[$key])) {
			return false;
		}

		// Attempt to obtain GET data
		if (!empty ($_GET[$key])) {
			$request = $_GET[$key];
		}

		// Attempt to obtain POST data
		if (!empty ($_POST[$key])) {
			$request = $_POST[$key];
		}

		// Strip escape slashes from POST or GET
		if (get_magic_quotes_gpc ()) {
			$request = stripslashes ($request);
		}

		return $request;
	}

	protected function getPageURL ()
	{
		// Attempt to obtain URL via GET or POST
		$request = $this->getRequest ('url');

		// Return on success
		if ($request !== false) {
			return $request;
		}

		// Attempt to obtain URL via HTTP referer
		if (!empty ($_SERVER['HTTP_REFERER'])) {
			return $_SERVER['HTTP_REFERER'];
		}

		// Error on failure
		throw new Exception ('Failed to obtain page URL.');
	}

	public function setThreadDirectory ($directory_name = '')
	{
		// Replace reserved characters with dashes
		$directory_name = str_replace ($this->reservedCharacters, '-', $directory_name);

		// Remove multiple dashes
		if (mb_strpos ($directory_name, '--') !== false) {
			$directory_name = preg_replace ('/-{2,}/', '-', $directory_name);
		}

		// Remove leading and trailing dashes
		$directory_name = trim ($directory_name, '-');

		// Final comment directory name
		$this->threadDirectory = $directory_name;
		$this->dir = $this->rootDirectory . '/pages/' . $directory_name;
	}

	public function setPageURL ($url = '')
	{
		// Set page URL
		$this->pageURL = $url;

		try {
			// Request page URL by default
			if (empty ($url) or $url === 'request') {
				$this->pageURL = $this->getPageURL ();
			}

			// Strip HTML tags from page URL
			$this->pageURL = strip_tags (html_entity_decode ($this->pageURL, false, 'UTF-8'));

			// Turn page URL into array
			$url_parts = parse_url ($this->pageURL);

			// Set initial path
			if (empty ($url_parts['path']) or $url_parts['path'] === '/') {
				$this->threadDirectory = 'index';
				$this->filePath = '/';
			} else {
				// Remove starting slash
				$this->threadDirectory = mb_substr ($url_parts['path'], 1);

				// Set file path
				$this->filePath = $url_parts['path'];
			}

			if (!empty ($url_parts['query'])) {
				$url_parts['query'] = explode ('&', $url_parts['query']);
				$ignore_queries = array ('hashover-reply', 'hashover-edit');
				$ignore_queries_file = $this->rootDirectory . '/ignore-queries.txt';

				// Remove unwanted URL queries
				if (file_exists ($ignore_queries_file)) {
					$ignore_queries_file = explode (PHP_EOL, file_get_contents ($ignore_queries_file));
					$ignore_queries = array_merge ($ignore_queries_file, $ignore_queries);
				}

				for ($q = 0, $ql = count ($url_parts['query']); $q < $ql; $q++) {
					if (!in_array ($url_parts['query'][$q], $ignore_queries, true)) {
						$equals = explode ('=', $url_parts['query'][$q]);

						if (!in_array ($equals[0], $ignore_queries, true)) {
							$this->URLQueryList[] = $url_parts['query'][$q];
						}
					}
				}

				$this->URLQueries = implode ('&', $this->URLQueryList);
				$this->threadDirectory .= '-' . $this->URLQueries;
			} else {
				$url_parts['query'] = array ();
			}

			// Encode HTML characters in page URL
			$this->pageURL = htmlspecialchars ($this->pageURL, false, 'UTF-8', false);

			// Final URL
			if (!empty ($url_parts['scheme']) and !empty ($url_parts['host'])) {
				$this->pageURL  = $url_parts['scheme'] . '://';
				$this->pageURL .= $url_parts['host'];
			} else {
				throw new Exception ('URL needs a hostname and scheme.');
				return;
			}

			// Add optional port to URL
			if (!empty ($url_parts['port'])) {
				$this->pageURL .= ':' . $url_parts['port'];
			}

			// Add file path
			$this->pageURL .= $this->filePath;

			// Add option queries
			if (!empty ($this->URLQueries)) {
				$this->pageURL .= '?' . $this->URLQueries;
			}

			// Set thread directory name to page URL
			$this->setThreadDirectory ($this->threadDirectory);

		} catch (Exception $error) {
			throw new Exception ($error->getMessage ());
		}
	}

	protected function getPageTitle ()
	{
		// Attempt to obtain title via GET or POST
		$request = $this->getRequest ('title');

		// Return on success
		if ($request !== false) {
			return $request;
		}

		// Return empty string by default
		return '';
	}

	public function setPageTitle ($title = '')
	{
		// Set page title
		$this->pageTitle = $title;

		// Request page title by default
		if ($title === 'request') {
			$this->pageTitle = $this->getPageTitle ();
		}

		// Strip HTML tags from page title
		$this->pageTitle = strip_tags (html_entity_decode ($this->pageTitle, false, 'UTF-8'));

		// Encode HTML characters in page title
		$this->pageTitle = htmlspecialchars ($this->pageTitle, false, 'UTF-8', false);
	}

	// Check if a give API format is enabled
	public function APIStatus ($api)
	{
		// Check if all available APIs are enabled
		if ($this->enablesAPI === true) {
			return 'enabled';
		}

		// Check if the given API is enabled
		if (is_array ($this->enablesAPI)) {
			if (in_array ($api, $this->enablesAPI)) {
				return 'enabled';
			}
		}

		// Assume API is disabled by default
		return 'disabled';
	}

	// Check if user name and password again admin name and password
	public function verifyAdmin ($name, $password)
	{
		// Check if user is logged in as admin
		if ($name === $this->adminName) {
			if ($this->encryption->verifyHash ($this->adminPassword, $password) === true) {
				return true;
			}
		}

		return false;
	}
}
