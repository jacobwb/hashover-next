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


class Setup extends Settings
{
	public $scheme;
	public $absolutePath;
	public $commentsPath;
	public $threadsPath;
	public $website;
	public $isMobile = false;
	public $remoteAccess = false;
	public $filePath;
	public $urlQueryList = array ();
	public $threadQueryList = array ();
	public $urlQueries;
	public $threadQueries;
	public $threadPath;
	public $threadName;
	public $pageURL;
	public $pageTitle;
	public $instanceNumber = 1;

	// Required extensions to check for
	protected $extensions = array (
		'date', 'dom', 'json', 'mbstring', 'openssl', 'pcre', 'intl'
	);

	// Characters to convert to dashes in thread names
	protected $dashFromThreads = array (
		'<', '>', ':', '"', '/', '\\', '|', '?',
		'&', '!', '*', '.', '=', '_', '+', ' '
	);

	// Characters to convert to dashes in domain names
	protected $dashFromDomains = array (
		'<', '>', ':', '"', '/', '\\', '|', '?',
		'&', '!', '*', '=', '_', '+', ' '
	);

	// HashOver-specific URL queries to be ignored
	protected $hashoverQueries = array (
		'hashover-reply', 'hashover-edit'
	);

	public function __construct ()
	{
		// Construct parent class
		parent::__construct ();

		// Check for required extensions
		$this->extensionsLoaded ($this->extensions);

		// Get connection scheme
		$this->scheme = $this->isHTTPS () ? 'https' : 'http';

		// Absolute path or remote access
		$this->absolutePath = $this->scheme . '://' . $this->domain;

		// Check if multisite support is enabled
		if ($this->supportsMultisites === true) {
			// If so, set website based on domain
			$this->setWebsite ($this->domain);
		} else {
			// If not, comments directory path
			$this->commentsPath = $this->commentsRoot;

			// Set threads directory path
			$this->threadsPath = $this->commentsRoot . '/threads';

			// And set website to "all"
			$this->website = 'all';
		}

		// Check if we have a user agent
		if (!empty ($_SERVER['HTTP_USER_AGENT'])) {
			// If so, get user agent
			$agent = $_SERVER['HTTP_USER_AGENT'];

			// And check if visitor is on mobile device based on user agent
			if (preg_match ('/android|blackberry|phone|mobile|tablet/i', $agent)) {
				// If so, set mobile indicator to true
				$this->isMobile = true;

				// And set image format to vector
				$this->imageFormat = 'svg';
			}
		}
	}

	// Throws an exception if a require extension isn't loaded
	public function extensionsLoaded (array $extensions)
	{
		// Run through given extensions
		foreach ($extensions as $extension) {
			// Throw exception if extension isn't loaded
			if (extension_loaded ($extension) === false) {
				throw new \Exception (
					'Failed to detect required extension: ' . $extension . '.'
				);
			}
		}
	}

	// Checks if host is localhost
	public function isLocalhost ($host)
	{
		// "localhost" equivalent addresses
		$addresses = array ('127.0.0.1', 'localhost', '::1');

		// Check if we're on localhost
		$is_localhost = in_array ($host, $addresses, true);

		return $is_localhost;
	}

	// Sanitizes given data for HTML use
	protected function sanitizeData ($data = '')
	{
		// Strip HTML tags from data
		$data = strip_tags (html_entity_decode ($data, false, 'UTF-8'));

		// Encode HTML characters in data
		$data = htmlspecialchars ($data, false, 'UTF-8', false);

		return $data;
	}

	// Gets sanitized data from POST or GET data
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

	// Sets path to website-specific threads directory
	public function setWebsite ($host = 'request')
	{
		// Do nothing if multisites is disabled
		if ($this->supportsMultisites === false) {
			return;
		}

		// Attempt to obtain website from POST or GET if told to
		if ($host === 'request') {
			$host = $this->requestData ('website');
		}

		// Do nothing else if host is false
		if ($host === false) {
			return;
		}

		// Remove "www." from URL host
		$host = str_replace ('www.', '', $host);

		// Get directory safe name of host
		$host = $this->getSafeDomainName ($host);

		// Set host as "localhost" on local addresses
		$host = $this->isLocalhost ($host) ? 'localhost' : $host;

		// Append website host to comments directory path
		$this->commentsPath = $this->joinPaths ($this->commentsRoot, $host);

		// Set threads directory path
		$this->threadsPath = $this->commentsPath . '/threads';

		// Set thread directory path
		$this->threadPath = $this->threadsPath . '/' . $this->threadName;

		// And set website
		$this->website = $host;
	}

	// Gets a domain with a port from given URL
	protected function getDomainWithPort ($url = '')
	{
		// Parse URL
		$url = parse_url ($url);

		// Throw exception if URL or host is empty
		if ($url === false or empty ($url['host'])) {
			throw new \Exception (
				'Failed to obtain domain name.'
			);
		}

		// If URL has a port, return domain with port
		if (!empty ($url['port'])) {
			return $url['host'] . ':' . $url['port'];
		}

		// Otherwise, return domain without port
		return $url['host'];
	}

	// Enables and sets up remote access
	public function setupRemoteAccess ()
	{
		// Set remote access indicator
		$this->remoteAccess = true;

		// Make HTTP root path absolute
		$this->httpRoot = $this->joinPaths ($this->absolutePath, $this->httpRoot);

		// Synchronize settings
		$this->syncSettings ();
	}

	// Checks remote request against allowed domains setting
	public function refererCheck ()
	{
		// Return true if no referer is set
		if (empty ($_SERVER['HTTP_REFERER'])) {
			return true;
		}

		// Otherwise, get HTTP referer domain with port
		$domain = $this->getDomainWithPort ($_SERVER['HTTP_REFERER']);

		// Return true if referer domain is the same server
		if ($domain === $this->domain) {
			return true;
		}

		// Otherwise, escape wildcard for regular expression
		$sub_regex = '/^' . preg_quote ('\*\.') . '/S';

		// Run through allowed domains
		foreach ($this->allowedDomains as $allowed_domain) {
			// Escape allowed domain for regular expression
			$safe_domain = preg_quote ($allowed_domain);

			// Replace subdomain wildcard with proper regular expression
			$domain_regex = preg_replace ($sub_regex, '(?:.*?\.)*', $safe_domain);

			// Final domain regular expression
			$domain_regex = '/^' . $domain_regex . '$/iS';

			// Check if script was requested from an allowed domain
			if (preg_match ($domain_regex, $domain)) {
				// If so, setup remote access
				$this->setupRemoteAccess ();

				// Connection origin
				$origin = $this->scheme . '://' . $domain;

				// Set remote access headers
				header ('Access-Control-Allow-Origin: ' . $origin);
				header ('Access-Control-Allow-Credentials: true');

				// And return true
				return true;
			}
		}

		// Otherwise, throw exception
		throw new \Exception (
			'External use not allowed.'
		);
	}

	// Gets value from POST or GET data
	public function getRequest ($key, $default = false)
	{
		// Attempt to obtain GET data
		if (!empty ($_GET[$key])) {
			$request = $_GET[$key];
		}

		// Attempt to obtain POST data
		if (!empty ($_POST[$key])) {
			$request = $_POST[$key];
		}

		// Check if we got a value from POST or GET
		if (!empty ($request)) {
			// Check if GET or POST data is type string
			if (gettype ($request) === 'string') {
				// If so, strip escape slashes if enabled
				if (get_magic_quotes_gpc ()) {
					$request = stripslashes ($request);
				}

				// URL decode value
				$request = urldecode ($request);
			}

			// And return POST or GET data
			return $request;
		}

		// Otherwise, return default
		return $default;
	}

	// Gets current page URL
	protected function getPageURL ()
	{
		// Attempt to obtain URL via POST or GET
		$request = $this->getRequest ('url');

		// Return URL from POST or GET if it exists
		if ($request !== false) {
			return $request;
		}

		// Otherwise, return HTTP referer if it exists
		if (!empty ($_SERVER['HTTP_REFERER'])) {
			return $_SERVER['HTTP_REFERER'];
		}

		// Otherwise, throw exception
		throw new \Exception (
			'Failed to obtain page URL.'
		);
	}

	// Gets configured URL queries to be ignored
	protected function getIgnoredQueries ()
	{
		// Initial ignored URL queries
		$queries = array ();

		// Ignored URL queries file
		$ignored_queries = $this->getAbsolutePath ('config/ignored-queries.json');

		// Check if ignored URL queries file exists
		if (file_exists ($ignored_queries)) {
			// If so, get ignored URL queries
			$data = @file_get_contents ($ignored_queries);

			// Parse ignored URL queries JSON file
			$json = @json_decode ($data, true);

			// Merge file with initial queries if parsed successfully
			if ($json !== null) {
				$queries = array_merge ($json, $queries);
			}
		}

		return $queries;
	}

	// Filters URL queries for URLs and thread name usage
	protected function filterURLQueries ($queries)
	{
		// Split queries by ampersand
		$url_queries = explode ('&', $queries);

		// Get configured queries to be ignored
		$ignored_queries = $this->getIgnoredQueries ();

		// Run through queries
		for ($q = 0, $ql = count ($url_queries); $q < $ql; $q++) {
			// Current URL query
			$current = $url_queries[$q];

			// Split current URL query by equals sign
			$query_parts = explode ('=', $current);

			// Current URL query name
			$query = $query_parts[0];

			// Skip default HashOver-specific URL queries
			if (in_array ($query, $this->hashoverQueries, true)) {
				continue;
			}

			// Store query for usage in URLs
			$this->urlQueryList[] = $current;

			// Skip name=value queries to be ignored in thread name
			if (in_array ($current, $ignored_queries, true)) {
				continue;
			}

			// Skip query names to be ignored entirely in thread name
			if (in_array ($query, $ignored_queries, true)) {
				continue;
			}

			// Store query thread name
			$this->threadQueryList[] = $current;
		}
	}

	// Reduces dashes to one per removed character
	protected function reduceDashes ($name)
	{
		// Remove multiple dashes
		if (mb_strpos ($name, '--') !== false) {
			$name = preg_replace ('/-{2,}/', '-', $name);
		}

		// Remove leading and trailing dashes
		$name = trim ($name, '-');

		return $name;
	}

	// Gets an OS-agnostic safe directory name
	protected function getSafeThreadName ($name)
	{
		// Replace reserved characters with dashes
		$name = str_replace ($this->dashFromThreads, '-', $name);

		// Remove multiple/leading/trailing dashes
		$name = $this->reduceDashes ($name);

		return $name;
	}

	// Gets an OS-agnostic safe directory name
	protected function getSafeDomainName ($name)
	{
		// Replace reserved characters with dashes
		$name = str_replace ($this->dashFromDomains, '-', $name);

		// Remove multiple/leading/trailing dashes
		$name = $this->reduceDashes ($name);

		// Remove leading periods to prevent hiding in UNIX
		$name = ltrim ($name, '.');

		return $name;
	}

	// Sets comment thread to read comments from
	public function setThreadName ($name = 'request')
	{
		// Request thread if told to
		if ($name === 'request') {
			$name = $this->requestData ('thread', $this->threadName);
		}

		// Convert thread to safe directory name
		$name = $this->getSafeThreadName ($name);

		// Set thread directory path
		$this->threadPath = $this->joinPaths ($this->threadsPath, $name);

		// And set thread name
		$this->threadName = $name;
	}

	// Sets page URL
	public function setPageURL ($url = 'request')
	{
		// Request page URL by default
		if ($url === 'request') {
			$url = $this->getPageURL ();
		}

		// Strip HTML tags from page URL
		$url = strip_tags (html_entity_decode ($url, false, 'UTF-8'));

		// Turn page URL into array
		$parts = parse_url ($url);

		// Throw exception if URL doesn't have a scheme or host
		if (empty ($parts['scheme']) or empty ($parts['host'])) {
			throw new \Exception (
				'URL needs a hostname and scheme.'
			);
		}

		// Set various paths to be website-specific
		$this->setWebsite ($parts['host']);

		// Check if URL has a path and is not the index
		if (!empty ($parts['path']) and $parts['path'] !== '/') {
			// If so, set file path property
			$this->filePath = $parts['path'];

			// And set thread name as path without slash
			$thread_name = mb_substr ($parts['path'], 1);
		} else {
			// If not, set file path as a single slash
			$this->filePath = '/';

			// And set thread name as index
			$thread_name = 'index';
		}

		// Check if URL has queries
		if (!empty ($parts['query'])) {
			// If so, filter queries in page URL
			$this->filterURLQueries ($parts['query']);

			// Store a string version of page URL queries
			$this->urlQueries = implode ('&', $this->urlQueryList);

			// Store a string version of thread name URL queries
			$this->threadQueries = implode ('&', $this->threadQueryList);

			// And add queries to thread name
			$thread_name .= '-' . $this->threadQueries;
		}

		// Encode HTML characters in page URL
		$url = htmlspecialchars ($url, false, 'UTF-8', false);

		// Final URL scheme and host
		$url = $parts['scheme'] . '://' . $parts['host'];

		// Add optional port to URL
		if (!empty ($parts['port'])) {
			$url .= ':' . $parts['port'];
		}

		// Add file path to URL
		$url .= $this->filePath;

		// Add optional queries to URL
		if (!empty ($this->urlQueries)) {
			$url .= '?' . $this->urlQueries;
		}

		// Set thread directory name to page URL
		$this->setThreadName ($thread_name);

		// And set page URL property
		$this->pageURL = $url;
	}

	// Sets page title
	public function setPageTitle ($title = 'request')
	{
		// Check if we are getting title from POST/GET
		if ($title === 'request') {
			// If so, get sanitized POST/GET data
			$title = $this->requestData ('title', '');
		} else {
			// If not, sanitize given title
			$title = $this->sanitizeData ($title);
		}

		// And set page title
		$this->pageTitle = $title;
	}

	// Sets instance number
	public function setInstance ($instance = 'request')
	{
		// Request instance if told to
		if ($instance === 'request') {
			$instance = $this->getRequest ('instance', '');
		}

		// Throw exception if given instance is not numeric
		if (!is_numeric ($instance)) {
			throw new \Exception (
				'Instance must be a number.'
			);
		}

		// Otherwise, cast instance to actual integer
		$this->instanceNumber = (int)($instance);
	}
}
