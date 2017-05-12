<?php namespace HashOver;

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
	}
}

// Do some standard HashOver setup work
require ('standard-setup.php');
require ('javascript-setup.php');
require ('oop-setup.php');

try {
	// Instantiate HashOver class
	$hashover = new \HashOver ('javascript');
	$hashover->setup->setPageURL ('request');
	$hashover->setup->setPageTitle ('request');
	$hashover->initiate ();
	$hashover->parsePrimary ();
	$hashover->parsePopular ();
	$hashover->finalize ();

	// Start output buffer
	ob_start ();

	// Attempt to include JavaScript frontend code
	if (!@include ('./javascript-mode.php')) {
		ob_end_clean ();
		$error = 'file "javascript-mode.php" could not be included!';
		throw new \Exception ($error);
	}

	// Get output buffer contents and turn off output buffering
	$javascript = ob_get_contents ();
	ob_end_clean ();

	// Minify JavaScript if enabled, and non-minified version isn't requested
	if ($hashover->setup->minifiesJavaScript === true) {
		if (!isset ($_GET['hashover-unminified'])) {
			$jsminifier = new JSMinifier ();
			$javascript = $jsminifier->minify ($javascript, $hashover->setup->minifyLevel);
		}
	}

	// Use JSON Pretty Print if defined, so long as minification is not enabled
	if (defined ('JSON_PRETTY_PRINT')
	    and $hashover->setup->minifiesJavaScript !== true
	    or $hashover->setup->minifyLevel < 2)
	{
		// Encode comments as JSON
		$json_comments = json_encode ($hashover->comments, JSON_PRETTY_PRINT);

		// Convert JSON space indention to tab indention, when minification is not enabled
		$json_search = array ('    ', PHP_EOL);
		$json_replace = array ("\t", PHP_EOL . "\t\t");
		$json_comments = str_replace ($json_search, $json_replace, $json_comments);
	} else {
		// Encode comments as JSON
		$json_comments = json_encode ($hashover->comments);
	}

	// Return JavaScript
	echo str_replace ('HASHOVER_PHP_CONTENT', $json_comments, $javascript);

	// Display statistics
	echo $hashover->statistics->executionEnd ();

} catch (\Exception $error) {
	$misc = new Misc ('javascript');
	$misc->displayError ($error->getMessage ());
}
