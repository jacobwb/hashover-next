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
		}
	}

	// Use UTF-8 character set
	ini_set ('default_charset', 'UTF-8');

	// Enable display of PHP errors
	ini_set ('display_errors', true);
	error_reporting (E_ALL);

	// Tell browser output is JavaScript
	header ('Content-Type: application/javascript');

	// Disable browser cache
	header ('Expires: Wed, 08 May 1991 12:00:00 GMT');
	header ('Last-Modified: ' . gmdate ('D, d M Y H:i:s') . ' GMT');
	header ('Cache-Control: no-store, no-cache, must-revalidate');
	header ('Cache-Control: post-check=0, pre-check=0', false);
	header ('Pragma: no-cache');

	// Attempt to obtain URL via GET
	if (!empty ($_GET['url'])) {
		$page_url = $_GET['url'];
	} else {
		// Attempt to obtain URL via POST
		if (!empty ($_POST['url'])) {
			$page_url = $_POST['url'];
		}
	}

	// Attempt to obtain URL via HTTP referer
	if (empty ($page_url) and !empty ($_SERVER['HTTP_REFERER'])) {
		$page_url = $_SERVER['HTTP_REFERER'];
		$referer = parse_url ($page_url);
		$referer_host = $referer['host'];
		$http_host = $_SERVER['HTTP_HOST'];

		// Add referer port to referer host
		if (!empty ($referer['port'])) {
			$referer_host .= ':' . $referer['port'];
		}

		// Error if the script wasn't requested by this server
		if ($referer_host !== $http_host) {
			exit ('document.getElementById (\'hashover\').innerHTML = \'<b>HashOver</b>: External use not allowed.\';');
		}
	}

	// Error on failure
	if (empty ($page_url)) {
		exit ('document.getElementById (\'hashover\').innerHTML = \'<b>HashOver</b>: Failed to obtain page URL.\';');
	}

	// Attempt to obtain page title via GET or POST
	if (!empty ($_GET['title'])) {
		$page_title = $_GET['title'];
	} else {
		if (!empty ($_POST['title'])) {
			$page_title = $_POST['title'];
		} else {
			// Error on failure
			exit ('document.getElementById (\'hashover\').innerHTML = \'<b>HashOver</b>: Failed to obtain page title.\';');
		}
	}

	// Autoload class files
	spl_autoload_register (function ($classname) {
		$classname = strtolower ($classname);

		if (!@include ('./' . $classname . '.php')) {
			exit ('<b>HashOver</b>: "' . $classname . '.php" file could not be included!');
		}
	});

	// Instantiate HashOver class
	$hashover = new HashOver ('javascript', $page_url, $page_title);
	$hashover->parseAll ();

	// Start output buffer
	ob_start ();

	// Attempt to include JavaScript frontend code
	if (!include ('./javascript-mode.php')) {
		ob_end_clean ();
		exit ('document.getElementById (\'hashover\').innerHTML = \'<b>HashOver - Error:</b> file "javascript-mode.php" could not be included!\';');
	}

	// Get output buffer contents and turn off output buffering
	$javascript = ob_get_contents ();
	ob_end_clean ();

	// Minify JavaScript if enabled, and non-minified version isn't requested
	if ($hashover->settings->minifiesJavaScript === true) {
		if (!isset ($_GET['hashover-unminified'])) {
			$jsminifier = new JSMinifier ();
			$javascript = $jsminifier->minify ($javascript, $hashover->settings->minifyLevel);
		}
	}

	// Use JSON Pretty Print if defined, so long as minification is not enabled
	if (defined ('JSON_PRETTY_PRINT')
	    and $hashover->settings->minifiesJavaScript !== true
	    or $hashover->settings->minifyLevel < 2)
	{
		// Encode comments as JSON
		$json_comments = json_encode ($hashover->comments, JSON_PRETTY_PRINT);

		// Convert JSON space indention to tab indention, when minification is not enabled
		$json_search = array ('    ', PHP_EOL);
		$json_replace = array ("\t", PHP_EOL . "\t");
		$json_comments = str_replace ($json_search, $json_replace, $json_comments);
	} else {
		// Encode comments as JSON
		$json_comments = json_encode ($hashover->comments);
	}

	// Return JavaScript
	echo str_replace ('HASHOVER_PHP_CONTENT', $json_comments, $javascript);

	// Display statistics
	echo $hashover->statistics->executionEnd ();

?>
