<?php namespace HashOver;

// Copyright (C) 2010-2018 Jacob Barkdull
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


class Setup extends Settings
{
	public $usage;
	public $encryption;
	public $remoteAccess = false;
	public $pageURL;
	public $pageTitle;
	public $filePath;
	public $threadName;
	public $commentsDirectory;
	public $pagesDirectory;
	public $threadDirectory;
	public $URLQueryList = array ();
	public $URLQueries;

	// Required extensions to check for
	public $extensions = array (
		'date',
		'dom',
		'json',
		'mbstring',
		'openssl',
		'pcre',
		'PDO',
		'SimpleXML'
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

	// HashOver-specific URL queries to be ignored
	public $ignoredQueries = array (
		'hashover-reply',
		'hashover-edit'
	);

	// Default metadata
	public $metadata = array (
		'title' => '',
		'url' => '',
		'status' => 'open',
		'latest' => array ()
	);

	public function __construct (array $usage)
	{
		parent::__construct ();

		$this->usage = $usage;
		$this->misc = new Misc ($usage['mode']);

		// Check if PHP version is the minimum required
		if (version_compare (PHP_VERSION, '5.3.3') < 0) {
			$version_parts = explode ('-', PHP_VERSION);
			$version = current ($version_parts);

			throw new \Exception ('PHP ' . $version . ' is too old. Must be at least version 5.3.3.');
		}

		// Check for required extensions
		$this->extensionsLoaded ($this->extensions);

		// Comments directory path
		$this->commentsDirectory = $this->getAbsolutePath ('comments');

		// Comment threads directory path
		$this->pagesDirectory = $this->commentsDirectory . '/threads';

		// Throw exception if for Blowfish hashing support isn't detected
		if ((defined ('CRYPT_BLOWFISH') and CRYPT_BLOWFISH) === false) {
			throw new \Exception ('Failed to find CRYPT_BLOWFISH. Blowfish hashing support is required.');
		}

		// Throw exception if notification email is set to the default
		if ($this->notificationEmail === 'example@example.com') {
			throw new \Exception (sprintf (
				'You must use a UNIQUE notification e-mail in %s',
				$this->getSecretConfigPath()
			));
		}

		// Throw exception if encryption key is set to the default
		if ($this->encryptionKey === '8CharKey') {
			throw new \Exception (sprintf (
				'You must use a UNIQUE encryption key in %s',
				$this->getSecretConfigPath()
			));
		}

		// Throw exception if administrative password is set to the default
		if ($this->adminPassword === 'password') {
			throw new \Exception (sprintf (
				'You must use a UNIQUE admin password in %s',
				$this->getSecretConfigPath()
			));
		}

		// Throw exception if the script wasn't requested by this server
		if ($this->usage['mode'] !== 'php') {
			if ($this->refererCheck () === false) {
				throw new \Exception ('External use not allowed.');
			}
		}

		// Instantiate encryption class
		$this->encryption = new Encryption ($this->encryptionKey);

		// Check if visitor is on mobile device
		if (!empty ($_SERVER['HTTP_USER_AGENT'])) {
			if (preg_match ('/(android|blackberry|phone|mobile|tablet)/i', $_SERVER['HTTP_USER_AGENT'])) {
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
				throw new \Exception ('Failed to detect required extension: ' . $extension . '.');
			}
		}
	}

	public function getRequest ($key, $default = false)
	{
		// Return default value if POST and GET are empty
		if (empty ($_GET[$key]) and empty ($_POST[$key])) {
			return $default;
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

	protected function getDomainWithPort ($url = '')
	{
		// Parse URL
		$url = parse_url ($url);

		if ($url === false or empty ($url['host'])) {
			throw new \Exception ('Failed to obtain domain name.');
		}

		// If URL has a port, return domain with port
		if (!empty ($url['port'])) {
			return $url['host'] . ':' . $url['port'];
		}

		// Otherwise return domain without port
		return $url['host'];
	}

	protected function setupRemoteAccess ()
	{
		$this->remoteAccess = true;
		$this->httpRoot = $this->absolutePath . $this->httpRoot;
		$this->syncSettings ();
	}

	protected function refererCheck ()
	{
		// No referer set
		if (empty ($_SERVER['HTTP_REFERER'])) {
			return true;
		}

		// Get HTTP referer domain with port
		$domain = $this->getDomainWithPort ($_SERVER['HTTP_REFERER']);

		// Check if the script was requested by this server
		if ($domain === $this->domain) {
			return true;
		}

		// Run through allowed domains
		foreach ($this->allowedDomains as $allowed_domain) {
			$sub_regex = '/^' . preg_quote ('\*\.') . '/S';
			$safe_domain = preg_quote ($allowed_domain);
			$domain_regex = preg_replace ($sub_regex, '(?:.*?\.)*', $safe_domain);
			$domain_regex = '/^' . $domain_regex . '$/iS';

			// Check if the script was requested from an allowed domain
			if (preg_match ($domain_regex, $domain)) {
				// If so, setup remote access
				$this->setupRemoteAccess ();
				return true;
			}
		}

		// Check if the usage context is an API
		if ($this->usage['context'] === 'api') {
			// If so, setup remote access
			$this->setupRemoteAccess ();
			return true;
		}

		return false;
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
		throw new \Exception ('Failed to obtain page URL.');
	}

	protected function sanitizeData ($data = '')
	{
		// Strip HTML tags from data
		$data = strip_tags (html_entity_decode ($data, false, 'UTF-8'));

		// Encode HTML characters in data
		$data = htmlspecialchars ($data, false, 'UTF-8', false);

		return $data;
	}

	protected function requestData ($data = '', $default = false)
	{
		// Attempt to obtain data via GET or POST
		$request = $this->getRequest ($data, $default);

		// Return on success
		if ($request !== $default) {
			$request = $this->sanitizeData ($request);
		}

		return $request;
	}

	public function setThreadName ($name = '')
	{
		// Requesting the title if told to
		if ($name === 'request') {
			$name = $this->requestData ('thread', $this->threadName);
		}

		// Replace reserved characters with dashes
		$name = str_replace ($this->reservedCharacters, '-', $name);

		// Remove multiple dashes
		if (mb_strpos ($name, '--') !== false) {
			$name = preg_replace ('/-{2,}/', '-', $name);
		}

		// Remove leading and trailing dashes
		$name = trim ($name, '-');

		// Final comment directory name
		$this->threadDirectory = $this->pagesDirectory . '/' . $name;
		$this->threadName = $name;
	}

	protected function getIgnoredQueries ()
	{
		// Ignored URL queries list file
		$ignored_queries = $this->getAbsolutePath ('config/ignored-queries.json');

		// Queries to be ignored
		$queries = $this->ignoredQueries;

		// Check if ignored URL queries list file exists
		if (file_exists ($ignored_queries)) {
			// If so, get ignored URL queries list
			$data = @file_get_contents ($ignored_queries);

			// Parse ignored URL queries list JSON
			$json = @json_decode ($data, true);

			// Check if file parsed successfully
			if ($json !== null) {
				// If so, merge ignored URL queries file with defaults
				$queries = array_merge ($json, $queries);
			}
		}

		return $queries;
	}

	public function setPageURL ($url = '')
	{
		// Set page URL
		$this->pageURL = $url;

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
			$this->threadName = 'index';
			$this->filePath = '/';
		} else {
			// Remove starting slash
			$this->threadName = mb_substr ($url_parts['path'], 1);

			// Set file path
			$this->filePath = $url_parts['path'];
		}

		// Remove unwanted URL queries
		if (!empty ($url_parts['query'])) {
			$url_queries = explode ('&', $url_parts['query']);
			$ignored_queries = $this->getIgnoredQueries ();

			for ($q = 0, $ql = count ($url_queries); $q < $ql; $q++) {
				if (!in_array ($url_queries[$q], $ignored_queries, true)) {
					$equals = explode ('=', $url_queries[$q]);

					if (!in_array ($equals[0], $ignored_queries, true)) {
						$this->URLQueryList[] = $url_queries[$q];
					}
				}
			}

			$this->URLQueries = implode ('&', $this->URLQueryList);
			$this->threadName .= '-' . $this->URLQueries;
		}

		// Encode HTML characters in page URL
		$this->pageURL = htmlspecialchars ($this->pageURL, false, 'UTF-8', false);

		// Final URL
		if (!empty ($url_parts['scheme']) and !empty ($url_parts['host'])) {
			$this->pageURL  = $url_parts['scheme'] . '://';
			$this->pageURL .= $url_parts['host'];
		} else {
			throw new \Exception ('URL needs a hostname and scheme.');
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
		$this->setThreadName ($this->threadName);
	}

	public function setPageTitle ($title = '')
	{
		// Requesting the title if told to
		if ($title === 'request') {
			$title = $this->requestData ('title', '');
		}

		// Sanitize page title
		$title = $this->sanitizeData ($title);

		// Set page title
		$this->pageTitle = $title;
	}

	// Weak verification of an admin login
	public function adminLogin ($hash)
	{
		return ($hash === hash ('ripemd160', $this->adminName . $this->adminPassword));
	}

	// Strict verification of an admin login
	public function verifyAdmin ($password)
	{
		return $this->encryption->verifyHash ($this->adminPassword, $password);
	}
}
